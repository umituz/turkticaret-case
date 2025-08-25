

export const createFormChangeHandler = <T extends Record<string, unknown>>(
  setFormData: React.Dispatch<React.SetStateAction<T>>
) => {
  return (field: keyof T, value: unknown) => {
    setFormData(prev => ({
      ...prev,
      [field]: value
    }));
  };
};

export const createInputChangeHandler = <T extends Record<string, unknown>>(
  setFormData: React.Dispatch<React.SetStateAction<T>>
) => {
  return (e: React.ChangeEvent<HTMLInputElement>) => {
    const { name, value, type, checked } = e.target;
    setFormData(prev => ({
      ...prev,
      [name]: type === 'checkbox' ? checked : value
    }));
  };
};

export const resetFormData = <T>(
  setFormData: React.Dispatch<React.SetStateAction<T>>,
  initialValues: T
) => {
  setFormData(initialValues);
};


export const validateRequired = (fields: Record<string, unknown>): string[] => {
  const errors: string[] = [];
  
  Object.entries(fields).forEach(([key, value]) => {
    if (!value || (typeof value === 'string' && value.trim() === '')) {
      errors.push(`${key} is required`);
    }
  });
  
  return errors;
};


export const handleAsyncSubmission = async <T>(
  operation: () => Promise<T>,
  options: {
    setLoading: (loading: boolean) => void;
    onSuccess?: (result: T) => void;
    onError?: (error: Error) => void;
    successMessage?: string;
    errorMessage?: string;
  }
): Promise<T | null> => {
  const {
    setLoading,
    onSuccess,
    onError,
    errorMessage = 'Operation failed'
  } = options;

  try {
    setLoading(true);
    const result = await operation();
    
    if (onSuccess) {
      onSuccess(result);
    }
    
    
    
    
    return result;
  } catch (error) {
    const err = error instanceof Error ? error : new Error(errorMessage);
    
    if (onError) {
      onError(err);
    }
    
    
    
    
    return null;
  } finally {
    setLoading(false);
  }
};