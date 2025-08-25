import { useEffect, useState, useCallback } from 'react';
import { logoutManager } from '@/lib/logout-utils';

/**
 * Hook to guard components against execution during logout
 */
export const useLogoutGuard = () => {
  const [isLoggingOut, setIsLoggingOut] = useState(false);

  useEffect(() => {
    // Set initial state
    setIsLoggingOut(logoutManager.isLoggingOutState());

    // Subscribe to logout state changes
    const unsubscribe = logoutManager.subscribe(() => {
      setIsLoggingOut(logoutManager.isLoggingOutState());
    });

    return unsubscribe;
  }, []);

  /**
   * Check if execution should be prevented due to logout
   */
  const shouldPreventExecution = useCallback(() => {
    return logoutManager.shouldPreventExecution();
  }, []);

  /**
   * Check if an error should be ignored due to logout
   */
  const shouldIgnoreError = useCallback((error: any) => {
    return logoutManager.shouldIgnoreError(error);
  }, []);

  /**
   * Wrapper for async functions that should be prevented during logout
   */
  const guardedExecution = useCallback(async <T>(
    fn: () => Promise<T>,
    options: {
      onPreventExecution?: () => void;
      onError?: (error: any) => void;
      shouldThrowOnPrevent?: boolean;
    } = {}
  ): Promise<T | null> => {
    const {
      onPreventExecution,
      onError,
      shouldThrowOnPrevent = false
    } = options;

    // Prevent execution if logging out
    if (shouldPreventExecution()) {
      onPreventExecution?.();
      if (shouldThrowOnPrevent) {
        throw new Error('Execution prevented due to logout');
      }
      return null;
    }

    try {
      return await fn();
    } catch (error) {
      // Ignore errors during logout
      if (shouldIgnoreError(error)) {
        return null;
      }

      // Handle other errors
      onError?.(error);
      throw error;
    }
  }, [shouldPreventExecution, shouldIgnoreError]);

  /**
   * Wrapper for useEffect callbacks that should be prevented during logout
   */
  const guardedEffect = useCallback((
    effectFn: () => void | Promise<void>,
    deps?: React.DependencyList
  ) => {
    return useEffect(() => {
      if (shouldPreventExecution()) {
        return;
      }

      const result = effectFn();
      
      // If it's a promise, catch any errors
      if (result && typeof result.then === 'function') {
        result.catch((error) => {
          if (!shouldIgnoreError(error)) {
            console.error('Guarded effect error:', error);
          }
        });
      }
    }, deps);
  }, [shouldPreventExecution, shouldIgnoreError]);

  return {
    isLoggingOut,
    shouldPreventExecution,
    shouldIgnoreError,
    guardedExecution,
    guardedEffect,
  };
};