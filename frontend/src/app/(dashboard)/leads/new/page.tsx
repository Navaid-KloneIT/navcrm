'use client';

import React, { useState } from 'react';
import { useRouter } from 'next/navigation';
import { leadsApi } from '@/lib/api/leads';
import { useToast } from '@/components/ui/toast';
import { PageHeader } from '@/components/shared/page-header';
import { Card, CardContent } from '@/components/ui/card';
import { LeadForm } from '@/components/leads/lead-form';
import type { LeadFormData } from '@/lib/validations/lead';
import type { AxiosError } from 'axios';
import type { ApiError } from '@/types';

export default function NewLeadPage() {
  const router = useRouter();
  const { toast } = useToast();
  const [loading, setLoading] = useState(false);

  const handleSubmit = async (data: LeadFormData) => {
    setLoading(true);
    try {
      await leadsApi.create(data);
      toast('Lead created successfully', 'success');
      router.push('/leads');
    } catch (err) {
      const error = err as AxiosError<ApiError>;
      toast(error.response?.data?.message || 'Failed to create lead', 'error');
    } finally {
      setLoading(false);
    }
  };

  return (
    <div>
      <PageHeader title="New Lead" description="Add a new lead to your pipeline" />
      <Card>
        <CardContent className="py-6">
          <LeadForm
            onSubmit={handleSubmit}
            onCancel={() => router.push('/leads')}
            loading={loading}
          />
        </CardContent>
      </Card>
    </div>
  );
}
