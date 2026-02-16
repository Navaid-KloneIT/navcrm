import type { Contact } from './contact';

export interface Address {
  id: number;
  type: 'billing' | 'shipping' | 'other';
  label: string | null;
  address_line_1: string;
  address_line_2: string | null;
  city: string;
  state: string | null;
  postal_code: string | null;
  country: string;
  is_primary: boolean;
}

export interface Account {
  id: number;
  name: string;
  industry: string | null;
  website: string | null;
  phone: string | null;
  email: string | null;
  annual_revenue: number | null;
  employee_count: number | null;
  tax_id: string | null;
  description: string | null;
  parent_id: number | null;
  parent?: Account | null;
  children?: Account[];
  owner: { id: number; name: string } | null;
  contacts?: Contact[];
  addresses?: Address[];
  created_at: string;
  updated_at: string;
}

export interface AccountFilters {
  search?: string;
  industry?: string;
  owner_id?: number;
  sort_by?: string;
  sort_dir?: 'asc' | 'desc';
  page?: number;
  per_page?: number;
}
