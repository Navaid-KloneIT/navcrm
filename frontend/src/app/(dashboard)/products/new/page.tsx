'use client';

import React, { useState } from 'react';
import { useRouter } from 'next/navigation';
import { productsApi } from '@/lib/api/products';
import { useToast } from '@/components/ui/toast';
import { PageHeader } from '@/components/shared/page-header';
import { Card, CardContent } from '@/components/ui/card';
import { ProductForm } from '@/components/products/product-form';
import type { ProductFormData } from '@/lib/validations/product';
import type { AxiosError } from 'axios';
import type { ApiError } from '@/types';

export default function NewProductPage() {
  const router = useRouter();
  const { toast } = useToast();
  const [loading, setLoading] = useState(false);

  const handleSubmit = async (data: ProductFormData) => {
    setLoading(true);
    try {
      await productsApi.create(data);
      toast('Product created successfully', 'success');
      router.push('/products');
    } catch (err) {
      const error = err as AxiosError<ApiError>;
      toast(error.response?.data?.message || 'Failed to create product', 'error');
    } finally {
      setLoading(false);
    }
  };

  return (
    <div>
      <PageHeader title="New Product" description="Add a new product to your catalog" />
      <Card>
        <CardContent className="py-6">
          <ProductForm
            onSubmit={handleSubmit}
            onCancel={() => router.push('/products')}
            loading={loading}
          />
        </CardContent>
      </Card>
    </div>
  );
}
