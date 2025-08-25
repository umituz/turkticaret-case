
export const validationRules = {
  required: (fieldName: string) => (value: unknown): string | null => {
    if (!value || (typeof value === 'string' && value.trim() === '')) {
      return `${fieldName} is required`;
    }
    return null;
  },

  email: (value: unknown): string | null => {
    if (!value) return null;
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (typeof value === 'string' && !emailRegex.test(value)) {
      return 'Please enter a valid email address';
    }
    return null;
  },

  minLength: (min: number) => (value: unknown): string | null => {
    if (!value) return null;
    if (typeof value === 'string' && value.length < min) {
      return `Must be at least ${min} characters`;
    }
    return null;
  },

  maxLength: (max: number) => (value: unknown): string | null => {
    if (!value) return null;
    if (typeof value === 'string' && value.length > max) {
      return `Must be no more than ${max} characters`;
    }
    return null;
  },

  password: (value: unknown): string | null => {
    if (!value) return null;
    if (typeof value === 'string') {
      if (value.length < 8) {
        return 'Password must be at least 8 characters';
      }
      if (!/(?=.*[a-z])(?=.*[A-Z])(?=.*\d)/.test(value)) {
        return 'Password must contain uppercase, lowercase, and number';
      }
    }
    return null;
  },

  passwordConfirmation: (originalPassword: string) => (value: unknown): string | null => {
    if (!value) return null;
    if (value !== originalPassword) {
      return 'Passwords do not match';
    }
    return null;
  },

  numeric: (value: unknown): string | null => {
    if (!value) return null;
    if (isNaN(Number(value))) {
      return 'Must be a valid number';
    }
    return null;
  },

  positive: (value: unknown): string | null => {
    if (!value) return null;
    if (Number(value) <= 0) {
      return 'Must be a positive number';
    }
    return null;
  },

  url: (value: unknown): string | null => {
    if (!value) return null;
    try {
      new URL(value as string);
      return null;
    } catch {
      return 'Must be a valid URL';
    }
  }
};


export const combineValidators = (...validators: Array<(value: unknown) => string | null>) => {
  return (value: unknown): string | null => {
    for (const validator of validators) {
      const error = validator(value);
      if (error) return error;
    }
    return null;
  };
};