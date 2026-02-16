'use client';

import React, { useState } from 'react';
import { useRouter } from 'next/navigation';
import { accountsApi } from '@/lib/api/accounts';
import { useToast } from '@/components/ui/toast';
import { PageHeader } from '@/components/shared/page-header';
import { Card, CardContent } from '@/components/ui/card';
import { AccountForm } from '@/components/accounts/account-form';
import type { AccountFormData } from '@/lib/validations/account';
import type { AxiosError } from 'axios';
import type { ApiError } from '@/types';

export default function NewAccountPage() {
  const router = useRouter();
  const { toast } = useToast();
  const [loading, setLoading] = useState(false);

  const handleSubmit = async (data: AccountFormData) => {
    setLoading(true);
    try {
      await accountsApi.create(data);
      toast('Account created successfully', 'success');
      router.push('/accounts');
    } catch (err) {
      const error = err as AxiosError<ApiError>;
      toast(error.response?.data?.message || 'Failed to create account', 'error');
    } finally {
      setLoading(false);
    }
  };

  return (
    <div>
      <PageHeader title="New Account" description="Add a new business account" />
      <Card>
        <CardContent className="py-6">
          <AccountForm
            onSubmit={handleSubmit}
            onCancel={() => router.push('/accounts')}
            loading={loading}
          />
        </CardContent>
      </Card>
    </div>
  );
}
