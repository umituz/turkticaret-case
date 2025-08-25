'use client';

import { useEffect, useState, Suspense } from 'react';
import { useSearchParams, useRouter } from 'next/navigation';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { useAuth } from '@/hooks/useAuth';
import {
  CheckCircle,
  Package,
  Mail,
  Download,
  ArrowRight,
  Home,
  ShoppingBag
} from 'lucide-react';

function CheckoutSuccessContent() {
  const searchParams = useSearchParams();
  const router = useRouter();
  const { user } = useAuth();
  const [orderNumber, setOrderNumber] = useState<string>('');

  useEffect(() => {
    const order = searchParams.get('order');
    if (order) {
      setOrderNumber(order);
    } else {
      
      router.push('/cart');
    }
  }, [searchParams, router]);

  const handleContinueShopping = () => {
    router.push('/');
  };

  const handleViewOrders = () => {
    router.push('/account/orders');
  };

  const handleDownloadInvoice = () => {
    
    const link = document.createElement('a');
    link.href = '#';
    link.download = `invoice-${orderNumber}.pdf`;
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
  };

  if (!orderNumber) {
    return (
      <div className="container mx-auto px-4 py-8">
        <div className="text-center">
          <div className="animate-spin rounded-full h-8 w-8 border-b-2 border-red-600 mx-auto"></div>
          <p className="mt-2 text-muted-foreground">Loading...</p>
        </div>
      </div>
    );
  }

  return (
    <div className="container mx-auto px-4 py-8">
      <div className="max-w-2xl mx-auto">
        {}
        <div className="text-center mb-8">
          <div className="w-20 h-20 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
            <CheckCircle className="w-10 h-10 text-green-600" />
          </div>
          <h1 className="text-3xl font-bold text-green-600 mb-2">Order Confirmed!</h1>
          <p className="text-muted-foreground">
            Thank you for your purchase. Your order has been successfully placed.
          </p>
        </div>

        {}
        <Card className="mb-6">
          <CardHeader>
            <CardTitle className="flex items-center space-x-2">
              <Package className="h-5 w-5" />
              <span>Order Details</span>
            </CardTitle>
          </CardHeader>
          <CardContent className="space-y-4">
            <div className="bg-gray-50 p-4 rounded-lg">
              <div className="flex items-center justify-between">
                <div>
                  <p className="font-semibold text-lg">Order #{orderNumber}</p>
                  <p className="text-sm text-muted-foreground">
                    Placed on {new Date().toLocaleDateString('en-US', {
                      year: 'numeric',
                      month: 'long',
                      day: 'numeric',
                      hour: '2-digit',
                      minute: '2-digit'
                    })}
                  </p>
                </div>
                <Button variant="outline" size="sm" onClick={handleDownloadInvoice}>
                  <Download className="mr-2 h-4 w-4" />
                  Download Invoice
                </Button>
              </div>
            </div>

            {}
            <div className="space-y-3">
              <h3 className="font-semibold">What happens next?</h3>
              <div className="space-y-3">
                <div className="flex items-start space-x-3">
                  <div className="w-6 h-6 bg-blue-100 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5">
                    <span className="text-xs font-semibold text-blue-600">1</span>
                  </div>
                  <div>
                    <p className="font-medium">Order Processing</p>
                    <p className="text-sm text-muted-foreground">
                      We&apos;ll prepare your items for shipment within 1-2 business days
                    </p>
                  </div>
                </div>
                
                <div className="flex items-start space-x-3">
                  <div className="w-6 h-6 bg-blue-100 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5">
                    <span className="text-xs font-semibold text-blue-600">2</span>
                  </div>
                  <div>
                    <p className="font-medium">Shipping Notification</p>
                    <p className="text-sm text-muted-foreground">
                      You&apos;ll receive a tracking number once your order ships
                    </p>
                  </div>
                </div>
                
                <div className="flex items-start space-x-3">
                  <div className="w-6 h-6 bg-blue-100 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5">
                    <span className="text-xs font-semibold text-blue-600">3</span>
                  </div>
                  <div>
                    <p className="font-medium">Delivery</p>
                    <p className="text-sm text-muted-foreground">
                      Your order will arrive within the selected delivery timeframe
                    </p>
                  </div>
                </div>
              </div>
            </div>
          </CardContent>
        </Card>

        {}
        <Card className="mb-6">
          <CardContent className="p-4">
            <div className="flex items-center space-x-3">
              <Mail className="h-5 w-5 text-blue-600" />
              <div>
                <p className="font-medium">Confirmation email sent</p>
                <p className="text-sm text-muted-foreground">
                  Order confirmation has been sent to {user?.email}
                </p>
              </div>
            </div>
          </CardContent>
        </Card>

        {}
        <div className="space-y-3">
          <Button 
            className="w-full bg-red-600 hover:bg-red-700" 
            onClick={handleContinueShopping}
          >
            <ShoppingBag className="mr-2 h-4 w-4" />
            Continue Shopping
            <ArrowRight className="ml-2 h-4 w-4" />
          </Button>
          
          <Button 
            variant="outline" 
            className="w-full" 
            onClick={handleViewOrders}
          >
            <Package className="mr-2 h-4 w-4" />
            View My Orders
          </Button>

          <Button 
            variant="ghost" 
            className="w-full" 
            onClick={() => router.push('/')}
          >
            <Home className="mr-2 h-4 w-4" />
            Back to Home
          </Button>
        </div>

      </div>
    </div>
  );
}

export default function CheckoutSuccessPage() {
  return (
    <Suspense
      fallback={
        <div className="container mx-auto px-4 py-8">
          <div className="text-center">
            <div className="animate-spin rounded-full h-8 w-8 border-b-2 border-red-600 mx-auto"></div>
            <p className="mt-2 text-muted-foreground">Loading order details...</p>
          </div>
        </div>
      }
    >
      <CheckoutSuccessContent />
    </Suspense>
  );
}