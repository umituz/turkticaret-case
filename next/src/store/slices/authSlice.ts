import { createSlice } from '@reduxjs/toolkit';

interface AuthState {
  // Minimal state for compatibility - NextAuth handles actual authentication
  placeholder?: boolean;
}

const initialState: AuthState = {
  placeholder: true,
};

// Minimal auth slice for compatibility - NextAuth handles all authentication
const authSlice = createSlice({
  name: 'auth',
  initialState,
  reducers: {},
});

export default authSlice.reducer;