import { z } from 'zod';

export const contactFormSchema = z.object({
  first_name: z.string().min(1, 'First name is required').max(255),
  last_name: z.string().min(1, 'Last name is required').max(255),
  email: z.string().email('Invalid email').max(255).optional().or(z.literal('')),
  phone: z.string().max(50).optional().or(z.literal('')),
  mobile: z.string().max(50).optional().or(z.literal('')),
  job_title: z.string().max(255).optional().or(z.literal('')),
  department: z.string().max(255).optional().or(z.literal('')),
  description: z.string().max(5000).optional().or(z.literal('')),
  linkedin_url: z.string().url('Invalid URL').max(255).optional().or(z.literal('')),
  twitter_handle: z.string().max(255).optional().or(z.literal('')),
  facebook_url: z.string().url('Invalid URL').max(255).optional().or(z.literal('')),
  address_line_1: z.string().max(255).optional().or(z.literal('')),
  address_line_2: z.string().max(255).optional().or(z.literal('')),
  city: z.string().max(255).optional().or(z.literal('')),
  state: z.string().max(255).optional().or(z.literal('')),
  postal_code: z.string().max(20).optional().or(z.literal('')),
  country: z.string().max(100).optional().or(z.literal('')),
  source: z.string().max(100).optional().or(z.literal('')),
  owner_id: z.number().nullable().optional(),
});

export type ContactFormData = z.infer<typeof contactFormSchema>;
