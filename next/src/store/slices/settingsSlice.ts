import { createSlice, createAsyncThunk, PayloadAction } from '@reduxjs/toolkit';
import { applicationSettingsService } from '@/services/applicationSettingsService';
import type { SimpleAllSettings, SystemStatus, SettingsFormData } from '@/types/settings';

interface SettingsState {
  settings: SimpleAllSettings | null;
  systemStatus: SystemStatus | null;
  isLoading: boolean;
  error: string | null;
}

const initialState: SettingsState = {
  settings: null,
  systemStatus: null,
  isLoading: false,
  error: null,
};


export const loadSettings = createAsyncThunk(
  'settings/loadSettings',
  async (_, { rejectWithValue }) => {
    try {
      const settings = await applicationSettingsService.getAllSettings();
      return settings;
    } catch {
      return rejectWithValue('Failed to load settings');
    }
  }
);

export const loadSystemStatus = createAsyncThunk(
  'settings/loadSystemStatus',
  async (_, { rejectWithValue }) => {
    try {
      const status = await applicationSettingsService.getSystemStatus();
      return status;
    } catch {
      return rejectWithValue('Failed to load system status');
    }
  }
);

export const updateUISettings = createAsyncThunk(
  'settings/updateUISettings',
  async (uiSettings: Partial<SettingsFormData>, { rejectWithValue }) => {
    try {
      await applicationSettingsService.updateUISettings(uiSettings);
      return { ui: uiSettings };
    } catch {
      return rejectWithValue('Failed to update UI settings');
    }
  }
);

export const updateCommerceSettings = createAsyncThunk(
  'settings/updateCommerceSettings',
  async (commerceSettings: Partial<SettingsFormData>, { rejectWithValue }) => {
    try {
      await applicationSettingsService.updateCommerceSettings(commerceSettings);
      return { commerce: commerceSettings };
    } catch {
      return rejectWithValue('Failed to update commerce settings');
    }
  }
);

export const updateSystemSettings = createAsyncThunk(
  'settings/updateSystemSettings',
  async (systemSettings: Partial<SettingsFormData>, { rejectWithValue }) => {
    try {
      await applicationSettingsService.updateSystemSettings(systemSettings);
      return { system: systemSettings };
    } catch {
      return rejectWithValue('Failed to update system settings');
    }
  }
);

export const updateLocalizationSettings = createAsyncThunk(
  'settings/updateLocalizationSettings',
  async (localizationSettings: Partial<SettingsFormData>, { rejectWithValue }) => {
    try {
      await applicationSettingsService.updateLocalizationSettings(localizationSettings);
      return { localization: localizationSettings };
    } catch {
      return rejectWithValue('Failed to update localization settings');
    }
  }
);

export const updateNotificationSettings = createAsyncThunk(
  'settings/updateNotificationSettings',
  async (notificationSettings: Partial<SettingsFormData>, { rejectWithValue }) => {
    try {
      await applicationSettingsService.updateNotificationSettings(notificationSettings);
      return { notification: notificationSettings };
    } catch {
      return rejectWithValue('Failed to update notification settings');
    }
  }
);


const settingsSlice = createSlice({
  name: 'settings',
  initialState,
  reducers: {
    clearError: (state) => {
      state.error = null;
    },
    updateLocalSettings: (state, action: PayloadAction<Partial<SimpleAllSettings>>) => {
      if (state.settings) {
        state.settings = { ...state.settings, ...action.payload };
      }
    },
  },
  extraReducers: (builder) => {
    
    builder
      .addCase(loadSettings.pending, (state) => {
        state.isLoading = true;
        state.error = null;
      })
      .addCase(loadSettings.fulfilled, (state, action) => {
        state.isLoading = false;
        state.settings = action.payload;
        state.error = null;
      })
      .addCase(loadSettings.rejected, (state, action) => {
        state.isLoading = false;
        state.error = action.payload as string;
      });

    
    builder
      .addCase(loadSystemStatus.fulfilled, (state, action) => {
        state.systemStatus = action.payload;
      });

    
    builder
      .addCase(updateUISettings.fulfilled, (state, action) => {
        if (state.settings && state.settings.ui) {
          Object.assign(state.settings.ui, action.payload.ui);
        }
      })
      .addCase(updateUISettings.rejected, (state, action) => {
        state.error = action.payload as string;
      });

    
    builder
      .addCase(updateCommerceSettings.fulfilled, (state, action) => {
        if (state.settings && state.settings.commerce) {
          Object.assign(state.settings.commerce, action.payload.commerce);
        }
      })
      .addCase(updateCommerceSettings.rejected, (state, action) => {
        state.error = action.payload as string;
      });

    
    builder
      .addCase(updateSystemSettings.fulfilled, (state, action) => {
        if (state.settings && state.settings.system) {
          Object.assign(state.settings.system, action.payload.system);
        }
      })
      .addCase(updateSystemSettings.rejected, (state, action) => {
        state.error = action.payload as string;
      });

    
    builder
      .addCase(updateLocalizationSettings.fulfilled, (state, action) => {
        if (state.settings && state.settings.localization) {
          Object.assign(state.settings.localization, action.payload.localization);
        }
      })
      .addCase(updateLocalizationSettings.rejected, (state, action) => {
        state.error = action.payload as string;
      });

    
    builder
      .addCase(updateNotificationSettings.fulfilled, (state, action) => {
        if (state.settings && state.settings.notification) {
          Object.assign(state.settings.notification, action.payload.notification);
        }
      })
      .addCase(updateNotificationSettings.rejected, (state, action) => {
        state.error = action.payload as string;
      });
  },
});

export const { clearError, updateLocalSettings } = settingsSlice.actions;
export default settingsSlice.reducer;