import { useState, useCallback } from 'react';
import { useDebounce } from './useDebounce';

export interface UseSearchConfig {
  delay?: number;
  minLength?: number;
}

export interface UseSearchReturn {
  searchTerm: string;
  debouncedSearchTerm: string;
  setSearchTerm: (term: string) => void;
  clearSearch: () => void;
  isSearching: boolean;
}


export function useSearch(config: UseSearchConfig = {}): UseSearchReturn {
  const { delay = 300, minLength = 0 } = config;
  
  const [searchTerm, setSearchTermState] = useState('');
  const debouncedSearchTerm = useDebounce(searchTerm, delay);
  
  const setSearchTerm = useCallback((term: string) => {
    setSearchTermState(term);
  }, []);

  const clearSearch = useCallback(() => {
    setSearchTermState('');
  }, []);

  const isSearching = searchTerm !== debouncedSearchTerm && searchTerm.length >= minLength;

  return {
    searchTerm,
    debouncedSearchTerm: debouncedSearchTerm.length >= minLength ? debouncedSearchTerm : '',
    setSearchTerm,
    clearSearch,
    isSearching,
  };
}