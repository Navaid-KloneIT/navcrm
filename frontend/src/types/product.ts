export interface Product {
  id: number;
  name: string;
  sku: string | null;
  description: string | null;
  unit_price: number;
  cost_price: number | null;
  currency: string;
  unit: string;
  is_active: boolean;
  category: string | null;
  created_at: string;
  updated_at: string;
}

export interface ProductFilters {
  search?: string;
  is_active?: boolean;
  category?: string;
  sort_by?: string;
  sort_dir?: 'asc' | 'desc';
  page?: number;
  per_page?: number;
}
