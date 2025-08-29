import { apiClient } from '@/lib/api';
import { User } from '@/types/user';
import { BaseService } from './BaseService';
import { API_ENDPOINTS } from '@/lib/constants';
import { logError } from '@/lib/errorHandler';

export interface LoginRequest {
  email: string;
  password: string;
}

export interface LoginResponse {
  success: boolean;
  message: string;
  errors: string[];
  data: {
    user: User;
    token: string;
    token_type: string;
  };
}

export interface RegisterRequest {
  name: string;
  email: string;
  password: string;
  password_confirmation: string;
  country_code: string;
  terms_accepted: boolean;
}

class AuthService extends BaseService<User, User, never> {
  protected endpoint = 'users';

  protected mapFromApi(apiUser: User): User {
    return apiUser;
  }

  protected mapToApi(user: Partial<User>): Partial<User> {
    return user;
  }

  async getCsrfCookie(): Promise<boolean> {
    try {
      const sanctumUrl = process.env.NEXT_PUBLIC_SANCTUM_URL;
      if (!sanctumUrl) {
        throw new Error('NEXT_PUBLIC_SANCTUM_URL environment variable is not set');
      }
      
      
      const response = await fetch(`${sanctumUrl}${API_ENDPOINTS.AUTH.CSRF_COOKIE}`, {
        method: 'GET',
        credentials: 'include',
        headers: {
          'Accept': 'application/json',
        }
      });
      return response.ok;
    } catch (error) {
      logError(error, 'CSRF_COOKIE');
      return false;
    }
  }

  async login(credentials: LoginRequest): Promise<LoginResponse> {
    await this.getCsrfCookie();
    return apiClient.post<LoginResponse>(API_ENDPOINTS.AUTH.LOGIN, credentials);
  }

  async register(userData: RegisterRequest): Promise<LoginResponse> {
    await this.getCsrfCookie();
    return apiClient.post<LoginResponse>(API_ENDPOINTS.AUTH.REGISTER, userData);
  }

  async getProfile(): Promise<User> {
    const response = await apiClient.get<{ success: boolean; data: User }>(API_ENDPOINTS.AUTH.PROFILE);
    return response.data;
  }

  async updateProfile(userData: Partial<User>): Promise<User> {
    const response = await apiClient.put<{ success: boolean; data: User }>(API_ENDPOINTS.AUTH.PROFILE, userData);
    return response.data;
  }

  async logout(): Promise<void> {
    try {
      await this.getCsrfCookie();
      await apiClient.post(API_ENDPOINTS.AUTH.LOGOUT);
    } catch (error) {
      logError(error, 'LOGOUT');
    }
  }
}

export const authService = new AuthService();