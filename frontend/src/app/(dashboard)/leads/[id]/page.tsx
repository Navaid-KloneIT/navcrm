'use client';

import React, { useEffect, useState, useCallback } from 'react';
import { useParams, useRouter } from 'next/navigation';
import Link from 'next/link';
import {
  Mail,
  Phone,
  Globe,
  Building2,
  MapPin,
  Pencil,
  Trash2,
  RefreshCw,
} from 'lucide-react';
import { leadsApi } from '@/lib/api/leads';
import type { Lead } from '@/types';
import { useToast } from '@/components/ui/toast';
import { PageHeader } from '@/components/shared/page-header';
import { Card, CardHeader, CardTitle, CardContent } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Spinner } from '@/components/ui/spinner';
import { ConfirmDialog } from '@/components/shared/confirm-dialog';
import { LeadScoreBadge } from '@/components/leads/lead-score-badge';
import { LeadConversionDialog } from '@/components/leads/lead-conversion-dialog';
import { LEAD_STATUSES } from '@/lib/utils/constants';
import { formatDate } from '@/lib/utils/format';
import type { AxiosError } from 'axios';
import type { ApiError } from '@/types';

export default function LeadDetailPage() {
  const params = useParams();
  const router = useRouter();
  const { toast } = useToast();
  const [lead, setLead] = useState<Lead | null>(null);
  const [loading, setLoading] = useState(true);
  const [deleteOpen, setDeleteOpen] = useState(false);
  const [convertOpen, setConvertOpen] = useState(false);

  const leadId = Number(params.id);

  const fetchLead = useCallback(async () => {
    try {
      const response = await leadsApi.get(leadId);
      setLead(response.data.data);
    } catch {
      toast('Failed to load lead', 'error');
      router.push('/leads');
    } finally {
      setLoading(false);
    }
  }, [leadId, router, toast]);

  useEffect(() => {
    fetchLead();
  }, [fetchLead]);

  const handleDelete = async () => {
    try {
      await leadsApi.delete(leadId);
      toast('Lead deleted successfully', 'success');
      router.push('/leads');
    } catch (err) {
      const error = err as AxiosError<ApiError>;
      toast(error.response?.data?.message || 'Failed to delete lead', 'error');
    }
  };

  if (loading) {
    return (
      <div className="flex items-center justify-center py-20">
        <Spinner size="lg" />
      </div>
    );
  }

  if (!lead) return null;

  const statusInfo = LEAD_STATUSES.find((s) => s.value === lead.status);

  return (
    <div>
      <PageHeader
        title={lead.full_name}
        description={lead.company_name || undefined}
        action={
          <div className="flex gap-2">
            {!lead.is_converted && (
              <Button onClick={() => setConvertOpen(true)}>
                <RefreshCw className="mr-2 h-4 w-4" />
                Convert
              </Button>
            )}
            <Link href={`/leads/${lead.id}/edit`}>
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

      <div className="grid gap-6 lg:grid-cols-3">
        <div className="space-y-6 lg:col-span-2">
          <Card>
            <CardHeader>
              <CardTitle>Lead Information</CardTitle>
            </CardHeader>
            <CardContent>
              <div className="mb-4 flex items-center gap-3">
                <Badge className={statusInfo?.color}>
                  {statusInfo?.label || lead.status}
                </Badge>
                <LeadScoreBadge score={lead.score} />
              </div>

              <div className="grid gap-4 sm:grid-cols-2">
                {lead.email && (
                  <div className="flex items-center gap-2 text-sm">
                    <Mail className="h-4 w-4 text-gray-400" />
                    <a
                      href={`mailto:${lead.email}`}
                      className="text-blue-600 hover:underline"
                    >
                      {lead.email}
                    </a>
                  </div>
                )}
                {lead.phone && (
                  <div className="flex items-center gap-2 text-sm">
                    <Phone className="h-4 w-4 text-gray-400" />
                    <span>{lead.phone}</span>
                  </div>
                )}
                {lead.company_name && (
                  <div className="flex items-center gap-2 text-sm">
                    <Building2 className="h-4 w-4 text-gray-400" />
                    <span>{lead.company_name}</span>
                  </div>
                )}
                {lead.website && (
                  <div className="flex items-center gap-2 text-sm">
                    <Globe className="h-4 w-4 text-gray-400" />
                    <a
                      href={lead.website}
                      target="_blank"
                      rel="noopener noreferrer"
                      className="text-blue-600 hover:underline"
                    >
                      {lead.website}
                    </a>
                  </div>
                )}
                {(lead.address.line_1 || lead.address.city) && (
                  <div className="flex items-start gap-2 text-sm sm:col-span-2">
                    <MapPin className="mt-0.5 h-4 w-4 text-gray-400" />
                    <span>
                      {[
                        lead.address.line_1,
                        lead.address.line_2,
                        lead.address.city,
                        lead.address.state,
                        lead.address.postal_code,
                        lead.address.country,
                      ]
                        .filter(Boolean)
                        .join(', ')}
                    </span>
                  </div>
                )}
              </div>

              {lead.description && (
                <div className="mt-4 border-t border-gray-200 pt-4">
                  <p className="text-sm text-gray-600">{lead.description}</p>
                </div>
              )}
            </CardContent>
          </Card>

          {lead.is_converted && (
            <Card>
              <CardHeader>
                <CardTitle>Conversion Details</CardTitle>
              </CardHeader>
              <CardContent>
                <div className="rounded-md bg-purple-50 p-4">
                  <p className="mb-2 text-sm font-medium text-purple-800">
                    This lead was converted on{' '}
                    {lead.converted_at ? formatDate(lead.converted_at) : 'N/A'}
                  </p>
                  <div className="flex flex-wrap gap-3">
                    {lead.converted_contact_id && (
                      <Link
                        href={`/contacts/${lead.converted_contact_id}`}
                        className="text-sm text-purple-700 underline hover:text-purple-900"
                      >
                        View Contact
                      </Link>
                    )}
                    {lead.converted_account_id && (
                      <Link
                        href={`/accounts/${lead.converted_account_id}`}
                        className="text-sm text-purple-700 underline hover:text-purple-900"
                      >
                        View Account
                      </Link>
                    )}
                  </div>
                </div>
              </CardContent>
            </Card>
          )}
        </div>

        <div className="space-y-6">
          <Card>
            <CardHeader>
              <CardTitle>Details</CardTitle>
            </CardHeader>
            <CardContent className="space-y-3 text-sm">
              <div className="flex justify-between">
                <span className="text-gray-500">Job Title</span>
                <span className="font-medium text-gray-900">
                  {lead.job_title || '-'}
                </span>
              </div>
              <div className="flex justify-between">
                <span className="text-gray-500">Source</span>
                <span className="font-medium capitalize text-gray-900">
                  {lead.source || '-'}
                </span>
              </div>
              <div className="flex justify-between">
                <span className="text-gray-500">Owner</span>
                <span className="font-medium text-gray-900">
                  {lead.owner?.name || '-'}
                </span>
              </div>
              <div className="flex justify-between">
                <span className="text-gray-500">Created</span>
                <span className="font-medium text-gray-900">
                  {formatDate(lead.created_at)}
                </span>
              </div>
              <div className="flex justify-between">
                <span className="text-gray-500">Updated</span>
                <span className="font-medium text-gray-900">
                  {formatDate(lead.updated_at)}
                </span>
              </div>
            </CardContent>
          </Card>

          <Card>
            <CardHeader>
              <CardTitle>Tags</CardTitle>
            </CardHeader>
            <CardContent>
              {lead.tags.length > 0 ? (
                <div className="flex flex-wrap gap-2">
                  {lead.tags.map((tag) => (
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
              ) : (
                <p className="text-sm text-gray-500">No tags assigned</p>
              )}
            </CardContent>
          </Card>
        </div>
      </div>

      <ConfirmDialog
        open={deleteOpen}
        onClose={() => setDeleteOpen(false)}
        onConfirm={handleDelete}
        title="Delete Lead"
        message={`Are you sure you want to delete ${lead.full_name}? This action cannot be undone.`}
        confirmText="Delete"
        variant="danger"
      />

      {lead && !lead.is_converted && (
        <LeadConversionDialog
          open={convertOpen}
          onClose={() => setConvertOpen(false)}
          lead={lead}
          onConverted={fetchLead}
        />
      )}
    </div>
  );
}
