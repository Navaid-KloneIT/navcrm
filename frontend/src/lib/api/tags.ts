import apiClient from './client';
import type { Tag, ApiResponse } from '@/types';

export const tagsApi = {
  list: () =>
    apiClient.get<{ data: Tag[] }>('/tags'),

  create: (data: { name: string; color?: string }) =>
    apiClient.post<ApiResponse<Tag>>('/tags', data),

  update: (id: number, data: { name?: string; color?: string }) =>
    apiClient.put<ApiResponse<Tag>>(`/tags/${id}`, data),

  delete: (id: number) =>
    apiClient.delete(`/tags/${id}`),
};
