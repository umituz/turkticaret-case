import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { 
  Package, 
  Users, 
  ShoppingCart, 
  Plus,
  BarChart3,
  Settings,
  Tag
} from 'lucide-react';

interface QuickAction {
  id: string;
  label: string;
  icon: React.ComponentType<{ className?: string }>;
  description?: string;
  onClick?: () => void;
  disabled?: boolean;
}

interface QuickActionsProps {
  actions?: QuickAction[];
  title?: string;
  description?: string;
}

const defaultActions: QuickAction[] = [
  {
    id: 'add-product',
    label: 'Add New Product',
    icon: Package,
    description: 'Create a new product listing',
    onClick: () => window.location.href = '/dashboard/products/new'
  },
  {
    id: 'manage-users',
    label: 'Manage Users',
    icon: Users,
    description: 'View and edit user accounts',
    onClick: () => console.log('User management feature coming soon')
  },
  {
    id: 'view-orders',
    label: 'View Orders',
    icon: ShoppingCart,
    description: 'Check recent customer orders',
    onClick: () => window.location.href = '/account/orders'
  },
  {
    id: 'analytics',
    label: 'Analytics',
    icon: BarChart3,
    description: 'View sales and traffic reports',
    onClick: () => console.log('Analytics feature coming soon')
  },
  {
    id: 'settings',
    label: 'Store Settings',
    icon: Settings,
    description: 'Configure store preferences',
    onClick: () => window.location.href = '/dashboard/settings'
  },
  {
    id: 'categories',
    label: 'Manage Categories',
    icon: Tag,
    description: 'Organize product categories',
    onClick: () => window.location.href = '/dashboard/categories'
  }
];

export const QuickActions = ({ 
  actions = defaultActions,
  title = "Quick Actions",
  description = "Manage your store with these shortcuts"
}: QuickActionsProps) => {
  return (
    <Card className="border-border/50">
      <CardHeader>
        <CardTitle className="flex items-center gap-2">
          <Plus className="h-5 w-5" />
          {title}
        </CardTitle>
        <CardDescription>{description}</CardDescription>
      </CardHeader>
      <CardContent>
        <div className="grid gap-2">
          {actions.map((action) => {
            const Icon = action.icon;
            return (
              <Button
                key={action.id}
                variant="outline"
                className="w-full justify-start h-auto p-3"
                onClick={action.onClick}
                disabled={action.disabled}
              >
                <div className="flex items-center gap-3 w-full">
                  <Icon className="h-4 w-4 flex-shrink-0" />
                  <div className="flex-1 text-left">
                    <div className="font-medium">{action.label}</div>
                    {action.description && (
                      <div className="text-xs text-muted-foreground mt-1">
                        {action.description}
                      </div>
                    )}
                  </div>
                </div>
              </Button>
            );
          })}
        </div>
      </CardContent>
    </Card>
  );
};