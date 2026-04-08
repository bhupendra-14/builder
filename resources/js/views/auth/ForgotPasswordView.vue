<template>
  <div class="min-h-screen flex items-center justify-center bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8 bg-white p-8 rounded-lg shadow-md">
      <div>
        <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">Reset Password</h2>
      </div>
      <form v-if="!success" class="mt-8 space-y-6" @submit.prevent="handleForgot">
        <div class="rounded-md shadow-sm -space-y-px">
          <div>
            <label for="email-address" class="sr-only">Email address</label>
            <input id="email-address" name="email" type="email" v-model="email" required class="appearance-none rounded-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 focus:z-10 sm:text-sm" placeholder="Email address">
          </div>
        </div>

        <div>
          <button type="submit" :disabled="loading" class="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50">
            {{ loading ? 'Sending...' : 'Send Reset Link' }}
          </button>
        </div>
        
        <p v-if="error" class="text-red-500 text-sm mt-2 text-center">{{ error }}</p>
      </form>
      <div v-else class="mt-8 space-y-6 text-center text-green-600">
        Password reset link has been sent to your email!
      </div>
      <div class="text-center mt-4">
        <router-link :to="{ name: 'login' }" class="text-sm font-medium text-indigo-600 hover:text-indigo-500">Back to Login</router-link>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import axios from '../../axios';

const email = ref('');
const loading = ref(false);
const error = ref('');
const success = ref(false);

const handleForgot = async () => {
    loading.value = true;
    error.value = '';
    success.value = false;
    
    try {
        await axios.post('/forgot-password', { email: email.value });
        success.value = true;
    } catch (err) {
        error.value = err.response?.data?.message || err.response?.data?.errors?.[0] || 'Error sending link';
    } finally {
        loading.value = false;
    }
};
</script>
