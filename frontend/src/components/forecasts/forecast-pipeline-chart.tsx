import React from 'react';
import type { PipelineStageData } from '@/types';
import { Card, CardHeader, CardTitle, CardContent } from '@/components/ui/card';

interface ForecastPipelineChartProps {
  data: PipelineStageData[];
}

function formatAmount(amount: number): string {
  return new Intl.NumberFormat('en-US', {
    style: 'currency',
    currency: 'USD',
    minimumFractionDigits: 0,
    maximumFractionDigits: 0,
  }).format(amount);
}

function ForecastPipelineChart({ data }: ForecastPipelineChartProps) {
  const maxAmount = Math.max(...data.map((d) => d.total_amount), 1);

  return (
    <Card>
      <CardHeader>
        <CardTitle>Pipeline by Stage</CardTitle>
      </CardHeader>
      <CardContent>
        {data.length === 0 ? (
          <p className="py-8 text-center text-sm text-gray-500">
            No pipeline data available
          </p>
        ) : (
          <div className="space-y-4">
            {data.map((stage) => {
              const widthPercent = (stage.total_amount / maxAmount) * 100;

              return (
                <div key={stage.stage_id} className="space-y-1">
                  <div className="flex items-center justify-between text-sm">
                    <span className="font-medium text-gray-700">
                      {stage.stage_name}
                    </span>
                    <span className="text-gray-500">
                      {formatAmount(stage.total_amount)} ({stage.count} deals)
                    </span>
                  </div>
                  <div className="h-6 w-full rounded-md bg-gray-100">
                    <div
                      className="flex h-full items-center rounded-md px-2 text-xs font-medium text-white transition-all duration-300"
                      style={{
                        width: `${Math.max(widthPercent, 2)}%`,
                        backgroundColor: stage.color || '#3b82f6',
                      }}
                    >
                      {widthPercent > 15 && formatAmount(stage.total_amount)}
                    </div>
                  </div>
                </div>
              );
            })}
          </div>
        )}
      </CardContent>
    </Card>
  );
}

export { ForecastPipelineChart };
