'use client';

import { useState, useEffect, useCallback, useRef, Suspense } from 'react';
import { useAuth } from '@/hooks/useAuth';
import { useRouter } from 'next/navigation';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { ProfileSidebar } from '@/components/profile/ProfileSidebar';
import { useToast } from '@/hooks/use-toast';

import { getUserStats, UserStats } from '@/services/userService';
import { formatDate } from '@/utils/common';
import {
  User,
  Calendar,
  Package,
  DollarSign,
  MapPin,
  Mail,
  Edit,
  CheckCircle
} from 'lucide-react';

function AccountOverviewPageContent() {
  
  const { user, isLoading } = useAuth();
  const router = useRouter();
  const { toast } = useToast();
  const toastRef = useRef(toast);
  const [stats, setStats] = useState<UserStats | null>(null);
  const [loadingStats, setLoadingStats] = useState(true);
  const [mounted, setMounted] = useState(false);
  
  
  useEffect(() => {
    setMounted(true);
  }, []);

  
  useEffect(() => {
    toastRef.current = toast;
  }, [toast]);

  const loadUserStats = useCallback(async () => {
    if (!user) return;
    
    try {
      setLoadingStats(true);
      const userStats = await getUserStats();
      setStats(userStats);
    } catch (error) {
      console.error('Failed to load user stats:', error);
      toastRef.current({
        title: 'Error!',
        description: 'Failed to load account statistics.',
        variant: 'destructive',
      });
      setStats(null);
    } finally {
      setLoadingStats(false);
    }
  }, [user]);

  useEffect(() => {
    if (!isLoading && !user) {
      router.push('/auth/login');
      return;
    }

    if (user?.role === 'admin') {
      router.push('/dashboard');
      return;
    }

    if (user) {
      loadUserStats();
    }
  }, [user, isLoading, router, loadUserStats]);



  
  if (!mounted || isLoading) {
    return (
      <div className="container mx-auto px-4 py-8">
        <div className="flex items-center justify-center py-12">
          <div className="animate-spin rounded-full h-8 w-8 border-b-2 border-red-600"></div>
          <p className="ml-2 text-muted-foreground">Loading account...</p>
        </div>
      </div>
    );
  }

  if (!user) {
    return null; 
  }

  return (
    <div className="container mx-auto px-4 py-8">
      <div className="grid gap-8 lg:grid-cols-4">
        {}
        <div className="lg:col-span-1">
          <ProfileSidebar />
        </div>

        {}
        <div className="lg:col-span-3 space-y-8">
          {}
          <div className="mb-8">
            <h1 className="text-3xl font-bold tracking-tight mb-2">
              Welcome back, {user.name}!
            </h1>
            <p className="text-muted-foreground">
              Here&apos;s an overview of your account activity and information.
            </p>
          </div>

          {}
          {loadingStats ? (
            <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
              {[...Array(4)].map((_, i) => (
                <Card key={i}>
                  <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                    <div className="h-4 bg-muted animate-pulse rounded w-20"></div>
                    <div className="h-4 w-4 bg-muted animate-pulse rounded"></div>
                  </CardHeader>
                  <CardContent>
                    <div className="h-8 bg-muted animate-pulse rounded w-16"></div>
                  </CardContent>
                </Card>
              ))}
            </div>
          ) : stats ? (
            <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
              <Card>
                <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                  <CardTitle className="text-sm font-medium">Total Orders</CardTitle>
                  <Package className="h-4 w-4 text-muted-foreground" />
                </CardHeader>
                <CardContent>
                  <div className="text-2xl font-bold">{stats.totalOrders}</div>
                </CardContent>
              </Card>

              <Card>
                <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                  <CardTitle className="text-sm font-medium">Total Spent</CardTitle>
                  <DollarSign className="h-4 w-4 text-muted-foreground" />
                </CardHeader>
                <CardContent>
                  <div className="text-2xl font-bold">-</div>
                </CardContent>
              </Card>

              <Card>
                <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                  <CardTitle className="text-sm font-medium">Average Order</CardTitle>
                  <DollarSign className="h-4 w-4 text-muted-foreground" />
                </CardHeader>
                <CardContent>
                  <div className="text-2xl font-bold">-</div>
                </CardContent>
              </Card>

              <Card>
                <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                  <CardTitle className="text-sm font-medium">Member Since</CardTitle>
                  <Calendar className="h-4 w-4 text-muted-foreground" />
                </CardHeader>
                <CardContent>
                  <div className="text-lg font-bold">
                    {formatDate(stats.memberSince).split(' ')[1]} {formatDate(stats.memberSince).split(' ')[2]}
                  </div>
                </CardContent>
              </Card>
            </div>
          ) : (
            <div className="text-center py-8 text-muted-foreground">
              <Package className="mx-auto h-12 w-12 mb-4" />
              <p>Unable to load account statistics.</p>
            </div>
          )}

          {}
          <Card>
            <CardHeader className="flex flex-row items-center justify-between">
              <div>
                <CardTitle>Profile Information</CardTitle>
                <CardDescription>
                  Your personal details and contact information
                </CardDescription>
              </div>
              <Button variant="outline" asChild>
                <a href="/account/profile">
                  <Edit className="mr-2 h-4 w-4" />
                  Edit Profile
                </a>
              </Button>
            </CardHeader>
            <CardContent className="space-y-6">
              <div className="flex items-center space-x-4">
                <div className="w-16 h-16 rounded-full bg-muted flex items-center justify-center">
                  <User className="h-8 w-8 text-muted-foreground" />
                </div>
                <div>
                  <h3 className="text-xl font-semibold">{user.name}</h3>
                  <p className="text-muted-foreground">Welcome to TurkTicaret</p>
                </div>
              </div>

              <div className="grid gap-6 md:grid-cols-2">
                <div className="space-y-4">
                  <div className="flex items-center space-x-3">
                    <Mail className="h-4 w-4 text-muted-foreground" />
                    <div>
                      <p className="text-sm font-medium">Email</p>
                      <div className="flex items-center space-x-2">
                        <p className="text-sm text-muted-foreground">{user.email}</p>
                        <Badge variant="secondary" className="bg-green-100 text-green-800">
                          <CheckCircle className="mr-1 h-3 w-3" />
                          Verified
                        </Badge>
                      </div>
                    </div>
                  </div>

                </div>

                <div className="space-y-4">
                  <div className="flex items-center space-x-3">
                    <MapPin className="h-4 w-4 text-muted-foreground" />
                    <div>
                      <p className="text-sm font-medium">Default Address</p>
                      <p className="text-sm text-muted-foreground">
                        No default address set
                      </p>
                    </div>
                  </div>

                  <div className="flex items-center space-x-3">
                    <Calendar className="h-4 w-4 text-muted-foreground" />
                    <div>
                      <p className="text-sm font-medium">Member Since</p>
                      <p className="text-sm text-muted-foreground">
                        Recently joined
                      </p>
                    </div>
                  </div>
                </div>
              </div>
            </CardContent>
          </Card>

          {}
          {stats?.lastOrder && (
            <Card>
              <CardHeader className="flex flex-row items-center justify-between">
                <div>
                  <CardTitle>Recent Order</CardTitle>
                  <CardDescription>
                    Your most recent purchase
                  </CardDescription>
                </div>
                <Button variant="outline" onClick={() => router.push('/account/orders')}>
                  View All Orders
                </Button>
              </CardHeader>
              <CardContent>
                <div className="flex items-center justify-between p-4 border rounded-lg">
                  <div>
                    <p className="font-medium">Order #{stats.lastOrder.orderNumber}</p>
                    <p className="text-sm text-muted-foreground">
                      {formatDate(stats.lastOrder.createdAt)}
                    </p>
                  </div>
                  <div className="text-right">
                    <p className="font-semibold">-</p>
                    <Badge 
                      variant="secondary" 
                      className={
                        stats.lastOrder.status === 'delivered' ? 'bg-green-100 text-green-800' :
                        stats.lastOrder.status === 'shipped' ? 'bg-blue-100 text-blue-800' :
                        stats.lastOrder.status === 'processing' ? 'bg-yellow-100 text-yellow-800' :
                        'bg-gray-100 text-gray-800'
                      }
                    >
                      {stats.lastOrder.status.charAt(0).toUpperCase() + stats.lastOrder.status.slice(1)}
                    </Badge>
                  </div>
                </div>
              </CardContent>
            </Card>
          )}

          {}
          <Card>
            <CardHeader>
              <CardTitle>Quick Actions</CardTitle>
              <CardDescription>
                Common account tasks and settings
              </CardDescription>
            </CardHeader>
            <CardContent>
              <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                <Button variant="outline" className="h-auto p-4" asChild>
                  <a href="/account/profile" className="flex flex-col items-center space-y-2">
                    <User className="h-6 w-6" />
                    <div className="text-center">
                      <p className="font-medium">Edit Profile</p>
                      <p className="text-xs text-muted-foreground">Update personal info</p>
                    </div>
                  </a>
                </Button>

                <Button variant="outline" className="h-auto p-4" asChild>
                  <a href="/account/addresses" className="flex flex-col items-center space-y-2">
                    <MapPin className="h-6 w-6" />
                    <div className="text-center">
                      <p className="font-medium">Addresses</p>
                      <p className="text-xs text-muted-foreground">Manage addresses</p>
                    </div>
                  </a>
                </Button>

                <Button variant="outline" className="h-auto p-4" asChild>
                  <a href="/account/security" className="flex flex-col items-center space-y-2">
                    <Package className="h-6 w-6" />
                    <div className="text-center">
                      <p className="font-medium">Security</p>
                      <p className="text-xs text-muted-foreground">Password & security</p>
                    </div>
                  </a>
                </Button>
              </div>
            </CardContent>
          </Card>
        </div>
      </div>
    </div>
  );
}

export default function AccountOverviewPage() {
  return (
    <Suspense fallback={
      <div className="container mx-auto px-4 py-8">
        <div className="flex items-center justify-center py-12">
          <div className="animate-spin rounded-full h-8 w-8 border-b-2 border-red-600"></div>
          <p className="ml-2 text-muted-foreground">Loading account...</p>
        </div>
      </div>
    }>
      <AccountOverviewPageContent />
    </Suspense>
  );
}