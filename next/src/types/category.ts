export interface Category {
  uuid: string;
  name: string;
  slug: string;
  description?: string;
  isActive: boolean;
  createdAt: string;
  updatedAt: string;
  productsCount: number;
}

export interface CategoryFormData {
  name: string;
  description?: string;
  isActive: boolean;
}

export interface CategoryFilters {
  search?: string;
  isActive?: boolean | null;
  sortBy?: 'name' | 'createdAt' | 'updatedAt' | 'productsCount';
  sortOrder?: 'asc' | 'desc';
}

export interface CategoryStats {
  totalCategories: number;
  activeCategories: number;
  inactiveCategories: number;
  categoriesWithProducts: number;
}