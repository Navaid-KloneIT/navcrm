import apiClient from './client';

export interface Role {
  id: number;
  name: string;
  guard_name: string;
  permissions: { id: number; name: string }[];
}

export interface Permission {
  id: number;
  name: string;
  guard_name: string;
}

export const rolesApi = {
  list: () =>
    apiClient.get<{ data: Role[] }>('/roles'),

  get: (id: number) =>
    apiClient.get<{ data: Role }>(`/roles/${id}`),

  create: (data: { name: string; permission_ids?: number[] }) =>
    apiClient.post<{ data: Role }>('/roles', data),

  update: (id: number, data: { name?: string; permission_ids?: number[] }) =>
    apiClient.put<{ data: Role }>(`/roles/${id}`, data),

  delete: (id: number) =>
    apiClient.delete(`/roles/${id}`),

  listPermissions: () =>
    apiClient.get<{ data: Permission[] }>('/permissions'),
};
