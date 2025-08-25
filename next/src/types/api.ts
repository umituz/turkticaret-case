import { MoneyInfo } from './money';

export interface BaseApiEntity {
  uuid: string;
  created_at: string;
  updated_at: string;
}

export interface NamedEntity {
  name: string;
  slug: string;
  is_active: boolean;
}

export interface ApiProduct extends BaseApiEntity, NamedEntity {
  description: string;
  sku: string;
  price: MoneyInfo;
  stock_quantity: number;
  image_path: string | null;
  is_featured: boolean;
  category_uuid: string;
  category?: ApiCategory;
}

export interface ApiCategory extends BaseApiEntity, NamedEntity {
  description: string | null;
}

export interface ApiOrder extends BaseApiEntity {
  user_uuid: string;
  total_amount: MoneyInfo;
  status: string;
  notes: string | null;
  order_items?: ApiOrderItem[];
  user?: ApiUser;
}

export interface ApiOrderItem extends BaseApiEntity {
  order_uuid: string;
  product_uuid: string;
  quantity: number;
  unit_price: MoneyInfo;
  total_price: MoneyInfo;
  product?: ApiProduct;
}

export interface ApiCart extends BaseApiEntity {
  user_uuid: string;
  cart_items?: ApiCartItem[];
}

export interface ApiCartItem extends BaseApiEntity {
  cart_uuid: string;
  product_uuid: string;
  quantity: number;
  product?: ApiProduct;
}

export interface ApiUser extends BaseApiEntity {
  name: string;
  email: string;
  role?: string;
}

export interface ApiPaginationMeta {
  current_page: number;
  from: number;
  last_page: number;
  per_page: number;
  to: number;
  total: number;
}

export interface ApiResponse<T> {
  success: boolean;
  message: string;
  errors: string[];
  data: T;
  meta?: ApiPaginationMeta;
}

export interface ApiAuthResponse {
  access_token: string;
  token_type: string;
  expires_in?: number;
  user: ApiUser;
}

export interface ProductFilters {
  page?: number;
  per_page?: number;
  limit?: number;
  category_uuid?: string;
  min_price?: number;
  max_price?: number;
  search?: string;
  is_active?: boolean;
  isFeatured?: boolean;
  sort_by?: string;
  sort_order?: 'asc' | 'desc';
}