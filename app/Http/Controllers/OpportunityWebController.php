<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\Contact;
use App\Models\Opportunity;
use App\Models\PipelineStage;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OpportunityWebController extends Controller
{
    public function index(Request $request): View
    {
        $stages = PipelineStage::orderBy('position')->get();

        $query = Opportunity::with(['stage', 'account', 'contact', 'owner']);

        if ($search = $request->get('search')) {
            $query->where('name', 'like', "%{$search}%");
        }

        if ($stage = $request->get('stage')) {
            $query->where('pipeline_stage_id', $stage);
        }

        $opportunities = $query->latest()->paginate(25)->withQueryString();

        // For kanban view - group by stage
        $kanbanData = [];
        foreach ($stages as $stage) {
            $kanbanData[$stage->id] = Opportunity::with(['account', 'contact', 'owner'])
                ->where('pipeline_stage_id', $stage->id)
                ->whereNull('won_at')
                ->whereNull('lost_at')
                ->latest()
                ->get();
        }

        return view('opportunities.index', compact('stages', 'opportunities', 'kanbanData'));
    }

    public function create(): View
    {
        $stages      = PipelineStage::orderBy('position')->get(['id', 'name', 'probability']);
        $accounts    = Account::orderBy('name')->get(['id', 'name']);
        $contacts    = Contact::orderBy('first_name')->get(['id', 'first_name', 'last_name']);
        $owners      = User::where('is_active', true)->orderBy('name')->get(['id', 'name']);
        $opportunity = null;

        return view('opportunities.create', compact('opportunity', 'stages', 'accounts', 'contacts', 'owners'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name'              => ['required', 'string', 'max:255'],
            'amount'            => ['nullable', 'numeric', 'min:0'],
            'close_date'        => ['nullable', 'date'],
            'probability'       => ['nullable', 'integer', 'min:0', 'max:100'],
            'pipeline_stage_id' => ['required', 'integer', 'exists:pipeline_stages,id'],
            'account_id'        => ['nullable', 'integer', 'exists:accounts,id'],
            'contact_id'        => ['nullable', 'integer', 'exists:contacts,id'],
            'description'       => ['nullable', 'string'],
            'next_steps'        => ['nullable', 'string'],
            'competitor'        => ['nullable', 'string', 'max:255'],
            'source'            => ['nullable', 'string', 'max:50'],
            'owner_id'          => ['nullable', 'integer', 'exists:users,id'],
        ]);

        $validated['created_by'] = auth()->id();
        $validated['owner_id']   = $validated['owner_id'] ?? auth()->id();

        $opportunity = Opportunity::create($validated);

        return redirect()->route('opportunities.show', $opportunity)
            ->with('success', 'Opportunity created successfully.');
    }

    public function show(Opportunity $opportunity): View
    {
        $opportunity->load([
            'stage', 'account', 'contact', 'owner',
            'teamMembers', 'quotes', 'activities.user', 'notes.user', 'tags',
        ]);

        return view('opportunities.show', compact('opportunity'));
    }

    public function edit(Opportunity $opportunity): View
    {
        $stages   = PipelineStage::orderBy('position')->get(['id', 'name', 'probability']);
        $accounts = Account::orderBy('name')->get(['id', 'name']);
        $contacts = Contact::orderBy('first_name')->get(['id', 'first_name', 'last_name']);
        $owners   = User::where('is_active', true)->orderBy('name')->get(['id', 'name']);

        return view('opportunities.create', compact('opportunity', 'stages', 'accounts', 'contacts', 'owners'));
    }

    public function update(Request $request, Opportunity $opportunity): RedirectResponse
    {
        $validated = $request->validate([
            'name'              => ['required', 'string', 'max:255'],
            'amount'            => ['nullable', 'numeric', 'min:0'],
            'close_date'        => ['nullable', 'date'],
            'probability'       => ['nullable', 'integer', 'min:0', 'max:100'],
            'pipeline_stage_id' => ['required', 'integer', 'exists:pipeline_stages,id'],
            'account_id'        => ['nullable', 'integer', 'exists:accounts,id'],
            'contact_id'        => ['nullable', 'integer', 'exists:contacts,id'],
            'description'       => ['nullable', 'string'],
            'next_steps'        => ['nullable', 'string'],
            'competitor'        => ['nullable', 'string', 'max:255'],
            'source'            => ['nullable', 'string', 'max:50'],
            'owner_id'          => ['nullable', 'integer', 'exists:users,id'],
        ]);

        $opportunity->update($validated);

        return redirect()->route('opportunities.show', $opportunity)
            ->with('success', 'Opportunity updated successfully.');
    }

    public function destroy(Opportunity $opportunity): RedirectResponse
    {
        $opportunity->delete();

        return redirect()->route('opportunities.index')
            ->with('success', 'Opportunity deleted successfully.');
    }
}
