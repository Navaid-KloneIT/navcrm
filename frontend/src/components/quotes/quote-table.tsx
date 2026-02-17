'use client';

import React from 'react';
import { useRouter } from 'next/navigation';
import type { Quote } from '@/types';
import { DataTable, type Column } from '@/components/shared/data-table';
import { QuoteStatusBadge } from '@/components/quotes/quote-status-badge';
import { formatCurrency, formatDate } from '@/lib/utils/format';

interface QuoteTableProps {
  quotes: Quote[];
  loading?: boolean;
  sortBy?: string;
  sortDir?: 'asc' | 'desc';
  onSort?: (key: string) => void;
}

function QuoteTable({ quotes, loading, sortBy, sortDir, onSort }: QuoteTableProps) {
  const router = useRouter();

  const columns: Column<Quote>[] = [
    {
      key: 'quote_number',
      label: 'Quote #',
      sortable: true,
      render: (quote) => (
        <span className="font-medium text-gray-900">{quote.quote_number}</span>
      ),
    },
    {
      key: 'opportunity',
      label: 'Opportunity',
      render: (quote) => (
        <span className="text-gray-600">{quote.opportunity?.name || '-'}</span>
      ),
    },
    {
      key: 'account',
      label: 'Account',
      render: (quote) => (
        <span className="text-gray-600">{quote.account?.name || '-'}</span>
      ),
    },
    {
      key: 'status',
      label: 'Status',
      sortable: true,
      render: (quote) => <QuoteStatusBadge status={quote.status} />,
    },
    {
      key: 'total',
      label: 'Total',
      sortable: true,
      render: (quote) => (
        <span className="font-medium text-gray-900">{formatCurrency(quote.total)}</span>
      ),
    },
    {
      key: 'valid_until',
      label: 'Valid Until',
      sortable: true,
      render: (quote) => (
        <span className="text-gray-500">
          {quote.valid_until ? formatDate(quote.valid_until) : '-'}
        </span>
      ),
    },
    {
      key: 'prepared_by',
      label: 'Prepared By',
      render: (quote) => (
        <span className="text-gray-600">{quote.prepared_by?.name || '-'}</span>
      ),
    },
  ];

  return (
    <DataTable
      columns={columns}
      data={quotes}
      loading={loading}
      sortBy={sortBy}
      sortDir={sortDir}
      onSort={onSort}
      onRowClick={(item) => router.push(`/quotes/${item.id}`)}
      emptyTitle="No quotes found"
      emptyDescription="Get started by creating your first quote."
    />
  );
}

export { QuoteTable };
