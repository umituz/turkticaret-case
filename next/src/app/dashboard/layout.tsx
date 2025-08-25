'use client';

import { AuthGuard } from '@/components/guards/AuthGuard';
import { AdminLayout } from '@/components/admin/AdminLayout';

export default function DashboardLayout({
  children,
}: {
  children: React.ReactNode;
}) {
  return (
    <AuthGuard requireAuth requireAdmin>
      <AdminLayout>{children}</AdminLayout>
    </AuthGuard>
  );
}