import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Clock, User, Package, ShoppingCart } from 'lucide-react';

interface ActivityItem {
  uuid: string | null;
  type: 'order' | 'user' | 'product' | 'system';
  message: string;
  timestamp: string;
  user?: string;
  status?: 'success' | 'warning' | 'info';
}

interface RecentActivityProps {
  activities?: ActivityItem[];
  maxItems?: number;
}

const defaultActivities: ActivityItem[] = [];

const getActivityIcon = (type: ActivityItem['type']) => {
  switch (type) {
    case 'order':
      return <ShoppingCart className="h-4 w-4" />;
    case 'user':
      return <User className="h-4 w-4" />;
    case 'product':
      return <Package className="h-4 w-4" />;
    default:
      return <Clock className="h-4 w-4" />;
  }
};

const getStatusColor = (status?: ActivityItem['status']) => {
  switch (status) {
    case 'success':
      return 'bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-400';
    case 'warning':
      return 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/20 dark:text-yellow-400';
    case 'info':
      return 'bg-blue-100 text-blue-800 dark:bg-blue-900/20 dark:text-blue-400';
    default:
      return 'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-300';
  }
};

const getIconBackground = (status?: ActivityItem['status']) => {
  switch (status) {
    case 'success':
      return 'bg-green-100 text-green-600 dark:bg-green-900/20 dark:text-green-400';
    case 'warning':
      return 'bg-yellow-100 text-yellow-600 dark:bg-yellow-900/20 dark:text-yellow-400';
    case 'info':
      return 'bg-blue-100 text-blue-600 dark:bg-blue-900/20 dark:text-blue-400';
    default:
      return 'bg-muted text-muted-foreground';
  }
};

export const RecentActivity = ({ 
  activities = defaultActivities, 
  maxItems = 5 
}: RecentActivityProps) => {
  const displayedActivities = activities.slice(0, maxItems);

  return (
    <Card className="border-border/50">
      <CardHeader>
        <CardTitle className="flex items-center gap-2">
          <Clock className="h-5 w-5" />
          Recent Activity
        </CardTitle>
        <CardDescription>Latest updates from your store</CardDescription>
      </CardHeader>
      <CardContent>
        <div className="space-y-4">
          {displayedActivities.map((activity) => (
            <div key={activity.uuid} className="flex items-start gap-3 pb-3 border-b border-border/50 last:border-0">
              <div className={`flex items-center justify-center w-8 h-8 rounded-full ${getIconBackground(activity.status)}`}>
                {getActivityIcon(activity.type)}
              </div>
              <div className="flex-1 min-w-0">
                <div className="flex items-start justify-between gap-2">
                  <p className="text-sm font-medium leading-snug">{activity.message}</p>
                  {activity.status && (
                    <Badge 
                      variant="secondary" 
                      className={`text-xs shrink-0 ${getStatusColor(activity.status)}`}
                    >
                      {activity.status}
                    </Badge>
                  )}
                </div>
                <div className="flex items-center gap-2 mt-1">
                  <p className="text-xs text-muted-foreground">{activity.timestamp}</p>
                  {activity.user && activity.type !== 'user' && (
                    <>
                      <span className="text-xs text-muted-foreground">•</span>
                      <p className="text-xs text-muted-foreground">by {activity.user}</p>
                    </>
                  )}
                </div>
              </div>
            </div>
          ))}
        </div>
        
        {activities.length > maxItems && (
          <div className="text-center mt-4">
            <button className="text-xs text-red-600 hover:text-red-700 font-medium">
              View all activities ({activities.length - maxItems} more)
            </button>
          </div>
        )}
      </CardContent>
    </Card>
  );
};