import apiClient from './client';
import type { Account, AccountFilters, Address, PaginatedResponse, ApiResponse } from '@/types';

export const accountsApi = {
  list: (params?: AccountFilters) =>
    apiClient.get<PaginatedResponse<Account>>('/accounts', { params }),

  get: (id: number) =>
    apiClient.get<ApiResponse<Account>>(`/accounts/${id}`),

  create: (data: Partial<Account>) =>
    apiClient.post<ApiResponse<Account>>('/accounts', data),

  update: (id: number, data: Partial<Account>) =>
    apiClient.put<ApiResponse<Account>>(`/accounts/${id}`, data),

  delete: (id: number) =>
    apiClient.delete(`/accounts/${id}`),

  getContacts: (id: number) =>
    apiClient.get(`/accounts/${id}/contacts`),

  attachContact: (id: number, contactId: number, role?: string, isPrimary?: boolean) =>
    apiClient.post(`/accounts/${id}/contacts`, {
      contact_id: contactId,
      role,
      is_primary: isPrimary,
    }),

  detachContact: (id: number, contactId: number) =>
    apiClient.delete(`/accounts/${id}/contacts/${contactId}`),

  getChildren: (id: number) =>
    apiClient.get(`/accounts/${id}/children`),

  // Addresses
  getAddresses: (accountId: number) =>
    apiClient.get<ApiResponse<Address[]>>(`/accounts/${accountId}/addresses`),

  createAddress: (accountId: number, data: Partial<Address>) =>
    apiClient.post<ApiResponse<Address>>(`/accounts/${accountId}/addresses`, data),

  updateAddress: (addressId: number, data: Partial<Address>) =>
    apiClient.put<ApiResponse<Address>>(`/addresses/${addressId}`, data),

  deleteAddress: (addressId: number) =>
    apiClient.delete(`/addresses/${addressId}`),
};
