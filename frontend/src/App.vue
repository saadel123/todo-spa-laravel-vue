<script setup lang="ts">
import { useAuthStore } from '@/stores/auth';// Import the authentication store to manage auth state

const authStore = useAuthStore();
</script>

<template>
  <v-app>
    <v-container>
      <v-app-bar color="primary" dark>
        <v-toolbar-title class="font-weight-bold text-h5">To-Do App</v-toolbar-title>
        <v-spacer></v-spacer>
        <v-btn v-if="authStore.isAuthenticated" @click="authStore.logout" color="white" variant="outlined"
          class="text-white" :loading="authStore.loggingOut">
          Logout
        </v-btn>
      </v-app-bar>

      <v-main>
        <router-view />
      </v-main>
    </v-container>
  </v-app>
</template>
