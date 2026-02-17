'use client';

import React from 'react';
import { useForm } from 'react-hook-form';
import { zodResolver } from '@hookform/resolvers/zod';
import { salesTargetFormSchema, type SalesTargetFormData } from '@/lib/validations/sales-target';
import { Input } from '@/components/ui/input';
import { Select } from '@/components/ui/select';
import { Button } from '@/components/ui/button';
import { PERIOD_TYPES, FORECAST_CATEGORIES } from '@/lib/utils/constants';

interface TargetFormProps {
  defaultValues?: Partial<SalesTargetFormData>;
  onSubmit: (data: SalesTargetFormData) => Promise<void>;
  onCancel: () => void;
  loading?: boolean;
  users: { id: number; name: string }[];
}

function TargetForm({ defaultValues, onSubmit, onCancel, loading = false, users }: TargetFormProps) {
  const {
    register,
    handleSubmit,
    formState: { errors },
  } = useForm<SalesTargetFormData>({
    resolver: zodResolver(salesTargetFormSchema),
    defaultValues: {
      user_id: null,
      period_type: 'quarterly',
      period_start: '',
      period_end: '',
      target_amount: 0,
      category: '',
      ...defaultValues,
    },
  });

  const userOptions = [
    { value: '', label: 'Team Target (No User)' },
    ...users.map((u) => ({ value: u.id, label: u.name })),
  ];

  return (
    <form onSubmit={handleSubmit(onSubmit)} className="space-y-4">
      <Select
        label="Assigned User"
        options={userOptions}
        placeholder="Select user"
        error={errors.user_id?.message}
        {...register('user_id', { valueAsNumber: true })}
      />

      <div className="grid gap-4 sm:grid-cols-2">
        <Select
          label="Period Type"
          options={PERIOD_TYPES.map((p) => ({ value: p.value, label: p.label }))}
          error={errors.period_type?.message}
          {...register('period_type')}
        />
        <Select
          label="Category"
          options={FORECAST_CATEGORIES.map((c) => ({ value: c.value, label: c.label }))}
          placeholder="Select category"
          error={errors.category?.message}
          {...register('category')}
        />
      </div>

      <div className="grid gap-4 sm:grid-cols-2">
        <Input
          label="Period Start"
          type="date"
          error={errors.period_start?.message}
          {...register('period_start')}
        />
        <Input
          label="Period End"
          type="date"
          error={errors.period_end?.message}
          {...register('period_end')}
        />
      </div>

      <Input
        label="Target Amount ($)"
        type="number"
        step="0.01"
        min="0"
        placeholder="0.00"
        error={errors.target_amount?.message}
        {...register('target_amount', { valueAsNumber: true })}
      />

      <div className="flex justify-end gap-3 border-t border-gray-200 pt-4">
        <Button type="button" variant="outline" onClick={onCancel}>
          Cancel
        </Button>
        <Button type="submit" disabled={loading}>
          {loading ? 'Saving...' : 'Save Target'}
        </Button>
      </div>
    </form>
  );
}

export { TargetForm };
