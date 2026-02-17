'use client';

import React, { useState } from 'react';
import { Dialog } from '@/components/ui/dialog';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Select } from '@/components/ui/select';
import {
  Table,
  TableHeader,
  TableBody,
  TableRow,
  TableHead,
  TableCell,
} from '@/components/ui/table';
import { opportunitiesApi } from '@/lib/api/opportunities';
import { useToast } from '@/components/ui/toast';
import { TEAM_ROLES } from '@/lib/utils/constants';
import { Trash2 } from 'lucide-react';
import type { OpportunityTeamMember } from '@/types';
import type { AxiosError } from 'axios';
import type { ApiError } from '@/types';

interface OpportunityTeamDialogProps {
  open: boolean;
  onClose: () => void;
  opportunityId: number;
  teamMembers: OpportunityTeamMember[];
  onUpdate: () => void;
  users?: { id: number; name: string }[];
}

function OpportunityTeamDialog({
  open,
  onClose,
  opportunityId,
  teamMembers,
  onUpdate,
  users = [],
}: OpportunityTeamDialogProps) {
  const { toast } = useToast();
  const [loading, setLoading] = useState(false);
  const [userId, setUserId] = useState<string>('');
  const [role, setRole] = useState<string>('support');
  const [splitPercentage, setSplitPercentage] = useState<string>('0');

  const totalSplit = teamMembers.reduce(
    (sum, m) => sum + Number(m.split_percentage),
    0
  );

  const handleAddMember = async () => {
    if (!userId) return;
    setLoading(true);
    try {
      await opportunitiesApi.addTeamMember(opportunityId, {
        user_id: Number(userId),
        role,
        split_percentage: Number(splitPercentage),
      });
      toast('Team member added successfully', 'success');
      setUserId('');
      setRole('support');
      setSplitPercentage('0');
      onUpdate();
    } catch (err) {
      const error = err as AxiosError<ApiError>;
      toast(
        error.response?.data?.message || 'Failed to add team member',
        'error'
      );
    } finally {
      setLoading(false);
    }
  };

  const handleRemoveMember = async (memberId: number) => {
    setLoading(true);
    try {
      await opportunitiesApi.removeTeamMember(opportunityId, memberId);
      toast('Team member removed', 'success');
      onUpdate();
    } catch (err) {
      const error = err as AxiosError<ApiError>;
      toast(
        error.response?.data?.message || 'Failed to remove team member',
        'error'
      );
    } finally {
      setLoading(false);
    }
  };

  const userOptions = users.map((u) => ({ value: u.id, label: u.name }));
  const roleOptions = TEAM_ROLES.map((r) => ({
    value: r.value,
    label: r.label,
  }));

  return (
    <Dialog open={open} onClose={onClose} title="Deal Team" size="lg">
      <div className="space-y-6">
        {teamMembers.length > 0 ? (
          <Table>
            <TableHeader>
              <TableRow>
                <TableHead>Name</TableHead>
                <TableHead>Role</TableHead>
                <TableHead>Split %</TableHead>
                <TableHead className="w-12" />
              </TableRow>
            </TableHeader>
            <TableBody>
              {teamMembers.map((member) => (
                <TableRow key={member.id}>
                  <TableCell>
                    <div>
                      <p className="font-medium text-gray-900">{member.name}</p>
                      <p className="text-xs text-gray-500">{member.email}</p>
                    </div>
                  </TableCell>
                  <TableCell>
                    <span className="capitalize text-gray-600">
                      {member.role}
                    </span>
                  </TableCell>
                  <TableCell>
                    <span className="text-gray-600">
                      {member.split_percentage}%
                    </span>
                  </TableCell>
                  <TableCell>
                    <Button
                      variant="ghost"
                      size="sm"
                      onClick={() => handleRemoveMember(member.id)}
                      disabled={loading}
                    >
                      <Trash2 className="h-4 w-4 text-red-500" />
                    </Button>
                  </TableCell>
                </TableRow>
              ))}
            </TableBody>
          </Table>
        ) : (
          <p className="py-4 text-center text-sm text-gray-500">
            No team members yet. Add someone below.
          </p>
        )}

        <div className="rounded-md border border-gray-200 bg-gray-50 p-4">
          <p className="mb-1 text-sm font-medium text-gray-700">
            Total Split: {totalSplit}%
          </p>
          {totalSplit > 100 && (
            <p className="text-xs text-red-600">
              Warning: Total split exceeds 100%
            </p>
          )}
        </div>

        <div>
          <h4 className="mb-3 text-sm font-semibold text-gray-700">
            Add Team Member
          </h4>
          <div className="grid gap-3 sm:grid-cols-3">
            <Select
              label="User"
              options={userOptions}
              placeholder="Select user"
              value={userId}
              onChange={(e) => setUserId(e.target.value)}
            />
            <Select
              label="Role"
              options={roleOptions}
              value={role}
              onChange={(e) => setRole(e.target.value)}
            />
            <Input
              label="Split %"
              type="number"
              min="0"
              max="100"
              value={splitPercentage}
              onChange={(e) => setSplitPercentage(e.target.value)}
            />
          </div>
          <div className="mt-4 flex justify-end">
            <Button
              onClick={handleAddMember}
              disabled={loading || !userId}
              size="sm"
            >
              {loading ? 'Adding...' : 'Add Member'}
            </Button>
          </div>
        </div>

        <div className="flex justify-end border-t border-gray-200 pt-4">
          <Button variant="outline" onClick={onClose}>
            Close
          </Button>
        </div>
      </div>
    </Dialog>
  );
}

export { OpportunityTeamDialog };
