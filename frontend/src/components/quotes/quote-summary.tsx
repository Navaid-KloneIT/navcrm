import React from 'react';
import { formatCurrency } from '@/lib/utils/format';

interface QuoteSummaryProps {
  subtotal: number;
  discountType: 'percentage' | 'fixed';
  discountValue: number;
  discountAmount: number;
  taxRate: number;
  taxAmount: number;
  total: number;
}

function QuoteSummary({
  subtotal,
  discountType,
  discountValue,
  discountAmount,
  taxRate,
  taxAmount,
  total,
}: QuoteSummaryProps) {
  return (
    <div className="space-y-2 text-sm">
      <div className="flex justify-between">
        <span className="text-gray-500">Subtotal</span>
        <span className="font-medium text-gray-900">{formatCurrency(subtotal)}</span>
      </div>
      {discountAmount > 0 && (
        <div className="flex justify-between">
          <span className="text-gray-500">
            Discount
            {discountType === 'percentage'
              ? ` (${discountValue}%)`
              : ''}
          </span>
          <span className="font-medium text-red-600">-{formatCurrency(discountAmount)}</span>
        </div>
      )}
      {taxAmount > 0 && (
        <div className="flex justify-between">
          <span className="text-gray-500">Tax ({taxRate}%)</span>
          <span className="font-medium text-gray-900">{formatCurrency(taxAmount)}</span>
        </div>
      )}
      <div className="flex justify-between border-t border-gray-200 pt-2">
        <span className="font-medium text-gray-900">Total</span>
        <span className="text-lg font-semibold text-gray-900">{formatCurrency(total)}</span>
      </div>
    </div>
  );
}

export { QuoteSummary };
