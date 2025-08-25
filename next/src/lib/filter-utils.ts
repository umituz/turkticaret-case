import type { ProductFilters } from '@/types/api';


export function transformFiltersForAPI(filters?: ProductFilters): Record<string, string | number | boolean> | undefined {
  if (!filters) return undefined;
  
  const apiFilters: Record<string, string | number | boolean> = {};
  
  
  const directFields = ['page', 'per_page', 'limit', 'category_uuid', 'search', 'is_active', 'isFeatured', 'sort_by', 'sort_order'] as const;
  
  directFields.forEach(field => {
    if (filters[field] !== undefined) {
      apiFilters[field] = filters[field];
    }
  });
  
  
  if (filters.min_price !== undefined) {
    apiFilters.min_price = filters.min_price;
  }
  if (filters.max_price !== undefined) {
    apiFilters.max_price = filters.max_price;
  }
  
  return Object.keys(apiFilters).length > 0 ? apiFilters : undefined;
}