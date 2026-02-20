<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Timesheet;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TimesheetWebController extends Controller
{
    public function index(Request $request): View
    {
        $query = Timesheet::with(['project', 'user']);

        $query->filterDateRange($request->get('date_from'), $request->get('date_to'), 'date');

        if ($projectId = $request->get('project_id')) {
            $query->where('project_id', $projectId);
        }

        if ($userId = $request->get('user_id')) {
            $query->where('user_id', $userId);
        }

        if ($request->filled('is_billable')) {
            $query->where('is_billable', $request->boolean('is_billable'));
        }

        $timesheets = $query->orderBy('date', 'desc')->paginate(25)->withQueryString();

        // Summary stats
        $statsQuery = Timesheet::query();
        if ($projectId = $request->get('project_id')) {
            $statsQuery->where('project_id', $projectId);
        }
        if ($userId = $request->get('user_id')) {
            $statsQuery->where('user_id', $userId);
        }
        $stats = [
            'total_hours'        => (float) $statsQuery->sum('hours'),
            'billable_hours'     => (float) $statsQuery->where('is_billable', true)->sum('hours'),
            'non_billable_hours' => (float) $statsQuery->where('is_billable', false)->sum('hours'),
        ];

        $projects = Project::orderBy('name')->get(['id', 'name', 'project_number']);
        $users    = User::where('is_active', true)->orderBy('name')->get(['id', 'name']);

        return view('timesheets.index', compact('timesheets', 'stats', 'projects', 'users'));
    }

    public function workload(Request $request): View
    {
        $month     = $request->get('month', now()->format('Y-m'));
        $startDate = \Carbon\Carbon::parse($month . '-01')->startOfMonth();
        $endDate   = $startDate->copy()->endOfMonth();

        // All active users
        $users = User::where('is_active', true)->orderBy('name')->get();

        // Hours logged per user this month
        $loggedHours = Timesheet::whereBetween('date', [$startDate, $endDate])
            ->selectRaw('user_id, SUM(hours) as total_hours')
            ->groupBy('user_id')
            ->pluck('total_hours', 'user_id');

        return view('timesheets.workload', compact('users', 'loggedHours', 'month', 'startDate', 'endDate'));
    }

    public function create(Request $request): View
    {
        $projects  = Project::whereNotIn('status', ['completed', 'cancelled'])
            ->orderBy('name')->get(['id', 'name', 'project_number']);
        $users     = User::where('is_active', true)->orderBy('name')->get(['id', 'name']);
        $timesheet = null;
        $preselect = $request->get('project_id');

        return view('timesheets.create', compact('timesheet', 'projects', 'users', 'preselect'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateTimesheet($request);
        $validated['created_by'] = auth()->id();

        $timesheet = Timesheet::create($validated);

        return redirect()->route('timesheets.show', $timesheet)
            ->with('success', 'Time entry logged successfully.');
    }

    public function show(Timesheet $timesheet): View
    {
        $timesheet->load(['project', 'user', 'createdBy']);

        return view('timesheets.show', compact('timesheet'));
    }

    public function edit(Timesheet $timesheet): View
    {
        $projects = Project::orderBy('name')->get(['id', 'name', 'project_number']);
        $users    = User::where('is_active', true)->orderBy('name')->get(['id', 'name']);

        return view('timesheets.create', compact('timesheet', 'projects', 'users'));
    }

    public function update(Request $request, Timesheet $timesheet): RedirectResponse
    {
        $validated = $this->validateTimesheet($request);
        $timesheet->update($validated);

        return redirect()->route('timesheets.show', $timesheet)
            ->with('success', 'Time entry updated successfully.');
    }

    public function destroy(Timesheet $timesheet): RedirectResponse
    {
        $timesheet->delete();

        return redirect()->route('timesheets.index')
            ->with('success', 'Time entry deleted.');
    }

    private function validateTimesheet(Request $request): array
    {
        return $request->validate([
            'project_id'    => ['required', 'integer', 'exists:projects,id'],
            'user_id'       => ['required', 'integer', 'exists:users,id'],
            'date'          => ['required', 'date'],
            'hours'         => ['required', 'numeric', 'min:0.25', 'max:24'],
            'description'   => ['nullable', 'string'],
            'is_billable'   => ['boolean'],
            'billable_rate' => ['nullable', 'numeric', 'min:0'],
        ]);
    }
}
