import { STORAGE_KEYS } from './constants';

/**
 * Centralized logout state management utility
 */
export class LogoutManager {
  private static instance: LogoutManager;
  private isLoggingOut = false;
  private listeners: Array<() => void> = [];

  private constructor() {}

  public static getInstance(): LogoutManager {
    if (!LogoutManager.instance) {
      LogoutManager.instance = new LogoutManager();
    }
    return LogoutManager.instance;
  }

  /**
   * Check if logout is currently in progress
   */
  public isLoggingOutState(): boolean {
    if (typeof window === 'undefined') return false;
    
    // Check both in-memory state and session storage
    return this.isLoggingOut || 
           window.sessionStorage.getItem(STORAGE_KEYS.LOGGING_OUT) === 'true';
  }

  /**
   * Set logout state to prevent API calls
   */
  public setLoggingOut(state: boolean): void {
    this.isLoggingOut = state;
    
    if (typeof window !== 'undefined') {
      if (state) {
        window.sessionStorage.setItem(STORAGE_KEYS.LOGGING_OUT, 'true');
      } else {
        window.sessionStorage.removeItem(STORAGE_KEYS.LOGGING_OUT);
      }
    }

    // Notify all listeners about state change
    this.listeners.forEach(listener => listener());
  }

  /**
   * Subscribe to logout state changes
   */
  public subscribe(listener: () => void): () => void {
    this.listeners.push(listener);
    
    // Return unsubscribe function
    return () => {
      this.listeners = this.listeners.filter(l => l !== listener);
    };
  }

  /**
   * Check if a component should prevent execution due to logout
   */
  public shouldPreventExecution(): boolean {
    return this.isLoggingOutState();
  }

  /**
   * Check if an API error should be ignored due to logout
   */
  public shouldIgnoreError(error: unknown): boolean {
    if (this.isLoggingOutState()) {
      return true;
    }

    // Check if error is authentication related
    // eslint-disable-next-line @typescript-eslint/no-explicit-any
    const errorObj = error as any;
    const isAuthError = errorObj?.message?.includes('Authentication') ||
                       errorObj?.message?.includes('401') ||
                       errorObj?.status === 401;

    return isAuthError && this.isLoggingOutState();
  }

  /**
   * Clean up logout state
   */
  public cleanup(): void {
    this.setLoggingOut(false);
  }
}

// Export singleton instance
export const logoutManager = LogoutManager.getInstance();

/**
 * Utility function to check if logout is in progress
 */
export const isLoggingOut = (): boolean => {
  return logoutManager.isLoggingOutState();
};

/**
 * Utility function to set logout state
 */
export const setLogoutState = (state: boolean): void => {
  logoutManager.setLoggingOut(state);
};

/**
 * Utility function to check if execution should be prevented
 */
export const shouldPreventExecution = (): boolean => {
  return logoutManager.shouldPreventExecution();
};

/**
 * Utility function to check if error should be ignored
 */
export const shouldIgnoreLogoutError = (error: unknown): boolean => {
  return logoutManager.shouldIgnoreError(error);
};