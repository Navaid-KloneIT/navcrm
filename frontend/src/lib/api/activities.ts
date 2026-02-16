import apiClient from './client';
import type { Activity, PaginatedResponse, ApiResponse } from '@/types';

export const activitiesApi = {
  list: (params?: { activitable_type?: string; activitable_id?: number; page?: number }) =>
    apiClient.get<PaginatedResponse<Activity>>('/activities', { params }),

  create: (data: {
    type: string;
    subject: string;
    description?: string;
    activitable_type: string;
    activitable_id: number;
    occurred_at?: string;
  }) =>
    apiClient.post<ApiResponse<Activity>>('/activities', data),

  delete: (id: number) =>
    apiClient.delete(`/activities/${id}`),
};
