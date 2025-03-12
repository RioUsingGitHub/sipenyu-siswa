import { create } from 'zustand';
import { User } from '../Contexts/AuthContext';
import axios from 'axios';

interface AuthState {
  user: User | null;
  isLoading: boolean;
  isAuthenticated: boolean;
  
  // Actions
  login: (email: string, password: string) => Promise<void>;
  logout: () => Promise<void>;
  checkAuth: () => Promise<void>;
  setUser: (user: User | null) => void;
}

export const useAuthStore = create<AuthState>((set) => ({
  user: null,
  isLoading: true,
  isAuthenticated: false,
  
  setUser: (user: User | null) => set({ 
    user, 
    isAuthenticated: !!user 
  }),
  
  checkAuth: async () => {
    try {
      set({ isLoading: true });
      const response = await axios.get('/api/user');
      set({ user: response.data, isAuthenticated: true, isLoading: false });
    } catch (error) {
      set({ user: null, isAuthenticated: false, isLoading: false });
    }
  },
  
  login: async (email: string, password: string) => {
    try {
      set({ isLoading: true });
      await axios.post('/api/login', { email, password });
      const response = await axios.get('/api/user');
      set({ user: response.data, isAuthenticated: true, isLoading: false });
    } catch (error) {
      set({ isLoading: false });
      throw new Error('Login failed');
    }
  },
  
  logout: async () => {
    try {
      await axios.post('/api/logout');
      set({ user: null, isAuthenticated: false });
    } catch (error) {
      console.error('Logout error:', error);
    }
  },
}));