import { apiClient } from '@/lib/api';

export interface NotificationSettings {
  email: boolean;
  push: boolean;
  sms: boolean;
  marketing: boolean;
  orderUpdates: boolean;
}

export interface PreferenceSettings {
  language: string; 
  timezone: string;
}

export interface UserSettingsData {
  notifications?: NotificationSettings;
  preferences?: PreferenceSettings;
}

export interface UserSettingsResponse {
  success: boolean;
  message: string;
  data?: Record<string, unknown>;
  errors?: string[];
}

class SettingsService {
  async updateNotifications(notifications: NotificationSettings): Promise<UserSettingsResponse> {
    try {
      const response = await apiClient.put<UserSettingsResponse>('user/settings/notifications', {
        ...notifications
      });
      return response;
    } catch (error) {
      console.error('Failed to update notifications:', error);
      throw error;
    }
  }

  async updatePreferences(preferences: PreferenceSettings): Promise<UserSettingsResponse> {
    try {
      const response = await apiClient.put<UserSettingsResponse>('user/settings/preferences', {
        language_uuid: preferences.language,
        timezone: preferences.timezone
      });
      return response;
    } catch (error) {
      console.error('Failed to update preferences:', error);
      throw error;
    }
  }

  async getUserSettings(): Promise<UserSettingsData> {
    try {
      const response = await apiClient.get<{
        success: boolean;
        data: UserSettingsData;
      }>('user/settings');
      return response.data;
    } catch (error) {
      console.error('Failed to get user settings:', error);
      throw error;
    }
  }

  async changePassword(passwordData: {
    current_password: string;
    new_password: string;
    new_password_confirmation: string;
  }): Promise<UserSettingsResponse> {
    try {
      const response = await apiClient.put<UserSettingsResponse>('user/password', passwordData);
      return response;
    } catch (error) {
      console.error('Failed to change password:', error);
      throw error;
    }
  }
}

export const settingsService = new SettingsService();