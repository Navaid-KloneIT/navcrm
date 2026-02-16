'use client';

import React, { useState } from 'react';
import Link from 'next/link';
import { useSearchParams } from 'next/navigation';
import { useForm } from 'react-hook-form';
import { zodResolver } from '@hookform/resolvers/zod';
import { resetPasswordSchema, type ResetPasswordFormData } from '@/lib/validations/auth';
import { authApi } from '@/lib/api/auth';
import { Input } from '@/components/ui/input';
import { Button } from '@/components/ui/button';
import type { AxiosError } from 'axios';
import type { ApiError } from '@/types';

export default function ResetPasswordPage() {
  const searchParams = useSearchParams();
  const [serverError, setServerError] = useState('');
  const [success, setSuccess] = useState(false);
  const [loading, setLoading] = useState(false);

  const tokenFromUrl = searchParams.get('token') || '';
  const emailFromUrl = searchParams.get('email') || '';

  const {
    register,
    handleSubmit,
    formState: { errors },
  } = useForm<ResetPasswordFormData>({
    resolver: zodResolver(resetPasswordSchema),
    defaultValues: {
      token: tokenFromUrl,
      email: emailFromUrl,
      password: '',
      password_confirmation: '',
    },
  });

  const onSubmit = async (data: ResetPasswordFormData) => {
    setServerError('');
    setLoading(true);
    try {
      await authApi.resetPassword(data);
      setSuccess(true);
    } catch (err) {
      const error = err as AxiosError<ApiError>;
      setServerError(
        error.response?.data?.message || 'Unable to reset password. Please try again.'
      );
    } finally {
      setLoading(false);
    }
  };

  if (success) {
    return (
      <div className="text-center">
        <div className="mb-4 inline-flex h-16 w-16 items-center justify-center rounded-full bg-green-100">
          <svg
            className="h-8 w-8 text-green-600"
            fill="none"
            viewBox="0 0 24 24"
            stroke="currentColor"
          >
            <path
              strokeLinecap="round"
              strokeLinejoin="round"
              strokeWidth={2}
              d="M5 13l4 4L19 7"
            />
          </svg>
        </div>
        <h2 className="mb-2 text-xl font-semibold text-gray-900">Password reset successful</h2>
        <p className="mb-6 text-sm text-gray-600">
          Your password has been reset. You can now sign in with your new password.
        </p>
        <Link
          href="/login"
          className="inline-block rounded-md bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700"
        >
          Sign in
        </Link>
      </div>
    );
  }

  return (
    <div>
      <h2 className="mb-2 text-center text-xl font-semibold text-gray-900">
        Reset your password
      </h2>
      <p className="mb-6 text-center text-sm text-gray-500">
        Enter your new password below.
      </p>

      {serverError && (
        <div className="mb-4 rounded-md bg-red-50 px-4 py-3 text-sm text-red-700">
          {serverError}
        </div>
      )}

      <form onSubmit={handleSubmit(onSubmit)} className="space-y-4">
        <input type="hidden" {...register('token')} />
        <Input
          label="Email"
          type="email"
          placeholder="you@example.com"
          error={errors.email?.message}
          {...register('email')}
        />
        <Input
          label="New Password"
          type="password"
          placeholder="At least 8 characters"
          error={errors.password?.message}
          {...register('password')}
        />
        <Input
          label="Confirm New Password"
          type="password"
          placeholder="Confirm your new password"
          error={errors.password_confirmation?.message}
          {...register('password_confirmation')}
        />

        <Button type="submit" className="w-full" disabled={loading}>
          {loading ? 'Resetting...' : 'Reset password'}
        </Button>
      </form>

      <div className="mt-4 text-center text-sm">
        <Link href="/login" className="text-blue-600 hover:text-blue-700">
          Back to sign in
        </Link>
      </div>
    </div>
  );
}
