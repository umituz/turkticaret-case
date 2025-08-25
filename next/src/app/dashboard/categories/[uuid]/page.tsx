'use client';

import { useState, useEffect, useCallback, useRef } from 'react';
import { useParams, useRouter } from 'next/navigation';
import Link from 'next/link';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { useToast } from '@/hooks/use-toast';
import { getCategoryByUuid, deleteCategory } from '@/services/categoryService';
import { Category } from '@/types/category';
import {
  ArrowLeft,
  Edit,
  Trash2,
  Calendar,
  Hash,
  Package,
  AlertCircle
} from 'lucide-react';

export default function CategoryDetailPage() {
  const params = useParams();
  const router = useRouter();
  const { toast } = useToast();
  const toastRef = useRef(toast);
  const [category, setCategory] = useState<Category | null>(null);
  const [loading, setLoading] = useState(true);
  const [deleteLoading, setDeleteLoading] = useState(false);

  const categoryUuid = params.uuid as string;

  
  useEffect(() => {
    toastRef.current = toast;
  }, [toast]);

  const loadCategory = useCallback(async () => {
    try {
      setLoading(true);
      const data = await getCategoryByUuid(categoryUuid);
      
      if (!data) {
        toastRef.current({
          title: 'Error!',
          description: 'Category not found.',
          variant: 'destructive',
        });
        router.push('/dashboard/categories');
        return;
      }
      
      setCategory(data);
    } catch {
      toastRef.current({
        title: 'Error!',
        description: 'Failed to load category details.',
        variant: 'destructive',
      });
      router.push('/dashboard/categories');
    } finally {
      setLoading(false);
    }
  }, [categoryUuid, router]); 

  useEffect(() => {
    if (categoryUuid) {
      loadCategory();
    }
  }, [categoryUuid, loadCategory]);

  const handleDelete = async () => {
    if (!category) return;

    if (!confirm(`Are you sure you want to delete "${category.name}"? This action cannot be undone.`)) {
      return;
    }

    try {
      setDeleteLoading(true);
      await deleteCategory(category.uuid);
      
      toastRef.current({
        title: 'Success!',
        description: 'Category deleted successfully.',
      });
      
      router.push('/dashboard/categories');
    } catch (error) {
      toastRef.current({
        title: 'Error!',
        description: error instanceof Error ? error.message : 'Failed to delete category.',
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

  if (loading) {
    return (
      <div className="container mx-auto py-6">
        <div className="flex items-center justify-center py-12">
          <div className="animate-spin rounded-full h-8 w-8 border-b-2 border-red-600"></div>
          <p className="ml-2 text-muted-foreground">Loading category details...</p>
        </div>
      </div>
    );
  }

  if (!category) {
    return (
      <div className="container mx-auto py-6">
        <div className="text-center py-12">
          <AlertCircle className="mx-auto h-12 w-12 text-muted-foreground" />
          <h3 className="mt-2 text-sm font-medium">Category not found</h3>
          <p className="mt-1 text-sm text-muted-foreground">
            The category you&apos;re looking for doesn&apos;t exist or has been deleted.
          </p>
          <div className="mt-6">
            <Button asChild>
              <Link href="/dashboard/categories">
                <ArrowLeft className="mr-2 h-4 w-4" />
                Back to Categories
              </Link>
            </Button>
          </div>
        </div>
      </div>
    );
  }

  return (
    <div className="container mx-auto py-6 space-y-6">
      {}
      <div className="flex items-center justify-between">
        <div className="flex items-center space-x-4">
          <Button variant="ghost" size="icon" onClick={() => router.back()}>
            <ArrowLeft className="h-4 w-4" />
          </Button>
          <div>
            <h1 className="text-3xl font-bold tracking-tight">{category.name}</h1>
            <p className="text-muted-foreground">Category details and information</p>
          </div>
        </div>
        <div className="flex items-center space-x-2">
          <Button variant="outline" asChild>
            <Link href={`/dashboard/categories/${category.uuid}/edit`}>
              <Edit className="mr-2 h-4 w-4" />
              Edit Category
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
          <Card>
            <CardHeader>
              <CardTitle>Basic Information</CardTitle>
              <CardDescription>Category details and metadata</CardDescription>
            </CardHeader>
            <CardContent className="space-y-6">
              <div className="grid gap-6 md:grid-cols-2">
                <div>
                  <label className="text-sm font-medium text-muted-foreground">Category Name</label>
                  <p className="text-lg font-medium">{category.name}</p>
                </div>
                <div>
                  <label className="text-sm font-medium text-muted-foreground">URL Slug</label>
                  <p className="text-lg font-mono">/{category.slug}</p>
                </div>
              </div>

              <div>
                <label className="text-sm font-medium text-muted-foreground">Description</label>
                <p className="text-base mt-1">
                  {category.description || 'No description provided.'}
                </p>
              </div>

            </CardContent>
          </Card>
        </div>

        {}
        <div className="space-y-6">
          <Card>
            <CardHeader>
              <CardTitle>Category Status</CardTitle>
            </CardHeader>
            <CardContent className="space-y-4">
              <div className="flex items-center justify-between">
                <span className="text-sm font-medium">Status</span>
                <Badge
                  variant="secondary"
                  className={category.isActive 
                    ? "bg-green-100 text-green-800" 
                    : "bg-red-100 text-red-800"
                  }
                >
                  {category.isActive ? 'Active' : 'Inactive'}
                </Badge>
              </div>
              
              <div className="flex items-center justify-between">
                <span className="text-sm font-medium">Products Count</span>
                <div className="flex items-center space-x-1">
                  <Package className="h-4 w-4 text-muted-foreground" />
                  <span className="font-medium">{category.productsCount}</span>
                </div>
              </div>
            </CardContent>
          </Card>

          <Card>
            <CardHeader>
              <CardTitle>Category Details</CardTitle>
            </CardHeader>
            <CardContent className="space-y-4">
              <div className="flex items-center justify-between">
                <span className="text-sm font-medium">Category ID</span>
                <div className="flex items-center space-x-1">
                  <Hash className="h-4 w-4 text-muted-foreground" />
                  <span className="font-mono">{category.slug}</span>
                </div>
              </div>


            </CardContent>
          </Card>

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
                  {formatDate(category.createdAt)}
                </p>
              </div>

              <div>
                <div className="flex items-center space-x-1 mb-1">
                  <Calendar className="h-4 w-4 text-muted-foreground" />
                  <span className="text-sm font-medium">Last Updated</span>
                </div>
                <p className="text-sm text-muted-foreground">
                  {formatDate(category.updatedAt)}
                </p>
              </div>
            </CardContent>
          </Card>
        </div>
      </div>
    </div>
  );
}