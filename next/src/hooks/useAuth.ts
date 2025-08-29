'use client';

import { useSession, signIn, signOut } from "next-auth/react";
import { useRouter } from "next/navigation";
import { useState } from "react";

interface LoginCredentials {
  email: string;
  password: string;
}

export function useAuth() {
  const { data: session, status, update } = useSession();
  const router = useRouter();
  const [isLoading, setIsLoading] = useState(false);

  const login = async (credentials: LoginCredentials) => {
    try {
      setIsLoading(true);
      const result = await signIn("credentials", {
        email: credentials.email,
        password: credentials.password,
        redirect: false,
      });

      if (result?.error) {
        throw new Error(result.error);
      }

      // Single session refresh after login
      const updatedSession = await update();
      const userRole = updatedSession?.user?.role;
      
      if (userRole === "admin") {
        router.push("/dashboard");
      } else {
        router.push("/"); // Non-admin users go to homepage
      }

      return result;
    } catch (error) {
      throw error;
    } finally {
      setIsLoading(false);
    }
  };

  const logout = async (redirectTo: string = "/") => {
    try {
      setIsLoading(true);
      await signOut({ 
        redirect: false 
      });
      router.push(redirectTo);
    } catch (error) {
      console.error("Logout failed:", error);
    } finally {
      setIsLoading(false);
    }
  };

  const refreshSession = async () => {
    await update();
  };

  return {
    // Session data
    user: session?.user || null,
    isAuthenticated: status === "authenticated",
    isLoading: status === "loading" || isLoading,
    error: null,
    
    // Actions
    login,
    logout,
    updateUser: refreshSession,
    refetchUser: refreshSession,
    refetchProfile: refreshSession,
    clearAuthError: () => {},
    
    // Computed properties
    isAdmin: session?.user?.role === "admin",
    isUser: session?.user?.role === "user",
    isLoginPending: isLoading,
    isLogoutPending: isLoading,
    loginError: null,
    authError: null,
  };
}