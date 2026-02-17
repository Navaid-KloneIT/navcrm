'use client';

import React, { useEffect, useState } from 'react';
import { useForm } from 'react-hook-form';
import { zodResolver } from '@hookform/resolvers/zod';
import { accountFormSchema, type AccountFormData } from '@/lib/validations/account';
import { accountsApi } from '@/lib/api/accounts';
import { Input } from '@/components/ui/input';
import { Textarea } from '@/components/ui/textarea';
import { Select } from '@/components/ui/select';
import { Button } from '@/components/ui/button';
import type { Account } from '@/types';

interface AccountFormProps {
  defaultValues?: Partial<AccountFormData>;
  onSubmit: (data: AccountFormData) => Promise<void>;
  onCancel: () => void;
  loading?: boolean;
  excludeAccountId?: number;
}

const industryOptions = [
  { value: 'technology', label: 'Technology' },
  { value: 'healthcare', label: 'Healthcare' },
  { value: 'finance', label: 'Finance' },
  { value: 'manufacturing', label: 'Manufacturing' },
  { value: 'retail', label: 'Retail' },
  { value: 'education', label: 'Education' },
  { value: 'real_estate', label: 'Real Estate' },
  { value: 'consulting', label: 'Consulting' },
  { value: 'media', label: 'Media' },
  { value: 'other', label: 'Other' },
];

function AccountForm({ defaultValues, onSubmit, onCancel, loading = false, excludeAccountId }: AccountFormProps) {
  const [parentAccounts, setParentAccounts] = useState<{ value: string | number; label: string }[]>([]);

  const {
    register,
    handleSubmit,
    formState: { errors },
  } = useForm<AccountFormData>({
    resolver: zodResolver(accountFormSchema),
    defaultValues: {
      name: '',
      industry: '',
      website: '',
      phone: '',
      email: '',
      annual_revenue: null,
      employee_count: null,
      tax_id: '',
      description: '',
      parent_id: null,
      ...defaultValues,
    },
  });

  useEffect(() => {
    const fetchParentAccounts = async () => {
      try {
        const response = await accountsApi.list({ per_page: 100 });
        const options = response.data.data
          .filter((a: Account) => a.id !== excludeAccountId)
          .map((a: Account) => ({
            value: a.id,
            label: a.name,
          }));
        setParentAccounts(options);
      } catch {
        // Silently handle
      }
    };

    fetchParentAccounts();
  }, [excludeAccountId]);

  return (
    <form onSubmit={handleSubmit(onSubmit)} className="space-y-8">
      <div>
        <h3 className="mb-4 text-lg font-medium text-gray-900">Company Information</h3>
        <div className="grid gap-4 sm:grid-cols-2">
          <Input
            label="Company Name"
            placeholder="Acme Inc."
            error={errors.name?.message}
            {...register('name')}
          />
          <Select
            label="Industry"
            options={industryOptions}
            placeholder="Select industry"
            error={errors.industry?.message}
            {...register('industry')}
          />
          <Input
            label="Website"
            placeholder="https://example.com"
            error={errors.website?.message}
            {...register('website')}
          />
          <Input
            label="Phone"
            type="tel"
            placeholder="(555) 123-4567"
            error={errors.phone?.message}
            {...register('phone')}
          />
          <Input
            label="Email"
            type="email"
            placeholder="info@company.com"
            error={errors.email?.message}
            {...register('email')}
          />
          <Input
            label="Annual Revenue"
            type="number"
            placeholder="1000000"
            error={errors.annual_revenue?.message}
            {...register('annual_revenue', { valueAsNumber: true })}
          />
          <Input
            label="Employee Count"
            type="number"
            placeholder="50"
            error={errors.employee_count?.message}
            {...register('employee_count', { valueAsNumber: true })}
          />
          <Input
            label="Tax ID"
            placeholder="XX-XXXXXXX"
            error={errors.tax_id?.message}
            {...register('tax_id')}
          />
        </div>
      </div>

      <div>
        <h3 className="mb-4 text-lg font-medium text-gray-900">Hierarchy</h3>
        <div className="grid gap-4 sm:grid-cols-2">
          <Select
            label="Parent Account"
            options={parentAccounts}
            placeholder="No parent account"
            error={errors.parent_id?.message}
            {...register('parent_id', { setValueAs: (v) => (v === '' ? null : Number(v)) })}
          />
        </div>
      </div>

      <div>
        <Textarea
          label="Description"
          placeholder="Additional notes about this account..."
          error={errors.description?.message}
          {...register('description')}
        />
      </div>

      <div className="flex justify-end gap-3 border-t border-gray-200 pt-6">
        <Button type="button" variant="outline" onClick={onCancel}>
          Cancel
        </Button>
        <Button type="submit" disabled={loading}>
          {loading ? 'Saving...' : 'Save Account'}
        </Button>
      </div>
    </form>
  );
}

export { AccountForm };
