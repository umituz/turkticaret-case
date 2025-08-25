export interface ApplicationSetting {
  uuid: string;
  key: string;
  value: string | boolean | number | Record<string, unknown>;
  raw_value: Record<string, unknown>;
  type: 'string' | 'boolean' | 'integer' | 'json';
  group: 'commerce' | 'localization' | 'system' | 'ui' | 'notification';
  description: string;
  is_active: boolean;
  is_editable: boolean;
  value_as_string: string;
  created_at: string;
  updated_at: string;
}

export interface SettingsGroup {
  [key: string]: ApplicationSetting;
}

export interface SimpleSettingsGroup {
  [key: string]: { value: string | number | boolean };
}

export interface AllSettings {
  commerce: SettingsGroup;
  localization: SettingsGroup;
  system: SettingsGroup;
  ui: SettingsGroup;
  notification: SettingsGroup;
}

export interface SimpleAllSettings {
  commerce: SimpleSettingsGroup;
  localization: SimpleSettingsGroup;
  system: SimpleSettingsGroup;
  ui: SimpleSettingsGroup;
  notification: SimpleSettingsGroup;
}

export interface GuestDefaults {
  currency: string;
  language: string;
  country: string;
  timezone: string;
}

export interface SystemStatus {
  maintenance_mode: boolean;
  registration_enabled: boolean;
  email_notifications: boolean;
  sms_notifications: boolean;
}

export interface CommerceSettings {
  default_currency: string;
  tax_enabled: boolean;
  shipping_enabled: boolean;
}

export interface LocalizationSettings {
  default_language: string;
  default_country: string;
  default_timezone: string;
}

export interface SystemSettings {
  maintenance_mode: boolean;
  app_name: string;
  app_url: string;
  registration_enabled: boolean;
}

export interface UISettings {
  items_per_page: number;
  theme: string;
  logo_url: string;
}

export interface NotificationSettings {
  email_notifications_enabled: boolean;
  sms_notifications_enabled: boolean;
}

export interface SettingsUpdateRequest {
  [key: string]: string | boolean | number | Record<string, unknown>;
}

export interface SettingsUpdateResponse {
  success: boolean;
  message: string;
  data?: string[];
  errors?: string[];
}

export interface SettingResponse {
  success: boolean;
  message: string;
  data: ApplicationSetting | AllSettings | GuestDefaults | SystemStatus;
  errors?: string[];
}

export interface SettingsFormData {
  
  default_currency?: string;
  tax_enabled?: boolean;
  shipping_enabled?: boolean;
  
  
  default_language?: string;
  default_country?: string;
  default_timezone?: string;
  
  
  maintenance_mode?: boolean;
  app_name?: string;
  app_url?: string;
  registration_enabled?: boolean;
  
  
  items_per_page?: number;
  theme?: string;
  logo_url?: string;
  
  
  email_notifications_enabled?: boolean;
  sms_notifications_enabled?: boolean;
}

export type SettingsGroupType = 'commerce' | 'localization' | 'system' | 'ui' | 'notification';