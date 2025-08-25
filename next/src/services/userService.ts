import { ApiResponse } from '@/types/api';
import { BaseService } from './BaseService';

export interface UserStats {
  totalOrders: number;
  totalSpent: number;
  averageOrderValue: number;
  memberSince: string;
  lastOrder?: {
    orderNumber: string;
    total: number;
    status: string;
    createdAt: string;
  } | null;
}

interface ApiUserStats {
  total_orders: number;
  total_spent: number;
  average_order_value: number;
  member_since: string;
  last_order?: {
    order_number: string;
    total: number;
    status: string;
    created_at: string;
  } | null;
}

class UserService extends BaseService<UserStats, ApiUserStats> {
  protected endpoint = 'profile';

  protected mapFromApi(apiStats: ApiUserStats): UserStats {
    return {
      totalOrders: apiStats.total_orders,
      totalSpent: apiStats.total_spent,
      averageOrderValue: apiStats.average_order_value,
      memberSince: apiStats.member_since,
      lastOrder: apiStats.last_order ? {
        orderNumber: apiStats.last_order.order_number,
        total: apiStats.last_order.total,
        status: apiStats.last_order.status,
        createdAt: apiStats.last_order.created_at,
      } : null,
    };
  }

  async getUserStats(): Promise<UserStats> {
    const response = await this.apiGet<ApiResponse<ApiUserStats>>('profile/stats');
    return this.mapFromApi(response.data);
  }
}

export const userService = new UserService();


export const getUserStats = () => userService.getUserStats();