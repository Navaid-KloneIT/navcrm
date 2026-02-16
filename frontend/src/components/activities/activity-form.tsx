'use client';

import React, { useState } from 'react';
import { activitiesApi } from '@/lib/api/activities';
import { useToast } from '@/components/ui/toast';
import { Input } from '@/components/ui/input';
import { Textarea } from '@/components/ui/textarea';
import { Select } from '@/components/ui/select';
import { Button } from '@/components/ui/button';
import { ACTIVITY_TYPES } from '@/lib/utils/constants';
import type { AxiosError } from 'axios';
import type { ApiError } from '@/types';

interface ActivityFormProps {
  activitableType: string;
  activitableId: number;
  onCreated: () => void;
  onCancel?: () => void;
}

function ActivityForm({ activitableType, activitableId, onCreated, onCancel }: ActivityFormProps) {
  const { toast } = useToast();
  const [loading, setLoading] = useState(false);
  const [form, setForm] = useState({
    type: 'note',
    subject: '',
    description: '',
    occurred_at: '',
  });

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!form.subject.trim()) {
      toast('Subject is required', 'error');
      return;
    }

    setLoading(true);
    try {
      await activitiesApi.create({
        type: form.type,
        subject: form.subject,
        description: form.description || undefined,
        activitable_type: activitableType,
        activitable_id: activitableId,
        occurred_at: form.occurred_at || undefined,
      });
      toast('Activity added successfully', 'success');
      setForm({ type: 'note', subject: '', description: '', occurred_at: '' });
      onCreated();
    } catch (err) {
      const error = err as AxiosError<ApiError>;
      toast(error.response?.data?.message || 'Failed to add activity', 'error');
    } finally {
      setLoading(false);
    }
  };

  return (
    <form onSubmit={handleSubmit} className="space-y-4">
      <div className="grid gap-4 sm:grid-cols-2">
        <Select
          label="Type"
          options={ACTIVITY_TYPES.map((t) => ({ value: t.value, label: t.label }))}
          value={form.type}
          onChange={(e) => setForm({ ...form, type: e.target.value })}
        />
        <Input
          label="Date"
          type="datetime-local"
          value={form.occurred_at}
          onChange={(e) => setForm({ ...form, occurred_at: e.target.value })}
        />
      </div>
      <Input
        label="Subject"
        placeholder="Activity subject..."
        value={form.subject}
        onChange={(e) => setForm({ ...form, subject: e.target.value })}
      />
      <Textarea
        label="Description"
        placeholder="Additional details..."
        value={form.description}
        onChange={(e) => setForm({ ...form, description: e.target.value })}
      />
      <div className="flex justify-end gap-3">
        {onCancel && (
          <Button type="button" variant="outline" onClick={onCancel}>
            Cancel
          </Button>
        )}
        <Button type="submit" disabled={loading}>
          {loading ? 'Adding...' : 'Add Activity'}
        </Button>
      </div>
    </form>
  );
}

export { ActivityForm };
