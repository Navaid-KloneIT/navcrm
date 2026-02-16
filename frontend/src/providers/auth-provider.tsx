'use client';

import React, { useEffect, useState } from 'react';
import { usePathname, useRouter } from 'next/navigation';
import { useAuthStore } from '@/lib/stores/auth-store';
import { authApi } from '@/lib/api/auth';
import { Spinner } from '@/components/ui/spinner';

const publicPaths = ['/login', '/register', '/forgot-password', '/reset-password'];

function AuthProvider({ children }: { children: React.ReactNode }) {
  const [loading, setLoading] = useState(true);
  const pathname = usePathname();
  const router = useRouter();
  const { token, isAuthenticated, setUser, logout } = useAuthStore();

  const isPublicPath = publicPaths.some((path) => pathname.startsWith(path));

  useEffect(() => {
    const checkAuth = async () => {
      if (token) {
        try {
          const response = await authApi.me();
          setUser(response.data.user);

          if (isPublicPath) {
            router.replace('/dashboard');
            return;
          }
        } catch {
          logout();
          if (!isPublicPath) {
            router.replace('/login');
            return;
          }
        }
      } else {
        if (!isPublicPath) {
          router.replace('/login');
          return;
        }
      }
      setLoading(false);
    };

    checkAuth();
  }, [token, isPublicPath, router, setUser, logout]);

  if (loading) {
    return (
      <div className="flex h-screen items-center justify-center bg-gray-50">
        <div className="flex flex-col items-center gap-3">
          <Spinner size="lg" />
          <p className="text-sm text-gray-500">Loading...</p>
        </div>
      </div>
    );
  }

  return <>{children}</>;
}

export { AuthProvider };
