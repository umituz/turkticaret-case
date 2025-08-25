import { useState, useCallback, useMemo, useEffect } from 'react';
import { useToast } from '@/hooks/use-toast';
import { useSearch } from '@/hooks/useSearch';

export interface UseAdminListPageConfig<T, TStats> {
  fetchItems: (searchQuery?: string) => Promise<T[]>;
  deleteItem: (id: string) => Promise<void>;
  calculateStats: (items: T[]) => TStats;
  itemName: string;
  searchConfig?: {
    delay?: number;
    minLength?: number;
  };
}

export interface UseAdminListPageReturn<T, TStats> {
  items: T[];
  loading: boolean;
  deleteLoading: string | null;
  stats: TStats;
  searchTerm: string;
  debouncedSearchTerm: string;
  setSearchTerm: (term: string) => void;
  clearSearch: () => void;
  handleDelete: (id: string, name: string) => Promise<void>;
  refreshItems: () => Promise<void>;
}

export function useAdminListPage<T, TStats>({
  fetchItems,
  deleteItem,
  calculateStats,
  itemName,
  searchConfig = {}
}: UseAdminListPageConfig<T, TStats>): UseAdminListPageReturn<T, TStats> {
  const [items, setItems] = useState<T[]>([]);
  const [loading, setLoading] = useState(true);
  const [deleteLoading, setDeleteLoading] = useState<string | null>(null);
  
  const { toast } = useToast();
  const { searchTerm, debouncedSearchTerm, setSearchTerm, clearSearch } = useSearch({
    delay: 300,
    minLength: 0,
    ...searchConfig
  });

  const stats = useMemo(() => calculateStats(items), [items, calculateStats]);

  const loadItems = useCallback(async (searchQuery = '') => {
    try {
      setLoading(true);
      const result = await fetchItems(searchQuery);
      setItems(result);
    } catch (error) {
      toast({
        title: 'Error!',
        description: `Failed to load ${itemName}s.`,
        variant: 'destructive',
      });
      console.error(`Error loading ${itemName}s:`, error);
    } finally {
      setLoading(false);
    }
  }, [fetchItems, itemName, toast]);

  useEffect(() => {
    void loadItems(debouncedSearchTerm);
  }, [debouncedSearchTerm, loadItems]);

  const handleDelete = useCallback(async (id: string, name: string) => {
    if (!confirm(`Are you sure you want to delete "${name}"? This action cannot be undone.`)) {
      return;
    }

    try {
      setDeleteLoading(id);
      await deleteItem(id);
      
      toast({
        title: 'Success!',
        description: `${itemName} deleted successfully.`,
      });
      
      await loadItems(debouncedSearchTerm);
    } catch (error) {
      toast({
        title: 'Error!',
        description: error instanceof Error ? error.message : `Failed to delete ${itemName}.`,
        variant: 'destructive',
      });
      console.error(`Error deleting ${itemName}:`, error);
    } finally {
      setDeleteLoading(null);
    }
  }, [deleteItem, itemName, toast, loadItems, debouncedSearchTerm]);

  const refreshItems = useCallback(() => loadItems(debouncedSearchTerm), [loadItems, debouncedSearchTerm]);

  return {
    items,
    loading,
    deleteLoading,
    stats,
    searchTerm,
    debouncedSearchTerm,
    setSearchTerm,
    clearSearch,
    handleDelete,
    refreshItems,
  };
}