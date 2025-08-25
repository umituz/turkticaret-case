import { createSlice, createAsyncThunk, PayloadAction } from '@reduxjs/toolkit';
import { authService } from '@/services/authService';
import { SecureStorage } from '@/lib/security';
import { STORAGE_KEYS, COOKIE_KEYS, TIME } from '@/lib/constants';
import { User } from '@/types/user';
import { handleAuthError, logError } from '@/lib/errorHandler';

export interface LoginCredentials {
  email: string;
  password: string;
}

interface AuthState {
  user: User | null;
  isLoading: boolean;
  isAuthenticated: boolean;
  error: string | null;
}

const initialState: AuthState = {
  user: null,
  isLoading: false,
  isAuthenticated: false,
  error: null,
};


export const loginUser = createAsyncThunk(
  'auth/login',
  async (credentials: LoginCredentials, { rejectWithValue }) => {
    try {
      const response = await authService.login(credentials);
      
      const user: User = {
        uuid: response.data.user.uuid,
        name: response.data.user.name,
        email: response.data.user.email,
        email_verified_at: response.data.user.email_verified_at,
        created_at: response.data.user.created_at,
        updated_at: response.data.user.updated_at,
        role: response.data.user.role || 'user'
      };

      
      authService.saveUser(user);
      SecureStorage.setItem(STORAGE_KEYS.AUTH_STATUS, 'true');
      SecureStorage.setItem(STORAGE_KEYS.AUTH_TOKEN, response.data.token);
      document.cookie = `${COOKIE_KEYS.AUTH_STATUS}=true; path=/; max-age=${TIME.COOKIE_MAX_AGE}`;

      return { user, token: response.data.token };
    } catch (error: unknown) {
      logError(error, 'AUTH_LOGIN');
      return rejectWithValue(handleAuthError(error));
    }
  }
);

export const checkAuth = createAsyncThunk(
  'auth/checkAuth',
  async (_, { rejectWithValue }) => {
    try {
      
      if (typeof window === 'undefined') {
        return rejectWithValue('Server-side auth check');
      }

      const isAuth = SecureStorage.getItem(STORAGE_KEYS.AUTH_STATUS);
      const user = authService.getCurrentUser();
      const token = SecureStorage.getItem(STORAGE_KEYS.AUTH_TOKEN);

      if (isAuth === 'true' && user && token) {
        return { user, token };
      }

      return rejectWithValue('Not authenticated');
    } catch {
      return rejectWithValue('Auth check failed');
    }
  }
);

export const refreshProfile = createAsyncThunk(
  'auth/refreshProfile',
  async (_, { rejectWithValue, getState }) => {
    try {
      const state = getState() as { auth: AuthState };
      
      if (!state.auth.isAuthenticated) {
        return rejectWithValue('Not authenticated');
      }

      const profile = await authService.getProfile();
      const user: User = {
        uuid: profile.uuid,
        name: profile.name,
        email: profile.email,
        role: profile.role || 'user',
        email_verified_at: profile.email_verified_at,
        created_at: profile.created_at,
        updated_at: profile.updated_at
      };

      authService.saveUser(user);
      return user;
    } catch (error: unknown) {
      
      const errorObj = error as { status?: number; message?: string };
      if (errorObj?.status === 401 || errorObj?.message?.includes('401')) {
        return rejectWithValue('Unauthorized');
      }
      return rejectWithValue('Profile refresh failed');
    }
  }
);

export const logoutUser = createAsyncThunk(
  'auth/logout',
  async () => {
    // Clear all auth-related storage
    if (typeof window !== 'undefined') {
      SecureStorage.removeItem(STORAGE_KEYS.AUTH_STATUS);
      SecureStorage.removeItem(STORAGE_KEYS.AUTH_TOKEN);
      SecureStorage.removeItem(STORAGE_KEYS.USER_DATA);
      document.cookie = `${COOKIE_KEYS.AUTH_STATUS}=; path=/; expires=Thu, 01 Jan 1970 00:00:00 GMT`;
    }
    
    try {
      await authService.logout();
    } catch {
      // Ignore errors during logout
    }
    
    return true;
  }
);


const authSlice = createSlice({
  name: 'auth',
  initialState,
  reducers: {
    clearError: (state) => {
      state.error = null;
    },
    updateUser: (state, action: PayloadAction<User>) => {
      state.user = action.payload;
      authService.saveUser(action.payload);
    },
    initializeAuth: (state, action: PayloadAction<{ user: User; token: string }>) => {
      state.user = action.payload.user;
      state.isAuthenticated = true;
      state.isLoading = false;
      state.error = null;
    },
  },
  extraReducers: (builder) => {
    
    builder
      .addCase(loginUser.pending, (state) => {
        state.isLoading = true;
        state.error = null;
      })
      .addCase(loginUser.fulfilled, (state, action) => {
        state.isLoading = false;
        state.user = action.payload.user;
        state.isAuthenticated = true;
        state.error = null;
      })
      .addCase(loginUser.rejected, (state, action) => {
        state.isLoading = false;
        state.error = action.payload as string;
      });

    
    builder
      .addCase(checkAuth.pending, (state) => {
        state.isLoading = true;
      })
      .addCase(checkAuth.fulfilled, (state, action) => {
        state.isLoading = false;
        state.user = action.payload.user;
        state.isAuthenticated = true;
        state.error = null;
      })
      .addCase(checkAuth.rejected, (state) => {
        state.isLoading = false;
        state.user = null;
        state.isAuthenticated = false;
      });

    
    builder
      .addCase(refreshProfile.fulfilled, (state, action) => {
        state.user = action.payload;
      })
      .addCase(refreshProfile.rejected, (state, action) => {
        
        if (action.payload === 'Unauthorized') {
          state.user = null;
          state.isAuthenticated = false;
        }
      });

    
    builder
      .addCase(logoutUser.pending, (state) => {
        state.isLoading = true;
      })
      .addCase(logoutUser.fulfilled, (state) => {
        state.isLoading = false;
        state.user = null;
        state.isAuthenticated = false;
        state.error = null;
      });
  },
});

export const { clearError, updateUser, initializeAuth } = authSlice.actions;
export default authSlice.reducer;