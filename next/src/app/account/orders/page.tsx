'use client';

import { useState, useEffect, useCallback, useRef, Suspense } from 'react';
import { useAuth } from '@/hooks/useAuth';
import { useRouter } from 'next/navigation';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Input } from '@/components/ui/input';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select';
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuLabel,
  DropdownMenuSeparator,
  DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { ProfileSidebar } from '@/components/profile/ProfileSidebar';
import { useToast } from '@/hooks/use-toast';

import { Order } from '@/types/user';
import { getOrders } from '@/services/orderService';
import { transformOrderFiltersForAPI } from '@/lib/order-filter-utils';
import {
  Package,
  Search,
  MoreHorizontal,
  Eye,
  Download,
  RefreshCw,
  Calendar,
  DollarSign,
  Truck,
  CheckCircle,
  Clock
} from 'lucide-react';

function OrdersPageContent() {
  
  const { user, isLoading } = useAuth();
  const router = useRouter();
  const { toast } = useToast();
  const toastRef = useRef(toast);
  const [orders, setOrders] = useState<Order[]>([]);
  const [loading, setLoading] = useState(true);
  const [searchTerm, setSearchTerm] = useState('');
  const [statusFilter, setStatusFilter] = useState<string>('all');
  const [dateFilter, setDateFilter] = useState<string>('all');
  const [orderStats, setOrderStats] = useState({ totalSpent: 0, monthlyCount: 0 });
  const [mounted, setMounted] = useState(false);
  
  
  useEffect(() => {
    setMounted(true);
  }, []);

  
  useEffect(() => {
    toastRef.current = toast;
  }, [toast]);

  const loadOrders = useCallback(async () => {
    if (!user) {
      router.push('/');
      return;
    }
    
    try {
      setLoading(true);
      
      const apiFilters = transformOrderFiltersForAPI({
        search: searchTerm || undefined,
        status: statusFilter,
        dateFilter: dateFilter
      });
      
      const result = await getOrders(apiFilters);
      setOrders(result.orders);
      
      
      const totalSpent = result.orders.reduce((sum, order) => sum + order.total.raw, 0);
      const now = new Date();
      const monthlyCount = result.orders.filter(order => {
        const orderDate = new Date(order.createdAt);
        return orderDate.getMonth() === now.getMonth() && 
               orderDate.getFullYear() === now.getFullYear();
      }).length;
      
      setOrderStats({ totalSpent, monthlyCount });
    } catch (error) {
      console.error('Failed to load orders:', error);
      toastRef.current({
        title: 'Error!',
        description: 'Failed to load orders. Please try again.',
        variant: 'destructive',
      });
      setOrders([]);
    } finally {
      setLoading(false);
    }
  }, [user, searchTerm, statusFilter, dateFilter, router]);

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
      loadOrders();
    }
  }, [user, isLoading, router, loadOrders]);

  const getStatusIcon = (status: string) => {
    switch (status) {
      case 'delivered':
        return <CheckCircle className="h-4 w-4" />;
      case 'shipped':
        return <Truck className="h-4 w-4" />;
      case 'processing':
        return <Package className="h-4 w-4" />;
      default:
        return <Clock className="h-4 w-4" />;
    }
  };

  const getStatusColor = (status: string) => {
    switch (status) {
      case 'delivered':
        return 'bg-green-100 text-green-800';
      case 'shipped':
        return 'bg-blue-100 text-blue-800';
      case 'processing':
        return 'bg-yellow-100 text-yellow-800';
      case 'cancelled':
        return 'bg-red-100 text-red-800';
      default:
        return 'bg-gray-100 text-gray-800';
    }
  };

  const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleDateString('en-US', {
      year: 'numeric',
      month: 'short',
      day: 'numeric'
    });
  };


  const handleViewOrder = (orderUuid: string) => {
    router.push(`/account/orders/${orderUuid}`);
  };

  const handleDownloadInvoice = (orderNumber: string) => {
    toastRef.current({
      title: 'Download Started',
      description: `Invoice for order ${orderNumber} is being prepared.`,
    });
  };

  const handleReorder = () => {
    toastRef.current({
      title: 'Added to Cart',
      description: 'Items from this order have been added to your cart.',
    });
  };

  
  if (!mounted || isLoading) {
    return (
      <div className="container mx-auto px-4 py-8">
        <div className="flex items-center justify-center py-12">
          <div className="animate-spin rounded-full h-8 w-8 border-b-2 border-red-600"></div>
          <p className="ml-2 text-muted-foreground">Loading orders...</p>
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
            <h1 className="text-3xl font-bold tracking-tight mb-2">Order History</h1>
            <p className="text-muted-foreground">
              Track your orders and view purchase history
            </p>
          </div>

          {}
          <div className="grid gap-4 md:grid-cols-3">
            <Card>
              <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                <CardTitle className="text-sm font-medium">Total Orders</CardTitle>
                <Package className="h-4 w-4 text-muted-foreground" />
              </CardHeader>
              <CardContent>
                <div className="text-2xl font-bold">{orders.length}</div>
              </CardContent>
            </Card>

            <Card>
              <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                <CardTitle className="text-sm font-medium">Total Spent</CardTitle>
                <DollarSign className="h-4 w-4 text-muted-foreground" />
              </CardHeader>
              <CardContent>
                <div className="text-2xl font-bold">
                  -
                </div>
              </CardContent>
            </Card>

            <Card>
              <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                <CardTitle className="text-sm font-medium">This Month</CardTitle>
                <Calendar className="h-4 w-4 text-muted-foreground" />
              </CardHeader>
              <CardContent>
                <div className="text-2xl font-bold">
                  {orderStats.monthlyCount}
                </div>
              </CardContent>
            </Card>
          </div>

          {}
          <Card>
            <CardHeader>
              <CardTitle>Order List</CardTitle>
              <CardDescription>
                View and manage all your orders
              </CardDescription>
            </CardHeader>
            <CardContent>
              <div className="flex flex-col sm:flex-row gap-4 mb-6">
                <div className="flex-1 relative">
                  <Search className="absolute left-3 top-1/2 transform -translate-y-1/2 text-muted-foreground h-4 w-4" />
                  <Input
                    placeholder="Search orders or products..."
                    value={searchTerm}
                    onChange={(e) => setSearchTerm(e.target.value)}
                    className="pl-10"
                  />
                </div>
                
                <Select value={statusFilter} onValueChange={setStatusFilter}>
                  <SelectTrigger className="w-full sm:w-48">
                    <SelectValue placeholder="Filter by status" />
                  </SelectTrigger>
                  <SelectContent>
                    <SelectItem value="all">All Status</SelectItem>
                    <SelectItem value="pending">Pending</SelectItem>
                    <SelectItem value="processing">Processing</SelectItem>
                    <SelectItem value="shipped">Shipped</SelectItem>
                    <SelectItem value="delivered">Delivered</SelectItem>
                    <SelectItem value="cancelled">Cancelled</SelectItem>
                  </SelectContent>
                </Select>

                <Select value={dateFilter} onValueChange={setDateFilter}>
                  <SelectTrigger className="w-full sm:w-48">
                    <SelectValue placeholder="Filter by date" />
                  </SelectTrigger>
                  <SelectContent>
                    <SelectItem value="all">All Time</SelectItem>
                    <SelectItem value="week">Last Week</SelectItem>
                    <SelectItem value="month">Last Month</SelectItem>
                    <SelectItem value="quarter">Last 3 Months</SelectItem>
                    <SelectItem value="year">Last Year</SelectItem>
                  </SelectContent>
                </Select>
              </div>

              {}
              {loading ? (
                <div className="text-center py-8">
                  <div className="animate-spin rounded-full h-8 w-8 border-b-2 border-red-600 mx-auto"></div>
                  <p className="text-muted-foreground mt-2">Loading orders...</p>
                </div>
              ) : orders.length === 0 ? (
                <div className="text-center py-8">
                  <Package className="mx-auto h-12 w-12 text-muted-foreground" />
                  <h3 className="mt-2 text-sm font-medium">No orders found</h3>
                  <p className="mt-1 text-sm text-muted-foreground">
                    {searchTerm || statusFilter !== 'all' || dateFilter !== 'all' 
                      ? 'Try adjusting your filters.' 
                      : 'You haven\'t placed any orders yet.'
                    }
                  </p>
                </div>
              ) : (
                <div className="space-y-4">
                  {orders.map((order, index) => (
                    <Card key={`order-${order.orderNumber}-${index}`} className="p-6">
                      <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                        <div className="space-y-1">
                          <div className="flex items-center space-x-2">
                            <p className="font-semibold">Order #{order.orderNumber}</p>
                            <Badge 
                              variant="secondary" 
                              className={`${getStatusColor(order.status)} flex items-center space-x-1`}
                            >
                              {getStatusIcon(order.status)}
                              <span>{order.status.charAt(0).toUpperCase() + order.status.slice(1)}</span>
                            </Badge>
                          </div>
                          <p className="text-sm text-muted-foreground">
                            Ordered on {formatDate(order.createdAt)}
                          </p>
                          <p className="text-sm text-muted-foreground">
                            {order.items.length} item{order.items.length !== 1 ? 's' : ''} • {order.total.formatted}
                          </p>
                        </div>

                        <div className="flex items-center space-x-2">
                          <Button 
                            variant="outline" 
                            size="sm"
                            onClick={() => handleViewOrder(order.uuid)}
                          >
                            <Eye className="mr-2 h-4 w-4" />
                            View Details
                          </Button>
                          
                          <DropdownMenu>
                            <DropdownMenuTrigger asChild>
                              <Button variant="ghost" size="icon">
                                <MoreHorizontal className="h-4 w-4" />
                              </Button>
                            </DropdownMenuTrigger>
                            <DropdownMenuContent align="end">
                              <DropdownMenuLabel>Actions</DropdownMenuLabel>
                              <DropdownMenuSeparator />
                              <DropdownMenuItem onClick={() => handleDownloadInvoice(order.orderNumber)}>
                                <Download className="mr-2 h-4 w-4" />
                                Download Invoice
                              </DropdownMenuItem>
                              <DropdownMenuItem onClick={() => handleReorder()}>
                                <RefreshCw className="mr-2 h-4 w-4" />
                                Reorder
                              </DropdownMenuItem>
                            </DropdownMenuContent>
                          </DropdownMenu>
                        </div>
                      </div>

                      {}
                      <div className="mt-4 pt-4 border-t">
                        <div className="flex flex-wrap gap-2">
                          {order.items.slice(0, 3).map((item, index) => (
                            <div 
                              key={`${order.orderNumber}-item-${index}-${item.productUuid}`} 
                              className="text-sm bg-muted px-2 py-1 rounded"
                            >
                              {item.productName} × {item.quantity}
                            </div>
                          ))}
                          {order.items.length > 3 && (
                            <div className="text-sm text-muted-foreground px-2 py-1">
                              +{order.items.length - 3} more
                            </div>
                          )}
                        </div>
                      </div>
                    </Card>
                  ))}
                </div>
              )}
            </CardContent>
          </Card>
        </div>
      </div>
    </div>
  );
}

export default function OrdersPage() {
  return (
    <Suspense fallback={
      <div className="container mx-auto px-4 py-8">
        <div className="flex items-center justify-center py-12">
          <div className="animate-spin rounded-full h-8 w-8 border-b-2 border-red-600"></div>
          <p className="ml-2 text-muted-foreground">Loading orders...</p>
        </div>
      </div>
    }>
      <OrdersPageContent />
    </Suspense>
  );
}