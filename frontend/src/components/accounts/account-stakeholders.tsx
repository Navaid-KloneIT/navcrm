'use client';

import React from 'react';
import Link from 'next/link';
import { UserPlus } from 'lucide-react';
import type { Contact } from '@/types';
import { Card, CardHeader, CardTitle, CardContent } from '@/components/ui/card';
import { Avatar } from '@/components/ui/avatar';
import { Button } from '@/components/ui/button';

interface AccountStakeholdersProps {
  contacts: Contact[];
  onAddContact?: () => void;
}

function AccountStakeholders({ contacts, onAddContact }: AccountStakeholdersProps) {
  return (
    <Card>
      <CardHeader>
        <div className="flex items-center justify-between">
          <CardTitle>Contacts ({contacts.length})</CardTitle>
          {onAddContact && (
            <Button variant="outline" size="sm" onClick={onAddContact}>
              <UserPlus className="mr-1 h-4 w-4" />
              Add Contact
            </Button>
          )}
        </div>
      </CardHeader>
      <CardContent>
        {contacts.length > 0 ? (
          <div className="space-y-3">
            {contacts.map((contact) => (
              <Link
                key={contact.id}
                href={`/contacts/${contact.id}`}
                className="flex items-center gap-3 rounded-md p-2 transition-colors hover:bg-gray-50"
              >
                <Avatar name={contact.full_name} size="sm" />
                <div className="flex-1">
                  <p className="text-sm font-medium text-gray-900">
                    {contact.full_name}
                  </p>
                  <p className="text-xs text-gray-500">
                    {contact.job_title || contact.email || ''}
                  </p>
                </div>
              </Link>
            ))}
          </div>
        ) : (
          <p className="py-4 text-center text-sm text-gray-500">
            No contacts associated with this account.
          </p>
        )}
      </CardContent>
    </Card>
  );
}

export { AccountStakeholders };
