'use client';

import { useState, useEffect, useCallback, useRef } from 'react';
import { useParams, useRouter } from 'next/navigation';
import Link from 'next/link';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { useToast } from '@/hooks/use-toast';
import { getProductByUuid, deleteProduct } from '@/services/productService';
import { Product } from '@/types/product';
import Image from 'next/image';
import {
  ArrowLeft,
  Edit,
  Trash2,
  Calendar,
  Hash,
  Package,
  Image as ImageIcon,
  AlertCircle,
  Star,
  CheckCircle,
  XCircle,
  Truck,
  Archive
} from 'lucide-react';

export default function ProductDetailPage() {
  const params = useParams();
  const router = useRouter();
  const { toast } = useToast();
  const toastRef = useRef(toast);
  const [product, setProduct] = useState<Product | null>(null);
  const [loading, setLoading] = useState(true);
  const [deleteLoading, setDeleteLoading] = useState(false);

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

  const handleDelete = async () => {
    if (!product) return;

    if (!confirm(`Are you sure you want to delete "${product.name}"? This action cannot be undone.`)) {
      return;
    }

    try {
      setDeleteLoading(true);
      await deleteProduct(product.slug);
      
      toastRef.current({
        title: 'Success!',
        description: 'Product deleted successfully.',
      });
      
      router.push('/dashboard/products');
    } catch (error) {
      toastRef.current({
        title: 'Error!',
        description: error instanceof Error ? error.message : 'Failed to delete product.',
        variant: 'destructive',
      });
    } finally {
      setDeleteLoading(false);
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

  const getStockStatus = () => {
    if (!product) return { status: 'unknown', color: '', text: '' };
    
    if (!product.trackQuantity) {
      return { status: 'unlimited', color: 'bg-blue-100 text-blue-800', text: 'Unlimited Stock' };
    }
    
    if (product.quantity === 0) {
      return { status: 'out-of-stock', color: 'bg-red-100 text-red-800', text: 'Out of Stock' };
    }
    
    if (product.lowStockThreshold && product.quantity <= product.lowStockThreshold) {
      return { status: 'low-stock', color: 'bg-yellow-100 text-yellow-800', text: 'Low Stock' };
    }
    
    return { status: 'in-stock', color: 'bg-green-100 text-green-800', text: 'In Stock' };
  };

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
            The product you&apos;re looking for doesn&apos;t exist or has been deleted.
          </p>
          <div className="mt-6">
            <Button asChild>
              <Link href="/dashboard/products">
                <ArrowLeft className="mr-2 h-4 w-4" />
                Back to Products
              </Link>
            </Button>
          </div>
        </div>
      </div>
    );
  }

  const stockStatus = getStockStatus();

  return (
    <div className="container mx-auto py-6 space-y-6">
      {}
      <div className="flex items-center justify-between">
        <div className="flex items-center space-x-4">
          <Button variant="ghost" size="icon" onClick={() => router.back()}>
            <ArrowLeft className="h-4 w-4" />
          </Button>
          <div>
            <div className="flex items-center space-x-2">
              <h1 className="text-3xl font-bold tracking-tight">{product.name}</h1>
              {product.isFeatured && <Star className="h-5 w-5 text-yellow-500" />}
            </div>
            <p className="text-muted-foreground">Product details and information</p>
          </div>
        </div>
        <div className="flex items-center space-x-2">
          <Button variant="outline" asChild>
            <Link href={`/dashboard/products/${product.uuid}/edit`}>
              <Edit className="mr-2 h-4 w-4" />
              Edit Product
            </Link>
          </Button>
          <Button
            variant="destructive"
            onClick={handleDelete}
            disabled={deleteLoading}
          >
            <Trash2 className="mr-2 h-4 w-4" />
            {deleteLoading ? 'Deleting...' : 'Delete'}
          </Button>
        </div>
      </div>

      <div className="grid gap-6 lg:grid-cols-3">
        {}
        <div className="lg:col-span-2 space-y-6">
          {}
          <Card>
            <CardHeader>
              <CardTitle>Basic Information</CardTitle>
              <CardDescription>Product details and description</CardDescription>
            </CardHeader>
            <CardContent className="space-y-6">
              <div className="grid gap-6 md:grid-cols-2">
                <div>
                  <label className="text-sm font-medium text-muted-foreground">Product Name</label>
                  <p className="text-lg font-medium">{product.name}</p>
                </div>
                <div>
                  <label className="text-sm font-medium text-muted-foreground">SKU</label>
                  <p className="text-lg font-mono">{product.sku}</p>
                </div>
              </div>

              {product.shortDescription && (
                <div>
                  <label className="text-sm font-medium text-muted-foreground">Short Description</label>
                  <p className="text-base mt-1">{product.shortDescription}</p>
                </div>
              )}

              <div>
                <label className="text-sm font-medium text-muted-foreground">Description</label>
                <p className="text-base mt-1 whitespace-pre-wrap">{product.description}</p>
              </div>

              {product.brand && (
                <div>
                  <label className="text-sm font-medium text-muted-foreground">Brand</label>
                  <p className="text-base mt-1">{product.brand}</p>
                </div>
              )}

              {product.tags.length > 0 && (
                <div>
                  <label className="text-sm font-medium text-muted-foreground">Tags</label>
                  <div className="flex flex-wrap gap-2 mt-2">
                    {product.tags.map((tag) => (
                      <Badge key={tag} variant="secondary">
                        {tag}
                      </Badge>
                    ))}
                  </div>
                </div>
              )}
            </CardContent>
          </Card>

          {}
          <Card>
            <CardHeader>
              <CardTitle>Pricing</CardTitle>
              <CardDescription>Product pricing information</CardDescription>
            </CardHeader>
            <CardContent className="space-y-4">
              <div className="grid gap-4 md:grid-cols-3">
                <div>
                  <label className="text-sm font-medium text-muted-foreground">Regular Price</label>
                  <p className="text-2xl font-bold text-green-600">{product.price.formatted}</p>
                </div>
                
                {product.comparePrice && (
                  <div>
                    <label className="text-sm font-medium text-muted-foreground">Compare Price</label>
                    <p className="text-lg text-muted-foreground line-through">{product.comparePrice.formatted}</p>
                  </div>
                )}

                {product.costPrice && (
                  <div>
                    <label className="text-sm font-medium text-muted-foreground">Cost Price</label>
                    <p className="text-lg">{product.costPrice?.formatted || 'N/A'}</p>
                  </div>
                )}
              </div>
            </CardContent>
          </Card>

          {}
          {product.images.length > 0 && (
            <Card>
              <CardHeader>
                <CardTitle>Product Images</CardTitle>
                <CardDescription>{product.images.length} images</CardDescription>
              </CardHeader>
              <CardContent>
                <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                  {product.images.map((image) => (
                    <div key={image.id} className="aspect-square bg-muted rounded-lg overflow-hidden">
                      <Image
                        src={image.url}
                        alt={image.alt || product.name}
                        width={200}
                        height={200}
                        className="w-full h-full object-cover"
                        onError={(e) => {
                          const target = e.target as HTMLImageElement;
                          target.style.display = 'none';
                          target.nextElementSibling?.classList.remove('hidden');
                        }}
                      />
                      <div className="hidden items-center justify-center h-full">
                        <ImageIcon className="h-8 w-8 text-muted-foreground" />
                      </div>
                    </div>
                  ))}
                </div>
              </CardContent>
            </Card>
          )}
        </div>

        {}
        <div className="space-y-6">
          {}
          <Card>
            <CardHeader>
              <CardTitle>Product Status</CardTitle>
            </CardHeader>
            <CardContent className="space-y-4">
              <div className="flex items-center justify-between">
                <span className="text-sm font-medium">Status</span>
                <Badge
                  variant="secondary"
                  className={product.isActive 
                    ? "bg-green-100 text-green-800" 
                    : "bg-red-100 text-red-800"
                  }
                >
                  <div className="flex items-center space-x-1">
                    {product.isActive ? (
                      <CheckCircle className="h-3 w-3" />
                    ) : (
                      <XCircle className="h-3 w-3" />
                    )}
                    <span>{product.isActive ? 'Active' : 'Inactive'}</span>
                  </div>
                </Badge>
              </div>

              <div className="flex items-center justify-between">
                <span className="text-sm font-medium">Stock Status</span>
                <Badge variant="secondary" className={stockStatus.color}>
                  {stockStatus.text}
                </Badge>
              </div>

              <div className="flex items-center justify-between">
                <span className="text-sm font-medium">Featured</span>
                <Badge variant={product.isFeatured ? "default" : "secondary"}>
                  <div className="flex items-center space-x-1">
                    <Star className="h-3 w-3" />
                    <span>{product.isFeatured ? 'Featured' : 'Not Featured'}</span>
                  </div>
                </Badge>
              </div>

              <div className="flex items-center justify-between">
                <span className="text-sm font-medium">Product Type</span>
                <Badge variant="secondary" className="bg-blue-100 text-blue-800">
                  <div className="flex items-center space-x-1">
                    {product.isDigital ? (
                      <Archive className="h-3 w-3" />
                    ) : (
                      <Package className="h-3 w-3" />
                    )}
                    <span>{product.isDigital ? 'Digital' : 'Physical'}</span>
                  </div>
                </Badge>
              </div>
            </CardContent>
          </Card>

          {}
          <Card>
            <CardHeader>
              <CardTitle>Inventory</CardTitle>
            </CardHeader>
            <CardContent className="space-y-4">
              {product.trackQuantity ? (
                <>
                  <div className="flex items-center justify-between">
                    <span className="text-sm font-medium">Quantity</span>
                    <span className="font-bold text-lg">{product.quantity}</span>
                  </div>
                  
                  {product.lowStockThreshold && (
                    <div className="flex items-center justify-between">
                      <span className="text-sm font-medium">Low Stock Threshold</span>
                      <span className="text-sm">{product.lowStockThreshold}</span>
                    </div>
                  )}
                </>
              ) : (
                <div className="text-center py-4">
                  <Package className="h-8 w-8 mx-auto text-muted-foreground mb-2" />
                  <p className="text-sm text-muted-foreground">Inventory not tracked</p>
                </div>
              )}

              {product.barcode && (
                <div className="flex items-center justify-between">
                  <span className="text-sm font-medium">Barcode</span>
                  <span className="text-sm font-mono">{product.barcode}</span>
                </div>
              )}
            </CardContent>
          </Card>

          {}
          <Card>
            <CardHeader>
              <CardTitle>Product Details</CardTitle>
            </CardHeader>
            <CardContent className="space-y-4">
              <div className="flex items-center justify-between">
                <span className="text-sm font-medium">Product ID</span>
                <div className="flex items-center space-x-1">
                  <Hash className="h-4 w-4 text-muted-foreground" />
                  <span className="font-mono">{product.slug}</span>
                </div>
              </div>

              <div className="flex items-center justify-between">
                <span className="text-sm font-medium">Category</span>
                <span className="text-sm">{product.categoryName || 'Uncategorized'}</span>
              </div>

              {product.weight && (
                <div className="flex items-center justify-between">
                  <span className="text-sm font-medium">Weight</span>
                  <span className="text-sm">{product.weight}g</span>
                </div>
              )}

              <div className="flex items-center justify-between">
                <span className="text-sm font-medium">Shipping Required</span>
                <div className="flex items-center space-x-1">
                  <Truck className="h-4 w-4 text-muted-foreground" />
                  <span className="text-sm">{product.requiresShipping ? 'Yes' : 'No'}</span>
                </div>
              </div>

              <div className="flex items-center justify-between">
                <span className="text-sm font-medium">Taxable</span>
                <span className="text-sm">{product.taxable ? 'Yes' : 'No'}</span>
              </div>
            </CardContent>
          </Card>

          {}
          <Card>
            <CardHeader>
              <CardTitle>Timestamps</CardTitle>
            </CardHeader>
            <CardContent className="space-y-4">
              <div>
                <div className="flex items-center space-x-1 mb-1">
                  <Calendar className="h-4 w-4 text-muted-foreground" />
                  <span className="text-sm font-medium">Created</span>
                </div>
                <p className="text-sm text-muted-foreground">
                  {formatDate(product.createdAt)}
                </p>
              </div>

              <div>
                <div className="flex items-center space-x-1 mb-1">
                  <Calendar className="h-4 w-4 text-muted-foreground" />
                  <span className="text-sm font-medium">Last Updated</span>
                </div>
                <p className="text-sm text-muted-foreground">
                  {formatDate(product.updatedAt)}
                </p>
              </div>

              {product.publishedAt && (
                <div>
                  <div className="flex items-center space-x-1 mb-1">
                    <Calendar className="h-4 w-4 text-muted-foreground" />
                    <span className="text-sm font-medium">Published</span>
                  </div>
                  <p className="text-sm text-muted-foreground">
                    {formatDate(product.publishedAt)}
                  </p>
                </div>
              )}
            </CardContent>
          </Card>
        </div>
      </div>
    </div>
  );
}