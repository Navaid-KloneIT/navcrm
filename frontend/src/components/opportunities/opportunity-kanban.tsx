'use client';

import React, { useState } from 'react';
import Link from 'next/link';
import type { Opportunity, PipelineStage } from '@/types';
import { formatDate } from '@/lib/utils/format';

interface OpportunityKanbanProps {
  opportunities: Opportunity[];
  stages: PipelineStage[];
  onStageChange: (id: number, stageId: number) => void;
}

function OpportunityKanban({
  opportunities,
  stages,
  onStageChange,
}: OpportunityKanbanProps) {
  const [draggedId, setDraggedId] = useState<number | null>(null);

  const columns = stages.map((stage) => {
    const stageOpps = opportunities.filter((o) => o.stage.id === stage.id);
    const totalAmount = stageOpps.reduce((sum, o) => sum + Number(o.amount), 0);
    return {
      stage,
      opportunities: stageOpps,
      totalAmount,
    };
  });

  const handleDragStart = (e: React.DragEvent, opportunityId: number) => {
    e.dataTransfer.effectAllowed = 'move';
    e.dataTransfer.setData('text/plain', String(opportunityId));
    setDraggedId(opportunityId);
  };

  const handleDragOver = (e: React.DragEvent) => {
    e.preventDefault();
    e.dataTransfer.dropEffect = 'move';
  };

  const handleDrop = (e: React.DragEvent, stageId: number) => {
    e.preventDefault();
    const opportunityId = Number(e.dataTransfer.getData('text/plain'));
    if (opportunityId) {
      onStageChange(opportunityId, stageId);
    }
    setDraggedId(null);
  };

  const handleDragEnd = () => {
    setDraggedId(null);
  };

  return (
    <div className="flex gap-4 overflow-x-auto pb-4">
      {columns.map((column) => (
        <div
          key={column.stage.id}
          className="flex w-72 shrink-0 flex-col rounded-lg border-t-4 bg-gray-50"
          style={{ borderTopColor: column.stage.color || '#9ca3af' }}
          onDragOver={handleDragOver}
          onDrop={(e) => handleDrop(e, column.stage.id)}
        >
          <div className="px-3 py-2">
            <div className="flex items-center justify-between">
              <h3 className="text-sm font-semibold text-gray-700">
                {column.stage.name}
              </h3>
              <span className="rounded-full bg-gray-200 px-2 py-0.5 text-xs font-medium text-gray-600">
                {column.opportunities.length}
              </span>
            </div>
            <p className="mt-0.5 text-xs text-gray-500">
              ${column.totalAmount.toLocaleString('en-US', { minimumFractionDigits: 2 })}
            </p>
          </div>
          <div className="flex-1 space-y-2 p-2">
            {column.opportunities.map((opp) => (
              <Link
                key={opp.id}
                href={`/opportunities/${opp.id}`}
                draggable
                onDragStart={(e) => handleDragStart(e, opp.id)}
                onDragEnd={handleDragEnd}
                className={`block rounded-md border border-gray-200 bg-white p-3 shadow-sm transition-shadow hover:shadow-md ${
                  draggedId === opp.id ? 'opacity-50' : ''
                }`}
              >
                <p className="text-sm font-medium text-gray-900">{opp.name}</p>
                {opp.account && (
                  <p className="mt-0.5 text-xs text-gray-500">
                    {opp.account.name}
                  </p>
                )}
                <p className="mt-1 text-sm font-semibold text-gray-800">
                  ${Number(opp.amount).toLocaleString('en-US', { minimumFractionDigits: 2 })}
                </p>
                {opp.close_date && (
                  <p className="mt-0.5 text-xs text-gray-400">
                    Close: {formatDate(opp.close_date)}
                  </p>
                )}
              </Link>
            ))}
            {column.opportunities.length === 0 && (
              <p className="py-4 text-center text-xs text-gray-400">
                No opportunities
              </p>
            )}
          </div>
        </div>
      ))}
    </div>
  );
}

export { OpportunityKanban };
