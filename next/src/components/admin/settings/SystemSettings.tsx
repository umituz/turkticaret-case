'use client';

import { useState, useEffect } from 'react';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Switch } from '@/components/ui/switch';
import { Badge } from '@/components/ui/badge';
import { toast } from 'sonner';
import { applicationSettingsService } from '@/services/applicationSettingsService';
import type { SimpleSettingsGroup, SystemStatus } from '@/types/settings';
import { 
  Loader2, 
  Shield, 
  Globe, 
  UserPlus, 
  AlertTriangle,
  Settings as SettingsIcon 
} from 'lucide-react';

interface SystemSettingsProps {
  settings: SimpleSettingsGroup;
  systemStatus: SystemStatus | null;
  onUpdate: (updatedSettings: Record<string, unknown>) => void;
}

export function SystemSettings({ settings, systemStatus, onUpdate }: SystemSettingsProps) {
  const [loading, setLoading] = useState(false);
  const [formData, setFormData] = useState({
    maintenance_mode: (settings?.maintenance_mode?.value as boolean) || false,
    app_name: (settings?.app_name?.value as string) || 'Ecommerce',
    app_url: (settings?.app_url?.value as string) || 'http://localhost:3000',
    registration_enabled: (settings?.registration_enabled?.value as boolean) || true,
  });

  
  useEffect(() => {
    if (settings) {
      setFormData({
        maintenance_mode: (settings.maintenance_mode?.value as boolean) || false,
        app_name: (settings.app_name?.value as string) || 'Ecommerce',
        app_url: (settings.app_url?.value as string) || 'http://localhost:3000',
        registration_enabled: (settings.registration_enabled?.value as boolean) || true,
      });
    }
  }, [settings]);

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setLoading(true);

    try {
      await applicationSettingsService.updateSystemSettings(formData);
      onUpdate({ system: formData });
      toast.success('System settings updated successfully');
    } catch (error) {
      console.error('Failed to update system settings:', error);
      toast.error('Failed to update system settings');
    } finally {
      setLoading(false);
    }
  };

  const handleChange = (field: keyof typeof formData, value: string | boolean) => {
    setFormData(prev => ({ ...prev, [field]: value }));
  };

  return (
    <Card>
      <CardHeader>
        <CardTitle className="flex items-center gap-2">
          <Shield className="h-5 w-5" />
          System Settings
        </CardTitle>
        <CardDescription>
          Configure core system settings and application behavior
        </CardDescription>
      </CardHeader>
      <CardContent className="space-y-6">
        <form onSubmit={handleSubmit} className="space-y-6">
          <div className="grid gap-6 md:grid-cols-2">
            {}
            <div className="space-y-4">
              <div className="space-y-2">
                <Label htmlFor="app_name" className="flex items-center gap-2">
                  <SettingsIcon className="h-4 w-4" />
                  Application Name
                </Label>
                <Input
                  id="app_name"
                  value={formData.app_name}
                  onChange={(e) => handleChange('app_name', e.target.value)}
                  placeholder="Enter application name"
                />
                <p className="text-sm text-muted-foreground">
                  Display name shown to users
                </p>
              </div>

              <div className="space-y-2">
                <Label htmlFor="app_url" className="flex items-center gap-2">
                  <Globe className="h-4 w-4" />
                  Application URL
                </Label>
                <Input
                  id="app_url"
                  value={formData.app_url}
                  onChange={(e) => handleChange('app_url', e.target.value)}
                  placeholder="https://example.com"
                  type="url"
                />
                <p className="text-sm text-muted-foreground">
                  Base URL of the application
                </p>
              </div>
            </div>

            {}
            <div className="space-y-4">
              {}
              <div className="flex items-center justify-between space-x-2 p-4 border rounded-lg">
                <div className="flex items-center space-x-3">
                  <AlertTriangle className="h-4 w-4 text-muted-foreground" />
                  <div>
                    <div className="flex items-center gap-2">
                      <Label htmlFor="maintenance_mode" className="text-sm font-medium">
                        Maintenance Mode
                      </Label>
                      {systemStatus?.maintenance_mode && (
                        <Badge variant="destructive" className="text-xs">
                          Active
                        </Badge>
                      )}
                    </div>
                    <p className="text-xs text-muted-foreground">
                      Put application in maintenance mode
                    </p>
                  </div>
                </div>
                <Switch
                  id="maintenance_mode"
                  checked={formData.maintenance_mode}
                  onCheckedChange={(checked) => handleChange('maintenance_mode', checked)}
                />
              </div>

              {}
              <div className="flex items-center justify-between space-x-2 p-4 border rounded-lg">
                <div className="flex items-center space-x-3">
                  <UserPlus className="h-4 w-4 text-muted-foreground" />
                  <div>
                    <div className="flex items-center gap-2">
                      <Label htmlFor="registration_enabled" className="text-sm font-medium">
                        User Registration
                      </Label>
                      {!systemStatus?.registration_enabled && (
                        <Badge variant="secondary" className="text-xs">
                          Disabled
                        </Badge>
                      )}
                    </div>
                    <p className="text-xs text-muted-foreground">
                      Allow new user registrations
                    </p>
                  </div>
                </div>
                <Switch
                  id="registration_enabled"
                  checked={formData.registration_enabled}
                  onCheckedChange={(checked) => handleChange('registration_enabled', checked)}
                />
              </div>
            </div>
          </div>

          {}
          {formData.maintenance_mode && (
            <div className="p-4 border border-orange-200 rounded-lg bg-orange-50">
              <div className="flex items-center gap-2 text-orange-800">
                <AlertTriangle className="h-4 w-4" />
                <p className="text-sm font-medium">Maintenance Mode Warning</p>
              </div>
              <p className="text-sm text-orange-700 mt-1">
                When maintenance mode is enabled, only administrators will be able to access the site. 
                Regular users will see a maintenance message.
              </p>
            </div>
          )}

          {}
          <div className="flex justify-end">
            <Button type="submit" disabled={loading}>
              {loading && <Loader2 className="mr-2 h-4 w-4 animate-spin" />}
              Save System Settings
            </Button>
          </div>
        </form>
      </CardContent>
    </Card>
  );
}