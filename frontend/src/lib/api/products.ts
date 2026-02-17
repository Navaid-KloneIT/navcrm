import apiClient from './client';
import type { Product, ProductFilters, PaginatedResponse, ApiResponse } from '@/types';

export const productsApi = {
  list: (params?: ProductFilters) =>
    apiClient.get<PaginatedResponse<Product>>('/products', { params }),

  get: (id: number) =>
    apiClient.get<ApiResponse<Product>>(`/products/${id}`),

  create: (data: Partial<Product>) =>
    apiClient.post<ApiResponse<Product>>('/products', data),

  update: (id: number, data: Partial<Product>) =>
    apiClient.put<ApiResponse<Product>>(`/products/${id}`, data),

  delete: (id: number) =>
    apiClient.delete(`/products/${id}`),
};
