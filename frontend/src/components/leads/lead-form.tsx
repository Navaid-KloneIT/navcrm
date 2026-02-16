'use client';

import React from 'react';
import { useForm } from 'react-hook-form';
import { zodResolver } from '@hookform/resolvers/zod';
import { leadFormSchema, type LeadFormData } from '@/lib/validations/lead';
import { Input } from '@/components/ui/input';
import { Textarea } from '@/components/ui/textarea';
import { Select } from '@/components/ui/select';
import { Button } from '@/components/ui/button';
import { LEAD_STATUSES, LEAD_SCORES } from '@/lib/utils/constants';

interface LeadFormProps {
  defaultValues?: Partial<LeadFormData>;
  onSubmit: (data: LeadFormData) => Promise<void>;
  onCancel: () => void;
  loading?: boolean;
}

const sourceOptions = [
  { value: 'website', label: 'Website' },
  { value: 'referral', label: 'Referral' },
  { value: 'linkedin', label: 'LinkedIn' },
  { value: 'cold_call', label: 'Cold Call' },
  { value: 'trade_show', label: 'Trade Show' },
  { value: 'advertisement', label: 'Advertisement' },
  { value: 'other', label: 'Other' },
];

function LeadForm({ defaultValues, onSubmit, onCancel, loading = false }: LeadFormProps) {
  const {
    register,
    handleSubmit,
    formState: { errors },
  } = useForm<LeadFormData>({
    resolver: zodResolver(leadFormSchema),
    defaultValues: {
      first_name: '',
      last_name: '',
      email: '',
      phone: '',
      company_name: '',
      job_title: '',
      website: '',
      description: '',
      status: 'new',
      score: 'warm',
      source: '',
      address_line_1: '',
      address_line_2: '',
      city: '',
      state: '',
      postal_code: '',
      country: '',
      ...defaultValues,
    },
  });

  return (
    <form onSubmit={handleSubmit(onSubmit)} className="space-y-8">
      <div>
        <h3 className="mb-4 text-lg font-medium text-gray-900">Lead Information</h3>
        <div className="grid gap-4 sm:grid-cols-2">
          <Input
            label="First Name"
            placeholder="John"
            error={errors.first_name?.message}
            {...register('first_name')}
          />
          <Input
            label="Last Name"
            placeholder="Doe"
            error={errors.last_name?.message}
            {...register('last_name')}
          />
          <Input
            label="Email"
            type="email"
            placeholder="john@example.com"
            error={errors.email?.message}
            {...register('email')}
          />
          <Input
            label="Phone"
            type="tel"
            placeholder="(555) 123-4567"
            error={errors.phone?.message}
            {...register('phone')}
          />
          <Input
            label="Company Name"
            placeholder="Acme Inc."
            error={errors.company_name?.message}
            {...register('company_name')}
          />
          <Input
            label="Job Title"
            placeholder="CEO"
            error={errors.job_title?.message}
            {...register('job_title')}
          />
          <Input
            label="Website"
            placeholder="https://example.com"
            error={errors.website?.message}
            {...register('website')}
          />
        </div>
      </div>

      <div>
        <h3 className="mb-4 text-lg font-medium text-gray-900">Qualification</h3>
        <div className="grid gap-4 sm:grid-cols-3">
          <Select
            label="Status"
            options={LEAD_STATUSES.map((s) => ({ value: s.value, label: s.label }))}
            error={errors.status?.message}
            {...register('status')}
          />
          <Select
            label="Score"
            options={LEAD_SCORES.map((s) => ({ value: s.value, label: s.label }))}
            error={errors.score?.message}
            {...register('score')}
          />
          <Select
            label="Source"
            options={sourceOptions}
            placeholder="Select source"
            error={errors.source?.message}
            {...register('source')}
          />
        </div>
      </div>

      <div>
        <h3 className="mb-4 text-lg font-medium text-gray-900">Address</h3>
        <div className="grid gap-4 sm:grid-cols-2">
          <div className="sm:col-span-2">
            <Input
              label="Address Line 1"
              placeholder="123 Main St"
              error={errors.address_line_1?.message}
              {...register('address_line_1')}
            />
          </div>
          <div className="sm:col-span-2">
            <Input
              label="Address Line 2"
              placeholder="Suite 100"
              error={errors.address_line_2?.message}
              {...register('address_line_2')}
            />
          </div>
          <Input
            label="City"
            placeholder="New York"
            error={errors.city?.message}
            {...register('city')}
          />
          <Input
            label="State"
            placeholder="NY"
            error={errors.state?.message}
            {...register('state')}
          />
          <Input
            label="Postal Code"
            placeholder="10001"
            error={errors.postal_code?.message}
            {...register('postal_code')}
          />
          <Input
            label="Country"
            placeholder="United States"
            error={errors.country?.message}
            {...register('country')}
          />
        </div>
      </div>

      <div>
        <Textarea
          label="Description"
          placeholder="Additional notes about this lead..."
          error={errors.description?.message}
          {...register('description')}
        />
      </div>

      <div className="flex justify-end gap-3 border-t border-gray-200 pt-6">
        <Button type="button" variant="outline" onClick={onCancel}>
          Cancel
        </Button>
        <Button type="submit" disabled={loading}>
          {loading ? 'Saving...' : 'Save Lead'}
        </Button>
      </div>
    </form>
  );
}

export { LeadForm };
