'use client';

import React, { useEffect, useState } from 'react';
import { useParams, useRouter } from 'next/navigation';
import { productsApi } from '@/lib/api/products';
import { useToast } from '@/components/ui/toast';
import { PageHeader } from '@/components/shared/page-header';
import { Card, CardContent } from '@/components/ui/card';
import { Spinner } from '@/components/ui/spinner';
import { ProductForm } from '@/components/products/product-form';
import type { Product } from '@/types';
import type { ProductFormData } from '@/lib/validations/product';
import type { AxiosError } from 'axios';
import type { ApiError } from '@/types';

export default function EditProductPage() {
  const params = useParams();
  const router = useRouter();
  const { toast } = useToast();
  const [product, setProduct] = useState<Product | null>(null);
  const [loading, setLoading] = useState(true);
  const [saving, setSaving] = useState(false);

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

  const handleSubmit = async (data: ProductFormData) => {
    setSaving(true);
    try {
      await productsApi.update(productId, data);
      toast('Product updated successfully', 'success');
      router.push(`/products/${productId}`);
    } catch (err) {
      const error = err as AxiosError<ApiError>;
      toast(error.response?.data?.message || 'Failed to update product', 'error');
    } finally {
      setSaving(false);
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

  const defaultValues: Partial<ProductFormData> = {
    name: product.name,
    sku: product.sku || '',
    description: product.description || '',
    unit_price: product.unit_price,
    cost_price: product.cost_price,
    currency: product.currency,
    unit: product.unit,
    is_active: product.is_active,
    category: product.category || '',
  };

  return (
    <div>
      <PageHeader title="Edit Product" description={`Editing ${product.name}`} />
      <Card>
        <CardContent className="py-6">
          <ProductForm
            defaultValues={defaultValues}
            onSubmit={handleSubmit}
            onCancel={() => router.push(`/products/${productId}`)}
            loading={saving}
          />
        </CardContent>
      </Card>
    </div>
  );
}
