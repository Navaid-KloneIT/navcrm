export type ActivityType = 'call' | 'email' | 'meeting' | 'task' | 'note' | 'status_change';

export interface Activity {
  id: number;
  type: ActivityType;
  subject: string;
  description: string | null;
  activitable_type: string;
  activitable_id: number;
  user: { id: number; name: string } | null;
  occurred_at: string | null;
  metadata: Record<string, unknown> | null;
  created_at: string;
}
