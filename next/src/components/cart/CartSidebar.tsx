'use client';

import { useCart } from '@/hooks/useCart';

import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { useRouter } from 'next/navigation';
import {
  Sheet,
  SheetContent,
  SheetDescription,
  SheetHeader,
  SheetTitle,
  SheetTrigger,
} from '@/components/ui/sheet';
import Image from 'next/image';
import {
  ShoppingCart,
  Plus,
  Minus,
  Trash2,
  Package,
  X
} from 'lucide-react';

export const CartSidebar = () => {
  const { 
    items, 
    total, 
    itemCount, 
    isOpen, 
    updateQuantity, 
    removeItem, 
    clearCart, 
    closeCart,
    toggleCart 
  } = useCart();
  const router = useRouter();

  const handleCheckout = () => {
    closeCart();
    router.push('/cart');
  };

  const handleContinueShopping = () => {
    closeCart();
    router.push('/');
  };

  return (
    <Sheet key="cart-sidebar" open={isOpen} onOpenChange={toggleCart}>
      <SheetTrigger asChild>
        <Button variant="outline" size="icon" className="relative">
          <ShoppingCart className="h-4 w-4" />
          {itemCount > 0 && (
            <Badge 
              variant="destructive" 
              className="absolute -top-2 -right-2 h-5 w-5 rounded-full p-0 flex items-center justify-center text-xs"
            >
              {itemCount > 99 ? '99+' : itemCount}
            </Badge>
          )}
        </Button>
      </SheetTrigger>
      <SheetContent className="w-full sm:w-96 flex flex-col">
        <SheetHeader>
          <SheetTitle className="flex items-center justify-between">
            <span>Shopping Cart</span>
            <Button variant="ghost" size="icon" onClick={closeCart}>
              <X className="h-4 w-4" />
            </Button>
          </SheetTitle>
          <SheetDescription>
            {itemCount === 0 ? 'Your cart is empty' : `${itemCount} item${itemCount !== 1 ? 's' : ''} in your cart`}
          </SheetDescription>
        </SheetHeader>

        {items.length === 0 ? (
          <div className="flex-1 flex flex-col items-center justify-center space-y-4">
            <Package className="h-16 w-16 text-muted-foreground" />
            <div className="text-center">
              <h3 className="text-lg font-medium">Your cart is empty</h3>
              <p className="text-muted-foreground">Add some products to get started</p>
            </div>
            <Button onClick={handleContinueShopping}>
              Continue Shopping
            </Button>
          </div>
        ) : (
          <>
            {}
            <div className="flex-1 overflow-y-auto space-y-4 py-4">
              {items.map((item) => (
                <div key={item.productUuid} className="flex items-center space-x-3 p-3 border rounded-lg">
                  <div className="w-16 h-16 bg-muted rounded-lg flex items-center justify-center flex-shrink-0">
                    {item.image ? (
                      <Image
                        src={item.image}
                        alt={item.name}
                        width={64}
                        height={64}
                        className="w-full h-full object-cover rounded-lg"
                      />
                    ) : (
                      <Package className="h-8 w-8 text-muted-foreground" />
                    )}
                  </div>
                  
                  <div className="flex-1 min-w-0">
                    <h4 className="font-medium truncate">{item.name}</h4>
                    <div className="flex items-center space-x-1 text-sm text-muted-foreground">
                      {item.variant && <span>{item.variant}</span>}
                      {item.size && <span>• {item.size}</span>}
                      {item.color && <span>• {item.color}</span>}
                    </div>
                    <div className="flex items-center justify-between mt-2">
                      <div className="flex items-center space-x-1">
                        <Button
                          variant="outline"
                          size="icon"
                          className="h-8 w-8"
                          onClick={() => updateQuantity(item.uuid, item.quantity - 1)}
                        >
                          <Minus className="h-3 w-3" />
                        </Button>
                        <span className="w-8 text-center text-sm font-medium">
                          {item.quantity}
                        </span>
                        <Button
                          variant="outline"
                          size="icon"
                          className="h-8 w-8"
                          onClick={() => updateQuantity(item.uuid, item.quantity + 1)}
                        >
                          <Plus className="h-3 w-3" />
                        </Button>
                      </div>
                      <Button
                        variant="ghost"
                        size="icon"
                        className="h-8 w-8 text-red-600 hover:text-red-700"
                        onClick={() => removeItem(item.uuid)}
                      >
                        <Trash2 className="h-3 w-3" />
                      </Button>
                    </div>
                  </div>
                  
                  <div className="text-right">
                    <div className="font-semibold">
                      {(item.salePrice || item.price).formatted}
                    </div>
                    {item.salePrice && (
                      <div className="text-sm text-muted-foreground line-through">
                        {item.price.formatted}
                      </div>
                    )}
                  </div>
                </div>
              ))}
            </div>

            {}
            <div className="border-t pt-4 mt-4 bg-background/95 backdrop-blur supports-[backdrop-filter]:bg-background/60">
              {}
              <div className="flex items-center justify-between text-xl font-bold mb-4 px-1">
                <span>Total</span>
                <span className="text-primary">{total.formatted}</span>
              </div>

              {}
              <div className="space-y-3">
                <Button 
                  className="w-full h-12 text-base font-semibold" 
                  size="lg"
                  onClick={handleCheckout}
                >
                  Proceed to Checkout
                </Button>
                <Button 
                  variant="outline" 
                  className="w-full h-10 text-sm" 
                  onClick={handleContinueShopping}
                >
                  Continue Shopping
                </Button>
                <Button 
                  variant="ghost" 
                  className="w-full h-8 text-xs text-muted-foreground hover:text-destructive" 
                  onClick={clearCart}
                >
                  Clear Cart
                </Button>
              </div>
            </div>
          </>
        )}
      </SheetContent>
    </Sheet>
  );
};