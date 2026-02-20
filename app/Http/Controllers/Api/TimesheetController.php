<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Timesheet;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TimesheetController extends Controller
{
    public function index(Request $request): JsonResponse
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

        return response()->json($query->orderBy('date', 'desc')->paginate(25)->withQueryString());
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'project_id'    => ['required', 'integer', 'exists:projects,id'],
            'user_id'       => ['nullable', 'integer', 'exists:users,id'],
            'date'          => ['required', 'date'],
            'hours'         => ['required', 'numeric', 'min:0.25', 'max:24'],
            'description'   => ['nullable', 'string'],
            'is_billable'   => ['boolean'],
            'billable_rate' => ['nullable', 'numeric', 'min:0'],
        ]);

        $validated['user_id']    = $validated['user_id'] ?? auth()->id();
        $validated['created_by'] = auth()->id();

        $timesheet = Timesheet::create($validated);

        return response()->json($timesheet->load(['project', 'user']), 201);
    }

    public function show(Timesheet $timesheet): JsonResponse
    {
        return response()->json($timesheet->load(['project', 'user', 'createdBy']));
    }

    public function update(Request $request, Timesheet $timesheet): JsonResponse
    {
        $validated = $request->validate([
            'project_id'    => ['sometimes', 'integer', 'exists:projects,id'],
            'user_id'       => ['nullable', 'integer', 'exists:users,id'],
            'date'          => ['sometimes', 'date'],
            'hours'         => ['sometimes', 'numeric', 'min:0.25', 'max:24'],
            'description'   => ['nullable', 'string'],
            'is_billable'   => ['boolean'],
            'billable_rate' => ['nullable', 'numeric', 'min:0'],
        ]);

        $timesheet->update($validated);

        return response()->json($timesheet->fresh());
    }

    public function destroy(Timesheet $timesheet): JsonResponse
    {
        $timesheet->delete();

        return response()->json(null, 204);
    }
}
