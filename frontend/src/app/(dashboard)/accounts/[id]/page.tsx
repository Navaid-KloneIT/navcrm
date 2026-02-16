'use client';

import React, { useEffect, useState, useCallback } from 'react';
import { useParams, useRouter } from 'next/navigation';
import Link from 'next/link';
import {
  Globe,
  Phone,
  Mail,
  Building2,
  Pencil,
  Trash2,
  Users,
  DollarSign,
} from 'lucide-react';
import { accountsApi } from '@/lib/api/accounts';
import { activitiesApi } from '@/lib/api/activities';
import type { Account, Activity, Contact, Address } from '@/types';
import { useToast } from '@/components/ui/toast';
import { PageHeader } from '@/components/shared/page-header';
import { Card, CardHeader, CardTitle, CardContent } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Spinner } from '@/components/ui/spinner';
import { ConfirmDialog } from '@/components/shared/confirm-dialog';
import { AccountHierarchy } from '@/components/accounts/account-hierarchy';
import { AccountStakeholders } from '@/components/accounts/account-stakeholders';
import { AddressManager } from '@/components/accounts/address-manager';
import { ContactActivityTimeline } from '@/components/contacts/contact-activity-timeline';
import { formatDate, formatCurrency } from '@/lib/utils/format';
import type { AxiosError } from 'axios';
import type { ApiError } from '@/types';

export default function AccountDetailPage() {
  const params = useParams();
  const router = useRouter();
  const { toast } = useToast();
  const [account, setAccount] = useState<Account | null>(null);
  const [contacts, setContacts] = useState<Contact[]>([]);
  const [addresses, setAddresses] = useState<Address[]>([]);
  const [children, setChildren] = useState<Account[]>([]);
  const [activities, setActivities] = useState<Activity[]>([]);
  const [loading, setLoading] = useState(true);
  const [deleteOpen, setDeleteOpen] = useState(false);

  const accountId = Number(params.id);

  const fetchData = useCallback(async () => {
    try {
      const [accountRes, contactsRes, addressesRes, childrenRes, activitiesRes] =
        await Promise.all([
          accountsApi.get(accountId),
          accountsApi.getContacts(accountId),
          accountsApi.getAddresses(accountId),
          accountsApi.getChildren(accountId),
          activitiesApi.list({
            activitable_type: 'account',
            activitable_id: accountId,
          }),
        ]);

      setAccount(accountRes.data.data);
      setContacts(contactsRes.data.data || []);
      setAddresses(addressesRes.data.data || []);
      setChildren(childrenRes.data.data || []);
      setActivities(activitiesRes.data.data);
    } catch {
      toast('Failed to load account', 'error');
      router.push('/accounts');
    } finally {
      setLoading(false);
    }
  }, [accountId, router, toast]);

  useEffect(() => {
    fetchData();
  }, [fetchData]);

  const handleDelete = async () => {
    try {
      await accountsApi.delete(accountId);
      toast('Account deleted successfully', 'success');
      router.push('/accounts');
    } catch (err) {
      const error = err as AxiosError<ApiError>;
      toast(error.response?.data?.message || 'Failed to delete account', 'error');
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

  return (
    <div>
      <PageHeader
        title={account.name}
        description={account.industry ? `${account.industry} industry` : undefined}
        action={
          <div className="flex gap-2">
            <Link href={`/accounts/${account.id}/edit`}>
              <Button variant="outline">
                <Pencil className="mr-2 h-4 w-4" />
                Edit
              </Button>
            </Link>
            <Button variant="destructive" onClick={() => setDeleteOpen(true)}>
              <Trash2 className="mr-2 h-4 w-4" />
              Delete
            </Button>
          </div>
        }
      />

      <div className="grid gap-6 lg:grid-cols-3">
        <div className="space-y-6 lg:col-span-2">
          <Card>
            <CardHeader>
              <CardTitle>Company Details</CardTitle>
            </CardHeader>
            <CardContent>
              <div className="grid gap-4 sm:grid-cols-2">
                {account.website && (
                  <div className="flex items-center gap-2 text-sm">
                    <Globe className="h-4 w-4 text-gray-400" />
                    <a
                      href={account.website}
                      target="_blank"
                      rel="noopener noreferrer"
                      className="text-blue-600 hover:underline"
                    >
                      {account.website}
                    </a>
                  </div>
                )}
                {account.phone && (
                  <div className="flex items-center gap-2 text-sm">
                    <Phone className="h-4 w-4 text-gray-400" />
                    <span>{account.phone}</span>
                  </div>
                )}
                {account.email && (
                  <div className="flex items-center gap-2 text-sm">
                    <Mail className="h-4 w-4 text-gray-400" />
                    <a
                      href={`mailto:${account.email}`}
                      className="text-blue-600 hover:underline"
                    >
                      {account.email}
                    </a>
                  </div>
                )}
                {account.annual_revenue && (
                  <div className="flex items-center gap-2 text-sm">
                    <DollarSign className="h-4 w-4 text-gray-400" />
                    <span>{formatCurrency(account.annual_revenue)}</span>
                  </div>
                )}
                {account.employee_count && (
                  <div className="flex items-center gap-2 text-sm">
                    <Users className="h-4 w-4 text-gray-400" />
                    <span>{account.employee_count.toLocaleString()} employees</span>
                  </div>
                )}
                {account.industry && (
                  <div className="flex items-center gap-2 text-sm">
                    <Building2 className="h-4 w-4 text-gray-400" />
                    <span className="capitalize">{account.industry}</span>
                  </div>
                )}
              </div>
              {account.description && (
                <div className="mt-4 border-t border-gray-200 pt-4">
                  <p className="text-sm text-gray-600">{account.description}</p>
                </div>
              )}
            </CardContent>
          </Card>

          <AccountStakeholders contacts={contacts} />

          <AddressManager
            accountId={accountId}
            addresses={addresses}
            onRefresh={fetchData}
          />

          <Card>
            <CardHeader>
              <CardTitle>Activity Timeline</CardTitle>
            </CardHeader>
            <CardContent>
              <ContactActivityTimeline activities={activities} />
            </CardContent>
          </Card>
        </div>

        <div className="space-y-6">
          <Card>
            <CardHeader>
              <CardTitle>Details</CardTitle>
            </CardHeader>
            <CardContent className="space-y-3 text-sm">
              <div className="flex justify-between">
                <span className="text-gray-500">Tax ID</span>
                <span className="font-medium text-gray-900">
                  {account.tax_id || '-'}
                </span>
              </div>
              <div className="flex justify-between">
                <span className="text-gray-500">Owner</span>
                <span className="font-medium text-gray-900">
                  {account.owner?.name || '-'}
                </span>
              </div>
              <div className="flex justify-between">
                <span className="text-gray-500">Created</span>
                <span className="font-medium text-gray-900">
                  {formatDate(account.created_at)}
                </span>
              </div>
              <div className="flex justify-between">
                <span className="text-gray-500">Updated</span>
                <span className="font-medium text-gray-900">
                  {formatDate(account.updated_at)}
                </span>
              </div>
            </CardContent>
          </Card>

          <AccountHierarchy parent={account.parent} children={children} />
        </div>
      </div>

      <ConfirmDialog
        open={deleteOpen}
        onClose={() => setDeleteOpen(false)}
        onConfirm={handleDelete}
        title="Delete Account"
        message={`Are you sure you want to delete ${account.name}? This action cannot be undone.`}
        confirmText="Delete"
        variant="danger"
      />
    </div>
  );
}
