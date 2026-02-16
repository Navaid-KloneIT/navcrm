'use client';

import React, { useEffect, useState } from 'react';
import { useParams, useRouter } from 'next/navigation';
import { contactsApi } from '@/lib/api/contacts';
import { useToast } from '@/components/ui/toast';
import { PageHeader } from '@/components/shared/page-header';
import { Card, CardContent } from '@/components/ui/card';
import { Spinner } from '@/components/ui/spinner';
import { ContactForm } from '@/components/contacts/contact-form';
import type { Contact } from '@/types';
import type { ContactFormData } from '@/lib/validations/contact';
import type { AxiosError } from 'axios';
import type { ApiError } from '@/types';

export default function EditContactPage() {
  const params = useParams();
  const router = useRouter();
  const { toast } = useToast();
  const [contact, setContact] = useState<Contact | null>(null);
  const [loading, setLoading] = useState(true);
  const [saving, setSaving] = useState(false);

  const contactId = Number(params.id);

  useEffect(() => {
    const fetchContact = async () => {
      try {
        const response = await contactsApi.get(contactId);
        setContact(response.data.data);
      } catch {
        toast('Failed to load contact', 'error');
        router.push('/contacts');
      } finally {
        setLoading(false);
      }
    };

    fetchContact();
  }, [contactId, router, toast]);

  const handleSubmit = async (data: ContactFormData) => {
    setSaving(true);
    try {
      await contactsApi.update(contactId, data);
      toast('Contact updated successfully', 'success');
      router.push(`/contacts/${contactId}`);
    } catch (err) {
      const error = err as AxiosError<ApiError>;
      toast(error.response?.data?.message || 'Failed to update contact', 'error');
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

  if (!contact) return null;

  const defaultValues: Partial<ContactFormData> = {
    first_name: contact.first_name,
    last_name: contact.last_name,
    email: contact.email || '',
    phone: contact.phone || '',
    mobile: contact.mobile || '',
    job_title: contact.job_title || '',
    department: contact.department || '',
    linkedin_url: contact.linkedin_url || '',
    twitter_handle: contact.twitter_handle || '',
    facebook_url: contact.facebook_url || '',
    address_line_1: contact.address.line_1 || '',
    address_line_2: contact.address.line_2 || '',
    city: contact.address.city || '',
    state: contact.address.state || '',
    postal_code: contact.address.postal_code || '',
    country: contact.address.country || '',
    source: contact.source || '',
    description: contact.description || '',
  };

  return (
    <div>
      <PageHeader title="Edit Contact" description={`Editing ${contact.full_name}`} />
      <Card>
        <CardContent className="py-6">
          <ContactForm
            defaultValues={defaultValues}
            onSubmit={handleSubmit}
            onCancel={() => router.push(`/contacts/${contactId}`)}
            loading={saving}
          />
        </CardContent>
      </Card>
    </div>
  );
}
