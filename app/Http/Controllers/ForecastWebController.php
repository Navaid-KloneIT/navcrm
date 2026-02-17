<?php

namespace App\Http\Controllers;

use App\Models\Opportunity;
use App\Models\SalesTarget;
use App\Services\ForecastService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ForecastWebController extends Controller
{
    public function index(Request $request): View
    {
        $service = app(ForecastService::class);

        $periodStart = $request->get('period_start', Carbon::now()->startOfQuarter()->toDateString());
        $periodEnd   = $request->get('period_end',   Carbon::now()->endOfQuarter()->toDateString());

        $summary     = $service->getSummary($periodStart, $periodEnd);
        $byStage     = $service->getPipelineByStage();
        $targetsData = $service->getTargetsVsActual($periodStart, $periodEnd);

        $targets = SalesTarget::with('user')
            ->where('period_start', '>=', $periodStart)
            ->where('period_end', '<=', $periodEnd)
            ->latest()
            ->get();

        $openPipeline = Opportunity::with(['stage', 'account', 'owner'])
            ->whereNull('won_at')
            ->whereNull('lost_at')
            ->latest()
            ->get();

        return view('forecasts.index', compact(
            'summary', 'byStage', 'targetsData',
            'targets', 'openPipeline',
            'periodStart', 'periodEnd'
        ));
    }
}
