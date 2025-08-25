import { useState, useCallback } from 'react';

export interface UseFormDataReturn<T> {
  formData: T;
  setFormData: React.Dispatch<React.SetStateAction<T>>;
  updateField: (field: keyof T, value: T[keyof T]) => void;
  resetForm: () => void;
  handleInputChange: (e: React.ChangeEvent<HTMLInputElement | HTMLTextAreaElement | HTMLSelectElement>) => void;
}

export function useFormData<T extends Record<string, unknown>>(
  initialData: T
): UseFormDataReturn<T> {
  const [formData, setFormData] = useState<T>(initialData);

  const updateField = useCallback((field: keyof T, value: T[keyof T]) => {
    setFormData(prev => ({ ...prev, [field]: value }));
  }, []);

  const resetForm = useCallback(() => {
    setFormData(initialData);
  }, [initialData]);

  const handleInputChange = useCallback((
    e: React.ChangeEvent<HTMLInputElement | HTMLTextAreaElement | HTMLSelectElement>
  ) => {
    const { name, value, type } = e.target;
    const finalValue = type === 'checkbox' ? (e.target as HTMLInputElement).checked : value;
    
    updateField(name as keyof T, finalValue as T[keyof T]);
  }, [updateField]);

  return {
    formData,
    setFormData,
    updateField,
    resetForm,
    handleInputChange,
  };
}