import apiClient from './client';
import type { PriceBook, PriceBookEntry } from '@/types';

export const priceBooksApi = {
  list: (params?: { is_active?: boolean }) =>
    apiClient.get<{ data: PriceBook[] }>('/price-books', { params }),

  get: (id: number) =>
    apiClient.get<{ data: PriceBook }>(`/price-books/${id}`),

  create: (data: Partial<PriceBook>) =>
    apiClient.post<{ data: PriceBook }>('/price-books', data),

  update: (id: number, data: Partial<PriceBook>) =>
    apiClient.put<{ data: PriceBook }>(`/price-books/${id}`, data),

  delete: (id: number) =>
    apiClient.delete(`/price-books/${id}`),

  addEntry: (priceBookId: number, data: { product_id: number; unit_price: number; min_quantity?: number }) =>
    apiClient.post<{ data: PriceBookEntry }>(`/price-books/${priceBookId}/entries`, data),

  updateEntry: (entryId: number, data: { unit_price?: number; min_quantity?: number; is_active?: boolean }) =>
    apiClient.put<{ data: PriceBookEntry }>(`/price-book-entries/${entryId}`, data),

  removeEntry: (entryId: number) =>
    apiClient.delete(`/price-book-entries/${entryId}`),
};
