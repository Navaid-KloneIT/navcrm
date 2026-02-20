<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Document;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DocumentController extends Controller
{
    public function index(Request $request): JsonResponse
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

        return response()->json($query->latest()->paginate(25)->withQueryString());
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title'          => ['required', 'string', 'max:255'],
            'type'           => ['required', 'string', 'in:nda,contract,proposal,sow,msa,other'],
            'body'           => ['required', 'string'],
            'template_id'    => ['nullable', 'integer', 'exists:document_templates,id'],
            'account_id'     => ['nullable', 'integer', 'exists:accounts,id'],
            'contact_id'     => ['nullable', 'integer', 'exists:contacts,id'],
            'opportunity_id' => ['nullable', 'integer', 'exists:opportunities,id'],
            'notes'          => ['nullable', 'string'],
            'expires_at'     => ['nullable', 'date'],
        ]);

        $tenantId = auth()->user()->tenant_id;
        $validated['created_by']      = auth()->id();
        $validated['owner_id']        = auth()->id();
        $validated['document_number'] = $this->generateDocumentNumber($tenantId);

        $document = Document::create($validated);

        return response()->json($document->load(['account', 'contact']), 201);
    }

    public function show(Document $document): JsonResponse
    {
        return response()->json($document->load([
            'template', 'account', 'contact', 'opportunity', 'owner', 'signatories', 'versions',
        ]));
    }

    public function update(Request $request, Document $document): JsonResponse
    {
        $validated = $request->validate([
            'title'          => ['sometimes', 'string', 'max:255'],
            'type'           => ['sometimes', 'string', 'in:nda,contract,proposal,sow,msa,other'],
            'status'         => ['sometimes', 'string', 'in:draft,sent,viewed,signed,rejected,expired'],
            'body'           => ['sometimes', 'string'],
            'account_id'     => ['nullable', 'integer', 'exists:accounts,id'],
            'contact_id'     => ['nullable', 'integer', 'exists:contacts,id'],
            'opportunity_id' => ['nullable', 'integer', 'exists:opportunities,id'],
            'notes'          => ['nullable', 'string'],
            'expires_at'     => ['nullable', 'date'],
        ]);

        $document->update($validated);

        return response()->json($document->fresh());
    }

    public function destroy(Document $document): JsonResponse
    {
        $document->delete();
        return response()->json(null, 204);
    }

    private function generateDocumentNumber(int $tenantId): string
    {
        $last = Document::withTrashed()
            ->where('tenant_id', $tenantId)
            ->max('document_number');

        $number = 1;
        if ($last && preg_match('/DOC-(\d+)/', $last, $m)) {
            $number = (int) $m[1] + 1;
        }

        return 'DOC-' . str_pad($number, 5, '0', STR_PAD_LEFT);
    }
}
