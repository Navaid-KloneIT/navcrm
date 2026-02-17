'use client';

import React from 'react';
import { useRouter } from 'next/navigation';
import type { Contact } from '@/types';
import { Badge } from '@/components/ui/badge';
import { Avatar } from '@/components/ui/avatar';
import { DataTable, type Column } from '@/components/shared/data-table';
import { formatDate } from '@/lib/utils/format';

interface ContactTableProps {
  contacts: Contact[];
  loading?: boolean;
  sortBy?: string;
  sortDir?: 'asc' | 'desc';
  onSort?: (key: string) => void;
}

function ContactTable({ contacts, loading, sortBy, sortDir, onSort }: ContactTableProps) {
  const router = useRouter();

  const columns: Column<Contact>[] = [
    {
      key: 'full_name',
      label: 'Name',
      sortable: true,
      render: (contact) => (
        <div className="flex items-center gap-3">
          <Avatar name={contact.full_name} size="sm" />
          <div>
            <p className="font-medium text-gray-900">{contact.full_name}</p>
            {contact.job_title && (
              <p className="text-xs text-gray-500">{contact.job_title}</p>
            )}
          </div>
        </div>
      ),
    },
    {
      key: 'email',
      label: 'Email',
      sortable: true,
      render: (contact) => (
        <span className="text-gray-600">{contact.email || '-'}</span>
      ),
    },
    {
      key: 'phone',
      label: 'Phone',
      render: (contact) => (
        <span className="text-gray-600">{contact.phone || '-'}</span>
      ),
    },
    {
      key: 'tags',
      label: 'Tags',
      render: (contact) => (
        <div className="flex flex-wrap gap-1">
          {contact.tags.slice(0, 3).map((tag) => (
            <Badge
              key={tag.id}
              className={tag.color ? `bg-opacity-20` : undefined}
              style={tag.color ? { backgroundColor: `${tag.color}20`, color: tag.color } : undefined}
            >
              {tag.name}
            </Badge>
          ))}
          {contact.tags.length > 3 && (
            <Badge variant="default">+{contact.tags.length - 3}</Badge>
          )}
        </div>
      ),
    },
    {
      key: 'owner',
      label: 'Owner',
      render: (contact) => (
        <span className="text-gray-600">{contact.owner?.name || '-'}</span>
      ),
    },
    {
      key: 'created_at',
      label: 'Created',
      sortable: true,
      render: (contact) => (
        <span className="text-gray-500">{formatDate(contact.created_at)}</span>
      ),
    },
  ];

  return (
    <DataTable
      columns={columns}
      data={contacts}
      loading={loading}
      sortBy={sortBy}
      sortDir={sortDir}
      onSort={onSort}
      onRowClick={(item) => router.push(`/contacts/${item.id}`)}
      emptyTitle="No contacts found"
      emptyDescription="Get started by adding your first contact."
    />
  );
}

export { ContactTable };
