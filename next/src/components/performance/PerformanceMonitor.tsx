'use client';

import { useEffect, useRef } from 'react';

interface PerformanceMetrics {
  name: string;
  value: number;
  timestamp: number;
}

interface PerformanceMonitorProps {
  componentName: string;
  enabled?: boolean;
  onMetric?: (metric: PerformanceMetrics) => void;
  children: React.ReactNode;
}

export function PerformanceMonitor({
  componentName,
  enabled = process.env.NODE_ENV === 'development',
  onMetric,
  children
}: PerformanceMonitorProps) {
  const startTimeRef = useRef<number | undefined>(undefined);
  const mountTimeRef = useRef<number | undefined>(undefined);

  useEffect(() => {
    if (!enabled) return;

    
    mountTimeRef.current = performance.now();
    const mountMetric: PerformanceMetrics = {
      name: `${componentName}_mount`,
      value: mountTimeRef.current - (startTimeRef.current || 0),
      timestamp: Date.now()
    };

    if (onMetric) {
      onMetric(mountMetric);
    } else {
      console.log(`🎯 Performance: ${mountMetric.name} took ${mountMetric.value.toFixed(2)}ms`);
    }

    return () => {
      
      const unmountTime = performance.now();
      const unmountMetric: PerformanceMetrics = {
        name: `${componentName}_unmount`,
        value: unmountTime - (mountTimeRef.current || 0),
        timestamp: Date.now()
      };

      if (onMetric) {
        onMetric(unmountMetric);
      } else {
        console.log(`🎯 Performance: ${componentName} was mounted for ${unmountMetric.value.toFixed(2)}ms`);
      }
    };
  }, [componentName, enabled, onMetric]);

  
  if (enabled) {
    startTimeRef.current = performance.now();
  }

  return <>{children}</>;
}


export function withPerformanceMonitor<P extends Record<string, unknown>>(
  Component: React.ComponentType<P>,
  componentName?: string
) {
  const WithPerformanceMonitor = (props: P) => (
    <PerformanceMonitor 
      componentName={componentName || Component.displayName || Component.name || 'Unknown'}
    >
      <Component {...props} />
    </PerformanceMonitor>
  );

  WithPerformanceMonitor.displayName = `withPerformanceMonitor(${Component.displayName || Component.name})`;
  
  return WithPerformanceMonitor;
}


export function usePerformanceMetric(name: string, enabled = process.env.NODE_ENV === 'development') {
  const startTimeRef = useRef<number | undefined>(undefined);

  const start = () => {
    if (enabled) {
      startTimeRef.current = performance.now();
    }
  };

  const end = (onMetric?: (metric: PerformanceMetrics) => void) => {
    if (!enabled || !startTimeRef.current) return;

    const endTime = performance.now();
    const metric: PerformanceMetrics = {
      name,
      value: endTime - startTimeRef.current,
      timestamp: Date.now()
    };

    if (onMetric) {
      onMetric(metric);
    } else {
      console.log(`🎯 Performance: ${metric.name} took ${metric.value.toFixed(2)}ms`);
    }

    startTimeRef.current = undefined;
  };

  return { start, end };
}


export function WebVitalsMonitor() {
  useEffect(() => {
    if (typeof window === 'undefined' || process.env.NODE_ENV !== 'development') {
      return;
    }

    
    const observer = new PerformanceObserver((list) => {
      for (const entry of list.getEntries()) {
        if (entry.entryType === 'navigation') {
          const navEntry = entry as PerformanceNavigationTiming;
          console.log('🎯 Navigation Metrics:', {
            domContentLoaded: navEntry.domContentLoadedEventEnd - navEntry.domContentLoadedEventStart,
            loadComplete: navEntry.loadEventEnd - navEntry.loadEventStart,
            firstPaint: navEntry.responseEnd - navEntry.requestStart,
          });
        }

        if (entry.entryType === 'largest-contentful-paint') {
          console.log('🎯 LCP:', entry.startTime);
        }

        if (entry.entryType === 'first-input') {
          const fidEntry = entry as PerformanceEventTiming;
          console.log('🎯 FID:', fidEntry.processingStart - fidEntry.startTime);
        }
      }
    });

    try {
      observer.observe({ entryTypes: ['navigation', 'largest-contentful-paint', 'first-input'] });
    } catch (error) {
      console.warn('Performance Observer not supported:', error);
    }

    return () => observer.disconnect();
  }, []);

  return null;
}