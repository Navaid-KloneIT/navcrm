'use client';

import React, { useEffect, useState } from 'react';
import { Users, Building2, Target, TrendingUp } from 'lucide-react';
import { contactsApi } from '@/lib/api/contacts';
import { accountsApi } from '@/lib/api/accounts';
import { leadsApi } from '@/lib/api/leads';
import { StatsCard } from '@/components/shared/stats-card';
import { Card, CardHeader, CardTitle, CardContent } from '@/components/ui/card';
import { Skeleton } from '@/components/ui/skeleton';

interface DashboardStats {
  totalContacts: number;
  totalAccounts: number;
  totalLeads: number;
  convertedLeads: number;
}

export default function DashboardPage() {
  const [stats, setStats] = useState<DashboardStats | null>(null);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    const fetchStats = async () => {
      try {
        const [contactsRes, accountsRes, leadsRes, convertedRes] = await Promise.all([
          contactsApi.list({ per_page: 1 }),
          accountsApi.list({ per_page: 1 }),
          leadsApi.list({ per_page: 1 }),
          leadsApi.list({ per_page: 1, is_converted: true }),
        ]);

        setStats({
          totalContacts: contactsRes.data.meta.total,
          totalAccounts: accountsRes.data.meta.total,
          totalLeads: leadsRes.data.meta.total,
          convertedLeads: convertedRes.data.meta.total,
        });
      } catch {
        // Silently handle - stats will show as 0
        setStats({
          totalContacts: 0,
          totalAccounts: 0,
          totalLeads: 0,
          convertedLeads: 0,
        });
      } finally {
        setLoading(false);
      }
    };

    fetchStats();
  }, []);

  return (
    <div>
      <h1 className="mb-6 text-2xl font-bold text-gray-900">Dashboard</h1>

      <div className="mb-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        {loading ? (
          <>
            {Array.from({ length: 4 }).map((_, i) => (
              <Card key={i}>
                <CardContent className="py-5">
                  <Skeleton className="mb-2 h-4 w-24" />
                  <Skeleton className="h-8 w-16" />
                </CardContent>
              </Card>
            ))}
          </>
        ) : (
          <>
            <StatsCard
              title="Total Contacts"
              value={stats?.totalContacts ?? 0}
              icon={<Users className="h-5 w-5" />}
            />
            <StatsCard
              title="Total Accounts"
              value={stats?.totalAccounts ?? 0}
              icon={<Building2 className="h-5 w-5" />}
            />
            <StatsCard
              title="Total Leads"
              value={stats?.totalLeads ?? 0}
              icon={<Target className="h-5 w-5" />}
            />
            <StatsCard
              title="Converted Leads"
              value={stats?.convertedLeads ?? 0}
              icon={<TrendingUp className="h-5 w-5" />}
            />
          </>
        )}
      </div>

      <Card>
        <CardHeader>
          <CardTitle>Recent Activity</CardTitle>
        </CardHeader>
        <CardContent>
          <div className="py-8 text-center text-sm text-gray-500">
            <p>Activity feed will display recent interactions, new contacts, and lead updates.</p>
            <p className="mt-1 text-xs text-gray-400">
              Activities across your contacts, accounts, and leads will appear here.
            </p>
          </div>
        </CardContent>
      </Card>
    </div>
  );
}
