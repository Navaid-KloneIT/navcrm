<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\OnboardingPipeline;
use App\Models\OnboardingStep;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OnboardingPipelineController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = OnboardingPipeline::with(['account', 'assignee', 'steps']);

        $query->search($request->get('search'), ['name', 'pipeline_number']);

        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }
        if ($accountId = $request->get('account_id')) {
            $query->where('account_id', $accountId);
        }

        $pipelines = $query->latest()->paginate($request->get('per_page', 25));

        return response()->json($pipelines);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name'        => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'status'      => ['required', 'string', 'in:not_started,in_progress,completed,cancelled'],
            'account_id'  => ['required', 'integer', 'exists:accounts,id'],
            'contact_id'  => ['nullable', 'integer', 'exists:contacts,id'],
            'assigned_to' => ['nullable', 'integer', 'exists:users,id'],
            'due_date'    => ['nullable', 'date'],
            'steps'       => ['nullable', 'array'],
            'steps.*.title'       => ['required_with:steps', 'string', 'max:255'],
            'steps.*.description' => ['nullable', 'string'],
            'steps.*.due_date'    => ['nullable', 'date'],
        ]);

        $validated['created_by']      = auth()->id();
        $validated['assigned_to']     = $validated['assigned_to'] ?? auth()->id();
        $validated['pipeline_number'] = $this->generatePipelineNumber();

        $steps = $validated['steps'] ?? [];
        unset($validated['steps']);

        $pipeline = OnboardingPipeline::create($validated);

        foreach ($steps as $i => $stepData) {
            $pipeline->steps()->create([
                'title'       => $stepData['title'],
                'description' => $stepData['description'] ?? null,
                'due_date'    => $stepData['due_date'] ?? null,
                'sort_order'  => $i,
            ]);
        }

        return response()->json($pipeline->load(['account', 'assignee', 'steps']), 201);
    }

    public function show(OnboardingPipeline $onboardingPipeline): JsonResponse
    {
        return response()->json(
            $onboardingPipeline->load(['account', 'contact', 'assignee', 'creator', 'steps.completedByUser'])
        );
    }

    public function update(Request $request, OnboardingPipeline $onboardingPipeline): JsonResponse
    {
        $validated = $request->validate([
            'name'        => ['sometimes', 'required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'status'      => ['sometimes', 'required', 'string', 'in:not_started,in_progress,completed,cancelled'],
            'account_id'  => ['sometimes', 'required', 'integer', 'exists:accounts,id'],
            'contact_id'  => ['nullable', 'integer', 'exists:contacts,id'],
            'assigned_to' => ['nullable', 'integer', 'exists:users,id'],
            'due_date'    => ['nullable', 'date'],
        ]);

        $onboardingPipeline->update($validated);

        return response()->json($onboardingPipeline->fresh(['account', 'assignee', 'steps']));
    }

    public function destroy(OnboardingPipeline $onboardingPipeline): JsonResponse
    {
        $onboardingPipeline->delete();

        return response()->json(null, 204);
    }

    public function toggleStep(OnboardingPipeline $onboardingPipeline, OnboardingStep $step): JsonResponse
    {
        abort_unless($step->onboarding_pipeline_id === $onboardingPipeline->id, 404);

        $step->update([
            'is_completed' => ! $step->is_completed,
            'completed_at' => ! $step->is_completed ? now() : null,
            'completed_by' => ! $step->is_completed ? auth()->id() : null,
        ]);

        return response()->json($step->fresh('completedByUser'));
    }

    private function generatePipelineNumber(): string
    {
        $tenantId = auth()->user()->tenant_id;
        $last = OnboardingPipeline::withTrashed()
            ->where('tenant_id', $tenantId)
            ->max('pipeline_number');

        $number = 1;
        if ($last && preg_match('/OB-(\d+)/', $last, $m)) {
            $number = (int) $m[1] + 1;
        }

        return 'OB-' . str_pad($number, 5, '0', STR_PAD_LEFT);
    }
}
