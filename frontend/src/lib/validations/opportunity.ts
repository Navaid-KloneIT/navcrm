import { z } from 'zod';

export const opportunityFormSchema = z.object({
  name: z.string().min(1, 'Name is required').max(255),
  amount: z.number().min(0, 'Amount must be positive').optional(),
  currency: z.string().length(3).optional(),
  close_date: z.string().optional().or(z.literal('')),
  probability: z.number().min(0).max(100).optional(),
  pipeline_stage_id: z.number().min(1, 'Stage is required'),
  account_id: z.number().nullable().optional(),
  contact_id: z.number().nullable().optional(),
  description: z.string().max(5000).optional().or(z.literal('')),
  next_steps: z.string().max(2000).optional().or(z.literal('')),
  competitor: z.string().max(255).optional().or(z.literal('')),
  source: z.string().max(255).optional().or(z.literal('')),
  owner_id: z.number().nullable().optional(),
});

export type OpportunityFormData = z.infer<typeof opportunityFormSchema>;
