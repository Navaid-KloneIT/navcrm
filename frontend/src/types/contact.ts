import type { Tag } from './tag';
import type { Activity } from './activity';

export interface Contact {
  id: number;
  first_name: string;
  last_name: string;
  full_name: string;
  email: string | null;
  phone: string | null;
  mobile: string | null;
  job_title: string | null;
  department: string | null;
  description: string | null;
  linkedin_url: string | null;
  twitter_handle: string | null;
  facebook_url: string | null;
  address: {
    line_1: string | null;
    line_2: string | null;
    city: string | null;
    state: string | null;
    postal_code: string | null;
    country: string | null;
  };
  source: string | null;
  owner: { id: number; name: string } | null;
  tags: Tag[];
  activities?: Activity[];
  created_at: string;
  updated_at: string;
}

export interface ContactFilters {
  search?: string;
  tag_id?: number;
  owner_id?: number;
  source?: string;
  sort_by?: string;
  sort_dir?: 'asc' | 'desc';
  page?: number;
  per_page?: number;
}
