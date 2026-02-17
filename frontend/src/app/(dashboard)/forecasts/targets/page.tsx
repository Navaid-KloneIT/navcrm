'use client';

import React, { useEffect, useState, useCallback } from 'react';
import Link from 'next/link';
import { Plus, Pencil, Trash2, ArrowLeft } from 'lucide-react';
import { salesTargetsApi } from '@/lib/api/sales-targets';
import { usersApi } from '@/lib/api/users';
import type { SalesTarget, SalesTargetFilters, PaginatedResponse } from '@/types';
import type { SalesTargetFormData } from '@/lib/validations/sales-target';
import { PERIOD_TYPES, FORECAST_CATEGORIES } from '@/lib/utils/constants';
import { PageHeader } from '@/components/shared/page-header';
import { Button } from '@/components/ui/button';
import { Select } from '@/components/ui/select';
import { Spinner } from '@/components/ui/spinner';
import { Pagination } from '@/components/ui/pagination';
import { Dialog } from '@/components/ui/dialog';
import { ConfirmDialog } from '@/components/shared/confirm-dialog';
import { TargetForm } from '@/components/forecasts/target-form';

function formatCurrency(amount: number): string {
  return new Intl.NumberFormat('en-US', {
    style: 'currency',
    currency: 'USD',
    minimumFractionDigits: 0,
    maximumFractionDigits: 0,
  }).format(amount);
}

function getCategoryLabel(value: string | null): string {
  if (!value) return '-';
  const found = FORECAST_CATEGORIES.find((c) => c.value === value);
  return found ? found.label : value;
}

function getPeriodTypeLabel(value: string): string {
  const found = PERIOD_TYPES.find((p) => p.value === value);
  return found ? found.label : value;
}

export default function SalesTargetsPage() {
  const [targets, setTargets] = useState<SalesTarget[]>([]);
  const [meta, setMeta] = useState<PaginatedResponse<SalesTarget>['meta'] | null>(null);
  const [loading, setLoading] = useState(true);
  const [saving, setSaving] = useState(false);
  const [users, setUsers] = useState<{ id: number; name: string }[]>([]);
  const [filters, setFilters] = useState<SalesTargetFilters>({
    page: 1,
    per_page: 15,
  });

  // Dialog state
  const [formOpen, setFormOpen] = useState(false);
  const [editingTarget, setEditingTarget] = useState<SalesTarget | null>(null);
  const [deleteTarget, setDeleteTarget] = useState<SalesTarget | null>(null);

  const fetchTargets = useCallback(async () => {
    setLoading(true);
    try {
      const params: SalesTargetFilters = { ...filters };
      if (!params.period_type) delete params.period_type;
      const response = await salesTargetsApi.list(params);
      setTargets(response.data.data);
      setMeta(response.data.meta);
    } catch {
      // Handle silently
    } finally {
      setLoading(false);
    }
  }, [filters]);

  const fetchUsers = useCallback(async () => {
    try {
      const response = await usersApi.list({ per_page: 100 });
      setUsers(response.data.data.map((u) => ({ id: u.id, name: u.name })));
    } catch {
      // Handle silently
    }
  }, []);

  useEffect(() => {
    fetchTargets();
  }, [fetchTargets]);

  useEffect(() => {
    fetchUsers();
  }, [fetchUsers]);

  const handleCreate = () => {
    setEditingTarget(null);
    setFormOpen(true);
  };

  const handleEdit = (target: SalesTarget) => {
    setEditingTarget(target);
    setFormOpen(true);
  };

  const handleFormClose = () => {
    setFormOpen(false);
    setEditingTarget(null);
  };

  const handleSubmit = async (data: SalesTargetFormData) => {
    setSaving(true);
    try {
      const payload = {
        ...data,
        user_id: data.user_id || null,
      };

      if (editingTarget) {
        await salesTargetsApi.update(editingTarget.id, payload);
      } else {
        await salesTargetsApi.create(payload);
      }
      handleFormClose();
      fetchTargets();
    } catch {
      // Handle silently
    } finally {
      setSaving(false);
    }
  };

  const handleDelete = async () => {
    if (!deleteTarget) return;
    try {
      await salesTargetsApi.delete(deleteTarget.id);
      setDeleteTarget(null);
      fetchTargets();
    } catch {
      // Handle silently
    }
  };

  return (
    <div>
      <PageHeader
        title="Sales Targets"
        description="Manage sales targets for your team"
        action={
          <div className="flex items-center gap-3">
            <Link href="/forecasts">
              <Button variant="outline">
                <ArrowLeft className="mr-2 h-4 w-4" />
                Forecasts
              </Button>
            </Link>
            <Button onClick={handleCreate}>
              <Plus className="mr-2 h-4 w-4" />
              Add Target
            </Button>
          </div>
        }
      />

      <div className="mb-4 flex flex-wrap items-end gap-4">
        <div className="w-40">
          <Select
            options={PERIOD_TYPES.map((p) => ({ value: p.value, label: p.label }))}
            placeholder="All periods"
            value={filters.period_type || ''}
            onChange={(e) =>
              setFilters((prev) => ({
                ...prev,
                period_type: e.target.value || undefined,
                page: 1,
              }))
            }
          />
        </div>
      </div>

      <div className="rounded-lg border border-gray-200 bg-white">
        {loading ? (
          <div className="flex items-center justify-center py-20">
            <Spinner size="lg" />
          </div>
        ) : targets.length === 0 ? (
          <div className="py-20 text-center">
            <p className="text-sm text-gray-500">No sales targets found</p>
            <Button className="mt-4" onClick={handleCreate}>
              <Plus className="mr-2 h-4 w-4" />
              Add Your First Target
            </Button>
          </div>
        ) : (
          <>
            <div className="overflow-x-auto">
              <table className="w-full text-sm">
                <thead>
                  <tr className="border-b border-gray-200 bg-gray-50 text-left">
                    <th className="px-4 py-3 font-medium text-gray-500">User</th>
                    <th className="px-4 py-3 font-medium text-gray-500">Period</th>
                    <th className="px-4 py-3 font-medium text-gray-500">Date Range</th>
                    <th className="px-4 py-3 font-medium text-gray-500 text-right">Amount</th>
                    <th className="px-4 py-3 font-medium text-gray-500">Category</th>
                    <th className="px-4 py-3 font-medium text-gray-500 text-right">Actions</th>
                  </tr>
                </thead>
                <tbody className="divide-y divide-gray-100">
                  {targets.map((target) => (
                    <tr key={target.id} className="hover:bg-gray-50">
                      <td className="px-4 py-3 font-medium text-gray-900">
                        {target.user ? target.user.name : 'Team Target'}
                      </td>
                      <td className="px-4 py-3 text-gray-600">
                        {getPeriodTypeLabel(target.period_type)}
                      </td>
                      <td className="px-4 py-3 text-gray-600">
                        {target.period_start} - {target.period_end}
                      </td>
                      <td className="px-4 py-3 text-right font-medium text-gray-900">
                        {formatCurrency(target.target_amount)}
                      </td>
                      <td className="px-4 py-3 text-gray-600">
                        {getCategoryLabel(target.category)}
                      </td>
                      <td className="px-4 py-3 text-right">
                        <div className="flex items-center justify-end gap-1">
                          <button
                            onClick={() => handleEdit(target)}
                            className="rounded p-1.5 text-gray-400 hover:bg-gray-100 hover:text-gray-600"
                            title="Edit target"
                          >
                            <Pencil className="h-4 w-4" />
                          </button>
                          <button
                            onClick={() => setDeleteTarget(target)}
                            className="rounded p-1.5 text-gray-400 hover:bg-red-50 hover:text-red-600"
                            title="Delete target"
                          >
                            <Trash2 className="h-4 w-4" />
                          </button>
                        </div>
                      </td>
                    </tr>
                  ))}
                </tbody>
              </table>
            </div>
            {meta && (
              <Pagination
                meta={meta}
                onPageChange={(page) =>
                  setFilters((prev) => ({ ...prev, page }))
                }
              />
            )}
          </>
        )}
      </div>

      <Dialog
        open={formOpen}
        onClose={handleFormClose}
        title={editingTarget ? 'Edit Sales Target' : 'Add Sales Target'}
        size="md"
      >
        <TargetForm
          defaultValues={
            editingTarget
              ? {
                  user_id: editingTarget.user_id,
                  period_type: editingTarget.period_type,
                  period_start: editingTarget.period_start,
                  period_end: editingTarget.period_end,
                  target_amount: editingTarget.target_amount,
                  category: editingTarget.category || '',
                }
              : undefined
          }
          onSubmit={handleSubmit}
          onCancel={handleFormClose}
          loading={saving}
          users={users}
        />
      </Dialog>

      <ConfirmDialog
        open={!!deleteTarget}
        onClose={() => setDeleteTarget(null)}
        onConfirm={handleDelete}
        title="Delete Sales Target"
        message={`Are you sure you want to delete the target for ${deleteTarget?.user?.name || 'Team Target'}? This action cannot be undone.`}
        confirmText="Delete"
        variant="danger"
      />
    </div>
  );
}
