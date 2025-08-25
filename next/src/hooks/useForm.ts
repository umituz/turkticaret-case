import { useState, useCallback } from 'react';
import { useToast } from '@/hooks/use-toast';

export interface UseFormOptions<T> {
  initialValues: T;
  validationRules?: Partial<Record<keyof T, (value: unknown) => string | null>>;
  onSubmit: (values: T) => Promise<unknown>;
  successMessage?: string;
  resetOnSuccess?: boolean;
}

export interface UseFormReturn<T> {
  values: T;
  errors: Partial<Record<keyof T, string>>;
  loading: boolean;
  handleChange: (name: keyof T, value: unknown) => void;
  handleSubmit: (e: React.FormEvent) => Promise<void>;
  reset: () => void;
  setFieldError: (name: keyof T, error: string) => void;
  clearErrors: () => void;
}

export function useForm<T extends Record<string, unknown>>({
  initialValues,
  validationRules = {},
  onSubmit,
  successMessage = 'Operation completed successfully!',
  resetOnSuccess = false
}: UseFormOptions<T>): UseFormReturn<T> {
  const [values, setValues] = useState<T>(initialValues);
  const [errors, setErrors] = useState<Partial<Record<keyof T, string>>>({});
  const [loading, setLoading] = useState(false);
  const { toast } = useToast();

  const handleChange = useCallback((name: keyof T, value: unknown) => {
    setValues(prev => ({ ...prev, [name]: value }));
    
    
    if (errors[name]) {
      setErrors(prev => ({ ...prev, [name]: undefined }));
    }
  }, [errors]);

  const validateField = useCallback((name: keyof T, value: unknown): string | null => {
    const rule = validationRules[name];
    return rule ? rule(value) : null;
  }, [validationRules]);

  const validateForm = useCallback((): boolean => {
    const newErrors: Partial<Record<keyof T, string>> = {};
    let hasErrors = false;

    Object.keys(values).forEach((key) => {
      const fieldKey = key as keyof T;
      const error = validateField(fieldKey, values[fieldKey]);
      if (error) {
        newErrors[fieldKey] = error;
        hasErrors = true;
      }
    });

    setErrors(newErrors);
    return !hasErrors;
  }, [values, validateField]);

  const handleSubmit = useCallback(async (e: React.FormEvent) => {
    e.preventDefault();

    if (!validateForm()) {
      toast({
        title: 'Validation Error!',
        description: 'Please fix the errors in the form.',
        variant: 'destructive',
      });
      return;
    }

    try {
      setLoading(true);
      await onSubmit(values);

      toast({
        title: 'Success!',
        description: successMessage,
      });

      if (resetOnSuccess) {
        setValues(initialValues);
      }
    } catch (error) {
      const errorMessage = error instanceof Error ? error.message : 'An error occurred';
      toast({
        title: 'Error!',
        description: errorMessage,
        variant: 'destructive',
      });
    } finally {
      setLoading(false);
    }
  }, [values, validateForm, onSubmit, successMessage, resetOnSuccess, initialValues, toast]);

  const reset = useCallback(() => {
    setValues(initialValues);
    setErrors({});
    setLoading(false);
  }, [initialValues]);

  const setFieldError = useCallback((name: keyof T, error: string) => {
    setErrors(prev => ({ ...prev, [name]: error }));
  }, []);

  const clearErrors = useCallback(() => {
    setErrors({});
  }, []);

  return {
    values,
    errors,
    loading,
    handleChange,
    handleSubmit,
    reset,
    setFieldError,
    clearErrors
  };
}