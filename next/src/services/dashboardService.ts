import { apiClient } from '@/lib/api';

export interface StatItem {
  title: string;
  value: string;
  change: string;
  description: string;
}

export interface ActivityItem {
  uuid: string | null;
  type: 'order' | 'user' | 'product' | 'system';
  message: string;
  timestamp: string;
  user?: string;
  status: 'success' | 'warning' | 'info';
}

export interface SystemStatusItem {
  id: string;
  label: string;
  status: 'online' | 'offline' | 'warning' | 'maintenance';
  value?: string;
  last_updated: string;
}

export interface DashboardData {
  stats: StatItem[];
  recent_activity: ActivityItem[];
  system_status: SystemStatusItem[];
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