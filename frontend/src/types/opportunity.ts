export interface PipelineStage {
  id: number;
  name: string;
  position: number;
  probability: number;
  is_won: boolean;
  is_lost: boolean;
  color: string;
  opportunities_count?: number;
  created_at: string;
  updated_at: string;
}

export interface OpportunityTeamMember {
  id: number;
  name: string;
  email: string;
  role: 'owner' | 'support' | 'technical';
  split_percentage: number;
}

export interface Opportunity {
  id: number;
  name: string;
  amount: number;
  currency: string;
  close_date: string | null;
  probability: number;
  weighted_amount: number;
  description: string | null;
  next_steps: string | null;
  competitor: string | null;
  source: string | null;
  won_at: string | null;
  lost_at: string | null;
  lost_reason: string | null;
  stage: PipelineStage;
  account: { id: number; name: string } | null;
  contact: { id: number; first_name: string; last_name: string; full_name: string } | null;
  owner: { id: number; name: string } | null;
  team_members?: OpportunityTeamMember[];
  tags?: { id: number; name: string; color: string | null }[];
  quotes_count?: number;
  created_at: string;
  updated_at: string;
}

export interface OpportunityFilters {
  search?: string;
  pipeline_stage_id?: number;
  owner_id?: number;
  account_id?: number;
  closed?: boolean;
  close_date_from?: string;
  close_date_to?: string;
  sort_by?: string;
  sort_dir?: 'asc' | 'desc';
  page?: number;
  per_page?: number;
}
