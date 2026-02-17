'use client';

import React, { useEffect, useState } from 'react';
import { useParams, useRouter } from 'next/navigation';
import Link from 'next/link';
import {
  FileText,
  Building2,
  User,
  Calendar,
  Pencil,
  Trash2,
  Download,
} from 'lucide-react';
import { quotesApi } from '@/lib/api/quotes';
import { QUOTE_STATUSES } from '@/lib/utils/constants';
import type { Quote, QuoteStatus } from '@/types';
import { useToast } from '@/components/ui/toast';
import { PageHeader } from '@/components/shared/page-header';
import { Card, CardHeader, CardTitle, CardContent } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Select } from '@/components/ui/select';
import { Spinner } from '@/components/ui/spinner';
import { ConfirmDialog } from '@/components/shared/confirm-dialog';
import { QuoteStatusBadge } from '@/components/quotes/quote-status-badge';
import { QuoteLineItems } from '@/components/quotes/quote-line-items';
import { QuoteSummary } from '@/components/quotes/quote-summary';
import { formatDate } from '@/lib/utils/format';
import type { AxiosError } from 'axios';
import type { ApiError } from '@/types';

const statusOptions = QUOTE_STATUSES.map((s) => ({ value: s.value, label: s.label }));

export default function QuoteDetailPage() {
  const params = useParams();
  const router = useRouter();
  const { toast } = useToast();
  const [quote, setQuote] = useState<Quote | null>(null);
  const [loading, setLoading] = useState(true);
  const [deleteOpen, setDeleteOpen] = useState(false);
  const [statusLoading, setStatusLoading] = useState(false);

  const quoteId = Number(params.id);

  useEffect(() => {
    const fetchQuote = async () => {
      try {
        const response = await quotesApi.get(quoteId);
        setQuote(response.data.data);
      } catch {
        toast('Failed to load quote', 'error');
        router.push('/quotes');
      } finally {
        setLoading(false);
      }
    };

    fetchQuote();
  }, [quoteId, router, toast]);

  const handleDelete = async () => {
    try {
      await quotesApi.delete(quoteId);
      toast('Quote deleted successfully', 'success');
      router.push('/quotes');
    } catch (err) {
      const error = err as AxiosError<ApiError>;
      toast(error.response?.data?.message || 'Failed to delete quote', 'error');
    }
  };

  const handleStatusChange = async (newStatus: string) => {
    if (!quote || newStatus === quote.status) return;
    setStatusLoading(true);
    try {
      const response = await quotesApi.updateStatus(quoteId, newStatus as QuoteStatus);
      setQuote(response.data.data);
      toast('Quote status updated', 'success');
    } catch (err) {
      const error = err as AxiosError<ApiError>;
      toast(error.response?.data?.message || 'Failed to update status', 'error');
    } finally {
      setStatusLoading(false);
    }
  };

  const handleDownloadPdf = async () => {
    try {
      const response = await quotesApi.downloadPdf(quoteId);
      const blob = new Blob([response.data], { type: 'application/pdf' });
      const url = window.URL.createObjectURL(blob);
      const link = document.createElement('a');
      link.href = url;
      link.download = `quote-${quote?.quote_number || quoteId}.pdf`;
      document.body.appendChild(link);
      link.click();
      document.body.removeChild(link);
      window.URL.revokeObjectURL(url);
    } catch {
      toast('Failed to download PDF', 'error');
    }
  };

  if (loading) {
    return (
      <div className="flex items-center justify-center py-20">
        <Spinner size="lg" />
      </div>
    );
  }

  if (!quote) return null;

  const lineItems = (quote.line_items || []).map((item) => ({
    product_id: item.product_id,
    description: item.description,
    quantity: item.quantity,
    unit_price: item.unit_price,
    discount_percent: item.discount_percent,
  }));

  return (
    <div>
      <PageHeader
        title={`Quote ${quote.quote_number}`}
        description={quote.opportunity?.name || undefined}
        action={
          <div className="flex gap-2">
            <Button variant="outline" onClick={handleDownloadPdf}>
              <Download className="mr-2 h-4 w-4" />
              Download PDF
            </Button>
            <Link href={`/quotes/${quote.id}/edit`}>
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
              <CardTitle>Quote Information</CardTitle>
            </CardHeader>
            <CardContent>
              <div className="grid gap-4 sm:grid-cols-2">
                <div className="flex items-center gap-2 text-sm">
                  <FileText className="h-4 w-4 text-gray-400" />
                  <span className="text-gray-500">Quote #:</span>
                  <span className="font-medium text-gray-900">{quote.quote_number}</span>
                </div>
                <div className="flex items-center gap-2 text-sm">
                  <span className="text-gray-500">Status:</span>
                  <QuoteStatusBadge status={quote.status} />
                </div>
                {quote.opportunity && (
                  <div className="flex items-center gap-2 text-sm">
                    <span className="text-gray-500">Opportunity:</span>
                    <span className="font-medium text-gray-900">{quote.opportunity.name}</span>
                  </div>
                )}
                {quote.account && (
                  <div className="flex items-center gap-2 text-sm">
                    <Building2 className="h-4 w-4 text-gray-400" />
                    <span className="text-gray-500">Account:</span>
                    <span className="font-medium text-gray-900">{quote.account.name}</span>
                  </div>
                )}
                {quote.contact && (
                  <div className="flex items-center gap-2 text-sm">
                    <User className="h-4 w-4 text-gray-400" />
                    <span className="text-gray-500">Contact:</span>
                    <span className="font-medium text-gray-900">{quote.contact.full_name}</span>
                  </div>
                )}
                {quote.valid_until && (
                  <div className="flex items-center gap-2 text-sm">
                    <Calendar className="h-4 w-4 text-gray-400" />
                    <span className="text-gray-500">Valid Until:</span>
                    <span className="font-medium text-gray-900">
                      {formatDate(quote.valid_until)}
                    </span>
                  </div>
                )}
                {quote.prepared_by && (
                  <div className="flex items-center gap-2 text-sm">
                    <span className="text-gray-500">Prepared By:</span>
                    <span className="font-medium text-gray-900">{quote.prepared_by.name}</span>
                  </div>
                )}
              </div>
            </CardContent>
          </Card>

          <Card>
            <CardHeader>
              <CardTitle>Line Items</CardTitle>
            </CardHeader>
            <CardContent>
              <QuoteLineItems
                lineItems={lineItems}
                products={[]}
                onChange={() => {}}
                readOnly
              />
            </CardContent>
          </Card>

          {(quote.notes || quote.terms) && (
            <Card>
              <CardHeader>
                <CardTitle>Additional Information</CardTitle>
              </CardHeader>
              <CardContent className="space-y-4">
                {quote.notes && (
                  <div>
                    <h4 className="text-sm font-medium text-gray-700">Notes</h4>
                    <p className="mt-1 text-sm text-gray-600 whitespace-pre-wrap">{quote.notes}</p>
                  </div>
                )}
                {quote.terms && (
                  <div>
                    <h4 className="text-sm font-medium text-gray-700">Terms & Conditions</h4>
                    <p className="mt-1 text-sm text-gray-600 whitespace-pre-wrap">{quote.terms}</p>
                  </div>
                )}
              </CardContent>
            </Card>
          )}
        </div>

        <div className="space-y-6">
          <Card>
            <CardHeader>
              <CardTitle>Summary</CardTitle>
            </CardHeader>
            <CardContent>
              <QuoteSummary
                subtotal={quote.subtotal}
                discountType={quote.discount_type}
                discountValue={quote.discount_value}
                discountAmount={quote.discount_amount}
                taxRate={quote.tax_rate}
                taxAmount={quote.tax_amount}
                total={quote.total}
              />
            </CardContent>
          </Card>

          <Card>
            <CardHeader>
              <CardTitle>Change Status</CardTitle>
            </CardHeader>
            <CardContent>
              <Select
                options={statusOptions}
                value={quote.status}
                onChange={(e) => handleStatusChange(e.target.value)}
                disabled={statusLoading}
              />
            </CardContent>
          </Card>

          <Card>
            <CardHeader>
              <CardTitle>Details</CardTitle>
            </CardHeader>
            <CardContent className="space-y-3 text-sm">
              <div className="flex justify-between">
                <span className="text-gray-500">Created</span>
                <span className="font-medium text-gray-900">
                  {formatDate(quote.created_at)}
                </span>
              </div>
              <div className="flex justify-between">
                <span className="text-gray-500">Updated</span>
                <span className="font-medium text-gray-900">
                  {formatDate(quote.updated_at)}
                </span>
              </div>
            </CardContent>
          </Card>
        </div>
      </div>

      <ConfirmDialog
        open={deleteOpen}
        onClose={() => setDeleteOpen(false)}
        onConfirm={handleDelete}
        title="Delete Quote"
        message={`Are you sure you want to delete quote ${quote.quote_number}? This action cannot be undone.`}
        confirmText="Delete"
        variant="danger"
      />
    </div>
  );
}
