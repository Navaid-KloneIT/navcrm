import React from 'react';
import { Badge } from '@/components/ui/badge';
import type { BadgeVariant } from '@/components/ui/badge';
import type { QuoteStatus } from '@/types';

interface QuoteStatusBadgeProps {
  status: QuoteStatus;
}

const statusConfig: Record<QuoteStatus, { label: string; variant: BadgeVariant }> = {
  draft: { label: 'Draft', variant: 'default' },
  sent: { label: 'Sent', variant: 'primary' },
  accepted: { label: 'Accepted', variant: 'success' },
  rejected: { label: 'Rejected', variant: 'danger' },
  expired: { label: 'Expired', variant: 'warning' },
};

function QuoteStatusBadge({ status }: QuoteStatusBadgeProps) {
  const config = statusConfig[status];

  return (
    <Badge variant={config.variant}>{config.label}</Badge>
  );
}

export { QuoteStatusBadge };
