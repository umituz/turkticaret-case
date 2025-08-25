'use client';

import { useState, useEffect, useCallback, useRef, Suspense } from 'react';
import { useAuth } from '@/hooks/useAuth';
import { useRouter, useParams } from 'next/navigation';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { ProfileSidebar } from '@/components/profile/ProfileSidebar';
import { useToast } from '@/hooks/use-toast';
import { formatDate } from '@/utils/common';
import { getOrderStatusIcon, getOrderStatusColor, getOrderStatusDotColor, getPaymentStatusColor } from '@/lib/order-utils';
import { Order } from '@/types/user';
import { orderService, OrderStatusHistory } from '@/services/orderService';
import {
  ArrowLeft,
  Package,
  Calendar,
  DollarSign,
  MapPin,
  CreditCard,
  Download,
  RefreshCw,
  Truck
} from 'lucide-react';
import Image from 'next/image';

function OrderDetailPageContent() {
  
  const { user, isLoading } = useAuth();
  const router = useRouter();
  const params = useParams() as { uuid: string };
  const { toast } = useToast();
  const toastRef = useRef(toast);
  const [order, setOrder] = useState<Order | null>(null);
  const [orderHistory, setOrderHistory] = useState<OrderStatusHistory | null>(null);
  const [loading, setLoading] = useState(true);
  const [historyLoading, setHistoryLoading] = useState(false);
  const [mounted, setMounted] = useState(false);
  
  
  useEffect(() => {
    setMounted(true);
  }, []);

  
  useEffect(() => {
    toastRef.current = toast;
  }, [toast]);

  const loadOrder = useCallback(async () => {
    if (!user || !params.uuid) {
      router.push('/');
      return;
    }
    
    try {
      setLoading(true);
      
      
      const foundOrder = await orderService.getOrder(params.uuid as string);
      
      setOrder(foundOrder);
    } catch (error) {
      console.error('Failed to load order:', error);
      toastRef.current({
        title: 'Error!',
        description: 'Failed to load order details.',
        variant: 'destructive',
      });
      router.push('/account/orders');
    } finally {
      setLoading(false);
    }
  }, [user, params.uuid, router]);

  const loadOrderHistory = useCallback(async () => {
    if (!user || !params.uuid) {
      return;
    }
    
    try {
      setHistoryLoading(true);
      const history = await orderService.getOrderStatusHistory(params.uuid as string);
      setOrderHistory(history);
    } catch (error) {
      console.error('Failed to load order history:', error);
      
    } finally {
      setHistoryLoading(false);
    }
  }, [user, params.uuid]);

  useEffect(() => {
    if (!isLoading && !user) {
      router.push('/auth/login');
      return;
    }

    if (user?.role === 'admin') {
      router.push('/dashboard');
      return;
    }

    if (user && params.uuid) {
      loadOrder();
      loadOrderHistory();
    }
  }, [user, isLoading, params.uuid, router, loadOrder, loadOrderHistory]);



  const handleDownloadInvoice = () => {
    if (!order) return;
    toastRef.current({
      title: 'Download Started',
      description: `Invoice for order ${order.orderNumber} is being prepared.`,
    });
  };

  const handleReorder = () => {
    if (!order) return;
    toastRef.current({
      title: 'Added to Cart',
      description: 'Items from this order have been added to your cart.',
    });
  };

  const handleTrackOrder = () => {
    if (!order) return;
    toastRef.current({
      title: 'Track Order',
      description: 'Tracking information will be available soon.',
    });
  };

  
  if (!mounted || isLoading) {
    return (
      <div className="container mx-auto px-4 py-8">
        <div className="flex items-center justify-center py-12">
          <div className="animate-spin rounded-full h-8 w-8 border-b-2 border-red-600"></div>
          <p className="ml-2 text-muted-foreground">Loading order...</p>
        </div>
      </div>
    );
  }

  if (!user) {
    return null; 
  }

  if (loading) {
    return (
      <div className="container mx-auto px-4 py-8">
        <div className="flex items-center justify-center py-12">
          <div className="animate-spin rounded-full h-8 w-8 border-b-2 border-red-600"></div>
          <p className="ml-2 text-muted-foreground">Loading order details...</p>
        </div>
      </div>
    );
  }

  if (!order) {
    return (
      <div className="container mx-auto px-4 py-8">
        <div className="text-center py-12">
          <Package className="mx-auto h-12 w-12 text-muted-foreground" />
          <h3 className="mt-2 text-sm font-medium">Order not found</h3>
          <p className="mt-1 text-sm text-muted-foreground">
            The requested order could not be found.
          </p>
          <Button className="mt-4" onClick={() => router.push('/account/orders')}>
            Back to Orders
          </Button>
        </div>
      </div>
    );
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
          <div className="flex items-center space-x-4">
            <Button variant="ghost" size="icon" onClick={() => router.back()}>
              <ArrowLeft className="h-4 w-4" />
            </Button>
            <div className="flex-1">
              <h1 className="text-3xl font-bold tracking-tight">Order #{order.orderNumber}</h1>
              <p className="text-muted-foreground">
                Placed on {formatDate(order.createdAt)}
              </p>
            </div>
            <div className="flex items-center space-x-2">
              <Button variant="outline" onClick={handleDownloadInvoice}>
                <Download className="mr-2 h-4 w-4" />
                Download Invoice
              </Button>
              <Button variant="outline" onClick={handleReorder}>
                <RefreshCw className="mr-2 h-4 w-4" />
                Reorder
              </Button>
            </div>
          </div>

          {}
          <Card>
            <CardHeader>
              <CardTitle className="flex items-center space-x-2">
                {getOrderStatusIcon(order.status)}
                <span>Order Status</span>
              </CardTitle>
            </CardHeader>
            <CardContent>
              <div className="flex items-center justify-between">
                <div>
                  <Badge variant="secondary" className={`${getOrderStatusColor(order.status)} flex items-center space-x-1`}>
                    <span>{order.status.charAt(0).toUpperCase() + order.status.slice(1)}</span>
                  </Badge>
                  <p className="text-sm text-muted-foreground mt-2">
                    {order.status === 'delivered' && order.deliveredAt && `Delivered on ${formatDate(order.deliveredAt)}`}
                    {order.status === 'shipped' && order.shippedAt && `Shipped on ${formatDate(order.shippedAt)}`}
                    {order.status === 'processing' && 'Your order is being processed'}
                    {order.status === 'pending' && 'Your order is pending confirmation'}
                  </p>
                </div>
                {(order.status === 'shipped' || order.status === 'delivered') && (
                  <Button variant="outline" onClick={handleTrackOrder}>
                    <Truck className="mr-2 h-4 w-4" />
                    Track Package
                  </Button>
                )}
              </div>
            </CardContent>
          </Card>

          {}
          <div className="grid gap-6 md:grid-cols-2">
            {}
            <Card>
              <CardHeader>
                <CardTitle className="flex items-center space-x-2">
                  <Package className="h-5 w-5" />
                  <span>Order Details</span>
                </CardTitle>
              </CardHeader>
              <CardContent className="space-y-4">
                <div className="flex justify-between">
                  <span className="text-muted-foreground">Order Number</span>
                  <span className="font-medium">{order.orderNumber}</span>
                </div>
                <div className="flex justify-between">
                  <span className="text-muted-foreground">Order Date</span>
                  <span className="font-medium">{formatDate(order.createdAt)}</span>
                </div>
                <div className="flex justify-between">
                  <span className="text-muted-foreground">Total Items</span>
                  <span className="font-medium">{order.items.length}</span>
                </div>
                <div className="flex justify-between">
                  <span className="text-muted-foreground">Payment Status</span>
                  <Badge variant="secondary" className={getPaymentStatusColor(order.paymentStatus)}>
                    {order.paymentStatus.charAt(0).toUpperCase() + order.paymentStatus.slice(1)}
                  </Badge>
                </div>
              </CardContent>
            </Card>

            {}
            <Card>
              <CardHeader>
                <CardTitle className="flex items-center space-x-2">
                  <DollarSign className="h-5 w-5" />
                  <span>Payment Summary</span>
                </CardTitle>
              </CardHeader>
              <CardContent className="space-y-4">
                <div className="flex justify-between">
                  <span className="text-muted-foreground">Subtotal</span>
                  <span>{order.subtotal.formatted}</span>
                </div>
                <div className="flex justify-between">
                  <span className="text-muted-foreground">Shipping</span>
                  <span>{order.shipping.formatted}</span>
                </div>
                <div className="flex justify-between">
                  <span className="text-muted-foreground">Tax</span>
                  <span>{order.tax.formatted}</span>
                </div>
                {order.discount.raw > 0 && (
                  <div className="flex justify-between text-green-600">
                    <span>Discount</span>
                    <span>-{order.discount.formatted}</span>
                  </div>
                )}
                <hr />
                <div className="flex justify-between font-semibold text-lg">
                  <span>Total</span>
                  <span>{order.total.formatted}</span>
                </div>
                <div className="flex items-center space-x-2 text-sm text-muted-foreground">
                  <CreditCard className="h-4 w-4" />
                  <span>Paid with {order.paymentMethod}</span>
                </div>
              </CardContent>
            </Card>
          </div>

          {}
          <Card>
            <CardHeader>
              <CardTitle>Order Items</CardTitle>
              <CardDescription>
                {order.items.length} item{order.items.length !== 1 ? 's' : ''} in this order
              </CardDescription>
            </CardHeader>
            <CardContent>
              <div className="space-y-4">
                {order.items.map((item, index) => (
                  <div key={index} className="flex items-center space-x-4 p-4 border rounded-lg">
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
                      <h4 className="font-medium">{item.productName}</h4>
                      <p className="text-sm text-muted-foreground">SKU: {item.productSku}</p>
                      <p className="text-sm text-muted-foreground">Quantity: {item.quantity}</p>
                    </div>
                    <div className="text-right">
                      <p className="font-semibold">{item.total.formatted}</p>
                      <p className="text-sm text-muted-foreground">{item.price.formatted} each</p>
                    </div>
                  </div>
                ))}
              </div>
            </CardContent>
          </Card>

          {}
          <div className="grid gap-6 md:grid-cols-2">
            {}
            <Card>
              <CardHeader>
                <CardTitle className="flex items-center space-x-2">
                  <MapPin className="h-5 w-5" />
                  <span>Shipping Address</span>
                </CardTitle>
              </CardHeader>
              <CardContent>
                <div className="space-y-1">
                  <p className="font-medium">
                    {order.shippingAddress.firstName} {order.shippingAddress.lastName}
                  </p>
                  {order.shippingAddress.company && (
                    <p className="text-muted-foreground">{order.shippingAddress.company}</p>
                  )}
                  <p className="text-muted-foreground">{order.shippingAddress.address1}</p>
                  {order.shippingAddress.address2 && (
                    <p className="text-muted-foreground">{order.shippingAddress.address2}</p>
                  )}
                  <p className="text-muted-foreground">
                    {order.shippingAddress.city}, {order.shippingAddress.state} {order.shippingAddress.postalCode}
                  </p>
                  <p className="text-muted-foreground">{order.shippingAddress.country}</p>
                  {order.shippingAddress.phone && (
                    <p className="text-muted-foreground">{order.shippingAddress.phone}</p>
                  )}
                </div>
              </CardContent>
            </Card>

            {}
            <Card>
              <CardHeader>
                <CardTitle className="flex items-center space-x-2">
                  <CreditCard className="h-5 w-5" />
                  <span>Billing Address</span>
                </CardTitle>
              </CardHeader>
              <CardContent>
                <div className="space-y-1">
                  <p className="font-medium">
                    {order.billingAddress.firstName} {order.billingAddress.lastName}
                  </p>
                  {order.billingAddress.company && (
                    <p className="text-muted-foreground">{order.billingAddress.company}</p>
                  )}
                  <p className="text-muted-foreground">{order.billingAddress.address1}</p>
                  {order.billingAddress.address2 && (
                    <p className="text-muted-foreground">{order.billingAddress.address2}</p>
                  )}
                  <p className="text-muted-foreground">
                    {order.billingAddress.city}, {order.billingAddress.state} {order.billingAddress.postalCode}
                  </p>
                  <p className="text-muted-foreground">{order.billingAddress.country}</p>
                </div>
              </CardContent>
            </Card>
          </div>

          {}
          <Card>
            <CardHeader>
              <CardTitle className="flex items-center space-x-2">
                <Calendar className="h-5 w-5" />
                <span>Order Timeline</span>
              </CardTitle>
            </CardHeader>
            <CardContent>
              {historyLoading ? (
                <div className="flex items-center justify-center py-8">
                  <div className="animate-spin rounded-full h-6 w-6 border-b-2 border-red-600"></div>
                  <p className="ml-2 text-muted-foreground">Loading timeline...</p>
                </div>
              ) : orderHistory && orderHistory.history.length > 0 ? (
                <div className="space-y-4">
                  {orderHistory.history.map((historyItem, index) => (
                    <div key={`${historyItem.status}-${index}`} className="flex items-start space-x-3">
                      <div className={`w-3 h-3 ${getOrderStatusDotColor(historyItem.status)} rounded-full mt-1 flex-shrink-0`}></div>
                      <div className="flex-1 min-w-0">
                        <div className="flex items-center justify-between">
                          <p className="font-medium text-sm">
                            {historyItem.description}
                          </p>
                          <p className="text-xs text-muted-foreground">
                            {formatDate(historyItem.date)}
                          </p>
                        </div>
                      </div>
                    </div>
                  ))}
                </div>
              ) : (
                <div className="space-y-4">
                  <div className="flex items-center space-x-3">
                    <div className="w-2 h-2 bg-blue-600 rounded-full"></div>
                    <div>
                      <p className="font-medium">Order Placed</p>
                      <p className="text-sm text-muted-foreground">{formatDate(order.createdAt)}</p>
                    </div>
                  </div>
                  
                  {order.status !== 'pending' && (
                    <div className="flex items-center space-x-3">
                      <div className="w-2 h-2 bg-yellow-600 rounded-full"></div>
                      <div>
                        <p className="font-medium">Order Confirmed</p>
                        <p className="text-sm text-muted-foreground">{formatDate(order.updatedAt)}</p>
                      </div>
                    </div>
                  )}

                  {order.shippedAt && (
                    <div className="flex items-center space-x-3">
                      <div className="w-2 h-2 bg-blue-600 rounded-full"></div>
                      <div>
                        <p className="font-medium">Order Shipped</p>
                        <p className="text-sm text-muted-foreground">{formatDate(order.shippedAt)}</p>
                      </div>
                    </div>
                  )}

                  {order.deliveredAt && (
                    <div className="flex items-center space-x-3">
                      <div className="w-2 h-2 bg-green-600 rounded-full"></div>
                      <div>
                        <p className="font-medium">Order Delivered</p>
                        <p className="text-sm text-muted-foreground">{formatDate(order.deliveredAt)}</p>
                      </div>
                    </div>
                  )}
                </div>
              )}
            </CardContent>
          </Card>
        </div>
      </div>
    </div>
  );
}

export default function OrderDetailPage() {
  return (
    <Suspense fallback={
      <div className="container mx-auto px-4 py-8">
        <div className="flex items-center justify-center py-12">
          <div className="animate-spin rounded-full h-8 w-8 border-b-2 border-red-600"></div>
          <p className="ml-2 text-muted-foreground">Loading order...</p>
        </div>
      </div>
    }>
      <OrderDetailPageContent />
    </Suspense>
  );
}