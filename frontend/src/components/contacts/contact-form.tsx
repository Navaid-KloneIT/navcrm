'use client';

import React from 'react';
import { useForm } from 'react-hook-form';
import { zodResolver } from '@hookform/resolvers/zod';
import { contactFormSchema, type ContactFormData } from '@/lib/validations/contact';
import { Input } from '@/components/ui/input';
import { Textarea } from '@/components/ui/textarea';
import { Select } from '@/components/ui/select';
import { Button } from '@/components/ui/button';

interface ContactFormProps {
  defaultValues?: Partial<ContactFormData>;
  onSubmit: (data: ContactFormData) => Promise<void>;
  onCancel: () => void;
  loading?: boolean;
}

const sourceOptions = [
  { value: 'website', label: 'Website' },
  { value: 'referral', label: 'Referral' },
  { value: 'linkedin', label: 'LinkedIn' },
  { value: 'cold_call', label: 'Cold Call' },
  { value: 'trade_show', label: 'Trade Show' },
  { value: 'other', label: 'Other' },
];

function ContactForm({ defaultValues, onSubmit, onCancel, loading = false }: ContactFormProps) {
  const {
    register,
    handleSubmit,
    formState: { errors },
  } = useForm<ContactFormData>({
    resolver: zodResolver(contactFormSchema),
    defaultValues: {
      first_name: '',
      last_name: '',
      email: '',
      phone: '',
      mobile: '',
      job_title: '',
      department: '',
      linkedin_url: '',
      twitter_handle: '',
      facebook_url: '',
      address_line_1: '',
      address_line_2: '',
      city: '',
      state: '',
      postal_code: '',
      country: '',
      source: '',
      description: '',
      ...defaultValues,
    },
  });

  return (
    <form onSubmit={handleSubmit(onSubmit)} className="space-y-8">
      <div>
        <h3 className="mb-4 text-lg font-medium text-gray-900">Basic Information</h3>
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
            label="Mobile"
            type="tel"
            placeholder="(555) 987-6543"
            error={errors.mobile?.message}
            {...register('mobile')}
          />
        </div>
      </div>

      <div>
        <h3 className="mb-4 text-lg font-medium text-gray-900">Professional</h3>
        <div className="grid gap-4 sm:grid-cols-2">
          <Input
            label="Job Title"
            placeholder="Software Engineer"
            error={errors.job_title?.message}
            {...register('job_title')}
          />
          <Input
            label="Department"
            placeholder="Engineering"
            error={errors.department?.message}
            {...register('department')}
          />
        </div>
      </div>

      <div>
        <h3 className="mb-4 text-lg font-medium text-gray-900">Social</h3>
        <div className="grid gap-4 sm:grid-cols-2">
          <Input
            label="LinkedIn URL"
            placeholder="https://linkedin.com/in/johndoe"
            error={errors.linkedin_url?.message}
            {...register('linkedin_url')}
          />
          <Input
            label="Twitter Handle"
            placeholder="@johndoe"
            error={errors.twitter_handle?.message}
            {...register('twitter_handle')}
          />
          <Input
            label="Facebook URL"
            placeholder="https://facebook.com/johndoe"
            error={errors.facebook_url?.message}
            {...register('facebook_url')}
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
        <h3 className="mb-4 text-lg font-medium text-gray-900">Other</h3>
        <div className="grid gap-4 sm:grid-cols-2">
          <Select
            label="Source"
            options={sourceOptions}
            placeholder="Select source"
            error={errors.source?.message}
            {...register('source')}
          />
        </div>
        <div className="mt-4">
          <Textarea
            label="Description"
            placeholder="Additional notes about this contact..."
            error={errors.description?.message}
            {...register('description')}
          />
        </div>
      </div>

      <div className="flex justify-end gap-3 border-t border-gray-200 pt-6">
        <Button type="button" variant="outline" onClick={onCancel}>
          Cancel
        </Button>
        <Button type="submit" disabled={loading}>
          {loading ? 'Saving...' : 'Save Contact'}
        </Button>
      </div>
    </form>
  );
}

export { ContactForm };
