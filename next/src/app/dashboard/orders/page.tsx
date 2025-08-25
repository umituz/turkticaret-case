'use client';

import { useState, useEffect, useCallback, useRef, Suspense } from 'react';
import { useAuth } from '@/hooks/useAuth';
import { useRouter } from 'next/navigation';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuLabel,
  DropdownMenuSeparator,
  DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { useToast } from '@/hooks/use-toast';

import { Order } from '@/types/user';
import { getAllOrders, updateOrderStatus } from '@/services/adminOrderService';
import { useLogoutGuard } from '@/hooks/useLogoutGuard';
import {
  Package,
  MoreHorizontal,
  Eye,
  Truck,
  CheckCircle,
  Clock,
  XCircle,
  AlertCircle,
  TrendingUp
} from 'lucide-react';

function AdminOrdersPageContent() {
  
  const { user, isLoading } = useAuth();
  const router = useRouter();
  const { toast } = useToast();
  const toastRef = useRef(toast);
  const { guardedExecution, shouldPreventExecution } = useLogoutGuard();
  const [orders, setOrders] = useState<Order[]>([]);
  const [loading, setLoading] = useState(true);
  const [orderStats, setOrderStats] = useState({ totalOrders: 0, totalRevenue: 0 });
  const [mounted, setMounted] = useState(false);
  const [updatingStatus, setUpdatingStatus] = useState<string | null>(null);
  
  
  useEffect(() => {
    setMounted(true);
  }, []);

  
  useEffect(() => {
    toastRef.current = toast;
  }, [toast]);

  const loadOrders = useCallback(async () => {
    if (!user) return;
    
    await guardedExecution(
      async () => {
        setLoading(true);
        
        const result = await getAllOrders({});
        setOrders(result.orders);
        
        const totalRevenue = result.orders.reduce((sum, order) => sum + order.total.raw, 0);
        setOrderStats({ 
          totalOrders: result.orders.length, 
          totalRevenue 
        });
      },
      {
        onError: (error) => {
          console.error('Failed to load orders:', error);
          toastRef.current({
            title: 'Error!',
            description: 'Failed to load orders. Please try again.',
            variant: 'destructive',
          });
          setOrders([]);
        }
      }
    );
    
    setLoading(false);
  }, [user, guardedExecution]);

  useEffect(() => {
    if (shouldPreventExecution()) {
      return;
    }
    
    if (!isLoading && !user) {
      router.push('/auth/login');
      return;
    }

    if (user?.role !== 'admin') {
      router.push('/');
      return;
    }

    if (user) {
      loadOrders();
    }
  }, [user, isLoading, router, loadOrders, shouldPreventExecution]);

  const handleStatusUpdate = async (orderUuid: string, newStatus: string) => {
    try {
      setUpdatingStatus(orderUuid);
      
      await updateOrderStatus(orderUuid, newStatus);
      
      toastRef.current({
        title: 'Success!',
        description: `Order status updated to ${newStatus}.`,
      });
      
      
      loadOrders();
    } catch (error) {
      console.error('Failed to update order status:', error);
      toastRef.current({
        title: 'Error!',
        description: 'Failed to update order status. Please try again.',
        variant: 'destructive',
      });
    } finally {
      setUpdatingStatus(null);
    }
  };

  const getStatusIcon = (status: string) => {
    switch (status) {
      case 'delivered':
        return <CheckCircle className="h-4 w-4" />;
      case 'shipped':
        return <Truck className="h-4 w-4" />;
      case 'processing':
        return <Package className="h-4 w-4" />;
      case 'cancelled':
        return <XCircle className="h-4 w-4" />;
      case 'refunded':
        return <AlertCircle className="h-4 w-4" />;
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
      case 'refunded':
        return 'bg-purple-100 text-purple-800';
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
    router.push(`/dashboard/orders/${orderUuid}`);
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

  if (!user || user.role !== 'admin') {
    return null; 
  }

  return (
    <div className="space-y-8">
      {}
      <div className="mb-8">
        <h1 className="text-3xl font-bold tracking-tight mb-2">Order Management</h1>
        <p className="text-muted-foreground">
          Manage and track all customer orders
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
            <div className="text-2xl font-bold">{orderStats.totalOrders}</div>
          </CardContent>
        </Card>

        <Card>
          <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle className="text-sm font-medium">Total Revenue</CardTitle>
            <TrendingUp className="h-4 w-4 text-muted-foreground" />
          </CardHeader>
          <CardContent>
            <div className="text-2xl font-bold">
              -
            </div>
          </CardContent>
        </Card>

        <Card>
          <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle className="text-sm font-medium">Status</CardTitle>
            <CheckCircle className="h-4 w-4 text-muted-foreground" />
          </CardHeader>
          <CardContent>
            <div className="text-2xl font-bold">
              Active
            </div>
          </CardContent>
        </Card>
      </div>

      {}
      <Card>
        <CardHeader>
          <CardTitle>Order List</CardTitle>
          <CardDescription>
            View and manage all customer orders
          </CardDescription>
        </CardHeader>
        <CardContent>

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
                  : 'No orders have been placed yet.'
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
                          <Button variant="ghost" size="icon" disabled={updatingStatus === order.uuid}>
                            {updatingStatus === order.uuid ? (
                              <div className="animate-spin rounded-full h-4 w-4 border-b-2 border-current"></div>
                            ) : (
                              <MoreHorizontal className="h-4 w-4" />
                            )}
                          </Button>
                        </DropdownMenuTrigger>
                        <DropdownMenuContent align="end">
                          <DropdownMenuLabel>Update Status</DropdownMenuLabel>
                          <DropdownMenuSeparator />
                          <DropdownMenuItem onClick={() => handleStatusUpdate(order.uuid, 'pending')}>
                            <Clock className="mr-2 h-4 w-4" />
                            Pending
                          </DropdownMenuItem>
                          <DropdownMenuItem onClick={() => handleStatusUpdate(order.uuid, 'processing')}>
                            <Package className="mr-2 h-4 w-4" />
                            Processing
                          </DropdownMenuItem>
                          <DropdownMenuItem onClick={() => handleStatusUpdate(order.uuid, 'shipped')}>
                            <Truck className="mr-2 h-4 w-4" />
                            Shipped
                          </DropdownMenuItem>
                          <DropdownMenuItem onClick={() => handleStatusUpdate(order.uuid, 'delivered')}>
                            <CheckCircle className="mr-2 h-4 w-4" />
                            Delivered
                          </DropdownMenuItem>
                          <DropdownMenuSeparator />
                          <DropdownMenuItem onClick={() => handleStatusUpdate(order.uuid, 'cancelled')} className="text-red-600">
                            <XCircle className="mr-2 h-4 w-4" />
                            Cancel Order
                          </DropdownMenuItem>
                          <DropdownMenuItem onClick={() => handleStatusUpdate(order.uuid, 'refunded')} className="text-purple-600">
                            <AlertCircle className="mr-2 h-4 w-4" />
                            Refund Order
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
  );
}

export default function AdminOrdersPage() {
  return (
    <Suspense fallback={
      <div className="container mx-auto px-4 py-8">
        <div className="flex items-center justify-center py-12">
          <div className="animate-spin rounded-full h-8 w-8 border-b-2 border-red-600"></div>
          <p className="ml-2 text-muted-foreground">Loading orders...</p>
        </div>
      </div>
    }>
      <AdminOrdersPageContent />
    </Suspense>
  );
}