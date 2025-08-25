import { createSlice, createAsyncThunk, PayloadAction } from '@reduxjs/toolkit';
import { cartService, CartItem } from '@/services/cartService';
import { SecureStorage } from '@/lib/security';
import { STORAGE_KEYS } from '@/lib/constants';
import { MoneyInfo } from '@/types/money';

interface CartState {
  items: CartItem[];
  total: MoneyInfo;
  itemCount: number;
  isOpen: boolean;
  isLoading: boolean;
  error: string | null;
}

const initialState: CartState = {
  items: [],
  total: null as unknown as MoneyInfo,
  itemCount: 0,
  isOpen: false,
  isLoading: false,
  error: null,
};



export const loadCart = createAsyncThunk(
  'cart/loadCart',
  async (isAuthenticated: boolean, { rejectWithValue }) => {
    try {
      if (isAuthenticated) {
        
        const cartData = await cartService.getCart();
        return cartData;
      } else {
        
        return { items: [], total: null as unknown as MoneyInfo, itemCount: 0 };
      }
    } catch (error) {
      console.error('Cart API error:', error);
      return rejectWithValue('Failed to load cart');
    }
  }
);

export const syncCartWithAPI = createAsyncThunk(
  'cart/syncCartWithAPI',
  async (items: CartItem[], { rejectWithValue }) => {
    try {
      
      for (const item of items) {
        await cartService.addToCart(item.productUuid, item.quantity);
      }
      
      const cartData = await cartService.getCart();
      return cartData;
    } catch {
      return rejectWithValue('Failed to sync cart with API');
    }
  }
);

export const addToCartAPI = createAsyncThunk(
  'cart/addToCartAPI',
  async ({ productUuid, quantity, isAuthenticated }: { productUuid: string; quantity: number; isAuthenticated: boolean }, { rejectWithValue }) => {
    try {
      if (isAuthenticated) {
        await cartService.addToCart(productUuid, quantity);
        const cartData = await cartService.getCart();
        return cartData;
      } else {
        
        return null;
      }
    } catch {
      return rejectWithValue('Failed to add item to cart');
    }
  }
);

export const updateCartItemAPI = createAsyncThunk(
  'cart/updateCartItemAPI',
  async ({ productUuid, quantity, isAuthenticated }: { productUuid: string; quantity: number; isAuthenticated: boolean }, { rejectWithValue }) => {
    try {
      if (isAuthenticated) {
        await cartService.updateCartItem(productUuid, quantity);
        const cartData = await cartService.getCart();
        return cartData;
      } else {
        
        return null;
      }
    } catch {
      return rejectWithValue('Failed to update cart item');
    }
  }
);

export const removeFromCartAPI = createAsyncThunk(
  'cart/removeFromCartAPI',  
  async ({ productUuid, isAuthenticated }: { productUuid: string; isAuthenticated: boolean }, { rejectWithValue }) => {
    try {
      if (isAuthenticated) {
        await cartService.removeFromCart(productUuid);
        const cartData = await cartService.getCart();
        return cartData;
      } else {
        
        return null;
      }
    } catch {
      return rejectWithValue('Failed to remove item from cart');
    }
  }
);

export const clearCartAPI = createAsyncThunk(
  'cart/clearCartAPI',
  async (isAuthenticated: boolean, { rejectWithValue }) => {
    try {
      if (isAuthenticated) {
        await cartService.clearCart();
      }
      return [];
    } catch {
      return rejectWithValue('Failed to clear cart');
    }
  }
);


const cartSlice = createSlice({
  name: 'cart',
  initialState,
  reducers: {
    addItem: (state, action: PayloadAction<Omit<CartItem, 'uuid'>>) => {
      const existingItemIndex = state.items.findIndex(
        item => 
          item.productUuid === action.payload.productUuid &&
          item.variant === action.payload.variant &&
          item.size === action.payload.size &&
          item.color === action.payload.color
      );

      if (existingItemIndex >= 0) {
        
        state.items[existingItemIndex].quantity += action.payload.quantity;
      } else {
        
        const newItem: CartItem = {
          ...action.payload,
          uuid: action.payload.productUuid
        };
        state.items.push(newItem);
      }

      state.itemCount = state.items.reduce((sum, item) => sum + item.quantity, 0);
    },

    removeItem: (state, action: PayloadAction<string>) => {
      state.items = state.items.filter(item => item.uuid !== action.payload);
      state.itemCount = state.items.reduce((sum, item) => sum + item.quantity, 0);
    },

    updateQuantity: (state, action: PayloadAction<{ uuid: string; quantity: number }>) => {
      if (action.payload.quantity <= 0) {
        
        state.items = state.items.filter(item => item.uuid !== action.payload.uuid);
      } else {
        const item = state.items.find(item => item.uuid === action.payload.uuid);
        if (item) {
          item.quantity = action.payload.quantity;
        }
      }

      state.itemCount = state.items.reduce((sum, item) => sum + item.quantity, 0);
    },

    clearCart: (state) => {
      state.items = [];
      state.total = null as unknown as MoneyInfo;
      state.itemCount = 0;
    },

    toggleCart: (state) => {
      state.isOpen = !state.isOpen;
    },

    closeCart: (state) => {
      state.isOpen = false;
    },

    openCart: (state) => {
      state.isOpen = true;
    },

    saveToStorage: (state) => {
      try {
        SecureStorage.setItem(STORAGE_KEYS.CART_DATA, JSON.stringify(state.items));
      } catch (error) {
        console.error('Failed to save cart to storage:', error);
      }
    },
  },
  extraReducers: (builder) => {
    
    builder
      .addCase(loadCart.pending, (state) => {
        state.isLoading = true;
        state.error = null;
      })
      .addCase(loadCart.fulfilled, (state, action) => {
        state.isLoading = false;
        state.items = action.payload.items;
        state.total = action.payload.total;
        state.itemCount = action.payload.itemCount;
        state.error = null;
      })
      .addCase(loadCart.rejected, (state, action) => {
        state.isLoading = false;
        state.error = action.payload as string;
      });

    
    builder
      .addCase(syncCartWithAPI.pending, (state) => {
        state.isLoading = true;
      })
      .addCase(syncCartWithAPI.fulfilled, (state, action) => {
        state.isLoading = false;
        state.items = action.payload.items;
        state.total = action.payload.total;
        state.itemCount = action.payload.itemCount;
        state.error = null;
      })
      .addCase(syncCartWithAPI.rejected, (state, action) => {
        state.isLoading = false;
        state.error = action.payload as string;
      });

    
    builder
      .addCase(addToCartAPI.pending, (state) => {
        state.isLoading = true;
      })
      .addCase(addToCartAPI.fulfilled, (state, action) => {
        state.isLoading = false;
        if (action.payload) {
          state.items = action.payload.items;
          state.total = action.payload.total;
          state.itemCount = action.payload.itemCount;
        }
        state.error = null;
      })
      .addCase(addToCartAPI.rejected, (state, action) => {
        state.isLoading = false;
        state.error = action.payload as string;
      });

    
    builder
      .addCase(updateCartItemAPI.pending, (state) => {
        state.isLoading = true;
      })
      .addCase(updateCartItemAPI.fulfilled, (state, action) => {
        state.isLoading = false;
        if (action.payload) {
          state.items = action.payload.items;
          state.total = action.payload.total;
          state.itemCount = action.payload.itemCount;
        }
        state.error = null;
      })
      .addCase(updateCartItemAPI.rejected, (state, action) => {
        state.isLoading = false;
        state.error = action.payload as string;
      });

    
    builder
      .addCase(removeFromCartAPI.pending, (state) => {
        state.isLoading = true;
      })
      .addCase(removeFromCartAPI.fulfilled, (state, action) => {
        state.isLoading = false;
        if (action.payload) {
          state.items = action.payload.items;
          state.total = action.payload.total;
          state.itemCount = action.payload.itemCount;
        }
        state.error = null;
      })
      .addCase(removeFromCartAPI.rejected, (state, action) => {
        state.isLoading = false;
        state.error = action.payload as string;
      });

    
    builder
      .addCase(clearCartAPI.pending, (state) => {
        state.isLoading = true;
      })
      .addCase(clearCartAPI.fulfilled, (state) => {
        state.isLoading = false;
        state.items = [];
        state.total = { raw: 0, formatted: '', formatted_minus: '', type: 'nil' };
        state.itemCount = 0;
        state.error = null;
      })
      .addCase(clearCartAPI.rejected, (state, action) => {
        state.isLoading = false;
        state.error = action.payload as string;
      });
  },
});

export const { 
  addItem, 
  removeItem, 
  updateQuantity, 
  clearCart, 
  toggleCart, 
  closeCart, 
  openCart,
  saveToStorage
} = cartSlice.actions;



export default cartSlice.reducer;