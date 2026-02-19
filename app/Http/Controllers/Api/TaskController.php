<?php

namespace App\Http\Controllers\Api;

use App\Enums\TaskStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Task\StoreTaskRequest;
use App\Http\Requests\Task\UpdateTaskRequest;
use App\Models\Task;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Task::with(['assignee', 'taskable']);

        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }

        if ($priority = $request->get('priority')) {
            $query->where('priority', $priority);
        }

        if ($assignedTo = $request->get('assigned_to')) {
            $query->where('assigned_to', $assignedTo);
        }

        if ($search = $request->get('search')) {
            $query->where('title', 'like', "%{$search}%");
        }

        $tasks = $query->orderBy('due_date')->paginate(25);

        return response()->json($tasks);
    }

    public function store(StoreTaskRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $task = Task::create([
            ...$validated,
            'tenant_id'  => auth()->user()->tenant_id,
            'created_by' => auth()->id(),
        ]);

        return response()->json($task->load(['assignee', 'creator']), 201);
    }

    public function show(Task $task): JsonResponse
    {
        return response()->json($task->load(['assignee', 'creator', 'taskable']));
    }

    public function update(UpdateTaskRequest $request, Task $task): JsonResponse
    {
        $validated = $request->validated();

        if (isset($validated['status'])) {
            $newStatus = TaskStatus::from($validated['status']);
            if ($newStatus === TaskStatus::Completed && ! $task->completed_at) {
                $validated['completed_at'] = now();
            } elseif ($newStatus !== TaskStatus::Completed) {
                $validated['completed_at'] = null;
            }
        }

        $task->update($validated);

        return response()->json($task->fresh(['assignee', 'creator']));
    }

    public function destroy(Task $task): JsonResponse
    {
        $task->delete();

        return response()->json(null, 204);
    }
}
