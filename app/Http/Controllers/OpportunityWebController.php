<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\Contact;
use App\Models\Opportunity;
use App\Models\PipelineStage;
use App\Models\Project;
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

        $query->search($request->get('search'), ['name']);
        $query->filterOwner($request->get('owner_id'));
        $query->filterDateRange($request->get('close_from'), $request->get('close_to'), 'close_date');

        if ($stage = $request->get('stage')) {
            $query->where('pipeline_stage_id', $stage);
        }

        if ($accountId = $request->get('account_id')) {
            $query->where('account_id', $accountId);
        }

        if ($amountMin = $request->get('amount_min')) {
            $query->where('amount', '>=', $amountMin);
        }

        if ($amountMax = $request->get('amount_max')) {
            $query->where('amount', '<=', $amountMax);
        }

        $opportunities = $query->latest()->paginate(25)->withQueryString();

        // Kanban: open deals grouped by stage (no filters, no pagination)
        $kanbanData = [];
        foreach ($stages as $s) {
            $kanbanData[$s->id] = Opportunity::with(['account', 'contact', 'owner'])
                ->where('pipeline_stage_id', $s->id)
                ->whereNull('won_at')
                ->whereNull('lost_at')
                ->latest()
                ->get();
        }

        // Pipeline header KPIs
        $pipelineStats = [
            'total_value'  => (float) Opportunity::whereNull('won_at')->whereNull('lost_at')->sum('amount'),
            'total_count'  => Opportunity::whereNull('won_at')->whereNull('lost_at')->count(),
            'closing_month'=> (float) Opportunity::whereNull('won_at')->whereNull('lost_at')
                ->whereMonth('close_date', now()->month)->whereYear('close_date', now()->year)->sum('amount'),
        ];

        $owners   = User::where('is_active', true)->orderBy('name')->get(['id', 'name']);
        $accounts = Account::orderBy('name')->get(['id', 'name']);

        return view('opportunities.index', compact('stages', 'opportunities', 'kanbanData', 'pipelineStats', 'owners', 'accounts'));
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

    public function convertToProject(Opportunity $opportunity): RedirectResponse
    {
        // Guard: prevent duplicate projects from same opportunity
        $existing = Project::where('opportunity_id', $opportunity->id)->first();
        if ($existing) {
            return redirect()->route('projects.show', $existing)
                ->with('success', 'A project already exists for this opportunity.');
        }

        // Generate project number
        $tenantId = auth()->user()->tenant_id;
        $last = Project::withTrashed()->where('tenant_id', $tenantId)->max('project_number');
        $number = 1;
        if ($last && preg_match('/PRJ-(\d+)/', $last, $m)) {
            $number = (int) $m[1] + 1;
        }
        $projectNumber = 'PRJ-' . str_pad($number, 5, '0', STR_PAD_LEFT);

        $project = Project::create([
            'project_number'      => $projectNumber,
            'name'                => $opportunity->name,
            'description'         => $opportunity->description,
            'status'              => 'active',
            'opportunity_id'      => $opportunity->id,
            'account_id'          => $opportunity->account_id,
            'contact_id'          => $opportunity->contact_id,
            'manager_id'          => $opportunity->owner_id,
            'is_from_opportunity' => true,
            'created_by'          => auth()->id(),
            'currency'            => 'USD',
        ]);

        return redirect()->route('projects.show', $project)
            ->with('success', 'Project created from opportunity "' . $opportunity->name . '".');
    }
}
