import type { Tag } from './tag';

export type LeadStatus = 'new' | 'contacted' | 'qualified' | 'converted' | 'recycled';
export type LeadScore = 'hot' | 'warm' | 'cold';

export interface Lead {
  id: number;
  first_name: string;
  last_name: string;
  full_name: string;
  email: string | null;
  phone: string | null;
  company_name: string | null;
  job_title: string | null;
  website: string | null;
  description: string | null;
  status: LeadStatus;
  score: LeadScore;
  source: string | null;
  is_converted: boolean;
  converted_at: string | null;
  converted_contact_id: number | null;
  converted_account_id: number | null;
  address: {
    line_1: string | null;
    line_2: string | null;
    city: string | null;
    state: string | null;
    postal_code: string | null;
    country: string | null;
  };
  owner: { id: number; name: string } | null;
  tags: Tag[];
  created_at: string;
  updated_at: string;
}

export interface LeadFilters {
  search?: string;
  status?: LeadStatus;
  score?: LeadScore;
  source?: string;
  owner_id?: number;
  is_converted?: boolean;
  sort_by?: string;
  sort_dir?: 'asc' | 'desc';
  page?: number;
  per_page?: number;
}
