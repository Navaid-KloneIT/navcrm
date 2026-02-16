import React from 'react';

interface TagBadgeProps {
  name: string;
  color?: string;
}

function TagBadge({ name, color }: TagBadgeProps) {
  const style = color
    ? { backgroundColor: `${color}20`, color }
    : { backgroundColor: '#e5e7eb', color: '#374151' };

  return (
    <span
      className="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium"
      style={style}
    >
      {name}
    </span>
  );
}

export { TagBadge };
