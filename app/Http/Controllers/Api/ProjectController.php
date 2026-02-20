<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Project;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Project::with(['account', 'manager', 'milestones']);

        $query->search($request->get('search'), ['name', 'project_number']);
        $query->filterDateRange($request->get('due_from'), $request->get('due_to'), 'due_date');

        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }

        if ($managerId = $request->get('manager_id')) {
            $query->where('manager_id', $managerId);
        }

        return response()->json($query->latest()->paginate(25)->withQueryString());
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name'           => ['required', 'string', 'max:255'],
            'description'    => ['nullable', 'string'],
            'status'         => ['required', 'string', 'in:planning,active,on_hold,completed,cancelled'],
            'opportunity_id' => ['nullable', 'integer', 'exists:opportunities,id'],
            'account_id'     => ['nullable', 'integer', 'exists:accounts,id'],
            'contact_id'     => ['nullable', 'integer', 'exists:contacts,id'],
            'manager_id'     => ['nullable', 'integer', 'exists:users,id'],
            'start_date'     => ['nullable', 'date'],
            'due_date'       => ['nullable', 'date'],
            'budget'         => ['nullable', 'numeric', 'min:0'],
            'currency'       => ['nullable', 'string', 'size:3'],
        ]);

        $validated['created_by']     = auth()->id();
        $validated['manager_id']     = $validated['manager_id'] ?? auth()->id();
        $validated['project_number'] = $this->generateProjectNumber();

        $project = Project::create($validated);

        return response()->json($project->load(['account', 'manager']), 201);
    }

    public function show(Project $project): JsonResponse
    {
        return response()->json($project->load([
            'opportunity', 'account', 'contact', 'manager', 'milestones', 'members', 'timesheets.user',
        ]));
    }

    public function update(Request $request, Project $project): JsonResponse
    {
        $validated = $request->validate([
            'name'           => ['sometimes', 'string', 'max:255'],
            'description'    => ['nullable', 'string'],
            'status'         => ['sometimes', 'string', 'in:planning,active,on_hold,completed,cancelled'],
            'opportunity_id' => ['nullable', 'integer', 'exists:opportunities,id'],
            'account_id'     => ['nullable', 'integer', 'exists:accounts,id'],
            'contact_id'     => ['nullable', 'integer', 'exists:contacts,id'],
            'manager_id'     => ['nullable', 'integer', 'exists:users,id'],
            'start_date'     => ['nullable', 'date'],
            'due_date'       => ['nullable', 'date'],
            'budget'         => ['nullable', 'numeric', 'min:0'],
            'currency'       => ['nullable', 'string', 'size:3'],
        ]);

        if (isset($validated['status']) && $validated['status'] === 'completed' && ! $project->completed_at) {
            $validated['completed_at'] = now();
        }

        $project->update($validated);

        return response()->json($project->fresh());
    }

    public function destroy(Project $project): JsonResponse
    {
        $project->delete();

        return response()->json(null, 204);
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
