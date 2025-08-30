'use client';

import Link from 'next/link';
import { Heart } from 'lucide-react';

export const AdminFooter = () => {
  return (
    <footer className="border-t border-border bg-background">
      <div className="flex items-center justify-between py-4 px-6 text-sm text-muted-foreground">
        <div className="flex items-center space-x-4">
          <p>© 2024 Ecommerce Admin Panel. All rights reserved.</p>
        </div>
        
        <div className="flex items-center space-x-4">
          <span className="flex items-center">
            Made with <Heart className="h-3 w-3 mx-1 text-red-600" /> for administrators
          </span>
          <span>•</span>
          <Link href="/dashboard/support" className="hover:text-foreground transition-colors">
            Support
          </Link>
          <span>•</span>
          <Link href="/dashboard/docs" className="hover:text-foreground transition-colors">
            Documentation
          </Link>
          <span>•</span>
          <span>v1.0.0</span>
        </div>
      </div>
    </footer>
  );
};