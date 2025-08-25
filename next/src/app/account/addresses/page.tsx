'use client';

import { useState, useEffect, useCallback, useRef, Suspense } from 'react';
import { useAuth } from '@/hooks/useAuth';
import { useRouter } from 'next/navigation';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select';
import { Switch } from '@/components/ui/switch';
import { Badge } from '@/components/ui/badge';
import { ProfileSidebar } from '@/components/profile/ProfileSidebar';
import { useToast } from '@/hooks/use-toast';
import { Address, AddressFormData } from '@/types/user';
import { addressService } from '@/services/addressService';
import { 
  Plus, 
  Edit, 
  Trash2, 
  MapPin, 
  Home, 
  Building, 
  X,
  Check
} from 'lucide-react';

function AddressesPageContent() {
  
  const { user, isLoading } = useAuth();
  const router = useRouter();
  const { toast } = useToast();
  const toastRef = useRef(toast);
  const [addresses, setAddresses] = useState<Address[]>([]);
  const [loadingAddresses, setLoadingAddresses] = useState(true);
  const [showForm, setShowForm] = useState(false);
  const [editingAddress, setEditingAddress] = useState<Address | null>(null);
  const [saving, setSaving] = useState(false);
  const [deleteLoading, setDeleteLoading] = useState<string | null>(null);
  const [mounted, setMounted] = useState(false);
  
  
  useEffect(() => {
    setMounted(true);
  }, []);

  
  useEffect(() => {
    toastRef.current = toast;
  }, [toast]);
  
  const [formData, setFormData] = useState<AddressFormData>({
    type: 'shipping',
    isDefault: false,
    firstName: '',
    lastName: '',
    company: '',
    address1: '',
    address2: '',
    city: '',
    state: '',
    postalCode: '',
    country: 'US',
    phone: ''
  });
  const [errors, setErrors] = useState<Record<string, string>>({});

  const loadAddresses = useCallback(async () => {
    if (!user) return;
    
    try {
      setLoadingAddresses(true);
      const addressList = await addressService.getAddresses();
      setAddresses(addressList);
    } catch (error) {
      console.error('Failed to load addresses:', error);
      toastRef.current({
        title: 'Error!',
        description: 'Failed to load addresses.',
        variant: 'destructive',
      });
    } finally {
      setLoadingAddresses(false);
    }
  }, [user]);

  useEffect(() => {
    if (!isLoading && !user) {
      router.push('/auth/login');
      return;
    }

    if (user?.role === 'admin') {
      router.push('/dashboard');
      return;
    }

    if (user) {
      loadAddresses();
    }
  }, [user, isLoading, router, loadAddresses]);

  const validateForm = (): boolean => {
    const newErrors: Record<string, string> = {};

    if (!formData.firstName.trim()) {
      newErrors.firstName = 'First name is required';
    }

    if (!formData.lastName.trim()) {
      newErrors.lastName = 'Last name is required';
    }

    if (!formData.address1.trim()) {
      newErrors.address1 = 'Street address is required';
    }

    if (!formData.city.trim()) {
      newErrors.city = 'City is required';
    }

    if (!formData.state.trim()) {
      newErrors.state = 'State is required';
    }

    if (!formData.postalCode.trim()) {
      newErrors.postalCode = 'Postal code is required';
    }

    if (!formData.country.trim()) {
      newErrors.country = 'Country is required';
    }

    if (formData.phone && !/^\+?[\d\s-()]+$/.test(formData.phone)) {
      newErrors.phone = 'Please enter a valid phone number';
    }

    setErrors(newErrors);
    return Object.keys(newErrors).length === 0;
  };

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    
    if (!validateForm()) {
      return;
    }

    if (!user) return;

    try {
      setSaving(true);
      
      if (editingAddress) {
        await addressService.updateAddress(editingAddress.uuid, formData);
        toastRef.current({
          title: 'Success!',
          description: 'Address updated successfully.',
        });
      } else {
        await addressService.createAddress(formData);
        toastRef.current({
          title: 'Success!',
          description: 'Address added successfully.',
        });
      }
      
      loadAddresses();
      resetForm();
    } catch (error) {
      toastRef.current({
        title: 'Error!',
        description: error instanceof Error ? error.message : 'Failed to save address.',
        variant: 'destructive',
      });
    } finally {
      setSaving(false);
    }
  };

  const handleEdit = (address: Address) => {
    setEditingAddress(address);
    setFormData({
      type: address.type,
      isDefault: address.isDefault,
      firstName: address.firstName,
      lastName: address.lastName,
      company: address.company || '',
      address1: address.address1,
      address2: address.address2 || '',
      city: address.city,
      state: address.state,
      postalCode: address.postalCode,
      country: address.country,
      phone: address.phone || ''
    });
    setShowForm(true);
  };

  const handleDelete = async (addressId: string) => {
    if (!user) return;
    
    if (!confirm('Are you sure you want to delete this address? This action cannot be undone.')) {
      return;
    }

    try {
      setDeleteLoading(addressId);
      
      await addressService.deleteAddress(addressId);
      toastRef.current({
        title: 'Success!',
        description: 'Address deleted successfully.',
      });
      
      loadAddresses();
    } catch (error) {
      toastRef.current({
        title: 'Error!',
        description: error instanceof Error ? error.message : 'Failed to delete address.',
        variant: 'destructive',
      });
    } finally {
      setDeleteLoading(null);
    }
  };

  const resetForm = () => {
    setFormData({
      type: 'shipping',
      isDefault: false,
      firstName: '',
      lastName: '',
      company: '',
      address1: '',
      address2: '',
      city: '',
      state: '',
      postalCode: '',
      country: 'US',
      phone: ''
    });
    setErrors({});
    setEditingAddress(null);
    setShowForm(false);
  };

  
  if (!mounted || isLoading) {
    return (
      <div className="container mx-auto px-4 py-8">
        <div className="flex items-center justify-center py-12">
          <div className="animate-spin rounded-full h-8 w-8 border-b-2 border-red-600"></div>
          <p className="ml-2 text-muted-foreground">Loading addresses...</p>
        </div>
      </div>
    );
  }

  if (!user) {
    return null; 
  }

  return (
    <div className="container mx-auto px-4 py-8">
      <div className="grid gap-8 lg:grid-cols-4">
        {}
        <div className="lg:col-span-1">
          <ProfileSidebar />
        </div>

        {}
        <div className="lg:col-span-3 space-y-6">
          {}
          <div className="flex items-center justify-between">
            <div>
              <h1 className="text-3xl font-bold tracking-tight">Addresses</h1>
              <p className="text-muted-foreground">
                Manage your shipping and billing addresses
              </p>
            </div>
            {!showForm && (
              <Button onClick={() => setShowForm(true)}>
                <Plus className="mr-2 h-4 w-4" />
                Add Address
              </Button>
            )}
          </div>

          {}
          {showForm && (
            <Card>
              <CardHeader>
                <div className="flex items-center justify-between">
                  <div>
                    <CardTitle>
                      {editingAddress ? 'Edit Address' : 'Add New Address'}
                    </CardTitle>
                    <CardDescription>
                      {editingAddress 
                        ? 'Update your address information' 
                        : 'Add a new shipping or billing address'
                      }
                    </CardDescription>
                  </div>
                  <Button variant="ghost" size="icon" onClick={resetForm}>
                    <X className="h-4 w-4" />
                  </Button>
                </div>
              </CardHeader>
              <CardContent>
                <form onSubmit={handleSubmit} className="space-y-4">
                  <div className="grid gap-4 md:grid-cols-2">
                    <div className="space-y-2">
                      <Label htmlFor="type">Address Type</Label>
                      <Select
                        value={formData.type}
                        onValueChange={(value) => setFormData(prev => ({ ...prev, type: value as 'shipping' | 'billing' }))}
                      >
                        <SelectTrigger>
                          <SelectValue />
                        </SelectTrigger>
                        <SelectContent>
                          <SelectItem value="shipping">Shipping Address</SelectItem>
                          <SelectItem value="billing">Billing Address</SelectItem>
                        </SelectContent>
                      </Select>
                    </div>

                    <div className="flex items-center space-x-2 pt-8">
                      <Switch
                        id="isDefault"
                        checked={formData.isDefault}
                        onCheckedChange={(checked) => setFormData(prev => ({ ...prev, isDefault: checked }))}
                      />
                      <Label htmlFor="isDefault">Set as default address</Label>
                    </div>
                  </div>

                  <div className="grid gap-4 md:grid-cols-2">
                    <div className="space-y-2">
                      <Label htmlFor="firstName">First Name *</Label>
                      <Input
                        id="firstName"
                        value={formData.firstName}
                        onChange={(e) => setFormData(prev => ({ ...prev, firstName: e.target.value }))}
                        className={errors.firstName ? 'border-red-500' : ''}
                      />
                      {errors.firstName && <p className="text-sm text-red-600">{errors.firstName}</p>}
                    </div>

                    <div className="space-y-2">
                      <Label htmlFor="lastName">Last Name *</Label>
                      <Input
                        id="lastName"
                        value={formData.lastName}
                        onChange={(e) => setFormData(prev => ({ ...prev, lastName: e.target.value }))}
                        className={errors.lastName ? 'border-red-500' : ''}
                      />
                      {errors.lastName && <p className="text-sm text-red-600">{errors.lastName}</p>}
                    </div>
                  </div>

                  <div className="space-y-2">
                    <Label htmlFor="company">Company (Optional)</Label>
                    <Input
                      id="company"
                      value={formData.company}
                      onChange={(e) => setFormData(prev => ({ ...prev, company: e.target.value }))}
                      placeholder="Company name"
                    />
                  </div>

                  <div className="space-y-2">
                    <Label htmlFor="address1">Street Address *</Label>
                    <Input
                      id="address1"
                      value={formData.address1}
                      onChange={(e) => setFormData(prev => ({ ...prev, address1: e.target.value }))}
                      placeholder="123 Main Street"
                      className={errors.address1 ? 'border-red-500' : ''}
                    />
                    {errors.address1 && <p className="text-sm text-red-600">{errors.address1}</p>}
                  </div>

                  <div className="space-y-2">
                    <Label htmlFor="address2">Apartment, suite, etc. (Optional)</Label>
                    <Input
                      id="address2"
                      value={formData.address2}
                      onChange={(e) => setFormData(prev => ({ ...prev, address2: e.target.value }))}
                      placeholder="Apt 4B, Suite 100, etc."
                    />
                  </div>

                  <div className="grid gap-4 md:grid-cols-3">
                    <div className="space-y-2">
                      <Label htmlFor="city">City *</Label>
                      <Input
                        id="city"
                        value={formData.city}
                        onChange={(e) => setFormData(prev => ({ ...prev, city: e.target.value }))}
                        className={errors.city ? 'border-red-500' : ''}
                      />
                      {errors.city && <p className="text-sm text-red-600">{errors.city}</p>}
                    </div>

                    <div className="space-y-2">
                      <Label htmlFor="state">State *</Label>
                      <Input
                        id="state"
                        value={formData.state}
                        onChange={(e) => setFormData(prev => ({ ...prev, state: e.target.value }))}
                        className={errors.state ? 'border-red-500' : ''}
                      />
                      {errors.state && <p className="text-sm text-red-600">{errors.state}</p>}
                    </div>

                    <div className="space-y-2">
                      <Label htmlFor="postalCode">Postal Code *</Label>
                      <Input
                        id="postalCode"
                        value={formData.postalCode}
                        onChange={(e) => setFormData(prev => ({ ...prev, postalCode: e.target.value }))}
                        className={errors.postalCode ? 'border-red-500' : ''}
                      />
                      {errors.postalCode && <p className="text-sm text-red-600">{errors.postalCode}</p>}
                    </div>
                  </div>

                  <div className="grid gap-4 md:grid-cols-2">
                    <div className="space-y-2">
                      <Label htmlFor="country">Country *</Label>
                      <Select
                        value={formData.country}
                        onValueChange={(value) => setFormData(prev => ({ ...prev, country: value }))}
                      >
                        <SelectTrigger className={errors.country ? 'border-red-500' : ''}>
                          <SelectValue />
                        </SelectTrigger>
                        <SelectContent>
                          <SelectItem value="US">United States</SelectItem>
                          <SelectItem value="CA">Canada</SelectItem>
                          <SelectItem value="GB">United Kingdom</SelectItem>
                          <SelectItem value="TR">Turkey</SelectItem>
                          <SelectItem value="DE">Germany</SelectItem>
                          <SelectItem value="FR">France</SelectItem>
                        </SelectContent>
                      </Select>
                      {errors.country && <p className="text-sm text-red-600">{errors.country}</p>}
                    </div>

                    <div className="space-y-2">
                      <Label htmlFor="phone">Phone (Optional)</Label>
                      <Input
                        id="phone"
                        type="tel"
                        value={formData.phone}
                        onChange={(e) => setFormData(prev => ({ ...prev, phone: e.target.value }))}
                        placeholder="+1 (555) 123-4567"
                        className={errors.phone ? 'border-red-500' : ''}
                      />
                      {errors.phone && <p className="text-sm text-red-600">{errors.phone}</p>}
                    </div>
                  </div>

                  <div className="flex flex-col-reverse sm:flex-row sm:justify-end sm:space-x-2 pt-6">
                    <Button
                      type="button"
                      variant="outline"
                      onClick={resetForm}
                      disabled={saving}
                    >
                      Cancel
                    </Button>
                    <Button type="submit" disabled={saving}>
                      <Check className="mr-2 h-4 w-4" />
                      {saving ? 'Saving...' : editingAddress ? 'Update Address' : 'Add Address'}
                    </Button>
                  </div>
                </form>
              </CardContent>
            </Card>
          )}

          {}
          {loadingAddresses ? (
            <div className="text-center py-8">
              <div className="animate-spin rounded-full h-8 w-8 border-b-2 border-red-600 mx-auto"></div>
              <p className="text-muted-foreground mt-2">Loading addresses...</p>
            </div>
          ) : addresses.length === 0 ? (
            <Card>
              <CardContent className="text-center py-12">
                <MapPin className="mx-auto h-12 w-12 text-muted-foreground mb-4" />
                <h3 className="text-lg font-medium mb-2">No addresses found</h3>
                <p className="text-muted-foreground mb-6">
                  Add your first address to get started with orders
                </p>
                <Button onClick={() => setShowForm(true)}>
                  <Plus className="mr-2 h-4 w-4" />
                  Add Address
                </Button>
              </CardContent>
            </Card>
          ) : (
            <div className="grid gap-4 md:grid-cols-2">
              {addresses.map((address) => (
                <Card key={address.uuid} className="relative">
                  <CardHeader className="pb-3">
                    <div className="flex items-center justify-between">
                      <div className="flex items-center space-x-2">
                        {address.type === 'shipping' ? (
                          <Home className="h-4 w-4 text-muted-foreground" />
                        ) : (
                          <Building className="h-4 w-4 text-muted-foreground" />
                        )}
                        <CardTitle className="text-lg">
                          {address.type === 'shipping' ? 'Shipping Address' : 'Billing Address'}
                        </CardTitle>
                        {address.isDefault && (
                          <Badge variant="secondary" className="bg-green-100 text-green-800">
                            Default
                          </Badge>
                        )}
                      </div>
                      <div className="flex items-center space-x-1">
                        <Button
                          variant="ghost"
                          size="icon"
                          onClick={() => handleEdit(address)}
                          className="h-8 w-8"
                        >
                          <Edit className="h-3 w-3" />
                        </Button>
                        <Button
                          variant="ghost"
                          size="icon"
                          onClick={() => handleDelete(address.uuid)}
                          disabled={deleteLoading === address.uuid}
                          className="h-8 w-8 text-red-600 hover:text-red-700"
                        >
                          <Trash2 className="h-3 w-3" />
                        </Button>
                      </div>
                    </div>
                  </CardHeader>
                  <CardContent>
                    <div className="space-y-1 text-sm">
                      <p className="font-medium">
                        {address.firstName} {address.lastName}
                      </p>
                      {address.company && (
                        <p className="text-muted-foreground">{address.company}</p>
                      )}
                      <p>{address.address1}</p>
                      {address.address2 && <p>{address.address2}</p>}
                      <p>
                        {address.city}, {address.state} {address.postalCode}
                      </p>
                      <p>{address.country}</p>
                      {address.phone && (
                        <p className="text-muted-foreground">{address.phone}</p>
                      )}
                    </div>
                  </CardContent>
                </Card>
              ))}
            </div>
          )}
        </div>
      </div>
    </div>
  );
}

export default function AddressesPage() {
  return (
    <Suspense fallback={
      <div className="container mx-auto px-4 py-8">
        <div className="flex items-center justify-center py-12">
          <div className="animate-spin rounded-full h-8 w-8 border-b-2 border-red-600"></div>
          <p className="ml-2 text-muted-foreground">Loading addresses...</p>
        </div>
      </div>
    }>
      <AddressesPageContent />
    </Suspense>
  );
}