<template>
  <router-view />
  <ToastContainer />
  <ConfirmDialog />
</template>

<script setup>
import { onMounted } from 'vue';
import { useSettingsStore } from './stores/settings';
import ToastContainer from './components/common/ToastContainer.vue';
import ConfirmDialog from './components/common/ConfirmDialog.vue';

const settingsStore = useSettingsStore();

onMounted(() => {
  // Load public site settings once on app boot. They drive document.title,
  // meta description, brand color CSS var, sidebar/login branding, etc.
  if (!settingsStore.loaded) settingsStore.fetchPublic();
});
</script>
