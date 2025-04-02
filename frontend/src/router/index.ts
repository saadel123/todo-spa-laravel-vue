import { createRouter, createWebHistory } from 'vue-router';
import Login from '../views/Login.vue';
import TodoList from '../views/TodoList.vue';
import { useAuthStore } from '@/stores/auth';

// Define the application's routes
const routes = [
  { path: '/', component: Login }, // Default route for login page
  {
    path: '/todos',
    component: TodoList,
    meta: { requiresAuth: true }
  },
];

// Create a new Vue Router instance with history mode enabled
const router = createRouter({
  history: createWebHistory(),
  routes,
});

// Global navigation guard to protect authenticated routes
router.beforeEach((to) => {
  const authStore = useAuthStore();
  if (to.meta.requiresAuth && !authStore.isAuthenticated) {
    return '/'; // Redirect to login page if the user is not authenticated
  }
});

export default router;
