'use client';

import React, { useEffect, useState, useCallback } from 'react';
import Link from 'next/link';
import { Plus, LayoutList, Kanban } from 'lucide-react';
import { leadsApi } from '@/lib/api/leads';
import type { Lead, LeadFilters, PaginatedResponse } from '@/types';
import { LEAD_STATUSES, LEAD_SCORES } from '@/lib/utils/constants';
import { PageHeader } from '@/components/shared/page-header';
import { SearchInput } from '@/components/shared/search-input';
import { Button } from '@/components/ui/button';
import { Select } from '@/components/ui/select';
import { Pagination } from '@/components/ui/pagination';
import { LeadTable } from '@/components/leads/lead-table';
import { LeadKanban } from '@/components/leads/lead-kanban';
import { cn } from '@/lib/utils/cn';

type ViewMode = 'table' | 'kanban';

export default function LeadsPage() {
  const [leads, setLeads] = useState<Lead[]>([]);
  const [meta, setMeta] = useState<PaginatedResponse<Lead>['meta'] | null>(null);
  const [loading, setLoading] = useState(true);
  const [viewMode, setViewMode] = useState<ViewMode>('table');
  const [filters, setFilters] = useState<LeadFilters>({
    search: '',
    status: undefined,
    score: undefined,
    sort_by: 'created_at',
    sort_dir: 'desc',
    page: 1,
    per_page: viewMode === 'kanban' ? 100 : 15,
  });

  const fetchLeads = useCallback(async () => {
    setLoading(true);
    try {
      const params: LeadFilters = { ...filters };
      if (!params.search) delete params.search;
      if (!params.status) delete params.status;
      if (!params.score) delete params.score;
      const response = await leadsApi.list(params);
      setLeads(response.data.data);
      setMeta(response.data.meta);
    } catch {
      // Handle silently
    } finally {
      setLoading(false);
    }
  }, [filters]);

  useEffect(() => {
    fetchLeads();
  }, [fetchLeads]);

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
        title="Leads"
        description="Track and convert your leads"
        action={
          <Link href="/leads/new">
            <Button>
              <Plus className="mr-2 h-4 w-4" />
              Add Lead
            </Button>
          </Link>
        }
      />

      <div className="mb-4 flex flex-wrap items-end gap-4">
        <SearchInput
          value={filters.search || ''}
          onChange={(search) =>
            setFilters((prev) => ({ ...prev, search, page: 1 }))
          }
          placeholder="Search leads..."
          className="w-64"
        />
        <div className="w-40">
          <Select
            options={LEAD_STATUSES.map((s) => ({ value: s.value, label: s.label }))}
            placeholder="All statuses"
            value={filters.status || ''}
            onChange={(e) =>
              setFilters((prev) => ({
                ...prev,
                status: (e.target.value || undefined) as LeadFilters['status'],
                page: 1,
              }))
            }
          />
        </div>
        <div className="w-36">
          <Select
            options={LEAD_SCORES.map((s) => ({ value: s.value, label: s.label }))}
            placeholder="All scores"
            value={filters.score || ''}
            onChange={(e) =>
              setFilters((prev) => ({
                ...prev,
                score: (e.target.value || undefined) as LeadFilters['score'],
                page: 1,
              }))
            }
          />
        </div>
        <div className="ml-auto flex rounded-md border border-gray-300">
          <button
            onClick={() => {
              setViewMode('table');
              setFilters((prev) => ({ ...prev, per_page: 15, page: 1 }));
            }}
            className={cn(
              'flex items-center gap-1 px-3 py-2 text-sm',
              viewMode === 'table'
                ? 'bg-gray-100 text-gray-900'
                : 'text-gray-500 hover:text-gray-700'
            )}
          >
            <LayoutList className="h-4 w-4" />
            Table
          </button>
          <button
            onClick={() => {
              setViewMode('kanban');
              setFilters((prev) => ({ ...prev, per_page: 100, page: 1 }));
            }}
            className={cn(
              'flex items-center gap-1 px-3 py-2 text-sm',
              viewMode === 'kanban'
                ? 'bg-gray-100 text-gray-900'
                : 'text-gray-500 hover:text-gray-700'
            )}
          >
            <Kanban className="h-4 w-4" />
            Kanban
          </button>
        </div>
      </div>

      {viewMode === 'table' ? (
        <div className="rounded-lg border border-gray-200 bg-white">
          <LeadTable
            leads={leads}
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
      ) : (
        <LeadKanban leads={leads} />
      )}
    </div>
  );
}
