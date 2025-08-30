'use client';

import { useState, useEffect } from 'react';
import Link from 'next/link';
import { Button } from '@/components/ui/button';
import { ArrowRight, ShoppingBag, Sparkles, ChevronLeft, ChevronRight } from 'lucide-react';
import { getAllProducts } from '@/services/productService';

import Image from 'next/image';
import type { Product } from '@/types/product';

export function HeroSection() {
  const [currentSlide, setCurrentSlide] = useState(0);
  const [products, setProducts] = useState<Product[]>([]);
  const [loading, setLoading] = useState(true);
  
  useEffect(() => {
    const fetchFeaturedProducts = async () => {
      try {
        const { products: data } = await getAllProducts({ limit: 8, isFeatured: true });
        setProducts(data);
      } catch (error) {
        console.error('Failed to fetch featured products:', error);
      } finally {
        setLoading(false);
      }
    };
    fetchFeaturedProducts();
  }, []);

  const featuredProducts = products.slice(0, 8);

  
  useEffect(() => {
    const interval = setInterval(() => {
      setCurrentSlide((prev) => (prev + 1) % featuredProducts.length);
    }, 4000);
    return () => clearInterval(interval);
  }, [featuredProducts.length]);

  const nextSlide = () => {
    setCurrentSlide((prev) => (prev + 1) % featuredProducts.length);
  };

  const prevSlide = () => {
    setCurrentSlide((prev) => (prev - 1 + featuredProducts.length) % featuredProducts.length);
  };


  return (
    <section className="relative min-h-[85vh] overflow-hidden bg-gradient-to-br from-gray-50 via-white to-gray-100">
      {}
      <div className="container mx-auto px-4 py-16 relative z-10">
        <div className="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center min-h-[70vh]">
          
          {}
          <div className="space-y-8 text-left">
            {}
            <div className="inline-flex items-center gap-2 px-4 py-2 bg-turkticaret-50 border border-turkticaret-200 rounded-full">
              <Sparkles className="w-4 h-4 text-turkticaret-500" />
              <span className="text-turkticaret-700 font-medium text-sm">Turkey&apos;s #1 E-Commerce Platform</span>
            </div>

            {}
            <div className="space-y-4">
              <h1 className="text-4xl md:text-5xl lg:text-6xl font-black text-gray-900 leading-tight">
                <span className="block">Quality</span>
                <span 
                  className="block bg-clip-text text-transparent"
                  style={{
                    background: 'linear-gradient(to right, #e55a2b, #c04426)',
                    WebkitBackgroundClip: 'text',
                    backgroundClip: 'text'
                  }}
                >
                  Products
                </span>
                <span className="block text-gray-700">Fast Delivery</span>
              </h1>
              
              <p className="text-lg md:text-xl text-gray-600 leading-relaxed max-w-lg">
                Premium brands, reliable shopping experience and same-day delivery. 
                <span className="font-semibold" style={{ color: '#2563eb' }}>Experience the Ecommerce difference.</span>
              </p>
            </div>

            {}
            <div className="flex flex-col sm:flex-row gap-4">
              <Button
                size="lg"
                className="group text-white px-8 py-4 text-lg font-semibold shadow-lg hover:shadow-xl transition-all duration-300"
                style={{ 
                  backgroundColor: '#e55a2b'
                }}
                onMouseEnter={(e) => {
                  e.currentTarget.style.backgroundColor = '#c04426';
                }}
                onMouseLeave={(e) => {
                  e.currentTarget.style.backgroundColor = '#e55a2b';
                }}
                asChild
              >
                <Link href="#urunler">
                  <ShoppingBag className="w-5 h-5 mr-2" />
                  Start Shopping
                  <ArrowRight className="w-5 h-5 ml-2 group-hover:translate-x-1 transition-transform" />
                </Link>
              </Button>
              
              <Button
                variant="outline" 
                size="lg"
                className="px-8 py-4 text-lg font-semibold border-2 transition-all duration-300"
                style={{ 
                  borderColor: '#d1d5db'
                }}
                onMouseEnter={(e) => {
                  e.currentTarget.style.borderColor = '#fbb890';
                  e.currentTarget.style.backgroundColor = '#fef7f0';
                }}
                onMouseLeave={(e) => {
                  e.currentTarget.style.borderColor = '#d1d5db';
                  e.currentTarget.style.backgroundColor = 'transparent';
                }}
                asChild
              >
                <Link href="#featured">
                  Discover Products
                </Link>
              </Button>
            </div>

            {}
            <div className="flex flex-col sm:flex-row items-start sm:items-center gap-6">
              <div className="text-gray-600 text-sm font-medium">
                <span className="font-bold" style={{ color: '#e55a2b' }}>50,000+</span> happy customers
              </div>
              
              <div className="text-gray-600 text-sm font-medium">
                <span className="font-bold" style={{ color: '#e55a2b' }}>24/7</span> support
              </div>
            </div>
          </div>

          {}
          <div className="relative">
            <div className="relative overflow-hidden rounded-3xl bg-white shadow-2xl">
              {}
              <div className="relative h-[500px] lg:h-[600px]">
                {loading ? (
                  <div className="flex items-center justify-center h-full">
                    <div className="animate-spin rounded-full h-32 w-32 border-b-2 border-red-600"></div>
                  </div>
                ) : featuredProducts.length > 0 ? (
                  featuredProducts.map((product, index) => (
                  <div
                    key={product.uuid}
                    className={`absolute inset-0 transition-all duration-700 ease-in-out ${
                      index === currentSlide 
                        ? 'opacity-100 translate-x-0' 
                        : index < currentSlide 
                        ? 'opacity-0 -translate-x-full' 
                        : 'opacity-0 translate-x-full'
                    }`}
                  >
                    {}
                    <div className="relative h-full bg-gradient-to-br from-gray-50 to-gray-100 overflow-hidden">
                      {product.images && product.images.length > 0 ? (
                        <Image
                          src={product.images[0].url}
                          alt={product.name}
                          fill
                          className="object-cover transition-transform duration-700 hover:scale-105"
                          sizes="(max-width: 768px) 100vw, (max-width: 1200px) 50vw, 50vw"
                          priority={index === 0}
                        />
                      ) : (
                        <div className="flex items-center justify-center h-full">
                          <div className="text-8xl font-bold text-gray-300">
                            {product.name.charAt(0)}
                          </div>
                        </div>
                      )}
                      
                      {}
                      <div className="absolute bottom-0 left-0 right-0 bg-gradient-to-t from-black/60 to-transparent p-6">
                        <div className="text-white">
                          <div className="text-sm font-medium mb-1" style={{ color: '#fbb890' }}>
                            {product.categoryName}
                          </div>
                          <h3 className="text-xl font-bold mb-2">{product.name}</h3>
                          <div className="text-2xl font-black" style={{ color: '#f89060' }}>
                            {product.price.formatted}
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                  ))
                ) : (
                  <div className="flex items-center justify-center h-full">
                    <div className="text-center text-gray-500">
                      <div className="text-6xl mb-4">📦</div>
                      <p>No featured products available</p>
                    </div>
                  </div>
                )}
              </div>

              {}
              {!loading && featuredProducts.length > 1 && (
                <>
                  <button
                    onClick={prevSlide}
                    className="absolute left-4 top-1/2 -translate-y-1/2 bg-white/90 hover:bg-white backdrop-blur-sm rounded-full p-3 shadow-lg transition-all duration-300 hover:scale-110"
                  >
                    <ChevronLeft className="w-6 h-6 text-gray-700" />
                  </button>
                  
                  <button
                    onClick={nextSlide}
                    className="absolute right-4 top-1/2 -translate-y-1/2 bg-white/90 hover:bg-white backdrop-blur-sm rounded-full p-3 shadow-lg transition-all duration-300 hover:scale-110"
                  >
                    <ChevronRight className="w-6 h-6 text-gray-700" />
                  </button>
                </>
              )}

              {}
              {!loading && featuredProducts.length > 1 && (
                <div className="absolute bottom-4 left-1/2 -translate-x-1/2 flex gap-2">
                  {featuredProducts.map((_, index) => (
                  <button
                    key={index}
                    onClick={() => setCurrentSlide(index)}
                    className="w-3 h-3 rounded-full transition-all duration-300"
                    style={{
                      backgroundColor: index === currentSlide ? '#f5703a' : 'rgba(255, 255, 255, 0.6)',
                      transform: index === currentSlide ? 'scale(1.25)' : 'scale(1)'
                    }}
                    onMouseEnter={(e) => {
                      if (index !== currentSlide) {
                        e.currentTarget.style.backgroundColor = 'rgba(255, 255, 255, 0.8)';
                      }
                    }}
                    onMouseLeave={(e) => {
                      if (index !== currentSlide) {
                        e.currentTarget.style.backgroundColor = 'rgba(255, 255, 255, 0.6)';
                      }
                    }}
                  />
                  ))}
                </div>
              )}
            </div>

            {}
            {!loading && featuredProducts.length > 0 && (
              <div className="mt-6 flex gap-4 overflow-x-auto pb-2">
                {featuredProducts.slice(0, 4).map((product, index) => (
                <Link
                  key={product.uuid}
                  href={`/product/${product.slug}`}
                  className="flex-shrink-0 p-3 rounded-lg border transition-all duration-300"
                  style={{
                    backgroundColor: index === currentSlide ? '#fef7f0' : '#ffffff',
                    borderColor: index === currentSlide ? '#fdd7be' : '#e5e7eb',
                    boxShadow: index === currentSlide ? '0 4px 6px -1px rgba(0, 0, 0, 0.1)' : 'none'
                  }}
                  onMouseEnter={(e) => {
                    if (index !== currentSlide) {
                      e.currentTarget.style.borderColor = '#fdd7be';
                      e.currentTarget.style.boxShadow = '0 4px 6px -1px rgba(0, 0, 0, 0.1)';
                    }
                  }}
                  onMouseLeave={(e) => {
                    if (index !== currentSlide) {
                      e.currentTarget.style.borderColor = '#e5e7eb';
                      e.currentTarget.style.boxShadow = 'none';
                    }
                  }}
                  onClick={() => setCurrentSlide(index)}
                >
                  <div className="text-xs text-gray-500 mb-1">{product.categoryName}</div>
                  <div className="text-sm font-semibold text-gray-900 truncate w-20">
                    {product.name}
                  </div>
                  <div className="text-sm font-bold" style={{ color: '#e55a2b' }}>
                    {product.price.formatted}
                  </div>
                </Link>
                ))}
              </div>
            )}
          </div>
        </div>
      </div>

      {}
      <div 
        className="absolute top-20 left-10 w-2 h-2 rounded-full animate-pulse opacity-40"
        style={{ backgroundColor: '#f89060' }}
      />
      <div className="absolute top-40 right-20 w-3 h-3 bg-blue-400 rounded-full animate-bounce opacity-30" />
      <div className="absolute bottom-40 left-20 w-4 h-4 bg-yellow-400 rounded-full animate-ping opacity-20" />
    </section>
  );
}