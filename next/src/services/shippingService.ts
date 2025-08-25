import { BaseService } from './BaseService';
import { MoneyInfo } from '@/types/money';

export interface ShippingMethod {
  uuid: string;
  name: string;
  description: string;
  price: MoneyInfo;
  delivery_time: string;
  min_delivery_days: number;
  max_delivery_days: number;
  is_active: boolean;
  sort_order: number;
  created_at: string;
  updated_at: string;
}

class ShippingService extends BaseService<ShippingMethod> {
  protected endpoint = 'shipping/methods';

  async getShippingMethods(): Promise<ShippingMethod[]> {
    const result = await this.getAll();
    return result.items;
  }
}

export const shippingService = new ShippingService();


export const getShippingMethods = () => shippingService.getShippingMethods();