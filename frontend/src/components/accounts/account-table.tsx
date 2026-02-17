'use client';

import React from 'react';
import { useRouter } from 'next/navigation';
import type { Account } from '@/types';
import { DataTable, type Column } from '@/components/shared/data-table';
import { formatDate, formatCurrency } from '@/lib/utils/format';

interface AccountTableProps {
  accounts: Account[];
  loading?: boolean;
  sortBy?: string;
  sortDir?: 'asc' | 'desc';
  onSort?: (key: string) => void;
}

function AccountTable({ accounts, loading, sortBy, sortDir, onSort }: AccountTableProps) {
  const router = useRouter();

  const columns: Column<Account>[] = [
    {
      key: 'name',
      label: 'Name',
      sortable: true,
      render: (account) => (
        <div>
          <p className="font-medium text-gray-900">{account.name}</p>
          {account.industry && (
            <p className="text-xs text-gray-500 capitalize">{account.industry}</p>
          )}
        </div>
      ),
    },
    {
      key: 'industry',
      label: 'Industry',
      sortable: true,
      render: (account) => (
        <span className="capitalize text-gray-600">{account.industry || '-'}</span>
      ),
    },
    {
      key: 'phone',
      label: 'Phone',
      render: (account) => (
        <span className="text-gray-600">{account.phone || '-'}</span>
      ),
    },
    {
      key: 'annual_revenue',
      label: 'Revenue',
      sortable: true,
      render: (account) => (
        <span className="text-gray-600">
          {account.annual_revenue ? formatCurrency(account.annual_revenue) : '-'}
        </span>
      ),
    },
    {
      key: 'employee_count',
      label: 'Employees',
      sortable: true,
      render: (account) => (
        <span className="text-gray-600">
          {account.employee_count?.toLocaleString() || '-'}
        </span>
      ),
    },
    {
      key: 'owner',
      label: 'Owner',
      render: (account) => (
        <span className="text-gray-600">{account.owner?.name || '-'}</span>
      ),
    },
    {
      key: 'created_at',
      label: 'Created',
      sortable: true,
      render: (account) => (
        <span className="text-gray-500">{formatDate(account.created_at)}</span>
      ),
    },
  ];

  return (
    <DataTable
      columns={columns}
      data={accounts}
      loading={loading}
      sortBy={sortBy}
      sortDir={sortDir}
      onSort={onSort}
      onRowClick={(item) => router.push(`/accounts/${item.id}`)}
      emptyTitle="No accounts found"
      emptyDescription="Get started by adding your first account."
    />
  );
}

export { AccountTable };
