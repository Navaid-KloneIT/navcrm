import apiClient from './client';
import type { ForecastSummary, PipelineStageData, TargetVsActual } from '@/types';

export const forecastsApi = {
  getData: (params?: { period_start?: string; period_end?: string }) =>
    apiClient.get<{
      data: {
        targets_vs_actual: TargetVsActual[];
        pipeline_by_stage: PipelineStageData[];
      };
    }>('/forecasts', { params }),

  getSummary: (params?: { period_start?: string; period_end?: string }) =>
    apiClient.get<{ data: ForecastSummary }>('/forecasts/summary', { params }),
};
