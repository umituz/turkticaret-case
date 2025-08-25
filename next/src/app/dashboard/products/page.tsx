'use client';

import { useState, useEffect, useCallback, useRef } from 'react';
import Link from 'next/link';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Input } from '@/components/ui/input';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuLabel,
  DropdownMenuSeparator,
  DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { useToast } from '@/hooks/use-toast';
import { useSearch } from '@/hooks/useSearch';
import { getAllProducts, deleteProduct, getProductStats } from '@/services/productService';
import { Product, ProductStats } from '@/types/product';
import Image from 'next/image';
import {
  Plus,
  Search,
  MoreHorizontal,
  Edit,
  Trash2,
  Eye,
  Filter,
  Package,
  CheckCircle,
  Star,
  AlertTriangle,
  DollarSign,
  Image as ImageIcon,
  XCircle
} from 'lucide-react';

export default function ProductsPage() {
  const [products, setProducts] = useState<Product[]>([]);
  const [stats, setStats] = useState<ProductStats | null>(null);
  const [loading, setLoading] = useState(true);
  const [deleteLoading, setDeleteLoading] = useState<string | null>(null);
  const { toast } = useToast();
  const toastRef = useRef(toast);
  
  
  const { searchTerm, debouncedSearchTerm, setSearchTerm, clearSearch } = useSearch({
    delay: 300,
    minLength: 0
  });

  
  useEffect(() => {
    toastRef.current = toast;
  }, [toast]);

  const loadStats = useCallback(async () => {
    try {
      const statsData = await getProductStats();
      setStats(statsData);
    } catch (error) {
      console.error('Failed to load stats:', error);
    }
  }, []);

  const loadProducts = useCallback(async (searchQuery = '') => {
    try {
      setLoading(true);
      const result = await getAllProducts({ search: searchQuery, per_page: 20 });
      setProducts(result?.products || []);
    } catch {
      toastRef.current({
        title: 'Error!',
        description: 'Failed to load products.',
        variant: 'destructive',
      });
    } finally {
      setLoading(false);
    }
  }, []);

  
  useEffect(() => {
    loadProducts(debouncedSearchTerm);
    loadStats();
  }, [debouncedSearchTerm, loadProducts, loadStats]);

  const handleDelete = async (uuid: string, name: string) => {
    if (!confirm(`Are you sure you want to delete "${name}"? This action cannot be undone.`)) {
      return;
    }

    try {
      setDeleteLoading(uuid);
      await deleteProduct(uuid);
      
      toastRef.current({
        title: 'Success!',
        description: 'Product deleted successfully.',
      });
      
      
      loadProducts(debouncedSearchTerm);
      loadStats();
    } catch (error) {
      toastRef.current({
        title: 'Error!',
        description: error instanceof Error ? error.message : 'Failed to delete product.',
        variant: 'destructive',
      });
    } finally {
      setDeleteLoading(null);
    }
  };


  const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleDateString('en-US', {
      year: 'numeric',
      month: 'short',
      day: 'numeric'
    });
  };

  const getStockStatus = (product: Product) => {
    if (!product.trackQuantity) {
      return { status: 'unlimited', color: 'bg-blue-100 text-blue-800', text: 'Unlimited' };
    }
    
    if (product.quantity === 0) {
      return { status: 'out-of-stock', color: 'bg-red-100 text-red-800', text: 'Out of Stock' };
    }
    
    if (product.lowStockThreshold && product.quantity <= product.lowStockThreshold) {
      return { status: 'low-stock', color: 'bg-yellow-100 text-yellow-800', text: 'Low Stock' };
    }
    
    return { status: 'in-stock', color: 'bg-green-100 text-green-800', text: 'In Stock' };
  };

  return (
    <div className="space-y-6">
      {}
      <div className="flex items-center justify-between">
        <div>
          <h1 className="text-3xl font-bold tracking-tight">Products</h1>
          <p className="text-muted-foreground">
            Manage your product catalog and inventory
          </p>
        </div>
        <Button asChild>
          <Link href="/dashboard/products/new">
            <Plus className="mr-2 h-4 w-4" />
            Add Product
          </Link>
        </Button>
      </div>

      {}
      {!loading && stats && (
        <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
          <Card>
            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
              <CardTitle className="text-sm font-medium">Total Products</CardTitle>
              <Package className="h-4 w-4 text-muted-foreground" />
            </CardHeader>
            <CardContent>
              <div className="text-2xl font-bold">{stats.totalProducts}</div>
            </CardContent>
          </Card>
          
          <Card>
            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
              <CardTitle className="text-sm font-medium">Active Products</CardTitle>
              <CheckCircle className="h-4 w-4 text-green-600" />
            </CardHeader>
            <CardContent>
              <div className="text-2xl font-bold text-green-600">{stats.activeProducts}</div>
            </CardContent>
          </Card>
          
          <Card>
            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
              <CardTitle className="text-sm font-medium">Out of Stock</CardTitle>
              <AlertTriangle className="h-4 w-4 text-red-600" />
            </CardHeader>
            <CardContent>
              <div className="text-2xl font-bold text-red-600">{stats.outOfStockProducts}</div>
            </CardContent>
          </Card>
          
          <Card>
            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
              <CardTitle className="text-sm font-medium">Total Value</CardTitle>
              <DollarSign className="h-4 w-4 text-blue-600" />
            </CardHeader>
            <CardContent>
              <div className="text-2xl font-bold text-blue-600">{stats.totalValue.formatted}</div>
            </CardContent>
          </Card>
        </div>
      )}

      {}
      <Card>
        <CardHeader>
          <CardTitle>Products List</CardTitle>
          <CardDescription>
            View and manage all products in your catalog
          </CardDescription>
        </CardHeader>
        <CardContent>
          <div className="flex items-center space-x-4 mb-6">
            <div className="flex-1 relative">
              <Search className="absolute left-3 top-1/2 transform -translate-y-1/2 text-muted-foreground h-4 w-4" />
              <Input
                placeholder="Search products..."
                value={searchTerm}
                onChange={(e) => setSearchTerm(e.target.value)}
                className="pl-10"
              />
            </div>
            <Button onClick={clearSearch} variant="outline" disabled={!searchTerm}>
              <XCircle className="mr-2 h-4 w-4" />
              Clear
            </Button>
            <Button variant="outline">
              <Filter className="mr-2 h-4 w-4" />
              Filter
            </Button>
          </div>

          {loading ? (
            <div className="text-center py-8">
              <div className="animate-spin rounded-full h-8 w-8 border-b-2 border-red-600 mx-auto"></div>
              <p className="text-muted-foreground mt-2">Loading products...</p>
            </div>
          ) : (
            <div className="overflow-x-auto">
              <table className="w-full">
                <thead>
                  <tr className="border-b border-border">
                    <th className="text-left py-3 px-2 font-medium text-muted-foreground">Product</th>
                    <th className="text-left py-3 px-2 font-medium text-muted-foreground">Category</th>
                    <th className="text-left py-3 px-2 font-medium text-muted-foreground">Price</th>
                    <th className="text-left py-3 px-2 font-medium text-muted-foreground">Stock</th>
                    <th className="text-left py-3 px-2 font-medium text-muted-foreground">Status</th>
                    <th className="text-left py-3 px-2 font-medium text-muted-foreground">Created</th>
                    <th className="text-center py-3 px-2 font-medium text-muted-foreground">Actions</th>
                  </tr>
                </thead>
                <tbody>
                  {products.map((product) => {
                    const stockStatus = getStockStatus(product);
                    return (
                      <tr key={product.uuid} className="border-b border-border/50 hover:bg-muted/50">
                        <td className="py-4 px-2">
                          <div className="flex items-center space-x-3">
                            <div className="w-10 h-10 bg-muted rounded-lg flex items-center justify-center">
                              {product.images.length > 0 ? (
                                <Image 
                                  src={product.images[0].url} 
                                  alt={product.images[0].alt || product.name}
                                  width={40}
                                  height={40}
                                  className="w-full h-full object-cover rounded-lg"
                                  onError={(e) => {
                                    const target = e.target as HTMLImageElement;
                                    target.style.display = 'none';
                                    target.nextElementSibling?.classList.remove('hidden');
                                  }}
                                />
                              ) : null}
                              <ImageIcon className="h-4 w-4 text-muted-foreground" />
                            </div>
                            <div>
                              <p className="font-medium">{product.name}</p>
                              <p className="text-sm text-muted-foreground">{product.sku}</p>
                              <div className="flex items-center space-x-2 mt-1">
                                {product.isFeatured && (
                                  <Star className="h-3 w-3 text-yellow-500" />
                                )}
                                {product.isDigital && (
                                  <Badge variant="secondary" className="text-xs">Digital</Badge>
                                )}
                              </div>
                            </div>
                          </div>
                        </td>
                        <td className="py-4 px-2">
                          <span className="text-sm">{product.categoryName || 'Uncategorized'}</span>
                        </td>
                        <td className="py-4 px-2">
                          <div>
                            <p className="font-medium">{product.price.formatted}</p>
                            {product.comparePrice && product.comparePrice > product.price && (
                              <p className="text-sm text-muted-foreground line-through">
                                {product.comparePrice.formatted}
                              </p>
                            )}
                          </div>
                        </td>
                        <td className="py-4 px-2">
                          <div className="flex items-center space-x-2">
                            <Badge variant="secondary" className={stockStatus.color}>
                              {stockStatus.text}
                            </Badge>
                            {product.trackQuantity && (
                              <span className="text-sm text-muted-foreground">
                                {product.quantity} units
                              </span>
                            )}
                          </div>
                        </td>
                        <td className="py-4 px-2">
                          <Badge
                            variant="secondary"
                            className={product.isActive 
                              ? "bg-green-100 text-green-800" 
                              : "bg-red-100 text-red-800"
                            }
                          >
                            {product.isActive ? 'Active' : 'Inactive'}
                          </Badge>
                        </td>
                        <td className="py-4 px-2">
                          <span className="text-sm text-muted-foreground">
                            {formatDate(product.createdAt)}
                          </span>
                        </td>
                        <td className="py-4 px-2">
                          <DropdownMenu>
                            <DropdownMenuTrigger asChild>
                              <Button variant="ghost" size="icon" className="h-8 w-8">
                                <MoreHorizontal className="h-4 w-4" />
                              </Button>
                            </DropdownMenuTrigger>
                            <DropdownMenuContent align="end">
                              <DropdownMenuLabel>Actions</DropdownMenuLabel>
                              <DropdownMenuSeparator />
                              <DropdownMenuItem asChild>
                                <Link href={`/dashboard/products/${product.uuid}`}>
                                  <Eye className="mr-2 h-4 w-4" />
                                  View Details
                                </Link>
                              </DropdownMenuItem>
                              <DropdownMenuItem asChild>
                                <Link href={`/dashboard/products/${product.uuid}/edit`}>
                                  <Edit className="mr-2 h-4 w-4" />
                                  Edit Product
                                </Link>
                              </DropdownMenuItem>
                              <DropdownMenuSeparator />
                              <DropdownMenuItem
                                onClick={() => handleDelete(product.uuid, product.name)}
                                disabled={deleteLoading === product.uuid}
                                className="text-red-600"
                              >
                                <Trash2 className="mr-2 h-4 w-4" />
                                {deleteLoading === product.uuid ? 'Deleting...' : 'Delete'}
                              </DropdownMenuItem>
                            </DropdownMenuContent>
                          </DropdownMenu>
                        </td>
                      </tr>
                    );
                  })}
                </tbody>
              </table>

              {products.length === 0 && !loading && (
                <div className="text-center py-8">
                  <Package className="mx-auto h-12 w-12 text-muted-foreground" />
                  <h3 className="mt-2 text-sm font-medium">No products found</h3>
                  <p className="mt-1 text-sm text-muted-foreground">
                    {searchTerm ? 'Try adjusting your search criteria.' : 'Get started by creating a new product.'}
                  </p>
                  {!searchTerm && (
                    <div className="mt-6">
                      <Button asChild>
                        <Link href="/dashboard/products/new">
                          <Plus className="mr-2 h-4 w-4" />
                          Add Product
                        </Link>
                      </Button>
                    </div>
                  )}
                </div>
              )}
            </div>
          )}
        </CardContent>
      </Card>
    </div>
  );
}