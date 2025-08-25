'use client';

import { useState, useEffect } from 'react';
import Link from 'next/link';
import { usePathname, useRouter } from 'next/navigation';
import { Button } from '@/components/ui/button';
import { cn } from '@/lib/utils';
import { useAuth } from '@/hooks/useAuth';
import {
  User,
  MapPin,
  Package,
  Settings,
  LogOut,
  Menu,
  X
} from 'lucide-react';

const navigation = [
  {
    name: 'Profile Overview',
    href: '/account',
    icon: User,
    description: 'View your profile summary and stats'
  },
  {
    name: 'Edit Profile',
    href: '/account/profile',
    icon: User,
    description: 'Update your personal information'
  },
  {
    name: 'Order History',
    href: '/account/orders',
    icon: Package,
    description: 'View your past orders and tracking'
  },
  {
    name: 'Addresses',
    href: '/account/addresses',
    icon: MapPin,
    description: 'Manage shipping and billing addresses'
  },
  {
    name: 'Settings',
    href: '/account/settings',
    icon: Settings,
    description: 'Account preferences, notifications and security'
  }
];

interface ProfileSidebarProps {
  className?: string;
}

export const ProfileSidebar = ({ className }: ProfileSidebarProps) => {
  const pathname = usePathname();
  const router = useRouter();
  const { logout } = useAuth();
  const [isMobileMenuOpen, setIsMobileMenuOpen] = useState(false);
  const [mounted, setMounted] = useState(false);

  useEffect(() => {
    setMounted(true);
  }, []);

  const handleLogout = async () => {
    await logout('/');
  };

  if (!mounted) {
    return null; 
  }

  return (
    <>
      {}
      <div className="lg:hidden">
        <Button
          variant="outline"
          size="icon"
          onClick={() => setIsMobileMenuOpen(true)}
          className="mb-4"
        >
          <Menu className="h-4 w-4" />
        </Button>
      </div>

      {}
      {isMobileMenuOpen && (
        <div className="fixed inset-0 z-50 lg:hidden">
          <div className="fixed inset-0 bg-black/20" onClick={() => setIsMobileMenuOpen(false)} />
          <div className="fixed left-0 top-0 h-full w-80 bg-background shadow-lg">
            <div className="flex items-center justify-between p-4 border-b">
              <h2 className="text-lg font-semibold">Account Menu</h2>
              <Button
                variant="ghost"
                size="icon"
                onClick={() => setIsMobileMenuOpen(false)}
              >
                <X className="h-4 w-4" />
              </Button>
            </div>
            <nav className="p-4">
              <ul className="space-y-2">
                {navigation.map((item) => {
                  const isActive = pathname === item.href;
                  return (
                    <li key={item.name}>
                      <Link
                        href={item.href}
                        onClick={() => setIsMobileMenuOpen(false)}
                        className={cn(
                          'flex items-center space-x-3 rounded-lg px-3 py-3 text-sm font-medium transition-colors hover:bg-muted',
                          isActive 
                            ? 'bg-red-50 text-red-700 border-r-2 border-red-600' 
                            : 'text-muted-foreground hover:text-foreground'
                        )}
                      >
                        <item.icon className="h-4 w-4" />
                        <div className="flex-1">
                          <div className="font-medium">{item.name}</div>
                          <div className="text-xs text-muted-foreground">
                            {item.description}
                          </div>
                        </div>
                      </Link>
                    </li>
                  );
                })}
              </ul>
              
              <div className="mt-6 pt-6 border-t">
                <button
                  onClick={() => {
                    setIsMobileMenuOpen(false);
                    handleLogout();
                  }}
                  className="flex items-center space-x-3 rounded-lg px-3 py-3 text-sm font-medium text-red-600 transition-colors hover:bg-red-50 w-full text-left"
                >
                  <LogOut className="h-4 w-4" />
                  <span>Sign Out</span>
                </button>
              </div>
            </nav>
          </div>
        </div>
      )}

      {}
      <div className={cn("hidden lg:block", className)}>
        <div className="bg-background border rounded-lg p-4">
          <div className="mb-6">
            <h2 className="text-lg font-semibold mb-1">My Account</h2>
            <p className="text-sm text-muted-foreground">
              Manage your profile and preferences
            </p>
          </div>

          <nav>
            <ul className="space-y-2">
              {navigation.map((item) => {
                const isActive = pathname === item.href;
                return (
                  <li key={item.name}>
                    <Link
                      href={item.href}
                      className={cn(
                        'flex items-center space-x-3 rounded-lg px-3 py-3 text-sm font-medium transition-colors hover:bg-muted',
                        isActive 
                          ? 'bg-red-50 text-red-700 border-r-2 border-red-600' 
                          : 'text-muted-foreground hover:text-foreground'
                      )}
                    >
                      <item.icon className="h-4 w-4" />
                      <div className="flex-1">
                        <div className="font-medium">{item.name}</div>
                        <div className="text-xs text-muted-foreground">
                          {item.description}
                        </div>
                      </div>
                    </Link>
                  </li>
                );
              })}
            </ul>
            
            <div className="mt-6 pt-6 border-t">
              <button
                onClick={handleLogout}
                className="flex items-center space-x-3 rounded-lg px-3 py-3 text-sm font-medium text-red-600 transition-colors hover:bg-red-50 w-full text-left"
              >
                <LogOut className="h-4 w-4" />
                <span>Sign Out</span>
              </button>
            </div>
          </nav>
        </div>
      </div>
    </>
  );
};