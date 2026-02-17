<?php

namespace App\Services;

use App\Models\Opportunity;
use App\Models\PipelineStage;
use App\Models\SalesTarget;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ForecastService
{
    public function getSummary(?string $periodStart = null, ?string $periodEnd = null): array
    {
        $start = $periodStart ? Carbon::parse($periodStart) : Carbon::now()->startOfQuarter();
        $end = $periodEnd ? Carbon::parse($periodEnd) : Carbon::now()->endOfQuarter();

        $opportunities = Opportunity::query()
            ->whereNull('lost_at')
            ->where(function ($q) use ($start, $end) {
                $q->whereBetween('close_date', [$start, $end])
                    ->orWhereNull('close_date');
            })
            ->with('stage')
            ->get();

        $totalPipeline = $opportunities->sum('amount');
        $weightedPipeline = $opportunities->sum('weighted_amount');

        $closedWon = Opportunity::query()
            ->whereNotNull('won_at')
            ->whereBetween('won_at', [$start, $end])
            ->sum('amount');

        $closedLost = Opportunity::query()
            ->whereNotNull('lost_at')
            ->whereBetween('lost_at', [$start, $end])
            ->sum('amount');

        return [
            'period_start' => $start->toDateString(),
            'period_end' => $end->toDateString(),
            'total_pipeline' => round((float) $totalPipeline, 2),
            'weighted_pipeline' => round($weightedPipeline, 2),
            'closed_won' => round((float) $closedWon, 2),
            'closed_lost' => round((float) $closedLost, 2),
            'open_deals' => $opportunities->count(),
        ];
    }

    public function getPipelineByStage(): array
    {
        $stages = PipelineStage::query()
            ->withCount('opportunities')
            ->orderBy('position')
            ->get();

        return $stages->map(function ($stage) {
            $totalAmount = $stage->opportunities()
                ->whereNull('deleted_at')
                ->sum('amount');
            $weightedAmount = $stage->opportunities()
                ->whereNull('deleted_at')
                ->selectRaw('SUM(amount * probability / 100) as weighted')
                ->value('weighted');

            return [
                'stage_id' => $stage->id,
                'stage_name' => $stage->name,
                'color' => $stage->color,
                'count' => $stage->opportunities_count,
                'total_amount' => round((float) $totalAmount, 2),
                'weighted_amount' => round((float) ($weightedAmount ?? 0), 2),
            ];
        })->toArray();
    }

    public function getTargetsVsActual(?string $periodStart = null, ?string $periodEnd = null): array
    {
        $start = $periodStart ? Carbon::parse($periodStart) : Carbon::now()->startOfQuarter();
        $end = $periodEnd ? Carbon::parse($periodEnd) : Carbon::now()->endOfQuarter();

        $targets = SalesTarget::query()
            ->with('user')
            ->where('period_start', '>=', $start)
            ->where('period_end', '<=', $end)
            ->get();

        return $targets->map(function ($target) {
            $actual = Opportunity::query()
                ->where('owner_id', $target->user_id)
                ->whereNotNull('won_at')
                ->whereBetween('won_at', [$target->period_start, $target->period_end])
                ->sum('amount');

            $pipeline = Opportunity::query()
                ->where('owner_id', $target->user_id)
                ->whereNull('won_at')
                ->whereNull('lost_at')
                ->whereBetween('close_date', [$target->period_start, $target->period_end])
                ->sum('amount');

            return [
                'target_id' => $target->id,
                'user_id' => $target->user_id,
                'user_name' => $target->user?->name ?? 'Team',
                'period_type' => $target->period_type,
                'period_start' => $target->period_start->toDateString(),
                'period_end' => $target->period_end->toDateString(),
                'target_amount' => round((float) $target->target_amount, 2),
                'actual_amount' => round((float) $actual, 2),
                'pipeline_amount' => round((float) $pipeline, 2),
                'attainment_percent' => $target->target_amount > 0
                    ? round(($actual / $target->target_amount) * 100, 1)
                    : 0,
                'category' => $target->category,
            ];
        })->toArray();
    }
}
