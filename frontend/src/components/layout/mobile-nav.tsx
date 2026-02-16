'use client';

import React, { useState } from 'react';
import Link from 'next/link';
import { usePathname, useRouter } from 'next/navigation';
import {
  Menu,
  X,
  LayoutDashboard,
  Users,
  Building2,
  Target,
  UserCircle,
  UserCog,
  Shield,
  LogOut,
} from 'lucide-react';
import { cn } from '@/lib/utils/cn';
import { useAuthStore } from '@/lib/stores/auth-store';
import { authApi } from '@/lib/api/auth';

interface NavItem {
  label: string;
  href: string;
  icon: React.ReactNode;
  adminOnly?: boolean;
}

const mainNavItems: NavItem[] = [
  { label: 'Dashboard', href: '/dashboard', icon: <LayoutDashboard className="h-5 w-5" /> },
  { label: 'Contacts', href: '/contacts', icon: <Users className="h-5 w-5" /> },
  { label: 'Accounts', href: '/accounts', icon: <Building2 className="h-5 w-5" /> },
  { label: 'Leads', href: '/leads', icon: <Target className="h-5 w-5" /> },
];

const settingsNavItems: NavItem[] = [
  { label: 'Profile', href: '/settings/profile', icon: <UserCircle className="h-5 w-5" /> },
  { label: 'Users', href: '/settings/users', icon: <UserCog className="h-5 w-5" />, adminOnly: true },
  { label: 'Roles', href: '/settings/roles', icon: <Shield className="h-5 w-5" />, adminOnly: true },
];

function MobileNav() {
  const [open, setOpen] = useState(false);
  const pathname = usePathname();
  const router = useRouter();
  const { hasRole, logout } = useAuthStore();
  const isAdmin = hasRole('admin');

  const isActive = (href: string) => {
    if (href === '/dashboard') return pathname === '/dashboard';
    return pathname.startsWith(href);
  };

  const handleLogout = async () => {
    try {
      await authApi.logout();
    } catch {
      // Ignore errors
    }
    logout();
    router.push('/login');
  };

  const renderNavItem = (item: NavItem) => {
    if (item.adminOnly && !isAdmin) return null;

    return (
      <Link
        key={item.href}
        href={item.href}
        onClick={() => setOpen(false)}
        className={cn(
          'flex items-center gap-3 rounded-md px-3 py-2.5 text-sm font-medium transition-colors',
          isActive(item.href)
            ? 'bg-blue-50 text-blue-700'
            : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900'
        )}
      >
        {item.icon}
        <span>{item.label}</span>
      </Link>
    );
  };

  return (
    <>
      <button
        onClick={() => setOpen(true)}
        className="rounded-md p-2 text-gray-600 hover:bg-gray-100 lg:hidden"
      >
        <Menu className="h-6 w-6" />
      </button>

      {open && (
        <div className="fixed inset-0 z-50 lg:hidden">
          <div
            className="fixed inset-0 bg-black/50"
            onClick={() => setOpen(false)}
          />
          <div className="fixed inset-y-0 left-0 w-72 bg-white shadow-xl">
            <div className="flex h-16 items-center justify-between border-b border-gray-200 px-4">
              <span className="text-xl font-bold text-blue-600">NavCRM</span>
              <button
                onClick={() => setOpen(false)}
                className="rounded-md p-1 text-gray-400 hover:bg-gray-100 hover:text-gray-600"
              >
                <X className="h-5 w-5" />
              </button>
            </div>

            <nav className="flex-1 space-y-1 px-3 py-4">
              {mainNavItems.map(renderNavItem)}

              <div className="my-4 border-t border-gray-200" />

              <p className="mb-2 px-3 text-xs font-semibold uppercase tracking-wider text-gray-400">
                Settings
              </p>

              {settingsNavItems.map(renderNavItem)}
            </nav>

            <div className="border-t border-gray-200 p-3">
              <button
                onClick={handleLogout}
                className="flex w-full items-center gap-3 rounded-md px-3 py-2.5 text-sm font-medium text-gray-600 transition-colors hover:bg-gray-100 hover:text-gray-900"
              >
                <LogOut className="h-5 w-5" />
                <span>Logout</span>
              </button>
            </div>
          </div>
        </div>
      )}
    </>
  );
}

export { MobileNav };
