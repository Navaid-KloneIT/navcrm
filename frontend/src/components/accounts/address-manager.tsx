'use client';

import React, { useState } from 'react';
import { Plus, Pencil, Trash2, MapPin } from 'lucide-react';
import type { Address } from '@/types';
import { accountsApi } from '@/lib/api/accounts';
import { useToast } from '@/components/ui/toast';
import { Card, CardHeader, CardTitle, CardContent } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Dialog } from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Select } from '@/components/ui/select';
import { ConfirmDialog } from '@/components/shared/confirm-dialog';
import { ADDRESS_TYPES } from '@/lib/utils/constants';
import type { AxiosError } from 'axios';
import type { ApiError } from '@/types';

interface AddressManagerProps {
  accountId: number;
  addresses: Address[];
  onRefresh: () => void;
}

const typeVariants: Record<string, 'primary' | 'warning' | 'default'> = {
  billing: 'primary',
  shipping: 'warning',
  other: 'default',
};

function AddressManager({ accountId, addresses, onRefresh }: AddressManagerProps) {
  const { toast } = useToast();
  const [dialogOpen, setDialogOpen] = useState(false);
  const [deleteId, setDeleteId] = useState<number | null>(null);
  const [saving, setSaving] = useState(false);
  const [editingAddress, setEditingAddress] = useState<Address | null>(null);

  const [form, setForm] = useState({
    type: 'billing' as 'billing' | 'shipping' | 'other',
    address_line_1: '',
    address_line_2: '',
    city: '',
    state: '',
    postal_code: '',
    country: '',
    is_primary: false,
  });

  const resetForm = () => {
    setForm({
      type: 'billing',
      address_line_1: '',
      address_line_2: '',
      city: '',
      state: '',
      postal_code: '',
      country: '',
      is_primary: false,
    });
    setEditingAddress(null);
  };

  const openCreate = () => {
    resetForm();
    setDialogOpen(true);
  };

  const openEdit = (address: Address) => {
    setEditingAddress(address);
    setForm({
      type: address.type,
      address_line_1: address.address_line_1,
      address_line_2: address.address_line_2 || '',
      city: address.city,
      state: address.state || '',
      postal_code: address.postal_code || '',
      country: address.country,
      is_primary: address.is_primary,
    });
    setDialogOpen(true);
  };

  const handleSave = async () => {
    setSaving(true);
    try {
      if (editingAddress) {
        await accountsApi.updateAddress(editingAddress.id, form);
        toast('Address updated', 'success');
      } else {
        await accountsApi.createAddress(accountId, form);
        toast('Address added', 'success');
      }
      setDialogOpen(false);
      resetForm();
      onRefresh();
    } catch (err) {
      const error = err as AxiosError<ApiError>;
      toast(error.response?.data?.message || 'Failed to save address', 'error');
    } finally {
      setSaving(false);
    }
  };

  const handleDelete = async () => {
    if (!deleteId) return;
    try {
      await accountsApi.deleteAddress(deleteId);
      toast('Address deleted', 'success');
      setDeleteId(null);
      onRefresh();
    } catch (err) {
      const error = err as AxiosError<ApiError>;
      toast(error.response?.data?.message || 'Failed to delete address', 'error');
    }
  };

  return (
    <Card>
      <CardHeader>
        <div className="flex items-center justify-between">
          <CardTitle>Addresses ({addresses.length})</CardTitle>
          <Button variant="outline" size="sm" onClick={openCreate}>
            <Plus className="mr-1 h-4 w-4" />
            Add Address
          </Button>
        </div>
      </CardHeader>
      <CardContent>
        {addresses.length > 0 ? (
          <div className="space-y-3">
            {addresses.map((address) => (
              <div
                key={address.id}
                className="flex items-start justify-between rounded-md border border-gray-200 p-3"
              >
                <div className="flex gap-3">
                  <MapPin className="mt-0.5 h-4 w-4 text-gray-400" />
                  <div>
                    <div className="mb-1 flex items-center gap-2">
                      <Badge variant={typeVariants[address.type] || 'default'}>
                        {address.type}
                      </Badge>
                      {address.is_primary && (
                        <Badge variant="success">Primary</Badge>
                      )}
                    </div>
                    <p className="text-sm text-gray-700">
                      {[
                        address.address_line_1,
                        address.address_line_2,
                        address.city,
                        address.state,
                        address.postal_code,
                        address.country,
                      ]
                        .filter(Boolean)
                        .join(', ')}
                    </p>
                  </div>
                </div>
                <div className="flex gap-1">
                  <button
                    onClick={() => openEdit(address)}
                    className="rounded p-1 text-gray-400 hover:bg-gray-100 hover:text-gray-600"
                  >
                    <Pencil className="h-4 w-4" />
                  </button>
                  <button
                    onClick={() => setDeleteId(address.id)}
                    className="rounded p-1 text-gray-400 hover:bg-red-50 hover:text-red-600"
                  >
                    <Trash2 className="h-4 w-4" />
                  </button>
                </div>
              </div>
            ))}
          </div>
        ) : (
          <p className="py-4 text-center text-sm text-gray-500">
            No addresses added yet.
          </p>
        )}
      </CardContent>

      <Dialog
        open={dialogOpen}
        onClose={() => setDialogOpen(false)}
        title={editingAddress ? 'Edit Address' : 'Add Address'}
        size="md"
      >
        <div className="space-y-4">
          <Select
            label="Type"
            options={ADDRESS_TYPES.map((t) => ({ value: t.value, label: t.label }))}
            value={form.type}
            onChange={(e) =>
              setForm({ ...form, type: e.target.value as 'billing' | 'shipping' | 'other' })
            }
          />
          <Input
            label="Address Line 1"
            value={form.address_line_1}
            onChange={(e) => setForm({ ...form, address_line_1: e.target.value })}
          />
          <Input
            label="Address Line 2"
            value={form.address_line_2}
            onChange={(e) => setForm({ ...form, address_line_2: e.target.value })}
          />
          <div className="grid gap-4 sm:grid-cols-2">
            <Input
              label="City"
              value={form.city}
              onChange={(e) => setForm({ ...form, city: e.target.value })}
            />
            <Input
              label="State"
              value={form.state}
              onChange={(e) => setForm({ ...form, state: e.target.value })}
            />
            <Input
              label="Postal Code"
              value={form.postal_code}
              onChange={(e) => setForm({ ...form, postal_code: e.target.value })}
            />
            <Input
              label="Country"
              value={form.country}
              onChange={(e) => setForm({ ...form, country: e.target.value })}
            />
          </div>
          <label className="flex items-center gap-2 text-sm">
            <input
              type="checkbox"
              checked={form.is_primary}
              onChange={(e) => setForm({ ...form, is_primary: e.target.checked })}
              className="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500"
            />
            Set as primary address
          </label>
          <div className="flex justify-end gap-3 pt-4">
            <Button variant="outline" onClick={() => setDialogOpen(false)}>
              Cancel
            </Button>
            <Button onClick={handleSave} disabled={saving}>
              {saving ? 'Saving...' : 'Save'}
            </Button>
          </div>
        </div>
      </Dialog>

      <ConfirmDialog
        open={deleteId !== null}
        onClose={() => setDeleteId(null)}
        onConfirm={handleDelete}
        title="Delete Address"
        message="Are you sure you want to delete this address?"
        confirmText="Delete"
        variant="danger"
      />
    </Card>
  );
}

export { AddressManager };
