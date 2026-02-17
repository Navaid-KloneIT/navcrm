'use client';

import React, { useEffect, useState, useCallback } from 'react';
import { useParams, useRouter } from 'next/navigation';
import Link from 'next/link';
import {
  Pencil,
  Trash2,
  DollarSign,
  Calendar,
  TrendingUp,
  Users,
  FileText,
  Trophy,
  XCircle,
  Building2,
  User,
  Plus,
} from 'lucide-react';
import { opportunitiesApi } from '@/lib/api/opportunities';
import { quotesApi } from '@/lib/api/quotes';
import type { Opportunity, OpportunityTeamMember, Quote } from '@/types';
import { useToast } from '@/components/ui/toast';
import { PageHeader } from '@/components/shared/page-header';
import { Card, CardHeader, CardTitle, CardContent } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Spinner } from '@/components/ui/spinner';
import { ConfirmDialog } from '@/components/shared/confirm-dialog';
import { formatDate, formatCurrency } from '@/lib/utils/format';
import type { AxiosError } from 'axios';
import type { ApiError } from '@/types';

export default function OpportunityDetailPage() {
  const params = useParams();
  const router = useRouter();
  const { toast } = useToast();
  const [opportunity, setOpportunity] = useState<Opportunity | null>(null);
  const [teamMembers, setTeamMembers] = useState<OpportunityTeamMember[]>([]);
  const [quotes, setQuotes] = useState<Quote[]>([]);
  const [loading, setLoading] = useState(true);
  const [deleteOpen, setDeleteOpen] = useState(false);

  const opportunityId = Number(params.id);

  const fetchOpportunity = useCallback(async () => {
    try {
      const response = await opportunitiesApi.get(opportunityId);
      setOpportunity(response.data.data);
    } catch {
      toast('Failed to load deal', 'error');
      router.push('/opportunities');
    } finally {
      setLoading(false);
    }
  }, [opportunityId, router, toast]);

  const fetchTeam = useCallback(async () => {
    try {
      const response = await opportunitiesApi.getTeam(opportunityId);
      setTeamMembers(response.data.data);
    } catch {
      // Handle silently
    }
  }, [opportunityId]);

  const fetchQuotes = useCallback(async () => {
    try {
      const response = await quotesApi.list({ opportunity_id: opportunityId, per_page: 50 });
      setQuotes(response.data.data);
    } catch {
      // Handle silently
    }
  }, [opportunityId]);

  useEffect(() => {
    fetchOpportunity();
    fetchTeam();
    fetchQuotes();
  }, [fetchOpportunity, fetchTeam, fetchQuotes]);

  const handleDelete = async () => {
    try {
      await opportunitiesApi.delete(opportunityId);
      toast('Deal deleted successfully', 'success');
      router.push('/opportunities');
    } catch (err) {
      const error = err as AxiosError<ApiError>;
      toast(error.response?.data?.message || 'Failed to delete deal', 'error');
    }
  };

  if (loading) {
    return (
      <div className="flex items-center justify-center py-20">
        <Spinner size="lg" />
      </div>
    );
  }

  if (!opportunity) return null;

  const isWon = !!opportunity.won_at;
  const isLost = !!opportunity.lost_at;

  const quoteStatusColors: Record<string, string> = {
    draft: 'bg-gray-100 text-gray-800',
    sent: 'bg-blue-100 text-blue-800',
    accepted: 'bg-green-100 text-green-800',
    rejected: 'bg-red-100 text-red-800',
    expired: 'bg-yellow-100 text-yellow-800',
  };

  return (
    <div>
      <PageHeader
        title={opportunity.name}
        description={opportunity.account?.name || undefined}
        action={
          <div className="flex gap-2">
            <Link href={`/opportunities/${opportunity.id}/edit`}>
              <Button variant="outline">
                <Pencil className="mr-2 h-4 w-4" />
                Edit
              </Button>
            </Link>
            <Button variant="destructive" onClick={() => setDeleteOpen(true)}>
              <Trash2 className="mr-2 h-4 w-4" />
              Delete
            </Button>
          </div>
        }
      />

      {(isWon || isLost) && (
        <div
          className={`mb-6 flex items-center gap-2 rounded-lg border p-4 ${
            isWon
              ? 'border-green-200 bg-green-50'
              : 'border-red-200 bg-red-50'
          }`}
        >
          {isWon ? (
            <Trophy className="h-5 w-5 text-green-600" />
          ) : (
            <XCircle className="h-5 w-5 text-red-600" />
          )}
          <div>
            <p
              className={`text-sm font-medium ${
                isWon ? 'text-green-800' : 'text-red-800'
              }`}
            >
              {isWon ? 'Deal Won' : 'Deal Lost'}
              {isWon && opportunity.won_at
                ? ` on ${formatDate(opportunity.won_at)}`
                : ''}
              {isLost && opportunity.lost_at
                ? ` on ${formatDate(opportunity.lost_at)}`
                : ''}
            </p>
            {isLost && opportunity.lost_reason && (
              <p className="mt-0.5 text-sm text-red-600">
                Reason: {opportunity.lost_reason}
              </p>
            )}
          </div>
        </div>
      )}

      <div className="grid gap-6 lg:grid-cols-3">
        <div className="space-y-6 lg:col-span-2">
          {/* Deal Info Card */}
          <Card>
            <CardHeader>
              <CardTitle>Deal Information</CardTitle>
            </CardHeader>
            <CardContent>
              <div className="mb-4 flex flex-wrap items-center gap-3">
                <Badge
                  style={
                    opportunity.stage.color
                      ? {
                          backgroundColor: `${opportunity.stage.color}20`,
                          color: opportunity.stage.color,
                        }
                      : undefined
                  }
                >
                  {opportunity.stage.name}
                </Badge>
                <span className="text-sm text-gray-500">
                  {opportunity.probability}% probability
                </span>
              </div>

              <div className="grid gap-4 sm:grid-cols-2">
                <div className="flex items-center gap-2 text-sm">
                  <DollarSign className="h-4 w-4 text-gray-400" />
                  <span className="text-gray-500">Amount:</span>
                  <span className="font-semibold text-gray-900">
                    {formatCurrency(opportunity.amount)}
                  </span>
                </div>
                <div className="flex items-center gap-2 text-sm">
                  <TrendingUp className="h-4 w-4 text-gray-400" />
                  <span className="text-gray-500">Weighted:</span>
                  <span className="font-semibold text-gray-900">
                    {formatCurrency(opportunity.weighted_amount)}
                  </span>
                </div>
                {opportunity.close_date && (
                  <div className="flex items-center gap-2 text-sm">
                    <Calendar className="h-4 w-4 text-gray-400" />
                    <span className="text-gray-500">Close Date:</span>
                    <span className="font-medium text-gray-900">
                      {formatDate(opportunity.close_date)}
                    </span>
                  </div>
                )}
                {opportunity.account && (
                  <div className="flex items-center gap-2 text-sm">
                    <Building2 className="h-4 w-4 text-gray-400" />
                    <span className="text-gray-500">Account:</span>
                    <Link
                      href={`/accounts/${opportunity.account.id}`}
                      className="font-medium text-blue-600 hover:underline"
                    >
                      {opportunity.account.name}
                    </Link>
                  </div>
                )}
                {opportunity.contact && (
                  <div className="flex items-center gap-2 text-sm">
                    <User className="h-4 w-4 text-gray-400" />
                    <span className="text-gray-500">Contact:</span>
                    <Link
                      href={`/contacts/${opportunity.contact.id}`}
                      className="font-medium text-blue-600 hover:underline"
                    >
                      {opportunity.contact.full_name}
                    </Link>
                  </div>
                )}
                {opportunity.competitor && (
                  <div className="flex items-center gap-2 text-sm sm:col-span-2">
                    <span className="text-gray-500">Competitor:</span>
                    <span className="font-medium text-gray-900">
                      {opportunity.competitor}
                    </span>
                  </div>
                )}
              </div>

              {opportunity.description && (
                <div className="mt-4 border-t border-gray-200 pt-4">
                  <h4 className="mb-1 text-sm font-medium text-gray-700">
                    Description
                  </h4>
                  <p className="text-sm text-gray-600">{opportunity.description}</p>
                </div>
              )}

              {opportunity.next_steps && (
                <div className="mt-4 border-t border-gray-200 pt-4">
                  <h4 className="mb-1 text-sm font-medium text-gray-700">
                    Next Steps
                  </h4>
                  <p className="text-sm text-gray-600">{opportunity.next_steps}</p>
                </div>
              )}
            </CardContent>
          </Card>

          {/* Team Card */}
          <Card>
            <CardHeader>
              <div className="flex items-center justify-between">
                <CardTitle>Team</CardTitle>
              </div>
            </CardHeader>
            <CardContent>
              {teamMembers.length > 0 ? (
                <div className="divide-y divide-gray-100">
                  {teamMembers.map((member) => (
                    <div
                      key={member.id}
                      className="flex items-center justify-between py-3 first:pt-0 last:pb-0"
                    >
                      <div className="flex items-center gap-3">
                        <div className="flex h-8 w-8 items-center justify-center rounded-full bg-blue-100 text-sm font-medium text-blue-700">
                          {member.name
                            .split(' ')
                            .map((n) => n[0])
                            .join('')
                            .toUpperCase()
                            .slice(0, 2)}
                        </div>
                        <div>
                          <p className="text-sm font-medium text-gray-900">
                            {member.name}
                          </p>
                          <p className="text-xs text-gray-500">{member.email}</p>
                        </div>
                      </div>
                      <div className="flex items-center gap-2">
                        <Badge variant="default">{member.role}</Badge>
                        <span className="text-xs text-gray-500">
                          {member.split_percentage}%
                        </span>
                      </div>
                    </div>
                  ))}
                </div>
              ) : (
                <p className="text-sm text-gray-500">No team members assigned</p>
              )}
            </CardContent>
          </Card>

          {/* Quotes Card */}
          <Card>
            <CardHeader>
              <div className="flex items-center justify-between">
                <CardTitle>Quotes</CardTitle>
                <Link href={`/quotes/new?opportunity_id=${opportunity.id}`}>
                  <Button size="sm" variant="outline">
                    <Plus className="mr-1 h-3 w-3" />
                    New Quote
                  </Button>
                </Link>
              </div>
            </CardHeader>
            <CardContent>
              {quotes.length > 0 ? (
                <div className="divide-y divide-gray-100">
                  {quotes.map((quote) => (
                    <Link
                      key={quote.id}
                      href={`/quotes/${quote.id}`}
                      className="flex items-center justify-between py-3 first:pt-0 last:pb-0 hover:bg-gray-50 -mx-2 px-2 rounded"
                    >
                      <div>
                        <p className="text-sm font-medium text-gray-900">
                          {quote.quote_number}
                        </p>
                        <p className="text-xs text-gray-500">
                          {formatDate(quote.created_at)}
                        </p>
                      </div>
                      <div className="flex items-center gap-3">
                        <span className="text-sm font-semibold text-gray-900">
                          {formatCurrency(quote.total)}
                        </span>
                        <Badge className={quoteStatusColors[quote.status] || ''}>
                          {quote.status}
                        </Badge>
                      </div>
                    </Link>
                  ))}
                </div>
              ) : (
                <p className="text-sm text-gray-500">No quotes linked to this deal</p>
              )}
            </CardContent>
          </Card>
        </div>

        {/* Sidebar */}
        <div className="space-y-6">
          <Card>
            <CardHeader>
              <CardTitle>Details</CardTitle>
            </CardHeader>
            <CardContent className="space-y-3 text-sm">
              <div className="flex justify-between">
                <span className="text-gray-500">Source</span>
                <span className="font-medium capitalize text-gray-900">
                  {opportunity.source || '-'}
                </span>
              </div>
              <div className="flex justify-between">
                <span className="text-gray-500">Owner</span>
                <span className="font-medium text-gray-900">
                  {opportunity.owner?.name || '-'}
                </span>
              </div>
              <div className="flex justify-between">
                <span className="text-gray-500">Currency</span>
                <span className="font-medium text-gray-900">
                  {opportunity.currency || 'USD'}
                </span>
              </div>
              <div className="flex justify-between">
                <span className="text-gray-500">Created</span>
                <span className="font-medium text-gray-900">
                  {formatDate(opportunity.created_at)}
                </span>
              </div>
              <div className="flex justify-between">
                <span className="text-gray-500">Updated</span>
                <span className="font-medium text-gray-900">
                  {formatDate(opportunity.updated_at)}
                </span>
              </div>
            </CardContent>
          </Card>

          {opportunity.tags && opportunity.tags.length > 0 && (
            <Card>
              <CardHeader>
                <CardTitle>Tags</CardTitle>
              </CardHeader>
              <CardContent>
                <div className="flex flex-wrap gap-2">
                  {opportunity.tags.map((tag) => (
                    <Badge
                      key={tag.id}
                      style={
                        tag.color
                          ? { backgroundColor: `${tag.color}20`, color: tag.color }
                          : undefined
                      }
                    >
                      {tag.name}
                    </Badge>
                  ))}
                </div>
              </CardContent>
            </Card>
          )}
        </div>
      </div>

      <ConfirmDialog
        open={deleteOpen}
        onClose={() => setDeleteOpen(false)}
        onConfirm={handleDelete}
        title="Delete Deal"
        message={`Are you sure you want to delete "${opportunity.name}"? This action cannot be undone.`}
        confirmText="Delete"
        variant="danger"
      />
    </div>
  );
}
