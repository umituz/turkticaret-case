'use client';

import { useAuth } from '@/hooks/useAuth';
import { StatsCard } from '@/components/dashboard/StatsCard';
import { QuickActions } from '@/components/dashboard/QuickActions';
import { RecentActivity } from '@/components/dashboard/RecentActivity';
import { dashboardService, type DashboardData } from '@/services/dashboardService';
import { useState, useEffect } from 'react';
import { 
  Users, 
  ShoppingCart, 
  Package, 
  TrendingUp
} from 'lucide-react';

export default function DashboardPage() {
  const { user } = useAuth();
  const [dashboardData, setDashboardData] = useState<DashboardData | null>(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);

  useEffect(() => {
    const fetchDashboardData = async () => {
      try {
        setLoading(true);
        const data = await dashboardService.getDashboardData();
        setDashboardData(data);
        setError(null);
      } catch (err) {
        setError(err instanceof Error ? err.message : 'Failed to load dashboard data');
      } finally {
        setLoading(false);
      }
    };

    fetchDashboardData();
  }, []);

  
  const getStatIcon = (title: string) => {
    switch (title.toLowerCase()) {
      case 'total users':
        return Users;
      case 'orders':
        return ShoppingCart;
      case 'products':
        return Package;
      case 'revenue':
        return TrendingUp;
      default:
        return Package;
    }
  };

  
  if (loading) {
    return (
      <div className="flex items-center justify-center h-64">
        <div className="animate-spin rounded-full h-8 w-8 border-b-2 border-gray-900"></div>
      </div>
    );
  }

  
  if (error) {
    return (
      <div className="flex items-center justify-center h-64">
        <div className="text-center">
          <p className="text-red-600 mb-4">{error}</p>
          <button 
            onClick={() => window.location.reload()} 
            className="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700"
          >
            Retry
          </button>
        </div>
      </div>
    );
  }

  if (!dashboardData) {
    return null;
  }

  return (
    <>
      {}
      <div className="mb-8">
        <h1 className="text-3xl font-bold tracking-tight mb-2">
          Welcome back, {user?.name.split(' ')[0]}!
        </h1>
        <p className="text-muted-foreground">
          Here&apos;s what&apos;s happening with your store today.
        </p>
      </div>

      {}
      <div className="grid gap-6 md:grid-cols-2 lg:grid-cols-4 mb-8">
        {dashboardData.stats.map((stat) => (
          <StatsCard
            key={stat.title}
            title={stat.title}
            value={stat.value}
            description={stat.description}
            icon={getStatIcon(stat.title)}
          />
        ))}
      </div>

      {}
      <div className="grid gap-6 md:grid-cols-2">
        <QuickActions />
        <RecentActivity activities={dashboardData.recent_activity} />
      </div>
    </>
  );
}