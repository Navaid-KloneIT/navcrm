<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Workflow;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WorkflowController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $workflows = Workflow::with(['conditions', 'actions'])
            ->when($request->get('trigger'), fn ($q, $t) => $q->where('trigger_event', $t))
            ->latest()
            ->paginate(25);

        return response()->json($workflows);
    }

    public function show(Workflow $workflow): JsonResponse
    {
        $workflow->load(['conditions', 'actions', 'runs' => fn ($q) => $q->limit(10)]);

        return response()->json($workflow);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name'           => ['required', 'string', 'max:255'],
            'description'    => ['nullable', 'string'],
            'is_active'      => ['sometimes', 'boolean'],
            'trigger_event'  => ['required', 'string'],
            'trigger_config' => ['nullable', 'array'],
        ]);

        $workflow = Workflow::create(array_merge($validated, ['created_by' => $request->user()->id]));

        return response()->json($workflow, 201);
    }

    public function update(Request $request, Workflow $workflow): JsonResponse
    {
        $validated = $request->validate([
            'name'           => ['sometimes', 'string', 'max:255'],
            'description'    => ['nullable', 'string'],
            'is_active'      => ['sometimes', 'boolean'],
            'trigger_event'  => ['sometimes', 'string'],
            'trigger_config' => ['nullable', 'array'],
        ]);

        $workflow->update($validated);

        return response()->json($workflow);
    }

    public function destroy(Workflow $workflow): JsonResponse
    {
        $workflow->delete();

        return response()->json(null, 204);
    }
}
