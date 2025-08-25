'use client';

import { useState, useEffect } from 'react';
import { Layout } from '@/components/layout/Layout';
import dynamic from 'next/dynamic';
import { HeroSection } from '@/components/home/HeroSection';
import { ProductGrid } from '@/components/product/ProductGrid';

const FilterSidebar = dynamic(() => 
  import('@/components/product/FilterSidebar').then(mod => ({ default: mod.FilterSidebar })), 
  { 
    loading: () => <div className="w-80 bg-muted/30 rounded-lg animate-pulse h-96" />,
    ssr: false 
  }
);
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Filter } from 'lucide-react';
import { getAllProducts } from '@/services/productService';
import type { ProductFilters } from '@/types/api';
import type { Product } from '@/types/product';

export default function Home() {
  const [filters, setFilters] = useState<ProductFilters>({
    page: 1
  });
  const [showFilters, setShowFilters] = useState(false);
  const [products, setProducts] = useState<Product[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);
  const [isError, setIsError] = useState(false);
  
  useEffect(() => {
    const fetchProducts = async () => {
      try {
        setLoading(true);
        setError(null);
        setIsError(false);
        const { products: data } = await getAllProducts(filters);
        setProducts(data);
      } catch (err) {
        setError('Failed to load products');
        setIsError(true);
        console.error('Failed to fetch products:', err);
      } finally {
        setLoading(false);
      }
    };
    fetchProducts();
  }, [filters]);

  const handleSearchChange = (query: string) => {
    setFilters(prev => ({
      ...prev,
      search: query || undefined,
      page: 1
    }));
  };

  const handleFiltersChange = (newFilters: ProductFilters) => {
    setFilters({ ...newFilters, page: 1 });
  };

  const handleClearFilters = () => {
    setFilters({ page: 1 });
  };


  return (
    <Layout onSearchChange={handleSearchChange}>
      {}
      <HeroSection />
      
      <div className="container mx-auto px-4 py-8" id="urunler">

        {}
        <div className="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 gap-4">
          <div className="flex items-center gap-4">
            <h2 className="text-2xl font-semibold">
              Products {!loading && products && (
                <Badge variant="secondary" className="ml-2">
                  {products.length} products
                </Badge>
              )}
            </h2>
            {loading && <Badge variant="outline">Loading...</Badge>}
            {isError && <Badge variant="destructive">{error || 'Failed to load products'}</Badge>}
          </div>
          
          <Button
            variant="outline"
            onClick={() => setShowFilters(!showFilters)}
            className="md:hidden flex items-center gap-2"
          >
            <Filter className="w-4 h-4" />
            Filters
          </Button>
        </div>

        <div className="grid grid-cols-1 lg:grid-cols-4 gap-8">
          {}
          <div className={`lg:block ${showFilters ? 'block' : 'hidden'}`}>
            <div className="sticky top-24">
              <FilterSidebar
                filters={filters}
                onFiltersChange={handleFiltersChange}
                onClearFilters={handleClearFilters}
              />
            </div>
          </div>

          {}
          <div className="lg:col-span-3">
            {loading ? (
              <div className="flex justify-center items-center h-96">
                <div className="animate-spin rounded-full h-32 w-32 border-b-2 border-red-600"></div>
              </div>
            ) : isError ? (
              <div className="text-center py-12">
                <p className="text-red-600 mb-4">{error || 'Failed to load products'}</p>
                <Button onClick={() => window.location.reload()}>
                  Try Again
                </Button>
              </div>
            ) : (
              <ProductGrid
                products={products || []}
              />
            )}
          </div>
        </div>

      </div>
    </Layout>
  );
}
