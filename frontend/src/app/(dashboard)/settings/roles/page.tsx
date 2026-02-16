'use client';

import React, { useEffect, useState, useCallback } from 'react';
import { rolesApi, type Role, type Permission } from '@/lib/api/roles';
import { useAuthStore } from '@/lib/stores/auth-store';
import { useToast } from '@/components/ui/toast';
import { PageHeader } from '@/components/shared/page-header';
import { Card, CardContent } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Dialog } from '@/components/ui/dialog';
import { Badge } from '@/components/ui/badge';
import { Spinner } from '@/components/ui/spinner';
import { ConfirmDialog } from '@/components/shared/confirm-dialog';
import { Plus, Pencil, Trash2, Shield } from 'lucide-react';
import type { AxiosError } from 'axios';
import type { ApiError } from '@/types';

export default function RolesPage() {
  const { hasRole } = useAuthStore();
  const { toast } = useToast();
  const [roles, setRoles] = useState<Role[]>([]);
  const [permissions, setPermissions] = useState<Permission[]>([]);
  const [loading, setLoading] = useState(true);

  const [dialogOpen, setDialogOpen] = useState(false);
  const [editingRole, setEditingRole] = useState<Role | null>(null);
  const [roleName, setRoleName] = useState('');
  const [selectedPermissionIds, setSelectedPermissionIds] = useState<number[]>([]);
  const [saving, setSaving] = useState(false);
  const [deleteId, setDeleteId] = useState<number | null>(null);

  const fetchData = useCallback(async () => {
    setLoading(true);
    try {
      const [rolesRes, permissionsRes] = await Promise.all([
        rolesApi.list(),
        rolesApi.listPermissions(),
      ]);
      setRoles(rolesRes.data.data);
      setPermissions(permissionsRes.data.data);
    } catch {
      toast('Failed to load roles', 'error');
    } finally {
      setLoading(false);
    }
  }, [toast]);

  useEffect(() => {
    if (!hasRole('admin')) return;
    fetchData();
  }, [fetchData, hasRole]);

  if (!hasRole('admin')) {
    return (
      <div className="flex items-center justify-center py-20">
        <p className="text-gray-500">You do not have permission to access this page.</p>
      </div>
    );
  }

  const openCreate = () => {
    setEditingRole(null);
    setRoleName('');
    setSelectedPermissionIds([]);
    setDialogOpen(true);
  };

  const openEdit = (role: Role) => {
    setEditingRole(role);
    setRoleName(role.name);
    setSelectedPermissionIds(role.permissions.map((p) => p.id));
    setDialogOpen(true);
  };

  const handleSave = async () => {
    if (!roleName.trim()) {
      toast('Role name is required', 'error');
      return;
    }

    setSaving(true);
    try {
      if (editingRole) {
        await rolesApi.update(editingRole.id, {
          name: roleName,
          permission_ids: selectedPermissionIds,
        });
        toast('Role updated successfully', 'success');
      } else {
        await rolesApi.create({
          name: roleName,
          permission_ids: selectedPermissionIds,
        });
        toast('Role created successfully', 'success');
      }
      setDialogOpen(false);
      fetchData();
    } catch (err) {
      const error = err as AxiosError<ApiError>;
      toast(error.response?.data?.message || 'Failed to save role', 'error');
    } finally {
      setSaving(false);
    }
  };

  const handleDelete = async () => {
    if (!deleteId) return;
    try {
      await rolesApi.delete(deleteId);
      toast('Role deleted successfully', 'success');
      setDeleteId(null);
      fetchData();
    } catch (err) {
      const error = err as AxiosError<ApiError>;
      toast(error.response?.data?.message || 'Failed to delete role', 'error');
    }
  };

  const togglePermission = (permId: number) => {
    setSelectedPermissionIds((prev) =>
      prev.includes(permId) ? prev.filter((id) => id !== permId) : [...prev, permId]
    );
  };

  // Group permissions by resource
  const groupedPermissions = permissions.reduce<Record<string, Permission[]>>(
    (acc, perm) => {
      const parts = perm.name.split('.');
      const group = parts.length > 1 ? parts[0] : 'general';
      if (!acc[group]) acc[group] = [];
      acc[group].push(perm);
      return acc;
    },
    {}
  );

  if (loading) {
    return (
      <div className="flex items-center justify-center py-20">
        <Spinner size="lg" />
      </div>
    );
  }

  return (
    <div>
      <PageHeader
        title="Roles & Permissions"
        description="Manage roles and their permissions"
        action={
          <Button onClick={openCreate}>
            <Plus className="mr-2 h-4 w-4" />
            Create Role
          </Button>
        }
      />

      <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
        {roles.map((role) => (
          <Card key={role.id}>
            <CardContent className="py-5">
              <div className="mb-3 flex items-center justify-between">
                <div className="flex items-center gap-2">
                  <Shield className="h-5 w-5 text-blue-500" />
                  <h3 className="text-lg font-semibold capitalize text-gray-900">
                    {role.name}
                  </h3>
                </div>
                <div className="flex gap-1">
                  <button
                    onClick={() => openEdit(role)}
                    className="rounded p-1 text-gray-400 hover:bg-gray-100 hover:text-gray-600"
                  >
                    <Pencil className="h-4 w-4" />
                  </button>
                  <button
                    onClick={() => setDeleteId(role.id)}
                    className="rounded p-1 text-gray-400 hover:bg-red-50 hover:text-red-600"
                  >
                    <Trash2 className="h-4 w-4" />
                  </button>
                </div>
              </div>
              <div className="flex flex-wrap gap-1">
                {role.permissions.slice(0, 5).map((perm) => (
                  <Badge key={perm.id} variant="default">
                    {perm.name}
                  </Badge>
                ))}
                {role.permissions.length > 5 && (
                  <Badge variant="default">
                    +{role.permissions.length - 5} more
                  </Badge>
                )}
                {role.permissions.length === 0 && (
                  <span className="text-sm text-gray-400">No permissions</span>
                )}
              </div>
            </CardContent>
          </Card>
        ))}
      </div>

      <Dialog
        open={dialogOpen}
        onClose={() => setDialogOpen(false)}
        title={editingRole ? 'Edit Role' : 'Create Role'}
        size="lg"
      >
        <div className="space-y-4">
          <Input
            label="Role Name"
            value={roleName}
            onChange={(e) => setRoleName(e.target.value)}
            placeholder="e.g., manager"
          />

          <div>
            <label className="mb-2 block text-sm font-medium text-gray-700">
              Permissions
            </label>
            <div className="max-h-64 space-y-4 overflow-y-auto rounded-md border border-gray-200 p-4">
              {Object.entries(groupedPermissions).map(([group, perms]) => (
                <div key={group}>
                  <p className="mb-2 text-xs font-semibold uppercase tracking-wider text-gray-500">
                    {group}
                  </p>
                  <div className="grid gap-2 sm:grid-cols-2">
                    {perms.map((perm) => (
                      <label
                        key={perm.id}
                        className="flex items-center gap-2 text-sm"
                      >
                        <input
                          type="checkbox"
                          checked={selectedPermissionIds.includes(perm.id)}
                          onChange={() => togglePermission(perm.id)}
                          className="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                        />
                        <span className="text-gray-700">{perm.name}</span>
                      </label>
                    ))}
                  </div>
                </div>
              ))}
            </div>
          </div>

          <div className="flex justify-end gap-3 pt-4">
            <Button variant="outline" onClick={() => setDialogOpen(false)}>
              Cancel
            </Button>
            <Button onClick={handleSave} disabled={saving}>
              {saving ? 'Saving...' : editingRole ? 'Update Role' : 'Create Role'}
            </Button>
          </div>
        </div>
      </Dialog>

      <ConfirmDialog
        open={deleteId !== null}
        onClose={() => setDeleteId(null)}
        onConfirm={handleDelete}
        title="Delete Role"
        message="Are you sure you want to delete this role? Users with this role will lose associated permissions."
        confirmText="Delete"
        variant="danger"
      />
    </div>
  );
}
