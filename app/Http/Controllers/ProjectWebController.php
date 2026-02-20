<?php

namespace App\Http\Controllers;

use App\Enums\ProjectStatus;
use App\Models\Account;
use App\Models\Contact;
use App\Models\Opportunity;
use App\Models\Project;
use App\Models\ProjectMilestone;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProjectWebController extends Controller
{
    public function index(Request $request): View
    {
        $query = Project::with(['account', 'manager', 'milestones']);

        $query->search($request->get('search'), ['name', 'project_number']);

        if ($managerId = $request->get('manager_id')) {
            $query->where('manager_id', $managerId);
        }

        $query->filterDateRange($request->get('due_from'), $request->get('due_to'), 'due_date');

        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }

        $projects = $query->latest()->paginate(25)->withQueryString();

        // Kanban: group by status
        $kanbanData = [];
        foreach (ProjectStatus::cases() as $status) {
            if ($status === ProjectStatus::Cancelled) {
                continue;
            }
            $kanbanData[$status->value] = Project::with(['account', 'manager', 'milestones'])
                ->where('status', $status->value)
                ->latest()
                ->get();
        }

        $managers = User::where('is_active', true)->orderBy('name')->get(['id', 'name']);
        $statuses = ProjectStatus::cases();

        // Stats
        $stats = [
            'total'     => Project::count(),
            'active'    => Project::where('status', 'active')->count(),
            'completed' => Project::where('status', 'completed')->count(),
            'overdue'   => Project::whereNotIn('status', ['completed', 'cancelled'])
                ->whereNotNull('due_date')
                ->where('due_date', '<', now())
                ->count(),
        ];

        return view('projects.index', compact('projects', 'kanbanData', 'managers', 'statuses', 'stats'));
    }

    public function create(): View
    {
        $opportunities = Opportunity::orderBy('name')->get(['id', 'name']);
        $accounts      = Account::orderBy('name')->get(['id', 'name']);
        $contacts      = Contact::orderBy('first_name')->get(['id', 'first_name', 'last_name']);
        $managers      = User::where('is_active', true)->orderBy('name')->get(['id', 'name']);
        $project       = null;

        return view('projects.create', compact('project', 'opportunities', 'accounts', 'contacts', 'managers'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateProject($request);

        $validated['created_by'] = auth()->id();
        $validated['manager_id'] = $validated['manager_id'] ?? auth()->id();
        $validated['project_number'] = $this->generateProjectNumber();

        $project = Project::create($validated);

        return redirect()->route('projects.show', $project)
            ->with('success', 'Project created successfully.');
    }

    public function show(Project $project): View
    {
        $project->load([
            'opportunity', 'account', 'contact', 'manager', 'createdBy',
            'milestones.createdBy',
            'members',
            'timesheets.user',
        ]);

        $billableHours    = $project->timesheets->where('is_billable', true)->sum('hours');
        $nonBillableHours = $project->timesheets->where('is_billable', false)->sum('hours');
        $totalHours       = $project->timesheets->sum('hours');
        $allUsers         = User::where('is_active', true)->orderBy('name')->get(['id', 'name']);

        return view('projects.show', compact(
            'project', 'billableHours', 'nonBillableHours', 'totalHours', 'allUsers'
        ));
    }

    public function edit(Project $project): View
    {
        $opportunities = Opportunity::orderBy('name')->get(['id', 'name']);
        $accounts      = Account::orderBy('name')->get(['id', 'name']);
        $contacts      = Contact::orderBy('first_name')->get(['id', 'first_name', 'last_name']);
        $managers      = User::where('is_active', true)->orderBy('name')->get(['id', 'name']);

        return view('projects.create', compact('project', 'opportunities', 'accounts', 'contacts', 'managers'));
    }

    public function update(Request $request, Project $project): RedirectResponse
    {
        $validated = $this->validateProject($request);

        if ($validated['status'] === 'completed' && ! $project->completed_at) {
            $validated['completed_at'] = now();
        } elseif ($validated['status'] !== 'completed') {
            $validated['completed_at'] = null;
        }

        $project->update($validated);

        return redirect()->route('projects.show', $project)
            ->with('success', 'Project updated successfully.');
    }

    public function destroy(Project $project): RedirectResponse
    {
        $project->delete();

        return redirect()->route('projects.index')
            ->with('success', 'Project deleted successfully.');
    }

    // ── Milestones ────────────────────────────────────────────────────────────

    public function storeMilestone(Request $request, Project $project): RedirectResponse
    {
        $validated = $request->validate([
            'title'       => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'due_date'    => ['required', 'date'],
            'status'      => ['required', 'string', 'in:pending,in_progress,completed'],
        ]);

        $validated['created_by'] = auth()->id();
        $validated['sort_order'] = $project->milestones()->max('sort_order') + 1;

        if ($validated['status'] === 'completed') {
            $validated['completed_at'] = now();
        }

        $project->milestones()->create($validated);

        return redirect()->route('projects.show', $project)
            ->with('success', 'Milestone added successfully.');
    }

    public function updateMilestone(Request $request, Project $project, ProjectMilestone $milestone): RedirectResponse
    {
        abort_unless($milestone->project_id === $project->id, 404);

        $validated = $request->validate([
            'title'       => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'due_date'    => ['required', 'date'],
            'status'      => ['required', 'string', 'in:pending,in_progress,completed'],
        ]);

        if ($validated['status'] === 'completed' && ! $milestone->completed_at) {
            $validated['completed_at'] = now();
        } elseif ($validated['status'] !== 'completed') {
            $validated['completed_at'] = null;
        }

        $milestone->update($validated);

        return redirect()->route('projects.show', $project)
            ->with('success', 'Milestone updated successfully.');
    }

    public function destroyMilestone(Project $project, ProjectMilestone $milestone): RedirectResponse
    {
        abort_unless($milestone->project_id === $project->id, 404);

        $milestone->delete();

        return redirect()->route('projects.show', $project)
            ->with('success', 'Milestone deleted.');
    }

    // ── Members ───────────────────────────────────────────────────────────────

    public function addMember(Request $request, Project $project): RedirectResponse
    {
        $validated = $request->validate([
            'user_id'         => ['required', 'integer', 'exists:users,id'],
            'role'            => ['nullable', 'string', 'max:100'],
            'allocated_hours' => ['nullable', 'numeric', 'min:0'],
        ]);

        $project->members()->syncWithoutDetaching([
            $validated['user_id'] => [
                'role'            => $validated['role'] ?? null,
                'allocated_hours' => $validated['allocated_hours'] ?? null,
            ],
        ]);

        return redirect()->route('projects.show', $project)
            ->with('success', 'Team member added.');
    }

    public function removeMember(Project $project, User $user): RedirectResponse
    {
        $project->members()->detach($user->id);

        return redirect()->route('projects.show', $project)
            ->with('success', 'Team member removed.');
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function validateProject(Request $request): array
    {
        return $request->validate([
            'name'           => ['required', 'string', 'max:255'],
            'description'    => ['nullable', 'string'],
            'status'         => ['required', 'string', 'in:planning,active,on_hold,completed,cancelled'],
            'opportunity_id' => ['nullable', 'integer', 'exists:opportunities,id'],
            'account_id'     => ['nullable', 'integer', 'exists:accounts,id'],
            'contact_id'     => ['nullable', 'integer', 'exists:contacts,id'],
            'manager_id'     => ['nullable', 'integer', 'exists:users,id'],
            'start_date'     => ['nullable', 'date'],
            'due_date'       => ['nullable', 'date', 'after_or_equal:start_date'],
            'budget'         => ['nullable', 'numeric', 'min:0'],
            'currency'       => ['required', 'string', 'size:3'],
        ]);
    }

    private function generateProjectNumber(): string
    {
        $tenantId = auth()->user()->tenant_id;
        $last = Project::withTrashed()
            ->where('tenant_id', $tenantId)
            ->max('project_number');

        $number = 1;
        if ($last && preg_match('/PRJ-(\d+)/', $last, $m)) {
            $number = (int) $m[1] + 1;
        }

        return 'PRJ-' . str_pad($number, 5, '0', STR_PAD_LEFT);
    }
}
