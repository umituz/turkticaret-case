'use client';

import { useState } from 'react';
import { Card, CardContent } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Badge } from '@/components/ui/badge';

import { Minus, Plus, Trash2 } from 'lucide-react';
import type { ApiCartItem as CartItemType } from '@/types/api';

interface CartItemProps {
  item: CartItemType;
  onQuantityChange: (itemUuid: string, newQuantity: number) => void;
  onRemove: (itemUuid: string) => void;
}

export function CartItem({ item, onQuantityChange, onRemove }: CartItemProps) {
  const [quantity, setQuantity] = useState(item.quantity);


  const handleQuantityChange = (newQuantity: number) => {
    if (newQuantity < 1) return;
    if (newQuantity > (item.product?.stock_quantity || 0)) return;
    
    setQuantity(newQuantity);
    onQuantityChange(item.uuid, newQuantity);
  };

  const handleInputChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    const value = parseInt(e.target.value);
    if (!isNaN(value) && value > 0) {
      handleQuantityChange(value);
    }
  };

  const isLowStock = (item.product?.stock_quantity || 0) < 10;

  if (!item.product) {
    return null;
  }

  return (
    <Card className="mb-4">
      <CardContent className="p-4">
        <div className="flex flex-col md:flex-row gap-4">
          <div className="w-full md:w-24 h-24 bg-gradient-to-br from-red-50 to-red-100 rounded-lg flex items-center justify-center flex-shrink-0">
            <span className="text-2xl font-bold text-red-600/30">
              {item.product.name.charAt(0)}
            </span>
          </div>

          <div className="flex-1">
            <div className="flex flex-col md:flex-row justify-between gap-2">
              <div className="flex-1">
                <h3 className="font-semibold text-lg mb-1">
                  {item.product.name}
                </h3>
                <p className="text-muted-foreground text-sm line-clamp-2 mb-2">
                  {item.product.description}
                </p>
                <div className="flex items-center gap-2 mb-2">
                  {item.product.category && (
                    <Badge variant="secondary" className="text-xs">
                      {item.product.category.name}
                    </Badge>
                  )}
                  {isLowStock && (
                    <Badge variant="destructive" className="text-xs">
                      Only {item.product.stock_quantity} left
                    </Badge>
                  )}
                </div>
              </div>

              <div className="text-right">
                <p className="text-xl font-bold text-red-600">
                  -
                </p>
                <p className="text-sm text-muted-foreground">
                  Unit price
                </p>
              </div>
            </div>

            <div className="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mt-4">
              <div className="flex items-center gap-3">
                <span className="text-sm font-medium">Quantity:</span>
                <div className="flex items-center gap-2">
                  <Button
                    variant="outline"
                    size="icon"
                    className="h-8 w-8"
                    onClick={() => handleQuantityChange(quantity - 1)}
                    disabled={quantity <= 1}
                  >
                    <Minus className="h-4 w-4" />
                  </Button>
                  
                  <Input
                    type="number"
                    min="1"
                    max={item.product.stock_quantity}
                    value={quantity}
                    onChange={handleInputChange}
                    className="w-16 h-8 text-center"
                  />
                  
                  <Button
                    variant="outline"
                    size="icon"
                    className="h-8 w-8"
                    onClick={() => handleQuantityChange(quantity + 1)}
                    disabled={quantity >= (item.product.stock_quantity || 0)}
                  >
                    <Plus className="h-4 w-4" />
                  </Button>
                </div>
                
                <Button
                  variant="outline"
                  size="sm"
                  onClick={() => onRemove(item.uuid)}
                  className="text-destructive hover:text-destructive"
                >
                  <Trash2 className="h-4 w-4 mr-1" />
                  Remove
                </Button>
              </div>

              <div className="text-right">
                <p className="text-xl font-bold text-red-600">
                  -
                </p>
                <p className="text-sm text-muted-foreground">
                  {quantity} units total
                </p>
              </div>
            </div>
          </div>
        </div>
      </CardContent>
    </Card>
  );
}