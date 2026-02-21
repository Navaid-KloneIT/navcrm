<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\HealthScore;
use App\Services\HealthScoreService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class HealthScoreController extends Controller
{
    public function __construct(
        private HealthScoreService $healthScoreService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $accounts = Account::with('latestHealthScore')
            ->orderBy('name')
            ->paginate($request->get('per_page', 25));

        return response()->json($accounts);
    }

    public function show(Account $account): JsonResponse
    {
        $history = HealthScore::where('account_id', $account->id)
            ->orderByDesc('calculated_at')
            ->limit(20)
            ->get();

        return response()->json([
            'account' => $account->load('latestHealthScore'),
            'history' => $history,
        ]);
    }

    public function recalculate(Account $account): JsonResponse
    {
        $score = $this->healthScoreService->calculateForAccount($account);

        return response()->json($score, 201);
    }
}
