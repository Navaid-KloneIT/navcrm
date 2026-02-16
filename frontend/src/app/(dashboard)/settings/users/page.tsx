'use client';

import React, { useEffect, useState, useCallback } from 'react';
import { usersApi } from '@/lib/api/users';
import { rolesApi, type Role } from '@/lib/api/roles';
import { useAuthStore } from '@/lib/stores/auth-store';
import { useToast } from '@/components/ui/toast';
import { PageHeader } from '@/components/shared/page-header';
import { Card, CardContent } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Dialog } from '@/components/ui/dialog';
import { Badge } from '@/components/ui/badge';
import { Avatar } from '@/components/ui/avatar';
import { Spinner } from '@/components/ui/spinner';
import { Pagination, type PaginationMeta } from '@/components/ui/pagination';
import {
  Table,
  TableHeader,
  TableBody,
  TableRow,
  TableHead,
  TableCell,
} from '@/components/ui/table';
import { Plus, Shield } from 'lucide-react';
import type { User, PaginatedResponse } from '@/types';
import type { AxiosError } from 'axios';
import type { ApiError } from '@/types';

export default function UsersPage() {
  const { hasRole } = useAuthStore();
  const { toast } = useToast();
  const [users, setUsers] = useState<User[]>([]);
  const [meta, setMeta] = useState<PaginationMeta | null>(null);
  const [roles, setRoles] = useState<Role[]>([]);
  const [loading, setLoading] = useState(true);
  const [page, setPage] = useState(1);

  const [createOpen, setCreateOpen] = useState(false);
  const [creating, setCreating] = useState(false);
  const [newUser, setNewUser] = useState({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
  });

  const [roleDialogOpen, setRoleDialogOpen] = useState(false);
  const [selectedUser, setSelectedUser] = useState<User | null>(null);
  const [selectedRoleIds, setSelectedRoleIds] = useState<number[]>([]);
  const [savingRoles, setSavingRoles] = useState(false);

  const fetchData = useCallback(async () => {
    setLoading(true);
    try {
      const [usersRes, rolesRes] = await Promise.all([
        usersApi.list({ page, per_page: 15 }),
        rolesApi.list(),
      ]);
      setUsers(usersRes.data.data);
      setMeta(usersRes.data.meta);
      setRoles(rolesRes.data.data);
    } catch {
      toast('Failed to load users', 'error');
    } finally {
      setLoading(false);
    }
  }, [page, toast]);

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

  const handleCreateUser = async () => {
    setCreating(true);
    try {
      await usersApi.create(newUser);
      toast('User created successfully', 'success');
      setCreateOpen(false);
      setNewUser({ name: '', email: '', password: '', password_confirmation: '' });
      fetchData();
    } catch (err) {
      const error = err as AxiosError<ApiError>;
      toast(error.response?.data?.message || 'Failed to create user', 'error');
    } finally {
      setCreating(false);
    }
  };

  const openRoleDialog = (user: User) => {
    setSelectedUser(user);
    const userRoleIds = roles
      .filter((r) => user.roles.includes(r.name))
      .map((r) => r.id);
    setSelectedRoleIds(userRoleIds);
    setRoleDialogOpen(true);
  };

  const handleSaveRoles = async () => {
    if (!selectedUser) return;
    setSavingRoles(true);
    try {
      await usersApi.syncRoles(selectedUser.id, selectedRoleIds);
      toast('Roles updated successfully', 'success');
      setRoleDialogOpen(false);
      fetchData();
    } catch (err) {
      const error = err as AxiosError<ApiError>;
      toast(error.response?.data?.message || 'Failed to update roles', 'error');
    } finally {
      setSavingRoles(false);
    }
  };

  const toggleRole = (roleId: number) => {
    setSelectedRoleIds((prev) =>
      prev.includes(roleId) ? prev.filter((id) => id !== roleId) : [...prev, roleId]
    );
  };

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
        title="Users"
        description="Manage system users and their roles"
        action={
          <Button onClick={() => setCreateOpen(true)}>
            <Plus className="mr-2 h-4 w-4" />
            Create User
          </Button>
        }
      />

      <Card>
        <CardContent className="p-0">
          <Table>
            <TableHeader>
              <TableRow>
                <TableHead>User</TableHead>
                <TableHead>Email</TableHead>
                <TableHead>Roles</TableHead>
                <TableHead>Status</TableHead>
                <TableHead>Actions</TableHead>
              </TableRow>
            </TableHeader>
            <TableBody>
              {users.map((user) => (
                <TableRow key={user.id}>
                  <TableCell>
                    <div className="flex items-center gap-3">
                      <Avatar name={user.name} src={user.avatar} size="sm" />
                      <span className="font-medium text-gray-900">{user.name}</span>
                    </div>
                  </TableCell>
                  <TableCell>
                    <span className="text-gray-600">{user.email}</span>
                  </TableCell>
                  <TableCell>
                    <div className="flex flex-wrap gap-1">
                      {user.roles.map((role) => (
                        <Badge key={role} variant="primary">
                          {role}
                        </Badge>
                      ))}
                      {user.roles.length === 0 && (
                        <span className="text-gray-400">No roles</span>
                      )}
                    </div>
                  </TableCell>
                  <TableCell>
                    <Badge variant={user.is_active ? 'success' : 'default'}>
                      {user.is_active ? 'Active' : 'Inactive'}
                    </Badge>
                  </TableCell>
                  <TableCell>
                    <Button
                      variant="ghost"
                      size="sm"
                      onClick={() => openRoleDialog(user)}
                    >
                      <Shield className="mr-1 h-4 w-4" />
                      Roles
                    </Button>
                  </TableCell>
                </TableRow>
              ))}
            </TableBody>
          </Table>
          {meta && <Pagination meta={meta} onPageChange={setPage} />}
        </CardContent>
      </Card>

      <Dialog
        open={createOpen}
        onClose={() => setCreateOpen(false)}
        title="Create User"
        size="md"
      >
        <div className="space-y-4">
          <Input
            label="Full Name"
            value={newUser.name}
            onChange={(e) => setNewUser({ ...newUser, name: e.target.value })}
          />
          <Input
            label="Email"
            type="email"
            value={newUser.email}
            onChange={(e) => setNewUser({ ...newUser, email: e.target.value })}
          />
          <Input
            label="Password"
            type="password"
            value={newUser.password}
            onChange={(e) => setNewUser({ ...newUser, password: e.target.value })}
          />
          <Input
            label="Confirm Password"
            type="password"
            value={newUser.password_confirmation}
            onChange={(e) =>
              setNewUser({ ...newUser, password_confirmation: e.target.value })
            }
          />
          <div className="flex justify-end gap-3 pt-4">
            <Button variant="outline" onClick={() => setCreateOpen(false)}>
              Cancel
            </Button>
            <Button onClick={handleCreateUser} disabled={creating}>
              {creating ? 'Creating...' : 'Create User'}
            </Button>
          </div>
        </div>
      </Dialog>

      <Dialog
        open={roleDialogOpen}
        onClose={() => setRoleDialogOpen(false)}
        title={`Manage Roles - ${selectedUser?.name || ''}`}
        size="sm"
      >
        <div className="space-y-3">
          {roles.map((role) => (
            <label
              key={role.id}
              className="flex items-center gap-3 rounded-md border border-gray-200 p-3 hover:bg-gray-50"
            >
              <input
                type="checkbox"
                checked={selectedRoleIds.includes(role.id)}
                onChange={() => toggleRole(role.id)}
                className="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500"
              />
              <div>
                <p className="text-sm font-medium text-gray-900 capitalize">{role.name}</p>
                <p className="text-xs text-gray-500">
                  {role.permissions.length} permission{role.permissions.length !== 1 ? 's' : ''}
                </p>
              </div>
            </label>
          ))}
          <div className="flex justify-end gap-3 pt-4">
            <Button variant="outline" onClick={() => setRoleDialogOpen(false)}>
              Cancel
            </Button>
            <Button onClick={handleSaveRoles} disabled={savingRoles}>
              {savingRoles ? 'Saving...' : 'Save Roles'}
            </Button>
          </div>
        </div>
      </Dialog>
    </div>
  );
}
