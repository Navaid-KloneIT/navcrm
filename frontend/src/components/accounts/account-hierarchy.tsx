'use client';

import React from 'react';
import Link from 'next/link';
import { Building2, ChevronRight } from 'lucide-react';
import type { Account } from '@/types';
import { Card, CardHeader, CardTitle, CardContent } from '@/components/ui/card';

interface AccountHierarchyProps {
  parent?: Account | null;
  children?: Account[];
}

function AccountHierarchy({ parent, children = [] }: AccountHierarchyProps) {
  return (
    <Card>
      <CardHeader>
        <CardTitle>Account Hierarchy</CardTitle>
      </CardHeader>
      <CardContent className="space-y-4">
        {parent && (
          <div>
            <p className="mb-2 text-xs font-semibold uppercase tracking-wider text-gray-400">
              Parent Account
            </p>
            <Link
              href={`/accounts/${parent.id}`}
              className="flex items-center gap-2 rounded-md border border-gray-200 p-3 transition-colors hover:bg-gray-50"
            >
              <Building2 className="h-5 w-5 text-gray-400" />
              <span className="font-medium text-blue-600">{parent.name}</span>
              <ChevronRight className="ml-auto h-4 w-4 text-gray-400" />
            </Link>
          </div>
        )}

        {children.length > 0 && (
          <div>
            <p className="mb-2 text-xs font-semibold uppercase tracking-wider text-gray-400">
              Child Accounts ({children.length})
            </p>
            <div className="space-y-2">
              {children.map((child) => (
                <Link
                  key={child.id}
                  href={`/accounts/${child.id}`}
                  className="flex items-center gap-2 rounded-md border border-gray-200 p-3 transition-colors hover:bg-gray-50"
                >
                  <Building2 className="h-4 w-4 text-gray-400" />
                  <span className="text-sm font-medium text-blue-600">
                    {child.name}
                  </span>
                  {child.industry && (
                    <span className="text-xs text-gray-400 capitalize">
                      {child.industry}
                    </span>
                  )}
                  <ChevronRight className="ml-auto h-4 w-4 text-gray-400" />
                </Link>
              ))}
            </div>
          </div>
        )}

        {!parent && children.length === 0 && (
          <p className="py-4 text-center text-sm text-gray-500">
            No hierarchy relationships established.
          </p>
        )}
      </CardContent>
    </Card>
  );
}

export { AccountHierarchy };
