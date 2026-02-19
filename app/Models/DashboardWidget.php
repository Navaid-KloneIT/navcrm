<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DashboardWidget extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'user_id',
        'tenant_id',
        'widget_type',
        'position',
        'is_visible',
        'settings',
    ];

    protected function casts(): array
    {
        return [
            'is_visible' => 'boolean',
            'position'   => 'integer',
            'settings'   => 'array',
        ];
    }

    // ── Widget type constants ──────────────────────────────────────────────

    // KPI cards
    const KPI_REVENUE      = 'kpi_revenue';
    const KPI_LEADS        = 'kpi_leads';
    const KPI_OPEN_OPPS    = 'kpi_open_opps';
    const KPI_OPEN_TICKETS = 'kpi_open_tickets';
    const KPI_WIN_RATE     = 'kpi_win_rate';

    // Charts
    const CHART_MONTHLY_REVENUE = 'chart_monthly_revenue';
    const CHART_PIPELINE_STAGES = 'chart_pipeline_stages';
    const CHART_LEADS_SOURCE    = 'chart_leads_source';
    const CHART_TICKETS_STATUS  = 'chart_tickets_status';
    const CHART_CALLS_PER_DAY   = 'chart_calls_per_day';

    // Tables
    const TABLE_TOP_OPPS = 'table_top_opps';
    const TABLE_TOP_REPS = 'table_top_reps';

    /**
     * Default widget set for new users (ordered by position).
     */
    public static function defaultWidgets(): array
    {
        return [
            ['type' => self::KPI_REVENUE,            'position' => 0],
            ['type' => self::KPI_LEADS,              'position' => 1],
            ['type' => self::KPI_OPEN_OPPS,          'position' => 2],
            ['type' => self::KPI_OPEN_TICKETS,       'position' => 3],
            ['type' => self::KPI_WIN_RATE,           'position' => 4],
            ['type' => self::CHART_MONTHLY_REVENUE,  'position' => 5],
            ['type' => self::CHART_PIPELINE_STAGES,  'position' => 6],
            ['type' => self::CHART_LEADS_SOURCE,     'position' => 7],
            ['type' => self::CHART_TICKETS_STATUS,   'position' => 8],
            ['type' => self::CHART_CALLS_PER_DAY,    'position' => 9],
            ['type' => self::TABLE_TOP_OPPS,         'position' => 10],
            ['type' => self::TABLE_TOP_REPS,         'position' => 11],
        ];
    }

    /**
     * Human-readable label for each widget type.
     */
    public static function widgetLabel(string $type): string
    {
        return match ($type) {
            self::KPI_REVENUE            => 'Total Revenue',
            self::KPI_LEADS              => 'New Leads (30d)',
            self::KPI_OPEN_OPPS          => 'Open Opportunities',
            self::KPI_OPEN_TICKETS       => 'Open Tickets',
            self::KPI_WIN_RATE           => 'Win Rate',
            self::CHART_MONTHLY_REVENUE  => 'Monthly Revenue',
            self::CHART_PIPELINE_STAGES  => 'Pipeline by Stage',
            self::CHART_LEADS_SOURCE     => 'Leads by Source',
            self::CHART_TICKETS_STATUS   => 'Tickets by Status',
            self::CHART_CALLS_PER_DAY    => 'Calls per Day',
            self::TABLE_TOP_OPPS         => 'Top Opportunities',
            self::TABLE_TOP_REPS         => 'Top Sales Reps',
            default                      => ucwords(str_replace('_', ' ', $type)),
        };
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
