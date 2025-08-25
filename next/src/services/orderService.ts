import { apiClient } from '@/lib/api';
import { Order, OrderItem, Address } from '@/types/user';
import { BaseApiEntity } from '@/types/api';
import { BaseService } from './BaseService';
import { MoneyInfo } from '@/types/money';

export interface ApiOrderItem extends BaseApiEntity {
  product_uuid: string;
  product_name: string;
  quantity: number;
  unit_price: MoneyInfo;
  total_price: MoneyInfo;
  product: {
    uuid: string;
    name: string;
    slug: string;
    description: string;
    sku: string;
    price: number;
    stock_quantity: number;
    image_path: string;
    is_active: boolean;
    category_uuid: string;
    created_at: string;
    updated_at: string;
  };
}

export interface ApiOrder extends BaseApiEntity {
  order_number: string;
  user_uuid: string;
  status: string;
  total_amount: MoneyInfo;
  shipping_address: string;
  notes?: string;
  shipped_at?: string;
  delivered_at?: string;
  items: ApiOrderItem[];
  items_count: number;
}

interface OrderFilters {
  page?: number;
  per_page?: number;
  status?: string;
  search?: string;
  date_from?: string;
  date_to?: string;
}

class OrderService extends BaseService<Order, ApiOrder, OrderFilters> {
  protected endpoint = 'orders';

  async getOrders(filters?: OrderFilters): Promise<{ orders: Order[]; meta: { current_page: number; total: number; per_page: number; last_page: number } }> {
    const result = await this.getAll(filters);
    const page = filters?.page || 1;
    const perPage = filters?.per_page || 20;
    return {
      orders: result.items,
      meta: {
        current_page: page,
        total: result.total,
        per_page: perPage,
        last_page: Math.ceil(result.total / perPage)
      }
    };
  }

  async getOrder(orderUuid: string): Promise<Order> {
    const order = await this.getByUuid(orderUuid);
    if (!order) {
      throw new Error('Order not found');
    }
    return order;
  }

  protected mapFromApi(apiOrder: ApiOrder): Order {
    return this.transformOrder(apiOrder);
  }

  protected mapToApi(order: Partial<Order>): Partial<ApiOrder> {
    return {
      user_uuid: order.userUuid,
      total_amount: order.total,
      status: order.status,
      shipping_address: order.shippingAddress?.address1,
      notes: order.paymentMethod
    };
  }

  private transformOrder(apiOrder: ApiOrder): Order {
    const addressParts = (apiOrder.shipping_address || '').split(',').map(part => part.trim());
    const shippingAddress: Address = {
      uuid: apiOrder.uuid + '_shipping',
      userUuid: apiOrder.user_uuid,
      type: 'shipping',
      isDefault: false,
      firstName: addressParts[0] || '',
      lastName: addressParts[1] || '',
      company: '',
      address1: addressParts[2] || apiOrder.shipping_address || '',
      address2: '',
      city: addressParts[3] || '',
      state: addressParts[4] || '',
      postalCode: addressParts[5] || '',
      country: addressParts[6] || 'US',
      phone: '',
      createdAt: apiOrder.created_at,
      updatedAt: apiOrder.updated_at
    };

    const billingAddress: Address = {
      ...shippingAddress,
      uuid: apiOrder.uuid + '_billing',
      type: 'billing'
    };

    return {
      uuid: apiOrder.uuid,
      userUuid: apiOrder.user_uuid,
      orderNumber: apiOrder.order_number,
      status: this.mapStatus(apiOrder.status),
      total: apiOrder.total_amount,
      subtotal: apiOrder.total_amount,
      tax: { raw: 0, formatted: '', formatted_minus: '', type: 'nil' as const },
      shipping: { raw: 0, formatted: '', formatted_minus: '', type: 'nil' as const },
      discount: { raw: 0, formatted: '', formatted_minus: '', type: 'nil' as const },
      currency: 'TRY',
      paymentStatus: 'paid',
      paymentMethod: 'Credit Card',
      shippingAddress,
      billingAddress,
      items: this.transformOrderItems(apiOrder.items),
      createdAt: apiOrder.created_at,
      updatedAt: apiOrder.updated_at,
      shippedAt: apiOrder.shipped_at,
      deliveredAt: apiOrder.delivered_at
    };
  }

  private transformOrderItems(apiItems: ApiOrderItem[]): OrderItem[] {
    return apiItems.map((apiItem) => ({
      uuid: apiItem.uuid,
      orderUuid: apiItem.uuid, 
      productUuid: apiItem.product_uuid,
      productName: apiItem.product_name,
      productSku: apiItem.product.sku,
      productImage: apiItem.product.image_path || undefined,
      quantity: apiItem.quantity,
      price: apiItem.unit_price,
      total: apiItem.total_price
    }));
  }

  private mapStatus(apiStatus: string): Order['status'] {
    const statusMap: Record<string, Order['status']> = {
      'pending': 'pending',
      'processing': 'processing',
      'shipped': 'shipped',
      'delivered': 'delivered',
      'cancelled': 'cancelled',
      'refunded': 'refunded'
    };

    return statusMap[apiStatus] || 'pending';
  }

  async createOrder(orderData: {
    address_id?: string;
    payment_method: string;
    notes?: string;
    name?: string;
    email?: string;
    phone?: string;
    address?: string;
    city?: string;
    postal_code?: string;
    country?: string;
  }): Promise<Order> {
    const response = await apiClient.post<{ success: boolean; data: ApiOrder }>('orders', orderData);
    return this.transformOrder(response.data);
  }

  async updateOrderStatus(orderUuid: string, status: string): Promise<boolean> {
    const response = await apiClient.put<{ success: boolean }>(`orders/${orderUuid}/status`, { status });
    return response.success;
  }

  async getOrderStatusHistory(orderId: string): Promise<OrderStatusHistory> {
    const response = await apiClient.get<{ success: boolean; data: OrderStatusHistory }>(`orders/${orderId}/status/history`);
    return response.data;
  }
}

export interface OrderStatusHistoryItem {
  status: string;
  date: string;
  description: string;
}

export interface OrderStatusHistory {
  order_uuid: string;
  current_status: string;
  history: OrderStatusHistoryItem[];
}

export const orderService = new OrderService();

export const getOrders = (filters?: OrderFilters) => orderService.getOrders(filters);
export const getOrder = (orderUuid: string) => orderService.getOrder(orderUuid);
export const createOrder = (orderData: {
  address_id?: string;
  payment_method: string;
  notes?: string;
  name?: string;
  email?: string;
  phone?: string;
  address?: string;
  city?: string;
  postal_code?: string;
  country?: string;
}) => orderService.createOrder(orderData);
export const updateOrderStatus = (orderUuid: string, status: string) => orderService.updateOrderStatus(orderUuid, status);
export const getOrderStatusHistory = (orderId: string) => orderService.getOrderStatusHistory(orderId);