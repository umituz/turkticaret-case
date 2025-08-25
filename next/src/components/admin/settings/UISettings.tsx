'use client';

import { useState, useEffect } from 'react';
import Image from 'next/image';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { toast } from 'sonner';
import { useSettings } from '@/hooks/useSettings';
import type { SimpleSettingsGroup } from '@/types/settings';
import { Loader2, Palette, Hash, Image as ImageIcon, Monitor } from 'lucide-react';

interface UISettingsProps {
  settings: SimpleSettingsGroup;
  onUpdate: (updatedSettings: Record<string, unknown>) => void;
}

export function UISettings({ settings, onUpdate }: UISettingsProps) {
  const [loading, setLoading] = useState(false);
  const { updateUI } = useSettings();
  const [formData, setFormData] = useState({
    items_per_page: (settings?.items_per_page?.value as number) || 20,
    theme: (settings?.theme?.value as string) || 'default',
    logo_url: (settings?.logo_url?.value as string) || '/images/logo.png',
  });

  
  useEffect(() => {
    if (settings) {
      setFormData({
        items_per_page: (settings.items_per_page?.value as number) || 20,
        theme: (settings.theme?.value as string) || 'default',
        logo_url: (settings.logo_url?.value as string) || '/images/logo.png',
      });
    }
  }, [settings]);

  const themes = [
    { value: 'default', label: 'Default' },
    { value: 'dark', label: 'Dark' },
    { value: 'light', label: 'Light' },
    { value: 'modern', label: 'Modern' },
  ];

  const itemsPerPageOptions = [
    { value: 10, label: '10 items' },
    { value: 20, label: '20 items' },
    { value: 50, label: '50 items' },
    { value: 100, label: '100 items' },
  ];

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setLoading(true);

    try {
      await updateUI(formData);
      onUpdate({ ui: formData });
      toast.success('UI settings updated successfully');
    } catch (error) {
      console.error('Failed to update UI settings:', error);
      toast.error('Failed to update UI settings');
    } finally {
      setLoading(false);
    }
  };

  const handleChange = (field: keyof typeof formData, value: string | number) => {
    setFormData(prev => ({ ...prev, [field]: value }));
  };

  return (
    <Card>
      <CardHeader>
        <CardTitle className="flex items-center gap-2">
          <Palette className="h-5 w-5" />
          Interface Settings
        </CardTitle>
        <CardDescription>
          Configure user interface theme, layout, and display preferences
        </CardDescription>
      </CardHeader>
      <CardContent className="space-y-6">
        <form onSubmit={handleSubmit} className="space-y-6">
          <div className="grid gap-6 md:grid-cols-2">
            {}
            <div className="space-y-4">
              <div className="space-y-2">
                <Label htmlFor="theme" className="flex items-center gap-2">
                  <Monitor className="h-4 w-4" />
                  Application Theme
                </Label>
                <Select
                  value={formData.theme}
                  onValueChange={(value) => handleChange('theme', value)}
                >
                  <SelectTrigger>
                    <SelectValue placeholder="Select theme" />
                  </SelectTrigger>
                  <SelectContent>
                    {themes.map((theme) => (
                      <SelectItem key={theme.value} value={theme.value}>
                        {theme.label}
                      </SelectItem>
                    ))}
                  </SelectContent>
                </Select>
                <p className="text-sm text-muted-foreground">
                  Default theme for the application
                </p>
              </div>

              <div className="space-y-2">
                <Label htmlFor="logo_url" className="flex items-center gap-2">
                  <ImageIcon className="h-4 w-4" />
                  Logo URL
                </Label>
                <Input
                  id="logo_url"
                  value={formData.logo_url}
                  onChange={(e) => handleChange('logo_url', e.target.value)}
                  placeholder="/images/logo.png"
                />
                <p className="text-sm text-muted-foreground">
                  URL or path to the application logo
                </p>
              </div>
            </div>

            {}
            <div className="space-y-4">
              <div className="space-y-2">
                <Label htmlFor="items_per_page" className="flex items-center gap-2">
                  <Hash className="h-4 w-4" />
                  Items Per Page
                </Label>
                <Select
                  value={formData.items_per_page.toString()}
                  onValueChange={(value) => handleChange('items_per_page', parseInt(value))}
                >
                  <SelectTrigger>
                    <SelectValue placeholder="Select items per page" />
                  </SelectTrigger>
                  <SelectContent>
                    {itemsPerPageOptions.map((option) => (
                      <SelectItem key={option.value} value={option.value.toString()}>
                        {option.label}
                      </SelectItem>
                    ))}
                  </SelectContent>
                </Select>
                <p className="text-sm text-muted-foreground">
                  Default number of items per page in listings
                </p>
              </div>

              {}
              {formData.logo_url && (
                <div className="space-y-2">
                  <Label className="text-sm font-medium">Logo Preview</Label>
                  <div className="p-4 border rounded-lg bg-muted/50">
                    <Image
                      src={formData.logo_url}
                      alt="Application logo preview"
                      width={96}
                      height={48}
                      className="h-12 w-auto object-contain"
                      onError={(e) => {
                        const target = e.target as HTMLImageElement;
                        target.style.display = 'none';
                      }}
                      onLoad={(e) => {
                        const target = e.target as HTMLImageElement;
                        target.style.display = 'block';
                      }}
                    />
                    <p className="text-xs text-muted-foreground mt-2">
                      Logo preview (actual size may vary)
                    </p>
                  </div>
                </div>
              )}
            </div>
          </div>

          {}
          <div className="flex justify-end">
            <Button type="submit" disabled={loading}>
              {loading && <Loader2 className="mr-2 h-4 w-4 animate-spin" />}
              Save Interface Settings
            </Button>
          </div>
        </form>
      </CardContent>
    </Card>
  );
}