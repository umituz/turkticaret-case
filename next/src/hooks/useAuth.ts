'use client';

import { useAppDispatch, useAppSelector } from '@/store/hooks';
import { loginUser, logoutUser, checkAuth, refreshProfile, clearError, updateUser as updateUserAction, LoginCredentials } from '@/store/slices/authSlice';
import { useEffect, useRef } from 'react';
import { User } from '@/types/user';
import { useRouter } from 'next/navigation';
import { STORAGE_KEYS } from '@/lib/constants';
import { setLogoutState } from '@/lib/logout-utils';

export function useAuth() {
  const dispatch = useAppDispatch();
  const router = useRouter();
  const { user, isLoading, isAuthenticated, error } = useAppSelector((state) => state.auth);
  const hasCheckedAuth = useRef(false);

  
  useEffect(() => {
    if (typeof window !== 'undefined' && !isAuthenticated && !user && !hasCheckedAuth.current && !isLoading) {
      
      const authCookie = document.cookie.includes('turkticaret_auth=true');
      if (!authCookie) {
        return; 
      }
      hasCheckedAuth.current = true;
      dispatch(checkAuth());
    }
  }, [dispatch, isAuthenticated, user, isLoading]);

  
  useEffect(() => {
    if (!isAuthenticated) return;

    const interval = setInterval(() => {
      dispatch(refreshProfile());
    }, 10 * 60 * 1000); 

    return () => clearInterval(interval);
  }, [dispatch, isAuthenticated]);

  const login = async (credentials: LoginCredentials) => {
    const result = await dispatch(loginUser(credentials));
    if (loginUser.fulfilled.match(result)) {
      return result.payload;
    }
    throw new Error(result.payload as string);
  };

  const logout = async (redirectPath: string = '/') => {
    try {
      // Set logout state using centralized manager
      setLogoutState(true);
      
      // Navigate first to prevent API calls on protected pages
      router.push(redirectPath);
      
      // Reset auth check flag
      hasCheckedAuth.current = false;
      
      // Perform logout
      await dispatch(logoutUser());
    } catch (error) {
      console.error('Logout failed:', error);
    } finally {
      // Clean up logout state after a delay
      setTimeout(() => {
        setLogoutState(false);
      }, 1500);
    }
  };

  const updateUserProfile = (updatedUser: User) => {
    dispatch(updateUserAction(updatedUser));
  };

  const refetchUser = () => {
    dispatch(checkAuth());
  };

  const refetchProfile = () => {
    dispatch(refreshProfile());
  };

  const clearAuthError = () => {
    dispatch(clearError());
  };

  return {
    
    user,
    isAuthenticated,
    isLoading,
    error,
    
    
    login,
    logout,
    updateUser: updateUserProfile,
    refetchUser,
    refetchProfile,
    clearAuthError,
    
    
    isLoginPending: isLoading,
    isLogoutPending: isLoading,
    loginError: error,
    authError: error,
  };
}