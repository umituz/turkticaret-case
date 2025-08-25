'use client';

import { useState } from 'react';
import { Card, CardContent } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import { toast } from 'sonner';
import { useSettings } from '@/hooks/useSettings';
import { CommerceSettings } from '@/components/admin/settings/CommerceSettings';
import { SystemSettings } from '@/components/admin/settings/SystemSettings';
import { UISettings } from '@/components/admin/settings/UISettings';
import { NotificationSettings } from '@/components/admin/settings/NotificationSettings';
import { LocalizationSettings } from '@/components/admin/settings/LocalizationSettings';
import {
  Globe,
  Bell,
  Palette,
  Shield,
  ShoppingCart,
  Loader2,
  AlertCircle
} from 'lucide-react';

export default function SettingsPage() {
  const [activeTab, setActiveTab] = useState('commerce');
  const { settings, systemStatus, isLoading: loading, error, refreshSettings } = useSettings();

  const handleSettingsUpdate = async () => {
    
    refreshSettings();
    
    toast.success('Settings updated successfully');
  };

  if (loading) {
    return (
      <div className="flex items-center justify-center min-h-[400px]">
        <div className="text-center">
          <Loader2 className="h-8 w-8 animate-spin mx-auto mb-4" />
          <p className="text-muted-foreground">Loading settings...</p>
        </div>
      </div>
    );
  }

  if (error || !settings) {
    return (
      <div className="flex items-center justify-center min-h-[400px]">
        <Card className="w-full max-w-md">
          <CardContent className="pt-6">
            <div className="text-center">
              <AlertCircle className="h-12 w-12 text-destructive mx-auto mb-4" />
              <h3 className="text-lg font-semibold mb-2">Failed to Load Settings</h3>
              <p className="text-muted-foreground mb-4">
                {error || 'Unable to load application settings.'}
              </p>
              <Button onClick={refreshSettings}>
                Try Again
              </Button>
            </div>
          </CardContent>
        </Card>
      </div>
    );
  }

  return (
    <div className="space-y-6">
      {}
      <div className="flex items-center justify-between">
        <div>
          <h1 className="text-3xl font-bold tracking-tight">Application Settings</h1>
          <p className="text-muted-foreground">
            Manage global application settings and configurations
          </p>
        </div>
        
        {}
        {systemStatus && (
          <div className="flex items-center gap-2">
            {systemStatus.maintenance_mode && (
              <Badge variant="destructive" className="gap-1">
                <Shield className="h-3 w-3" />
                Maintenance Mode
              </Badge>
            )}
            {!systemStatus.registration_enabled && (
              <Badge variant="secondary" className="gap-1">
                Registration Disabled
              </Badge>
            )}
          </div>
        )}
      </div>

      {}
      <Tabs value={activeTab} onValueChange={setActiveTab} className="space-y-6">
        <TabsList className="grid w-full grid-cols-5">
          <TabsTrigger value="commerce" className="gap-2">
            <ShoppingCart className="h-4 w-4" />
            Commerce
          </TabsTrigger>
          <TabsTrigger value="localization" className="gap-2">
            <Globe className="h-4 w-4" />
            Localization
          </TabsTrigger>
          <TabsTrigger value="system" className="gap-2">
            <Shield className="h-4 w-4" />
            System
          </TabsTrigger>
          <TabsTrigger value="ui" className="gap-2">
            <Palette className="h-4 w-4" />
            Interface
          </TabsTrigger>
          <TabsTrigger value="notification" className="gap-2">
            <Bell className="h-4 w-4" />
            Notifications
          </TabsTrigger>
        </TabsList>

        <TabsContent value="commerce" className="space-y-6">
          <CommerceSettings 
            settings={settings.commerce} 
            onUpdate={handleSettingsUpdate}
          />
        </TabsContent>

        <TabsContent value="localization" className="space-y-6">
          <LocalizationSettings 
            settings={settings.localization} 
            onUpdate={handleSettingsUpdate}
          />
        </TabsContent>

        <TabsContent value="system" className="space-y-6">
          <SystemSettings 
            settings={settings.system} 
            onUpdate={handleSettingsUpdate}
            systemStatus={systemStatus}
          />
        </TabsContent>

        <TabsContent value="ui" className="space-y-6">
          <UISettings 
            settings={settings.ui} 
            onUpdate={handleSettingsUpdate}
          />
        </TabsContent>

        <TabsContent value="notification" className="space-y-6">
          <NotificationSettings 
            settings={settings.notification} 
            onUpdate={handleSettingsUpdate}
            systemStatus={systemStatus}
          />
        </TabsContent>
      </Tabs>
    </div>
  );
}