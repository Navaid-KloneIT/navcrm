import apiClient from './client';
import type { Lead, LeadFilters, PaginatedResponse, ApiResponse } from '@/types';

export const leadsApi = {
  list: (params?: LeadFilters) =>
    apiClient.get<PaginatedResponse<Lead>>('/leads', { params }),

  get: (id: number) =>
    apiClient.get<ApiResponse<Lead>>(`/leads/${id}`),

  create: (data: Partial<Lead>) =>
    apiClient.post<ApiResponse<Lead>>('/leads', data),

  update: (id: number, data: Partial<Lead>) =>
    apiClient.put<ApiResponse<Lead>>(`/leads/${id}`, data),

  delete: (id: number) =>
    apiClient.delete(`/leads/${id}`),

  convert: (id: number, options?: { create_account?: boolean; account_name?: string; existing_account_id?: number }) =>
    apiClient.post(`/leads/${id}/convert`, options),

  syncTags: (id: number, tagIds: number[]) =>
    apiClient.post(`/leads/${id}/tags`, { tag_ids: tagIds }),
};
