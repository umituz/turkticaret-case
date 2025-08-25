import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { 
  Server, 
  Database, 
  Shield, 
  HardDrive,
  Wifi,
  AlertTriangle,
  CheckCircle,
  Clock
} from 'lucide-react';

interface StatusItem {
  id: string;
  label: string;
  status: 'online' | 'offline' | 'warning' | 'maintenance';
  value?: string;
  lastUpdated?: string;
}

interface SystemStatusProps {
  statusItems?: StatusItem[];
  title?: string;
  description?: string;
}

const defaultStatusItems: StatusItem[] = [
  {
    id: 'server',
    label: 'Server Status',
    status: 'online',
    lastUpdated: '1 minute ago'
  },
  {
    id: 'database',
    label: 'Database',
    status: 'online',
    lastUpdated: '2 minutes ago'
  },
  {
    id: 'backup',
    label: 'Last Backup',
    status: 'online',
    value: '2 hours ago',
    lastUpdated: '2 hours ago'
  },
  {
    id: 'storage',
    label: 'Storage Usage',
    status: 'warning',
    value: '78% used',
    lastUpdated: '5 minutes ago'
  },
  {
    id: 'security',
    label: 'Security Scan',
    status: 'online',
    value: 'No issues',
    lastUpdated: '1 day ago'
  }
];

const getStatusConfig = (status: StatusItem['status']) => {
  switch (status) {
    case 'online':
      return {
        badge: 'bg-green-100 text-green-800',
        icon: CheckCircle,
        text: 'Online'
      };
    case 'offline':
      return {
        badge: 'bg-red-100 text-red-800',
        icon: AlertTriangle,
        text: 'Offline'
      };
    case 'warning':
      return {
        badge: 'bg-yellow-100 text-yellow-800',
        icon: AlertTriangle,
        text: 'Warning'
      };
    case 'maintenance':
      return {
        badge: 'bg-blue-100 text-blue-800',
        icon: Clock,
        text: 'Maintenance'
      };
    default:
      return {
        badge: 'bg-gray-100 text-gray-800',
        icon: Clock,
        text: 'Unknown'
      };
  }
};

const getServiceIcon = (id: string) => {
  switch (id) {
    case 'server':
      return Server;
    case 'database':
      return Database;
    case 'security':
      return Shield;
    case 'storage':
      return HardDrive;
    case 'backup':
      return HardDrive;
    default:
      return Wifi;
  }
};

export const SystemStatus = ({ 
  statusItems = defaultStatusItems,
  title = "System Status",
  description = "Monitor your application health"
}: SystemStatusProps) => {
  return (
    <Card className="border-border/50">
      <CardHeader>
        <CardTitle className="flex items-center gap-2">
          <Server className="h-5 w-5" />
          {title}
        </CardTitle>
        <CardDescription>{description}</CardDescription>
      </CardHeader>
      <CardContent>
        <div className="space-y-4">
          {statusItems.map((item) => {
            const statusConfig = getStatusConfig(item.status);
            const ServiceIcon = getServiceIcon(item.id);
            const StatusIcon = statusConfig.icon;

            return (
              <div key={item.id} className="flex items-center justify-between p-3 rounded-lg border border-border/50">
                <div className="flex items-center gap-3">
                  <ServiceIcon className="h-4 w-4 text-muted-foreground" />
                  <div>
                    <span className="text-sm font-medium">{item.label}</span>
                    {item.value && (
                      <p className="text-xs text-muted-foreground">{item.value}</p>
                    )}
                  </div>
                </div>
                <div className="flex items-center gap-2">
                  <Badge variant="secondary" className={`text-xs ${statusConfig.badge}`}>
                    <StatusIcon className="h-3 w-3 mr-1" />
                    {statusConfig.text}
                  </Badge>
                </div>
              </div>
            );
          })}
        </div>
        
        <div className="mt-4 pt-4 border-t border-border/50">
          <div className="flex items-center justify-between text-xs text-muted-foreground">
            <span>Last system check</span>
            <span>2 minutes ago</span>
          </div>
        </div>
      </CardContent>
    </Card>
  );
};