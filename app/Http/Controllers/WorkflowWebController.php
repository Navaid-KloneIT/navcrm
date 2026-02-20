<?php

namespace App\Http\Controllers;

use App\Enums\WorkflowTrigger;
use App\Models\Workflow;
use App\Models\WorkflowAction;
use App\Models\WorkflowCondition;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WorkflowWebController extends Controller
{
    public function index(Request $request): View
    {
        $query = Workflow::with(['creator', 'runs']);

        $query->search($request->get('search'), ['name', 'description']);

        if ($trigger = $request->get('trigger')) {
            $query->where('trigger_event', $trigger);
        }

        if ($request->filled('active')) {
            $query->where('is_active', (bool) $request->get('active'));
        }

        $workflows = $query->latest()->paginate(25)->withQueryString();

        $stats = [
            'total'     => Workflow::count(),
            'active'    => Workflow::where('is_active', true)->count(),
            'runs_today' => \App\Models\WorkflowRun::where('tenant_id', auth()->user()->tenant_id)
                ->whereDate('triggered_at', today())
                ->count(),
        ];

        $triggers = WorkflowTrigger::cases();

        return view('workflows.index', compact('workflows', 'stats', 'triggers'));
    }

    public function create(): View
    {
        $workflow = null;
        $triggers = WorkflowTrigger::cases();
        $users    = \App\Models\User::where('tenant_id', auth()->user()->tenant_id)
            ->orderBy('name')->get();

        return view('workflows.create', compact('workflow', 'triggers', 'users'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateWorkflow($request);

        $workflow = Workflow::create([
            'created_by'     => auth()->id(),
            'name'           => $validated['name'],
            'description'    => $validated['description'] ?? null,
            'is_active'      => $validated['is_active'] ?? true,
            'trigger_event'  => $validated['trigger_event'],
            'trigger_config' => $validated['trigger_config'] ?? null,
        ]);

        $this->syncConditions($workflow, $request->input('conditions', []));
        $this->syncActions($workflow, $request->input('actions', []));

        return redirect()->route('workflows.show', $workflow)
            ->with('success', 'Workflow created successfully.');
    }

    public function show(Workflow $workflow): View
    {
        $workflow->load(['creator', 'conditions', 'actions']);
        $runs = $workflow->runs()->paginate(20);

        return view('workflows.show', compact('workflow', 'runs'));
    }

    public function edit(Workflow $workflow): View
    {
        $workflow->load(['conditions', 'actions']);
        $triggers = WorkflowTrigger::cases();
        $users    = \App\Models\User::where('tenant_id', auth()->user()->tenant_id)
            ->orderBy('name')->get();

        return view('workflows.create', compact('workflow', 'triggers', 'users'));
    }

    public function update(Request $request, Workflow $workflow): RedirectResponse
    {
        $validated = $this->validateWorkflow($request);

        $workflow->update([
            'name'           => $validated['name'],
            'description'    => $validated['description'] ?? null,
            'is_active'      => $validated['is_active'] ?? true,
            'trigger_event'  => $validated['trigger_event'],
            'trigger_config' => $validated['trigger_config'] ?? null,
        ]);

        // Delete and recreate conditions/actions
        $workflow->conditions()->delete();
        $workflow->actions()->delete();

        $this->syncConditions($workflow, $request->input('conditions', []));
        $this->syncActions($workflow, $request->input('actions', []));

        return redirect()->route('workflows.show', $workflow)
            ->with('success', 'Workflow updated successfully.');
    }

    public function destroy(Workflow $workflow): RedirectResponse
    {
        $workflow->delete();

        return redirect()->route('workflows.index')
            ->with('success', 'Workflow deleted.');
    }

    public function toggle(Workflow $workflow): RedirectResponse
    {
        $workflow->update(['is_active' => ! $workflow->is_active]);

        $state = $workflow->is_active ? 'activated' : 'deactivated';

        return back()->with('success', "Workflow {$state}.");
    }

    private function validateWorkflow(Request $request): array
    {
        return $request->validate([
            'name'                             => ['required', 'string', 'max:255'],
            'description'                      => ['nullable', 'string'],
            'is_active'                        => ['sometimes', 'boolean'],
            'trigger_event'                    => ['required', 'string', 'in:' . implode(',', array_column(WorkflowTrigger::cases(), 'value'))],
            'trigger_config'                   => ['nullable', 'array'],
            'trigger_config.discount_threshold'=> ['nullable', 'numeric', 'min:0', 'max:100'],
            'conditions'                       => ['nullable', 'array'],
            'conditions.*.field'               => ['required_with:conditions', 'string'],
            'conditions.*.operator'            => ['required_with:conditions', 'string', 'in:eq,neq,gt,lt,gte,lte,contains'],
            'conditions.*.value'               => ['required_with:conditions', 'string'],
            'actions'                          => ['required', 'array', 'min:1'],
            'actions.*.action_type'            => ['required', 'string', 'in:send_email,assign_user,change_status,send_webhook'],
            'actions.*.action_config'          => ['nullable', 'array'],
        ]);
    }

    private function syncConditions(Workflow $workflow, array $conditions): void
    {
        foreach ($conditions as $i => $cond) {
            if (empty($cond['field']) || empty($cond['operator']) || ! isset($cond['value'])) {
                continue;
            }
            WorkflowCondition::create([
                'workflow_id' => $workflow->id,
                'field'       => $cond['field'],
                'operator'    => $cond['operator'],
                'value'       => $cond['value'],
                'sort_order'  => $i,
            ]);
        }
    }

    private function syncActions(Workflow $workflow, array $actions): void
    {
        foreach ($actions as $i => $act) {
            if (empty($act['action_type'])) {
                continue;
            }
            WorkflowAction::create([
                'workflow_id'   => $workflow->id,
                'action_type'   => $act['action_type'],
                'action_config' => $act['action_config'] ?? [],
                'sort_order'    => $i,
            ]);
        }
    }
}
