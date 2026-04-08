<template>
  <div class="h-screen flex overflow-hidden bg-gray-100">
    <!-- Sidebar -->
    <div class="flex flex-col w-64 bg-gray-800 text-white">
      <div class="h-16 flex items-center justify-center border-b border-gray-700 px-4">
        <span class="text-xl font-bold tracking-tight truncate">{{ settingsStore.site_title }}</span>
      </div>
      <div class="flex-1 overflow-y-auto pt-4 pb-4">
        <nav class="mt-2 px-2 space-y-1">
          <router-link :to="{ name: 'dashboard' }" class="group flex items-center px-2 py-2 text-sm font-medium rounded-md hover:bg-gray-700 hover:text-white" :class="{'bg-gray-900 text-white': $route.name === 'dashboard', 'text-gray-300': $route.name !== 'dashboard'}">
            <svg class="mr-3 h-6 w-6 text-gray-400 group-hover:text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
            </svg>
            Dashboard
          </router-link>
          
          <router-link v-if="hasPermission('manage_assets')" :to="{ name: 'media' }" class="group flex items-center px-2 py-2 text-sm font-medium rounded-md hover:bg-gray-700 hover:text-white" :class="{'bg-gray-900 text-white': $route.name === 'media', 'text-gray-300': $route.name !== 'media'}">
            <svg class="mr-3 h-6 w-6 text-gray-400 group-hover:text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
               <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
            </svg>
            Media Library
          </router-link>
          
          <router-link v-if="hasPermission('manage_pages')" :to="{ name: 'builder' }" class="group flex items-center px-2 py-2 text-sm font-medium rounded-md hover:bg-gray-700 hover:text-white" :class="{'bg-gray-900 text-white': $route.path.startsWith('/admin/builder'), 'text-gray-300': !$route.path.startsWith('/admin/builder')}">
            <svg class="mr-3 h-6 w-6 text-gray-400 group-hover:text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 002-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
            </svg>
            Page Builder
          </router-link>

          <router-link v-if="canPublish" :to="{ name: 'publish' }" class="group flex items-center px-2 py-2 text-sm font-medium rounded-md hover:bg-gray-700 hover:text-white" :class="{'bg-gray-900 text-white': $route.name === 'publish', 'text-gray-300': $route.name !== 'publish'}">
            <svg class="mr-3 h-6 w-6 text-gray-400 group-hover:text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18" />
            </svg>
            Publish
          </router-link>

          <router-link v-if="hasPermission('manage_users')" :to="{ name: 'users' }" class="group mt-4 flex items-center px-2 py-2 text-sm font-medium rounded-md hover:bg-gray-700 hover:text-white" :class="{'bg-gray-900 text-white': $route.name === 'users', 'text-gray-300': $route.name !== 'users'}">
            <svg class="mr-3 h-6 w-6 text-gray-400 group-hover:text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
            </svg>
            Users
          </router-link>
 
          <router-link v-if="hasPermission('manage_settings')" :to="{ name: 'settings' }" class="group flex items-center px-2 py-2 text-sm font-medium rounded-md hover:bg-gray-700 hover:text-white" :class="{'bg-gray-900 text-white': $route.name === 'settings', 'text-gray-300': $route.name !== 'settings'}">
            <svg class="mr-3 h-6 w-6 text-gray-400 group-hover:text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
            </svg>
            Settings
          </router-link>
 
          <router-link v-if="hasPermission('view_audit')" :to="{ name: 'audit' }" class="group flex items-center px-2 py-2 text-sm font-medium rounded-md hover:bg-gray-700 hover:text-white" :class="{'bg-gray-900 text-white': $route.name === 'audit', 'text-gray-300': $route.name !== 'audit'}">
            <svg class="mr-3 h-6 w-6 text-gray-400 group-hover:text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
            Audit Logs
          </router-link>
          <!-- Additional nav items to be added as features are built -->
        </nav>
      </div>
    </div>
 
    <!-- Main content area -->
    <div class="flex flex-col w-0 flex-1 overflow-hidden">
      <!-- Topbar -->
      <div class="relative z-10 flex-shrink-0 flex h-16 bg-white shadow justify-between items-center px-4 sm:px-6 lg:px-8">
        <div class="flex-1"></div>
        <div class="flex items-center space-x-4">
            <div class="text-sm font-medium text-gray-700">{{ authStore.user?.name }}</div>
            <router-link :to="{ name: 'profile' }" class="text-gray-400 hover:text-gray-500">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0zm6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </router-link>
            <button @click="logout" class="text-sm text-red-600 hover:text-red-500 font-medium cursor-pointer">
                Logout
            </button>
        </div>
      </div>
 
      <!-- Main view (child routes) -->
      <main class="flex-1 relative overflow-y-auto focus:outline-none">
        <router-view v-if="authStore.user"></router-view>
        <div v-else class="flex justify-center items-center h-full">
            <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-indigo-600"></div>
        </div>
      </main>
    </div>
  </div>
</template>
 
<script setup>
import { useAuthStore } from '../stores/auth';
import { useSettingsStore } from '../stores/settings';
import { useRouter } from 'vue-router';
import { useIdle } from '@vueuse/core';
import { computed, watch } from 'vue';

const authStore = useAuthStore();
const settingsStore = useSettingsStore();
const router = useRouter();

const hasPermission = (permission) => {
    const perms = authStore.user?.permissions;
    if (!Array.isArray(perms)) return false;
    return perms.includes(permission);
};

const canPublish = computed(() => hasPermission('publish_dark') || hasPermission('publish_live'));

// 15 minutes idle timer for auto logout (imported from prev Dashboard structure)
const { idle } = useIdle(900000);

watch(idle, (idleValue) => {
    if (idleValue && authStore.isAuthenticated) {
        logout();
    }
});

const logout = async () => {
    await authStore.logout();
    router.push({ name: 'login' });
};
</script>
