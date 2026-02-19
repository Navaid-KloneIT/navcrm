<?php

namespace App\Http\Controllers;

use App\Services\AnalyticsService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AnalyticsReportWebController extends Controller
{
    private function defaultRange(): array
    {
        return [
            Carbon::now()->subDays(29)->toDateString(),
            Carbon::now()->toDateString(),
        ];
    }

    public function salesActivity(Request $request): View
    {
        [$defaultFrom, $defaultTo] = $this->defaultRange();

        $from    = $request->get('from', $defaultFrom);
        $to      = $request->get('to', $defaultTo);
        $service = app(AnalyticsService::class);
        $data    = $service->getSalesActivityData($from, $to);

        return view('analytics.reports.sales-activity', compact('data', 'from', 'to'));
    }

    public function salesPerformance(Request $request): View
    {
        [$defaultFrom, $defaultTo] = $this->defaultRange();

        $from    = $request->get('from', $defaultFrom);
        $to      = $request->get('to', $defaultTo);
        $service = app(AnalyticsService::class);
        $data    = $service->getSalesPerformanceData($from, $to);

        return view('analytics.reports.sales-performance', compact('data', 'from', 'to'));
    }

    public function funnelAnalysis(Request $request): View
    {
        $service = app(AnalyticsService::class);
        $data    = $service->getFunnelData();

        return view('analytics.reports.funnel', compact('data'));
    }

    public function serviceReport(Request $request): View
    {
        [$defaultFrom, $defaultTo] = $this->defaultRange();

        $from    = $request->get('from', $defaultFrom);
        $to      = $request->get('to', $defaultTo);
        $service = app(AnalyticsService::class);
        $data    = $service->getServiceReportData($from, $to);

        return view('analytics.reports.service', compact('data', 'from', 'to'));
    }
}
