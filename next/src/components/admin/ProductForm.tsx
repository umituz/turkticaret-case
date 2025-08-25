'use client';

import { useState, useEffect, useRef } from 'react';
import { useRouter } from 'next/navigation';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select';
import { Switch } from '@/components/ui/switch';
import { Badge } from '@/components/ui/badge';
import { useToast } from '@/hooks/use-toast';
import { createProduct, updateProduct } from '@/services/productService';
import { getAllCategories } from '@/services/categoryService';
import { Product, ProductFormData } from '@/types/product';
import { Category } from '@/types/category';
import Image from 'next/image';
import { ArrowLeft, Save, X, Plus, Trash2, Image as ImageIcon } from 'lucide-react';

interface ProductFormProps {
  product?: Product | null;
  mode: 'create' | 'edit';
}

export const ProductForm = ({ product, mode }: ProductFormProps) => {
  const router = useRouter();
  const { toast } = useToast();
  const toastRef = useRef(toast);
  const [loading, setLoading] = useState(false);
  const [categories, setCategories] = useState<Category[]>([]);
  const [formData, setFormData] = useState<ProductFormData>({
    name: '',
    description: '',
    shortDescription: '',
    price: { raw: 0, formatted: '', formatted_minus: '', type: 'nil' },
    comparePrice: undefined,
    costPrice: undefined,
    sku: '',
    barcode: '',
    trackQuantity: true,
    quantity: 0,
    lowStockThreshold: 5,
    categorySlug: '',
    brand: '',
    weight: undefined,
    images: [],
    tags: [],
    isActive: true,
    isFeatured: false,
    isDigital: false,
    requiresShipping: true,
    taxable: true
  });
  const [errors, setErrors] = useState<Record<string, string>>({});
  const [imageUrl, setImageUrl] = useState('');
  const [tagInput, setTagInput] = useState('');

  
  useEffect(() => {
    toastRef.current = toast;
  }, [toast]);

  useEffect(() => {
    loadCategories();
    
    if (product && mode === 'edit') {
      setFormData({
        name: product.name,
        description: product.description,
        shortDescription: product.shortDescription || '',
        price: product.price,
        comparePrice: product.comparePrice,
        costPrice: product.costPrice,
        sku: product.sku,
        barcode: product.barcode || '',
        trackQuantity: product.trackQuantity,
        quantity: product.quantity,
        lowStockThreshold: product.lowStockThreshold,
        categorySlug: product.categorySlug,
        brand: product.brand || '',
        weight: product.weight,
        images: product.images,
        tags: product.tags,
        isActive: product.isActive,
        isFeatured: product.isFeatured,
        isDigital: product.isDigital,
        requiresShipping: product.requiresShipping,
        taxable: product.taxable
      });
    }
  }, [product, mode]);

  const loadCategories = async () => {
    try {
      const data = await getAllCategories({ isActive: true });
      setCategories(data);
    } catch (error) {
      console.error('Failed to load categories:', error);
    }
  };

  const validateForm = (): boolean => {
    const newErrors: Record<string, string> = {};

    if (!formData.name.trim()) {
      newErrors.name = 'Product name is required';
    } else if (formData.name.trim().length < 2) {
      newErrors.name = 'Product name must be at least 2 characters';
    }

    if (!formData.sku.trim()) {
      newErrors.sku = 'SKU is required';
    }

    if (formData.price.raw <= 0) {
      newErrors.price = 'Price must be greater than 0';
    }

    if (formData.comparePrice && formData.comparePrice.raw <= formData.price.raw) {
      newErrors.comparePrice = 'Compare price must be greater than regular price';
    }

    if (!formData.categorySlug) {
      newErrors.categorySlug = 'Please select a category';
    }

    if (formData.trackQuantity && formData.quantity < 0) {
      newErrors.quantity = 'Quantity cannot be negative';
    }

    if (!formData.description.trim()) {
      newErrors.description = 'Description is required';
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
        await createProduct(formData);
        toastRef.current({
          title: 'Success!',
          description: 'Product created successfully.',
        });
      } else {
        if (!product) return;
        await updateProduct(product.uuid, formData);
        toastRef.current({
          title: 'Success!',
          description: 'Product updated successfully.',
        });
      }
      
      router.push('/dashboard/products');
    } catch (error) {
      toastRef.current({
        title: 'Error!',
        description: error instanceof Error ? error.message : `Failed to ${mode} product.`,
        variant: 'destructive',
      });
    } finally {
      setLoading(false);
    }
  };

  const addImage = () => {
    if (!imageUrl.trim()) return;
    
    const newImage = {
      id: Date.now(),
      url: imageUrl,
      alt: formData.name,
      position: formData.images.length + 1
    };
    
    setFormData(prev => ({
      ...prev,
      images: [...prev.images, newImage]
    }));
    setImageUrl('');
  };

  const removeImage = (imageId: number) => {
    setFormData(prev => ({
      ...prev,
      images: prev.images.filter(img => img.id !== imageId)
    }));
  };

  const addTag = () => {
    if (!tagInput.trim()) return;
    
    const newTag = tagInput.trim().toLowerCase();
    if (!formData.tags.includes(newTag)) {
      setFormData(prev => ({
        ...prev,
        tags: [...prev.tags, newTag]
      }));
    }
    setTagInput('');
  };

  const removeTag = (tag: string) => {
    setFormData(prev => ({
      ...prev,
      tags: prev.tags.filter(t => t !== tag)
    }));
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
            {mode === 'create' ? 'Create Product' : 'Edit Product'}
          </h1>
          <p className="text-muted-foreground">
            {mode === 'create' 
              ? 'Add a new product to your catalog' 
              : 'Update product information and settings'
            }
          </p>
        </div>
      </div>

      {}
      <form onSubmit={handleSubmit} className="space-y-6">
        <div className="grid gap-6 lg:grid-cols-3">
          {}
          <div className="lg:col-span-2 space-y-6">
            {}
            <Card>
              <CardHeader>
                <CardTitle>Basic Information</CardTitle>
                <CardDescription>Enter the basic details for the product</CardDescription>
              </CardHeader>
              <CardContent className="space-y-4">
                <div className="grid gap-4 md:grid-cols-2">
                  <div className="space-y-2">
                    <Label htmlFor="name">Product Name *</Label>
                    <Input
                      id="name"
                      value={formData.name}
                      onChange={(e) => setFormData(prev => ({ ...prev, name: e.target.value }))}
                      placeholder="e.g., iPhone 15 Pro Max"
                      className={errors.name ? 'border-red-500' : ''}
                    />
                    {errors.name && <p className="text-sm text-red-600">{errors.name}</p>}
                  </div>

                  <div className="space-y-2">
                    <Label htmlFor="sku">SKU *</Label>
                    <Input
                      id="sku"
                      value={formData.sku}
                      onChange={(e) => setFormData(prev => ({ ...prev, sku: e.target.value }))}
                      placeholder="e.g., IPH15PM-256-TIT"
                      className={errors.sku ? 'border-red-500' : ''}
                    />
                    {errors.sku && <p className="text-sm text-red-600">{errors.sku}</p>}
                  </div>
                </div>

                <div className="space-y-2">
                  <Label htmlFor="shortDescription">Short Description</Label>
                  <Input
                    id="shortDescription"
                    value={formData.shortDescription}
                    onChange={(e) => setFormData(prev => ({ ...prev, shortDescription: e.target.value }))}
                    placeholder="Brief one-line description"
                  />
                </div>

                <div className="space-y-2">
                  <Label htmlFor="description">Description *</Label>
                  <Textarea
                    id="description"
                    value={formData.description}
                    onChange={(e) => setFormData(prev => ({ ...prev, description: e.target.value }))}
                    placeholder="Detailed product description..."
                    rows={4}
                    className={errors.description ? 'border-red-500' : ''}
                  />
                  {errors.description && <p className="text-sm text-red-600">{errors.description}</p>}
                </div>
              </CardContent>
            </Card>

            {}
            <Card>
              <CardHeader>
                <CardTitle>Pricing</CardTitle>
                <CardDescription>Set product pricing information</CardDescription>
              </CardHeader>
              <CardContent className="space-y-4">
                <div className="grid gap-4 md:grid-cols-3">
                  <div className="space-y-2">
                    <Label htmlFor="price">Price *</Label>
                    <Input
                      id="price"
                      type="number"
                      min="0"
                      step="0.01"
                      value={formData.price.raw}
                      onChange={(e) => setFormData(prev => ({ ...prev, price: { ...prev.price, raw: parseFloat(e.target.value) || 0 } }))}
                      placeholder="0.00"
                      className={errors.price ? 'border-red-500' : ''}
                    />
                    {errors.price && <p className="text-sm text-red-600">{errors.price}</p>}
                  </div>

                  <div className="space-y-2">
                    <Label htmlFor="comparePrice">Compare Price</Label>
                    <Input
                      id="comparePrice"
                      type="number"
                      min="0"
                      step="0.01"
                      value={formData.comparePrice?.raw || ''}
                      onChange={(e) => setFormData(prev => ({ ...prev, comparePrice: e.target.value ? { raw: parseFloat(e.target.value), formatted: '', formatted_minus: '', type: 'positive' } : undefined }))}
                      placeholder="0.00"
                      className={errors.comparePrice ? 'border-red-500' : ''}
                    />
                    {errors.comparePrice && <p className="text-sm text-red-600">{errors.comparePrice}</p>}
                  </div>

                  <div className="space-y-2">
                    <Label htmlFor="costPrice">Cost Price</Label>
                    <Input
                      id="costPrice"
                      type="number"
                      min="0"
                      step="0.01"
                      value={formData.costPrice?.raw || ''}
                      onChange={(e) => setFormData(prev => ({ ...prev, costPrice: e.target.value ? { raw: parseFloat(e.target.value), formatted: '', formatted_minus: '', type: 'positive' } : undefined }))}
                      placeholder="0.00"
                    />
                  </div>
                </div>
              </CardContent>
            </Card>

            {}
            <Card>
              <CardHeader>
                <CardTitle>Product Images</CardTitle>
                <CardDescription>Add product images</CardDescription>
              </CardHeader>
              <CardContent className="space-y-4">
                <div className="flex space-x-2">
                  <Input
                    placeholder="Image URL"
                    value={imageUrl}
                    onChange={(e) => setImageUrl(e.target.value)}
                  />
                  <Button type="button" onClick={addImage} variant="outline">
                    <Plus className="h-4 w-4" />
                  </Button>
                </div>

                {formData.images.length > 0 && (
                  <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                    {formData.images.map((image) => (
                      <div key={image.id} className="relative group">
                        <div className="aspect-square bg-muted rounded-lg overflow-hidden">
                          <Image
                            src={image.url}
                            alt={image.alt || 'Product image'}
                            width={200}
                            height={200}
                            className="w-full h-full object-cover"
                            onError={(e) => {
                              const target = e.target as HTMLImageElement;
                              target.src = '';
                              target.style.display = 'none';
                              target.parentElement?.classList.add('flex', 'items-center', 'justify-center');
                            }}
                          />
                          <div className="flex items-center justify-center h-full">
                            <ImageIcon className="h-8 w-8 text-muted-foreground" />
                          </div>
                        </div>
                        <Button
                          type="button"
                          variant="destructive"
                          size="icon"
                          className="absolute top-2 right-2 h-6 w-6 opacity-0 group-hover:opacity-100 transition-opacity"
                          onClick={() => removeImage(image.id || 0)}
                        >
                          <Trash2 className="h-3 w-3" />
                        </Button>
                      </div>
                    ))}
                  </div>
                )}
              </CardContent>
            </Card>
          </div>

          {}
          <div className="space-y-6">
            {}
            <Card>
              <CardHeader>
                <CardTitle>Organization</CardTitle>
              </CardHeader>
              <CardContent className="space-y-4">
                <div className="space-y-2">
                  <Label htmlFor="categorySlug">Category *</Label>
                  <Select
                    value={formData.categorySlug}
                    onValueChange={(value) => setFormData(prev => ({ ...prev, categorySlug: value }))}
                  >
                    <SelectTrigger className={errors.categorySlug ? 'border-red-500' : ''}>
                      <SelectValue placeholder="Select category" />
                    </SelectTrigger>
                    <SelectContent>
                      {categories?.map?.((category) => (
                        <SelectItem key={category.uuid} value={category.slug}>
                          {category.name}
                        </SelectItem>
                      ))}
                    </SelectContent>
                  </Select>
                  {errors.categorySlug && <p className="text-sm text-red-600">{errors.categorySlug}</p>}
                </div>

                <div className="space-y-2">
                  <Label htmlFor="brand">Brand</Label>
                  <Input
                    id="brand"
                    value={formData.brand}
                    onChange={(e) => setFormData(prev => ({ ...prev, brand: e.target.value }))}
                    placeholder="e.g., Apple, Samsung"
                  />
                </div>

                <div className="space-y-2">
                  <Label>Tags</Label>
                  <div className="flex space-x-2">
                    <Input
                      placeholder="Add tag"
                      value={tagInput}
                      onChange={(e) => setTagInput(e.target.value)}
                      onKeyDown={(e) => e.key === 'Enter' && (e.preventDefault(), addTag())}
                    />
                    <Button type="button" onClick={addTag} variant="outline">
                      <Plus className="h-4 w-4" />
                    </Button>
                  </div>
                  {formData.tags.length > 0 && (
                    <div className="flex flex-wrap gap-2 mt-2">
                      {formData.tags.map((tag) => (
                        <Badge key={tag} variant="secondary" className="cursor-pointer" onClick={() => removeTag(tag)}>
                          {tag}
                          <X className="ml-1 h-3 w-3" />
                        </Badge>
                      ))}
                    </div>
                  )}
                </div>
              </CardContent>
            </Card>

            {}
            <Card>
              <CardHeader>
                <CardTitle>Inventory</CardTitle>
              </CardHeader>
              <CardContent className="space-y-4">
                <div className="flex items-center justify-between">
                  <div className="space-y-0.5">
                    <Label htmlFor="trackQuantity">Track Quantity</Label>
                    <p className="text-sm text-muted-foreground">
                      Track this product&apos;s inventory
                    </p>
                  </div>
                  <Switch
                    id="trackQuantity"
                    checked={formData.trackQuantity}
                    onCheckedChange={(checked) => setFormData(prev => ({ ...prev, trackQuantity: checked }))}
                  />
                </div>

                {formData.trackQuantity && (
                  <>
                    <div className="space-y-2">
                      <Label htmlFor="quantity">Quantity</Label>
                      <Input
                        id="quantity"
                        type="number"
                        min="0"
                        value={formData.quantity}
                        onChange={(e) => setFormData(prev => ({ ...prev, quantity: parseInt(e.target.value) || 0 }))}
                        className={errors.quantity ? 'border-red-500' : ''}
                      />
                      {errors.quantity && <p className="text-sm text-red-600">{errors.quantity}</p>}
                    </div>

                    <div className="space-y-2">
                      <Label htmlFor="lowStockThreshold">Low Stock Threshold</Label>
                      <Input
                        id="lowStockThreshold"
                        type="number"
                        min="0"
                        value={formData.lowStockThreshold || ''}
                        onChange={(e) => setFormData(prev => ({ ...prev, lowStockThreshold: parseInt(e.target.value) || undefined }))}
                      />
                    </div>
                  </>
                )}

                <div className="space-y-2">
                  <Label htmlFor="barcode">Barcode</Label>
                  <Input
                    id="barcode"
                    value={formData.barcode}
                    onChange={(e) => setFormData(prev => ({ ...prev, barcode: e.target.value }))}
                    placeholder="Product barcode"
                  />
                </div>
              </CardContent>
            </Card>

            {}
            <Card>
              <CardHeader>
                <CardTitle>Settings</CardTitle>
              </CardHeader>
              <CardContent className="space-y-4">
                <div className="flex items-center justify-between">
                  <div className="space-y-0.5">
                    <Label htmlFor="isActive">Active Status</Label>
                    <p className="text-sm text-muted-foreground">
                      Product is visible to customers
                    </p>
                  </div>
                  <Switch
                    id="isActive"
                    checked={formData.isActive}
                    onCheckedChange={(checked) => setFormData(prev => ({ ...prev, isActive: checked }))}
                  />
                </div>

                <div className="flex items-center justify-between">
                  <div className="space-y-0.5">
                    <Label htmlFor="isFeatured">Featured Product</Label>
                    <p className="text-sm text-muted-foreground">
                      Show on homepage and featured sections
                    </p>
                  </div>
                  <Switch
                    id="isFeatured"
                    checked={formData.isFeatured}
                    onCheckedChange={(checked) => setFormData(prev => ({ ...prev, isFeatured: checked }))}
                  />
                </div>

                <div className="flex items-center justify-between">
                  <div className="space-y-0.5">
                    <Label htmlFor="isDigital">Digital Product</Label>
                    <p className="text-sm text-muted-foreground">
                      No physical shipping required
                    </p>
                  </div>
                  <Switch
                    id="isDigital"
                    checked={formData.isDigital}
                    onCheckedChange={(checked) => setFormData(prev => ({ 
                      ...prev, 
                      isDigital: checked,
                      requiresShipping: !checked 
                    }))}
                  />
                </div>

                <div className="flex items-center justify-between">
                  <div className="space-y-0.5">
                    <Label htmlFor="taxable">Taxable</Label>
                    <p className="text-sm text-muted-foreground">
                      Apply tax to this product
                    </p>
                  </div>
                  <Switch
                    id="taxable"
                    checked={formData.taxable}
                    onCheckedChange={(checked) => setFormData(prev => ({ ...prev, taxable: checked }))}
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
                      : (mode === 'create' ? 'Create Product' : 'Update Product')
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