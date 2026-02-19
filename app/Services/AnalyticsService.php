<?php

namespace App\Services;

use App\Models\CalendarEvent;
use App\Models\CallLog;
use App\Models\Lead;
use App\Models\Opportunity;
use App\Models\PipelineStage;
use App\Models\Task;
use App\Models\Ticket;
use App\Models\User;
use Carbon\Carbon;

class AnalyticsService
{
    // ─────────────────────────────────────────────────────────────────────────
    // Dashboard Widget Data
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * All KPI card values in one query batch.
     */
    public function getKpiData(): array
    {
        $thirtyDaysAgo  = Carbon::now()->subDays(30)->startOfDay();
        $ninetyDaysAgo  = Carbon::now()->subDays(90)->startOfDay();

        $totalRevenue = Opportunity::whereNotNull('won_at')->sum('amount');

        $newLeads = Lead::where('created_at', '>=', $thirtyDaysAgo)->count();

        $openOppsQuery = Opportunity::whereNull('won_at')->whereNull('lost_at');
        $openOppsCount = (clone $openOppsQuery)->count();
        $openOppsValue = (clone $openOppsQuery)->sum('amount');

        $openTickets = Ticket::whereNotIn('status', ['resolved', 'closed'])->count();

        $won  = Opportunity::whereNotNull('won_at')->where('won_at', '>=', $ninetyDaysAgo)->count();
        $lost = Opportunity::whereNotNull('lost_at')->where('lost_at', '>=', $ninetyDaysAgo)->count();
        $winRate = ($won + $lost) > 0 ? round(($won / ($won + $lost)) * 100, 1) : 0.0;

        return [
            'total_revenue'   => round((float) $totalRevenue, 2),
            'new_leads_30d'   => $newLeads,
            'open_opps_count' => $openOppsCount,
            'open_opps_value' => round((float) $openOppsValue, 2),
            'open_tickets'    => $openTickets,
            'win_rate'        => $winRate,
            'win_rate_period' => '90 days',
        ];
    }

    /**
     * Monthly closed-won revenue for the last N months.
     * Always returns exactly $months entries (0 for months with no revenue).
     */
    public function getMonthlyRevenue(int $months = 12): array
    {
        $result = [];
        for ($i = $months - 1; $i >= 0; $i--) {
            $month    = Carbon::now()->subMonths($i)->startOfMonth();
            $monthEnd = $month->copy()->endOfMonth();

            $revenue = Opportunity::whereNotNull('won_at')
                ->whereBetween('won_at', [$month, $monthEnd])
                ->sum('amount');

            $result[] = [
                'label'   => $month->format('M Y'),
                'month'   => $month->format('Y-m'),
                'revenue' => round((float) $revenue, 2),
            ];
        }
        return $result;
    }

    /**
     * Open opportunity count and value per pipeline stage.
     */
    public function getPipelineByStage(): array
    {
        $stages = PipelineStage::orderBy('position')->get();

        return $stages->map(function ($stage) {
            $opps = Opportunity::where('pipeline_stage_id', $stage->id)
                ->whereNull('won_at')
                ->whereNull('lost_at');

            return [
                'stage_id'   => $stage->id,
                'stage_name' => $stage->name,
                'color'      => $stage->color ?? '#2563eb',
                'count'      => (clone $opps)->count(),
                'value'      => round((float) (clone $opps)->sum('amount'), 2),
            ];
        })->toArray();
    }

    /**
     * Lead count grouped by source.
     */
    public function getLeadsBySource(): array
    {
        return Lead::selectRaw('source, count(*) as count')
            ->groupBy('source')
            ->orderByDesc('count')
            ->get()
            ->map(fn ($r) => [
                'source' => $r->source ?? 'Unknown',
                'count'  => (int) $r->count,
            ])->toArray();
    }

    /**
     * Ticket count grouped by status.
     */
    public function getTicketsByStatus(): array
    {
        return Ticket::selectRaw('status, count(*) as count')
            ->groupBy('status')
            ->get()
            ->map(fn ($r) => [
                'status' => $r->getRawOriginal('status'),
                'label'  => ucwords(str_replace('_', ' ', $r->getRawOriginal('status'))),
                'count'  => (int) $r->count,
            ])->toArray();
    }

    /**
     * Call log count per day for the last N days.
     * Always returns exactly $days entries.
     */
    public function getCallsPerDay(int $days = 7): array
    {
        $result = [];
        for ($i = $days - 1; $i >= 0; $i--) {
            $day    = Carbon::now()->subDays($i)->startOfDay();
            $dayEnd = $day->copy()->endOfDay();

            $count = CallLog::whereBetween('called_at', [$day, $dayEnd])->count();

            $result[] = [
                'date'  => $day->toDateString(),
                'label' => $day->format('D'),
                'count' => $count,
            ];
        }
        return $result;
    }

    /**
     * Top open opportunities by amount.
     */
    public function getTopOpportunities(int $limit = 5): array
    {
        return Opportunity::with(['account', 'owner', 'stage'])
            ->whereNull('won_at')
            ->whereNull('lost_at')
            ->orderByDesc('amount')
            ->limit($limit)
            ->get()
            ->map(fn ($opp) => [
                'id'          => $opp->id,
                'name'        => $opp->name,
                'account_name'=> $opp->account?->name ?? '—',
                'owner_name'  => $opp->owner?->name ?? '—',
                'stage_name'  => $opp->stage?->name ?? '—',
                'stage_color' => $opp->stage?->color ?? '#6b7280',
                'amount'      => round((float) $opp->amount, 2),
                'close_date'  => $opp->close_date?->toDateString(),
                'probability' => $opp->probability,
            ])->toArray();
    }

    /**
     * Top sales reps by closed-won revenue this quarter.
     */
    public function getTopReps(int $limit = 5): array
    {
        $tenantId            = auth()->user()->tenant_id;
        $currentQuarterStart = Carbon::now()->startOfQuarter();
        $currentQuarterEnd   = Carbon::now()->endOfQuarter();

        return User::select('users.id', 'users.name')
            ->selectRaw('COALESCE(SUM(opportunities.amount), 0) as revenue')
            ->selectRaw('COUNT(opportunities.id) as deals_count')
            ->leftJoin('opportunities', function ($join) use ($currentQuarterStart, $currentQuarterEnd) {
                $join->on('opportunities.owner_id', '=', 'users.id')
                     ->whereNotNull('opportunities.won_at')
                     ->whereBetween('opportunities.won_at', [$currentQuarterStart, $currentQuarterEnd])
                     ->whereNull('opportunities.deleted_at');
            })
            ->where('users.tenant_id', $tenantId)
            ->where('users.is_active', true)
            ->groupBy('users.id', 'users.name')
            ->orderByDesc('revenue')
            ->limit($limit)
            ->get()
            ->map(fn ($u) => [
                'user_id'     => $u->id,
                'user_name'   => $u->name,
                'revenue'     => round((float) $u->revenue, 2),
                'deals_count' => (int) $u->deals_count,
            ])->toArray();
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Standard Reports
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Calls, meetings, and completed tasks per day for the given date range.
     * Always returns one entry per calendar day (0 for days with no activity).
     */
    public function getSalesActivityData(string $from, string $to): array
    {
        $start = Carbon::parse($from)->startOfDay();
        $end   = Carbon::parse($to)->endOfDay();

        // Build full date spine
        $days    = [];
        $current = $start->copy();
        while ($current->lte($end)) {
            $days[$current->toDateString()] = [
                'date'     => $current->toDateString(),
                'label'    => $current->format('M j'),
                'calls'    => 0,
                'meetings' => 0,
                'tasks'    => 0,
            ];
            $current->addDay();
        }

        // Calls per day
        CallLog::selectRaw('DATE(called_at) as day, count(*) as count')
            ->whereBetween('called_at', [$start, $end])
            ->groupByRaw('DATE(called_at)')
            ->get()
            ->each(function ($r) use (&$days) {
                if (isset($days[$r->day])) {
                    $days[$r->day]['calls'] = (int) $r->count;
                }
            });

        // Meetings/events per day (starts_at column on calendar_events)
        CalendarEvent::selectRaw('DATE(starts_at) as day, count(*) as count')
            ->whereBetween('starts_at', [$start, $end])
            ->groupByRaw('DATE(starts_at)')
            ->get()
            ->each(function ($r) use (&$days) {
                if (isset($days[$r->day])) {
                    $days[$r->day]['meetings'] = (int) $r->count;
                }
            });

        // Tasks completed per day
        Task::selectRaw('DATE(completed_at) as day, count(*) as count')
            ->whereBetween('completed_at', [$start, $end])
            ->whereNotNull('completed_at')
            ->groupByRaw('DATE(completed_at)')
            ->get()
            ->each(function ($r) use (&$days) {
                if (isset($days[$r->day])) {
                    $days[$r->day]['tasks'] = (int) $r->count;
                }
            });

        return array_values($days);
    }

    /**
     * Per-rep sales metrics for the given date range.
     */
    public function getSalesPerformanceData(string $from, string $to): array
    {
        $start    = Carbon::parse($from)->startOfDay();
        $end      = Carbon::parse($to)->endOfDay();
        $tenantId = auth()->user()->tenant_id;

        $reps = User::where('tenant_id', $tenantId)
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name']);

        return $reps->map(function ($user) use ($start, $end) {
            $wonOpps = Opportunity::where('owner_id', $user->id)
                ->whereNotNull('won_at')
                ->whereBetween('won_at', [$start, $end]);

            $lostOpps = Opportunity::where('owner_id', $user->id)
                ->whereNotNull('lost_at')
                ->whereBetween('lost_at', [$start, $end]);

            $wonCount  = (clone $wonOpps)->count();
            $lostCount = (clone $lostOpps)->count();
            $revenue   = round((float) (clone $wonOpps)->sum('amount'), 2);
            $total     = $wonCount + $lostCount;
            $winRate   = $total > 0 ? round(($wonCount / $total) * 100, 1) : 0.0;

            $callCount = CallLog::where('user_id', $user->id)
                ->whereBetween('called_at', [$start, $end])
                ->count();

            $meetingCount = CalendarEvent::where('organizer_id', $user->id)
                ->whereBetween('starts_at', [$start, $end])
                ->count();

            return [
                'user_id'       => $user->id,
                'user_name'     => $user->name,
                'revenue'       => $revenue,
                'won_count'     => $wonCount,
                'lost_count'    => $lostCount,
                'win_rate'      => $winRate,
                'call_count'    => $callCount,
                'meeting_count' => $meetingCount,
            ];
        })->sortByDesc('revenue')->values()->toArray();
    }

    /**
     * Pipeline funnel: opportunity counts per stage with stage-to-stage conversion rates.
     */
    public function getFunnelData(): array
    {
        $stages = PipelineStage::orderBy('position')->get();

        $stageData = $stages->map(function ($stage) {
            $count = Opportunity::where('pipeline_stage_id', $stage->id)->count();
            $value = Opportunity::where('pipeline_stage_id', $stage->id)
                ->whereNull('won_at')
                ->whereNull('lost_at')
                ->sum('amount');

            return [
                'stage_id'   => $stage->id,
                'stage_name' => $stage->name,
                'color'      => $stage->color ?? '#2563eb',
                'position'   => $stage->position,
                'count'      => $count,
                'open_value' => round((float) $value, 2),
                'conversion' => 0.0,
            ];
        })->values()->toArray();

        // Stage-to-stage conversion rates
        for ($i = 1; $i < count($stageData); $i++) {
            $prev = $stageData[$i - 1]['count'];
            $curr = $stageData[$i]['count'];
            $stageData[$i]['conversion'] = $prev > 0
                ? round(($curr / $prev) * 100, 1)
                : 0.0;
        }

        $wonCount          = Opportunity::whereNotNull('won_at')->count();
        $firstCount        = $stageData[0]['count'] ?? 0;
        $overallConversion = $firstCount > 0
            ? round(($wonCount / $firstCount) * 100, 1)
            : 0.0;

        return [
            'stages'             => $stageData,
            'won_count'          => $wonCount,
            'overall_conversion' => $overallConversion,
        ];
    }

    /**
     * Ticket metrics for the given date range.
     */
    public function getServiceReportData(string $from, string $to): array
    {
        $start = Carbon::parse($from)->startOfDay();
        $end   = Carbon::parse($to)->endOfDay();

        $tickets = Ticket::with('assignee')
            ->whereBetween('created_at', [$start, $end])
            ->get();

        // By status
        $byStatus = $tickets->groupBy(fn ($t) => $t->getRawOriginal('status'))
            ->map(fn ($grp, $key) => [
                'status' => $key,
                'label'  => ucwords(str_replace('_', ' ', $key)),
                'count'  => $grp->count(),
            ])->values()->toArray();

        // By priority
        $byPriority = $tickets->groupBy(fn ($t) => $t->getRawOriginal('priority'))
            ->map(fn ($grp, $key) => [
                'priority' => $key,
                'label'    => ucfirst($key),
                'count'    => $grp->count(),
            ])->values()->toArray();

        // By agent
        $byAgent = $tickets->groupBy(fn ($t) => $t->assigned_to ?? 0)
            ->map(fn ($grp, $userId) => [
                'agent_name' => $grp->first()->assignee?->name ?? 'Unassigned',
                'count'      => $grp->count(),
            ])->values()->sortByDesc('count')->values()->toArray();

        // Avg first response time (minutes)
        $responded = $tickets->filter(fn ($t) => $t->first_response_at !== null);
        $avgResponseMinutes = $responded->count() > 0
            ? round($responded->avg(fn ($t) => $t->created_at->diffInMinutes($t->first_response_at)), 1)
            : null;

        // Avg resolution time (hours)
        $resolved = $tickets->filter(fn ($t) => $t->resolved_at !== null);
        $avgResolutionHours = $resolved->count() > 0
            ? round($resolved->avg(fn ($t) => $t->created_at->diffInHours($t->resolved_at)), 1)
            : null;

        // SLA breach count
        $slaBreaches = $tickets->filter(function ($t) {
            if (! $t->sla_due_at) {
                return false;
            }
            if ($t->resolved_at) {
                return $t->resolved_at->gt($t->sla_due_at);
            }
            return Carbon::now()->gt($t->sla_due_at);
        })->count();

        return [
            'total_tickets'        => $tickets->count(),
            'by_status'            => $byStatus,
            'by_priority'          => $byPriority,
            'by_agent'             => $byAgent,
            'avg_response_minutes' => $avgResponseMinutes,
            'avg_resolution_hours' => $avgResolutionHours,
            'sla_breach_count'     => $slaBreaches,
            'period_start'         => $start->toDateString(),
            'period_end'           => $end->toDateString(),
        ];
    }
}
