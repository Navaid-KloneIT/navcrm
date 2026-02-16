<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ActivityResource;
use App\Models\Activity;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ActivityController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Activity::query();

        if ($activitableType = $request->input('activitable_type')) {
            $query->where('activitable_type', $activitableType);
        }

        if ($activitableId = $request->input('activitable_id')) {
            $query->where('activitable_id', $activitableId);
        }

        $activities = $query->with('user')
            ->orderBy('created_at', 'desc')
            ->paginate($request->input('per_page', 15));

        return response()->json(ActivityResource::collection($activities)->response()->getData(true));
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'type' => ['required', 'string', 'max:255'],
            'subject' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'activitable_type' => ['required', 'string', 'max:255'],
            'activitable_id' => ['required', 'integer'],
            'occurred_at' => ['nullable', 'date'],
            'metadata' => ['nullable', 'array'],
        ]);

        $validated['user_id'] = $request->user()->id;
        $validated['tenant_id'] = $request->user()->tenant_id;

        $activity = Activity::create($validated);

        return response()->json([
            'activity' => new ActivityResource($activity->load('user')),
        ], 201);
    }

    public function destroy(Activity $activity): JsonResponse
    {
        $activity->delete();

        return response()->json(null, 204);
    }
}
