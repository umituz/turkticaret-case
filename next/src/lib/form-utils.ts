export const FORM_CONFIGS = {
  email: {
    type: 'email',
    placeholder: 'örnek@email.com',
    autoComplete: 'email',
  },
  password: {
    type: 'password',
    placeholder: '••••••••',
    autoComplete: 'current-password',
  },
  newPassword: {
    type: 'password',
    placeholder: '••••••••',
    autoComplete: 'new-password',
  },
  name: {
    type: 'text',
    placeholder: 'Ad Soyad',
    autoComplete: 'name',
  },
  phone: {
    type: 'tel',
    placeholder: '05XX XXX XX XX',
    autoComplete: 'tel',
  },
  address: {
    type: 'text',
    placeholder: 'Adres bilginizi giriniz',
    autoComplete: 'street-address',
  },
} as const;


export const FORM_ERROR_MESSAGES = {
  required: (field: string) => `${field} alanı zorunludur.`,
  email: 'Geçerli bir email adresi giriniz.',
  minLength: (field: string, length: number) => `${field} en az ${length} karakter olmalıdır.`,
  maxLength: (field: string, length: number) => `${field} en fazla ${length} karakter olmalıdır.`,
  passwordMatch: 'Şifreler eşleşmiyor.',
  phone: 'Geçerli bir telefon numarası giriniz.',
  numeric: 'Sadece sayı girebilirsiniz.',
  positive: 'Pozitif bir sayı giriniz.',
  url: 'Geçerli bir URL giriniz.',
} as const;


export const VALIDATION_PATTERNS = {
  email: /^[^\s@]+@[^\s@]+\.[^\s@]+$/,
  phone: /^(\+90|0)?[5][0-9][0-9][0-9][0-9][0-9][0-9][0-9][0-9][0-9]$/,
  numeric: /^\d+$/,
  alphanumeric: /^[a-zA-Z0-9]+$/,
  url: /^https?:\/\/.+/,
} as const;


export const validators = {
  required: (value: string | undefined | null) => {
    return value && value.trim().length > 0;
  },
  
  email: (value: string) => {
    return VALIDATION_PATTERNS.email.test(value);
  },
  
  minLength: (value: string, min: number) => {
    return value.length >= min;
  },
  
  maxLength: (value: string, max: number) => {
    return value.length <= max;
  },
  
  phone: (value: string) => {
    return VALIDATION_PATTERNS.phone.test(value.replace(/\s/g, ''));
  },
  
  numeric: (value: string) => {
    return VALIDATION_PATTERNS.numeric.test(value);
  },
  
  positive: (value: string | number) => {
    const num = typeof value === 'string' ? parseFloat(value) : value;
    return !isNaN(num) && num > 0;
  },
  
  url: (value: string) => {
    return VALIDATION_PATTERNS.url.test(value);
  },
  
  passwordsMatch: (password: string, confirmPassword: string) => {
    return password === confirmPassword;
  },
} as const;


export function validateField(
  value: string,
  rules: Array<{
    validator: (value: unknown, ...args: unknown[]) => boolean;
    message: string;
    args?: unknown[];
  }>
): string | null {
  for (const rule of rules) {
    const isValid = rule.args 
      ? rule.validator(value, ...rule.args)
      : rule.validator(value);
    
    if (!isValid) {
      return rule.message;
    }
  }
  
  return null;
}


export function formatPhoneNumber(value: string): string {
  const cleaned = value.replace(/\D/g, '');
  if (cleaned.length === 11 && cleaned.startsWith('0')) {
    return cleaned.replace(/(\d{4})(\d{3})(\d{2})(\d{2})/, '$1 $2 $3 $4');
  }
  return value;
}


export function cleanPhoneNumber(value: string): string {
  return value.replace(/\D/g, '');
}


export function createFormState() {
  return {
    loading: false,
    success: false,
    error: null as string | null,
  };
}


export async function handleFormSubmission<T>(
  submitFn: () => Promise<T>,
  setState: (state: { loading: boolean; success: boolean; error: string | null }) => void,
  successCallback?: (result: T) => void
): Promise<void> {
  setState({ loading: true, success: false, error: null });
  
  try {
    const result = await submitFn();
    setState({ loading: false, success: true, error: null });
    
    if (successCallback) {
      successCallback(result);
    }
  } catch (error) {
    setState({ 
      loading: false, 
      success: false, 
      error: error instanceof Error ? error.message : 'Bir hata oluştu' 
    });
  }
}