'use client';

import { useEffect, useState } from 'react';
import { useRouter } from 'next/navigation';
import { useAppSelector, useAppDispatch } from '@/store/hooks';
import { checkAuth } from '@/store/slices/authSlice';
import { ROUTES } from '@/lib/constants';

interface AuthGuardProps {
  children: React.ReactNode;
  requireAuth?: boolean;
  requireAdmin?: boolean;
  redirectTo?: string;
}

export function AuthGuard({ 
  children, 
  requireAuth = true, 
  requireAdmin = false,
  redirectTo 
}: AuthGuardProps) {
  const { user, isLoading, isAuthenticated } = useAppSelector((state) => state.auth);
  const dispatch = useAppDispatch();
  const router = useRouter();
  const [mounted, setMounted] = useState(false);

  useEffect(() => {
    setMounted(true);
  }, []);

  
  useEffect(() => {
    if (mounted && !isAuthenticated && !user && !isLoading) {
      dispatch(checkAuth());
    }
  }, [mounted, isAuthenticated, user, isLoading, dispatch]);

  useEffect(() => {
    if (!mounted || isLoading) return;

    if (requireAuth && !isAuthenticated) {
      router.push(redirectTo || ROUTES.AUTH.LOGIN);
      return;
    }

    if (requireAdmin && user?.role !== 'admin') {
      router.push(redirectTo || ROUTES.PUBLIC.HOME);
      return;
    }
  }, [mounted, isAuthenticated, user, isLoading, requireAuth, requireAdmin, redirectTo, router]);

  
  if (!mounted) {
    return null;
  }

  if (isLoading) {
    return (
      <div className="flex items-center justify-center min-h-screen">
        <div className="animate-spin rounded-full h-8 w-8 border-b-2 border-red-600"></div>
        <p className="ml-2 text-muted-foreground">Loading...</p>
      </div>
    );
  }

  if (requireAuth && !isAuthenticated) {
    return null;
  }

  if (requireAdmin && user?.role !== 'admin') {
    return null;
  }

  return <>{children}</>;
}