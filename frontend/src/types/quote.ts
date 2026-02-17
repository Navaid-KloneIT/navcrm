import type { Product } from './product';

export type QuoteStatus = 'draft' | 'sent' | 'accepted' | 'rejected' | 'expired';

export interface QuoteLineItem {
  id: number;
  product_id: number | null;
  description: string;
  quantity: number;
  unit_price: number;
  discount_percent: number;
  subtotal: number;
  sort_order: number;
  product?: Product;
}

export interface Quote {
  id: number;
  quote_number: string;
  status: QuoteStatus;
  valid_until: string | null;
  subtotal: number;
  discount_type: 'percentage' | 'fixed';
  discount_value: number;
  discount_amount: number;
  tax_rate: number;
  tax_amount: number;
  total: number;
  notes: string | null;
  terms: string | null;
  opportunity: { id: number; name: string } | null;
  account: { id: number; name: string } | null;
  contact: { id: number; first_name: string; last_name: string; full_name: string } | null;
  prepared_by: { id: number; name: string } | null;
  line_items?: QuoteLineItem[];
  created_at: string;
  updated_at: string;
}

export interface QuoteFilters {
  search?: string;
  status?: QuoteStatus;
  opportunity_id?: number;
  account_id?: number;
  sort_by?: string;
  sort_dir?: 'asc' | 'desc';
  page?: number;
  per_page?: number;
}

export interface PriceBook {
  id: number;
  name: string;
  description: string | null;
  is_default: boolean;
  is_active: boolean;
  entries?: PriceBookEntry[];
  entries_count?: number;
  created_at: string;
  updated_at: string;
}

export interface PriceBookEntry {
  id: number;
  price_book_id: number;
  product_id: number;
  unit_price: number;
  min_quantity: number;
  is_active: boolean;
  product?: Product;
  created_at: string;
  updated_at: string;
}
