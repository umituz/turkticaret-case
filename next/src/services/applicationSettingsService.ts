import { apiClient } from '@/lib/api';
import { API_ENDPOINTS } from '@/lib/constants';
import { logError } from '@/lib/errorHandler';
import { BaseService } from './BaseService';
import type { 
  SettingResponse,
  SettingsUpdateResponse,
  SimpleAllSettings,
  GuestDefaults,
  SystemStatus,
  SettingsFormData,
  SettingsGroupType,
  ApplicationSetting
} from '@/types/settings';

interface SettingsFilters {
  group?: SettingsGroupType;
  active?: boolean;
}

class ApplicationSettingsService extends BaseService<ApplicationSetting, ApplicationSetting, SettingsFilters> {
  protected endpoint = 'admin/settings';

  protected mapFromApi(apiSetting: ApplicationSetting): ApplicationSetting {
    return apiSetting;
  }

  protected mapToApi(setting: Partial<ApplicationSetting>): Partial<ApplicationSetting> {
    return setting;
  }
  
  async getAllSettings(): Promise<SimpleAllSettings> {
    try {
      const response = await apiClient.get<SettingResponse>(API_ENDPOINTS.SETTINGS);
      const flatSettings = response.data as unknown as Record<string, string | number | boolean>;
      
      
      return {
        commerce: {
          default_currency: { value: flatSettings.default_currency },
          tax_enabled: { value: flatSettings.tax_enabled },
          shipping_enabled: { value: flatSettings.shipping_enabled },
        },
        localization: {
          default_language: { value: flatSettings.default_language },
          default_country: { value: flatSettings.default_country },
          default_timezone: { value: flatSettings.default_timezone },
        },
        system: {
          maintenance_mode: { value: flatSettings.maintenance_mode },
          app_name: { value: flatSettings.app_name },
          app_url: { value: flatSettings.app_url },
          registration_enabled: { value: flatSettings.registration_enabled },
        },
        ui: {
          items_per_page: { value: flatSettings.items_per_page },
          theme: { value: flatSettings.theme },
          logo_url: { value: flatSettings.logo_url },
        },
        notification: {
          email_notifications_enabled: { value: flatSettings.email_notifications_enabled },
          sms_notifications_enabled: { value: flatSettings.sms_notifications_enabled },
        },
      };
    } catch (error) {
      logError(error, 'ApplicationSettingsService_getAllSettings');
      throw error;
    }
  }

  
  async getSettingsByGroup(group: SettingsGroupType): Promise<{ data: ApplicationSetting[] }> {
    try {
      const response = await apiClient.get<{ success: boolean; message: string; data: { data: ApplicationSetting[] } }>(`${this.endpoint}/group/${group}`);
      return response.data;
    } catch (error) {
      console.error(`Failed to get settings for group ${group}:`, error);
      throw error;
    }
  }

  
  async getSetting(key: string): Promise<ApplicationSetting> {
    try {
      const response = await apiClient.get<SettingResponse>(`${this.endpoint}/${key}`);
      return response.data as ApplicationSetting;
    } catch (error) {
      console.error(`Failed to get setting ${key}:`, error);
      throw error;
    }
  }

  
  async updateSettings(settings: SettingsFormData): Promise<SettingsUpdateResponse> {
    try {
      const response = await apiClient.put<SettingsUpdateResponse>(this.endpoint, settings);
      return response;
    } catch (error) {
      console.error('Failed to update settings:', error);
      throw error;
    }
  }

  
  async updateSetting(key: string, value: string | boolean | number | Record<string, unknown>): Promise<SettingsUpdateResponse> {
    try {
      const response = await apiClient.put<SettingsUpdateResponse>(`${this.endpoint}/${key}`, { value });
      return response;
    } catch (error) {
      console.error(`Failed to update setting ${key}:`, error);
      throw error;
    }
  }

  
  async getGuestDefaults(): Promise<GuestDefaults> {
    try {
      const response = await apiClient.get<SettingResponse>('guest/settings/defaults');
      return response.data as GuestDefaults;
    } catch (error) {
      console.error('Failed to get guest defaults:', error);
      throw error;
    }
  }

  
  async getSystemStatus(): Promise<SystemStatus> {
    try {
      const response = await apiClient.get<SettingResponse>(API_ENDPOINTS.ADMIN.SETTINGS_STATUS);
      return response.data as SystemStatus;
    } catch (error) {
      console.error('Failed to get system status:', error);
      throw error;
    }
  }

  
  async updateCommerceSettings(settings: {
    default_currency?: string;
    tax_enabled?: boolean;
    shipping_enabled?: boolean;
  }): Promise<SettingsUpdateResponse> {
    return this.updateSettings(settings as SettingsFormData);
  }

  
  async updateLocalizationSettings(settings: {
    default_language?: string;
    default_country?: string;
    default_timezone?: string;
  }): Promise<SettingsUpdateResponse> {
    return this.updateSettings(settings as SettingsFormData);
  }

  
  async updateSystemSettings(settings: {
    maintenance_mode?: boolean;
    app_name?: string;
    app_url?: string;
    registration_enabled?: boolean;
  }): Promise<SettingsUpdateResponse> {
    return this.updateSettings(settings as SettingsFormData);
  }

  
  async updateUISettings(settings: {
    items_per_page?: number;
    theme?: string;
    logo_url?: string;
  }): Promise<SettingsUpdateResponse> {
    return this.updateSettings(settings as SettingsFormData);
  }

  
  async updateNotificationSettings(settings: {
    email_notifications_enabled?: boolean;
    sms_notifications_enabled?: boolean;
  }): Promise<SettingsUpdateResponse> {
    return this.updateSettings(settings as SettingsFormData);
  }
}

export const applicationSettingsService = new ApplicationSettingsService();