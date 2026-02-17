'use client';

import React, { useEffect, useState, useCallback } from 'react';
import Link from 'next/link';
import { Plus } from 'lucide-react';
import { productsApi } from '@/lib/api/products';
import type { Product, ProductFilters, PaginatedResponse } from '@/types';
import { PageHeader } from '@/components/shared/page-header';
import { SearchInput } from '@/components/shared/search-input';
import { Button } from '@/components/ui/button';
import { Select } from '@/components/ui/select';
import { Pagination } from '@/components/ui/pagination';
import { ProductTable } from '@/components/products/product-table';

const activeOptions = [
  { value: '', label: 'All' },
  { value: 'true', label: 'Active' },
  { value: 'false', label: 'Inactive' },
];

export default function ProductsPage() {
  const [products, setProducts] = useState<Product[]>([]);
  const [meta, setMeta] = useState<PaginatedResponse<Product>['meta'] | null>(null);
  const [loading, setLoading] = useState(true);
  const [filters, setFilters] = useState<ProductFilters>({
    search: '',
    category: '',
    sort_by: 'created_at',
    sort_dir: 'desc',
    page: 1,
    per_page: 15,
  });
  const [activeFilter, setActiveFilter] = useState('');

  const fetchProducts = useCallback(async () => {
    setLoading(true);
    try {
      const params: ProductFilters = { ...filters };
      if (!params.search) delete params.search;
      if (!params.category) delete params.category;
      if (activeFilter !== '') {
        params.is_active = activeFilter === 'true';
      }
      const response = await productsApi.list(params);
      setProducts(response.data.data);
      setMeta(response.data.meta);
    } catch {
      // Handle silently
    } finally {
      setLoading(false);
    }
  }, [filters, activeFilter]);

  useEffect(() => {
    fetchProducts();
  }, [fetchProducts]);

  const handleSort = (key: string) => {
    setFilters((prev) => ({
      ...prev,
      sort_by: key,
      sort_dir: prev.sort_by === key && prev.sort_dir === 'asc' ? 'desc' : 'asc',
      page: 1,
    }));
  };

  return (
    <div>
      <PageHeader
        title="Products"
        description="Manage your product catalog"
        action={
          <Link href="/products/new">
            <Button>
              <Plus className="mr-2 h-4 w-4" />
              Add Product
            </Button>
          </Link>
        }
      />

      <div className="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center">
        <SearchInput
          value={filters.search || ''}
          onChange={(search) =>
            setFilters((prev) => ({ ...prev, search, page: 1 }))
          }
          placeholder="Search products..."
          className="sm:w-72"
        />
        <input
          type="text"
          value={filters.category || ''}
          onChange={(e) =>
            setFilters((prev) => ({ ...prev, category: e.target.value, page: 1 }))
          }
          placeholder="Filter by category..."
          className="block rounded-md border border-gray-300 px-3 py-2 text-sm shadow-sm placeholder:text-gray-400 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 sm:w-48"
        />
        <Select
          options={activeOptions}
          value={activeFilter}
          onChange={(e) => {
            setActiveFilter(e.target.value);
            setFilters((prev) => ({ ...prev, page: 1 }));
          }}
          className="sm:w-36"
        />
      </div>

      <div className="rounded-lg border border-gray-200 bg-white">
        <ProductTable
          products={products}
          loading={loading}
          sortBy={filters.sort_by}
          sortDir={filters.sort_dir}
          onSort={handleSort}
        />
        {meta && (
          <Pagination
            meta={meta}
            onPageChange={(page) =>
              setFilters((prev) => ({ ...prev, page }))
            }
          />
        )}
      </div>
    </div>
  );
}
