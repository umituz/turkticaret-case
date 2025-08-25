'use client';

import { useState } from 'react';
import { AdminSidebar } from './AdminSidebar';
import { AdminHeader } from './AdminHeader';
import { AdminFooter } from './AdminFooter';
import { cn } from '@/lib/utils';

interface AdminLayoutProps {
  children: React.ReactNode;
  className?: string;
}

export const AdminLayout = ({ children, className }: AdminLayoutProps) => {
  const [isMobileMenuOpen, setIsMobileMenuOpen] = useState(false);

  return (
    <div className="min-h-screen bg-background">
      <div className="flex h-screen">
        {}
        {isMobileMenuOpen && (
          <div 
            className="fixed inset-0 bg-black/20 backdrop-blur-sm z-40 md:hidden"
            onClick={() => setIsMobileMenuOpen(false)}
          />
        )}

        {}
        <aside className={cn(
          "fixed left-0 top-0 z-50 h-full transform transition-transform duration-300 ease-in-out md:relative md:translate-x-0",
          isMobileMenuOpen ? "translate-x-0" : "-translate-x-full"
        )}>
          <AdminSidebar />
        </aside>

        {}
        <div className="flex flex-1 flex-col overflow-hidden">
          {}
          <AdminHeader onMobileMenuToggle={() => setIsMobileMenuOpen(!isMobileMenuOpen)} />
          
          {}
          <main className="flex-1 overflow-y-auto">
            <div className={cn("container mx-auto p-6", className)}>
              {children}
            </div>
          </main>
          
          {}
          <AdminFooter />
        </div>
      </div>
    </div>
  );
};