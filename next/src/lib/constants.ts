
export const STORAGE_KEYS = {
  AUTH_TOKEN: 'turkticaret_token',
  AUTH_STATUS: 'turkticaret_auth',
  USER_DATA: 'turkticaret_user',
  CART_DATA: 'turkticaret_cart',
  CSRF_TOKEN: 'csrf_token',
  LOGGING_OUT: 'turkticaret_logging_out',
} as const;


export const COOKIE_KEYS = {
  AUTH_STATUS: 'turkticaret_auth',
} as const;


export const API_ENDPOINTS = {
  AUTH: {
    LOGIN: 'login',
    REGISTER: 'register',
    LOGOUT: 'logout',
    PROFILE: 'profile',
    CSRF_COOKIE: '/sanctum/csrf-cookie',
  },
  SETTINGS: 'settings',
  ADMIN: {
    SETTINGS: 'admin/settings',
    SETTINGS_STATUS: 'admin/settings/status',
  },
} as const;


export const DEFAULT_VALUES = {
  CURRENCY: 'TRY',
  LANGUAGE: 'tr',
  COUNTRY: 'TR',
  TIMEZONE: 'Europe/Istanbul',
  ITEMS_PER_PAGE: 20,
  THEME: 'default',
} as const;


export const TIME = {
  COOKIE_MAX_AGE: 86400, 
  SEARCH_DEBOUNCE: 300,
  REQUEST_TIMEOUT: 10000,
} as const;


export const ROUTES = {
  AUTH: {
    LOGIN: '/auth/login',
    REGISTER: '/auth/register',
  },
  DASHBOARD: {
    HOME: '/dashboard',
    PRODUCTS: '/dashboard/products',
    CATEGORIES: '/dashboard/categories',
    SETTINGS: '/dashboard/settings',
  },
  PUBLIC: {
    HOME: '/',
    PRODUCTS: '/',
    CART: '/cart',
    CHECKOUT: '/checkout',
  },
} as const;

