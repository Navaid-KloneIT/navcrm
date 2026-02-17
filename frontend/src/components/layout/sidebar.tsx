'use client';

import React from 'react';
import Link from 'next/link';
import { usePathname, useRouter } from 'next/navigation';
import {
  LayoutDashboard,
  Users,
  Building2,
  Target,
  Handshake,
  Package,
  FileText,
  TrendingUp,
  UserCircle,
  UserCog,
  Shield,
  LogOut,
  ChevronsLeft,
  ChevronsRight,
} from 'lucide-react';
import { cn } from '@/lib/utils/cn';
import { useUIStore } from '@/lib/stores/ui-store';
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

const salesNavItems: NavItem[] = [
  { label: 'Opportunities', href: '/opportunities', icon: <Handshake className="h-5 w-5" /> },
  { label: 'Products', href: '/products', icon: <Package className="h-5 w-5" /> },
  { label: 'Quotes', href: '/quotes', icon: <FileText className="h-5 w-5" /> },
  { label: 'Forecasts', href: '/forecasts', icon: <TrendingUp className="h-5 w-5" /> },
];

const settingsNavItems: NavItem[] = [
  { label: 'Profile', href: '/settings/profile', icon: <UserCircle className="h-5 w-5" /> },
  { label: 'Users', href: '/settings/users', icon: <UserCog className="h-5 w-5" />, adminOnly: true },
  { label: 'Roles', href: '/settings/roles', icon: <Shield className="h-5 w-5" />, adminOnly: true },
];

function Sidebar() {
  const pathname = usePathname();
  const router = useRouter();
  const { sidebarCollapsed, toggleSidebar } = useUIStore();
  const { hasRole, logout } = useAuthStore();
  const isAdmin = hasRole('admin');

  const handleLogout = async () => {
    try {
      await authApi.logout();
    } catch {
      // Ignore errors on logout
    }
    logout();
    router.push('/login');
  };

  const isActive = (href: string) => {
    if (href === '/dashboard') return pathname === '/dashboard';
    return pathname.startsWith(href);
  };

  const renderNavItem = (item: NavItem) => {
    if (item.adminOnly && !isAdmin) return null;

    return (
      <Link
        key={item.href}
        href={item.href}
        className={cn(
          'flex items-center gap-3 rounded-md px-3 py-2 text-sm font-medium transition-colors',
          isActive(item.href)
            ? 'bg-blue-50 text-blue-700'
            : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900',
          sidebarCollapsed && 'justify-center px-2'
        )}
        title={sidebarCollapsed ? item.label : undefined}
      >
        {item.icon}
        {!sidebarCollapsed && <span>{item.label}</span>}
      </Link>
    );
  };

  return (
    <aside
      className={cn(
        'flex h-screen flex-col border-r border-gray-200 bg-white transition-all duration-200',
        sidebarCollapsed ? 'w-16' : 'w-64'
      )}
    >
      <div className={cn('flex h-16 items-center border-b border-gray-200 px-4', sidebarCollapsed && 'justify-center px-2')}>
        {!sidebarCollapsed ? (
          <Link href="/dashboard" className="text-xl font-bold text-blue-600">
            NavCRM
          </Link>
        ) : (
          <Link href="/dashboard" className="text-lg font-bold text-blue-600">
            N
          </Link>
        )}
      </div>

      <nav className="flex-1 space-y-1 overflow-y-auto px-3 py-4">
        {mainNavItems.map(renderNavItem)}

        <div className="my-4 border-t border-gray-200" />

        {!sidebarCollapsed && (
          <p className="mb-2 px-3 text-xs font-semibold uppercase tracking-wider text-gray-400">
            Sales
          </p>
        )}

        {salesNavItems.map(renderNavItem)}

        <div className="my-4 border-t border-gray-200" />

        {!sidebarCollapsed && (
          <p className="mb-2 px-3 text-xs font-semibold uppercase tracking-wider text-gray-400">
            Settings
          </p>
        )}

        {settingsNavItems.map(renderNavItem)}
      </nav>

      <div className="border-t border-gray-200 p-3 space-y-1">
        <button
          onClick={handleLogout}
          className={cn(
            'flex w-full items-center gap-3 rounded-md px-3 py-2 text-sm font-medium text-gray-600 transition-colors hover:bg-gray-100 hover:text-gray-900',
            sidebarCollapsed && 'justify-center px-2'
          )}
          title={sidebarCollapsed ? 'Logout' : undefined}
        >
          <LogOut className="h-5 w-5" />
          {!sidebarCollapsed && <span>Logout</span>}
        </button>
        <button
          onClick={toggleSidebar}
          className={cn(
            'flex w-full items-center gap-3 rounded-md px-3 py-2 text-sm font-medium text-gray-600 transition-colors hover:bg-gray-100 hover:text-gray-900',
            sidebarCollapsed && 'justify-center px-2'
          )}
          title={sidebarCollapsed ? 'Expand sidebar' : 'Collapse sidebar'}
        >
          {sidebarCollapsed ? (
            <ChevronsRight className="h-5 w-5" />
          ) : (
            <>
              <ChevronsLeft className="h-5 w-5" />
              <span>Collapse</span>
            </>
          )}
        </button>
      </div>
    </aside>
  );
}

export { Sidebar };
