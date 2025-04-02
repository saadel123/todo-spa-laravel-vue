import axios from 'axios';
import { useAuthStore } from '@/stores/auth';

// Create an Axios instance with a base URL for API requests
const api = axios.create({
  baseURL: '/api',
});

// Request interceptor to attach the authentication token (if available)
api.interceptors.request.use((config) => {
  const authStore = useAuthStore();
  if (authStore.token) {
    config.headers.Authorization = `Bearer ${authStore.token}`;
  }
  return config;
});

// Response interceptor to handle authentication errors (e.g., expired token)
api.interceptors.response.use(
  (response) => response,
  (error) => {
    if (error.response?.status === 401) { // If unauthorized (token expired or invalid)
      const authStore = useAuthStore();
      authStore.logout(); // Automatically log out the user
    }
    return Promise.reject(error);
  }
);

export default api;
