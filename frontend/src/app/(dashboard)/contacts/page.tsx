'use client';

import React, { useEffect, useState, useCallback } from 'react';
import Link from 'next/link';
import { Plus } from 'lucide-react';
import { contactsApi } from '@/lib/api/contacts';
import type { Contact, ContactFilters as ContactFiltersType, PaginatedResponse } from '@/types';
import { PageHeader } from '@/components/shared/page-header';
import { Button } from '@/components/ui/button';
import { Pagination } from '@/components/ui/pagination';
import { ContactTable } from '@/components/contacts/contact-table';
import { ContactFilters } from '@/components/contacts/contact-filters';

export default function ContactsPage() {
  const [contacts, setContacts] = useState<Contact[]>([]);
  const [meta, setMeta] = useState<PaginatedResponse<Contact>['meta'] | null>(null);
  const [loading, setLoading] = useState(true);
  const [filters, setFilters] = useState<ContactFiltersType>({
    search: '',
    source: '',
    sort_by: 'created_at',
    sort_dir: 'desc',
    page: 1,
    per_page: 15,
  });

  const fetchContacts = useCallback(async () => {
    setLoading(true);
    try {
      const params: ContactFiltersType = { ...filters };
      if (!params.search) delete params.search;
      if (!params.source) delete params.source;
      const response = await contactsApi.list(params);
      setContacts(response.data.data);
      setMeta(response.data.meta);
    } catch {
      // Handle silently
    } finally {
      setLoading(false);
    }
  }, [filters]);

  useEffect(() => {
    fetchContacts();
  }, [fetchContacts]);

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
        title="Contacts"
        description="Manage your contacts and relationships"
        action={
          <Link href="/contacts/new">
            <Button>
              <Plus className="mr-2 h-4 w-4" />
              Add Contact
            </Button>
          </Link>
        }
      />

      <div className="mb-4">
        <ContactFilters
          search={filters.search || ''}
          onSearchChange={(search) =>
            setFilters((prev) => ({ ...prev, search, page: 1 }))
          }
          source={filters.source || ''}
          onSourceChange={(source) =>
            setFilters((prev) => ({ ...prev, source, page: 1 }))
          }
        />
      </div>

      <div className="rounded-lg border border-gray-200 bg-white">
        <ContactTable
          contacts={contacts}
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
