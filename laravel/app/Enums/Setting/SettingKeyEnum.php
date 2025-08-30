<?php

namespace App\Enums\Setting;

/**
 * System setting key enumeration
 * 
 * Defines all available system configuration keys used throughout the application.
 * This enum centralizes setting management and provides metadata for each setting
 * including type, group, default values, and descriptions. Settings are organized
 * into logical groups for better management and UI presentation.
 * 
 * @method static self DEFAULT_CURRENCY() Default currency setting
 * @method static self DEFAULT_LANGUAGE() Default language setting
 * @method static self MAINTENANCE_MODE() Maintenance mode toggle
 */
enum SettingKeyEnum: string
{
    // Commerce Settings
    /**
     * Default currency for the application
     * Used for guest users and as fallback when user preference is not set
     */
    case DEFAULT_CURRENCY = 'default_currency';
    
    /**
     * Default language for the interface
     * Applied to guest users and new registrations
     */
    case DEFAULT_LANGUAGE = 'default_language';
    
    /**
     * Default country for localization
     * Used for address forms and regional settings
     */
    case DEFAULT_COUNTRY = 'default_country';
    
    /**
     * Default timezone for date/time operations
     * Affects how dates are displayed and calculated
     */
    case DEFAULT_TIMEZONE = 'default_timezone';
    

    // System Settings
    /**
     * Maintenance mode toggle
     * When enabled, only admins can access the application
     */
    case MAINTENANCE_MODE = 'maintenance_mode';
    
    /**
     * Application display name
     * Used in emails, page titles, and branding
     */
    case APP_NAME = 'app_name';
    
    /**
     * Base URL of the application
     * Used for generating absolute URLs in emails and API responses
     */
    case APP_URL = 'app_url';
    

    // UI Settings
    /**
     * Default pagination size
     * Number of items shown per page in listings
     */
    case ITEMS_PER_PAGE = 'items_per_page';
    
    /**
     * Application theme identifier
     * Determines the visual theme used in the frontend
     */
    case THEME = 'theme';
    
    /**
     * Logo image URL or path
     * Used for branding throughout the application
     */
    case LOGO_URL = 'logo_url';

    // Notification Settings
    /**
     * Global email notification toggle
     * Master switch for all email communications
     */
    case EMAIL_NOTIFICATIONS_ENABLED = 'email_notifications_enabled';
    
    /**
     * Global SMS notification toggle
     * Master switch for all SMS communications
     */
    case SMS_NOTIFICATIONS_ENABLED = 'sms_notifications_enabled';

    /**
     * Get all available setting keys
     * 
     * Returns an array of all setting key values for validation
     * and configuration management interfaces.
     * 
     * @return array Array of setting key strings
     */
    public static function getAvailableKeys(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * Get human-readable label for the setting
     * 
     * Provides display-friendly names for settings in admin interfaces
     * and configuration panels.
     * 
     * @return string Formatted label for the setting
     */
    public function getLabel(): string
    {
        return match ($this) {
            self::DEFAULT_CURRENCY => 'Default Currency',
            self::DEFAULT_LANGUAGE => 'Default Language',
            self::DEFAULT_COUNTRY => 'Default Country',
            self::DEFAULT_TIMEZONE => 'Default Timezone',
            self::MAINTENANCE_MODE => 'Maintenance Mode',
            self::APP_NAME => 'Application Name',
            self::APP_URL => 'Application URL',
            self::ITEMS_PER_PAGE => 'Items Per Page',
            self::THEME => 'Application Theme',
            self::LOGO_URL => 'Logo URL',
            self::EMAIL_NOTIFICATIONS_ENABLED => 'Email Notifications Enabled',
            self::SMS_NOTIFICATIONS_ENABLED => 'SMS Notifications Enabled',
        };
    }

    /**
     * Get the setting group identifier
     * 
     * Groups related settings together for organized display
     * in configuration interfaces and better management.
     * 
     * @return string Setting group identifier
     */
    public function getGroup(): string
    {
        return match ($this) {
            self::DEFAULT_CURRENCY => 'commerce',
            self::DEFAULT_LANGUAGE, self::DEFAULT_COUNTRY, self::DEFAULT_TIMEZONE => 'localization',
            self::MAINTENANCE_MODE, self::APP_NAME, self::APP_URL => 'system',
            self::ITEMS_PER_PAGE, self::THEME, self::LOGO_URL => 'ui',
            self::EMAIL_NOTIFICATIONS_ENABLED, self::SMS_NOTIFICATIONS_ENABLED => 'notification',
        };
    }

    /**
     * Get the data type of the setting value
     * 
     * Defines the expected data type for validation and
     * proper form input generation in admin interfaces.
     * 
     * @return string Data type (string, boolean, integer)
     */
    public function getType(): string
    {
        return match ($this) {
            self::DEFAULT_CURRENCY, self::DEFAULT_LANGUAGE, self::DEFAULT_COUNTRY => 'string',
            self::MAINTENANCE_MODE, self::EMAIL_NOTIFICATIONS_ENABLED,
            self::SMS_NOTIFICATIONS_ENABLED => 'boolean',
            self::ITEMS_PER_PAGE => 'integer',
            self::DEFAULT_TIMEZONE, self::APP_NAME, self::APP_URL,
            self::THEME, self::LOGO_URL => 'string',
        };
    }

    /**
     * Get the default value for the setting
     * 
     * Provides sensible defaults for initial setup and when
     * settings are reset. Returns value wrapped in array format
     * consistent with database storage structure.
     * 
     * @return array Array with 'value' key containing the default
     */
    public function getDefaultValue(): mixed
    {
        $rawValue = match ($this) {
            self::DEFAULT_CURRENCY => '₺',
            self::DEFAULT_LANGUAGE => 'tr',
            self::DEFAULT_COUNTRY => 'TR',
            self::DEFAULT_TIMEZONE => 'Europe/Istanbul',
            self::MAINTENANCE_MODE => false,
            self::APP_NAME => 'Ecommerce',
            self::APP_URL => 'http://localhost:8080',
            self::ITEMS_PER_PAGE => 20,
            self::THEME => 'default',
            self::LOGO_URL => '/images/logo.png',
            self::EMAIL_NOTIFICATIONS_ENABLED => true,
            self::SMS_NOTIFICATIONS_ENABLED => false,
        };

        return ['value' => $rawValue];
    }

    /**
     * Get the setting description
     * 
     * Provides detailed explanations of what each setting controls,
     * helping administrators understand the impact of changes.
     * 
     * @return string Detailed description of the setting's purpose
     */
    public function getDescription(): string
    {
        return match ($this) {
            self::DEFAULT_CURRENCY => 'Default currency for guest users and new registrations',
            self::DEFAULT_LANGUAGE => 'Default language for guest users',
            self::DEFAULT_COUNTRY => 'Default country for guest users',
            self::DEFAULT_TIMEZONE => 'Default timezone for the application',
            self::MAINTENANCE_MODE => 'Put application in maintenance mode',
            self::APP_NAME => 'Application display name',
            self::APP_URL => 'Base URL of the application',
            self::ITEMS_PER_PAGE => 'Default number of items per page in listings',
            self::THEME => 'Default theme for the application',
            self::LOGO_URL => 'URL or path to the application logo',
            self::EMAIL_NOTIFICATIONS_ENABLED => 'Enable email notifications globally',
            self::SMS_NOTIFICATIONS_ENABLED => 'Enable SMS notifications globally',
        };
    }
}
