<script setup lang="ts">
import { ref } from 'vue';
import { useAuthStore } from '@/stores/auth';

const authStore = useAuthStore();

// Reactive variables to store user input and state
const email = ref('');
const password = ref('');
const errorMessage = ref('');
const isLoading = ref(false);

// Handles the login process
const login = async () => {
  isLoading.value = true; // Disable inputs and show loading state
  errorMessage.value = ''; // Clear any previous errors

  try {
    const success = await authStore.login(email.value, password.value);
    if (!success) {
      errorMessage.value = 'Invalid credentials'; // Show error if login fails
    }
  } finally {
    isLoading.value = false;
  }
};
</script>

<template>
  <v-container>
    <v-card class="mx-auto" max-width="400">
      <v-card-title>Login</v-card-title>
      <v-card-text>
        <!-- Email input field -->
        <v-text-field label="Email" v-model="email" type="email" :disabled="isLoading"></v-text-field>

        <!-- Password input field -->
        <v-text-field label="Password" v-model="password" type="password" :disabled="isLoading"></v-text-field>

        <!-- Display an error message if login fails -->
        <v-alert v-if="errorMessage" type="error">{{ errorMessage }}</v-alert>
      </v-card-text>
      <v-card-actions>
        <v-btn @click="login" color="primary" :loading="isLoading" :disabled="isLoading">
          Login
        </v-btn>
      </v-card-actions>
    </v-card>
  </v-container>
</template>
