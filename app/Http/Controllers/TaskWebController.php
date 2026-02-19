<?php

namespace App\Http\Controllers;

use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use App\Http\Requests\Task\StoreTaskRequest;
use App\Http\Requests\Task\UpdateTaskRequest;
use App\Models\Account;
use App\Models\Contact;
use App\Models\Opportunity;
use App\Models\Task;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TaskWebController extends Controller
{
    public function index(Request $request): View
    {
        $query = Task::with(['assignee', 'taskable']);

        if ($search = $request->get('search')) {
            $query->where('title', 'like', "%{$search}%");
        }

        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }

        if ($priority = $request->get('priority')) {
            $query->where('priority', $priority);
        }

        if ($assignedTo = $request->get('assigned_to')) {
            $query->where('assigned_to', $assignedTo);
        }

        if ($due = $request->get('due')) {
            match($due) {
                'today'    => $query->whereDate('due_date', today()),
                'overdue'  => $query->whereDate('due_date', '<', today())
                                    ->whereNotIn('status', [TaskStatus::Completed->value, TaskStatus::Cancelled->value]),
                'upcoming' => $query->whereDate('due_date', '>', today()),
                default    => null,
            };
        }

        $tasks = $query->orderBy('due_date')->orderBy('priority', 'desc')->paginate(25)->withQueryString();

        $statusCounts = Task::selectRaw('status, count(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status');

        $users = User::orderBy('name')->get(['id', 'name']);

        return view('activity.tasks.index', compact('tasks', 'statusCounts', 'users'));
    }

    public function create(): View
    {
        $task         = null;
        $contacts     = Contact::orderBy('first_name')->get(['id', 'first_name', 'last_name']);
        $accounts     = Account::orderBy('name')->get(['id', 'name']);
        $opportunities = Opportunity::orderBy('name')->get(['id', 'name']);
        $users        = User::orderBy('name')->get(['id', 'name']);

        return view('activity.tasks.create', compact('task', 'contacts', 'accounts', 'opportunities', 'users'));
    }

    public function store(StoreTaskRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $task = Task::create([
            ...$validated,
            'tenant_id'  => auth()->user()->tenant_id,
            'created_by' => auth()->id(),
        ]);

        return redirect()->route('activity.tasks.show', $task)
            ->with('success', "Task \"{$task->title}\" created successfully.");
    }

    public function show(Task $task): View
    {
        $task->load(['assignee', 'creator', 'taskable']);
        $users = User::orderBy('name')->get(['id', 'name']);

        return view('activity.tasks.show', compact('task', 'users'));
    }

    public function edit(Task $task): View
    {
        $contacts     = Contact::orderBy('first_name')->get(['id', 'first_name', 'last_name']);
        $accounts     = Account::orderBy('name')->get(['id', 'name']);
        $opportunities = Opportunity::orderBy('name')->get(['id', 'name']);
        $users        = User::orderBy('name')->get(['id', 'name']);

        return view('activity.tasks.create', compact('task', 'contacts', 'accounts', 'opportunities', 'users'));
    }

    public function update(UpdateTaskRequest $request, Task $task): RedirectResponse
    {
        $validated = $request->validated();

        // Record completed_at timestamp when status changes to completed
        if (isset($validated['status'])) {
            $newStatus = TaskStatus::from($validated['status']);
            if ($newStatus === TaskStatus::Completed && ! $task->completed_at) {
                $validated['completed_at'] = now();
            } elseif ($newStatus !== TaskStatus::Completed) {
                $validated['completed_at'] = null;
            }
        }

        $task->update($validated);

        return redirect()->route('activity.tasks.show', $task)
            ->with('success', 'Task updated successfully.');
    }

    public function destroy(Task $task): RedirectResponse
    {
        $task->delete();

        return redirect()->route('activity.tasks.index')
            ->with('success', 'Task deleted successfully.');
    }
}
