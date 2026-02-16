'use client';

import React from 'react';
import Link from 'next/link';
import { usePathname } from 'next/navigation';
import { ChevronRight, Home } from 'lucide-react';

const segmentLabels: Record<string, string> = {
  dashboard: 'Dashboard',
  contacts: 'Contacts',
  accounts: 'Accounts',
  leads: 'Leads',
  settings: 'Settings',
  profile: 'Profile',
  users: 'Users',
  roles: 'Roles',
  new: 'New',
  edit: 'Edit',
};

function Breadcrumbs() {
  const pathname = usePathname();
  const segments = pathname.split('/').filter(Boolean);

  if (segments.length === 0) return null;

  const breadcrumbs = segments.map((segment, index) => {
    const href = '/' + segments.slice(0, index + 1).join('/');
    const label = segmentLabels[segment] || segment.charAt(0).toUpperCase() + segment.slice(1);
    const isLast = index === segments.length - 1;

    return { href, label, isLast, isId: /^\d+$/.test(segment) };
  });

  return (
    <nav className="flex items-center gap-1 text-sm">
      <Link
        href="/dashboard"
        className="text-gray-400 hover:text-gray-600"
      >
        <Home className="h-4 w-4" />
      </Link>
      {breadcrumbs.map((crumb, index) => (
        <React.Fragment key={crumb.href}>
          <ChevronRight className="h-4 w-4 text-gray-300" />
          {crumb.isLast ? (
            <span className="font-medium text-gray-700">
              {crumb.isId ? `#${crumb.label}` : crumb.label}
            </span>
          ) : (
            <Link
              href={crumb.href}
              className="text-gray-500 hover:text-gray-700"
            >
              {crumb.isId ? `#${crumb.label}` : crumb.label}
            </Link>
          )}
        </React.Fragment>
      ))}
    </nav>
  );
}

export { Breadcrumbs };
