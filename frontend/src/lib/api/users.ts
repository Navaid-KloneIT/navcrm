import apiClient from './client';
import type { User, PaginatedResponse } from '@/types';

export const usersApi = {
  list: (params?: { page?: number; per_page?: number }) =>
    apiClient.get<PaginatedResponse<User>>('/users', { params }),

  get: (id: number) =>
    apiClient.get<{ user: User }>(`/users/${id}`),

  create: (data: { name: string; email: string; password: string; password_confirmation: string }) =>
    apiClient.post<{ user: User }>('/users', data),

  update: (id: number, data: Partial<User>) =>
    apiClient.put<{ user: User }>(`/users/${id}`, data),

  delete: (id: number) =>
    apiClient.delete(`/users/${id}`),

  syncRoles: (id: number, roleIds: number[]) =>
    apiClient.post(`/users/${id}/sync-roles`, { role_ids: roleIds }),

  // Profile
  getProfile: () =>
    apiClient.get<{ user: User }>('/profile'),

  updateProfile: (data: { name?: string; email?: string; phone?: string }) =>
    apiClient.put<{ user: User }>('/profile', data),

  updatePassword: (data: { current_password: string; password: string; password_confirmation: string }) =>
    apiClient.put('/profile/password', data),
};
