<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PipelineStageResource;
use App\Models\PipelineStage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PipelineStageController extends Controller
{
    public function index(): JsonResponse
    {
        $stages = PipelineStage::query()
            ->withCount('opportunities')
            ->orderBy('position')
            ->get();

        return response()->json([
            'data' => PipelineStageResource::collection($stages),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'probability' => ['nullable', 'integer', 'min:0', 'max:100'],
            'is_won' => ['nullable', 'boolean'],
            'is_lost' => ['nullable', 'boolean'],
            'color' => ['nullable', 'string', 'max:7'],
        ]);

        $maxPosition = PipelineStage::max('position') ?? 0;
        $validated['position'] = $maxPosition + 1;
        $validated['tenant_id'] = $request->user()->tenant_id;

        $stage = PipelineStage::create($validated);

        return response()->json([
            'data' => new PipelineStageResource($stage),
        ], 201);
    }

    public function show(PipelineStage $pipelineStage): JsonResponse
    {
        $pipelineStage->loadCount('opportunities');

        return response()->json([
            'data' => new PipelineStageResource($pipelineStage),
        ]);
    }

    public function update(Request $request, PipelineStage $pipelineStage): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'probability' => ['nullable', 'integer', 'min:0', 'max:100'],
            'is_won' => ['nullable', 'boolean'],
            'is_lost' => ['nullable', 'boolean'],
            'color' => ['nullable', 'string', 'max:7'],
        ]);

        $pipelineStage->update($validated);

        return response()->json([
            'data' => new PipelineStageResource($pipelineStage->fresh()),
        ]);
    }

    public function destroy(PipelineStage $pipelineStage): JsonResponse
    {
        if ($pipelineStage->opportunities()->exists()) {
            return response()->json([
                'message' => 'Cannot delete a stage that has opportunities. Move them first.',
            ], 422);
        }

        $pipelineStage->delete();

        return response()->json(null, 204);
    }

    public function reorder(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'stages' => ['required', 'array'],
            'stages.*.id' => ['required', 'exists:pipeline_stages,id'],
            'stages.*.position' => ['required', 'integer', 'min:0'],
        ]);

        foreach ($validated['stages'] as $stageData) {
            PipelineStage::where('id', $stageData['id'])
                ->update(['position' => $stageData['position']]);
        }

        $stages = PipelineStage::query()
            ->withCount('opportunities')
            ->orderBy('position')
            ->get();

        return response()->json([
            'data' => PipelineStageResource::collection($stages),
        ]);
    }
}
