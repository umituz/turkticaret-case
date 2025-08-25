import { MoneyInfo } from './money';

export interface User {
  uuid: string;
  name: string;
  email: string;
  role?: 'admin' | 'user';
  email_verified_at?: string;
  created_at: string;
  updated_at: string;
}

export interface UserProfile extends User {
  id: number;
  firstName?: string;
  lastName?: string;
  phone?: string;
  avatar?: string;
  bio?: string;
  dateOfBirth?: string;
  gender?: 'male' | 'female' | 'other' | 'prefer-not-to-say';
  emailVerified: boolean;
  phoneVerified: boolean;
  twoFactorEnabled: boolean;
  language: string;
  currency: string;
  timezone: string;
  marketingEmails: boolean;
  orderUpdates: boolean;
  securityAlerts: boolean;
  lastLoginAt?: string;
  addresses: Address[];
  orders: Order[];
}

export interface Address {
  uuid: string;
  userUuid: string;
  type: 'billing' | 'shipping';
  isDefault: boolean;
  firstName: string;
  lastName: string;
  company?: string;
  address1: string;
  address2?: string;
  city: string;
  state: string;
  postalCode: string;
  country: string;
  phone?: string;
  createdAt: string;
  updatedAt: string;
}

export interface Order {
  uuid: string;
  userUuid: string;
  orderNumber: string;
  status: 'pending' | 'processing' | 'shipped' | 'delivered' | 'cancelled' | 'refunded';
  total: MoneyInfo;
  subtotal: MoneyInfo;
  tax: MoneyInfo;
  shipping: MoneyInfo;
  discount: MoneyInfo;
  currency: string;
  paymentStatus: 'pending' | 'paid' | 'failed' | 'refunded';
  paymentMethod: string;
  shippingAddress: Address;
  billingAddress: Address;
  items: OrderItem[];
  createdAt: string;
  updatedAt: string;
  shippedAt?: string;
  deliveredAt?: string;
}

export interface OrderItem {
  uuid: string;
  orderUuid: string;
  productUuid: string;
  productName: string;
  productSku: string;
  productImage?: string;
  quantity: number;
  price: MoneyInfo;
  total: MoneyInfo;
}

export interface UserProfileFormData {
  name: string;
  firstName?: string;
  lastName?: string;
  email: string;
  phone?: string;
  bio?: string;
  dateOfBirth?: string;
  gender?: 'male' | 'female' | 'other' | 'prefer-not-to-say';
  avatar?: string;
}


export interface AddressFormData {
  type: 'billing' | 'shipping';
  isDefault: boolean;
  firstName: string;
  lastName: string;
  company?: string;
  address1: string;
  address2?: string;
  city: string;
  state: string;
  postalCode: string;
  country: string;
  phone?: string;
}

export interface PasswordChangeFormData {
  currentPassword: string;
  newPassword: string;
  confirmPassword: string;
}


export interface UserSettings {
  notifications: {
    email: boolean;
    push: boolean;
    sms: boolean;
    marketing: boolean;
    orderUpdates: boolean;
  };
  preferences: {
    language: string;
    timezone: string;
  };
}

