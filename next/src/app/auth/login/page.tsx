'use client';

import { useState } from 'react';
import Link from 'next/link';
import { Card, CardContent, CardDescription, CardFooter, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Separator } from '@/components/ui/separator';
import { Eye, EyeOff, Mail, Lock, ArrowLeft } from 'lucide-react';
import { useAuth } from '@/hooks/useAuth';
import { useForm } from '@/hooks/useForm';
import { validationRules, combineValidators } from '@/lib/validation';

interface LoginFormData extends Record<string, unknown> {
  email: string;
  password: string;
}

export default function LoginPage() {
  const { login, isLoginPending } = useAuth();
  const [showPassword, setShowPassword] = useState(true);

  const form = useForm<LoginFormData>({
    initialValues: {
      email: 'user@test.com',
      password: 'user123'
    },
    validationRules: {
      email: combineValidators(
        validationRules.required('Email'),
        validationRules.email
      ),
      password: validationRules.required('Password')
    },
    onSubmit: async (values) => {
      try {
        await login(values);
        // Redirect is handled by the login function
      } catch (error) {
        console.error('Login failed:', error);
        throw new Error('Invalid email or password');
      }
    },
    successMessage: 'Login successful! Welcome back!',
  });

  return (
    <div className="min-h-screen bg-gradient-to-br from-background via-red-50/20 to-red-100/20 flex items-center justify-center p-4">
      <div className="w-full max-w-md space-y-6">
        <Link href="/" className="inline-flex items-center text-sm text-muted-foreground hover:text-red-600 transition-colors">
          <ArrowLeft className="w-4 h-4 mr-2" />
          Back to Home
        </Link>

        <div className="text-center">
          <h1 className="text-3xl font-bold bg-gradient-to-r from-blue-600 to-blue-700 bg-clip-text text-transparent mb-2">
            Ecommerce
          </h1>
          <p className="text-muted-foreground">Sign in to your account</p>
        </div>

        <Card className="border-border/50 shadow-lg">
          <CardHeader className="space-y-1">
            <CardTitle className="text-2xl text-center">Sign In</CardTitle>
            <CardDescription className="text-center">
              Enter your email address and password
            </CardDescription>
          </CardHeader>
          
          <form onSubmit={form.handleSubmit}>
            <CardContent className="space-y-4 pb-6">
              <div className="space-y-2">
                <Label htmlFor="email">Email</Label>
                <div className="relative">
                  <Mail className="absolute left-3 top-1/2 transform -translate-y-1/2 text-muted-foreground h-4 w-4" />
                  <Input
                    id="email"
                    name="email"
                    type="email"
                    placeholder="example@email.com"
                    value={form.values.email}
                    onChange={(e) => form.handleChange('email', e.target.value)}
                    className="pl-10"
                  />
                  {form.errors.email && (
                    <p className="text-sm text-red-600 mt-1">{form.errors.email}</p>
                  )}
                </div>
              </div>

              <div className="space-y-2">
                <Label htmlFor="password">Password</Label>
                <div className="relative">
                  <Lock className="absolute left-3 top-1/2 transform -translate-y-1/2 text-muted-foreground h-4 w-4" />
                  <Input
                    id="password"
                    name="password"
                    type={showPassword ? 'text' : 'password'}
                    placeholder="••••••••"
                    value={form.values.password}
                    onChange={(e) => form.handleChange('password', e.target.value)}
                    className="pl-10 pr-10"
                  />
                  <Button
                    type="button"
                    variant="ghost"
                    size="icon"
                    className="absolute right-0 top-0 h-full px-3 hover:bg-transparent"
                    onClick={() => setShowPassword(!showPassword)}
                  >
                    {showPassword ? (
                      <EyeOff className="h-4 w-4 text-muted-foreground" />
                    ) : (
                      <Eye className="h-4 w-4 text-muted-foreground" />
                    )}
                  </Button>
                </div>
                {form.errors.password && (
                  <p className="text-sm text-red-600 mt-1">{form.errors.password}</p>
                )}
              </div>

            </CardContent>

            <CardFooter className="flex flex-col space-y-4">
              <Button 
                type="submit"
                disabled={form.loading || isLoginPending}
                className="w-full bg-gradient-to-r from-red-600 to-red-700 hover:from-red-700 hover:to-red-800 transition-all duration-300"
              >
                {(form.loading || isLoginPending) ? 'Signing In...' : 'Sign In'}
              </Button>

              <Separator />

              <div className="text-center text-sm text-muted-foreground">
                Don&apos;t have an account?{' '}
                <Link href="/auth/register" className="text-red-600 hover:underline font-medium">
                  Sign Up
                </Link>
              </div>
            </CardFooter>
          </form>
        </Card>

      </div>
    </div>
  );
}