<?php

namespace App\Http\Controllers;

use App\Enums\DocumentType;
use App\Models\DocumentTemplate;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DocumentTemplateWebController extends Controller
{
    public function index(Request $request): View
    {
        $query = DocumentTemplate::with(['createdBy']);

        $query->search($request->get('search'), ['name', 'description']);

        if ($type = $request->get('type')) {
            $query->where('type', $type);
        }

        $templates = $query->latest()->paginate(25)->withQueryString();

        return view('document_templates.index', compact('templates'));
    }

    public function create(): View
    {
        $template = null;
        return view('document_templates.create', compact('template'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateTemplate($request);
        $validated['created_by'] = auth()->id();

        $template = DocumentTemplate::create($validated);

        return redirect()->route('document-templates.show', $template)
            ->with('success', 'Template created successfully.');
    }

    public function show(DocumentTemplate $documentTemplate): View
    {
        $documentTemplate->load(['createdBy', 'documents.account']);
        return view('document_templates.show', ['template' => $documentTemplate]);
    }

    public function edit(DocumentTemplate $documentTemplate): View
    {
        return view('document_templates.create', ['template' => $documentTemplate]);
    }

    public function update(Request $request, DocumentTemplate $documentTemplate): RedirectResponse
    {
        $validated = $this->validateTemplate($request);
        $documentTemplate->update($validated);

        return redirect()->route('document-templates.show', $documentTemplate)
            ->with('success', 'Template updated successfully.');
    }

    public function destroy(DocumentTemplate $documentTemplate): RedirectResponse
    {
        $documentTemplate->delete();
        return redirect()->route('document-templates.index')
            ->with('success', 'Template deleted.');
    }

    private function validateTemplate(Request $request): array
    {
        return $request->validate([
            'name'        => ['required', 'string', 'max:255'],
            'type'        => ['required', 'string', 'in:' . implode(',', array_column(DocumentType::cases(), 'value'))],
            'description' => ['nullable', 'string'],
            'body'        => ['required', 'string'],
            'is_active'   => ['sometimes', 'boolean'],
        ]);
    }
}
