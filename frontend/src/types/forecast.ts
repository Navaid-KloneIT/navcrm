export interface SalesTarget {
  id: number;
  user_id: number | null;
  period_type: 'monthly' | 'quarterly' | 'yearly';
  period_start: string;
  period_end: string;
  target_amount: number;
  currency: string;
  category: string | null;
  user: { id: number; name: string } | null;
  created_at: string;
  updated_at: string;
}

export interface ForecastSummary {
  period_start: string;
  period_end: string;
  total_pipeline: number;
  weighted_pipeline: number;
  closed_won: number;
  closed_lost: number;
  open_deals: number;
}

export interface PipelineStageData {
  stage_id: number;
  stage_name: string;
  color: string;
  count: number;
  total_amount: number;
  weighted_amount: number;
}

export interface TargetVsActual {
  target_id: number;
  user_id: number | null;
  user_name: string;
  period_type: string;
  period_start: string;
  period_end: string;
  target_amount: number;
  actual_amount: number;
  pipeline_amount: number;
  attainment_percent: number;
  category: string | null;
}

export interface SalesTargetFilters {
  user_id?: number;
  period_type?: string;
  period_start?: string;
  period_end?: string;
  page?: number;
  per_page?: number;
}
