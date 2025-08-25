'use client';

import { useState, useEffect } from 'react';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import { Switch } from '@/components/ui/switch';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { toast } from 'sonner';
import { applicationSettingsService } from '@/services/applicationSettingsService';
import type { SimpleSettingsGroup } from '@/types/settings';
import { Loader2, ShoppingCart, DollarSign, Package, Truck } from 'lucide-react';

interface CommerceSettingsProps {
  settings: SimpleSettingsGroup;
  onUpdate: (updatedSettings: Record<string, unknown>) => void;
}

export function CommerceSettings({ settings, onUpdate }: CommerceSettingsProps) {
  const [loading, setLoading] = useState(false);
  const [formData, setFormData] = useState({
    default_currency: (settings?.default_currency?.value as string) || 'TRY',
    tax_enabled: (settings?.tax_enabled?.value as boolean) || false,
    shipping_enabled: (settings?.shipping_enabled?.value as boolean) || false,
  });

  
  useEffect(() => {
    if (settings) {
      setFormData({
        default_currency: (settings.default_currency?.value as string) || 'TRY',
        tax_enabled: (settings.tax_enabled?.value as boolean) || false,
        shipping_enabled: (settings.shipping_enabled?.value as boolean) || false,
      });
    }
  }, [settings]);

  const currencies = [
    { value: 'TRY', label: 'Turkish Lira' },
    { value: 'USD', label: 'US Dollar' },
    { value: 'EUR', label: 'Euro' },
    { value: 'GBP', label: 'British Pound' },
  ];

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setLoading(true);

    try {
      await applicationSettingsService.updateCommerceSettings(formData);
      onUpdate({ commerce: formData });
      toast.success('Commerce settings updated successfully');
    } catch (error) {
      console.error('Failed to update commerce settings:', error);
      toast.error('Failed to update commerce settings');
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
          <ShoppingCart className="h-5 w-5" />
          Commerce Settings
        </CardTitle>
        <CardDescription>
          Configure global commerce and e-commerce related settings
        </CardDescription>
      </CardHeader>
      <CardContent className="space-y-6">
        <form onSubmit={handleSubmit} className="space-y-6">
          <div className="grid gap-6 md:grid-cols-2">
            {}
            <div className="space-y-2">
              <Label htmlFor="default_currency" className="flex items-center gap-2">
                <DollarSign className="h-4 w-4" />
                Default Currency
              </Label>
              <Select
                value={formData.default_currency}
                onValueChange={(value) => handleChange('default_currency', value)}
              >
                <SelectTrigger>
                  <SelectValue placeholder="Select currency" />
                </SelectTrigger>
                <SelectContent>
                  {currencies.map((currency) => (
                    <SelectItem key={currency.value} value={currency.value}>
                      {currency.label}
                    </SelectItem>
                  ))}
                </SelectContent>
              </Select>
              <p className="text-sm text-muted-foreground">
                Default currency for guest users and new registrations
              </p>
            </div>

            <div className="space-y-4">
              {}
              <div className="flex items-center justify-between space-x-2 p-4 border rounded-lg">
                <div className="flex items-center space-x-3">
                  <Package className="h-4 w-4 text-muted-foreground" />
                  <div>
                    <Label htmlFor="tax_enabled" className="text-sm font-medium">
                      Tax Calculations
                    </Label>
                    <p className="text-xs text-muted-foreground">
                      Enable tax calculations globally
                    </p>
                  </div>
                </div>
                <Switch
                  id="tax_enabled"
                  checked={formData.tax_enabled}
                  onCheckedChange={(checked) => handleChange('tax_enabled', checked)}
                />
              </div>

              {}
              <div className="flex items-center justify-between space-x-2 p-4 border rounded-lg">
                <div className="flex items-center space-x-3">
                  <Truck className="h-4 w-4 text-muted-foreground" />
                  <div>
                    <Label htmlFor="shipping_enabled" className="text-sm font-medium">
                      Shipping Functionality
                    </Label>
                    <p className="text-xs text-muted-foreground">
                      Enable shipping calculations and options
                    </p>
                  </div>
                </div>
                <Switch
                  id="shipping_enabled"
                  checked={formData.shipping_enabled}
                  onCheckedChange={(checked) => handleChange('shipping_enabled', checked)}
                />
              </div>
            </div>
          </div>

          {}
          <div className="flex justify-end">
            <Button type="submit" disabled={loading}>
              {loading && <Loader2 className="mr-2 h-4 w-4 animate-spin" />}
              Save Commerce Settings
            </Button>
          </div>
        </form>
      </CardContent>
    </Card>
  );
}