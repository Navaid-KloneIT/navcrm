'use client';

import React from 'react';
import Link from 'next/link';
import type { Lead, LeadStatus } from '@/types';
import { LEAD_STATUSES } from '@/lib/utils/constants';
import { LeadScoreBadge } from './lead-score-badge';
import { cn } from '@/lib/utils/cn';

interface LeadKanbanProps {
  leads: Lead[];
}

const statusColumnColors: Record<LeadStatus, string> = {
  new: 'border-t-blue-400',
  contacted: 'border-t-yellow-400',
  qualified: 'border-t-green-400',
  converted: 'border-t-purple-400',
  recycled: 'border-t-gray-400',
};

function LeadKanban({ leads }: LeadKanbanProps) {
  const columns: { status: LeadStatus; label: string; leads: Lead[] }[] = LEAD_STATUSES.map(
    (s) => ({
      status: s.value as LeadStatus,
      label: s.label,
      leads: leads.filter((l) => l.status === s.value),
    })
  );

  return (
    <div className="flex gap-4 overflow-x-auto pb-4">
      {columns.map((column) => (
        <div
          key={column.status}
          className={cn(
            'flex w-64 shrink-0 flex-col rounded-lg border-t-4 bg-gray-50',
            statusColumnColors[column.status]
          )}
        >
          <div className="flex items-center justify-between px-3 py-2">
            <h3 className="text-sm font-semibold text-gray-700">{column.label}</h3>
            <span className="rounded-full bg-gray-200 px-2 py-0.5 text-xs font-medium text-gray-600">
              {column.leads.length}
            </span>
          </div>
          <div className="flex-1 space-y-2 p-2">
            {column.leads.map((lead) => (
              <Link
                key={lead.id}
                href={`/leads/${lead.id}`}
                className="block rounded-md border border-gray-200 bg-white p-3 shadow-sm transition-shadow hover:shadow-md"
              >
                <p className="text-sm font-medium text-gray-900">
                  {lead.full_name}
                </p>
                {lead.company_name && (
                  <p className="mt-0.5 text-xs text-gray-500">
                    {lead.company_name}
                  </p>
                )}
                <div className="mt-2">
                  <LeadScoreBadge score={lead.score} />
                </div>
              </Link>
            ))}
            {column.leads.length === 0 && (
              <p className="py-4 text-center text-xs text-gray-400">
                No leads
              </p>
            )}
          </div>
        </div>
      ))}
    </div>
  );
}

export { LeadKanban };
