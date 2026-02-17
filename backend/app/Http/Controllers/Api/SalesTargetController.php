<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\SalesTarget\StoreSalesTargetRequest;
use App\Http\Resources\SalesTargetResource;
use App\Models\SalesTarget;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SalesTargetController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = SalesTarget::query()->with('user');

        if ($userId = $request->input('user_id')) {
            $query->where('user_id', $userId);
        }

        if ($periodType = $request->input('period_type')) {
            $query->where('period_type', $periodType);
        }

        if ($periodStart = $request->input('period_start')) {
            $query->where('period_start', '>=', $periodStart);
        }

        if ($periodEnd = $request->input('period_end')) {
            $query->where('period_end', '<=', $periodEnd);
        }

        $targets = $query->orderByDesc('period_start')
            ->paginate($request->input('per_page', 15));

        return response()->json(
            SalesTargetResource::collection($targets)->response()->getData(true)
        );
    }

    public function store(StoreSalesTargetRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $validated['tenant_id'] = $request->user()->tenant_id;

        $target = SalesTarget::create($validated);
        $target->load('user');

        return response()->json([
            'data' => new SalesTargetResource($target),
        ], 201);
    }

    public function show(SalesTarget $salesTarget): JsonResponse
    {
        $salesTarget->load('user');

        return response()->json([
            'data' => new SalesTargetResource($salesTarget),
        ]);
    }

    public function update(Request $request, SalesTarget $salesTarget): JsonResponse
    {
        $validated = $request->validate([
            'user_id' => ['nullable', 'exists:users,id'],
            'period_type' => ['sometimes', 'in:monthly,quarterly,yearly'],
            'period_start' => ['sometimes', 'date'],
            'period_end' => ['sometimes', 'date', 'after:period_start'],
            'target_amount' => ['sometimes', 'numeric', 'min:0'],
            'currency' => ['nullable', 'string', 'size:3'],
            'category' => ['nullable', 'string', 'max:255'],
        ]);

        $salesTarget->update($validated);
        $salesTarget->load('user');

        return response()->json([
            'data' => new SalesTargetResource($salesTarget),
        ]);
    }

    public function destroy(SalesTarget $salesTarget): JsonResponse
    {
        $salesTarget->delete();

        return response()->json(null, 204);
    }
}
