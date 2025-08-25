'use client';

import { useState, useEffect } from 'react';
import Link from 'next/link';
import { useRouter } from 'next/navigation';
import { Search, User, Menu, LogOut, Shield, Settings, Package, MapPin, ChevronDown } from 'lucide-react';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Badge } from '@/components/ui/badge';
import { Sheet, SheetContent, SheetTrigger } from '@/components/ui/sheet';
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuLabel,
  DropdownMenuSeparator,
  DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { useAuth } from '@/hooks/useAuth';
import { CartSidebar } from '@/components/cart/CartSidebar';
import { BRAND } from '@/constants/branding';

interface HeaderProps {
  onSearchChange?: (query: string) => void;
}

export function Header({ onSearchChange }: HeaderProps) {
  const [searchQuery, setSearchQuery] = useState('');
  const [isMobileMenuOpen, setIsMobileMenuOpen] = useState(false);
  const [mounted, setMounted] = useState(false);
  const { user, logout, isAuthenticated } = useAuth();
  const router = useRouter();

  useEffect(() => {
    setMounted(true);
  }, []);

  const handleSearch = (e: React.FormEvent) => {
    e.preventDefault();
    onSearchChange?.(searchQuery);
  };

  const handleSearchChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    const query = e.target.value;
    setSearchQuery(query);
    onSearchChange?.(query);
  };

  const handleLogout = async () => {
    await logout('/');
  };


  return (
    <header className="sticky top-0 z-50 w-full border-b bg-background/95 backdrop-blur supports-[backdrop-filter]:bg-background/60">
      <div className="container mx-auto px-4">
        <div className="flex h-16 items-center justify-between">
          {}
          <Link href="/" className="flex items-center space-x-2">
            <div className={BRAND.logo.classes}>
              <span className={BRAND.logo.textClasses}>{BRAND.logo.letter}</span>
            </div>
            <span className={`text-xl font-bold ${BRAND.colors.gradient} bg-clip-text text-transparent`}>
              {BRAND.name}
            </span>
          </Link>


          {}
          <div className="hidden md:flex flex-1 max-w-md mx-6">
            <form onSubmit={handleSearch} className="flex w-full">
              <div className="relative flex-1">
                <Search className="absolute left-3 top-1/2 transform -translate-y-1/2 text-muted-foreground w-4 h-4" />
                <Input
                  type="search"
                  placeholder="Search products..."
                  value={searchQuery}
                  onChange={handleSearchChange}
                  className="pl-10 pr-4"
                />
              </div>
            </form>
          </div>

          {}
          <div className="flex items-center space-x-2">
            {}
            <Sheet key="mobile-search">
              <SheetTrigger asChild>
                <Button variant="ghost" size="icon" className="md:hidden">
                  <Search className="w-5 h-5" />
                </Button>
              </SheetTrigger>
              <SheetContent side="top" className="h-20">
                <form onSubmit={handleSearch} className="flex w-full mt-4">
                  <div className="relative flex-1">
                    <Search className="absolute left-3 top-1/2 transform -translate-y-1/2 text-muted-foreground w-4 h-4" />
                    <Input
                      type="search"
                      placeholder="Search products..."
                      value={searchQuery}
                      onChange={handleSearchChange}
                      className="pl-10 pr-4"
                    />
                  </div>
                </form>
              </SheetContent>
            </Sheet>

            {}
            <CartSidebar />

            {}
            {mounted && isAuthenticated && user ? (
              <div className="flex items-center space-x-2">
                {user.role === 'admin' && (
                  <Button variant="ghost" size="icon" asChild>
                    <Link href="/dashboard">
                      <Shield className="w-5 h-5" />
                      <span className="sr-only">Admin Dashboard</span>
                    </Link>
                  </Button>
                )}
                
                {}
                <DropdownMenu>
                  <DropdownMenuTrigger asChild>
                    <Button variant="ghost" className="hidden md:flex items-center space-x-2 h-8 px-2">
                      <span className="text-sm font-medium">{user.name}</span>
                      {user.role === 'admin' && (
                        <Badge variant="secondary" className="text-xs bg-red-100 text-red-800">
                          Admin
                        </Badge>
                      )}
                      <ChevronDown className="h-3 w-3" />
                    </Button>
                  </DropdownMenuTrigger>
                  <DropdownMenuContent align="end" className="w-56">
                    <DropdownMenuLabel>My Account</DropdownMenuLabel>
                    <DropdownMenuSeparator />
                    <DropdownMenuItem asChild>
                      <Link href="/account/profile" className="flex items-center space-x-2">
                        <User className="h-4 w-4" />
                        <span>Profile</span>
                      </Link>
                    </DropdownMenuItem>
                    <DropdownMenuItem asChild>
                      <Link href="/account/orders" className="flex items-center space-x-2">
                        <Package className="h-4 w-4" />
                        <span>My Orders</span>
                      </Link>
                    </DropdownMenuItem>
                    <DropdownMenuItem asChild>
                      <Link href="/account/addresses" className="flex items-center space-x-2">
                        <MapPin className="h-4 w-4" />
                        <span>Addresses</span>
                      </Link>
                    </DropdownMenuItem>
                    <DropdownMenuItem asChild>
                      <Link href="/account/settings" className="flex items-center space-x-2">
                        <Settings className="h-4 w-4" />
                        <span>Settings</span>
                      </Link>
                    </DropdownMenuItem>
                    <DropdownMenuSeparator />
                    <DropdownMenuItem onClick={handleLogout} className="flex items-center space-x-2 text-red-600">
                      <LogOut className="h-4 w-4" />
                      <span>Logout</span>
                    </DropdownMenuItem>
                  </DropdownMenuContent>
                </DropdownMenu>
                
                {}
                <Button variant="ghost" size="icon" asChild className="md:hidden">
                  <Link href="/account">
                    <User className="w-5 h-5" />
                    <span className="sr-only">My Account</span>
                  </Link>
                </Button>
              </div>
            ) : mounted ? (
              <Button variant="ghost" size="icon" asChild>
                <Link href="/auth/login">
                  <User className="w-5 h-5" />
                  <span className="sr-only">My Account</span>
                </Link>
              </Button>
            ) : null}

            {}
            <Sheet key="mobile-menu" open={isMobileMenuOpen} onOpenChange={setIsMobileMenuOpen}>
              <SheetTrigger asChild>
                <Button variant="ghost" size="icon" className="md:hidden">
                  <Menu className="w-5 h-5" />
                </Button>
              </SheetTrigger>
              <SheetContent side="right" className="w-64">
                <div className="flex flex-col space-y-4 mt-8">
                  {}
                  {mounted && isAuthenticated && user ? (
                    <div className="border-t pt-4 space-y-4">
                      <div className="flex items-center space-x-2">
                        <span className="font-medium">{user.name}</span>
                        {user.role === 'admin' && (
                          <Badge variant="secondary" className="text-xs bg-red-100 text-red-800">
                            Admin
                          </Badge>
                        )}
                      </div>
                      
                      <Link
                        href="/account/profile"
                        className="flex items-center space-x-2 text-lg font-medium transition-colors hover:text-red-600"
                        onClick={() => setIsMobileMenuOpen(false)}
                      >
                        <User className="w-5 h-5" />
                        <span>Profile</span>
                      </Link>
                      
                      <Link
                        href="/account/orders"
                        className="flex items-center space-x-2 text-lg font-medium transition-colors hover:text-red-600"
                        onClick={() => setIsMobileMenuOpen(false)}
                      >
                        <Package className="w-5 h-5" />
                        <span>My Orders</span>
                      </Link>

                      <Link
                        href="/account/settings"
                        className="flex items-center space-x-2 text-lg font-medium transition-colors hover:text-red-600"
                        onClick={() => setIsMobileMenuOpen(false)}
                      >
                        <Settings className="w-5 h-5" />
                        <span>Settings</span>
                      </Link>
                      
                      {user.role === 'admin' && (
                        <Link
                          href="/dashboard"
                          className="flex items-center space-x-2 text-lg font-medium transition-colors hover:text-red-600"
                          onClick={() => setIsMobileMenuOpen(false)}
                        >
                          <Shield className="w-5 h-5" />
                          <span>Admin Dashboard</span>
                        </Link>
                      )}
                      
                      <button
                        onClick={() => {
                          handleLogout();
                          setIsMobileMenuOpen(false);
                        }}
                        className="flex items-center space-x-2 text-lg font-medium transition-colors hover:text-red-600"
                      >
                        <LogOut className="w-5 h-5" />
                        <span>Logout</span>
                      </button>
                    </div>
                  ) : mounted ? (
                    <Link
                      href="/auth/login"
                      className="flex items-center space-x-2 text-lg font-medium transition-colors hover:text-red-600 border-t pt-4"
                      onClick={() => setIsMobileMenuOpen(false)}
                    >
                      <User className="w-5 h-5" />
                      <span>Login</span>
                    </Link>
                  ) : null}
                </div>
              </SheetContent>
            </Sheet>
          </div>
        </div>
      </div>
    </header>
  );
}