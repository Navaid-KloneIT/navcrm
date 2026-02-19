<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\EmailLog\StoreEmailLogRequest;
use App\Http\Requests\EmailLog\UpdateEmailLogRequest;
use App\Models\EmailLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EmailLogController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = EmailLog::with(['user', 'emailable']);

        if ($direction = $request->get('direction')) {
            $query->where('direction', $direction);
        }

        if ($source = $request->get('source')) {
            $query->where('source', $source);
        }

        if ($userId = $request->get('user_id')) {
            $query->where('user_id', $userId);
        }

        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('subject', 'like', "%{$search}%")
                  ->orWhere('from_email', 'like', "%{$search}%")
                  ->orWhere('to_email', 'like', "%{$search}%");
            });
        }

        $emails = $query->orderBy('sent_at', 'desc')->orderBy('created_at', 'desc')->paginate(25);

        return response()->json($emails);
    }

    public function store(StoreEmailLogRequest $request): JsonResponse
    {
        $email = EmailLog::create([
            ...$request->validated(),
            'tenant_id' => auth()->user()->tenant_id,
            'user_id'   => auth()->id(),
        ]);

        return response()->json($email->load(['user']), 201);
    }

    public function show(EmailLog $emailLog): JsonResponse
    {
        return response()->json($emailLog->load(['user', 'emailable']));
    }

    public function update(UpdateEmailLogRequest $request, EmailLog $emailLog): JsonResponse
    {
        $emailLog->update($request->validated());

        return response()->json($emailLog->fresh(['user']));
    }

    public function destroy(EmailLog $emailLog): JsonResponse
    {
        $emailLog->delete();

        return response()->json(null, 204);
    }
}
