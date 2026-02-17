<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ForecastService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ForecastController extends Controller
{
    public function __construct(
        protected ForecastService $forecastService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $periodStart = $request->input('period_start');
        $periodEnd = $request->input('period_end');

        $targetsVsActual = $this->forecastService->getTargetsVsActual($periodStart, $periodEnd);
        $pipelineByStage = $this->forecastService->getPipelineByStage();

        return response()->json([
            'data' => [
                'targets_vs_actual' => $targetsVsActual,
                'pipeline_by_stage' => $pipelineByStage,
            ],
        ]);
    }

    public function summary(Request $request): JsonResponse
    {
        $periodStart = $request->input('period_start');
        $periodEnd = $request->input('period_end');

        $summary = $this->forecastService->getSummary($periodStart, $periodEnd);

        return response()->json([
            'data' => $summary,
        ]);
    }
}
