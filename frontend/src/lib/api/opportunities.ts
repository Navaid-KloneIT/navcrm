import apiClient from './client';
import type { Opportunity, OpportunityFilters, OpportunityTeamMember, PaginatedResponse, ApiResponse } from '@/types';

export const opportunitiesApi = {
  list: (params?: OpportunityFilters) =>
    apiClient.get<PaginatedResponse<Opportunity>>('/opportunities', { params }),

  get: (id: number) =>
    apiClient.get<ApiResponse<Opportunity>>(`/opportunities/${id}`),

  create: (data: Partial<Opportunity>) =>
    apiClient.post<ApiResponse<Opportunity>>('/opportunities', data),

  update: (id: number, data: Partial<Opportunity>) =>
    apiClient.put<ApiResponse<Opportunity>>(`/opportunities/${id}`, data),

  delete: (id: number) =>
    apiClient.delete(`/opportunities/${id}`),

  updateStage: (id: number, pipelineStageId: number) =>
    apiClient.put<ApiResponse<Opportunity>>(`/opportunities/${id}/stage`, {
      pipeline_stage_id: pipelineStageId,
    }),

  getTeam: (id: number) =>
    apiClient.get<{ data: OpportunityTeamMember[] }>(`/opportunities/${id}/team`),

  addTeamMember: (id: number, data: { user_id: number; role?: string; split_percentage?: number }) =>
    apiClient.post<{ data: OpportunityTeamMember[] }>(`/opportunities/${id}/team`, data),

  updateTeamMember: (id: number, userId: number, data: { role?: string; split_percentage?: number }) =>
    apiClient.put<{ data: OpportunityTeamMember[] }>(`/opportunities/${id}/team/${userId}`, data),

  removeTeamMember: (id: number, userId: number) =>
    apiClient.delete(`/opportunities/${id}/team/${userId}`),
};
