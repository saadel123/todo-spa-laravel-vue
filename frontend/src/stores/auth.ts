import { defineStore } from 'pinia';
import { ref, computed } from 'vue';
import api from '../api';
import { useRouter } from 'vue-router';

export const useAuthStore = defineStore('auth', () => {
  const token = ref(localStorage.getItem('token')); // Store token from localStorage
  const router = useRouter();

  // Check if the user is authenticated based on token
  const isAuthenticated = computed(() => !!token.value);

  // Login the user and store token
  const login = async (email: string, password: string) => {
    try {
      const response = await api.post('/login', { email, password });
      token.value = response.data.token; // Store token in state
      localStorage.setItem('token', token.value); // Store token in localStorage
      router.push('/todos');
      return true;
    } catch (error) {
      return false;
    }
  };

  // Logout the user and clear token
  const logout = async () => {
    try {
      if (token.value) await api.post('/logout');
    } catch (error) {
      console.error('Logout error:', error);
    } finally {
      token.value = null; // Clear token in state
      localStorage.removeItem('token');
      router.push('/');
    }
  };

  return { token, isAuthenticated, login, logout };
});
