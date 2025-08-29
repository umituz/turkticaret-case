import { ROUTES } from '@/lib/constants';
import { getSession } from 'next-auth/react';

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
    
    const headers: Record<string, string> = {
      'Content-Type': 'application/json',
      'Accept': 'application/json',
      ...fetchOptions.headers as Record<string, string>,
    };
    
    // Get session token for authentication
    let session = null;
    
    if (typeof window !== 'undefined') {
      session = await getSession();
    }
    
    // Add Authorization header if token is available
    if (session?.user?.accessToken) {
      headers['Authorization'] = `Bearer ${session.user.accessToken}`;
    }
    
    const defaultOptions: RequestInit = {
      headers,
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
          
          window.location.replace(ROUTES.AUTH.LOGIN);
          throw new Error('Authentication expired');
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