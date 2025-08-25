'use client';

import { useState, useEffect, useCallback, useRef } from 'react';
import { useParams, useRouter } from 'next/navigation';
import { CategoryForm } from '@/components/admin/CategoryForm';
import { useToast } from '@/hooks/use-toast';
import { getCategoryByUuid } from '@/services/categoryService';
import { Category } from '@/types/category';
import { AlertCircle } from 'lucide-react';

export default function EditCategoryPage() {
  const params = useParams();
  const router = useRouter();
  const { toast } = useToast();
  const toastRef = useRef(toast);
  const [category, setCategory] = useState<Category | null>(null);
  const [loading, setLoading] = useState(true);

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
            The category you&apos;re trying to edit doesn&apos;t exist or has been deleted.
          </p>
        </div>
      </div>
    );
  }

  return (
    <div className="container mx-auto py-6">
      <CategoryForm category={category} mode="edit" />
    </div>
  );
}