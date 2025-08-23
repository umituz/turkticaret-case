<?php

namespace App\Enums\Setting;

enum SettingKeyEnum: string
{
    // Commerce Settings
    case DEFAULT_CURRENCY = 'default_currency';
    case DEFAULT_LANGUAGE = 'default_language';
    case DEFAULT_COUNTRY = 'default_country';
    case DEFAULT_TIMEZONE = 'default_timezone';
    case TAX_ENABLED = 'tax_enabled';
    case SHIPPING_ENABLED = 'shipping_enabled';

    // System Settings
    case MAINTENANCE_MODE = 'maintenance_mode';
    case APP_NAME = 'app_name';
    case APP_URL = 'app_url';
    case REGISTRATION_ENABLED = 'registration_enabled';

    // UI Settings
    case ITEMS_PER_PAGE = 'items_per_page';
    case THEME = 'theme';
    case LOGO_URL = 'logo_url';

    // Notification Settings
    case EMAIL_NOTIFICATIONS_ENABLED = 'email_notifications_enabled';
    case SMS_NOTIFICATIONS_ENABLED = 'sms_notifications_enabled';

    public static function getAvailableKeys(): array
    {
        return array_column(self::cases(), 'value');
    }

    public function getLabel(): string
    {
        return match ($this) {
            self::DEFAULT_CURRENCY => 'Default Currency',
            self::DEFAULT_LANGUAGE => 'Default Language',
            self::DEFAULT_COUNTRY => 'Default Country',
            self::DEFAULT_TIMEZONE => 'Default Timezone',
            self::TAX_ENABLED => 'Tax Enabled',
            self::SHIPPING_ENABLED => 'Shipping Enabled',
            self::MAINTENANCE_MODE => 'Maintenance Mode',
            self::APP_NAME => 'Application Name',
            self::APP_URL => 'Application URL',
            self::REGISTRATION_ENABLED => 'User Registration Enabled',
            self::ITEMS_PER_PAGE => 'Items Per Page',
            self::THEME => 'Application Theme',
            self::LOGO_URL => 'Logo URL',
            self::EMAIL_NOTIFICATIONS_ENABLED => 'Email Notifications Enabled',
            self::SMS_NOTIFICATIONS_ENABLED => 'SMS Notifications Enabled',
        };
    }

    public function getGroup(): string
    {
        return match ($this) {
            self::DEFAULT_CURRENCY, self::TAX_ENABLED, self::SHIPPING_ENABLED => 'commerce',
            self::DEFAULT_LANGUAGE, self::DEFAULT_COUNTRY, self::DEFAULT_TIMEZONE => 'localization',
            self::MAINTENANCE_MODE, self::APP_NAME, self::APP_URL, self::REGISTRATION_ENABLED => 'system',
            self::ITEMS_PER_PAGE, self::THEME, self::LOGO_URL => 'ui',
            self::EMAIL_NOTIFICATIONS_ENABLED, self::SMS_NOTIFICATIONS_ENABLED => 'notification',
        };
    }

    public function getType(): string
    {
        return match ($this) {
            self::DEFAULT_CURRENCY, self::DEFAULT_LANGUAGE, self::DEFAULT_COUNTRY => 'string',
            self::TAX_ENABLED, self::SHIPPING_ENABLED, self::MAINTENANCE_MODE,
            self::REGISTRATION_ENABLED, self::EMAIL_NOTIFICATIONS_ENABLED,
            self::SMS_NOTIFICATIONS_ENABLED => 'boolean',
            self::ITEMS_PER_PAGE => 'integer',
            self::DEFAULT_TIMEZONE, self::APP_NAME, self::APP_URL,
            self::THEME, self::LOGO_URL => 'string',
        };
    }

    public function getDefaultValue(): mixed
    {
        $rawValue = match ($this) {
            self::DEFAULT_CURRENCY => 'TRY',
            self::DEFAULT_LANGUAGE => 'tr',
            self::DEFAULT_COUNTRY => 'TR',
            self::DEFAULT_TIMEZONE => 'Europe/Istanbul',
            self::TAX_ENABLED => true,
            self::SHIPPING_ENABLED => true,
            self::MAINTENANCE_MODE => false,
            self::APP_NAME => 'TurkTicaret',
            self::APP_URL => 'http://localhost:8080',
            self::REGISTRATION_ENABLED => true,
            self::ITEMS_PER_PAGE => 20,
            self::THEME => 'default',
            self::LOGO_URL => '/images/logo.png',
            self::EMAIL_NOTIFICATIONS_ENABLED => true,
            self::SMS_NOTIFICATIONS_ENABLED => false,
        };

        return ['value' => $rawValue];
    }

    public function getDescription(): string
    {
        return match ($this) {
            self::DEFAULT_CURRENCY => 'Default currency for guest users and new registrations',
            self::DEFAULT_LANGUAGE => 'Default language for guest users',
            self::DEFAULT_COUNTRY => 'Default country for guest users',
            self::DEFAULT_TIMEZONE => 'Default timezone for the application',
            self::TAX_ENABLED => 'Enable tax calculations globally',
            self::SHIPPING_ENABLED => 'Enable shipping functionality',
            self::MAINTENANCE_MODE => 'Put application in maintenance mode',
            self::APP_NAME => 'Application display name',
            self::APP_URL => 'Base URL of the application',
            self::REGISTRATION_ENABLED => 'Allow new user registrations',
            self::ITEMS_PER_PAGE => 'Default number of items per page in listings',
            self::THEME => 'Default theme for the application',
            self::LOGO_URL => 'URL or path to the application logo',
            self::EMAIL_NOTIFICATIONS_ENABLED => 'Enable email notifications globally',
            self::SMS_NOTIFICATIONS_ENABLED => 'Enable SMS notifications globally',
        };
    }
}
