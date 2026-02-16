import apiClient from './client';
import type { Contact, ContactFilters, PaginatedResponse, ApiResponse } from '@/types';

export const contactsApi = {
  list: (params?: ContactFilters) =>
    apiClient.get<PaginatedResponse<Contact>>('/contacts', { params }),

  get: (id: number) =>
    apiClient.get<ApiResponse<Contact>>(`/contacts/${id}`),

  create: (data: Partial<Contact>) =>
    apiClient.post<ApiResponse<Contact>>('/contacts', data),

  update: (id: number, data: Partial<Contact>) =>
    apiClient.put<ApiResponse<Contact>>(`/contacts/${id}`, data),

  delete: (id: number) =>
    apiClient.delete(`/contacts/${id}`),

  syncTags: (id: number, tagIds: number[]) =>
    apiClient.post(`/contacts/${id}/tags`, { tag_ids: tagIds }),

  getRelationships: (id: number) =>
    apiClient.get(`/contacts/${id}/relationships`),

  addRelationship: (id: number, relatedId: number, type: string) =>
    apiClient.post(`/contacts/${id}/relationships`, {
      related_contact_id: relatedId,
      relationship_type: type,
    }),

  removeRelationship: (id: number, relatedId: number) =>
    apiClient.delete(`/contacts/${id}/relationships/${relatedId}`),
};
