import { createSlice, createAsyncThunk } from '@reduxjs/toolkit';
import { Product } from '@/types/product';
import { ProductFilters } from '@/types/api';
import { getAllProducts as fetchAllProducts } from '@/services/productService';

interface ProductsState {
  products: Product[];
  currentProduct: Product | null;
  loading: boolean;
  error: string | null;
  lastFetch: number | null;
  filters: ProductFilters | null;
  total: number;
}

const initialState: ProductsState = {
  products: [],
  currentProduct: null,
  loading: false,
  error: null,
  lastFetch: null,
  filters: null,
  total: 0,
};


const CACHE_DURATION = 5 * 60 * 1000;


export const fetchProducts = createAsyncThunk(
  'products/fetchAll',
  async (filters?: ProductFilters) => {
    const response = await fetchAllProducts(filters);
    return response;
  }
);




const productsSlice = createSlice({
  name: 'products',
  initialState,
  reducers: {
    
    clearError: (state) => {
      state.error = null;
    },
  },
  extraReducers: (builder) => {
    builder
      
      .addCase(fetchProducts.pending, (state) => {
        state.loading = true;
        state.error = null;
      })
      .addCase(fetchProducts.fulfilled, (state, action) => {
        state.loading = false;
        state.products = action.payload.products;
        state.total = action.payload.total;
        state.lastFetch = Date.now();
        state.error = null;
      })
      .addCase(fetchProducts.rejected, (state, action) => {
        state.loading = false;
        state.error = action.error.message || 'Failed to fetch products';
      })
  },
});

export const { clearError } = productsSlice.actions;


export const selectProductBySlug = (slug: string) => (state: { products: ProductsState }) => 
  state.products.products.find(p => p.slug === slug);
export const selectProductsLoading = (state: { products: ProductsState }) => state.products.loading;
export const selectProductsError = (state: { products: ProductsState }) => state.products.error;
export const selectIsProductsCacheValid = (state: { products: ProductsState }) => {
  if (!state.products.lastFetch) return false;
  return Date.now() - state.products.lastFetch < CACHE_DURATION;
};

export default productsSlice.reducer;