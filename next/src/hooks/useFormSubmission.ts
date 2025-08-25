import { useState, useCallback } from 'react';

export interface UseFormSubmissionReturn<TData> {
  loading: boolean;
  error: string | null;
  success: boolean;
  submitForm: (submitFunction: () => Promise<TData>) => Promise<TData | null>;
  reset: () => void;
}

export function useFormSubmission<TData = unknown>(): UseFormSubmissionReturn<TData> {
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState<string | null>(null);
  const [success, setSuccess] = useState(false);

  const submitForm = useCallback(async (submitFunction: () => Promise<TData>): Promise<TData | null> => {
    try {
      setLoading(true);
      setError(null);
      setSuccess(false);
      
      const result = await submitFunction();
      setSuccess(true);
      return result;
    } catch (err) {
      const errorMessage = err instanceof Error ? err.message : 'Submission failed';
      setError(errorMessage);
      setSuccess(false);
      return null;
    } finally {
      setLoading(false);
    }
  }, []);

  const reset = useCallback(() => {
    setLoading(false);
    setError(null);
    setSuccess(false);
  }, []);

  return {
    loading,
    error,
    success,
    submitForm,
    reset
  };
}