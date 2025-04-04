<script setup lang="ts">
import { ref } from 'vue';
import { useAuthStore } from '@/stores/auth';

const authStore = useAuthStore();

// Reactive variables to store user input and state
const email = ref('');
const password = ref('');
const errorMessage = ref('');
const isLoading = ref(false);
const showPassword = ref(false); // For password visibility toggle

// Handles the login process
const login = async () => {
  isLoading.value = true; // Disable inputs and show loading state

  // Trim inputs to avoid spaces being counted as valid
  const trimmedEmail = email.value.trim();
  const trimmedPassword = password.value.trim();

  // Validate empty fields
  if (!trimmedEmail || !trimmedPassword) {
    errorMessage.value = 'Email and password are required.';
    isLoading.value = false;
    return;
  }
  errorMessage.value = '';

  try {
    const success = await authStore.login(email.value, password.value);
    if (!success) {
      errorMessage.value = 'Invalid credentials';
    }
  } finally {
    isLoading.value = false;
  }
};
</script>

<template>
  <v-container class="fill-height d-flex align-center justify-center">
    <v-card class="mx-auto auth-card pa-4" width="500" elevation="4">
      <v-card-title class="text-center py-8">
        <v-icon size="48" color="primary account-circle" class="mr-3">mdi-account-circle</v-icon>
        <span class="text-h3 font-weight-bold primary--text">Welcome Back</span>
      </v-card-title>

      <v-card-subtitle class="text-center text-h6 text-medium-emphasis mt-2">
        Sign in to access your tasks
      </v-card-subtitle>

      <v-card-text class="px-6 pb-6">
        <!-- Email input field -->
        <v-text-field v-model="email" label="Email Address" type="email" :disabled="isLoading"
          prepend-inner-icon="mdi-email-outline" variant="outlined" density="comfortable" class="mb-6" autofocus
          single-line height="60"></v-text-field>

        <!-- Password input field -->
        <v-text-field v-model="password" :label="showPassword ? 'Password (visible)' : 'Password'"
          :type="showPassword ? 'text' : 'password'" :disabled="isLoading" prepend-inner-icon="mdi-lock-outline"
          variant="outlined" density="comfortable" :append-inner-icon="showPassword ? 'mdi-eye-off' : 'mdi-eye'"
          @click:append-inner="showPassword = !showPassword" class="mb-2" single-line height="60"></v-text-field>

        <!-- Display an error message if login fails -->
        <v-alert v-if="errorMessage" type="error" variant="tonal" class="mb-6" icon="mdi-alert-circle-outline">
          {{ errorMessage }}
        </v-alert>

        <v-btn block @click="login" color="primary" :loading="isLoading" :disabled="isLoading" size="x-large"
          class="mt-4" append-icon="mdi-arrow-right" height="56">
          Sign In
        </v-btn>
      </v-card-text>
    </v-card>
  </v-container>
</template>

<style scoped>
.auth-card {
  border-radius: 8px;
  overflow: hidden;
  transition: transform 0.3s ease;
}

.v-text-field {
  border-radius: 6px;
  font-size: 1.1rem;
}

.v-btn {
  letter-spacing: 0.5px;
  font-weight: 600;
  font-size: 1.1rem;
  text-transform: none;
  transition: all 0.3s ease;
}

.v-btn:hover {
  transform: translateY(-1px);
}

.v-alert {
  border-left: 4px solid rgb(var(--v-theme-error));
  font-size: 1rem;
}

.account-circle {
  margin-top: -23px;
}
</style>