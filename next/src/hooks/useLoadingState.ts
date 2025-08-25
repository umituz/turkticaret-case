import { useState, useCallback } from 'react';

export interface UseLoadingStateReturn {
  loading: boolean;
  setLoading: (loading: boolean) => void;
  withLoading: <T>(asyncFn: () => Promise<T>) => Promise<T>;
}

export function useLoadingState(initialState = false): UseLoadingStateReturn {
  const [loading, setLoading] = useState<boolean>(initialState);

  const withLoading = useCallback(async <T>(asyncFn: () => Promise<T>): Promise<T> => {
    try {
      setLoading(true);
      return await asyncFn();
    } finally {
      setLoading(false);
    }
  }, []);

  return {
    loading,
    setLoading,
    withLoading,
  };
}