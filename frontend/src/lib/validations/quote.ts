import { z } from 'zod';

export const quoteLineItemSchema = z.object({
  product_id: z.number().nullable().optional(),
  description: z.string().min(1, 'Description is required').max(500),
  quantity: z.number().min(0.01, 'Quantity must be greater than 0'),
  unit_price: z.number().min(0, 'Price must be positive'),
  discount_percent: z.number().min(0).max(100).optional(),
});

export const quoteFormSchema = z.object({
  opportunity_id: z.number().nullable().optional(),
  account_id: z.number().nullable().optional(),
  contact_id: z.number().nullable().optional(),
  valid_until: z.string().optional().or(z.literal('')),
  discount_type: z.enum(['percentage', 'fixed']).optional(),
  discount_value: z.number().min(0).optional(),
  tax_rate: z.number().min(0).max(100).optional(),
  notes: z.string().max(5000).optional().or(z.literal('')),
  terms: z.string().max(5000).optional().or(z.literal('')),
  line_items: z.array(quoteLineItemSchema).min(1, 'At least one line item is required'),
});

export type QuoteFormData = z.infer<typeof quoteFormSchema>;
export type QuoteLineItemFormData = z.infer<typeof quoteLineItemSchema>;
