import apiClient from './client';
import type { PipelineStage } from '@/types';

export const pipelineStagesApi = {
  list: () =>
    apiClient.get<{ data: PipelineStage[] }>('/pipeline-stages'),

  get: (id: number) =>
    apiClient.get<{ data: PipelineStage }>(`/pipeline-stages/${id}`),

  create: (data: Partial<PipelineStage>) =>
    apiClient.post<{ data: PipelineStage }>('/pipeline-stages', data),

  update: (id: number, data: Partial<PipelineStage>) =>
    apiClient.put<{ data: PipelineStage }>(`/pipeline-stages/${id}`, data),

  delete: (id: number) =>
    apiClient.delete(`/pipeline-stages/${id}`),

  reorder: (stages: { id: number; position: number }[]) =>
    apiClient.post<{ data: PipelineStage[] }>('/pipeline-stages/reorder', { stages }),
};
