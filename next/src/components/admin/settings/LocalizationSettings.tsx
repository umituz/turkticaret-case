'use client';

import { useState, useEffect } from 'react';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { toast } from 'sonner';
import { applicationSettingsService } from '@/services/applicationSettingsService';
import type { SimpleSettingsGroup } from '@/types/settings';
import { Loader2, Globe, MapPin, Clock, Languages } from 'lucide-react';

interface LocalizationSettingsProps {
  settings: SimpleSettingsGroup;
  onUpdate: (updatedSettings: Record<string, unknown>) => void;
}

export function LocalizationSettings({ settings, onUpdate }: LocalizationSettingsProps) {
  const [loading, setLoading] = useState(false);
  const [formData, setFormData] = useState({
    default_language: (settings?.default_language?.value as string) || 'tr',
    default_country: (settings?.default_country?.value as string) || 'TR',
    default_timezone: (settings?.default_timezone?.value as string) || 'Europe/Istanbul',
  });

  
  useEffect(() => {
    if (settings) {
      setFormData({
        default_language: (settings.default_language?.value as string) || 'tr',
        default_country: (settings.default_country?.value as string) || 'TR',
        default_timezone: (settings.default_timezone?.value as string) || 'Europe/Istanbul',
      });
    }
  }, [settings]);

  const languages = [
    { value: 'tr', label: 'Turkish (Türkçe)' },
    { value: 'en', label: 'English' },
    { value: 'de', label: 'German (Deutsch)' },
    { value: 'fr', label: 'French (Français)' },
  ];

  const countries = [
    { value: 'TR', label: 'Turkey' },
    { value: 'US', label: 'United States' },
    { value: 'DE', label: 'Germany' },
    { value: 'FR', label: 'France' },
    { value: 'GB', label: 'United Kingdom' },
  ];

  const timezones = [
    { value: 'Europe/Istanbul', label: 'Istanbul (UTC+3)' },
    { value: 'UTC', label: 'UTC (UTC+0)' },
    { value: 'America/New_York', label: 'New York (UTC-5)' },
    { value: 'Europe/London', label: 'London (UTC+0)' },
    { value: 'Europe/Berlin', label: 'Berlin (UTC+1)' },
    { value: 'Asia/Tokyo', label: 'Tokyo (UTC+9)' },
  ];

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setLoading(true);

    try {
      await applicationSettingsService.updateLocalizationSettings(formData);
      onUpdate({ localization: formData });
      toast.success('Localization settings updated successfully');
    } catch (error) {
      console.error('Failed to update localization settings:', error);
      toast.error('Failed to update localization settings');
    } finally {
      setLoading(false);
    }
  };

  const handleChange = (field: keyof typeof formData, value: string) => {
    setFormData(prev => ({ ...prev, [field]: value }));
  };

  return (
    <Card>
      <CardHeader>
        <CardTitle className="flex items-center gap-2">
          <Globe className="h-5 w-5" />
          Localization Settings
        </CardTitle>
        <CardDescription>
          Configure default language, country, and timezone for guest users
        </CardDescription>
      </CardHeader>
      <CardContent className="space-y-6">
        <form onSubmit={handleSubmit} className="space-y-6">
          <div className="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
            {}
            <div className="space-y-2">
              <Label htmlFor="default_language" className="flex items-center gap-2">
                <Languages className="h-4 w-4" />
                Default Language
              </Label>
              <Select
                value={formData.default_language}
                onValueChange={(value) => handleChange('default_language', value)}
              >
                <SelectTrigger>
                  <SelectValue placeholder="Select language" />
                </SelectTrigger>
                <SelectContent>
                  {languages.map((language) => (
                    <SelectItem key={language.value} value={language.value}>
                      {language.label}
                    </SelectItem>
                  ))}
                </SelectContent>
              </Select>
              <p className="text-sm text-muted-foreground">
                Default language for guest users
              </p>
            </div>

            {}
            <div className="space-y-2">
              <Label htmlFor="default_country" className="flex items-center gap-2">
                <MapPin className="h-4 w-4" />
                Default Country
              </Label>
              <Select
                value={formData.default_country}
                onValueChange={(value) => handleChange('default_country', value)}
              >
                <SelectTrigger>
                  <SelectValue placeholder="Select country" />
                </SelectTrigger>
                <SelectContent>
                  {countries.map((country) => (
                    <SelectItem key={country.value} value={country.value}>
                      {country.label}
                    </SelectItem>
                  ))}
                </SelectContent>
              </Select>
              <p className="text-sm text-muted-foreground">
                Default country for guest users
              </p>
            </div>

            {}
            <div className="space-y-2">
              <Label htmlFor="default_timezone" className="flex items-center gap-2">
                <Clock className="h-4 w-4" />
                Default Timezone
              </Label>
              <Select
                value={formData.default_timezone}
                onValueChange={(value) => handleChange('default_timezone', value)}
              >
                <SelectTrigger>
                  <SelectValue placeholder="Select timezone" />
                </SelectTrigger>
                <SelectContent>
                  {timezones.map((timezone) => (
                    <SelectItem key={timezone.value} value={timezone.value}>
                      {timezone.label}
                    </SelectItem>
                  ))}
                </SelectContent>
              </Select>
              <p className="text-sm text-muted-foreground">
                Default timezone for the application
              </p>
            </div>
          </div>

          {}
          <div className="flex justify-end">
            <Button type="submit" disabled={loading}>
              {loading && <Loader2 className="mr-2 h-4 w-4 animate-spin" />}
              Save Localization Settings
            </Button>
          </div>
        </form>
      </CardContent>
    </Card>
  );
}