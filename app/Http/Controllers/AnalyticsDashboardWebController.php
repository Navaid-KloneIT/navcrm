<?php

namespace App\Http\Controllers;

use App\Models\DashboardWidget;
use App\Services\AnalyticsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AnalyticsDashboardWebController extends Controller
{
    public function index(): View
    {
        $user    = auth()->user();
        $service = app(AnalyticsService::class);

        // Create defaults via firstOrCreate (race-safe) if this user has no widgets
        $widgetCount = DashboardWidget::where('user_id', $user->id)->count();
        if ($widgetCount === 0) {
            foreach (DashboardWidget::defaultWidgets() as $def) {
                DashboardWidget::firstOrCreate(
                    ['user_id' => $user->id, 'widget_type' => $def['type']],
                    [
                        'tenant_id'  => $user->tenant_id,
                        'position'   => $def['position'],
                        'is_visible' => true,
                    ]
                );
            }
        }

        // Load all widgets ordered by position, keyed by widget_type
        $widgets = DashboardWidget::where('user_id', $user->id)
            ->orderBy('position')
            ->get()
            ->keyBy('widget_type');

        // Collect visible widget types
        $visible = $widgets->where('is_visible', true)->pluck('widget_type');

        // Fetch data only for visible widgets
        $widgetData = [];

        $kpiTypes = [
            DashboardWidget::KPI_REVENUE,
            DashboardWidget::KPI_LEADS,
            DashboardWidget::KPI_OPEN_OPPS,
            DashboardWidget::KPI_OPEN_TICKETS,
            DashboardWidget::KPI_WIN_RATE,
        ];
        if ($visible->intersect($kpiTypes)->isNotEmpty()) {
            $widgetData['kpi'] = $service->getKpiData();
        }

        if ($visible->contains(DashboardWidget::CHART_MONTHLY_REVENUE)) {
            $widgetData['monthly_revenue'] = $service->getMonthlyRevenue(12);
        }
        if ($visible->contains(DashboardWidget::CHART_PIPELINE_STAGES)) {
            $widgetData['pipeline_stages'] = $service->getPipelineByStage();
        }
        if ($visible->contains(DashboardWidget::CHART_LEADS_SOURCE)) {
            $widgetData['leads_source'] = $service->getLeadsBySource();
        }
        if ($visible->contains(DashboardWidget::CHART_TICKETS_STATUS)) {
            $widgetData['tickets_status'] = $service->getTicketsByStatus();
        }
        if ($visible->contains(DashboardWidget::CHART_CALLS_PER_DAY)) {
            $widgetData['calls_per_day'] = $service->getCallsPerDay(7);
        }
        if ($visible->contains(DashboardWidget::TABLE_TOP_OPPS)) {
            $widgetData['top_opps'] = $service->getTopOpportunities(5);
        }
        if ($visible->contains(DashboardWidget::TABLE_TOP_REPS)) {
            $widgetData['top_reps'] = $service->getTopReps(5);
        }

        return view('analytics.dashboard.index', compact('widgets', 'widgetData'));
    }

    public function updateLayout(Request $request): JsonResponse
    {
        $request->validate([
            'layout'            => ['required', 'array'],
            'layout.*.type'     => ['required', 'string'],
            'layout.*.position' => ['required', 'integer', 'min:0'],
        ]);

        $userId = auth()->id();

        foreach ($request->input('layout') as $item) {
            DashboardWidget::where('user_id', $userId)
                ->where('widget_type', $item['type'])
                ->update(['position' => $item['position']]);
        }

        return response()->json(['success' => true]);
    }

    public function toggleWidget(Request $request): JsonResponse
    {
        $request->validate([
            'widget_type' => ['required', 'string'],
        ]);

        $widget = DashboardWidget::where('user_id', auth()->id())
            ->where('widget_type', $request->widget_type)
            ->firstOrFail();

        $widget->update(['is_visible' => ! $widget->is_visible]);

        return response()->json([
            'success'    => true,
            'is_visible' => $widget->is_visible,
        ]);
    }
}
