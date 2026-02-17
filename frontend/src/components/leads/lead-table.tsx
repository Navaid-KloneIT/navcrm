'use client';

import React from 'react';
import { useRouter } from 'next/navigation';
import type { Lead } from '@/types';
import { Badge } from '@/components/ui/badge';
import { DataTable, type Column } from '@/components/shared/data-table';
import { LeadScoreBadge } from './lead-score-badge';
import { LEAD_STATUSES } from '@/lib/utils/constants';
import { formatDate } from '@/lib/utils/format';

interface LeadTableProps {
  leads: Lead[];
  loading?: boolean;
  sortBy?: string;
  sortDir?: 'asc' | 'desc';
  onSort?: (key: string) => void;
}

function LeadTable({ leads, loading, sortBy, sortDir, onSort }: LeadTableProps) {
  const router = useRouter();

  const columns: Column<Lead>[] = [
    {
      key: 'full_name',
      label: 'Name',
      sortable: true,
      render: (lead) => (
        <div>
          <p className="font-medium text-gray-900">{lead.full_name}</p>
          {lead.job_title && (
            <p className="text-xs text-gray-500">{lead.job_title}</p>
          )}
        </div>
      ),
    },
    {
      key: 'email',
      label: 'Email',
      sortable: true,
      render: (lead) => (
        <span className="text-gray-600">{lead.email || '-'}</span>
      ),
    },
    {
      key: 'company_name',
      label: 'Company',
      sortable: true,
      render: (lead) => (
        <span className="text-gray-600">{lead.company_name || '-'}</span>
      ),
    },
    {
      key: 'status',
      label: 'Status',
      sortable: true,
      render: (lead) => {
        const status = LEAD_STATUSES.find((s) => s.value === lead.status);
        return (
          <Badge className={status?.color}>{status?.label || lead.status}</Badge>
        );
      },
    },
    {
      key: 'score',
      label: 'Score',
      sortable: true,
      render: (lead) => <LeadScoreBadge score={lead.score} />,
    },
    {
      key: 'source',
      label: 'Source',
      render: (lead) => (
        <span className="capitalize text-gray-600">{lead.source || '-'}</span>
      ),
    },
    {
      key: 'owner',
      label: 'Owner',
      render: (lead) => (
        <span className="text-gray-600">{lead.owner?.name || '-'}</span>
      ),
    },
    {
      key: 'created_at',
      label: 'Created',
      sortable: true,
      render: (lead) => (
        <span className="text-gray-500">{formatDate(lead.created_at)}</span>
      ),
    },
  ];

  return (
    <DataTable
      columns={columns}
      data={leads}
      loading={loading}
      sortBy={sortBy}
      sortDir={sortDir}
      onSort={onSort}
      onRowClick={(item) => router.push(`/leads/${item.id}`)}
      emptyTitle="No leads found"
      emptyDescription="Get started by adding your first lead."
    />
  );
}

export { LeadTable };
