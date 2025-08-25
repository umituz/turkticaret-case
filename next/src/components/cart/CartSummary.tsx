'use client';

import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Separator } from '@/components/ui/separator';
import { Badge } from '@/components/ui/badge';

import { ShoppingCart, CreditCard, Truck } from 'lucide-react';

interface CartSummaryProps {
  subtotal: number;
  itemCount: number;
  onCheckout: () => void;
}

export function CartSummary({ itemCount, onCheckout }: CartSummaryProps) {

 

  return (
    <Card className="sticky top-24">
      <CardHeader>
        <CardTitle className="flex items-center gap-2">
          <ShoppingCart className="w-5 h-5" />
          Order Summary
        </CardTitle>
      </CardHeader>
      
      <CardContent className="space-y-4">
        <div className="flex justify-between items-center">
          <span className="text-muted-foreground">Items:</span>
          <Badge variant="secondary">{itemCount} items</Badge>
        </div>

        <Separator />

        <div className="space-y-3">
          <div className="flex justify-between items-center">
            <span className="text-muted-foreground">Subtotal:</span>
            <span className="font-medium">-</span>
          </div>

          <div className="flex justify-between items-center">
            <span className="text-muted-foreground">Tax (18%):</span>
            <span className="font-medium">-</span>
          </div>

          <div className="flex justify-between items-center">
            <span className="text-muted-foreground flex items-center gap-1">
              <Truck className="w-4 h-4" />
              Shipping:
            </span>
            <span className="font-medium text-green-600">Free</span>
          </div>
        </div>

        <Separator />

        <div className="flex justify-between items-center text-lg font-bold">
          <span>Total:</span>
          <span className="text-red-600">-</span>
        </div>

        <Button
          className="w-full bg-gradient-to-r from-red-600 to-red-700 hover:from-red-700 hover:to-red-800 transition-all duration-300 h-12"
          onClick={onCheckout}
          disabled={itemCount === 0}
        >
          <CreditCard className="w-5 h-5 mr-2" />
          Proceed to Checkout (-)
        </Button>

      </CardContent>
    </Card>
  );
}