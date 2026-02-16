'use client';

import React, { useEffect, useState, useCallback } from 'react';
import Link from 'next/link';
import { Plus } from 'lucide-react';
import { accountsApi } from '@/lib/api/accounts';
import type { Account, AccountFilters, PaginatedResponse } from '@/types';
import { PageHeader } from '@/components/shared/page-header';
import { Button } from '@/components/ui/button';
import { Pagination } from '@/components/ui/pagination';
import { SearchInput } from '@/components/shared/search-input';
import { AccountTable } from '@/components/accounts/account-table';

export default function AccountsPage() {
  const [accounts, setAccounts] = useState<Account[]>([]);
  const [meta, setMeta] = useState<PaginatedResponse<Account>['meta'] | null>(null);
  const [loading, setLoading] = useState(true);
  const [filters, setFilters] = useState<AccountFilters>({
    search: '',
    sort_by: 'created_at',
    sort_dir: 'desc',
    page: 1,
    per_page: 15,
  });

  const fetchAccounts = useCallback(async () => {
    setLoading(true);
    try {
      const params: AccountFilters = { ...filters };
      if (!params.search) delete params.search;
      const response = await accountsApi.list(params);
      setAccounts(response.data.data);
      setMeta(response.data.meta);
    } catch {
      // Handle silently
    } finally {
      setLoading(false);
    }
  }, [filters]);

  useEffect(() => {
    fetchAccounts();
  }, [fetchAccounts]);

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
        title="Accounts"
        description="Manage your business accounts"
        action={
          <Link href="/accounts/new">
            <Button>
              <Plus className="mr-2 h-4 w-4" />
              Add Account
            </Button>
          </Link>
        }
      />

      <div className="mb-4">
        <SearchInput
          value={filters.search || ''}
          onChange={(search) =>
            setFilters((prev) => ({ ...prev, search, page: 1 }))
          }
          placeholder="Search accounts..."
          className="w-64"
        />
      </div>

      <div className="rounded-lg border border-gray-200 bg-white">
        <AccountTable
          accounts={accounts}
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
