import { BaseApiEntity } from '@/types/api';
import { BaseService } from './BaseService';
import { MoneyInfo } from '@/types/money';

export interface ApiCartItem extends BaseApiEntity {
  quantity: number;
  unit_price: MoneyInfo;
  total_price: MoneyInfo;
  product: {
    uuid: string;
    name: string;
    sku: string;
    image_path: string | null;
    available_stock: number;
    is_available: boolean;
  };
}

export interface ApiCart extends BaseApiEntity {
  user_uuid: string;
  items: ApiCartItem[];
  total_items: number;
  total_amount: MoneyInfo;
}

export interface CartItem {
  uuid: string;
  productUuid: string;
  name: string;
  price: MoneyInfo;
  salePrice?: MoneyInfo;
  total: MoneyInfo;
  image?: string;
  quantity: number;
  variant?: string;
  size?: string;
  color?: string;
}

interface CartFilters {
  user_uuid?: string;
}

class CartService extends BaseService<CartItem, ApiCartItem, CartFilters> {
  protected endpoint = 'cart';

  protected mapFromApi(apiItem: ApiCartItem): CartItem {
    return {
      uuid: apiItem.uuid,
      productUuid: apiItem.product.uuid,
      name: apiItem.product.name,
      price: apiItem.unit_price,
      total: apiItem.total_price,
      image: apiItem.product.image_path || undefined,
      quantity: apiItem.quantity,
      variant: undefined,
      size: undefined,
      color: undefined,
    };
  }

  async getCart(): Promise<{ items: CartItem[]; total: MoneyInfo; itemCount: number }> {
    const response = await this.apiGet<{ success: boolean; data: ApiCart }>('cart');
    return {
      items: response.data.items.map(this.mapFromApi.bind(this)),
      total: response.data.total_amount,
      itemCount: response.data.total_items
    };
  }

  async addToCart(productUuid: string, quantity: number = 1): Promise<boolean> {
    await this.apiPost('cart/add', { product_uuid: productUuid, quantity });
    return true;
  }

  async updateCartItem(productUuid: string, quantity: number): Promise<boolean> {
    await this.apiPut('cart/update', { product_uuid: productUuid, quantity });
    return true;
  }

  async removeFromCart(itemUuid: string): Promise<boolean> {
    await this.apiDelete(`cart/remove/${itemUuid}`);
    return true;
  }

  async clearCart(): Promise<boolean> {
    await this.apiDelete('cart/clear');
    return true;
  }

}

export const cartService = new CartService();


export const getCart = () => cartService.getCart();
export const addToCart = (productUuid: string, quantity?: number) => cartService.addToCart(productUuid, quantity);
export const updateCartItem = (productUuid: string, quantity: number) => cartService.updateCartItem(productUuid, quantity);
export const removeFromCart = (itemUuid: string) => cartService.removeFromCart(itemUuid);
export const clearCart = () => cartService.clearCart();