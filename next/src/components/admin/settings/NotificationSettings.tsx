'use client';

import { useState, useEffect } from 'react';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import { Switch } from '@/components/ui/switch';
import { Badge } from '@/components/ui/badge';
import { toast } from 'sonner';
import { applicationSettingsService } from '@/services/applicationSettingsService';
import type { SimpleSettingsGroup, SystemStatus } from '@/types/settings';
import { Loader2, Bell, Mail, MessageSquare, CheckCircle, XCircle } from 'lucide-react';

interface NotificationSettingsProps {
  settings: SimpleSettingsGroup;
  systemStatus: SystemStatus | null;
  onUpdate: (updatedSettings: Record<string, unknown>) => void;
}

export function NotificationSettings({ settings, systemStatus, onUpdate }: NotificationSettingsProps) {
  const [loading, setLoading] = useState(false);
  const [formData, setFormData] = useState({
    email_notifications_enabled: (settings?.email_notifications_enabled?.value as boolean) || true,
    sms_notifications_enabled: (settings?.sms_notifications_enabled?.value as boolean) || false,
  });

  
  useEffect(() => {
    if (settings) {
      setFormData({
        email_notifications_enabled: (settings.email_notifications_enabled?.value as boolean) || true,
        sms_notifications_enabled: (settings.sms_notifications_enabled?.value as boolean) || false,
      });
    }
  }, [settings]);

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setLoading(true);

    try {
      await applicationSettingsService.updateNotificationSettings(formData);
      onUpdate({ notification: formData });
      toast.success('Notification settings updated successfully');
    } catch (error) {
      console.error('Failed to update notification settings:', error);
      toast.error('Failed to update notification settings');
    } finally {
      setLoading(false);
    }
  };

  const handleChange = (field: keyof typeof formData, value: boolean) => {
    setFormData(prev => ({ ...prev, [field]: value }));
  };

  return (
    <Card>
      <CardHeader>
        <CardTitle className="flex items-center gap-2">
          <Bell className="h-5 w-5" />
          Notification Settings
        </CardTitle>
        <CardDescription>
          Configure global notification preferences and channels
        </CardDescription>
      </CardHeader>
      <CardContent className="space-y-6">
        <form onSubmit={handleSubmit} className="space-y-6">
          <div className="space-y-4">
            {}
            <div className="grid gap-4 md:grid-cols-2">
              <div className="p-4 border rounded-lg">
                <div className="flex items-center justify-between">
                  <div className="flex items-center gap-2">
                    <Mail className="h-4 w-4 text-blue-500" />
                    <span className="text-sm font-medium">Email Notifications</span>
                  </div>
                  {systemStatus?.email_notifications ? (
                    <Badge variant="default" className="gap-1">
                      <CheckCircle className="h-3 w-3" />
                      Active
                    </Badge>
                  ) : (
                    <Badge variant="secondary" className="gap-1">
                      <XCircle className="h-3 w-3" />
                      Inactive
                    </Badge>
                  )}
                </div>
                <p className="text-xs text-muted-foreground mt-1">
                  System-wide email notification status
                </p>
              </div>

              <div className="p-4 border rounded-lg">
                <div className="flex items-center justify-between">
                  <div className="flex items-center gap-2">
                    <MessageSquare className="h-4 w-4 text-green-500" />
                    <span className="text-sm font-medium">SMS Notifications</span>
                  </div>
                  {systemStatus?.sms_notifications ? (
                    <Badge variant="default" className="gap-1">
                      <CheckCircle className="h-3 w-3" />
                      Active
                    </Badge>
                  ) : (
                    <Badge variant="secondary" className="gap-1">
                      <XCircle className="h-3 w-3" />
                      Inactive
                    </Badge>
                  )}
                </div>
                <p className="text-xs text-muted-foreground mt-1">
                  System-wide SMS notification status
                </p>
              </div>
            </div>

            {}
            <div className="flex items-center justify-between space-x-2 p-4 border rounded-lg">
              <div className="flex items-center space-x-3">
                <Mail className="h-4 w-4 text-muted-foreground" />
                <div>
                  <div className="flex items-center gap-2">
                    <Label htmlFor="email_notifications_enabled" className="text-sm font-medium">
                      Email Notifications
                    </Label>
                    {formData.email_notifications_enabled && (
                      <Badge variant="default" className="text-xs">
                        Enabled
                      </Badge>
                    )}
                  </div>
                  <p className="text-xs text-muted-foreground">
                    Enable email notifications globally for all users
                  </p>
                </div>
              </div>
              <Switch
                id="email_notifications_enabled"
                checked={formData.email_notifications_enabled}
                onCheckedChange={(checked) => handleChange('email_notifications_enabled', checked)}
              />
            </div>

            {}
            <div className="flex items-center justify-between space-x-2 p-4 border rounded-lg">
              <div className="flex items-center space-x-3">
                <MessageSquare className="h-4 w-4 text-muted-foreground" />
                <div>
                  <div className="flex items-center gap-2">
                    <Label htmlFor="sms_notifications_enabled" className="text-sm font-medium">
                      SMS Notifications
                    </Label>
                    {formData.sms_notifications_enabled && (
                      <Badge variant="default" className="text-xs">
                        Enabled
                      </Badge>
                    )}
                  </div>
                  <p className="text-xs text-muted-foreground">
                    Enable SMS notifications globally for all users
                  </p>
                </div>
              </div>
              <Switch
                id="sms_notifications_enabled"
                checked={formData.sms_notifications_enabled}
                onCheckedChange={(checked) => handleChange('sms_notifications_enabled', checked)}
              />
            </div>
          </div>

          {}
          <div className="p-4 border border-blue-200 rounded-lg bg-blue-50">
            <div className="flex items-center gap-2 text-blue-800">
              <Bell className="h-4 w-4" />
              <p className="text-sm font-medium">Notification Channel Information</p>
            </div>
            <div className="text-sm text-blue-700 mt-1 space-y-1">
              <p>• Email notifications are used for order confirmations, password resets, and system alerts</p>
              <p>• SMS notifications are used for urgent order updates and security alerts</p>
              <p>• Users can still control their individual notification preferences even when global settings are enabled</p>
            </div>
          </div>

          {}
          <div className="flex justify-end">
            <Button type="submit" disabled={loading}>
              {loading && <Loader2 className="mr-2 h-4 w-4 animate-spin" />}
              Save Notification Settings
            </Button>
          </div>
        </form>
      </CardContent>
    </Card>
  );
}