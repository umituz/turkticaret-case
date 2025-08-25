export interface ApiResponse<T = unknown> {
  success: boolean;
  message?: string;
  data?: T;
  errors?: string[];
}

export const API_ERROR_MESSAGES = {
  400: 'Geçersiz istek. Lütfen girdiğiniz bilgileri kontrol edin.',
  401: 'Oturum süreniz dolmuş. Lütfen tekrar giriş yapın.',
  403: 'Bu işlem için yetkiniz bulunmuyor.',
  404: 'İstenilen kaynak bulunamadı.',
  422: 'Girdiğiniz bilgiler geçerli değil.',
  429: 'Çok fazla istek gönderildi. Lütfen biraz bekleyin.',
  500: 'Sunucu hatası oluştu. Lütfen daha sonra tekrar deneyin.',
  503: 'Servis şu anda kullanılamıyor. Lütfen daha sonra tekrar deneyin.',
} as const;


export function getApiErrorMessage(error: unknown): string {
  if (error instanceof Error) {
    return error.message;
  }
  
  if (typeof error === 'string') {
    return error;
  }
  
  if (error && typeof error === 'object') {
    const apiError = error as { status?: number; message?: string; errors?: string[] };
    
    if (apiError.message) {
      return apiError.message;
    }
    
    if (apiError.errors && apiError.errors.length > 0) {
      return apiError.errors.join(', ');
    }
    
    if (apiError.status && apiError.status in API_ERROR_MESSAGES) {
      return API_ERROR_MESSAGES[apiError.status as keyof typeof API_ERROR_MESSAGES];
    }
  }
  
  return 'Beklenmeyen bir hata oluştu.';
}


export function handleApiResponse<T>(response: ApiResponse<T>): T {
  if (!response.success) {
    throw new Error(response.message || 'API isteği başarısız oldu');
  }
  
  return response.data as T;
}


export function createLoadingState(isLoading: boolean, error?: string | null) {
  return {
    loading: isLoading,
    error: error || null,
    success: !isLoading && !error
  };
}


export const TOAST_MESSAGES = {
  success: {
    create: 'Başarıyla oluşturuldu!',
    update: 'Başarıyla güncellendi!',
    delete: 'Başarıyla silindi!',
    save: 'Başarıyla kaydedildi!',
  },
  error: {
    create: 'Oluşturma işlemi başarısız oldu.',
    update: 'Güncelleme işlemi başarısız oldu.',
    delete: 'Silme işlemi başarısız oldu.',
    save: 'Kaydetme işlemi başarısız oldu.',
    fetch: 'Veriler yüklenirken hata oluştu.',
  }
} as const;