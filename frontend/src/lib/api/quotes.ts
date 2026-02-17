import apiClient from './client';
import type { Quote, QuoteFilters, QuoteStatus, PaginatedResponse, ApiResponse } from '@/types';

export const quotesApi = {
  list: (params?: QuoteFilters) =>
    apiClient.get<PaginatedResponse<Quote>>('/quotes', { params }),

  get: (id: number) =>
    apiClient.get<ApiResponse<Quote>>(`/quotes/${id}`),

  create: (data: {
    opportunity_id?: number;
    account_id?: number;
    contact_id?: number;
    valid_until?: string;
    discount_type?: string;
    discount_value?: number;
    tax_rate?: number;
    notes?: string;
    terms?: string;
    line_items: {
      product_id?: number;
      description: string;
      quantity: number;
      unit_price: number;
      discount_percent?: number;
    }[];
  }) =>
    apiClient.post<ApiResponse<Quote>>('/quotes', data),

  update: (id: number, data: Record<string, unknown>) =>
    apiClient.put<ApiResponse<Quote>>(`/quotes/${id}`, data),

  delete: (id: number) =>
    apiClient.delete(`/quotes/${id}`),

  updateStatus: (id: number, status: QuoteStatus) =>
    apiClient.put<ApiResponse<Quote>>(`/quotes/${id}/status`, { status }),

  downloadPdf: (id: number) =>
    apiClient.get(`/quotes/${id}/pdf`, { responseType: 'blob' }),
};
