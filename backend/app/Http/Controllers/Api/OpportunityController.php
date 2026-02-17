<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Opportunity\StoreOpportunityRequest;
use App\Http\Requests\Opportunity\UpdateOpportunityRequest;
use App\Http\Resources\OpportunityResource;
use App\Models\Opportunity;
use App\Models\PipelineStage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OpportunityController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Opportunity::query();

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhereHas('account', fn ($aq) => $aq->where('name', 'like', "%{$search}%"));
            });
        }

        if ($stageId = $request->input('pipeline_stage_id')) {
            $query->where('pipeline_stage_id', $stageId);
        }

        if ($ownerId = $request->input('owner_id')) {
            $query->where('owner_id', $ownerId);
        }

        if ($accountId = $request->input('account_id')) {
            $query->where('account_id', $accountId);
        }

        if ($request->has('closed')) {
            if ($request->boolean('closed')) {
                $query->where(fn ($q) => $q->whereNotNull('won_at')->orWhereNotNull('lost_at'));
            } else {
                $query->whereNull('won_at')->whereNull('lost_at');
            }
        }

        if ($closeDateFrom = $request->input('close_date_from')) {
            $query->where('close_date', '>=', $closeDateFrom);
        }

        if ($closeDateTo = $request->input('close_date_to')) {
            $query->where('close_date', '<=', $closeDateTo);
        }

        $sortBy = $request->input('sort_by', 'created_at');
        $sortDir = $request->input('sort_dir', 'desc');
        $query->orderBy($sortBy, $sortDir);

        $opportunities = $query->with(['stage', 'account', 'contact', 'owner'])
            ->withCount('quotes')
            ->paginate($request->input('per_page', 15));

        return response()->json(
            OpportunityResource::collection($opportunities)->response()->getData(true)
        );
    }

    public function store(StoreOpportunityRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $validated['tenant_id'] = $request->user()->tenant_id;
        $validated['created_by'] = $request->user()->id;

        $stage = PipelineStage::find($validated['pipeline_stage_id']);
        if ($stage && !isset($validated['probability'])) {
            $validated['probability'] = $stage->probability;
        }

        $opportunity = Opportunity::create($validated);

        if ($stage && $stage->is_won) {
            $opportunity->update(['won_at' => now()]);
        }

        return response()->json([
            'data' => new OpportunityResource($opportunity->load(['stage', 'account', 'contact', 'owner'])),
        ], 201);
    }

    public function show(Opportunity $opportunity): JsonResponse
    {
        $opportunity->load([
            'stage',
            'account',
            'contact',
            'owner',
            'teamMembers',
            'tags',
            'quotes' => fn ($q) => $q->latest()->limit(10),
            'activities' => fn ($q) => $q->latest()->limit(10),
            'notes',
        ])->loadCount('quotes');

        return response()->json([
            'data' => new OpportunityResource($opportunity),
        ]);
    }

    public function update(UpdateOpportunityRequest $request, Opportunity $opportunity): JsonResponse
    {
        $validated = $request->validated();

        if (isset($validated['pipeline_stage_id'])) {
            $stage = PipelineStage::find($validated['pipeline_stage_id']);
            if ($stage) {
                if ($stage->is_won && !$opportunity->won_at) {
                    $validated['won_at'] = now();
                    $validated['lost_at'] = null;
                    $validated['lost_reason'] = null;
                } elseif ($stage->is_lost && !$opportunity->lost_at) {
                    $validated['lost_at'] = now();
                    $validated['won_at'] = null;
                } elseif (!$stage->is_won && !$stage->is_lost) {
                    $validated['won_at'] = null;
                    $validated['lost_at'] = null;
                    $validated['lost_reason'] = null;
                }
            }
        }

        $opportunity->update($validated);

        return response()->json([
            'data' => new OpportunityResource(
                $opportunity->fresh()->load(['stage', 'account', 'contact', 'owner', 'teamMembers'])
            ),
        ]);
    }

    public function destroy(Opportunity $opportunity): JsonResponse
    {
        $opportunity->delete();

        return response()->json(null, 204);
    }

    public function updateStage(Request $request, Opportunity $opportunity): JsonResponse
    {
        $validated = $request->validate([
            'pipeline_stage_id' => ['required', 'exists:pipeline_stages,id'],
        ]);

        $stage = PipelineStage::find($validated['pipeline_stage_id']);
        $updateData = [
            'pipeline_stage_id' => $validated['pipeline_stage_id'],
            'probability' => $stage->probability,
        ];

        if ($stage->is_won) {
            $updateData['won_at'] = now();
            $updateData['lost_at'] = null;
            $updateData['lost_reason'] = null;
        } elseif ($stage->is_lost) {
            $updateData['lost_at'] = now();
            $updateData['won_at'] = null;
        } else {
            $updateData['won_at'] = null;
            $updateData['lost_at'] = null;
            $updateData['lost_reason'] = null;
        }

        $opportunity->update($updateData);

        return response()->json([
            'data' => new OpportunityResource(
                $opportunity->fresh()->load(['stage', 'account', 'contact', 'owner'])
            ),
        ]);
    }

    public function team(Opportunity $opportunity): JsonResponse
    {
        $opportunity->load('teamMembers');

        return response()->json([
            'data' => $opportunity->teamMembers->map(fn ($user) => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->pivot->role,
                'split_percentage' => $user->pivot->split_percentage,
            ]),
        ]);
    }

    public function addTeamMember(Request $request, Opportunity $opportunity): JsonResponse
    {
        $validated = $request->validate([
            'user_id' => ['required', 'exists:users,id'],
            'role' => ['nullable', 'string', 'in:owner,support,technical'],
            'split_percentage' => ['nullable', 'numeric', 'min:0', 'max:100'],
        ]);

        if ($opportunity->teamMembers()->where('user_id', $validated['user_id'])->exists()) {
            return response()->json(['message' => 'User is already on the team.'], 422);
        }

        $opportunity->teamMembers()->attach($validated['user_id'], [
            'role' => $validated['role'] ?? 'support',
            'split_percentage' => $validated['split_percentage'] ?? 0,
        ]);

        return response()->json([
            'data' => $opportunity->fresh()->teamMembers->map(fn ($user) => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->pivot->role,
                'split_percentage' => $user->pivot->split_percentage,
            ]),
        ], 201);
    }

    public function updateTeamMember(Request $request, Opportunity $opportunity, int $userId): JsonResponse
    {
        $validated = $request->validate([
            'role' => ['nullable', 'string', 'in:owner,support,technical'],
            'split_percentage' => ['nullable', 'numeric', 'min:0', 'max:100'],
        ]);

        $opportunity->teamMembers()->updateExistingPivot($userId, $validated);

        return response()->json([
            'data' => $opportunity->fresh()->teamMembers->map(fn ($user) => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->pivot->role,
                'split_percentage' => $user->pivot->split_percentage,
            ]),
        ]);
    }

    public function removeTeamMember(Opportunity $opportunity, int $userId): JsonResponse
    {
        $opportunity->teamMembers()->detach($userId);

        return response()->json(null, 204);
    }
}
