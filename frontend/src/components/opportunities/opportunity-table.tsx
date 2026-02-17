'use client';

import React from 'react';
import { useRouter } from 'next/navigation';
import type { Opportunity } from '@/types';
import { Badge } from '@/components/ui/badge';
import { DataTable, type Column } from '@/components/shared/data-table';
import { formatDate } from '@/lib/utils/format';

interface OpportunityTableProps {
  opportunities: Opportunity[];
  loading?: boolean;
  sortBy?: string;
  sortDir?: 'asc' | 'desc';
  onSort?: (key: string) => void;
}

function OpportunityTable({
  opportunities,
  loading,
  sortBy,
  sortDir,
  onSort,
}: OpportunityTableProps) {
  const router = useRouter();

  const columns: Column<Opportunity>[] = [
    {
      key: 'name',
      label: 'Name',
      sortable: true,
      render: (opp) => (
        <p className="font-medium text-gray-900">{opp.name}</p>
      ),
    },
    {
      key: 'account',
      label: 'Account',
      render: (opp) => (
        <span className="text-gray-600">{opp.account?.name || '-'}</span>
      ),
    },
    {
      key: 'stage',
      label: 'Stage',
      render: (opp) => (
        <Badge
          className={`${opp.stage.color ? `bg-[${opp.stage.color}]/10 text-[${opp.stage.color}]` : ''}`}
          style={{
            backgroundColor: opp.stage.color ? `${opp.stage.color}20` : undefined,
            color: opp.stage.color || undefined,
          }}
        >
          {opp.stage.name}
        </Badge>
      ),
    },
    {
      key: 'amount',
      label: 'Amount',
      sortable: true,
      render: (opp) => (
        <span className="font-medium text-gray-900">
          ${Number(opp.amount).toLocaleString('en-US', { minimumFractionDigits: 2 })}
        </span>
      ),
    },
    {
      key: 'close_date',
      label: 'Close Date',
      sortable: true,
      render: (opp) => (
        <span className="text-gray-500">
          {opp.close_date ? formatDate(opp.close_date) : '-'}
        </span>
      ),
    },
    {
      key: 'probability',
      label: 'Probability %',
      sortable: true,
      render: (opp) => (
        <span className="text-gray-600">{opp.probability}%</span>
      ),
    },
    {
      key: 'owner',
      label: 'Owner',
      render: (opp) => (
        <span className="text-gray-600">{opp.owner?.name || '-'}</span>
      ),
    },
  ];

  return (
    <DataTable
      columns={columns}
      data={opportunities}
      loading={loading}
      sortBy={sortBy}
      sortDir={sortDir}
      onSort={onSort}
      onRowClick={(item) => router.push(`/opportunities/${item.id}`)}
      emptyTitle="No opportunities found"
      emptyDescription="Get started by adding your first opportunity."
    />
  );
}

export { OpportunityTable };
