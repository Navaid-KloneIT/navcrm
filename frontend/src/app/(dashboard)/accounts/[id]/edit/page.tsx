'use client';

import React, { useEffect, useState } from 'react';
import { useParams, useRouter } from 'next/navigation';
import { accountsApi } from '@/lib/api/accounts';
import { useToast } from '@/components/ui/toast';
import { PageHeader } from '@/components/shared/page-header';
import { Card, CardContent } from '@/components/ui/card';
import { Spinner } from '@/components/ui/spinner';
import { AccountForm } from '@/components/accounts/account-form';
import type { Account } from '@/types';
import type { AccountFormData } from '@/lib/validations/account';
import type { AxiosError } from 'axios';
import type { ApiError } from '@/types';

export default function EditAccountPage() {
  const params = useParams();
  const router = useRouter();
  const { toast } = useToast();
  const [account, setAccount] = useState<Account | null>(null);
  const [loading, setLoading] = useState(true);
  const [saving, setSaving] = useState(false);

  const accountId = Number(params.id);

  useEffect(() => {
    const fetchAccount = async () => {
      try {
        const response = await accountsApi.get(accountId);
        setAccount(response.data.data);
      } catch {
        toast('Failed to load account', 'error');
        router.push('/accounts');
      } finally {
        setLoading(false);
      }
    };

    fetchAccount();
  }, [accountId, router, toast]);

  const handleSubmit = async (data: AccountFormData) => {
    setSaving(true);
    try {
      await accountsApi.update(accountId, data);
      toast('Account updated successfully', 'success');
      router.push(`/accounts/${accountId}`);
    } catch (err) {
      const error = err as AxiosError<ApiError>;
      toast(error.response?.data?.message || 'Failed to update account', 'error');
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

  if (!account) return null;

  const defaultValues: Partial<AccountFormData> = {
    name: account.name,
    industry: account.industry || '',
    website: account.website || '',
    phone: account.phone || '',
    email: account.email || '',
    annual_revenue: account.annual_revenue,
    employee_count: account.employee_count,
    tax_id: account.tax_id || '',
    description: account.description || '',
    parent_id: account.parent_id,
  };

  return (
    <div>
      <PageHeader title="Edit Account" description={`Editing ${account.name}`} />
      <Card>
        <CardContent className="py-6">
          <AccountForm
            defaultValues={defaultValues}
            onSubmit={handleSubmit}
            onCancel={() => router.push(`/accounts/${accountId}`)}
            loading={saving}
            excludeAccountId={accountId}
          />
        </CardContent>
      </Card>
    </div>
  );
}
