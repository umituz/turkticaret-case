'use client';

import { useAppDispatch, useAppSelector } from '@/store/hooks';
import { 
  loadSettings,
  loadSystemStatus,
  updateUISettings,
  updateCommerceSettings,
  updateSystemSettings,
  updateLocalizationSettings,
  updateNotificationSettings,
  clearError
} from '@/store/slices/settingsSlice';
import { useEffect } from 'react';
import { useAuth } from '@/hooks/useAuth';
import type { SettingsFormData } from '@/types/settings';

export function useSettings() {
  const dispatch = useAppDispatch();
  const { settings, systemStatus, isLoading, error } = useAppSelector((state) => state.settings);
  const { isAuthenticated } = useAuth();

  
  useEffect(() => {
    if (!settings) {
      dispatch(loadSettings());
    }
    
    if (!systemStatus && isAuthenticated) {
      dispatch(loadSystemStatus());
    }
  }, [dispatch, settings, systemStatus, isAuthenticated]);

  const updateUI = async (uiSettings: Partial<SettingsFormData>) => {
    const result = await dispatch(updateUISettings(uiSettings));
    if (updateUISettings.fulfilled.match(result)) {
      return result.payload;
    }
    throw new Error(result.payload as string);
  };

  const updateCommerce = async (commerceSettings: Partial<SettingsFormData>) => {
    const result = await dispatch(updateCommerceSettings(commerceSettings));
    if (updateCommerceSettings.fulfilled.match(result)) {
      return result.payload;
    }
    throw new Error(result.payload as string);
  };

  const updateSystem = async (systemSettings: Partial<SettingsFormData>) => {
    const result = await dispatch(updateSystemSettings(systemSettings));
    if (updateSystemSettings.fulfilled.match(result)) {
      return result.payload;
    }
    throw new Error(result.payload as string);
  };

  const updateLocalization = async (localizationSettings: Partial<SettingsFormData>) => {
    const result = await dispatch(updateLocalizationSettings(localizationSettings));
    if (updateLocalizationSettings.fulfilled.match(result)) {
      return result.payload;
    }
    throw new Error(result.payload as string);
  };

  const updateNotifications = async (notificationSettings: Partial<SettingsFormData>) => {
    const result = await dispatch(updateNotificationSettings(notificationSettings));
    if (updateNotificationSettings.fulfilled.match(result)) {
      return result.payload;
    }
    throw new Error(result.payload as string);
  };

  const refreshSettings = () => {
    dispatch(loadSettings());
    
    if (isAuthenticated) {
      dispatch(loadSystemStatus());
    }
  };

  const clearSettingsError = () => {
    dispatch(clearError());
  };

  
  const getTheme = () => settings?.ui?.theme?.value as string || 'default';
  const getItemsPerPage = () => settings?.ui?.items_per_page?.value as number || 20;
  const getLogoUrl = () => settings?.ui?.logo_url?.value as string || '/images/logo.png';
  const getCurrency = () => settings?.commerce?.default_currency?.value as string || 'TRY';
  const getLanguage = () => settings?.localization?.default_language?.value as string || 'tr';

  return {
    
    settings,
    systemStatus,
    isLoading,
    error,
    
    
    updateUI,
    updateCommerce,
    updateSystem,
    updateLocalization,
    updateNotifications,
    refreshSettings,
    clearError: clearSettingsError,
    
    
    getTheme,
    getItemsPerPage,
    getLogoUrl,
    getCurrency,
    getLanguage,
  };
}