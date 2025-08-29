'use client';

import { useAppDispatch, useAppSelector } from '@/store/hooks';
import { useAuth } from '@/hooks/useAuth';
import { 
  addItem, 
  removeItem, 
  updateQuantity, 
  clearCart, 
  toggleCart, 
  closeCart, 
  openCart,
  saveToStorage,
  loadCart,
  syncCartWithAPI,
  removeFromCartAPI,
  updateCartItemAPI,
  clearCartAPI
} from '@/store/slices/cartSlice';
import { CartItem } from '@/services/cartService';
import { useEffect } from 'react';

export function useCart() {
  const dispatch = useAppDispatch();
  const { items, total, itemCount, isOpen, isLoading, error } = useAppSelector((state) => state.cart);
  const { isAuthenticated, isLoading: authLoading } = useAuth();

  
  useEffect(() => {
    if (authLoading) return; 

    dispatch(loadCart(isAuthenticated));
  }, [dispatch, isAuthenticated, authLoading]);

  
  useEffect(() => {
    if (!authLoading && items.length >= 0) {
      dispatch(saveToStorage());
    }
  }, [dispatch, items, authLoading]);

  const addToCart = (item: Omit<CartItem, 'uuid'>) => {
    dispatch(addItem(item));
  };

  const removeFromCart = (uuid: string) => {
    if (isAuthenticated) {
      
      const item = items.find(item => item.uuid === uuid);
      if (item) {
        dispatch(removeFromCartAPI({ productUuid: item.productUuid, isAuthenticated }));
      }
    } else {
      dispatch(removeItem(uuid));
    }
  };

  const updateItemQuantity = (uuid: string, quantity: number) => {
    if (isAuthenticated) {
      
      const item = items.find(item => item.uuid === uuid);
      if (item) {
        dispatch(updateCartItemAPI({ productUuid: item.productUuid, quantity, isAuthenticated }));
      }
    } else {
      dispatch(updateQuantity({ uuid, quantity }));
    }
  };

  const clearAllItems = () => {
    if (isAuthenticated) {
      dispatch(clearCartAPI(isAuthenticated));
    } else {
      dispatch(clearCart());
    }
  };

  const toggleCartSidebar = () => {
    dispatch(toggleCart());
  };

  const closeCartSidebar = () => {
    dispatch(closeCart());
  };

  const openCartSidebar = () => {
    dispatch(openCart());
  };

  const syncWithAPI = (localItems: CartItem[]) => {
    dispatch(syncCartWithAPI(localItems));
  };

  return {
    
    items,
    total,
    itemCount,
    isOpen,
    isLoading,
    error,
    
    
    addItem: addToCart,
    removeItem: removeFromCart,
    updateQuantity: updateItemQuantity,
    clearCart: clearAllItems,
    toggleCart: toggleCartSidebar,
    closeCart: closeCartSidebar,
    openCart: openCartSidebar,
    syncWithAPI,
  };
}