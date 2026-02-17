'use client';

import React from 'react';
import { useForm } from 'react-hook-form';
import { zodResolver } from '@hookform/resolvers/zod';
import { productFormSchema, type ProductFormData } from '@/lib/validations/product';
import { PRODUCT_UNITS } from '@/lib/utils/constants';
import { Input } from '@/components/ui/input';
import { Textarea } from '@/components/ui/textarea';
import { Select } from '@/components/ui/select';
import { Button } from '@/components/ui/button';

interface ProductFormProps {
  defaultValues?: Partial<ProductFormData>;
  onSubmit: (data: ProductFormData) => Promise<void>;
  onCancel: () => void;
  loading?: boolean;
}

const currencyOptions = [
  { value: 'USD', label: 'USD - US Dollar' },
  { value: 'EUR', label: 'EUR - Euro' },
  { value: 'GBP', label: 'GBP - British Pound' },
  { value: 'CAD', label: 'CAD - Canadian Dollar' },
  { value: 'AUD', label: 'AUD - Australian Dollar' },
];

const unitOptions = PRODUCT_UNITS.map((u) => ({ value: u.value, label: u.label }));

function ProductForm({ defaultValues, onSubmit, onCancel, loading = false }: ProductFormProps) {
  const {
    register,
    handleSubmit,
    formState: { errors },
  } = useForm<ProductFormData>({
    resolver: zodResolver(productFormSchema),
    defaultValues: {
      name: '',
      sku: '',
      description: '',
      unit_price: 0,
      cost_price: null,
      currency: 'USD',
      unit: 'each',
      is_active: true,
      category: '',
      ...defaultValues,
    },
  });

  return (
    <form onSubmit={handleSubmit(onSubmit)} className="space-y-8">
      <div>
        <h3 className="mb-4 text-lg font-medium text-gray-900">Product Information</h3>
        <div className="grid gap-4 sm:grid-cols-2">
          <Input
            label="Name"
            placeholder="Product name"
            error={errors.name?.message}
            {...register('name')}
          />
          <Input
            label="SKU"
            placeholder="PRD-001"
            error={errors.sku?.message}
            {...register('sku')}
          />
          <Input
            label="Category"
            placeholder="e.g. Software, Hardware, Service"
            error={errors.category?.message}
            {...register('category')}
          />
          <Select
            label="Unit"
            options={unitOptions}
            placeholder="Select unit"
            error={errors.unit?.message}
            {...register('unit')}
          />
          <div className="sm:col-span-2">
            <Textarea
              label="Description"
              placeholder="Product description..."
              error={errors.description?.message}
              {...register('description')}
            />
          </div>
        </div>
      </div>

      <div>
        <h3 className="mb-4 text-lg font-medium text-gray-900">Pricing</h3>
        <div className="grid gap-4 sm:grid-cols-3">
          <Input
            label="Unit Price"
            type="number"
            step="0.01"
            min="0"
            placeholder="0.00"
            error={errors.unit_price?.message}
            {...register('unit_price', { valueAsNumber: true })}
          />
          <Input
            label="Cost Price"
            type="number"
            step="0.01"
            min="0"
            placeholder="0.00"
            error={errors.cost_price?.message}
            {...register('cost_price', { valueAsNumber: true })}
          />
          <Select
            label="Currency"
            options={currencyOptions}
            error={errors.currency?.message}
            {...register('currency')}
          />
        </div>
      </div>

      <div>
        <label className="flex items-center gap-2">
          <input
            type="checkbox"
            className="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500"
            {...register('is_active')}
          />
          <span className="text-sm font-medium text-gray-700">Active</span>
        </label>
        <p className="mt-1 text-xs text-gray-500">
          Inactive products will not appear in quote line item selections.
        </p>
      </div>

      <div className="flex justify-end gap-3 border-t border-gray-200 pt-6">
        <Button type="button" variant="outline" onClick={onCancel}>
          Cancel
        </Button>
        <Button type="submit" disabled={loading}>
          {loading ? 'Saving...' : 'Save Product'}
        </Button>
      </div>
    </form>
  );
}

export { ProductForm };
