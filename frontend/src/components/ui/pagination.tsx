'use client';

import React from 'react';
import { cn } from '@/lib/utils/cn';
import { ChevronLeft, ChevronRight } from 'lucide-react';

interface PaginationMeta {
  current_page: number;
  last_page: number;
  total: number;
  from: number;
  to: number;
}

interface PaginationProps {
  meta: PaginationMeta;
  onPageChange: (page: number) => void;
}

function Pagination({ meta, onPageChange }: PaginationProps) {
  const { current_page, last_page, total, from, to } = meta;

  if (last_page <= 1) return null;

  const getPageNumbers = (): (number | string)[] => {
    const pages: (number | string)[] = [];
    const maxVisible = 7;

    if (last_page <= maxVisible) {
      for (let i = 1; i <= last_page; i++) {
        pages.push(i);
      }
    } else {
      pages.push(1);

      if (current_page > 3) {
        pages.push('...');
      }

      const start = Math.max(2, current_page - 1);
      const end = Math.min(last_page - 1, current_page + 1);

      for (let i = start; i <= end; i++) {
        pages.push(i);
      }

      if (current_page < last_page - 2) {
        pages.push('...');
      }

      pages.push(last_page);
    }

    return pages;
  };

  return (
    <div className="flex items-center justify-between border-t border-gray-200 px-4 py-3">
      <div className="text-sm text-gray-500">
        Showing <span className="font-medium">{from}</span> to{' '}
        <span className="font-medium">{to}</span> of{' '}
        <span className="font-medium">{total}</span> results
      </div>
      <nav className="flex items-center gap-1">
        <button
          onClick={() => onPageChange(current_page - 1)}
          disabled={current_page === 1}
          className={cn(
            'inline-flex items-center rounded-md px-2 py-2 text-sm',
            current_page === 1
              ? 'cursor-not-allowed text-gray-300'
              : 'text-gray-500 hover:bg-gray-100'
          )}
        >
          <ChevronLeft className="h-4 w-4" />
        </button>
        {getPageNumbers().map((page, idx) =>
          typeof page === 'string' ? (
            <span key={`ellipsis-${idx}`} className="px-2 py-1 text-sm text-gray-500">
              {page}
            </span>
          ) : (
            <button
              key={page}
              onClick={() => onPageChange(page)}
              className={cn(
                'inline-flex items-center rounded-md px-3 py-1 text-sm font-medium',
                page === current_page
                  ? 'bg-blue-600 text-white'
                  : 'text-gray-700 hover:bg-gray-100'
              )}
            >
              {page}
            </button>
          )
        )}
        <button
          onClick={() => onPageChange(current_page + 1)}
          disabled={current_page === last_page}
          className={cn(
            'inline-flex items-center rounded-md px-2 py-2 text-sm',
            current_page === last_page
              ? 'cursor-not-allowed text-gray-300'
              : 'text-gray-500 hover:bg-gray-100'
          )}
        >
          <ChevronRight className="h-4 w-4" />
        </button>
      </nav>
    </div>
  );
}

export { Pagination };
export type { PaginationProps, PaginationMeta };
