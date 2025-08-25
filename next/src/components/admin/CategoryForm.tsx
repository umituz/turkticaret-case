'use client';

import { useState, useEffect, useRef } from 'react';
import { useRouter } from 'next/navigation';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Switch } from '@/components/ui/switch';
import { useToast } from '@/hooks/use-toast';
import { createCategory, updateCategory } from '@/services/categoryService';
import { Category, CategoryFormData } from '@/types/category';
import { ArrowLeft, Save, X } from 'lucide-react';

interface CategoryFormProps {
  category?: Category | null;
  mode: 'create' | 'edit';
}

export const CategoryForm = ({ category, mode }: CategoryFormProps) => {
  const router = useRouter();
  const { toast } = useToast();
  const toastRef = useRef(toast);
  const [loading, setLoading] = useState(false);
  const [formData, setFormData] = useState<CategoryFormData>({
    name: '',
    description: '',
    isActive: true,
  });
  const [errors, setErrors] = useState<Record<string, string>>({});

  
  useEffect(() => {
    toastRef.current = toast;
  }, [toast]);


  
  useEffect(() => {
    if (category && mode === 'edit') {
      setFormData({
        name: category.name,
        description: category.description || '',
        isActive: category.isActive,
      });
    }
  }, [category, mode]); 

  const validateForm = (): boolean => {
    const newErrors: Record<string, string> = {};

    if (!formData.name.trim()) {
      newErrors.name = 'Category name is required';
    } else if (formData.name.trim().length < 2) {
      newErrors.name = 'Category name must be at least 2 characters';
    } else if (formData.name.trim().length > 50) {
      newErrors.name = 'Category name must be less than 50 characters';
    }

    if (formData.description && formData.description.length > 200) {
      newErrors.description = 'Description must be less than 200 characters';
    }


    setErrors(newErrors);
    return Object.keys(newErrors).length === 0;
  };


  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    
    if (!validateForm()) {
      return;
    }

    try {
      setLoading(true);
      
      if (mode === 'create') {
        await createCategory(formData);
        toastRef.current({
          title: 'Success!',
          description: 'Category created successfully.',
        });
      } else {
        if (!category) return;
        await updateCategory(category.uuid, formData);
        toastRef.current({
          title: 'Success!',
          description: 'Category updated successfully.',
        });
      }
      
      router.push('/dashboard/categories');
    } catch (error) {
      toastRef.current({
        title: 'Error!',
        description: error instanceof Error ? error.message : `Failed to ${mode} category.`,
        variant: 'destructive',
      });
    } finally {
      setLoading(false);
    }
  };

  const handleNameChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    const name = e.target.value;
    setFormData(prev => ({ ...prev, name }));
    
    
    if (errors.name) {
      setErrors(prev => ({ ...prev, name: '' }));
    }
  };


  return (
    <div className="space-y-6">
      {}
      <div className="flex items-center space-x-4">
        <Button variant="ghost" size="icon" onClick={() => router.back()}>
          <ArrowLeft className="h-4 w-4" />
        </Button>
        <div>
          <h1 className="text-3xl font-bold tracking-tight">
            {mode === 'create' ? 'Create Category' : 'Edit Category'}
          </h1>
          <p className="text-muted-foreground">
            {mode === 'create' 
              ? 'Add a new product category to organize your inventory' 
              : 'Update category information and settings'
            }
          </p>
        </div>
      </div>

      {}
      <form onSubmit={handleSubmit} className="space-y-6">
        <div className="grid gap-6 lg:grid-cols-3">
          {}
          <div className="lg:col-span-2 space-y-6">
            <Card>
              <CardHeader>
                <CardTitle>Basic Information</CardTitle>
                <CardDescription>
                  Enter the basic details for the category
                </CardDescription>
              </CardHeader>
              <CardContent className="space-y-4">
                <div className="space-y-2">
                  <Label htmlFor="name">Category Name *</Label>
                  <Input
                    id="name"
                    value={formData.name}
                    onChange={handleNameChange}
                    placeholder="e.g., Electronics, Clothing, Books"
                    className={errors.name ? 'border-red-500' : ''}
                  />
                  {errors.name && (
                    <p className="text-sm text-red-600">{errors.name}</p>
                  )}
                </div>

                <div className="space-y-2">
                  <Label htmlFor="description">Description</Label>
                  <Textarea
                    id="description"
                    value={formData.description}
                    onChange={(e) => setFormData(prev => ({ ...prev, description: e.target.value }))}
                    placeholder="Brief description of the category..."
                    rows={3}
                    className={errors.description ? 'border-red-500' : ''}
                  />
                  {errors.description && (
                    <p className="text-sm text-red-600">{errors.description}</p>
                  )}
                  <p className="text-sm text-muted-foreground">
                    {formData.description?.length || 0}/200 characters
                  </p>
                </div>

              </CardContent>
            </Card>
          </div>

          {}
          <div className="space-y-6">
            <Card>
              <CardHeader>
                <CardTitle>Category Settings</CardTitle>
              </CardHeader>
              <CardContent className="space-y-4">

                <div className="flex items-center justify-between">
                  <div className="space-y-0.5">
                    <Label htmlFor="isActive">Active Status</Label>
                    <p className="text-sm text-muted-foreground">
                      Controls whether this category is visible to customers
                    </p>
                  </div>
                  <Switch
                    id="isActive"
                    checked={formData.isActive}
                    onCheckedChange={(checked) => setFormData(prev => ({ ...prev, isActive: checked }))}
                  />
                </div>
              </CardContent>
            </Card>

            {}
            <Card>
              <CardContent className="pt-6">
                <div className="flex flex-col space-y-2">
                  <Button type="submit" disabled={loading} className="w-full">
                    <Save className="mr-2 h-4 w-4" />
                    {loading 
                      ? (mode === 'create' ? 'Creating...' : 'Updating...')
                      : (mode === 'create' ? 'Create Category' : 'Update Category')
                    }
                  </Button>
                  <Button 
                    type="button" 
                    variant="outline" 
                    onClick={() => router.back()}
                    className="w-full"
                    disabled={loading}
                  >
                    <X className="mr-2 h-4 w-4" />
                    Cancel
                  </Button>
                </div>
              </CardContent>
            </Card>
          </div>
        </div>
      </form>
    </div>
  );
};