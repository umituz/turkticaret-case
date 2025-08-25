import { Category, CategoryFormData, CategoryStats } from '@/types/category';
import { ApiCategory, ApiResponse } from '@/types/api';
import { BaseService } from './BaseService';
import { apiClient } from '@/lib/api';

interface CategoryFilters {
  search?: string;
  isActive?: boolean;
}

class CategoryService extends BaseService<Category, ApiCategory, CategoryFilters> {
  protected endpoint = 'categories';

  protected mapFromApi(apiCategory: ApiCategory): Category {
    return {
      uuid: apiCategory.uuid,
      name: apiCategory.name,
      slug: apiCategory.slug,
      description: apiCategory.description || '',
      isActive: apiCategory.is_active,
      productsCount: 0,
      createdAt: apiCategory.created_at,
      updatedAt: apiCategory.updated_at
    };
  }

  protected mapToApi(category: Partial<Category>): Partial<ApiCategory> {
    return {
      name: category.name,
      slug: category.slug,
      description: category.description,
      is_active: category.isActive
    };
  }
}

export const categoryService = new CategoryService();

export const getAllCategories = async (filters?: CategoryFilters) => {
  const result = await categoryService.getAll(filters);
  return result.items;
};

export const getCategoryByUuid = (uuid: string) => categoryService.getByUuid(uuid);
export const createCategory = (data: CategoryFormData) => categoryService.create(data as Partial<Category>);
export const updateCategory = (uuid: string, data: CategoryFormData) => categoryService.update(uuid, data as Partial<Category>);
export const deleteCategory = (uuid: string) => categoryService.delete(uuid);

export const getCategoryStats = async (): Promise<CategoryStats> => {
  const response = await apiClient.get<ApiResponse<{
    total_categories: number;
    active_categories: number;
    inactive_categories: number;
    categories_with_products: number;
  }>>('categories/stats');
  
  
  return {
    totalCategories: response.data.total_categories,
    activeCategories: response.data.active_categories,
    inactiveCategories: response.data.inactive_categories,
    categoriesWithProducts: response.data.categories_with_products,
  };
};


