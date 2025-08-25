'use client';

import { useState, useEffect } from 'react';
import { useAuth } from '@/hooks/useAuth';
import { useRouter } from 'next/navigation';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { ProfileSidebar } from '@/components/profile/ProfileSidebar';
import { useToast } from '@/hooks/use-toast';
import { authService } from '@/services/authService';
import { ArrowLeft, Save, User as UserIcon } from 'lucide-react';

export default function EditProfilePage() {
  const { user, isLoading, updateUser } = useAuth();
  const router = useRouter();
  const { toast } = useToast();
  const [saving, setSaving] = useState(false);
  const [formData, setFormData] = useState({
    name: '',
    email: ''
  });
  const [errors, setErrors] = useState<Record<string, string>>({});

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
      setFormData({
        name: user.name || '',
        email: user.email || ''
      });
    }
  }, [user, isLoading, router]);

  const validateForm = (): boolean => {
    const newErrors: Record<string, string> = {};

    const name = formData.name || '';
    const email = formData.email || '';

    if (!name.trim()) {
      newErrors.name = 'Name is required';
    } else if (name.trim().length < 2) {
      newErrors.name = 'Name must be at least 2 characters';
    }

    if (!email.trim()) {
      newErrors.email = 'Email is required';
    } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
      newErrors.email = 'Please enter a valid email address';
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
      const updatedUserData = await authService.updateProfile({
        name: formData.name,
        email: formData.email
      });
      
      
      const updatedUser = {
        ...user!,
        ...updatedUserData
      };
      updateUser(updatedUser);
      
      toast({
        title: 'Success!',
        description: 'Your profile has been updated successfully.',
      });
      
      router.push('/account');
    } catch (error) {
      toast({
        title: 'Error!',
        description: error instanceof Error ? error.message : 'Failed to update profile.',
        variant: 'destructive',
      });
    } finally {
      setSaving(false);
    }
  };

  if (isLoading || !user) {
    return (
      <div className="container mx-auto px-4 py-8">
        <div className="flex items-center justify-center py-12">
          <div className="animate-spin rounded-full h-8 w-8 border-b-2 border-red-600"></div>
          <p className="ml-2 text-muted-foreground">Loading profile...</p>
        </div>
      </div>
    );
  }

  return (
    <div className="container mx-auto px-4 py-8">
      <div className="grid gap-8 lg:grid-cols-4">
        {}
        <div className="lg:col-span-1">
          <ProfileSidebar />
        </div>

        {}
        <div className="lg:col-span-3">
          <div className="space-y-6">
            {}
            <div className="flex items-center space-x-4">
              <Button variant="ghost" size="icon" onClick={() => router.back()}>
                <ArrowLeft className="h-4 w-4" />
              </Button>
              <div>
                <h1 className="text-3xl font-bold tracking-tight">Edit Profile</h1>
                <p className="text-muted-foreground">
                  Update your personal information and preferences
                </p>
              </div>
            </div>

            {}
            <form onSubmit={handleSubmit} className="space-y-8">
              {}
              <Card>
                <CardHeader>
                  <CardTitle>Profile Information</CardTitle>
                  <CardDescription>
                    Update your basic profile information
                  </CardDescription>
                </CardHeader>
                <CardContent className="space-y-6">
                  {}
                  <div className="flex items-center space-x-4">
                    <div className="w-20 h-20 rounded-full bg-muted flex items-center justify-center">
                      <UserIcon className="h-10 w-10 text-muted-foreground" />
                    </div>
                    <div>
                      <p className="text-sm font-medium">{user?.name}</p>
                      <p className="text-sm text-muted-foreground">Member since {user?.created_at ? new Date(user.created_at).toLocaleDateString() : 'Unknown'}</p>
                    </div>
                  </div>

                  {}
                  <div className="grid gap-4 md:grid-cols-2">
                    <div className="space-y-2">
                      <Label htmlFor="name">Display Name *</Label>
                      <Input
                        id="name"
                        value={formData.name || ''}
                        onChange={(e) => setFormData(prev => ({ ...prev, name: e.target.value }))}
                        placeholder="Enter your display name"
                        className={errors.name ? 'border-red-500' : ''}
                      />
                      {errors.name && <p className="text-sm text-red-600">{errors.name}</p>}
                    </div>

                    <div className="space-y-2">
                      <Label htmlFor="email">Email Address *</Label>
                      <Input
                        id="email"
                        type="email"
                        value={formData.email || ''}
                        onChange={(e) => setFormData(prev => ({ ...prev, email: e.target.value }))}
                        placeholder="your@email.com"
                        className={errors.email ? 'border-red-500' : ''}
                      />
                      {errors.email && <p className="text-sm text-red-600">{errors.email}</p>}
                    </div>
                  </div>

                  {}
                  <div className="pt-4 border-t">
                    <h4 className="text-sm font-medium mb-3">Account Information</h4>
                    <div className="grid gap-3 text-sm">
                      <div className="flex justify-between">
                        <span className="text-muted-foreground">Member Since:</span>
                        <span>{user?.created_at ? new Date(user.created_at).toLocaleDateString() : 'Unknown'}</span>
                      </div>
                      <div className="flex justify-between">
                        <span className="text-muted-foreground">Account Type:</span>
                        <span className="capitalize">{user?.role || 'User'}</span>
                      </div>
                    </div>
                  </div>
                </CardContent>
              </Card>

              {}
              <Card>
                <CardContent className="pt-6">
                  <div className="flex flex-col-reverse sm:flex-row sm:justify-end sm:space-x-2">
                    <Button
                      type="button"
                      variant="outline"
                      onClick={() => router.back()}
                      disabled={saving}
                    >
                      Cancel
                    </Button>
                    <Button type="submit" disabled={saving}>
                      <Save className="mr-2 h-4 w-4" />
                      {saving ? 'Saving...' : 'Save Changes'}
                    </Button>
                  </div>
                </CardContent>
              </Card>
            </form>
          </div>
        </div>
      </div>
    </div>
  );
}