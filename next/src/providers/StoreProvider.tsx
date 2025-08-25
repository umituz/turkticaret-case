'use client';

import { useRef } from 'react';
import { Provider } from 'react-redux';
import { makeStore, AppStore } from '@/store/store';
import { initializeAuth } from '@/store/slices/authSlice';
import { authService } from '@/services/authService';
import { SecureStorage } from '@/lib/security';
import { STORAGE_KEYS } from '@/lib/constants';

export default function StoreProvider({
  children,
}: {
  children: React.ReactNode;
}) {
  const storeRef = useRef<AppStore | null>(null);
  
  if (!storeRef.current) {
    
    storeRef.current = makeStore();
    
    
    if (typeof window !== 'undefined') {
      const isAuth = SecureStorage.getItem(STORAGE_KEYS.AUTH_STATUS);
      const user = authService.getCurrentUser();
      const token = SecureStorage.getItem(STORAGE_KEYS.AUTH_TOKEN);
      
      if (isAuth === 'true' && user && token) {
        storeRef.current.dispatch(initializeAuth({ user, token }));
      }
    }
  }

  return <Provider store={storeRef.current}>{children}</Provider>;
}