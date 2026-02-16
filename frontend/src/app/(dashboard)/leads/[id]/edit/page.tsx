'use client';

import React, { useEffect, useState } from 'react';
import { useParams, useRouter } from 'next/navigation';
import { leadsApi } from '@/lib/api/leads';
import { useToast } from '@/components/ui/toast';
import { PageHeader } from '@/components/shared/page-header';
import { Card, CardContent } from '@/components/ui/card';
import { Spinner } from '@/components/ui/spinner';
import { LeadForm } from '@/components/leads/lead-form';
import type { Lead } from '@/types';
import type { LeadFormData } from '@/lib/validations/lead';
import type { AxiosError } from 'axios';
import type { ApiError } from '@/types';

export default function EditLeadPage() {
  const params = useParams();
  const router = useRouter();
  const { toast } = useToast();
  const [lead, setLead] = useState<Lead | null>(null);
  const [loading, setLoading] = useState(true);
  const [saving, setSaving] = useState(false);

  const leadId = Number(params.id);

  useEffect(() => {
    const fetchLead = async () => {
      try {
        const response = await leadsApi.get(leadId);
        setLead(response.data.data);
      } catch {
        toast('Failed to load lead', 'error');
        router.push('/leads');
      } finally {
        setLoading(false);
      }
    };

    fetchLead();
  }, [leadId, router, toast]);

  const handleSubmit = async (data: LeadFormData) => {
    setSaving(true);
    try {
      await leadsApi.update(leadId, data);
      toast('Lead updated successfully', 'success');
      router.push(`/leads/${leadId}`);
    } catch (err) {
      const error = err as AxiosError<ApiError>;
      toast(error.response?.data?.message || 'Failed to update lead', 'error');
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

  if (!lead) return null;

  const defaultValues: Partial<LeadFormData> = {
    first_name: lead.first_name,
    last_name: lead.last_name,
    email: lead.email || '',
    phone: lead.phone || '',
    company_name: lead.company_name || '',
    job_title: lead.job_title || '',
    website: lead.website || '',
    description: lead.description || '',
    status: lead.status,
    score: lead.score,
    source: lead.source || '',
    address_line_1: lead.address.line_1 || '',
    address_line_2: lead.address.line_2 || '',
    city: lead.address.city || '',
    state: lead.address.state || '',
    postal_code: lead.address.postal_code || '',
    country: lead.address.country || '',
  };

  return (
    <div>
      <PageHeader title="Edit Lead" description={`Editing ${lead.full_name}`} />
      <Card>
        <CardContent className="py-6">
          <LeadForm
            defaultValues={defaultValues}
            onSubmit={handleSubmit}
            onCancel={() => router.push(`/leads/${leadId}`)}
            loading={saving}
          />
        </CardContent>
      </Card>
    </div>
  );
}
