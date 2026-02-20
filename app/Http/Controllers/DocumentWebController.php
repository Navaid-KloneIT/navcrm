<?php

namespace App\Http\Controllers;

use App\Enums\DocumentStatus;
use App\Enums\DocumentType;
use App\Models\Account;
use App\Models\Contact;
use App\Models\Document;
use App\Models\DocumentSignatory;
use App\Models\DocumentTemplate;
use App\Models\Opportunity;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;
use Illuminate\Support\Str;

class DocumentWebController extends Controller
{
    public function index(Request $request): View
    {
        $query = Document::with(['account', 'contact', 'owner']);

        $query->search($request->get('search'), ['document_number', 'title']);

        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }
        if ($type = $request->get('type')) {
            $query->where('type', $type);
        }
        if ($accountId = $request->get('account_id')) {
            $query->where('account_id', $accountId);
        }

        $documents = $query->latest()->paginate(25)->withQueryString();
        $accounts  = Account::orderBy('name')->get(['id', 'name']);

        $tenantId = auth()->user()->tenant_id;
        $stats = [
            'total'   => Document::where('tenant_id', $tenantId)->count(),
            'sent'    => Document::where('tenant_id', $tenantId)->where('status', 'sent')->count(),
            'signed'  => Document::where('tenant_id', $tenantId)->where('status', 'signed')->count(),
            'expired' => Document::where('tenant_id', $tenantId)->where('status', 'expired')->count(),
        ];

        return view('documents.index', compact('documents', 'accounts', 'stats'));
    }

    public function create(Request $request): View
    {
        $document    = null;
        $templates   = DocumentTemplate::where('is_active', true)->orderBy('name')->get(['id', 'name', 'type', 'body']);
        $accounts    = Account::orderBy('name')->get(['id', 'name']);
        $contacts    = Contact::orderBy('first_name')->get(['id', 'first_name', 'last_name']);
        $opportunities = Opportunity::orderBy('name')->get(['id', 'name']);
        $users       = User::where('is_active', true)->orderBy('name')->get(['id', 'name']);

        $prefilledBody = null;
        $selectedTemplate = null;

        if ($templateId = $request->get('template_id')) {
            $selectedTemplate = DocumentTemplate::find($templateId);
            if ($selectedTemplate) {
                $prefilledBody = $this->substituteVariables($selectedTemplate->body, $request);
            }
        }

        return view('documents.create', compact(
            'document', 'templates', 'accounts', 'contacts', 'opportunities', 'users',
            'prefilledBody', 'selectedTemplate'
        ));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateDocument($request);
        $tenantId  = auth()->user()->tenant_id;

        $validated['tenant_id']       = $tenantId;
        $validated['created_by']      = auth()->id();
        $validated['owner_id']        = $validated['owner_id'] ?? auth()->id();
        $validated['document_number'] = $this->generateDocumentNumber($tenantId);

        $document = Document::create($validated);

        return redirect()->route('documents.show', $document)
            ->with('success', 'Document created successfully.');
    }

    public function show(Document $document): View
    {
        $document->load(['template', 'account', 'contact', 'opportunity', 'owner', 'createdBy', 'signatories', 'versions.savedBy']);
        return view('documents.show', compact('document'));
    }

    public function edit(Document $document): View
    {
        $document->load(['account', 'contact', 'opportunity', 'owner']);
        $templates   = DocumentTemplate::where('is_active', true)->orderBy('name')->get(['id', 'name', 'type']);
        $accounts    = Account::orderBy('name')->get(['id', 'name']);
        $contacts    = Contact::orderBy('first_name')->get(['id', 'first_name', 'last_name']);
        $opportunities = Opportunity::orderBy('name')->get(['id', 'name']);
        $users       = User::where('is_active', true)->orderBy('name')->get(['id', 'name']);
        $prefilledBody = null;
        $selectedTemplate = null;

        return view('documents.create', compact(
            'document', 'templates', 'accounts', 'contacts', 'opportunities', 'users',
            'prefilledBody', 'selectedTemplate'
        ));
    }

    public function update(Request $request, Document $document): RedirectResponse
    {
        $validated = $this->validateDocument($request);

        // Save version snapshot if body changed and not in draft
        if ($document->status->value !== 'draft' && isset($validated['body']) && $validated['body'] !== $document->body) {
            $nextVersion = ($document->versions()->max('version_number') ?? 0) + 1;
            $document->versions()->create([
                'version_number' => $nextVersion,
                'body'           => $document->body,
                'saved_by'       => auth()->id(),
                'created_at'     => now(),
            ]);
        }

        $document->update($validated);

        return redirect()->route('documents.show', $document)
            ->with('success', 'Document updated successfully.');
    }

    public function destroy(Document $document): RedirectResponse
    {
        $document->delete();
        return redirect()->route('documents.index')
            ->with('success', 'Document deleted.');
    }

    public function download(Document $document): Response
    {
        $html = view('documents.pdf', compact('document'))->render();
        $pdf  = Pdf::loadHTML($html)->setPaper('a4');
        return $pdf->download($document->document_number . '.pdf');
    }

    public function sendForm(Document $document): View
    {
        $document->load('signatories');
        return view('documents.send', compact('document'));
    }

    public function send(Request $request, Document $document): RedirectResponse
    {
        $request->validate([
            'name'  => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
        ]);

        $signatory = $document->signatories()->create([
            'name'       => $request->name,
            'email'      => $request->email,
            'sign_token' => Str::uuid()->toString(),
            'status'     => 'pending',
        ]);

        // Update document status to 'sent' if it was draft/viewed
        if (in_array($document->status->value, ['draft'])) {
            $document->update(['status' => 'sent', 'sent_at' => now()]);
        }

        $signingUrl = route('signing.show', $signatory->sign_token);

        return redirect()->route('documents.show', $document)
            ->with('success', 'Signatory added. Signing link: ' . $signingUrl);
    }

    private function substituteVariables(string $body, Request $request): string
    {
        $account     = $request->get('account_id') ? Account::find($request->get('account_id')) : null;
        $contact     = $request->get('contact_id') ? Contact::find($request->get('contact_id')) : null;
        $opportunity = $request->get('opportunity_id') ? Opportunity::find($request->get('opportunity_id')) : null;

        $variables = [
            '{{Account.Name}}'      => $account?->name ?? '',
            '{{Contact.Name}}'      => $contact ? trim($contact->first_name . ' ' . $contact->last_name) : '',
            '{{Contact.Email}}'     => $contact?->email ?? '',
            '{{Opportunity.Name}}'  => $opportunity?->name ?? '',
            '{{Opportunity.Value}}' => $opportunity?->value ? number_format((float) $opportunity->value, 2) : '',
            '{{Today.Date}}'        => now()->format('F j, Y'),
            '{{Tenant.Name}}'       => auth()->user()->tenant->name ?? '',
        ];

        return str_replace(array_keys($variables), array_values($variables), $body);
    }

    private function generateDocumentNumber(int $tenantId): string
    {
        $max = Document::where('tenant_id', $tenantId)
            ->withTrashed()
            ->max('document_number');

        $num = $max ? ((int) substr($max, 4)) + 1 : 1;
        return 'DOC-' . str_pad($num, 5, '0', STR_PAD_LEFT);
    }

    private function validateDocument(Request $request): array
    {
        return $request->validate([
            'title'          => ['required', 'string', 'max:255'],
            'type'           => ['required', 'string', 'in:' . implode(',', array_column(DocumentType::cases(), 'value'))],
            'template_id'    => ['nullable', 'integer', 'exists:document_templates,id'],
            'account_id'     => ['nullable', 'integer', 'exists:accounts,id'],
            'contact_id'     => ['nullable', 'integer', 'exists:contacts,id'],
            'opportunity_id' => ['nullable', 'integer', 'exists:opportunities,id'],
            'owner_id'       => ['nullable', 'integer', 'exists:users,id'],
            'body'           => ['required', 'string'],
            'notes'          => ['nullable', 'string'],
            'expires_at'     => ['nullable', 'date'],
        ]);
    }
}
