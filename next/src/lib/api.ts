import { SecureStorage } from '@/lib/security';
import { STORAGE_KEYS, ROUTES, COOKIE_KEYS } from '@/lib/constants';

const API_URL = process.env.NEXT_PUBLIC_API_URL;

if (!API_URL) {
  throw new Error('NEXT_PUBLIC_API_URL environment variable is not set');
}

interface ApiRequestOptions extends RequestInit {
  params?: Record<string, string | number | boolean | undefined | null>;
}

class ApiClient {
  private baseUrl: string;

  constructor(baseUrl: string) {
    
    this.baseUrl = baseUrl.endsWith('/') ? baseUrl : `${baseUrl}/`;
  }

  private buildUrl(endpoint: string, params?: Record<string, string | number | boolean | undefined | null>): string {
    const url = new URL(endpoint, this.baseUrl);
    
    if (params) {
      Object.entries(params).forEach(([key, value]) => {
        if (value !== undefined && value !== null) {
          url.searchParams.append(key, value.toString());
        }
      });
    }
    
    return url.toString();
  }

  private async request<T>(endpoint: string, options: ApiRequestOptions = {}): Promise<T> {
    const { params, ...fetchOptions } = options;
    const url = this.buildUrl(endpoint, params);
    const token = typeof window !== 'undefined' ? SecureStorage.getItem(STORAGE_KEYS.AUTH_TOKEN) : null;
    
    const defaultOptions: RequestInit = {
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        ...(token && { 'Authorization': `Bearer ${token}` }),
        ...fetchOptions.headers,
      },
      credentials: 'include',
    };

    try {
      const response = await fetch(url, { ...defaultOptions, ...fetchOptions });
      
      if (!response.ok) {
        
        let errorResponse: unknown = null;
        try {
          const contentType = response.headers.get('content-type');
          if (contentType && contentType.includes('application/json')) {
            const text = await response.text();
            if (text) {
              errorResponse = JSON.parse(text);
            }
          }
        } catch {
          
        }

        
        if (response.status === 401 && typeof window !== 'undefined') {
          
          const isLogoutRequest = url.includes('/logout');
          if (isLogoutRequest) {
            
            return {} as T;
          }
          
          const token = SecureStorage.getItem(STORAGE_KEYS.AUTH_TOKEN);
          if (token) {
            
            SecureStorage.removeItem(STORAGE_KEYS.AUTH_STATUS);
            SecureStorage.removeItem(STORAGE_KEYS.AUTH_TOKEN);
            SecureStorage.removeItem(STORAGE_KEYS.USER_DATA);
            
            document.cookie = `${COOKIE_KEYS.AUTH_STATUS}=; path=/; expires=Thu, 01 Jan 1970 00:00:00 GMT`;
            
            
            const isLoggingOut = window.sessionStorage.getItem('turkticaret_logging_out');
            if (!isLoggingOut) {
              
              window.location.replace(ROUTES.AUTH.LOGIN);
            }
            throw new Error('Authentication expired');
          }
          
          throw new Error('Authentication required');
        }
        
        
        
        
        if (errorResponse) {
          throw errorResponse;
        }
        
        throw {
          message: `Request failed with status ${response.status}`,
          status: response.status,
          code: 'API_ERROR'
        };
      }
      
      
      const contentType = response.headers.get('content-type');
      if (response.status === 204 || !contentType || !contentType.includes('application/json')) {
        return {} as T;
      }
      
      const text = await response.text();
      if (!text) {
        return {} as T;
      }
      
      try {
        return JSON.parse(text);
      } catch {
        
        return text as T;
      }
    } catch (error) {
      if (error instanceof TypeError && error.message.includes('fetch')) {
        throw {
          message: 'Network error: Unable to connect to API server',
          code: 'NETWORK_ERROR',
          url: url,
          method: fetchOptions.method || 'GET'
        };
      }
      throw error;
    }
  }

  async get<T>(endpoint: string, params?: Record<string, string | number | boolean | undefined | null>): Promise<T> {
    return this.request<T>(endpoint, { method: 'GET', params });
  }

  async post<T>(endpoint: string, data?: unknown): Promise<T> {
    return this.request<T>(endpoint, {
      method: 'POST',
      body: data ? JSON.stringify(data) : undefined,
    });
  }

  async put<T>(endpoint: string, data?: unknown): Promise<T> {
    return this.request<T>(endpoint, {
      method: 'PUT',
      body: data ? JSON.stringify(data) : undefined,
    });
  }

  async patch<T>(endpoint: string, data?: unknown): Promise<T> {
    return this.request<T>(endpoint, {
      method: 'PATCH',
      body: data ? JSON.stringify(data) : undefined,
    });
  }

  async delete<T>(endpoint: string): Promise<T> {
    return this.request<T>(endpoint, { method: 'DELETE' });
  }
}

export const apiClient = new ApiClient(API_URL);