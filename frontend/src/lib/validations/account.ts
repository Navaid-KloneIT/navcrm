import { z } from 'zod';

export const accountFormSchema = z.object({
  name: z.string().min(1, 'Company name is required').max(255),
  industry: z.string().max(255).optional().or(z.literal('')),
  website: z.string().url('Invalid URL').max(255).optional().or(z.literal('')),
  phone: z.string().max(50).optional().or(z.literal('')),
  email: z.string().email('Invalid email').max(255).optional().or(z.literal('')),
  annual_revenue: z.number().min(0).nullable().optional(),
  employee_count: z.number().int().min(0).nullable().optional(),
  tax_id: z.string().max(255).optional().or(z.literal('')),
  description: z.string().max(5000).optional().or(z.literal('')),
  parent_id: z.number().nullable().optional(),
  owner_id: z.number().nullable().optional(),
});

export type AccountFormData = z.infer<typeof accountFormSchema>;
