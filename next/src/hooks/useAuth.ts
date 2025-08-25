'use client';

import { useAppDispatch, useAppSelector } from '@/store/hooks';
import { loginUser, logoutUser, checkAuth, refreshProfile, clearError, updateUser as updateUserAction, LoginCredentials } from '@/store/slices/authSlice';
import { useEffect, useRef } from 'react';
import { User } from '@/types/user';

export function useAuth() {
  const dispatch = useAppDispatch();
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

  const logout = async () => {
    hasCheckedAuth.current = false; 
    await dispatch(logoutUser());
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