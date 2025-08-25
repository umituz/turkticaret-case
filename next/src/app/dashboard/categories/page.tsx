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
import { getAllCategories, deleteCategory, getCategoryStats } from '@/services/categoryService';
import { Category, CategoryStats } from '@/types/category';
import {
  Plus,
  Search,
  MoreHorizontal,
  Edit,
  Trash2,
  Eye,
  Filter,
  Tags,
  CheckCircle,
  XCircle,
  Package
} from 'lucide-react';

export default function CategoriesPage() {
  const [categories, setCategories] = useState<Category[]>([]);
  const [stats, setStats] = useState<CategoryStats | null>(null);
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
      const statsData = await getCategoryStats();
      setStats(statsData);
    } catch (error) {
      console.error('Failed to load stats:', error);
    }
  }, []);

  const loadCategories = useCallback(async (searchQuery = '') => {
    try {
      setLoading(true);
      const data = await getAllCategories({ search: searchQuery });
      setCategories(data);
    } catch {
      toastRef.current({
        title: 'Error!',
        description: 'Failed to load categories.',
        variant: 'destructive',
      });
    } finally {
      setLoading(false);
    }
  }, []);

  
  useEffect(() => {
    loadCategories(debouncedSearchTerm);
    loadStats();
  }, [debouncedSearchTerm, loadCategories, loadStats]);

  const handleDelete = async (uuid: string, name: string) => {
    if (!confirm(`Are you sure you want to delete "${name}"? This action cannot be undone.`)) {
      return;
    }

    try {
      setDeleteLoading(uuid);
      await deleteCategory(uuid);
      
      toastRef.current({
        title: 'Success!',
        description: 'Category deleted successfully.',
      });
      
      
      loadCategories(debouncedSearchTerm);
      loadStats();
    } catch (error) {
      toastRef.current({
        title: 'Error!',
        description: error instanceof Error ? error.message : 'Failed to delete category.',
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


  return (
    <div className="space-y-6">
      {}
      <div className="flex items-center justify-between">
        <div>
          <h1 className="text-3xl font-bold tracking-tight">Categories</h1>
          <p className="text-muted-foreground">
            Manage your product categories and subcategories
          </p>
        </div>
        <Button asChild>
          <Link href="/dashboard/categories/new">
            <Plus className="mr-2 h-4 w-4" />
            Add Category
          </Link>
        </Button>
      </div>

      {}
      {!loading && stats && (
        <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
          <Card>
            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
              <CardTitle className="text-sm font-medium">Total Categories</CardTitle>
              <Tags className="h-4 w-4 text-muted-foreground" />
            </CardHeader>
            <CardContent>
              <div className="text-2xl font-bold">{stats.totalCategories}</div>
            </CardContent>
          </Card>
          
          <Card>
            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
              <CardTitle className="text-sm font-medium">Active Categories</CardTitle>
              <CheckCircle className="h-4 w-4 text-green-600" />
            </CardHeader>
            <CardContent>
              <div className="text-2xl font-bold text-green-600">{stats.activeCategories}</div>
            </CardContent>
          </Card>
          
          <Card>
            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
              <CardTitle className="text-sm font-medium">Inactive Categories</CardTitle>
              <XCircle className="h-4 w-4 text-red-600" />
            </CardHeader>
            <CardContent>
              <div className="text-2xl font-bold text-red-600">{stats.inactiveCategories}</div>
            </CardContent>
          </Card>
          
          <Card>
            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
              <CardTitle className="text-sm font-medium">With Products</CardTitle>
              <Package className="h-4 w-4 text-blue-600" />
            </CardHeader>
            <CardContent>
              <div className="text-2xl font-bold text-blue-600">{stats.categoriesWithProducts}</div>
            </CardContent>
          </Card>
        </div>
      )}

      {}
      <Card>
        <CardHeader>
          <CardTitle>Categories List</CardTitle>
          <CardDescription>
            View and manage all product categories
          </CardDescription>
        </CardHeader>
        <CardContent>
          <div className="flex items-center space-x-4 mb-6">
            <div className="flex-1 relative">
              <Search className="absolute left-3 top-1/2 transform -translate-y-1/2 text-muted-foreground h-4 w-4" />
              <Input
                placeholder="Search categories..."
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

          {}
          {loading ? (
            <div className="text-center py-8">
              <div className="animate-spin rounded-full h-8 w-8 border-b-2 border-red-600 mx-auto"></div>
              <p className="text-muted-foreground mt-2">Loading categories...</p>
            </div>
          ) : (
            <div className="overflow-x-auto">
              <table className="w-full">
                <thead>
                  <tr className="border-b border-border">
                    <th className="text-left py-3 px-2 font-medium text-muted-foreground">Name</th>
                    <th className="text-left py-3 px-2 font-medium text-muted-foreground">Description</th>
                    <th className="text-left py-3 px-2 font-medium text-muted-foreground">Status</th>
                    <th className="text-left py-3 px-2 font-medium text-muted-foreground">Products</th>
                    <th className="text-left py-3 px-2 font-medium text-muted-foreground">Created</th>
                    <th className="text-center py-3 px-2 font-medium text-muted-foreground">Actions</th>
                  </tr>
                </thead>
                <tbody>
                  {categories?.map?.((category) => (
                    <tr key={category.uuid} className="border-b border-border/50 hover:bg-muted/50">
                      <td className="py-4 px-2">
                        <div>
                          <p className="font-medium">{category.name}</p>
                          <p className="text-sm text-muted-foreground">/{category.slug}</p>
                        </div>
                      </td>
                      <td className="py-4 px-2">
                        <p className="text-sm text-muted-foreground line-clamp-2">
                          {category.description || 'No description'}
                        </p>
                      </td>
                      <td className="py-4 px-2">
                        <Badge
                          variant="secondary"
                          className={category.isActive 
                            ? "bg-green-100 text-green-800" 
                            : "bg-red-100 text-red-800"
                          }
                        >
                          {category.isActive ? 'Active' : 'Inactive'}
                        </Badge>
                      </td>
                      <td className="py-4 px-2">
                        <span className="text-sm font-medium">{category.productsCount}</span>
                      </td>
                      <td className="py-4 px-2">
                        <span className="text-sm text-muted-foreground">
                          {formatDate(category.createdAt)}
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
                              <Link href={`/dashboard/categories/${category.uuid}`}>
                                <Eye className="mr-2 h-4 w-4" />
                                View Details
                              </Link>
                            </DropdownMenuItem>
                            <DropdownMenuItem asChild>
                              <Link href={`/dashboard/categories/${category.uuid}/edit`}>
                                <Edit className="mr-2 h-4 w-4" />
                                Edit Category
                              </Link>
                            </DropdownMenuItem>
                            <DropdownMenuSeparator />
                            <DropdownMenuItem
                              onClick={() => handleDelete(category.uuid, category.name)}
                              disabled={deleteLoading === category.uuid}
                              className="text-red-600"
                            >
                              <Trash2 className="mr-2 h-4 w-4" />
                              {deleteLoading === category.uuid ? 'Deleting...' : 'Delete'}
                            </DropdownMenuItem>
                          </DropdownMenuContent>
                        </DropdownMenu>
                      </td>
                    </tr>
                  ))}
                </tbody>
              </table>

              {categories.length === 0 && !loading && (
                <div className="text-center py-8">
                  <Tags className="mx-auto h-12 w-12 text-muted-foreground" />
                  <h3 className="mt-2 text-sm font-medium">No categories found</h3>
                  <p className="mt-1 text-sm text-muted-foreground">
                    {searchTerm ? 'Try adjusting your search criteria.' : 'Get started by creating a new category.'}
                  </p>
                  {!searchTerm && (
                    <div className="mt-6">
                      <Button asChild>
                        <Link href="/dashboard/categories/new">
                          <Plus className="mr-2 h-4 w-4" />
                          Add Category
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