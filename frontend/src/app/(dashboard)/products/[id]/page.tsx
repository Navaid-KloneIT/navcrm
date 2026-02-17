'use client';

import React, { useEffect, useState } from 'react';
import { useParams, useRouter } from 'next/navigation';
import Link from 'next/link';
import {
  Package,
  DollarSign,
  Tag,
  Hash,
  Pencil,
  Trash2,
} from 'lucide-react';
import { productsApi } from '@/lib/api/products';
import type { Product } from '@/types';
import { useToast } from '@/components/ui/toast';
import { PageHeader } from '@/components/shared/page-header';
import { Card, CardHeader, CardTitle, CardContent } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Spinner } from '@/components/ui/spinner';
import { ConfirmDialog } from '@/components/shared/confirm-dialog';
import { formatCurrency, formatDate } from '@/lib/utils/format';
import type { AxiosError } from 'axios';
import type { ApiError } from '@/types';

export default function ProductDetailPage() {
  const params = useParams();
  const router = useRouter();
  const { toast } = useToast();
  const [product, setProduct] = useState<Product | null>(null);
  const [loading, setLoading] = useState(true);
  const [deleteOpen, setDeleteOpen] = useState(false);

  const productId = Number(params.id);

  useEffect(() => {
    const fetchProduct = async () => {
      try {
        const response = await productsApi.get(productId);
        setProduct(response.data.data);
      } catch {
        toast('Failed to load product', 'error');
        router.push('/products');
      } finally {
        setLoading(false);
      }
    };

    fetchProduct();
  }, [productId, router, toast]);

  const handleDelete = async () => {
    try {
      await productsApi.delete(productId);
      toast('Product deleted successfully', 'success');
      router.push('/products');
    } catch (err) {
      const error = err as AxiosError<ApiError>;
      toast(error.response?.data?.message || 'Failed to delete product', 'error');
    }
  };

  if (loading) {
    return (
      <div className="flex items-center justify-center py-20">
        <Spinner size="lg" />
      </div>
    );
  }

  if (!product) return null;

  return (
    <div>
      <PageHeader
        title={product.name}
        description={product.sku ? `SKU: ${product.sku}` : undefined}
        action={
          <div className="flex gap-2">
            <Link href={`/products/${product.id}/edit`}>
              <Button variant="outline">
                <Pencil className="mr-2 h-4 w-4" />
                Edit
              </Button>
            </Link>
            <Button variant="destructive" onClick={() => setDeleteOpen(true)}>
              <Trash2 className="mr-2 h-4 w-4" />
              Delete
            </Button>
          </div>
        }
      />

      <div className="grid gap-6 lg:grid-cols-3">
        <div className="space-y-6 lg:col-span-2">
          <Card>
            <CardHeader>
              <CardTitle>Product Information</CardTitle>
            </CardHeader>
            <CardContent>
              <div className="grid gap-4 sm:grid-cols-2">
                <div className="flex items-center gap-2 text-sm">
                  <Package className="h-4 w-4 text-gray-400" />
                  <span className="text-gray-500">Name:</span>
                  <span className="font-medium text-gray-900">{product.name}</span>
                </div>
                {product.sku && (
                  <div className="flex items-center gap-2 text-sm">
                    <Hash className="h-4 w-4 text-gray-400" />
                    <span className="text-gray-500">SKU:</span>
                    <span className="font-medium text-gray-900">{product.sku}</span>
                  </div>
                )}
                {product.category && (
                  <div className="flex items-center gap-2 text-sm">
                    <Tag className="h-4 w-4 text-gray-400" />
                    <span className="text-gray-500">Category:</span>
                    <span className="font-medium text-gray-900">{product.category}</span>
                  </div>
                )}
                <div className="flex items-center gap-2 text-sm">
                  <span className="text-gray-500">Unit:</span>
                  <span className="capitalize font-medium text-gray-900">{product.unit}</span>
                </div>
                <div className="flex items-center gap-2 text-sm">
                  <span className="text-gray-500">Status:</span>
                  <Badge variant={product.is_active ? 'success' : 'default'}>
                    {product.is_active ? 'Active' : 'Inactive'}
                  </Badge>
                </div>
              </div>
              {product.description && (
                <div className="mt-4 border-t border-gray-200 pt-4">
                  <p className="text-sm text-gray-600">{product.description}</p>
                </div>
              )}
            </CardContent>
          </Card>

          <Card>
            <CardHeader>
              <CardTitle>Pricing Details</CardTitle>
            </CardHeader>
            <CardContent>
              <div className="grid gap-4 sm:grid-cols-3">
                <div className="rounded-lg border border-gray-200 p-4">
                  <div className="flex items-center gap-2">
                    <DollarSign className="h-4 w-4 text-gray-400" />
                    <span className="text-sm text-gray-500">Unit Price</span>
                  </div>
                  <p className="mt-1 text-2xl font-semibold text-gray-900">
                    {formatCurrency(product.unit_price)}
                  </p>
                </div>
                <div className="rounded-lg border border-gray-200 p-4">
                  <div className="flex items-center gap-2">
                    <DollarSign className="h-4 w-4 text-gray-400" />
                    <span className="text-sm text-gray-500">Cost Price</span>
                  </div>
                  <p className="mt-1 text-2xl font-semibold text-gray-900">
                    {product.cost_price != null ? formatCurrency(product.cost_price) : '-'}
                  </p>
                </div>
                <div className="rounded-lg border border-gray-200 p-4">
                  <div className="flex items-center gap-2">
                    <span className="text-sm text-gray-500">Margin</span>
                  </div>
                  <p className="mt-1 text-2xl font-semibold text-gray-900">
                    {product.cost_price != null && product.cost_price > 0
                      ? `${(((product.unit_price - product.cost_price) / product.unit_price) * 100).toFixed(1)}%`
                      : '-'}
                  </p>
                </div>
              </div>
              <div className="mt-3 text-sm text-gray-500">
                Currency: <span className="font-medium text-gray-900">{product.currency}</span>
              </div>
            </CardContent>
          </Card>
        </div>

        <div className="space-y-6">
          <Card>
            <CardHeader>
              <CardTitle>Details</CardTitle>
            </CardHeader>
            <CardContent className="space-y-3 text-sm">
              <div className="flex justify-between">
                <span className="text-gray-500">Created</span>
                <span className="font-medium text-gray-900">
                  {formatDate(product.created_at)}
                </span>
              </div>
              <div className="flex justify-between">
                <span className="text-gray-500">Updated</span>
                <span className="font-medium text-gray-900">
                  {formatDate(product.updated_at)}
                </span>
              </div>
            </CardContent>
          </Card>
        </div>
      </div>

      <ConfirmDialog
        open={deleteOpen}
        onClose={() => setDeleteOpen(false)}
        onConfirm={handleDelete}
        title="Delete Product"
        message={`Are you sure you want to delete ${product.name}? This action cannot be undone.`}
        confirmText="Delete"
        variant="danger"
      />
    </div>
  );
}
