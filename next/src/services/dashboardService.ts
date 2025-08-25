import { apiClient } from '@/lib/api';

export interface StatItem {
  title: string;
  value: string;
  description: string;
}

export interface ActivityItem {
  uuid: string | null;
  type: 'order' | 'user';
  message: string;
  timestamp: string;
  user?: string;
  status: 'success' | 'warning' | 'info';
}


export interface DashboardData {
  stats: StatItem[];
  recent_activity: ActivityItem[];
}

export interface DashboardResponse {
  success: boolean;
  message: string;
  data: DashboardData;
  errors: string[];
}

export const dashboardService = {
  async getDashboardData(): Promise<DashboardData> {
    try {
      const response = await apiClient.get<DashboardResponse>('admin/dashboard');
      return response.data as DashboardData;
    } catch (error) {
      console.error('Dashboard API Error:', error);
      throw error;
    }
  }
};