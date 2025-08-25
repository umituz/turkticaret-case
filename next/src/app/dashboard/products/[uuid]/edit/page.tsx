'use client';

import { useState, useEffect, useCallback, useRef } from 'react';
import { useParams, useRouter } from 'next/navigation';
import { ProductForm } from '@/components/admin/ProductForm';
import { useToast } from '@/hooks/use-toast';
import { getProductByUuid } from '@/services/productService';
import { Product } from '@/types/product';
import { AlertCircle } from 'lucide-react';

export default function EditProductPage() {
  const params = useParams();
  const router = useRouter();
  const { toast } = useToast();
  const toastRef = useRef(toast);
  const [product, setProduct] = useState<Product | null>(null);
  const [loading, setLoading] = useState(true);

  const productUuid = params.uuid as string;

  
  useEffect(() => {
    toastRef.current = toast;
  }, [toast]);

  const loadProduct = useCallback(async () => {
    try {
      setLoading(true);
      const data = await getProductByUuid(productUuid);
      
      if (!data) {
        toastRef.current({
          title: 'Error!',
          description: 'Product not found.',
          variant: 'destructive',
        });
        router.push('/dashboard/products');
        return;
      }
      
      setProduct(data);
    } catch {
      toastRef.current({
        title: 'Error!',
        description: 'Failed to load product details.',
        variant: 'destructive',
      });
      router.push('/dashboard/products');
    } finally {
      setLoading(false);
    }
  }, [productUuid, router]); 

  useEffect(() => {
    if (productUuid) {
      loadProduct();
    }
  }, [productUuid, loadProduct]);

  if (loading) {
    return (
      <div className="container mx-auto py-6">
        <div className="flex items-center justify-center py-12">
          <div className="animate-spin rounded-full h-8 w-8 border-b-2 border-red-600"></div>
          <p className="ml-2 text-muted-foreground">Loading product details...</p>
        </div>
      </div>
    );
  }

  if (!product) {
    return (
      <div className="container mx-auto py-6">
        <div className="text-center py-12">
          <AlertCircle className="mx-auto h-12 w-12 text-muted-foreground" />
          <h3 className="mt-2 text-sm font-medium">Product not found</h3>
          <p className="mt-1 text-sm text-muted-foreground">
            The product you&apos;re trying to edit doesn&apos;t exist or has been deleted.
          </p>
        </div>
      </div>
    );
  }

  return (
    <div className="container mx-auto py-6">
      <ProductForm product={product} mode="edit" />
    </div>
  );
}