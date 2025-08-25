'use client';

import { useState, useEffect, useCallback, useRef, Suspense } from 'react';
import { useAuth } from '@/hooks/useAuth';
import { useRouter, useParams } from 'next/navigation';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { useToast } from '@/hooks/use-toast';

import { Order } from '@/types/user';
import { getOrder, updateOrderStatus } from '@/services/adminOrderService';
import {
  ArrowLeft,
  Package,
  Calendar,
  MapPin,
  Truck,
  CheckCircle,
  Clock,
  XCircle,
  AlertCircle
} from 'lucide-react';
import Image from 'next/image';

function AdminOrderDetailPageContent() {
  
  const { user, isLoading } = useAuth();
  const router = useRouter();
  const params = useParams() as { uuid: string };
  const { toast } = useToast();
  const toastRef = useRef(toast);
  const [order, setOrder] = useState<Order | null>(null);
  const [loading, setLoading] = useState(true);
  const [mounted, setMounted] = useState(false);
  const [updatingStatus, setUpdatingStatus] = useState(false);
  
  
  useEffect(() => {
    setMounted(true);
  }, []);

  
  useEffect(() => {
    toastRef.current = toast;
  }, [toast]);

  const loadOrder = useCallback(async () => {
    if (!user || !params.uuid) return;
    
    try {
      setLoading(true);
      const orderData = await getOrder(params.uuid);
      setOrder(orderData);
    } catch (error) {
      console.error('Failed to load order:', error);
      toastRef.current({
        title: 'Error!',
        description: 'Failed to load order details. Please try again.',
        variant: 'destructive',
      });
    } finally {
      setLoading(false);
    }
  }, [user, params.uuid]);

  useEffect(() => {
    if (!isLoading && !user) {
      router.push('/auth/login');
      return;
    }

    if (user?.role !== 'admin') {
      router.push('/');
      return;
    }

    if (user && params.uuid) {
      loadOrder();
    }
  }, [user, isLoading, router, params.uuid, loadOrder]);

  const handleStatusUpdate = useCallback(async (newStatus: string) => {
    if (!order || !user || updatingStatus) return;

    try {
      setUpdatingStatus(true);
      await updateOrderStatus(order.uuid, newStatus);
      
      setOrder(prev => prev ? { ...prev, status: newStatus as Order['status'] } : null);
      
      toastRef.current({
        title: 'Success!',
        description: `Order status updated to ${newStatus}`,
        variant: 'default',
      });
    } catch (error) {
      console.error('Failed to update order status:', error);
      toastRef.current({
        title: 'Error!',
        description: 'Failed to update order status. Please try again.',
        variant: 'destructive',
      });
    } finally {
      setUpdatingStatus(false);
    }
  }, [order, user, updatingStatus]);

  const getStatusIcon = (status: string) => {
    switch (status) {
      case 'delivered':
        return <CheckCircle className="h-5 w-5" />;
      case 'shipped':
        return <Truck className="h-5 w-5" />;
      case 'processing':
        return <Package className="h-5 w-5" />;
      case 'cancelled':
        return <XCircle className="h-5 w-5" />;
      case 'refunded':
        return <AlertCircle className="h-5 w-5" />;
      default:
        return <Clock className="h-5 w-5" />;
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
      month: 'long',
      day: 'numeric',
      hour: '2-digit',
      minute: '2-digit'
    });
  };

  
  if (!mounted || isLoading) {
    return (
      <div className="container mx-auto px-4 py-8">
        <div className="flex items-center justify-center py-12">
          <div className="animate-spin rounded-full h-8 w-8 border-b-2 border-red-600"></div>
          <p className="ml-2 text-muted-foreground">Loading order details...</p>
        </div>
      </div>
    );
  }

  if (!user || user.role !== 'admin') {
    return null; 
  }

  if (loading) {
    return (
      <div className="space-y-8">
        <div className="flex items-center justify-center py-12">
          <div className="animate-spin rounded-full h-8 w-8 border-b-2 border-red-600"></div>
          <p className="ml-2 text-muted-foreground">Loading order details...</p>
        </div>
      </div>
    );
  }

  if (!order) {
    return (
      <div className="space-y-8">
        <div className="flex items-center space-x-4 mb-8">
          <Button 
            variant="outline" 
            size="icon" 
            onClick={() => router.back()}
            className="h-10 w-10"
          >
            <ArrowLeft className="h-4 w-4" />
          </Button>
          <div>
            <h1 className="text-3xl font-bold tracking-tight">Order Not Found</h1>
            <p className="text-muted-foreground">
              The order you&apos;re looking for doesn&apos;t exist or you don&apos;t have permission to view it.
            </p>
          </div>
        </div>
      </div>
    );
  }

  return (
    <div className="space-y-8">
      {}
      <div className="flex items-center space-x-4 mb-8">
        <Button 
          variant="outline" 
          size="icon" 
          onClick={() => router.push('/dashboard/orders')}
          className="h-10 w-10"
        >
          <ArrowLeft className="h-4 w-4" />
        </Button>
        <div>
          <h1 className="text-3xl font-bold tracking-tight">Order #{order.orderNumber}</h1>
          <p className="text-muted-foreground">
            View order details and manage status
          </p>
        </div>
      </div>

      {}
      <div className="grid gap-6 md:grid-cols-2 lg:grid-cols-4">
        <Card>
          <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle className="text-sm font-medium">Status</CardTitle>
            {getStatusIcon(order.status)}
          </CardHeader>
          <CardContent>
            <Badge 
              variant="secondary" 
              className={`${getStatusColor(order.status)} flex items-center space-x-1`}
            >
              <span>{order.status.charAt(0).toUpperCase() + order.status.slice(1)}</span>
            </Badge>
          </CardContent>
        </Card>

        <Card>
          <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle className="text-sm font-medium">Total Amount</CardTitle>
            <DollarSign className="h-4 w-4 text-muted-foreground" />
          </CardHeader>
          <CardContent>
            <div className="text-2xl font-bold">{order.total.formatted}</div>
          </CardContent>
        </Card>

        <Card>
          <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle className="text-sm font-medium">Order Date</CardTitle>
            <Calendar className="h-4 w-4 text-muted-foreground" />
          </CardHeader>
          <CardContent>
            <div className="text-sm">{formatDate(order.createdAt)}</div>
          </CardContent>
        </Card>

        <Card>
          <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle className="text-sm font-medium">Items</CardTitle>
            <Package className="h-4 w-4 text-muted-foreground" />
          </CardHeader>
          <CardContent>
            <div className="text-2xl font-bold">{order.items.length}</div>
          </CardContent>
        </Card>
      </div>

      {}
      <Card>
        <CardHeader>
          <CardTitle>Update Order Status</CardTitle>
          <CardDescription>
            Change the status of this order
          </CardDescription>
        </CardHeader>
        <CardContent>
          <div className="flex items-center space-x-4">
            <div className="flex-1">
              <label className="text-sm font-medium text-gray-700 mb-2 block">
                Current Status: <span className="font-bold">{order.status.charAt(0).toUpperCase() + order.status.slice(1)}</span>
              </label>
              <Select
                value={order.status}
                onValueChange={handleStatusUpdate}
                disabled={updatingStatus}
              >
                <SelectTrigger className="w-full">
                  <SelectValue />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem value="pending">
                    <div className="flex items-center space-x-2">
                      <Clock className="h-4 w-4" />
                      <span>Pending</span>
                    </div>
                  </SelectItem>
                  <SelectItem value="processing">
                    <div className="flex items-center space-x-2">
                      <Package className="h-4 w-4" />
                      <span>Processing</span>
                    </div>
                  </SelectItem>
                  <SelectItem value="shipped">
                    <div className="flex items-center space-x-2">
                      <Truck className="h-4 w-4" />
                      <span>Shipped</span>
                    </div>
                  </SelectItem>
                  <SelectItem value="delivered">
                    <div className="flex items-center space-x-2">
                      <CheckCircle className="h-4 w-4" />
                      <span>Delivered</span>
                    </div>
                  </SelectItem>
                  <SelectItem value="cancelled">
                    <div className="flex items-center space-x-2">
                      <XCircle className="h-4 w-4" />
                      <span>Cancelled</span>
                    </div>
                  </SelectItem>
                  <SelectItem value="refunded">
                    <div className="flex items-center space-x-2">
                      <AlertCircle className="h-4 w-4" />
                      <span>Refunded</span>
                    </div>
                  </SelectItem>
                </SelectContent>
              </Select>
            </div>
            {updatingStatus && (
              <div className="flex items-center space-x-2 text-sm text-muted-foreground">
                <div className="animate-spin rounded-full h-4 w-4 border-b-2 border-red-600"></div>
                <span>Updating...</span>
              </div>
            )}
          </div>
        </CardContent>
      </Card>

      {}
      <Card>
        <CardHeader>
          <CardTitle>Order Items</CardTitle>
          <CardDescription>
            Products included in this order
          </CardDescription>
        </CardHeader>
        <CardContent>
          <div className="space-y-4">
            {order.items.map((item, index) => (
              <div key={`${item.uuid}-${index}`} className="flex items-center space-x-4 p-4 border rounded-lg">
                <div className="w-16 h-16 bg-muted rounded-lg flex items-center justify-center">
                  {item.productImage ? (
                    <Image
                      src={item.productImage}
                      alt={item.productName}
                      width={64}
                      height={64}
                      className="w-full h-full object-cover rounded-lg"
                    />
                  ) : (
                    <Package className="h-8 w-8 text-muted-foreground" />
                  )}
                </div>
                <div className="flex-1">
                  <h3 className="font-semibold">{item.productName}</h3>
                  {item.productSku && (
                    <p className="text-sm text-muted-foreground">SKU: {item.productSku}</p>
                  )}
                  <div className="flex items-center space-x-4 mt-1">
                    <span className="text-sm text-muted-foreground">
                      Quantity: {item.quantity}
                    </span>
                    <span className="text-sm text-muted-foreground">
                      Price: {item.price.formatted}
                    </span>
                  </div>
                </div>
                <div className="text-right">
                  <p className="font-semibold">{item.total.formatted}</p>
                </div>
              </div>
            ))}
          </div>
        </CardContent>
      </Card>

      {}
      <Card>
        <CardHeader>
          <CardTitle className="flex items-center space-x-2">
            <MapPin className="h-5 w-5" />
            <span>Shipping Address</span>
          </CardTitle>
        </CardHeader>
        <CardContent>
          <div className="space-y-2">
            <p className="font-medium">
              {order.shippingAddress.firstName} {order.shippingAddress.lastName}
            </p>
            <p>{order.shippingAddress.address1}</p>
            {order.shippingAddress.address2 && <p>{order.shippingAddress.address2}</p>}
            <p>
              {order.shippingAddress.city}, {order.shippingAddress.state} {order.shippingAddress.postalCode}
            </p>
            <p>{order.shippingAddress.country}</p>
            {order.shippingAddress.phone && <p>Phone: {order.shippingAddress.phone}</p>}
          </div>
        </CardContent>
      </Card>

      {}
    </div>
  );
}

export default function AdminOrderDetailPage() {
  return (
    <Suspense fallback={
      <div className="container mx-auto px-4 py-8">
        <div className="flex items-center justify-center py-12">
          <div className="animate-spin rounded-full h-8 w-8 border-b-2 border-red-600"></div>
          <p className="ml-2 text-muted-foreground">Loading order details...</p>
        </div>
      </div>
    }>
      <AdminOrderDetailPageContent />
    </Suspense>
  );
}