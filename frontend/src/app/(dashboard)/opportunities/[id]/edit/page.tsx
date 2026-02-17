'use client';

import React, { useEffect, useState, useCallback } from 'react';
import { useParams, useRouter } from 'next/navigation';
import { opportunitiesApi } from '@/lib/api/opportunities';
import { pipelineStagesApi } from '@/lib/api/pipeline-stages';
import { accountsApi } from '@/lib/api/accounts';
import { contactsApi } from '@/lib/api/contacts';
import type { Opportunity, PipelineStage, Account, Contact } from '@/types';
import { useToast } from '@/components/ui/toast';
import { PageHeader } from '@/components/shared/page-header';
import { Card, CardContent } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Textarea } from '@/components/ui/textarea';
import { Select } from '@/components/ui/select';
import { Button } from '@/components/ui/button';
import { Spinner } from '@/components/ui/spinner';
import type { AxiosError } from 'axios';
import type { ApiError } from '@/types';

export default function EditOpportunityPage() {
  const params = useParams();
  const router = useRouter();
  const { toast } = useToast();
  const [opportunity, setOpportunity] = useState<Opportunity | null>(null);
  const [loading, setLoading] = useState(true);
  const [saving, setSaving] = useState(false);
  const [stages, setStages] = useState<PipelineStage[]>([]);
  const [accounts, setAccounts] = useState<Account[]>([]);
  const [contacts, setContacts] = useState<Contact[]>([]);

  const [formData, setFormData] = useState({
    name: '',
    amount: '',
    pipeline_stage_id: '',
    account_id: '',
    contact_id: '',
    close_date: '',
    probability: '',
    description: '',
    next_steps: '',
    competitor: '',
    source: '',
  });

  const opportunityId = Number(params.id);

  const fetchData = useCallback(async () => {
    try {
      const [oppRes, stagesRes, accountsRes, contactsRes] = await Promise.all([
        opportunitiesApi.get(opportunityId),
        pipelineStagesApi.list(),
        accountsApi.list({ per_page: 100 }),
        contactsApi.list({ per_page: 100 }),
      ]);

      const opp = oppRes.data.data;
      setOpportunity(opp);
      setStages(stagesRes.data.data);
      setAccounts(accountsRes.data.data);
      setContacts(contactsRes.data.data);

      setFormData({
        name: opp.name || '',
        amount: String(opp.amount || ''),
        pipeline_stage_id: String(opp.stage?.id || ''),
        account_id: opp.account ? String(opp.account.id) : '',
        contact_id: opp.contact ? String(opp.contact.id) : '',
        close_date: opp.close_date ? opp.close_date.split('T')[0] : '',
        probability: String(opp.probability ?? ''),
        description: opp.description || '',
        next_steps: opp.next_steps || '',
        competitor: opp.competitor || '',
        source: opp.source || '',
      });
    } catch {
      toast('Failed to load deal', 'error');
      router.push('/opportunities');
    } finally {
      setLoading(false);
    }
  }, [opportunityId, router, toast]);

  useEffect(() => {
    fetchData();
  }, [fetchData]);

  const handleChange = (
    e: React.ChangeEvent<HTMLInputElement | HTMLSelectElement | HTMLTextAreaElement>
  ) => {
    const { name, value } = e.target;
    setFormData((prev) => ({ ...prev, [name]: value }));
  };

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setSaving(true);
    try {
      const payload: Record<string, unknown> = {
        name: formData.name,
        amount: Number(formData.amount),
        pipeline_stage_id: Number(formData.pipeline_stage_id),
      };
      if (formData.account_id) {
        payload.account_id = Number(formData.account_id);
      } else {
        payload.account_id = null;
      }
      if (formData.contact_id) {
        payload.contact_id = Number(formData.contact_id);
      } else {
        payload.contact_id = null;
      }
      if (formData.close_date) payload.close_date = formData.close_date;
      if (formData.probability) payload.probability = Number(formData.probability);
      payload.description = formData.description || null;
      payload.next_steps = formData.next_steps || null;
      payload.competitor = formData.competitor || null;
      payload.source = formData.source || null;

      await opportunitiesApi.update(opportunityId, payload);
      toast('Deal updated successfully', 'success');
      router.push(`/opportunities/${opportunityId}`);
    } catch (err) {
      const error = err as AxiosError<ApiError>;
      toast(error.response?.data?.message || 'Failed to update deal', 'error');
    } finally {
      setSaving(false);
    }
  };

  if (loading) {
    return (
      <div className="flex items-center justify-center py-20">
        <Spinner size="lg" />
      </div>
    );
  }

  if (!opportunity) return null;

  const sourceOptions = [
    { value: 'website', label: 'Website' },
    { value: 'referral', label: 'Referral' },
    { value: 'linkedin', label: 'LinkedIn' },
    { value: 'cold_call', label: 'Cold Call' },
    { value: 'trade_show', label: 'Trade Show' },
    { value: 'advertisement', label: 'Advertisement' },
    { value: 'other', label: 'Other' },
  ];

  return (
    <div>
      <PageHeader
        title="Edit Deal"
        description={`Editing ${opportunity.name}`}
      />
      <Card>
        <CardContent className="py-6">
          <form onSubmit={handleSubmit} className="space-y-8">
            <div>
              <h3 className="mb-4 text-lg font-medium text-gray-900">
                Deal Information
              </h3>
              <div className="grid gap-4 sm:grid-cols-2">
                <Input
                  label="Deal Name"
                  name="name"
                  placeholder="Enterprise License Deal"
                  value={formData.name}
                  onChange={handleChange}
                  required
                />
                <Input
                  label="Amount"
                  name="amount"
                  type="number"
                  step="0.01"
                  min="0"
                  placeholder="50000"
                  value={formData.amount}
                  onChange={handleChange}
                  required
                />
                <Select
                  label="Stage"
                  name="pipeline_stage_id"
                  options={stages.map((s) => ({ value: s.id, label: s.name }))}
                  placeholder="Select stage"
                  value={formData.pipeline_stage_id}
                  onChange={handleChange}
                  required
                />
                <Input
                  label="Probability (%)"
                  name="probability"
                  type="number"
                  min="0"
                  max="100"
                  placeholder="50"
                  value={formData.probability}
                  onChange={handleChange}
                />
                <Input
                  label="Close Date"
                  name="close_date"
                  type="date"
                  value={formData.close_date}
                  onChange={handleChange}
                />
                <Select
                  label="Source"
                  name="source"
                  options={sourceOptions}
                  placeholder="Select source"
                  value={formData.source}
                  onChange={handleChange}
                />
              </div>
            </div>

            <div>
              <h3 className="mb-4 text-lg font-medium text-gray-900">
                Associations
              </h3>
              <div className="grid gap-4 sm:grid-cols-2">
                <Select
                  label="Account"
                  name="account_id"
                  options={accounts.map((a) => ({ value: a.id, label: a.name }))}
                  placeholder="Select account"
                  value={formData.account_id}
                  onChange={handleChange}
                />
                <Select
                  label="Contact"
                  name="contact_id"
                  options={contacts.map((c) => ({
                    value: c.id,
                    label: `${c.first_name} ${c.last_name}`,
                  }))}
                  placeholder="Select contact"
                  value={formData.contact_id}
                  onChange={handleChange}
                />
              </div>
            </div>

            <div>
              <h3 className="mb-4 text-lg font-medium text-gray-900">
                Additional Details
              </h3>
              <div className="space-y-4">
                <Textarea
                  label="Description"
                  name="description"
                  placeholder="Describe this deal..."
                  value={formData.description}
                  onChange={handleChange}
                />
                <Textarea
                  label="Next Steps"
                  name="next_steps"
                  placeholder="What are the next steps?"
                  value={formData.next_steps}
                  onChange={handleChange}
                />
                <Input
                  label="Competitor"
                  name="competitor"
                  placeholder="Main competitor for this deal"
                  value={formData.competitor}
                  onChange={handleChange}
                />
              </div>
            </div>

            <div className="flex justify-end gap-3 border-t border-gray-200 pt-6">
              <Button
                type="button"
                variant="outline"
                onClick={() => router.push(`/opportunities/${opportunityId}`)}
              >
                Cancel
              </Button>
              <Button type="submit" disabled={saving}>
                {saving ? 'Saving...' : 'Update Deal'}
              </Button>
            </div>
          </form>
        </CardContent>
      </Card>
    </div>
  );
}
