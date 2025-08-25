import { Product, ProductStats } from '@/types/product';
import { Category, CategoryStats } from '@/types/category';




export interface StockStatus {
  status: 'unlimited' | 'in-stock' | 'low-stock' | 'out-of-stock';
  color: string;
  text: string;
}

export const getStockStatus = (product: Product): StockStatus => {
  if (!product.trackQuantity) {
    return { 
      status: 'unlimited', 
      color: 'bg-blue-100 text-blue-800', 
      text: 'Unlimited' 
    };
  }
  
  if (product.quantity === 0) {
    return { 
      status: 'out-of-stock', 
      color: 'bg-red-100 text-red-800', 
      text: 'Out of Stock' 
    };
  }
  
  if (product.lowStockThreshold && product.quantity <= product.lowStockThreshold) {
    return { 
      status: 'low-stock', 
      color: 'bg-yellow-100 text-yellow-800', 
      text: 'Low Stock' 
    };
  }
  
  return { 
    status: 'in-stock', 
    color: 'bg-green-100 text-green-800', 
    text: 'In Stock' 
  };
};

export const calculateProductStats = (products: Product[]): ProductStats => {
  if (!Array.isArray(products) || products.length === 0) {
    return {
      totalProducts: 0,
      activeProducts: 0,
      inactiveProducts: 0,
      featuredProducts: 0,
      outOfStockProducts: 0,
      lowStockProducts: 0,
      totalValue: { raw: 0, formatted: '', formatted_minus: '', type: 'nil' as const },
      averagePrice: { raw: 0, formatted: '', formatted_minus: '', type: 'nil' as const }
    };
  }

  const stats = products.reduce((acc, product) => {
    acc.totalProducts++;
    
    if (product.isActive) {
      acc.activeProducts++;
    } else {
      acc.inactiveProducts++;
    }
    
    if (product.isFeatured) {
      acc.featuredProducts++;
    }
    
    if (product.trackQuantity) {
      if (product.quantity === 0) {
        acc.outOfStockProducts++;
      } else if (product.lowStockThreshold && product.quantity <= product.lowStockThreshold) {
        acc.lowStockProducts++;
      }
    }
    
    return acc;
  }, {
    totalProducts: 0,
    activeProducts: 0,
    inactiveProducts: 0,
    featuredProducts: 0,
    outOfStockProducts: 0,
    lowStockProducts: 0,
    totalValue: { raw: 0, formatted: '', formatted_minus: '', type: 'nil' as const },
  });

  return {
    totalProducts: stats.totalProducts,
    activeProducts: stats.activeProducts,
    inactiveProducts: stats.inactiveProducts,
    featuredProducts: stats.featuredProducts,
    outOfStockProducts: stats.outOfStockProducts,
    lowStockProducts: stats.lowStockProducts,
    totalValue: stats.totalValue,
    averagePrice: { raw: 0, formatted: '', formatted_minus: '', type: 'nil' as const }
  };
};

export const calculateCategoryStats = (categories: Category[]): CategoryStats => {
  if (!Array.isArray(categories) || categories.length === 0) {
    return {
      totalCategories: 0,
      activeCategories: 0,
      inactiveCategories: 0,
      categoriesWithProducts: 0
    };
  }

  const stats = categories.reduce((acc, category) => {
    acc.totalCategories++;
    
    if (category.isActive) {
      acc.activeCategories++;
    } else {
      acc.inactiveCategories++;
    }
    
    if (category.productsCount > 0) {
      acc.categoriesWithProducts++;
    }
    
    return acc;
  }, {
    totalCategories: 0,
    activeCategories: 0,
    inactiveCategories: 0,
    categoriesWithProducts: 0
  });

  return stats;
};

export const confirmDelete = (itemName: string, name: string): boolean => {
  return confirm(`Are you sure you want to delete "${name}"? This action cannot be undone.`);
};

export const formatErrorMessage = (error: unknown, context: string): string => {
  if (error instanceof Error) {
    return error.message;
  }
  return `An error occurred while ${context}`;
};

export const sanitizeSearchTerm = (term: string): string => {
  return term.trim().toLowerCase();
};

export interface PaginationConfig {
  page: number;
  perPage: number;
  total: number;
}

export const calculatePagination = (config: PaginationConfig) => {
  const totalPages = Math.ceil(config.total / config.perPage);
  const hasNext = config.page < totalPages;
  const hasPrev = config.page > 1;
  const startItem = (config.page - 1) * config.perPage + 1;
  const endItem = Math.min(config.page * config.perPage, config.total);

  return {
    totalPages,
    hasNext,
    hasPrev,
    startItem,
    endItem,
    showing: `${startItem}-${endItem} of ${config.total}`
  };
};

export const getStatusBadgeVariant = (isActive: boolean): {
  variant: 'default' | 'secondary' | 'destructive' | 'outline';
  text: string;
} => {
  return isActive 
    ? { variant: 'default', text: 'Active' }
    : { variant: 'secondary', text: 'Inactive' };
};