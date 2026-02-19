<?php

namespace App\Http\Controllers;

use App\Models\Lead;
use App\Models\Tag;
use App\Models\User;
use App\Services\LeadConversionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LeadWebController extends Controller
{
    public function index(Request $request): View
    {
        $searchCols = ['first_name', 'last_name', 'email', 'company_name'];

        $query = Lead::with(['tags', 'owner']);
        $query->search($request->get('search'), $searchCols);
        $query->filterOwner($request->get('owner_id'));
        $query->filterDateRange($request->get('date_from'), $request->get('date_to'));

        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }

        if ($score = $request->get('score')) {
            $query->where('score', $score);
        }

        if ($source = $request->get('source')) {
            $query->where('source', $source);
        }

        if ($tag = $request->get('tag')) {
            $query->whereHas('tags', fn ($q) => $q->where('name', 'like', "%{$tag}%"));
        }

        $leads = $query->latest()->paginate(25)->withQueryString();

        // Kanban: same filters without pagination, grouped by status
        $kanbanQuery = Lead::with('owner');
        $kanbanQuery->search($request->get('search'), $searchCols);
        $kanbanQuery->filterOwner($request->get('owner_id'));
        $kanbanQuery->filterDateRange($request->get('date_from'), $request->get('date_to'));
        if ($status = $request->get('status')) $kanbanQuery->where('status', $status);
        if ($score  = $request->get('score'))  $kanbanQuery->where('score', $score);
        if ($source = $request->get('source')) $kanbanQuery->where('source', $source);
        if ($tag    = $request->get('tag'))    $kanbanQuery->whereHas('tags', fn ($q) => $q->where('name', 'like', "%{$tag}%"));

        $kanbanLeads = $kanbanQuery->latest()->get()->groupBy(fn ($l) => $l->status->value);

        $statusCounts = Lead::selectRaw('status, count(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status');

        $owners  = User::where('is_active', true)->orderBy('name')->get(['id', 'name']);
        $sources = Lead::distinct()->whereNotNull('source')->orderBy('source')->pluck('source');

        return view('leads.index', compact('leads', 'statusCounts', 'kanbanLeads', 'owners', 'sources'));
    }

    public function create(): View
    {
        $tags   = Tag::orderBy('name')->get(['id', 'name']);
        $owners = User::where('is_active', true)->orderBy('name')->get(['id', 'name']);
        $lead   = null;

        return view('leads.create', compact('lead', 'tags', 'owners'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'first_name'   => ['required', 'string', 'max:100'],
            'last_name'    => ['required', 'string', 'max:100'],
            'email'        => ['nullable', 'email', 'max:255'],
            'phone'        => ['nullable', 'string', 'max:50'],
            'company_name' => ['nullable', 'string', 'max:255'],
            'job_title'    => ['nullable', 'string', 'max:150'],
            'status'       => ['nullable', 'string', 'max:50'],
            'score'        => ['nullable', 'string', 'max:20'],
            'source'       => ['nullable', 'string', 'max:50'],
            'description'  => ['nullable', 'string'],
            'owner_id'     => ['nullable', 'integer', 'exists:users,id'],
            'tags'         => ['nullable', 'array'],
            'tags.*'       => ['integer', 'exists:tags,id'],
        ]);

        $validated['created_by'] = auth()->id();
        $validated['owner_id']   = $validated['owner_id'] ?? auth()->id();

        $tags = $validated['tags'] ?? [];
        unset($validated['tags']);

        $lead = Lead::create($validated);

        if ($tags) {
            $lead->tags()->sync($tags);
        }

        return redirect()->route('leads.show', $lead)
            ->with('success', 'Lead created successfully.');
    }

    public function show(Lead $lead): View
    {
        $lead->load(['tags', 'owner', 'activities.user', 'notes.user', 'convertedContact', 'convertedAccount']);

        return view('leads.show', compact('lead'));
    }

    public function edit(Lead $lead): View
    {
        $tags   = Tag::orderBy('name')->get(['id', 'name']);
        $owners = User::where('is_active', true)->orderBy('name')->get(['id', 'name']);

        return view('leads.create', compact('lead', 'tags', 'owners'));
    }

    public function update(Request $request, Lead $lead): RedirectResponse
    {
        $validated = $request->validate([
            'first_name'   => ['required', 'string', 'max:100'],
            'last_name'    => ['required', 'string', 'max:100'],
            'email'        => ['nullable', 'email', 'max:255'],
            'phone'        => ['nullable', 'string', 'max:50'],
            'company_name' => ['nullable', 'string', 'max:255'],
            'job_title'    => ['nullable', 'string', 'max:150'],
            'status'       => ['nullable', 'string', 'max:50'],
            'score'        => ['nullable', 'string', 'max:20'],
            'source'       => ['nullable', 'string', 'max:50'],
            'description'  => ['nullable', 'string'],
            'owner_id'     => ['nullable', 'integer', 'exists:users,id'],
            'tags'         => ['nullable', 'array'],
            'tags.*'       => ['integer', 'exists:tags,id'],
        ]);

        $tags = $validated['tags'] ?? null;
        unset($validated['tags']);

        $lead->update($validated);

        if ($tags !== null) {
            $lead->tags()->sync($tags);
        }

        return redirect()->route('leads.show', $lead)
            ->with('success', 'Lead updated successfully.');
    }

    public function destroy(Lead $lead): RedirectResponse
    {
        $lead->delete();

        return redirect()->route('leads.index')
            ->with('success', 'Lead deleted successfully.');
    }

    public function convert(Request $request, Lead $lead): RedirectResponse
    {
        if ($lead->is_converted) {
            return back()->with('error', 'This lead has already been converted.');
        }

        try {
            $service = app(LeadConversionService::class);
            $result  = $service->convert($lead);

            return redirect()->route('contacts.show', $result['contact'])
                ->with('success', 'Lead converted to Contact and Account successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to convert lead: ' . $e->getMessage());
        }
    }
}
