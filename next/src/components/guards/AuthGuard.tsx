'use client';

import { useEffect, useState } from 'react';
import { useRouter } from 'next/navigation';
import { useSession } from 'next-auth/react';
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
  const { data: session, status } = useSession();
  const router = useRouter();
  const [mounted, setMounted] = useState(false);

  useEffect(() => {
    setMounted(true);
  }, []);

  useEffect(() => {
    if (!mounted || status === 'loading') return;

    if (requireAuth && status === 'unauthenticated') {
      router.push(redirectTo || ROUTES.AUTH.LOGIN);
      return;
    }

    if (requireAdmin && session?.user?.role !== 'admin') {
      router.push(redirectTo || ROUTES.PUBLIC.HOME);
      return;
    }
  }, [mounted, session, status, requireAuth, requireAdmin, redirectTo, router]);

  if (!mounted) {
    return null;
  }

  if (status === 'loading') {
    return (
      <div className="flex items-center justify-center min-h-screen">
        <div className="animate-spin rounded-full h-8 w-8 border-b-2 border-red-600"></div>
        <p className="ml-2 text-muted-foreground">Loading...</p>
      </div>
    );
  }

  if (requireAuth && status === 'unauthenticated') {
    return null;
  }

  if (requireAdmin && session?.user?.role !== 'admin') {
    return null;
  }

  return <>{children}</>;
}