import { useState, useCallback } from 'react';

export interface AsyncState<T> {
  data: T | null;
  loading: boolean;
  error: string | null;
}

export interface UseAsyncStateReturn<T> extends AsyncState<T> {
  setData: (data: T | null) => void;
  setLoading: (loading: boolean) => void;
  setError: (error: string | null) => void;
  execute: <TResult = T>(asyncFunction: () => Promise<TResult>) => Promise<TResult | null>;
  reset: () => void;
}

export function useAsyncState<T = unknown>(initialData: T | null = null): UseAsyncStateReturn<T> {
  const [data, setData] = useState<T | null>(initialData);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState<string | null>(null);

  const execute = useCallback(async <TResult = T>(asyncFunction: () => Promise<TResult>): Promise<TResult | null> => {
    try {
      setLoading(true);
      setError(null);
      const result = await asyncFunction();
      setData(result as unknown as T);
      return result;
    } catch (err) {
      const errorMessage = err instanceof Error ? err.message : 'An error occurred';
      setError(errorMessage);
      return null;
    } finally {
      setLoading(false);
    }
  }, []);

  const reset = useCallback(() => {
    setData(initialData);
    setLoading(false);
    setError(null);
  }, [initialData]);

  return {
    data,
    loading,
    error,
    setData,
    setLoading,
    setError,
    execute,
    reset
  };
}