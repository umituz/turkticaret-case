import Image from 'next/image';
import { useEffect, useRef } from 'react';
import { useRouter } from 'next/navigation';
import { ShoppingCart } from 'lucide-react';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardFooter } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { useToast } from '@/hooks/use-toast';
import { useAppSelector, useAppDispatch } from '@/store/hooks';
import { addToCartAPI } from '@/store/slices/cartSlice';
import { Product } from '@/types/product';

interface ProductCardProps {
  product: Product;
  viewMode?: 'grid' | 'list';
  priority?: boolean;
}

export const ProductCard = ({ product, viewMode = 'grid', priority = false }: ProductCardProps) => {
  const { toast } = useToast();
  const dispatch = useAppDispatch();
  const { isAuthenticated } = useAppSelector((state) => state.auth);
  const router = useRouter();
  const toastRef = useRef(toast);
  
  useEffect(() => {
    toastRef.current = toast;
  }, [toast]);

  const isOutOfStock = product.quantity <= 0;
  const isLowStock = product.quantity > 0 && product.quantity <= 5;
  const hasDiscount = product.comparePrice && product.comparePrice.raw > product.price.raw;

  const handleAddToCart = async (e: React.MouseEvent) => {
    e.preventDefault();
    e.stopPropagation();
    
    if (isOutOfStock) return;

    
    if (!isAuthenticated) {
      toastRef.current({
        title: "Login Required",
        description: "Please login to add items to your cart. Redirecting...",
        variant: "default",
      });
      
      setTimeout(() => router.push('/auth/login'), 1500);
      return;
    }

    try {
      
      await dispatch(addToCartAPI({ 
        productUuid: product.uuid, 
        quantity: 1,
        isAuthenticated: true
      })).unwrap();

      toastRef.current({
        title: 'Added to cart',
        description: `${product.name} has been added to your cart.`,
      });
    } catch {
      toastRef.current({
        title: "Error",
        description: "Failed to add item to cart. Please try again.",
        variant: "destructive",
      });
    }
  };


  if (viewMode === 'list') {
    return (
      <Card className="group hover:shadow-lg transition-all duration-300">
        <div className="flex">
          <div className="flex flex-1">
            <div className="w-48 h-32 bg-muted relative overflow-hidden flex-shrink-0">
              {product.images?.[0]?.url ? (
                <Image
                  src={product.images[0].url}
                  alt={product.name}
                  fill
                  sizes="(max-width: 768px) 100vw, (max-width: 1200px) 50vw, 25vw"
                  priority={priority}
                  className="object-cover transition-transform duration-300 group-hover:scale-105"
                />
              ) : (
                <div className="w-full h-full flex items-center justify-center bg-muted">
                  <ShoppingCart className="h-8 w-8 text-muted-foreground" />
                </div>
              )}
              
              <div className="absolute top-2 left-2 flex flex-col gap-1">
                {hasDiscount && (
                  <Badge variant="destructive" className="text-xs">
                    SALE
                  </Badge>
                )}
                {isLowStock && !isOutOfStock && (
                  <Badge variant="secondary" className="text-xs">
                    Only {product.quantity} left
                  </Badge>
                )}
                {isOutOfStock && (
                  <Badge variant="destructive" className="text-xs">
                    Sold Out
                  </Badge>
                )}
              </div>
            </div>

            <div className="flex-1 p-4">
              <div className="flex items-start justify-between">
                <div className="flex-1">
                  <h3 className="font-semibold text-lg mb-1 group-hover:text-red-600 transition-colors">
                    {product.name}
                  </h3>
                  <p className="text-muted-foreground text-sm mb-2">
                    {product.brand}
                  </p>
                  <p className="text-muted-foreground text-sm mb-3 line-clamp-2">
                    {product.description}
                  </p>
                </div>
                
                <div className="text-right">
                  <div className="flex items-center space-x-2">
                    <span className="text-2xl font-bold text-red-600">
                      {product.price.formatted}
                    </span>
                    {hasDiscount && (
                      <span className="text-sm text-muted-foreground line-through">
                        {product.comparePrice?.formatted}
                      </span>
                    )}
                  </div>
                  <p className="text-xs text-muted-foreground mt-1">
                    Stock: {product.quantity} units
                  </p>
                </div>
              </div>
            </div>
          </div>

          <div className="p-4 flex flex-col justify-center space-y-2">
            <Button 
              onClick={handleAddToCart}
              disabled={isOutOfStock}
              className="bg-red-600 hover:bg-red-700"
            >
              <ShoppingCart className="mr-2 h-4 w-4" />
              {isOutOfStock ? 'Out of Stock' : 'Add to Cart'}
            </Button>
          </div>
        </div>
      </Card>
    );
  }

  return (
    <Card className="group hover:shadow-lg transition-all duration-300 hover:-translate-y-1 overflow-hidden">
      <div className="block">
        <div className="aspect-square bg-muted relative overflow-hidden">
          {product.images?.[0]?.url ? (
            <Image
              src={product.images[0].url}
              alt={product.name}
              fill
              sizes="(max-width: 768px) 100vw, (max-width: 1200px) 50vw, 25vw"
              priority={priority}
              className="object-cover transition-transform duration-300 group-hover:scale-105"
            />
          ) : (
            <div className="w-full h-full flex items-center justify-center bg-muted">
              <ShoppingCart className="h-12 w-12 text-muted-foreground" />
            </div>
          )}
          
          <div className="absolute top-2 left-2 flex flex-col gap-1">
            {hasDiscount && (
              <Badge variant="destructive" className="text-xs">
                SALE
              </Badge>
            )}
            {isLowStock && !isOutOfStock && (
              <Badge variant="secondary" className="text-xs">
                Only {product.quantity} left
              </Badge>
            )}
            {isOutOfStock && (
              <Badge variant="destructive" className="text-xs">
                Sold Out
              </Badge>
            )}
          </div>
        </div>

        <CardContent className="p-4">
          <h3 className="font-semibold text-lg mb-1 line-clamp-2 group-hover:text-red-600 transition-colors">
            {product.name}
          </h3>
          <p className="text-muted-foreground text-sm mb-2">
            {product.brand}
          </p>
          <p className="text-muted-foreground text-sm mb-3 line-clamp-2">
            {product.description}
          </p>
          
          <div className="flex items-center justify-between">
            <div>
              <div className="flex items-center space-x-2">
                <span className="text-2xl font-bold text-red-600">
                  {product.price.formatted}
                </span>
                {hasDiscount && (
                  <span className="text-sm text-muted-foreground line-through">
                    {product.comparePrice?.formatted}
                  </span>
                )}
              </div>
              <p className="text-xs text-muted-foreground">
                Stock: {product.quantity} units
              </p>
            </div>
          </div>
        </CardContent>
      </div>

      <CardFooter className="p-4 pt-0">
        <Button 
          className="w-full bg-red-600 hover:bg-red-700 disabled:opacity-50"
          onClick={handleAddToCart}
          disabled={isOutOfStock}
        >
          <ShoppingCart className="mr-2 h-4 w-4" />
          {isOutOfStock ? 'Out of Stock' : 'Add to Cart'}
        </Button>
      </CardFooter>
    </Card>
  );
};