'use client';

import React, { useEffect, useState, useCallback } from 'react';
import Link from 'next/link';
import { Plus } from 'lucide-react';
import { quotesApi } from '@/lib/api/quotes';
import type { Quote, QuoteFilters, QuoteStatus, PaginatedResponse } from '@/types';
import { QUOTE_STATUSES } from '@/lib/utils/constants';
import { PageHeader } from '@/components/shared/page-header';
import { SearchInput } from '@/components/shared/search-input';
import { Button } from '@/components/ui/button';
import { Select } from '@/components/ui/select';
import { Pagination } from '@/components/ui/pagination';
import { QuoteTable } from '@/components/quotes/quote-table';

const statusOptions = [
  { value: '', label: 'All Statuses' },
  ...QUOTE_STATUSES.map((s) => ({ value: s.value, label: s.label })),
];

export default function QuotesPage() {
  const [quotes, setQuotes] = useState<Quote[]>([]);
  const [meta, setMeta] = useState<PaginatedResponse<Quote>['meta'] | null>(null);
  const [loading, setLoading] = useState(true);
  const [filters, setFilters] = useState<QuoteFilters>({
    search: '',
    sort_by: 'created_at',
    sort_dir: 'desc',
    page: 1,
    per_page: 15,
  });
  const [statusFilter, setStatusFilter] = useState('');

  const fetchQuotes = useCallback(async () => {
    setLoading(true);
    try {
      const params: QuoteFilters = { ...filters };
      if (!params.search) delete params.search;
      if (statusFilter) {
        params.status = statusFilter as QuoteStatus;
      }
      const response = await quotesApi.list(params);
      setQuotes(response.data.data);
      setMeta(response.data.meta);
    } catch {
      // Handle silently
    } finally {
      setLoading(false);
    }
  }, [filters, statusFilter]);

  useEffect(() => {
    fetchQuotes();
  }, [fetchQuotes]);

  const handleSort = (key: string) => {
    setFilters((prev) => ({
      ...prev,
      sort_by: key,
      sort_dir: prev.sort_by === key && prev.sort_dir === 'asc' ? 'desc' : 'asc',
      page: 1,
    }));
  };

  return (
    <div>
      <PageHeader
        title="Quotes"
        description="Manage quotes and proposals"
        action={
          <Link href="/quotes/new">
            <Button>
              <Plus className="mr-2 h-4 w-4" />
              Create Quote
            </Button>
          </Link>
        }
      />

      <div className="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center">
        <SearchInput
          value={filters.search || ''}
          onChange={(search) =>
            setFilters((prev) => ({ ...prev, search, page: 1 }))
          }
          placeholder="Search quotes..."
          className="sm:w-72"
        />
        <Select
          options={statusOptions}
          value={statusFilter}
          onChange={(e) => {
            setStatusFilter(e.target.value);
            setFilters((prev) => ({ ...prev, page: 1 }));
          }}
          className="sm:w-44"
        />
      </div>

      <div className="rounded-lg border border-gray-200 bg-white">
        <QuoteTable
          quotes={quotes}
          loading={loading}
          sortBy={filters.sort_by}
          sortDir={filters.sort_dir}
          onSort={handleSort}
        />
        {meta && (
          <Pagination
            meta={meta}
            onPageChange={(page) =>
              setFilters((prev) => ({ ...prev, page }))
            }
          />
        )}
      </div>
    </div>
  );
}
