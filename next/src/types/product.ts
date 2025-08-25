import { MoneyInfo } from './money';

export interface Product {
  uuid: string;
  name: string;
  slug: string;
  description: string;
  shortDescription?: string;
  price: MoneyInfo;
  comparePrice?: MoneyInfo;
  costPrice?: MoneyInfo;
  sku: string;
  barcode?: string;
  trackQuantity: boolean;
  quantity: number;
  lowStockThreshold?: number;
  categorySlug?: string;
  categoryName?: string;
  brand?: string;
  weight?: number;
  images: ProductImage[];
  tags: string[];
  isActive: boolean;
  isFeatured: boolean;
  isDigital: boolean;
  requiresShipping: boolean;
  taxable: boolean;
  createdAt: string;
  updatedAt: string;
  publishedAt?: string;
}

export interface ProductImage {
  id?: number;
  url: string;
  alt?: string;
}


export interface ProductFormData {
  name: string;
  description: string;
  shortDescription?: string;
  price: MoneyInfo;
  comparePrice?: MoneyInfo;
  costPrice?: MoneyInfo;
  sku: string;
  barcode?: string;
  trackQuantity: boolean;
  quantity: number;
  lowStockThreshold?: number;
  categorySlug?: string;
  categoryUuid?: string;
  brand?: string;
  weight?: number;
  images: ProductImage[];
  tags: string[];
  isActive: boolean;
  isFeatured: boolean;
  isDigital: boolean;
  requiresShipping: boolean;
  taxable: boolean;
}


export interface ProductStats {
  totalProducts: number;
  activeProducts: number;
  inactiveProducts: number;
  featuredProducts: number;
  outOfStockProducts: number;
  lowStockProducts: number;
  totalValue: MoneyInfo;
  averagePrice: MoneyInfo;
}