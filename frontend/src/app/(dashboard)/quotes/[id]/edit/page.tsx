'use client';

import React, { useEffect, useState } from 'react';
import { useParams, useRouter } from 'next/navigation';
import { useForm, Controller } from 'react-hook-form';
import { zodResolver } from '@hookform/resolvers/zod';
import { quoteFormSchema, type QuoteFormData } from '@/lib/validations/quote';
import { quotesApi } from '@/lib/api/quotes';
import { productsApi } from '@/lib/api/products';
import { opportunitiesApi } from '@/lib/api/opportunities';
import { accountsApi } from '@/lib/api/accounts';
import { contactsApi } from '@/lib/api/contacts';
import type { Quote, Product, Opportunity, Account, Contact } from '@/types';
import { useToast } from '@/components/ui/toast';
import { PageHeader } from '@/components/shared/page-header';
import { Card, CardHeader, CardTitle, CardContent } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Select } from '@/components/ui/select';
import { Textarea } from '@/components/ui/textarea';
import { Button } from '@/components/ui/button';
import { Spinner } from '@/components/ui/spinner';
import { QuoteLineItems, type LineItem } from '@/components/quotes/quote-line-items';
import type { AxiosError } from 'axios';
import type { ApiError } from '@/types';

const discountTypeOptions = [
  { value: 'percentage', label: 'Percentage' },
  { value: 'fixed', label: 'Fixed Amount' },
];

export default function EditQuotePage() {
  const params = useParams();
  const router = useRouter();
  const { toast } = useToast();
  const [quote, setQuote] = useState<Quote | null>(null);
  const [loading, setLoading] = useState(true);
  const [saving, setSaving] = useState(false);

  const [products, setProducts] = useState<Product[]>([]);
  const [opportunities, setOpportunities] = useState<Opportunity[]>([]);
  const [accounts, setAccounts] = useState<Account[]>([]);
  const [contacts, setContacts] = useState<Contact[]>([]);

  const quoteId = Number(params.id);

  const {
    register,
    handleSubmit,
    control,
    reset,
    formState: { errors },
  } = useForm<QuoteFormData>({
    resolver: zodResolver(quoteFormSchema) as any,
    defaultValues: {
      opportunity_id: null,
      account_id: null,
      contact_id: null,
      valid_until: '',
      discount_type: 'percentage',
      discount_value: 0,
      tax_rate: 0,
      notes: '',
      terms: '',
      line_items: [],
    },
  });

  useEffect(() => {
    const fetchData = async () => {
      try {
        const [quoteRes, productsRes, opportunitiesRes, accountsRes, contactsRes] =
          await Promise.all([
            quotesApi.get(quoteId),
            productsApi.list({ per_page: 200, is_active: true }),
            opportunitiesApi.list({ per_page: 200 }),
            accountsApi.list({ per_page: 200 }),
            contactsApi.list({ per_page: 200 }),
          ]);

        const quoteData = quoteRes.data.data;
        setQuote(quoteData);
        setProducts(productsRes.data.data);
        setOpportunities(opportunitiesRes.data.data);
        setAccounts(accountsRes.data.data);
        setContacts(contactsRes.data.data);

        reset({
          opportunity_id: quoteData.opportunity?.id || null,
          account_id: quoteData.account?.id || null,
          contact_id: quoteData.contact?.id || null,
          valid_until: quoteData.valid_until || '',
          discount_type: quoteData.discount_type,
          discount_value: quoteData.discount_value,
          tax_rate: quoteData.tax_rate,
          notes: quoteData.notes || '',
          terms: quoteData.terms || '',
          line_items: (quoteData.line_items || []).map((item) => ({
            product_id: item.product_id,
            description: item.description,
            quantity: item.quantity,
            unit_price: item.unit_price,
            discount_percent: item.discount_percent,
          })),
        });
      } catch {
        toast('Failed to load quote', 'error');
        router.push('/quotes');
      } finally {
        setLoading(false);
      }
    };

    fetchData();
  }, [quoteId, router, toast, reset]);

  const onSubmit = async (data: QuoteFormData) => {
    setSaving(true);
    try {
      await quotesApi.update(quoteId, {
        opportunity_id: data.opportunity_id || null,
        account_id: data.account_id || null,
        contact_id: data.contact_id || null,
        valid_until: data.valid_until || null,
        discount_type: data.discount_type,
        discount_value: data.discount_value,
        tax_rate: data.tax_rate,
        notes: data.notes || null,
        terms: data.terms || null,
        line_items: data.line_items.map((item) => ({
          product_id: item.product_id || null,
          description: item.description,
          quantity: item.quantity,
          unit_price: item.unit_price,
          discount_percent: item.discount_percent || 0,
        })),
      });
      toast('Quote updated successfully', 'success');
      router.push(`/quotes/${quoteId}`);
    } catch (err) {
      const error = err as AxiosError<ApiError>;
      toast(error.response?.data?.message || 'Failed to update quote', 'error');
    } finally {
      setSaving(false);
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

  const opportunityOptions = [
    { value: '', label: 'None' },
    ...opportunities.map((o) => ({ value: o.id, label: o.name })),
  ];

  const accountOptions = [
    { value: '', label: 'None' },
    ...accounts.map((a) => ({ value: a.id, label: a.name })),
  ];

  const contactOptions = [
    { value: '', label: 'None' },
    ...contacts.map((c) => ({
      value: c.id,
      label: `${c.first_name} ${c.last_name}`,
    })),
  ];

  return (
    <div>
      <PageHeader
        title="Edit Quote"
        description={`Editing ${quote.quote_number}`}
      />

      <form onSubmit={handleSubmit(onSubmit)} className="space-y-6">
        <Card>
          <CardHeader>
            <CardTitle>Quote Details</CardTitle>
          </CardHeader>
          <CardContent>
            <div className="grid gap-4 sm:grid-cols-2">
              <Select
                label="Opportunity"
                options={opportunityOptions}
                placeholder="Select opportunity"
                error={errors.opportunity_id?.message}
                {...register('opportunity_id')}
              />
              <Select
                label="Account"
                options={accountOptions}
                placeholder="Select account"
                error={errors.account_id?.message}
                {...register('account_id')}
              />
              <Select
                label="Contact"
                options={contactOptions}
                placeholder="Select contact"
                error={errors.contact_id?.message}
                {...register('contact_id')}
              />
              <Input
                label="Valid Until"
                type="date"
                error={errors.valid_until?.message}
                {...register('valid_until')}
              />
            </div>
          </CardContent>
        </Card>

        <Card>
          <CardHeader>
            <CardTitle>Line Items</CardTitle>
          </CardHeader>
          <CardContent>
            <Controller
              name="line_items"
              control={control}
              render={({ field }) => (
                <QuoteLineItems
                  lineItems={field.value as LineItem[]}
                  products={products}
                  onChange={field.onChange}
                />
              )}
            />
            {errors.line_items?.message && (
              <p className="mt-2 text-sm text-red-600">{errors.line_items.message}</p>
            )}
          </CardContent>
        </Card>

        <Card>
          <CardHeader>
            <CardTitle>Discount & Tax</CardTitle>
          </CardHeader>
          <CardContent>
            <div className="grid gap-4 sm:grid-cols-3">
              <Select
                label="Discount Type"
                options={discountTypeOptions}
                error={errors.discount_type?.message}
                {...register('discount_type')}
              />
              <Input
                label="Discount Value"
                type="number"
                step="0.01"
                min="0"
                placeholder="0"
                error={errors.discount_value?.message}
                {...register('discount_value', { valueAsNumber: true })}
              />
              <Input
                label="Tax Rate (%)"
                type="number"
                step="0.01"
                min="0"
                max="100"
                placeholder="0"
                error={errors.tax_rate?.message}
                {...register('tax_rate', { valueAsNumber: true })}
              />
            </div>
          </CardContent>
        </Card>

        <Card>
          <CardHeader>
            <CardTitle>Additional Information</CardTitle>
          </CardHeader>
          <CardContent>
            <div className="grid gap-4">
              <Textarea
                label="Notes"
                placeholder="Internal notes about this quote..."
                error={errors.notes?.message}
                {...register('notes')}
              />
              <Textarea
                label="Terms & Conditions"
                placeholder="Terms and conditions for this quote..."
                error={errors.terms?.message}
                {...register('terms')}
              />
            </div>
          </CardContent>
        </Card>

        <div className="flex justify-end gap-3">
          <Button
            type="button"
            variant="outline"
            onClick={() => router.push(`/quotes/${quoteId}`)}
          >
            Cancel
          </Button>
          <Button type="submit" disabled={saving}>
            {saving ? 'Saving...' : 'Update Quote'}
          </Button>
        </div>
      </form>
    </div>
  );
}
