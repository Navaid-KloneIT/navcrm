import apiClient from './client';
import type { SalesTarget, SalesTargetFilters, PaginatedResponse, ApiResponse } from '@/types';

export const salesTargetsApi = {
  list: (params?: SalesTargetFilters) =>
    apiClient.get<PaginatedResponse<SalesTarget>>('/sales-targets', { params }),

  get: (id: number) =>
    apiClient.get<ApiResponse<SalesTarget>>(`/sales-targets/${id}`),

  create: (data: Partial<SalesTarget>) =>
    apiClient.post<ApiResponse<SalesTarget>>('/sales-targets', data),

  update: (id: number, data: Partial<SalesTarget>) =>
    apiClient.put<ApiResponse<SalesTarget>>(`/sales-targets/${id}`, data),

  delete: (id: number) =>
    apiClient.delete(`/sales-targets/${id}`),
};
