import { BaseService } from './BaseService';

export interface CreateOrderData {
  shipping_address: string;
  notes?: string;
}

export interface Order {
  uuid: string;
  order_number: string;
  user_uuid: string;
  status: string;
  total_amount: number;
  shipping_address: {
    first_name: string;
    last_name: string;
    company?: string;
    address_line_1: string;
    address_line_2?: string;
    city: string;
    state: string;
    postal_code: string;
    country_uuid: string;
    phone?: string;
  };
  notes?: string;
  created_at: string;
  updated_at: string;
  order_items: Array<{
    uuid: string;
    product_uuid: string;
    quantity: number;
    price: number;
    total: number;
    product?: {
      uuid: string;
      name: string;
      image?: string;
    };
  }>;
}

class CheckoutService extends BaseService<Order> {
  protected endpoint = 'orders';

  async createOrder(data: CreateOrderData): Promise<Order> {
    return this.create(data as unknown as Partial<Order>);
  }
}

export const checkoutService = new CheckoutService();


export const createOrder = (data: CreateOrderData) => checkoutService.createOrder(data);