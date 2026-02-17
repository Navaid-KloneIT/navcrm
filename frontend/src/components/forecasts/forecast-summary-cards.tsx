import React from 'react';
import { DollarSign, TrendingUp, CheckCircle, BarChart3 } from 'lucide-react';
import { StatsCard } from '@/components/shared/stats-card';
import type { ForecastSummary } from '@/types';

interface ForecastSummaryCardsProps {
  summary: ForecastSummary;
}

function formatCurrency(amount: number): string {
  return new Intl.NumberFormat('en-US', {
    style: 'currency',
    currency: 'USD',
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
  }).format(amount);
}

function ForecastSummaryCards({ summary }: ForecastSummaryCardsProps) {
  return (
    <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
      <StatsCard
        title="Total Pipeline"
        value={formatCurrency(summary.total_pipeline)}
        icon={<DollarSign className="h-5 w-5" />}
      />
      <StatsCard
        title="Weighted Forecast"
        value={formatCurrency(summary.weighted_pipeline)}
        icon={<TrendingUp className="h-5 w-5" />}
      />
      <StatsCard
        title="Closed Won"
        value={formatCurrency(summary.closed_won)}
        icon={<CheckCircle className="h-5 w-5" />}
      />
      <StatsCard
        title="Open Deals"
        value={summary.open_deals.toString()}
        icon={<BarChart3 className="h-5 w-5" />}
      />
    </div>
  );
}

export { ForecastSummaryCards };
export { formatCurrency };
