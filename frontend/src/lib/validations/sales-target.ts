import { z } from 'zod';

export const salesTargetFormSchema = z.object({
  user_id: z.number().nullable().optional(),
  period_type: z.enum(['monthly', 'quarterly', 'yearly']),
  period_start: z.string().min(1, 'Start date is required'),
  period_end: z.string().min(1, 'End date is required'),
  target_amount: z.number().min(0, 'Amount must be positive'),
  currency: z.string().length(3).optional(),
  category: z.string().max(255).optional().or(z.literal('')),
});

export type SalesTargetFormData = z.infer<typeof salesTargetFormSchema>;
