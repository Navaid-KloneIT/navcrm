import React from 'react';
import { Badge } from '@/components/ui/badge';
import type { LeadScore } from '@/types';

interface LeadScoreBadgeProps {
  score: LeadScore;
}

const scoreConfig: Record<LeadScore, { label: string; variant: 'danger' | 'warning' | 'primary' }> = {
  hot: { label: 'Hot', variant: 'danger' },
  warm: { label: 'Warm', variant: 'warning' },
  cold: { label: 'Cold', variant: 'primary' },
};

function LeadScoreBadge({ score }: LeadScoreBadgeProps) {
  const config = scoreConfig[score];

  return (
    <Badge variant={config.variant}>{config.label}</Badge>
  );
}

export { LeadScoreBadge };
