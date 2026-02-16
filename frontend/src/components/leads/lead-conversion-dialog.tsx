'use client';

import React, { useEffect, useState } from 'react';
import { Dialog } from '@/components/ui/dialog';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Select } from '@/components/ui/select';
import { leadsApi } from '@/lib/api/leads';
import { accountsApi } from '@/lib/api/accounts';
import { useToast } from '@/components/ui/toast';
import type { Account, Lead } from '@/types';
import type { AxiosError } from 'axios';
import type { ApiError } from '@/types';

interface LeadConversionDialogProps {
  open: boolean;
  onClose: () => void;
  lead: Lead;
  onConverted: () => void;
}

function LeadConversionDialog({
  open,
  onClose,
  lead,
  onConverted,
}: LeadConversionDialogProps) {
  const { toast } = useToast();
  const [mode, setMode] = useState<'new' | 'existing'>('new');
  const [accountName, setAccountName] = useState(lead.company_name || '');
  const [existingAccountId, setExistingAccountId] = useState<string>('');
  const [accounts, setAccounts] = useState<{ value: string | number; label: string }[]>([]);
  const [loading, setLoading] = useState(false);

  useEffect(() => {
    if (open) {
      const fetchAccounts = async () => {
        try {
          const response = await accountsApi.list({ per_page: 100 });
          setAccounts(
            response.data.data.map((a: Account) => ({
              value: a.id,
              label: a.name,
            }))
          );
        } catch {
          // Silently handle
        }
      };
      fetchAccounts();
    }
  }, [open]);

  const handleConvert = async () => {
    setLoading(true);
    try {
      const options =
        mode === 'new'
          ? { create_account: true, account_name: accountName }
          : { existing_account_id: Number(existingAccountId) };

      await leadsApi.convert(lead.id, options);
      toast('Lead converted successfully', 'success');
      onConverted();
      onClose();
    } catch (err) {
      const error = err as AxiosError<ApiError>;
      toast(error.response?.data?.message || 'Failed to convert lead', 'error');
    } finally {
      setLoading(false);
    }
  };

  return (
    <Dialog open={open} onClose={onClose} title="Convert Lead" size="md">
      <div className="space-y-4">
        <p className="text-sm text-gray-600">
          Converting <strong>{lead.full_name}</strong> will create a new contact.
          Choose how to handle the account:
        </p>

        <div className="space-y-3">
          <label className="flex items-center gap-2">
            <input
              type="radio"
              name="conversionMode"
              value="new"
              checked={mode === 'new'}
              onChange={() => setMode('new')}
              className="h-4 w-4 border-gray-300 text-blue-600 focus:ring-blue-500"
            />
            <span className="text-sm font-medium text-gray-700">
              Create new account
            </span>
          </label>

          {mode === 'new' && (
            <div className="ml-6">
              <Input
                label="Account Name"
                value={accountName}
                onChange={(e) => setAccountName(e.target.value)}
                placeholder="Enter account name"
              />
            </div>
          )}

          <label className="flex items-center gap-2">
            <input
              type="radio"
              name="conversionMode"
              value="existing"
              checked={mode === 'existing'}
              onChange={() => setMode('existing')}
              className="h-4 w-4 border-gray-300 text-blue-600 focus:ring-blue-500"
            />
            <span className="text-sm font-medium text-gray-700">
              Use existing account
            </span>
          </label>

          {mode === 'existing' && (
            <div className="ml-6">
              <Select
                label="Select Account"
                options={accounts}
                placeholder="Choose an account"
                value={existingAccountId}
                onChange={(e) => setExistingAccountId(e.target.value)}
              />
            </div>
          )}
        </div>

        <div className="flex justify-end gap-3 border-t border-gray-200 pt-4">
          <Button variant="outline" onClick={onClose}>
            Cancel
          </Button>
          <Button
            onClick={handleConvert}
            disabled={
              loading ||
              (mode === 'new' && !accountName) ||
              (mode === 'existing' && !existingAccountId)
            }
          >
            {loading ? 'Converting...' : 'Convert Lead'}
          </Button>
        </div>
      </div>
    </Dialog>
  );
}

export { LeadConversionDialog };
