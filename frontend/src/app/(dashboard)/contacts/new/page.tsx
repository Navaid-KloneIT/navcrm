'use client';

import React, { useState } from 'react';
import { useRouter } from 'next/navigation';
import { contactsApi } from '@/lib/api/contacts';
import { useToast } from '@/components/ui/toast';
import { PageHeader } from '@/components/shared/page-header';
import { Card, CardContent } from '@/components/ui/card';
import { ContactForm } from '@/components/contacts/contact-form';
import type { ContactFormData } from '@/lib/validations/contact';
import type { AxiosError } from 'axios';
import type { ApiError } from '@/types';

export default function NewContactPage() {
  const router = useRouter();
  const { toast } = useToast();
  const [loading, setLoading] = useState(false);

  const handleSubmit = async (data: ContactFormData) => {
    setLoading(true);
    try {
      await contactsApi.create(data);
      toast('Contact created successfully', 'success');
      router.push('/contacts');
    } catch (err) {
      const error = err as AxiosError<ApiError>;
      toast(error.response?.data?.message || 'Failed to create contact', 'error');
    } finally {
      setLoading(false);
    }
  };

  return (
    <div>
      <PageHeader title="New Contact" description="Add a new contact to your CRM" />
      <Card>
        <CardContent className="py-6">
          <ContactForm
            onSubmit={handleSubmit}
            onCancel={() => router.push('/contacts')}
            loading={loading}
          />
        </CardContent>
      </Card>
    </div>
  );
}
