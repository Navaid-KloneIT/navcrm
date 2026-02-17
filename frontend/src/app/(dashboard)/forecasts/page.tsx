'use client';

import React, { useEffect, useState, useCallback } from 'react';
import Link from 'next/link';
import { Settings } from 'lucide-react';
import { forecastsApi } from '@/lib/api/forecasts';
import type { ForecastSummary, PipelineStageData, TargetVsActual } from '@/types';
import { PageHeader } from '@/components/shared/page-header';
import { Button } from '@/components/ui/button';
import { Select } from '@/components/ui/select';
import { Spinner } from '@/components/ui/spinner';
import { ForecastSummaryCards } from '@/components/forecasts/forecast-summary-cards';
import { ForecastPipelineChart } from '@/components/forecasts/forecast-pipeline-chart';
import { ForecastTargetsTable } from '@/components/forecasts/forecast-targets-table';

function getQuarterDates(offset: number = 0): { start: string; end: string; label: string } {
  const now = new Date();
  const currentQuarter = Math.floor(now.getMonth() / 3) + offset;
  const year = now.getFullYear() + Math.floor(currentQuarter / 4);
  const quarter = ((currentQuarter % 4) + 4) % 4;

  const startMonth = quarter * 3;
  const start = new Date(year, startMonth, 1);
  const end = new Date(year, startMonth + 3, 0);

  const formatDate = (d: Date) => d.toISOString().split('T')[0];

  return {
    start: formatDate(start),
    end: formatDate(end),
    label: `Q${quarter + 1} ${year}`,
  };
}

const periodOptions = [
  { value: '-1', label: getQuarterDates(-1).label },
  { value: '0', label: `${getQuarterDates(0).label} (Current)` },
  { value: '1', label: getQuarterDates(1).label },
];

export default function ForecastsPage() {
  const [summary, setSummary] = useState<ForecastSummary | null>(null);
  const [pipelineData, setPipelineData] = useState<PipelineStageData[]>([]);
  const [targetsData, setTargetsData] = useState<TargetVsActual[]>([]);
  const [loading, setLoading] = useState(true);
  const [periodOffset, setPeriodOffset] = useState('0');

  const fetchData = useCallback(async () => {
    setLoading(true);
    try {
      const { start, end } = getQuarterDates(Number(periodOffset));
      const params = { period_start: start, period_end: end };

      const [summaryRes, dataRes] = await Promise.all([
        forecastsApi.getSummary(params),
        forecastsApi.getData(params),
      ]);

      setSummary(summaryRes.data.data);
      setPipelineData(dataRes.data.data.pipeline_by_stage);
      setTargetsData(dataRes.data.data.targets_vs_actual);
    } catch {
      // Handle silently
    } finally {
      setLoading(false);
    }
  }, [periodOffset]);

  useEffect(() => {
    fetchData();
  }, [fetchData]);

  return (
    <div>
      <PageHeader
        title="Forecasts"
        description="Sales pipeline and forecast overview"
        action={
          <Link href="/forecasts/targets">
            <Button variant="outline">
              <Settings className="mr-2 h-4 w-4" />
              Manage Targets
            </Button>
          </Link>
        }
      />

      <div className="mb-6">
        <div className="w-48">
          <Select
            options={periodOptions}
            value={periodOffset}
            onChange={(e) => setPeriodOffset(e.target.value)}
          />
        </div>
      </div>

      {loading ? (
        <div className="flex items-center justify-center py-20">
          <Spinner size="lg" />
        </div>
      ) : (
        <div className="space-y-6">
          {summary && <ForecastSummaryCards summary={summary} />}

          <div className="grid gap-6 lg:grid-cols-2">
            <ForecastPipelineChart data={pipelineData} />
            <ForecastTargetsTable data={targetsData} />
          </div>
        </div>
      )}
    </div>
  );
}
