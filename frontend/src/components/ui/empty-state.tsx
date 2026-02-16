import React from 'react';
import { cn } from '@/lib/utils/cn';
import { InboxIcon } from 'lucide-react';
import { Button } from './button';

interface EmptyStateProps {
  icon?: React.ReactNode;
  title: string;
  description?: string;
  actionLabel?: string;
  onAction?: () => void;
  className?: string;
}

function EmptyState({
  icon,
  title,
  description,
  actionLabel,
  onAction,
  className,
}: EmptyStateProps) {
  return (
    <div
      className={cn(
        'flex flex-col items-center justify-center py-12 text-center',
        className
      )}
    >
      <div className="mb-4 text-gray-400">
        {icon || <InboxIcon className="h-12 w-12" />}
      </div>
      <h3 className="mb-1 text-lg font-medium text-gray-900">{title}</h3>
      {description && (
        <p className="mb-4 max-w-sm text-sm text-gray-500">{description}</p>
      )}
      {actionLabel && onAction && (
        <Button onClick={onAction}>{actionLabel}</Button>
      )}
    </div>
  );
}

export { EmptyState };
export type { EmptyStateProps };
