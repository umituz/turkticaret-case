'use client';

import { useState } from 'react';
import { useRouter } from 'next/navigation';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Separator } from '@/components/ui/separator';
import { 
  ShoppingCart, 
  Heart, 
  Share2,
  Minus,
  Plus
} from 'lucide-react';
import { useToast } from '@/hooks/use-toast';

import { useAppDispatch, useAppSelector } from '@/store/hooks';
import { addToCartAPI } from '@/store/slices/cartSlice';
import type { Product } from '@/types/product';

interface ProductInfoProps {
  product: Product & { category?: { name: string } };
}

export function ProductInfo({ product }: ProductInfoProps) {
  const { toast } = useToast();
  const dispatch = useAppDispatch();
  const { isAuthenticated } = useAppSelector((state) => state.auth);
  const router = useRouter();
  const [quantity, setQuantity] = useState(1);
  const [isFavorite, setIsFavorite] = useState(false);


  const handleAddToCart = async () => {
    if (isOutOfStock) return;

    
    if (!isAuthenticated) {
      toast({
        title: "Login Required",
        description: "Please login to add items to your cart. Click here to login.",
        variant: "default",
      });
      
      setTimeout(() => router.push('/auth/login'), 2000);
      return;
    }

    try {
      
      await dispatch(addToCartAPI({ 
        productUuid: product.uuid, 
        quantity: quantity,
        isAuthenticated: true
      })).unwrap();

      toast({
        title: "Added to cart!",
        description: `${quantity} units of ${product.name} added to your cart.`,
      });
    } catch {
      toast({
        title: "Error",
        description: "Failed to add item to cart. Please try again.",
        variant: "destructive",
      });
    }
  };

  const handleToggleFavorite = () => {
    setIsFavorite(!isFavorite);
    toast({
      title: isFavorite ? "Removed from favorites" : "Added to favorites!",
      description: `${product.name} ${isFavorite ? 'removed from your favorites' : 'added to your favorites'}.`,
    });
  };

  const handleShare = () => {
    if (navigator.share) {
      navigator.share({
        title: product.name,
        text: product.description,
        url: window.location.href,
      });
    } else {
      navigator.clipboard.writeText(window.location.href);
      toast({
        title: "Link copied!",
        description: "Product link copied to clipboard.",
      });
    }
  };

  const isLowStock = product.quantity < 10;
  const isOutOfStock = product.quantity === 0;

  return (
    <div className="space-y-6">
      {}
      <div>
        <div className="flex items-center gap-2 mb-2">
          {product.category && (
            <Badge variant="secondary">{product.category.name}</Badge>
          )}
          {isLowStock && !isOutOfStock && (
            <Badge variant="destructive">Only {product.quantity} left</Badge>
          )}
          {isOutOfStock && (
            <Badge variant="destructive">Out of Stock</Badge>
          )}
        </div>
        
        <h1 className="text-2xl md:text-3xl font-bold mb-3">{product.name}</h1>
        
      </div>

      {}
      <div className="space-y-2">
        <div className="text-3xl font-bold text-red-600">
          {product.price.formatted}
        </div>
      </div>

      <Separator />

      {}
      <div>
        <h3 className="font-semibold mb-2">Product Description</h3>
        <p className="text-muted-foreground leading-relaxed">
          {product.description}
        </p>
      </div>

      {}
      <Card>
        <CardHeader>
          <CardTitle className="text-lg">Product Details</CardTitle>
        </CardHeader>
        <CardContent className="space-y-2">
          <div className="grid grid-cols-1 md:grid-cols-2 gap-2 text-sm">
            <div className="flex justify-between">
              <span className="text-muted-foreground">Stock Status:</span>
              <span className="font-medium">{product.quantity} units</span>
            </div>
            <div className="flex justify-between">
              <span className="text-muted-foreground">SKU:</span>
              <span className="font-medium">{product.sku || product.uuid}</span>
            </div>
            <div className="flex justify-between">
              <span className="text-muted-foreground">Category:</span>
              <span className="font-medium">{product.category?.name || 'General'}</span>
            </div>
          </div>
        </CardContent>
      </Card>

      {}
      <div className="space-y-4">
        <div className="flex items-center gap-4">
          <span className="font-medium">Quantity:</span>
          <div className="flex items-center gap-2">
            <Button
              variant="outline"
              size="icon"
              onClick={() => setQuantity(Math.max(1, quantity - 1))}
              disabled={quantity <= 1}
            >
              <Minus className="w-4 h-4" />
            </Button>
            <span className="w-12 text-center font-medium">{quantity}</span>
            <Button
              variant="outline"
              size="icon"
              onClick={() => setQuantity(Math.min(product.quantity, quantity + 1))}
              disabled={quantity >= product.quantity}
            >
              <Plus className="w-4 h-4" />
            </Button>
          </div>
        </div>

        <div className="flex gap-3">
          <Button
            className="flex-1 bg-gradient-to-r from-red-600 to-red-700 hover:from-red-700 hover:to-red-800 h-12"
            onClick={handleAddToCart}
            disabled={isOutOfStock}
          >
            <ShoppingCart className="w-5 h-5 mr-2" />
            {isOutOfStock ? 'Out of Stock' : 'Add to Cart'}
          </Button>
          
          <Button
            variant="outline"
            size="icon"
            className="h-12 w-12"
            onClick={handleToggleFavorite}
          >
            <Heart className={`w-5 h-5 ${isFavorite ? 'text-red-600 fill-current' : ''}`} />
          </Button>
          
          <Button
            variant="outline"
            size="icon"
            className="h-12 w-12"
            onClick={handleShare}
          >
            <Share2 className="w-5 h-5" />
          </Button>
        </div>
      </div>

    </div>
  );
}