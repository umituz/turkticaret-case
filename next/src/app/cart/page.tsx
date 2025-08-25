'use client';

import Link from 'next/link';
import Image from 'next/image';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { ArrowLeft, ShoppingBag, Plus, Minus, Trash2, Package } from 'lucide-react';
import { useCart } from '@/hooks/useCart';
import { useAuth } from '@/hooks/useAuth';
import { useToast } from '@/hooks/use-toast';
import { useRouter } from 'next/navigation';

export default function CartPage() {
  const { items, total, itemCount, updateQuantity, removeItem, clearCart } = useCart();
  const { isAuthenticated } = useAuth();
  const { toast } = useToast();
  const router = useRouter();

  const handleQuantityChange = (itemUuid: string, newQuantity: number) => {
    updateQuantity(itemUuid, newQuantity);
    toast({
      title: "Quantity Updated",
      description: "Product quantity has been updated successfully.",
    });
  };

  const handleRemoveItem = (itemUuid: string) => {
    const item = items.find(item => item.uuid === itemUuid);
    removeItem(itemUuid);
    toast({
      title: "Product Removed",
      description: `${item?.name} has been removed from your cart.`,
    });
  };

  const handleCheckout = () => {
    if (!isAuthenticated) {
      toast({
        title: "Login Required",
        description: "Please login to proceed with checkout.",
        variant: "destructive",
      });
      router.push('/auth/login?redirect=/cart');
      return;
    }

    
    router.push('/checkout');
  };

  const handleClearCart = () => {
    clearCart();
    toast({
      title: "Cart Cleared",
      description: "All items have been removed from your cart.",
    });
  };

  
 

  
  if (items.length === 0) {
    return (
      <div className="container mx-auto px-4 py-8">
        <div className="max-w-2xl mx-auto text-center">
          <div className="mb-8">
            <Link 
              href="/" 
              className="inline-flex items-center text-sm text-muted-foreground hover:text-red-600 transition-colors"
            >
              <ArrowLeft className="w-4 h-4 mr-2" />
              Continue Shopping
            </Link>
          </div>

          <Card className="p-8">
            <CardContent className="text-center">
              <ShoppingBag className="w-16 h-16 mx-auto mb-6 text-muted-foreground" />
              <h1 className="text-2xl font-bold mb-4">Your Cart is Empty</h1>
              <p className="text-muted-foreground mb-6">
                You haven&apos;t added any products to your cart yet. 
                Start shopping to discover our amazing products.
              </p>
              <Button 
                asChild
                className="bg-red-600 hover:bg-red-700"
              >
                <Link href="/">
                  <ShoppingBag className="w-4 h-4 mr-2" />
                  Start Shopping
                </Link>
              </Button>
            </CardContent>
          </Card>
        </div>
      </div>
    );
  }

  return (
    <div className="container mx-auto px-4 py-8">
      {}
      <div className="mb-8">
        <Link 
          href="/" 
          className="inline-flex items-center text-sm text-muted-foreground hover:text-red-600 transition-colors mb-4"
        >
          <ArrowLeft className="w-4 h-4 mr-2" />
          Continue Shopping
        </Link>
        
        <div className="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
          <div>
            <h1 className="text-3xl font-bold mb-2">Shopping Cart</h1>
            <p className="text-muted-foreground">
              {itemCount} item{itemCount !== 1 ? 's' : ''} in your cart
            </p>
          </div>
          
          {items.length > 0 && (
            <Button variant="outline" onClick={handleClearCart}>
              Clear Cart
            </Button>
          )}
        </div>
      </div>

      <div className="grid grid-cols-1 lg:grid-cols-3 gap-8">
        {}
        <div className="lg:col-span-2">
          <div className="space-y-4">
            {items.map((item) => (
              <Card key={item.productUuid}>
                <CardContent className="p-6">
                  <div className="flex items-center space-x-4">
                    <div className="w-20 h-20 bg-muted rounded-lg flex items-center justify-center flex-shrink-0">
                      {item.image ? (
                        <Image
                          src={item.image}
                          alt={item.name}
                          width={80}
                          height={80}
                          className="w-full h-full object-cover rounded-lg"
                        />
                      ) : (
                        <Package className="h-8 w-8 text-muted-foreground" />
                      )}
                    </div>
                    
                    <div className="flex-1 min-w-0">
                      <h3 className="font-medium text-lg">{item.name}</h3>
                      <div className="flex items-center space-x-1 text-sm text-muted-foreground">
                        {item.variant && <span>{item.variant}</span>}
                        {item.size && <span>• {item.size}</span>}
                        {item.color && <span>• {item.color}</span>}
                      </div>
                      <div className="flex items-center space-x-2 mt-2">
                        <span className="font-semibold text-lg">
                          {(item.salePrice || item.price).formatted}
                        </span>
                        {item.salePrice && (
                          <span className="text-sm text-muted-foreground line-through">
                            {item.price.formatted}
                          </span>
                        )}
                      </div>
                    </div>
                    
                    <div className="flex flex-col items-end space-y-2">
                      <div className="flex items-center space-x-1">
                        <Button
                          variant="outline"
                          size="icon"
                          className="h-8 w-8"
                          onClick={() => handleQuantityChange(item.uuid, item.quantity - 1)}
                        >
                          <Minus className="h-3 w-3" />
                        </Button>
                        <span className="w-12 text-center text-sm font-medium">
                          {item.quantity}
                        </span>
                        <Button
                          variant="outline"
                          size="icon"
                          className="h-8 w-8"
                          onClick={() => handleQuantityChange(item.uuid, item.quantity + 1)}
                        >
                          <Plus className="h-3 w-3" />
                        </Button>
                      </div>
                      
                      <Button
                        variant="ghost"
                        size="sm"
                        className="text-red-600 hover:text-red-700"
                        onClick={() => handleRemoveItem(item.uuid)}
                      >
                        <Trash2 className="h-4 w-4 mr-1" />
                        Remove
                      </Button>
                      
                      <div className="font-semibold text-lg">
                        {(item.salePrice || item.price).formatted}
                      </div>
                    </div>
                  </div>
                </CardContent>
              </Card>
            ))}
          </div>

          {}
          <div className="mt-8 text-center">
            <Button variant="outline" asChild>
              <Link href="/">
                <ArrowLeft className="w-4 h-4 mr-2" />
                Continue Shopping
              </Link>
            </Button>
          </div>
        </div>

        {}
        <div className="lg:col-span-1">
          <Card>
            <CardHeader>
              <CardTitle>Order Summary</CardTitle>
            </CardHeader>
            <CardContent className="space-y-4">
              <div className="flex justify-between font-semibold text-lg">
                <span>Total ({itemCount} items)</span>
                <span>{total.formatted}</span>
              </div>
              
              
              <Button 
                className="w-full bg-red-600 hover:bg-red-700" 
                onClick={handleCheckout}
              >
                Proceed to Checkout
              </Button>
              
              {!isAuthenticated && (
                <p className="text-sm text-muted-foreground text-center">
                  Please login to complete your purchase
                </p>
              )}
            </CardContent>
          </Card>

        </div>
      </div>
    </div>
  );
}