import { z } from 'zod';

export const productFormSchema = z.object({
  name: z.string().min(1, 'Name is required').max(255),
  sku: z.string().max(100).optional().or(z.literal('')),
  description: z.string().max(5000).optional().or(z.literal('')),
  unit_price: z.number().min(0, 'Price must be positive'),
  cost_price: z.number().min(0).nullable().optional(),
  currency: z.string().length(3).optional(),
  unit: z.string().max(50).optional(),
  is_active: z.boolean().optional(),
  category: z.string().max(255).optional().or(z.literal('')),
});

export type ProductFormData = z.infer<typeof productFormSchema>;
