<template>
  <div class="py-6 px-4 sm:px-6 lg:px-8">
    <div class="mb-6 md:flex md:items-center md:justify-between">
      <div class="flex-1 min-w-0">
        <h2 class="text-2xl font-bold leading-7 text-gray-900 sm:text-3xl sm:truncate">
          Dashboard Overview
        </h2>
      </div>
      <div class="mt-4 flex md:mt-0 md:ml-4 space-x-3">
        <!-- Quick Actions -->
        <router-link :to="{ name: 'dashboard' }" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
          Create Page
        </router-link>
        <router-link :to="{ name: 'media' }" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
          Upload Asset
        </router-link>
      </div>
    </div>

    <!-- Stats -->
    <div v-if="stats" class="mt-4 grid grid-cols-1 gap-5 sm:grid-cols-3">
      <div class="bg-white overflow-hidden shadow rounded-lg">
        <div class="px-4 py-5 sm:p-6">
          <dt class="text-sm font-medium text-gray-500 truncate">Total Users</dt>
          <dd class="mt-1 text-3xl font-semibold text-gray-900">{{ stats.total_users }}</dd>
        </div>
      </div>
      <div class="bg-white overflow-hidden shadow rounded-lg">
        <div class="px-4 py-5 sm:p-6">
          <dt class="text-sm font-medium text-gray-500 truncate">Pages / Sections</dt>
          <dd class="mt-1 text-3xl font-semibold text-gray-900">{{ stats.total_sections }}</dd>
        </div>
      </div>
      <div class="bg-white overflow-hidden shadow rounded-lg">
        <div class="px-4 py-5 sm:p-6">
          <dt class="text-sm font-medium text-gray-500 truncate">Media Assets</dt>
          <dd class="mt-1 text-3xl font-semibold text-gray-900">{{ stats.total_assets }}</dd>
        </div>
      </div>
    </div>

    <!-- Recent Activity -->
    <h3 class="mt-8 text-lg leading-6 font-medium text-gray-900">Recent Activity</h3>
    <div class="mt-4 shadow overflow-hidden border-b border-gray-200 sm:rounded-lg">
      <table class="min-w-full divide-y divide-gray-200 bg-white">
        <thead class="bg-gray-50">
          <tr>
            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Target</th>
            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
          </tr>
        </thead>
        <tbody>
          <tr v-if="loading">
            <td colspan="4" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">Loading...</td>
          </tr>
          <tr v-else-if="activities.length === 0">
            <td colspan="4" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">No recent activity found.</td>
          </tr>
          <tr v-else v-for="(activity, index) in activities" :key="activity.id" :class="index % 2 === 0 ? 'bg-white' : 'bg-gray-50'">
            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ activity.user_name || 'System' }}</td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                    {{ activity.action }}
                </span>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ activity.target_type }} #{{ activity.target_id }}</td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ new Date(activity.created_at).toLocaleString() }}</td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from '../axios';

const stats = ref(null);
const activities = ref([]);
const loading = ref(true);

onMounted(async () => {
    try {
        const response = await axios.get('/dashboard');
        stats.value = response.data.data.stats;
        activities.value = response.data.data.activities;
    } catch (err) {
        console.error('Failed to load dashboard data');
    } finally {
        loading.value = false;
    }
});
</script>
