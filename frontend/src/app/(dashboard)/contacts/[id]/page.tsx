'use client';

import React, { useEffect, useState } from 'react';
import { useParams, useRouter } from 'next/navigation';
import Link from 'next/link';
import {
  Mail,
  Phone,
  Smartphone,
  MapPin,
  Briefcase,
  Globe,
  Pencil,
  Trash2,
} from 'lucide-react';
import { contactsApi } from '@/lib/api/contacts';
import { activitiesApi } from '@/lib/api/activities';
import type { Contact, Activity } from '@/types';
import { useToast } from '@/components/ui/toast';
import { PageHeader } from '@/components/shared/page-header';
import { Card, CardHeader, CardTitle, CardContent } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Avatar } from '@/components/ui/avatar';
import { Button } from '@/components/ui/button';
import { Spinner } from '@/components/ui/spinner';
import { ConfirmDialog } from '@/components/shared/confirm-dialog';
import { ContactActivityTimeline } from '@/components/contacts/contact-activity-timeline';
import { formatDate } from '@/lib/utils/format';
import type { AxiosError } from 'axios';
import type { ApiError } from '@/types';

export default function ContactDetailPage() {
  const params = useParams();
  const router = useRouter();
  const { toast } = useToast();
  const [contact, setContact] = useState<Contact | null>(null);
  const [activities, setActivities] = useState<Activity[]>([]);
  const [loading, setLoading] = useState(true);
  const [deleteOpen, setDeleteOpen] = useState(false);

  const contactId = Number(params.id);

  useEffect(() => {
    const fetchData = async () => {
      try {
        const [contactRes, activitiesRes] = await Promise.all([
          contactsApi.get(contactId),
          activitiesApi.list({ activitable_type: 'contact', activitable_id: contactId }),
        ]);
        setContact(contactRes.data.data);
        setActivities(activitiesRes.data.data);
      } catch {
        toast('Failed to load contact', 'error');
        router.push('/contacts');
      } finally {
        setLoading(false);
      }
    };

    fetchData();
  }, [contactId, router, toast]);

  const handleDelete = async () => {
    try {
      await contactsApi.delete(contactId);
      toast('Contact deleted successfully', 'success');
      router.push('/contacts');
    } catch (err) {
      const error = err as AxiosError<ApiError>;
      toast(error.response?.data?.message || 'Failed to delete contact', 'error');
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

  return (
    <div>
      <PageHeader
        title={contact.full_name}
        description={contact.job_title || undefined}
        action={
          <div className="flex gap-2">
            <Link href={`/contacts/${contact.id}/edit`}>
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
              <CardTitle>Contact Information</CardTitle>
            </CardHeader>
            <CardContent>
              <div className="flex items-start gap-4">
                <Avatar name={contact.full_name} size="lg" />
                <div className="grid flex-1 gap-4 sm:grid-cols-2">
                  {contact.email && (
                    <div className="flex items-center gap-2 text-sm">
                      <Mail className="h-4 w-4 text-gray-400" />
                      <a
                        href={`mailto:${contact.email}`}
                        className="text-blue-600 hover:underline"
                      >
                        {contact.email}
                      </a>
                    </div>
                  )}
                  {contact.phone && (
                    <div className="flex items-center gap-2 text-sm">
                      <Phone className="h-4 w-4 text-gray-400" />
                      <span>{contact.phone}</span>
                    </div>
                  )}
                  {contact.mobile && (
                    <div className="flex items-center gap-2 text-sm">
                      <Smartphone className="h-4 w-4 text-gray-400" />
                      <span>{contact.mobile}</span>
                    </div>
                  )}
                  {contact.department && (
                    <div className="flex items-center gap-2 text-sm">
                      <Briefcase className="h-4 w-4 text-gray-400" />
                      <span>{contact.department}</span>
                    </div>
                  )}
                  {contact.linkedin_url && (
                    <div className="flex items-center gap-2 text-sm">
                      <Globe className="h-4 w-4 text-gray-400" />
                      <a
                        href={contact.linkedin_url}
                        target="_blank"
                        rel="noopener noreferrer"
                        className="text-blue-600 hover:underline"
                      >
                        LinkedIn
                      </a>
                    </div>
                  )}
                  {(contact.address.line_1 || contact.address.city) && (
                    <div className="flex items-start gap-2 text-sm sm:col-span-2">
                      <MapPin className="mt-0.5 h-4 w-4 text-gray-400" />
                      <span>
                        {[
                          contact.address.line_1,
                          contact.address.line_2,
                          contact.address.city,
                          contact.address.state,
                          contact.address.postal_code,
                          contact.address.country,
                        ]
                          .filter(Boolean)
                          .join(', ')}
                      </span>
                    </div>
                  )}
                </div>
              </div>
              {contact.description && (
                <div className="mt-4 border-t border-gray-200 pt-4">
                  <p className="text-sm text-gray-600">{contact.description}</p>
                </div>
              )}
            </CardContent>
          </Card>

          <Card>
            <CardHeader>
              <CardTitle>Activity Timeline</CardTitle>
            </CardHeader>
            <CardContent>
              <ContactActivityTimeline activities={activities} />
            </CardContent>
          </Card>
        </div>

        <div className="space-y-6">
          <Card>
            <CardHeader>
              <CardTitle>Details</CardTitle>
            </CardHeader>
            <CardContent className="space-y-3 text-sm">
              <div className="flex justify-between">
                <span className="text-gray-500">Source</span>
                <span className="font-medium text-gray-900">
                  {contact.source || '-'}
                </span>
              </div>
              <div className="flex justify-between">
                <span className="text-gray-500">Owner</span>
                <span className="font-medium text-gray-900">
                  {contact.owner?.name || '-'}
                </span>
              </div>
              <div className="flex justify-between">
                <span className="text-gray-500">Created</span>
                <span className="font-medium text-gray-900">
                  {formatDate(contact.created_at)}
                </span>
              </div>
              <div className="flex justify-between">
                <span className="text-gray-500">Updated</span>
                <span className="font-medium text-gray-900">
                  {formatDate(contact.updated_at)}
                </span>
              </div>
            </CardContent>
          </Card>

          <Card>
            <CardHeader>
              <CardTitle>Tags</CardTitle>
            </CardHeader>
            <CardContent>
              {contact.tags.length > 0 ? (
                <div className="flex flex-wrap gap-2">
                  {contact.tags.map((tag) => (
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
        title="Delete Contact"
        message={`Are you sure you want to delete ${contact.full_name}? This action cannot be undone.`}
        confirmText="Delete"
        variant="danger"
      />
    </div>
  );
}
