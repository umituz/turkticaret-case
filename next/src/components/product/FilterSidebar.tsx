'use client';

import { useState, useEffect } from 'react';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Separator } from '@/components/ui/separator';
import { Badge } from '@/components/ui/badge';
import { X } from 'lucide-react';
import { getAllCategories } from '@/services/categoryService';

import type { ProductFilters } from '@/types/api';
import type { Category } from '@/types/category';

interface FilterSidebarProps {
  filters: ProductFilters;
  onFiltersChange: (filters: ProductFilters) => void;
  onClearFilters: () => void;
  hideCategories?: boolean;
}

export const FilterSidebar = ({ filters, onFiltersChange, onClearFilters }: FilterSidebarProps) => {
  const [priceRange, setPriceRange] = useState({
    min: filters.min_price?.toString() || '',
    max: filters.max_price?.toString() || ''
  });
  const [categories, setCategories] = useState<Category[]>([]);
  const [loadingCategories, setLoadingCategories] = useState(true);

  useEffect(() => {
    const fetchCategories = async () => {
      try {
        const data = await getAllCategories();
        setCategories(data);
      } catch (error) {
        console.error('Failed to fetch categories:', error);
      } finally {
        setLoadingCategories(false);
      }
    };
    fetchCategories();
  }, []);

  const handleCategoryChange = (categoryUuid: string) => {
    onFiltersChange({
      ...filters,
      category_uuid: filters.category_uuid === categoryUuid ? undefined : categoryUuid
    });
  };

  const handlePriceChange = () => {
    onFiltersChange({
      ...filters,
      min_price: priceRange.min ? parseFloat(priceRange.min) : undefined,
      max_price: priceRange.max ? parseFloat(priceRange.max) : undefined
    });
  };

  const getActiveFiltersCount = () => {
    let count = 0;
    if (filters.category_uuid) count++;
    if (filters.min_price || filters.max_price) count++;
    if (filters.search) count++;
    return count;
  };

  const selectedCategory = categories.find(cat => cat.uuid.toString() === filters.category_uuid);

  return (
    <Card className="border-border/50">
      <CardHeader>
        <div className="flex items-center justify-between">
          <CardTitle className="text-lg">Filters</CardTitle>
          {getActiveFiltersCount() > 0 && (
            <Button
              variant="outline"
              size="sm"
              onClick={onClearFilters}
              className="text-xs"
            >
              <X className="w-3 h-3 mr-1" />
              Clear
            </Button>
          )}
        </div>
      </CardHeader>
      
      <CardContent className="space-y-6">
        {}
        {getActiveFiltersCount() > 0 && (
          <div>
            <Label className="text-sm font-medium mb-2 block">Active Filters</Label>
            <div className="flex flex-wrap gap-2">
              {selectedCategory && (
                <Badge variant="secondary" className="flex items-center gap-1">
                  {selectedCategory.name}
                  <X 
                    className="w-3 h-3 cursor-pointer" 
                    onClick={() => handleCategoryChange(selectedCategory.uuid)}
                  />
                </Badge>
              )}
              {(filters.min_price || filters.max_price) && (
                <Badge variant="secondary" className="flex items-center gap-1">
                  Price: {filters.min_price || '0'} - 
                  {filters.max_price || '∞'}
                  <X 
                    className="w-3 h-3 cursor-pointer" 
                    onClick={() => {
                      setPriceRange({ min: '', max: '' });
                      onFiltersChange({ ...filters, min_price: undefined, max_price: undefined });
                    }}
                  />
                </Badge>
              )}
              {filters.search && (
                <Badge variant="secondary" className="flex items-center gap-1">
                  &quot;{filters.search}&quot;
                  <X 
                    className="w-3 h-3 cursor-pointer" 
                    onClick={() => onFiltersChange({ ...filters, search: undefined })}
                  />
                </Badge>
              )}
            </div>
            <Separator className="mt-4" />
          </div>
        )}

        {}
        <div>
          <Label className="text-sm font-medium mb-3 block">Categories</Label>
          <div className="space-y-2">
            {loadingCategories ? (
              <div className="space-y-2">
                {[...Array(5)].map((_, i) => (
                  <div key={i} className="h-12 bg-gray-200 rounded animate-pulse" />
                ))}
              </div>
            ) : categories.length === 0 ? (
              <div className="text-center py-4 text-muted-foreground">
                <p className="text-sm">Categories unavailable</p>
              </div>
            ) : (
              categories.map((category) => (
                <Button
                  key={category.uuid}
                  variant={filters.category_uuid === category.uuid.toString() ? "default" : "ghost"}
                  className="w-full justify-start text-sm h-auto py-2"
                  onClick={() => handleCategoryChange(category.uuid)}
                >
                  <div className="text-left">
                    <div className="font-medium">{category.name}</div>
                    <div className="text-xs text-muted-foreground">{category.description}</div>
                  </div>
                </Button>
              ))
            )}
          </div>
        </div>

        <Separator />

        {}
        <div>
          <Label className="text-sm font-medium mb-3 block">Price Range</Label>
          <div className="space-y-3">
            <div className="grid grid-cols-2 gap-2">
              <div>
                <Label htmlFor="min-price" className="text-xs">Min</Label>
                <Input
                  id="min-price"
                  type="number"
                  placeholder="0"
                  value={priceRange.min}
                  onChange={(e) => setPriceRange(prev => ({ ...prev, min: e.target.value }))}
                  className="text-sm"
                />
              </div>
              <div>
                <Label htmlFor="max-price" className="text-xs">Max</Label>
                <Input
                  id="max-price"
                  type="number"
                  placeholder="∞"
                  value={priceRange.max}
                  onChange={(e) => setPriceRange(prev => ({ ...prev, max: e.target.value }))}
                  className="text-sm"
                />
              </div>
            </div>
            <Button
              variant="outline"
              size="sm"
              onClick={handlePriceChange}
              className="w-full"
            >
              Apply Price Filter
            </Button>
          </div>
        </div>

        <Separator />

        {}
        <div>
          <Label className="text-sm font-medium mb-3 block">Quick Price Filters</Label>
          <div className="grid grid-cols-1 gap-2">
            {[
              { label: '0 - 100', min: 0, max: 100 },
              { label: '100 - 200', min: 100, max: 200 },
              { label: '200 - 500', min: 200, max: 500 },
              { label: '500+', min: 500, max: undefined }
            ].map((range) => (
              <Button
                key={range.label}
                variant="outline"
                size="sm"
                onClick={() => {
                  setPriceRange({ 
                    min: range.min.toString(), 
                    max: range.max ? range.max.toString() : '' 
                  });
                  onFiltersChange({
                    ...filters,
                    min_price: range.min,
                    max_price: range.max
                  });
                }}
                className="justify-start text-sm"
              >
                {range.label}
              </Button>
            ))}
          </div>
        </div>
      </CardContent>
    </Card>
  );
};