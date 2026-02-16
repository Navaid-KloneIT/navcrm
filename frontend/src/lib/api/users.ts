import apiClient from './client';
import type { User, PaginatedResponse, ApiResponse } from '@/types';

export const usersApi = {
  list: (params?: { page?: number; per_page?: number }) =>
    apiClient.get<PaginatedResponse<User>>('/users', { params }),

  get: (id: number) =>
    apiClient.get<ApiResponse<User>>(`/users/${id}`),

  create: (data: { name: string; email: string; password: string; password_confirmation: string }) =>
    apiClient.post<ApiResponse<User>>('/users', data),

  update: (id: number, data: Partial<User>) =>
    apiClient.put<ApiResponse<User>>(`/users/${id}`, data),

  delete: (id: number) =>
    apiClient.delete(`/users/${id}`),

  syncRoles: (id: number, roleIds: number[]) =>
    apiClient.post(`/users/${id}/roles`, { role_ids: roleIds }),

  // Profile
  getProfile: () =>
    apiClient.get<ApiResponse<User>>('/profile'),

  updateProfile: (data: { name?: string; email?: string; phone?: string }) =>
    apiClient.put<ApiResponse<User>>('/profile', data),

  updatePassword: (data: { current_password: string; password: string; password_confirmation: string }) =>
    apiClient.put('/profile/password', data),
};
