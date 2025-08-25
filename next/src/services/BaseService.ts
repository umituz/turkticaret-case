import { apiClient } from '@/lib/api';
import { ApiResponse } from '@/types/api';
import { handleApiError, logError } from '@/lib/errorHandler';


export abstract class BaseService<TEntity = unknown, TApiEntity = unknown, TFilters = Record<string, unknown>> {
  protected abstract endpoint: string;
  
  
  protected mapFromApi?(apiEntity: TApiEntity): TEntity;
  protected mapToApi?(entity: Partial<TEntity>): Partial<TApiEntity>;

  
  protected handleError(operation: string, error: unknown): never {
    const context = `${this.constructor.name}_${operation}`;
    logError(error, context);
    
    const errorMessage = handleApiError(error);
    throw new Error(errorMessage);
  }

  
  protected async apiGet<T>(path: string, params?: Record<string, unknown>): Promise<T> {
    try {
      return await apiClient.get<T>(path, params as Record<string, string | number | boolean | undefined | null>);
    } catch (error) {
      this.handleError(`GET ${path}`, error);
    }
  }

  protected async apiPost<T>(path: string, data?: unknown): Promise<T> {
    try {
      return await apiClient.post<T>(path, data);
    } catch (error) {
      this.handleError(`POST ${path}`, error);
    }
  }

  protected async apiPut<T>(path: string, data?: unknown): Promise<T> {
    try {
      return await apiClient.put<T>(path, data);
    } catch (error) {
      this.handleError(`PUT ${path}`, error);
    }
  }

  protected async apiDelete<T>(path: string): Promise<T> {
    try {
      return await apiClient.delete<T>(path);
    } catch (error) {
      this.handleError(`DELETE ${path}`, error);
    }
  }

  
  async getAll(filters?: TFilters): Promise<{ items: TEntity[]; total: number }> {
    try {
      const response = await apiClient.get<ApiResponse<TApiEntity[]>>(
        this.endpoint, 
        filters as Record<string, string | number | boolean | undefined | null>
      );
      
      const items = this.mapFromApi 
        ? (response.data || []).map(this.mapFromApi.bind(this))
        : (response.data || []) as unknown as TEntity[];
        
      return {
        items,
        total: response.meta?.total || 0
      };
    } catch (error) {
      this.handleError('getAll', error);
    }
  }

  async getByUuid(uuid: string): Promise<TEntity | null> {
    try {
      const response = await apiClient.get<ApiResponse<TApiEntity>>(`${this.endpoint}/${uuid}`);
      if (!response.data) return null;
      
      return this.mapFromApi ? this.mapFromApi(response.data) : response.data as unknown as TEntity;
    } catch {
      return null; 
    }
  }

  async create(data: Partial<TEntity>): Promise<TEntity> {
    try {
      const payload = this.mapToApi ? this.mapToApi(data) : data;
      const response = await apiClient.post<ApiResponse<TApiEntity>>(this.endpoint, payload);
      
      return this.mapFromApi ? this.mapFromApi(response.data) : response.data as unknown as TEntity;
    } catch (error) {
      this.handleError('create', error);
    }
  }

  async update(id: string, data: Partial<TEntity>): Promise<TEntity> {
    try {
      const payload = this.mapToApi ? this.mapToApi(data) : data;
      const response = await apiClient.put<ApiResponse<TApiEntity>>(`${this.endpoint}/${id}`, payload);
      
      return this.mapFromApi ? this.mapFromApi(response.data) : response.data as unknown as TEntity;
    } catch (error) {
      this.handleError('update', error);
    }
  }

  async delete(id: string): Promise<void> {
    try {
      await apiClient.delete<ApiResponse<void>>(`${this.endpoint}/${id}`);
    } catch (error) {
      this.handleError('delete', error);
    }
  }

  async getBySlug(slug: string): Promise<TEntity | null> {
    try {
      const response = await apiClient.get<ApiResponse<TApiEntity>>(`${this.endpoint}/${slug}`);
      if (!response.data) return null;
      
      return this.mapFromApi ? this.mapFromApi(response.data) : response.data as unknown as TEntity;
    } catch {
      return null; 
    }
  }
}