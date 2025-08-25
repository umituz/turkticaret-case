export class SecureStorage {
  private static encode(value: string): string {
    return btoa(encodeURIComponent(value));
  }

  private static decode(value: string): string {
    try {
      const decoded = decodeURIComponent(atob(value));
      return decoded;
    } catch (error) {
      console.warn('Failed to decode stored value:', error);
      return '';
    }
  }

  static setItem(key: string, value: string): void {
    if (typeof window === 'undefined') return;
    
    try {
      localStorage.setItem(key, this.encode(value));
    } catch (error) {
      console.warn('Failed to save to localStorage:', error);
    }
  }

  static getItem(key: string): string | null {
    if (typeof window === 'undefined') return null;
    
    try {
      const item = localStorage.getItem(key);
      if (!item) return null;
      
      const decoded = this.decode(item);
      if (!decoded) {
        console.warn(`Corrupted data detected for key: ${key}, clearing...`);
        localStorage.removeItem(key);
        return null;
      }
      
      return decoded;
    } catch (error) {
      console.warn('Failed to read from localStorage:', error);
      localStorage.removeItem(key);
      return null;
    }
  }

  static removeItem(key: string): void {
    if (typeof window === 'undefined') return;
    
    try {
      localStorage.removeItem(key);
    } catch (error) {
      console.warn('Failed to remove from localStorage:', error);
    }
  }

  static clear(): void {
    if (typeof window === 'undefined') return;
    
    try {
      localStorage.clear();
    } catch (error) {
      console.warn('Failed to clear localStorage:', error);
    }
  }
}