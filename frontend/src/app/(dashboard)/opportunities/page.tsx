'use client';

import React, { useEffect, useState, useCallback } from 'react';
import Link from 'next/link';
import { useRouter } from 'next/navigation';
import { Plus, LayoutList, Kanban, GripVertical } from 'lucide-react';
import { opportunitiesApi } from '@/lib/api/opportunities';
import { pipelineStagesApi } from '@/lib/api/pipeline-stages';
import type { Opportunity, OpportunityFilters, PipelineStage, PaginatedResponse } from '@/types';
import { PageHeader } from '@/components/shared/page-header';
import { SearchInput } from '@/components/shared/search-input';
import { Button } from '@/components/ui/button';
import { Select } from '@/components/ui/select';
import { Pagination } from '@/components/ui/pagination';
import { Badge } from '@/components/ui/badge';
import { DataTable, type Column } from '@/components/shared/data-table';
import { Spinner } from '@/components/ui/spinner';
import { useToast } from '@/components/ui/toast';
import { formatDate, formatCurrency } from '@/lib/utils/format';
import { cn } from '@/lib/utils/cn';

type ViewMode = 'table' | 'kanban';

export default function OpportunitiesPage() {
  const router = useRouter();
  const { toast } = useToast();
  const [opportunities, setOpportunities] = useState<Opportunity[]>([]);
  const [meta, setMeta] = useState<PaginatedResponse<Opportunity>['meta'] | null>(null);
  const [stages, setStages] = useState<PipelineStage[]>([]);
  const [loading, setLoading] = useState(true);
  const [viewMode, setViewMode] = useState<ViewMode>('table');
  const [draggedOpportunityId, setDraggedOpportunityId] = useState<number | null>(null);
  const [filters, setFilters] = useState<OpportunityFilters>({
    search: '',
    pipeline_stage_id: undefined,
    owner_id: undefined,
    sort_by: 'created_at',
    sort_dir: 'desc',
    page: 1,
    per_page: 15,
  });

  const fetchStages = useCallback(async () => {
    try {
      const response = await pipelineStagesApi.list();
      setStages(response.data.data);
    } catch {
      // Handle silently
    }
  }, []);

  const fetchOpportunities = useCallback(async () => {
    setLoading(true);
    try {
      const params: OpportunityFilters = { ...filters };
      if (!params.search) delete params.search;
      if (!params.pipeline_stage_id) delete params.pipeline_stage_id;
      if (!params.owner_id) delete params.owner_id;
      const response = await opportunitiesApi.list(params);
      setOpportunities(response.data.data);
      setMeta(response.data.meta);
    } catch {
      // Handle silently
    } finally {
      setLoading(false);
    }
  }, [filters]);

  useEffect(() => {
    fetchStages();
  }, [fetchStages]);

  useEffect(() => {
    fetchOpportunities();
  }, [fetchOpportunities]);

  const handleSort = (key: string) => {
    setFilters((prev) => ({
      ...prev,
      sort_by: key,
      sort_dir: prev.sort_by === key && prev.sort_dir === 'asc' ? 'desc' : 'asc',
      page: 1,
    }));
  };

  const handleDragStart = (e: React.DragEvent, opportunityId: number) => {
    setDraggedOpportunityId(opportunityId);
    e.dataTransfer.effectAllowed = 'move';
  };

  const handleDragOver = (e: React.DragEvent) => {
    e.preventDefault();
    e.dataTransfer.dropEffect = 'move';
  };

  const handleDrop = async (e: React.DragEvent, stageId: number) => {
    e.preventDefault();
    if (!draggedOpportunityId) return;

    const opportunity = opportunities.find((o) => o.id === draggedOpportunityId);
    if (!opportunity || opportunity.stage.id === stageId) {
      setDraggedOpportunityId(null);
      return;
    }

    try {
      await opportunitiesApi.updateStage(draggedOpportunityId, stageId);
      toast('Deal stage updated', 'success');
      fetchOpportunities();
    } catch {
      toast('Failed to update deal stage', 'error');
    } finally {
      setDraggedOpportunityId(null);
    }
  };

  const columns: Column<Opportunity>[] = [
    {
      key: 'name',
      label: 'Deal Name',
      sortable: true,
      render: (opp) => (
        <div>
          <p className="font-medium text-gray-900">{opp.name}</p>
          {opp.account && (
            <p className="text-xs text-gray-500">{opp.account.name}</p>
          )}
        </div>
      ),
    },
    {
      key: 'amount',
      label: 'Amount',
      sortable: true,
      render: (opp) => (
        <span className="font-medium text-gray-900">
          {formatCurrency(opp.amount)}
        </span>
      ),
    },
    {
      key: 'stage',
      label: 'Stage',
      render: (opp) => (
        <Badge
          style={
            opp.stage.color
              ? { backgroundColor: `${opp.stage.color}20`, color: opp.stage.color }
              : undefined
          }
        >
          {opp.stage.name}
        </Badge>
      ),
    },
    {
      key: 'probability',
      label: 'Probability',
      sortable: true,
      render: (opp) => (
        <span className="text-gray-600">{opp.probability}%</span>
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
      key: 'owner',
      label: 'Owner',
      render: (opp) => (
        <span className="text-gray-600">{opp.owner?.name || '-'}</span>
      ),
    },
    {
      key: 'created_at',
      label: 'Created',
      sortable: true,
      render: (opp) => (
        <span className="text-gray-500">{formatDate(opp.created_at)}</span>
      ),
    },
  ];

  return (
    <div>
      <PageHeader
        title="Opportunities"
        description="Manage your deals and pipeline"
        action={
          <Link href="/opportunities/new">
            <Button>
              <Plus className="mr-2 h-4 w-4" />
              Add Deal
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
          placeholder="Search deals..."
          className="w-64"
        />
        <div className="w-44">
          <Select
            options={stages.map((s) => ({ value: s.id, label: s.name }))}
            placeholder="All stages"
            value={filters.pipeline_stage_id || ''}
            onChange={(e) =>
              setFilters((prev) => ({
                ...prev,
                pipeline_stage_id: e.target.value ? Number(e.target.value) : undefined,
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
          <DataTable
            columns={columns}
            data={opportunities}
            loading={loading}
            sortBy={filters.sort_by}
            sortDir={filters.sort_dir}
            onSort={handleSort}
            onRowClick={(item) =>
              router.push(`/opportunities/${item.id}`)
            }
            emptyTitle="No deals found"
            emptyDescription="Get started by adding your first deal."
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
        <div className="flex gap-4 overflow-x-auto pb-4">
          {loading ? (
            <div className="flex w-full items-center justify-center py-20">
              <Spinner size="lg" />
            </div>
          ) : (
            stages.map((stage) => {
              const stageOpportunities = opportunities.filter(
                (o) => o.stage.id === stage.id
              );
              const stageTotal = stageOpportunities.reduce(
                (sum, o) => sum + o.amount,
                0
              );
              return (
                <div
                  key={stage.id}
                  className="flex w-72 shrink-0 flex-col rounded-lg border-t-4 bg-gray-50"
                  style={{ borderTopColor: stage.color || '#94a3b8' }}
                  onDragOver={handleDragOver}
                  onDrop={(e) => handleDrop(e, stage.id)}
                >
                  <div className="px-3 py-2">
                    <div className="flex items-center justify-between">
                      <h3 className="text-sm font-semibold text-gray-700">
                        {stage.name}
                      </h3>
                      <span className="rounded-full bg-gray-200 px-2 py-0.5 text-xs font-medium text-gray-600">
                        {stageOpportunities.length}
                      </span>
                    </div>
                    <p className="mt-0.5 text-xs text-gray-500">
                      {formatCurrency(stageTotal)}
                    </p>
                  </div>
                  <div className="flex-1 space-y-2 p-2">
                    {stageOpportunities.map((opp) => (
                      <div
                        key={opp.id}
                        draggable
                        onDragStart={(e) => handleDragStart(e, opp.id)}
                        className={cn(
                          'block cursor-grab rounded-md border border-gray-200 bg-white p-3 shadow-sm transition-shadow hover:shadow-md',
                          draggedOpportunityId === opp.id && 'opacity-50'
                        )}
                      >
                        <Link
                          href={`/opportunities/${opp.id}`}
                          className="block"
                        >
                          <div className="flex items-start justify-between">
                            <p className="text-sm font-medium text-gray-900">
                              {opp.name}
                            </p>
                            <GripVertical className="h-4 w-4 shrink-0 text-gray-300" />
                          </div>
                          {opp.account && (
                            <p className="mt-0.5 text-xs text-gray-500">
                              {opp.account.name}
                            </p>
                          )}
                          <div className="mt-2 flex items-center justify-between">
                            <span className="text-sm font-semibold text-gray-900">
                              {formatCurrency(opp.amount)}
                            </span>
                            <span className="text-xs text-gray-500">
                              {opp.probability}%
                            </span>
                          </div>
                          {opp.close_date && (
                            <p className="mt-1 text-xs text-gray-400">
                              Close: {formatDate(opp.close_date)}
                            </p>
                          )}
                        </Link>
                      </div>
                    ))}
                    {stageOpportunities.length === 0 && (
                      <p className="py-4 text-center text-xs text-gray-400">
                        No deals
                      </p>
                    )}
                  </div>
                </div>
              );
            })
          )}
        </div>
      )}
    </div>
  );
}
