import { useState, useCallback } from 'react';
import { useToast } from '@/hooks/use-toast';

export interface UseAsyncOperationOptions {
  successMessage?: string;
  errorMessage?: string;
  showToast?: boolean;
}

export interface UseAsyncOperationReturn<T> {
  loading: boolean;
  error: string | null;
  data: T | null;
  execute: (operation: () => Promise<T>, options?: UseAsyncOperationOptions) => Promise<T | null>;
  reset: () => void;
}

export function useAsyncOperation<T = unknown>(): UseAsyncOperationReturn<T> {
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState<string | null>(null);
  const [data, setData] = useState<T | null>(null);
  const { toast } = useToast();

  const execute = useCallback(async (
    operation: () => Promise<T>,
    options: UseAsyncOperationOptions = {}
  ): Promise<T | null> => {
    const {
      successMessage,
      errorMessage = 'An error occurred',
      showToast = true
    } = options;

    try {
      setLoading(true);
      setError(null);

      const result = await operation();
      setData(result);

      if (showToast && successMessage) {
        toast({
          title: 'Success!',
          description: successMessage,
        });
      }

      return result;
    } catch (err) {
      const errorMsg = err instanceof Error ? err.message : errorMessage;
      setError(errorMsg);

      if (showToast) {
        toast({
          title: 'Error!',
          description: errorMsg,
          variant: 'destructive',
        });
      }

      return null;
    } finally {
      setLoading(false);
    }
  }, [toast]);

  const reset = useCallback(() => {
    setLoading(false);
    setError(null);
    setData(null);
  }, []);

  return {
    loading,
    error,
    data,
    execute,
    reset
  };
}