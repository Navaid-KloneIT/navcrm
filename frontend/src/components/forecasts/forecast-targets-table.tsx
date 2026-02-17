import React from 'react';
import { cn } from '@/lib/utils/cn';
import { Card, CardHeader, CardTitle, CardContent } from '@/components/ui/card';
import type { TargetVsActual } from '@/types';

interface ForecastTargetsTableProps {
  data: TargetVsActual[];
}

function formatCurrency(amount: number): string {
  return new Intl.NumberFormat('en-US', {
    style: 'currency',
    currency: 'USD',
    minimumFractionDigits: 0,
    maximumFractionDigits: 0,
  }).format(amount);
}

function getAttainmentColor(percent: number): string {
  if (percent >= 100) return 'text-green-700 bg-green-50';
  if (percent >= 75) return 'text-yellow-700 bg-yellow-50';
  return 'text-red-700 bg-red-50';
}

function getProgressBarColor(percent: number): string {
  if (percent >= 100) return 'bg-green-500';
  if (percent >= 75) return 'bg-yellow-500';
  return 'bg-red-500';
}

function ForecastTargetsTable({ data }: ForecastTargetsTableProps) {
  return (
    <Card>
      <CardHeader>
        <CardTitle>Target vs Actual</CardTitle>
      </CardHeader>
      <CardContent>
        {data.length === 0 ? (
          <p className="py-8 text-center text-sm text-gray-500">
            No target data available
          </p>
        ) : (
          <div className="overflow-x-auto">
            <table className="w-full text-sm">
              <thead>
                <tr className="border-b border-gray-200 text-left">
                  <th className="pb-3 pr-4 font-medium text-gray-500">Rep Name</th>
                  <th className="pb-3 pr-4 font-medium text-gray-500 text-right">Target</th>
                  <th className="pb-3 pr-4 font-medium text-gray-500 text-right">Actual</th>
                  <th className="pb-3 pr-4 font-medium text-gray-500 text-right">Pipeline</th>
                  <th className="pb-3 font-medium text-gray-500 text-right">Attainment</th>
                </tr>
              </thead>
              <tbody className="divide-y divide-gray-100">
                {data.map((row) => (
                  <tr key={row.target_id}>
                    <td className="py-3 pr-4 font-medium text-gray-900">
                      {row.user_name}
                    </td>
                    <td className="py-3 pr-4 text-right text-gray-600">
                      {formatCurrency(row.target_amount)}
                    </td>
                    <td className="py-3 pr-4 text-right text-gray-600">
                      {formatCurrency(row.actual_amount)}
                    </td>
                    <td className="py-3 pr-4 text-right text-gray-600">
                      {formatCurrency(row.pipeline_amount)}
                    </td>
                    <td className="py-3">
                      <div className="flex items-center justify-end gap-3">
                        <div className="w-20">
                          <div className="h-2 w-full rounded-full bg-gray-100">
                            <div
                              className={cn(
                                'h-full rounded-full transition-all duration-300',
                                getProgressBarColor(row.attainment_percent)
                              )}
                              style={{
                                width: `${Math.min(row.attainment_percent, 100)}%`,
                              }}
                            />
                          </div>
                        </div>
                        <span
                          className={cn(
                            'inline-flex min-w-[3.5rem] justify-center rounded-full px-2 py-0.5 text-xs font-medium',
                            getAttainmentColor(row.attainment_percent)
                          )}
                        >
                          {row.attainment_percent.toFixed(0)}%
                        </span>
                      </div>
                    </td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
        )}
      </CardContent>
    </Card>
  );
}

export { ForecastTargetsTable };
