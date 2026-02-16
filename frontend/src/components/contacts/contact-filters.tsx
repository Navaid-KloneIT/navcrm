'use client';

import React from 'react';
import { SearchInput } from '@/components/shared/search-input';
import { Select } from '@/components/ui/select';

interface ContactFiltersProps {
  search: string;
  onSearchChange: (value: string) => void;
  source: string;
  onSourceChange: (value: string) => void;
}

const sourceOptions = [
  { value: 'website', label: 'Website' },
  { value: 'referral', label: 'Referral' },
  { value: 'linkedin', label: 'LinkedIn' },
  { value: 'cold_call', label: 'Cold Call' },
  { value: 'trade_show', label: 'Trade Show' },
  { value: 'other', label: 'Other' },
];

function ContactFilters({
  search,
  onSearchChange,
  source,
  onSourceChange,
}: ContactFiltersProps) {
  return (
    <div className="flex flex-wrap items-end gap-4">
      <div className="w-64">
        <SearchInput
          value={search}
          onChange={onSearchChange}
          placeholder="Search contacts..."
        />
      </div>
      <div className="w-48">
        <Select
          options={sourceOptions}
          placeholder="All sources"
          value={source}
          onChange={(e) => onSourceChange(e.target.value)}
        />
      </div>
    </div>
  );
}

export { ContactFilters };
