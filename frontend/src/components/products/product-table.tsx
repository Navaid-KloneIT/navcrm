'use client';

import React from 'react';
import { useRouter } from 'next/navigation';
import type { Product } from '@/types';
import { Badge } from '@/components/ui/badge';
import { DataTable, type Column } from '@/components/shared/data-table';
import { formatCurrency } from '@/lib/utils/format';

interface ProductTableProps {
  products: Product[];
  loading?: boolean;
  sortBy?: string;
  sortDir?: 'asc' | 'desc';
  onSort?: (key: string) => void;
}

function ProductTable({ products, loading, sortBy, sortDir, onSort }: ProductTableProps) {
  const router = useRouter();

  const columns: Column<Product>[] = [
    {
      key: 'name',
      label: 'Name',
      sortable: true,
      render: (product) => (
        <span className="font-medium text-gray-900">{product.name}</span>
      ),
    },
    {
      key: 'sku',
      label: 'SKU',
      sortable: true,
      render: (product) => (
        <span className="text-gray-600">{product.sku || '-'}</span>
      ),
    },
    {
      key: 'category',
      label: 'Category',
      sortable: true,
      render: (product) => (
        <span className="text-gray-600">{product.category || '-'}</span>
      ),
    },
    {
      key: 'unit_price',
      label: 'Unit Price',
      sortable: true,
      render: (product) => (
        <span className="text-gray-900">{formatCurrency(product.unit_price)}</span>
      ),
    },
    {
      key: 'unit',
      label: 'Unit',
      render: (product) => (
        <span className="capitalize text-gray-600">{product.unit}</span>
      ),
    },
    {
      key: 'is_active',
      label: 'Active',
      render: (product) => (
        <Badge variant={product.is_active ? 'success' : 'default'}>
          {product.is_active ? 'Active' : 'Inactive'}
        </Badge>
      ),
    },
  ];

  return (
    <DataTable
      columns={columns}
      data={products}
      loading={loading}
      sortBy={sortBy}
      sortDir={sortDir}
      onSort={onSort}
      onRowClick={(item) => router.push(`/products/${item.id}`)}
      emptyTitle="No products found"
      emptyDescription="Get started by adding your first product."
    />
  );
}

export { ProductTable };
