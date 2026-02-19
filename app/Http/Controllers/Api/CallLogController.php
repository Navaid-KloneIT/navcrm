<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CallLog\StoreCallLogRequest;
use App\Http\Requests\CallLog\UpdateCallLogRequest;
use App\Models\CallLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CallLogController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = CallLog::with(['user', 'loggable']);

        if ($direction = $request->get('direction')) {
            $query->where('direction', $direction);
        }

        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }

        if ($userId = $request->get('user_id')) {
            $query->where('user_id', $userId);
        }

        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('phone_number', 'like', "%{$search}%")
                  ->orWhere('notes', 'like', "%{$search}%");
            });
        }

        $calls = $query->orderBy('called_at', 'desc')->paginate(25);

        return response()->json($calls);
    }

    public function store(StoreCallLogRequest $request): JsonResponse
    {
        $call = CallLog::create([
            ...$request->validated(),
            'tenant_id' => auth()->user()->tenant_id,
            'user_id'   => auth()->id(),
        ]);

        return response()->json($call->load(['user']), 201);
    }

    public function show(CallLog $callLog): JsonResponse
    {
        return response()->json($callLog->load(['user', 'loggable']));
    }

    public function update(UpdateCallLogRequest $request, CallLog $callLog): JsonResponse
    {
        $callLog->update($request->validated());

        return response()->json($callLog->fresh(['user']));
    }

    public function destroy(CallLog $callLog): JsonResponse
    {
        $callLog->delete();

        return response()->json(null, 204);
    }
}
