

export interface AppError {
  message: string;
  code?: string;
  status?: number;
}


export const handleApiError = (error: unknown): string => {
  
  if (error && typeof error === 'object' && 'message' in error && typeof error.message === 'string') {
    // Make network errors more user-friendly
    if (error.message.includes('Unable to connect to API server')) {
      return 'Service temporarily unavailable. Please try again later.';
    }
    if (error.message.includes('Network error')) {
      return 'Connection failed. Please check your internet connection.';
    }
    return error.message;
  }
  
  
  if (error && typeof error === 'object' && 'errors' in error && Array.isArray(error.errors) && error.errors.length > 0) {
    return error.errors[0];
  }
  
  
  if (typeof error === 'string') {
    if (error.includes('Unable to connect to API server')) {
      return 'Service temporarily unavailable. Please try again later.';
    }
    if (error.includes('Network error')) {
      return 'Connection failed. Please check your internet connection.';
    }
    return error;
  }
  
  
  if (error && typeof error === 'object' && 'code' in error && error.code === 'NETWORK_ERROR') {
    return 'Network connection failed. Please check your internet.';
  }
  
  
  return 'Something went wrong. Please try again.';
};


export const handleAuthError = (error: unknown): string => {
  if (error && typeof error === 'object' && 'status' in error) {
    if (error.status === 401) return 'Invalid email or password';
    if (error.status === 422) return handleApiError(error);
  }
  return handleApiError(error);
};


const extractErrorInfo = (error: unknown): { message: string; details?: Record<string, unknown> } => {
  if (!error) {
    return { message: 'Unknown error occurred' };
  }

  
  if (typeof error === 'string') {
    return { message: error };
  }

  
  if (error instanceof Error) {
    return {
      message: error.message,
      details: {
        name: error.name,
        stack: error.stack?.split('\n').slice(0, 3).join('\n'), 
      }
    };
  }

  
  if (error && typeof error === 'object') {
    try {
      
      const serialized = JSON.parse(JSON.stringify(error, Object.getOwnPropertyNames(error)));
      
      const message = serialized.message || 
                     serialized.error || 
                     serialized.statusText ||
                     'Request failed';

      const details: Record<string, unknown> = {};
      if (serialized.status) details.status = serialized.status;
      if (serialized.url) details.url = serialized.url;
      if (serialized.method) details.method = serialized.method;
      if (serialized.data || serialized.response) {
        details.response = serialized.data || serialized.response;
      }

      return { message, details: Object.keys(details).length > 0 ? details : undefined };
    } catch {
      return { message: 'Request failed', details: { type: typeof error } };
    }
  }

  return { message: String(error) };
};


export const logError = (error: unknown, context?: string): void => {
  if (process.env.NODE_ENV === 'development') {
    const { message, details } = extractErrorInfo(error);
    
    // Skip verbose logging for network connection errors
    if (message.includes('Unable to connect to API server') || message.includes('Network error')) {
      console.warn(`[${context || 'API'}] Connection failed - API server may be offline`);
      return;
    }
    
    console.error(`[${context || 'APP'}] ${message}`);
    if (details && Object.keys(details).length > 0) {
      console.error('Details:', details);
    }
  }
  
  
  
  
  
};