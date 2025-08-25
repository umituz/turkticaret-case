import { Product, ProductFormData, ProductStats } from '@/types/product';
import { ProductFilters, ApiProduct } from '@/types/api';
import { BaseService } from './BaseService';
import { transformFiltersForAPI } from '@/lib/filter-utils';
import { MoneyInfo } from '@/types/money';

class ProductService extends BaseService<Product, ApiProduct, ProductFilters> {
  protected endpoint = 'products';

  protected mapFromApi(apiProduct: ApiProduct): Product {
    return {
      uuid: apiProduct.uuid,
      name: apiProduct.name,
      slug: apiProduct.slug,
      description: apiProduct.description,
      price: apiProduct.price,
      sku: apiProduct.sku,
      trackQuantity: true,
      quantity: apiProduct.stock_quantity,
      categorySlug: apiProduct.category?.slug,
      categoryName: apiProduct.category?.name,
      images: apiProduct.image_path ? [{ url: apiProduct.image_path, alt: apiProduct.name }] : [],
      tags: [],
      isActive: apiProduct.is_active,
      isFeatured: apiProduct.is_featured,
      isDigital: false,
      requiresShipping: true,
      taxable: true,
      createdAt: apiProduct.created_at,
      updatedAt: apiProduct.updated_at
    };
  }

  protected mapToApi(product: Partial<Product>): Partial<ApiProduct> {
    return {
      name: product.name,
      slug: product.slug,
      description: product.description,
      price: product.price,
      sku: product.sku,
      stock_quantity: product.quantity,
      is_active: product.isActive,
      is_featured: product.isFeatured
    };
  }

  async getProductStats(): Promise<ProductStats> {
    const response = await this.apiGet<{
      total_products: number;
      active_products: number;
      inactive_products: number;
      featured_products: number;
      out_of_stock_products: number;
      low_stock_products: number;
      total_value: MoneyInfo;
      average_price: MoneyInfo;
    }>('products/stats');
    
    
    return {
      totalProducts: response.total_products,
      activeProducts: response.active_products,
      inactiveProducts: response.inactive_products,
      featuredProducts: response.featured_products,
      outOfStockProducts: response.out_of_stock_products,
      lowStockProducts: response.low_stock_products,
      totalValue: response.total_value,
      averagePrice: response.average_price,
    };
  }

}

export const productService = new ProductService();

export const getAllProducts = async (filters?: ProductFilters) => {
  const apiFilters = transformFiltersForAPI(filters);
  const result = await productService.getAll(apiFilters);
  return { products: result.items, total: result.total };
};

export const getProductByUuid = (uuid: string) => productService.getByUuid(uuid);

export const createProduct = (data: ProductFormData) => productService.create(data as Partial<Product>);
export const updateProduct = (uuid: string, data: ProductFormData) => productService.update(uuid, data as Partial<Product>);
export const deleteProduct = (uuid: string) => productService.delete(uuid);

export const getProductStats = () => productService.getProductStats();

