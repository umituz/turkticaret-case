'use client';

import { useState } from 'react';
import Link from 'next/link';
import { usePathname } from 'next/navigation';
import { cn } from '@/lib/utils';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import {
  LayoutDashboard,
  Package,
  Tags,
  Settings,
  ChevronLeft,
  ChevronRight,
  Home,
  ShoppingCart,
} from 'lucide-react';

interface NavItem {
  title: string;
  href: string;
  icon: React.ComponentType<{ className?: string }>;
  badge?: string;
  children?: NavItem[];
}

const navItems: NavItem[] = [
  {
    title: 'Dashboard',
    href: '/dashboard',
    icon: LayoutDashboard
  },
  {
    title: 'Orders',
    href: '/dashboard/orders',
    icon: ShoppingCart
  },
  {
    title: 'Categories',
    href: '/dashboard/categories',
    icon: Tags
  },
  {
    title: 'Products',
    href: '/dashboard/products',
    icon: Package
  },
  {
    title: 'Settings',
    href: '/dashboard/settings',
    icon: Settings,
    badge: 'Coming Soon'
  },
];

interface AdminSidebarProps {
  className?: string;
}

export const AdminSidebar = ({ className }: AdminSidebarProps) => {
  const [collapsed, setCollapsed] = useState(false);
  const pathname = usePathname();

  return (
    <div className={cn(
      "flex flex-col bg-card border-r border-border h-full transition-all duration-300",
      collapsed ? "w-16" : "w-64",
      className
    )}>
      {}
      <div className="flex items-center justify-between p-4 border-b border-border">
        {!collapsed && (
          <div className="flex items-center space-x-2">
            <div className="h-8 w-8 rounded-lg bg-gradient-to-br from-red-600 to-red-700 flex items-center justify-center">
              <span className="text-white font-bold text-lg">T</span>
            </div>
            <div>
              <h2 className="text-lg font-bold bg-gradient-to-r from-red-600 to-red-700 bg-clip-text text-transparent">
                Admin Panel
              </h2>
            </div>
          </div>
        )}
        
        <Button
          variant="ghost"
          size="icon"
          className="h-8 w-8"
          onClick={() => setCollapsed(!collapsed)}
        >
          {collapsed ? (
            <ChevronRight className="h-4 w-4" />
          ) : (
            <ChevronLeft className="h-4 w-4" />
          )}
        </Button>
      </div>

      {}
      <div className="p-3 border-b border-border">
        <Link href="/">
          <Button variant="ghost" className={cn(
            "w-full justify-start text-muted-foreground hover:text-foreground hover:bg-muted",
            collapsed && "justify-center px-2"
          )}>
            <Home className="h-4 w-4" />
            {!collapsed && <span className="ml-3">Back to Site</span>}
          </Button>
        </Link>
      </div>

      {}
      <nav className="flex-1 overflow-y-auto py-4">
        <div className="space-y-1 px-3">
          {navItems.map((item) => {
            const Icon = item.icon;
            const isActive = pathname === item.href || (item.href !== '/dashboard' && pathname.startsWith(item.href + '/'));
            
            return (
              <Link key={item.href} href={item.href}>
                <Button
                  variant={isActive ? "secondary" : "ghost"}
                  className={cn(
                    "w-full justify-start transition-all",
                    collapsed ? "justify-center px-2" : "px-3",
                    isActive && "bg-red-50 text-red-700 hover:bg-red-100"
                  )}
                >
                  <Icon className="h-4 w-4" />
                  {!collapsed && (
                    <>
                      <span className="ml-3 flex-1 text-left">{item.title}</span>
                      {item.badge && (
                        <Badge variant="secondary" className="ml-auto text-xs bg-red-100 text-red-800">
                          {item.badge}
                        </Badge>
                      )}
                    </>
                  )}
                </Button>
              </Link>
            );
          })}
        </div>
      </nav>

      {}
      <div className="p-4 border-t border-border">
        {!collapsed && (
          <div className="text-xs text-muted-foreground text-center">
            <p>Ecommerce Admin v1.0</p>
            <p className="mt-1">© 2024 All rights reserved</p>
          </div>
        )}
      </div>
    </div>
  );
};