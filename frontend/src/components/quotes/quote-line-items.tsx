'use client';

import React from 'react';
import { Plus, X } from 'lucide-react';
import type { Product } from '@/types';
import { Button } from '@/components/ui/button';
import { formatCurrency } from '@/lib/utils/format';

interface LineItem {
  product_id?: number | null;
  description: string;
  quantity: number;
  unit_price: number;
  discount_percent: number;
}

interface QuoteLineItemsProps {
  lineItems: LineItem[];
  products: Product[];
  onChange: (updatedItems: LineItem[]) => void;
  readOnly?: boolean;
}

function calculateSubtotal(item: LineItem): number {
  const base = item.quantity * item.unit_price;
  const discount = base * (item.discount_percent / 100);
  return base - discount;
}

function QuoteLineItems({ lineItems, products, onChange, readOnly = false }: QuoteLineItemsProps) {
  const updateItem = (index: number, field: keyof LineItem, value: string | number | null) => {
    const updated = [...lineItems];
    updated[index] = { ...updated[index], [field]: value };
    onChange(updated);
  };

  const removeItem = (index: number) => {
    const updated = lineItems.filter((_, i) => i !== index);
    onChange(updated);
  };

  const addProductItem = (productId: number) => {
    const product = products.find((p) => p.id === productId);
    if (!product) return;

    const newItem: LineItem = {
      product_id: product.id,
      description: product.name,
      quantity: 1,
      unit_price: product.unit_price,
      discount_percent: 0,
    };
    onChange([...lineItems, newItem]);
  };

  const addCustomItem = () => {
    const newItem: LineItem = {
      product_id: null,
      description: '',
      quantity: 1,
      unit_price: 0,
      discount_percent: 0,
    };
    onChange([...lineItems, newItem]);
  };

  const total = lineItems.reduce((sum, item) => sum + calculateSubtotal(item), 0);

  return (
    <div className="space-y-4">
      <div className="overflow-x-auto">
        <table className="w-full text-sm">
          <thead>
            <tr className="border-b border-gray-200">
              <th className="pb-2 text-left font-medium text-gray-700">Description</th>
              <th className="pb-2 text-right font-medium text-gray-700 w-24">Qty</th>
              <th className="pb-2 text-right font-medium text-gray-700 w-32">Unit Price</th>
              <th className="pb-2 text-right font-medium text-gray-700 w-28">Discount %</th>
              <th className="pb-2 text-right font-medium text-gray-700 w-32">Subtotal</th>
              {!readOnly && <th className="pb-2 w-10"></th>}
            </tr>
          </thead>
          <tbody className="divide-y divide-gray-100">
            {lineItems.map((item, index) => (
              <tr key={index}>
                <td className="py-2 pr-2">
                  {readOnly ? (
                    <span className="text-gray-900">{item.description}</span>
                  ) : (
                    <input
                      type="text"
                      value={item.description}
                      onChange={(e) => updateItem(index, 'description', e.target.value)}
                      placeholder="Item description"
                      className="block w-full rounded-md border border-gray-300 px-2 py-1.5 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500"
                    />
                  )}
                </td>
                <td className="py-2 px-2">
                  {readOnly ? (
                    <span className="block text-right text-gray-900">{item.quantity}</span>
                  ) : (
                    <input
                      type="number"
                      min="0.01"
                      step="0.01"
                      value={item.quantity}
                      onChange={(e) => updateItem(index, 'quantity', parseFloat(e.target.value) || 0)}
                      className="block w-full rounded-md border border-gray-300 px-2 py-1.5 text-right text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500"
                    />
                  )}
                </td>
                <td className="py-2 px-2">
                  {readOnly ? (
                    <span className="block text-right text-gray-900">
                      {formatCurrency(item.unit_price)}
                    </span>
                  ) : (
                    <input
                      type="number"
                      min="0"
                      step="0.01"
                      value={item.unit_price}
                      onChange={(e) => updateItem(index, 'unit_price', parseFloat(e.target.value) || 0)}
                      className="block w-full rounded-md border border-gray-300 px-2 py-1.5 text-right text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500"
                    />
                  )}
                </td>
                <td className="py-2 px-2">
                  {readOnly ? (
                    <span className="block text-right text-gray-900">
                      {item.discount_percent}%
                    </span>
                  ) : (
                    <input
                      type="number"
                      min="0"
                      max="100"
                      step="0.1"
                      value={item.discount_percent}
                      onChange={(e) => updateItem(index, 'discount_percent', parseFloat(e.target.value) || 0)}
                      className="block w-full rounded-md border border-gray-300 px-2 py-1.5 text-right text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500"
                    />
                  )}
                </td>
                <td className="py-2 pl-2 text-right font-medium text-gray-900">
                  {formatCurrency(calculateSubtotal(item))}
                </td>
                {!readOnly && (
                  <td className="py-2 pl-2">
                    <button
                      type="button"
                      onClick={() => removeItem(index)}
                      className="rounded p-1 text-gray-400 hover:bg-red-50 hover:text-red-500"
                    >
                      <X className="h-4 w-4" />
                    </button>
                  </td>
                )}
              </tr>
            ))}
          </tbody>
          <tfoot>
            <tr className="border-t border-gray-200">
              <td colSpan={readOnly ? 4 : 5} className="pt-3 text-right font-medium text-gray-700">
                Total
              </td>
              <td className="pt-3 text-right font-semibold text-gray-900">
                {formatCurrency(total)}
              </td>
            </tr>
          </tfoot>
        </table>
      </div>

      {!readOnly && (
        <div className="flex flex-col gap-2 sm:flex-row">
          <div className="flex items-center gap-2">
            <select
              onChange={(e) => {
                const productId = Number(e.target.value);
                if (productId) {
                  addProductItem(productId);
                  e.target.value = '';
                }
              }}
              defaultValue=""
              className="block rounded-md border border-gray-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500"
            >
              <option value="" disabled>
                Add product...
              </option>
              {products
                .filter((p) => p.is_active)
                .map((product) => (
                  <option key={product.id} value={product.id}>
                    {product.name} ({formatCurrency(product.unit_price)})
                  </option>
                ))}
            </select>
          </div>
          <Button type="button" variant="outline" size="sm" onClick={addCustomItem}>
            <Plus className="mr-1 h-4 w-4" />
            Add Custom Item
          </Button>
        </div>
      )}
    </div>
  );
}

export { QuoteLineItems };
export type { LineItem };
