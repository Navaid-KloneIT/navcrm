'use client';

import React from 'react';
import { useForm } from 'react-hook-form';
import { zodResolver } from '@hookform/resolvers/zod';
import {
  opportunityFormSchema,
  type OpportunityFormData,
} from '@/lib/validations/opportunity';
import { Input } from '@/components/ui/input';
import { Textarea } from '@/components/ui/textarea';
import { Select } from '@/components/ui/select';
import { Button } from '@/components/ui/button';
import { OPPORTUNITY_SOURCES } from '@/lib/utils/constants';
import type { PipelineStage } from '@/types';

interface OpportunityFormProps {
  defaultValues?: Partial<OpportunityFormData>;
  onSubmit: (data: OpportunityFormData) => Promise<void>;
  onCancel: () => void;
  loading?: boolean;
  stages: PipelineStage[];
  accounts: { id: number; name: string }[];
  contacts: { id: number; name: string }[];
}

function OpportunityForm({
  defaultValues,
  onSubmit,
  onCancel,
  loading = false,
  stages,
  accounts,
  contacts,
}: OpportunityFormProps) {
  const {
    register,
    handleSubmit,
    formState: { errors },
  } = useForm<OpportunityFormData>({
    resolver: zodResolver(opportunityFormSchema),
    defaultValues: {
      name: '',
      amount: 0,
      close_date: '',
      probability: 0,
      pipeline_stage_id: undefined as unknown as number,
      account_id: null,
      contact_id: null,
      owner_id: null,
      description: '',
      next_steps: '',
      competitor: '',
      source: '',
      ...defaultValues,
    },
  });

  const stageOptions = stages.map((s) => ({ value: s.id, label: s.name }));
  const accountOptions = accounts.map((a) => ({ value: a.id, label: a.name }));
  const contactOptions = contacts.map((c) => ({ value: c.id, label: c.name }));

  return (
    <form onSubmit={handleSubmit(onSubmit)} className="space-y-8">
      <div>
        <h3 className="mb-4 text-lg font-medium text-gray-900">
          Deal Information
        </h3>
        <div className="grid gap-4 sm:grid-cols-2">
          <Input
            label="Deal Name"
            placeholder="Enterprise License Deal"
            error={errors.name?.message}
            {...register('name')}
          />
          <Input
            label="Amount"
            type="number"
            step="0.01"
            placeholder="0.00"
            error={errors.amount?.message}
            {...register('amount', { valueAsNumber: true })}
          />
          <Input
            label="Close Date"
            type="date"
            error={errors.close_date?.message}
            {...register('close_date')}
          />
          <Input
            label="Probability %"
            type="number"
            min="0"
            max="100"
            placeholder="50"
            error={errors.probability?.message}
            {...register('probability', { valueAsNumber: true })}
          />
          <Select
            label="Stage"
            options={stageOptions}
            placeholder="Select stage"
            error={errors.pipeline_stage_id?.message}
            {...register('pipeline_stage_id', { valueAsNumber: true })}
          />
          <Select
            label="Source"
            options={OPPORTUNITY_SOURCES.map((s) => ({
              value: s.value,
              label: s.label,
            }))}
            placeholder="Select source"
            error={errors.source?.message}
            {...register('source')}
          />
        </div>
      </div>

      <div>
        <h3 className="mb-4 text-lg font-medium text-gray-900">
          Related Records
        </h3>
        <div className="grid gap-4 sm:grid-cols-3">
          <Select
            label="Account"
            options={accountOptions}
            placeholder="Select account"
            error={errors.account_id?.message}
            {...register('account_id')}
          />
          <Select
            label="Contact"
            options={contactOptions}
            placeholder="Select contact"
            error={errors.contact_id?.message}
            {...register('contact_id')}
          />
          <Input
            label="Owner"
            type="hidden"
            {...register('owner_id')}
          />
        </div>
      </div>

      <div>
        <h3 className="mb-4 text-lg font-medium text-gray-900">
          Additional Info
        </h3>
        <div className="grid gap-4">
          <Textarea
            label="Description"
            placeholder="Describe the opportunity..."
            error={errors.description?.message}
            {...register('description')}
          />
          <Textarea
            label="Next Steps"
            placeholder="What are the next steps?"
            error={errors.next_steps?.message}
            {...register('next_steps')}
          />
          <Input
            label="Competitor"
            placeholder="Main competitor"
            error={errors.competitor?.message}
            {...register('competitor')}
          />
        </div>
      </div>

      <div className="flex justify-end gap-3 border-t border-gray-200 pt-6">
        <Button type="button" variant="outline" onClick={onCancel}>
          Cancel
        </Button>
        <Button type="submit" disabled={loading}>
          {loading ? 'Saving...' : 'Save Opportunity'}
        </Button>
      </div>
    </form>
  );
}

export { OpportunityForm };
