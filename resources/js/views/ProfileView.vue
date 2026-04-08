<template>
  <div class="py-6 px-4 sm:px-6 lg:px-8">
    <h1 class="text-2xl font-semibold text-gray-900 mb-6">User Profile</h1>

    <div class="bg-white shadow px-4 py-5 sm:rounded-lg sm:p-6 mb-6">
      <div class="md:grid md:grid-cols-3 md:gap-6">
        <div class="md:col-span-1">
          <h3 class="text-lg font-medium leading-6 text-gray-900">Personal Information</h3>
          <p class="mt-1 text-sm text-gray-500">Update your account's profile information and email address.</p>
        </div>
        <div class="mt-5 md:mt-0 md:col-span-2">
          <form @submit.prevent="updateProfile">
            <div class="grid grid-cols-6 gap-6">
              <div class="col-span-6 sm:col-span-4">
                <label for="name" class="block text-sm font-medium text-gray-700">Name</label>
                <input type="text" name="name" id="name" v-model="form.name" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
              </div>

              <div class="col-span-6 sm:col-span-4">
                <label for="email" class="block text-sm font-medium text-gray-700">Email address</label>
                <input type="email" name="email" id="email" v-model="form.email" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
              </div>
              
              <div class="col-span-6 sm:col-span-4">
                <label for="password" class="block text-sm font-medium text-gray-700">New Password (optional)</label>
                <input type="password" name="password" id="password" v-model="form.password" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
              </div>
              
              <div class="col-span-6 sm:col-span-4">
                <label for="password_confirmation" class="block text-sm font-medium text-gray-700">Confirm Password</label>
                <input type="password" name="password_confirmation" id="password_confirmation" v-model="form.password_confirmation" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
              </div>
            </div>
            
            <p v-if="error" class="mt-2 text-sm text-red-600">{{ error }}</p>
            <p v-if="successMsg" class="mt-2 text-sm text-green-600">{{ successMsg }}</p>

            <div class="mt-6 flex justify-end">
              <button type="submit" :disabled="loading" class="ml-3 inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:bg-indigo-400">
                {{ loading ? 'Saving...' : 'Save' }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
    
    <div class="bg-white shadow px-4 py-5 sm:rounded-lg sm:p-6">
      <div class="md:grid md:grid-cols-3 md:gap-6">
        <div class="md:col-span-1">
          <h3 class="text-lg font-medium leading-6 text-gray-900">Avatar</h3>
          <p class="mt-1 text-sm text-gray-500">Change your profile picture.</p>
        </div>
        <div class="mt-5 md:mt-0 md:col-span-2">
            <input type="file" @change="uploadAvatar" accept="image/*" class="mb-4" />
            <p v-if="avatarMsg" class="text-sm text-green-600">{{ avatarMsg }}</p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useAuthStore } from '../stores/auth';
import axios from '../axios';

const authStore = useAuthStore();

const form = ref({
    name: '',
    email: '',
    password: '',
    password_confirmation: ''
});

const loading = ref(false);
const error = ref('');
const successMsg = ref('');
const avatarMsg = ref('');

onMounted(() => {
    if (authStore.user) {
        form.value.name = authStore.user.name;
        form.value.email = authStore.user.email;
    }
});

const updateProfile = async () => {
    loading.value = true;
    error.value = '';
    successMsg.value = '';
    
    try {
        const response = await axios.put('/profile', form.value);
        authStore.user.name = form.value.name;
        authStore.user.email = form.value.email;
        successMsg.value = 'Profile updated successfully.';
        form.value.password = '';
        form.value.password_confirmation = '';
    } catch (err) {
        error.value = err.response?.data?.message || err.response?.data?.errors?.[0] || 'Error updating profile';
    } finally {
        loading.value = false;
    }
};

const uploadAvatar = async (event) => {
    const file = event.target.files[0];
    if (!file) return;
    
    avatarMsg.value = 'Uploading...';
    
    const formData = new FormData();
    formData.append('avatar', file);
    
    try {
        await axios.post('/profile/avatar', formData, {
            headers: { 'Content-Type': 'multipart/form-data' }
        });
        avatarMsg.value = 'Avatar updated successfully!';
    } catch (err) {
        avatarMsg.value = err.response?.data?.message || err.response?.data?.errors?.[0] || 'Upload failed';
    }
};
</script>
