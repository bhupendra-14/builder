<template>
  <div class="fixed top-4 right-4 z-[100] flex flex-col gap-2 max-w-sm w-full pointer-events-none">
    <transition-group name="toast" tag="div" class="flex flex-col gap-2">
      <div
        v-for="t in toast.items"
        :key="t.id"
        class="pointer-events-auto rounded-lg shadow-lg overflow-hidden border-l-4 bg-white flex items-start gap-3 p-4"
        :class="borderClass(t.type)"
        role="status"
      >
        <div class="flex-shrink-0 mt-0.5" :class="iconClass(t.type)">
          <svg v-if="t.type === 'success'" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
          <svg v-else-if="t.type === 'error'" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
          <svg v-else-if="t.type === 'warning'" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M5.07 19h13.86a2 2 0 001.74-3L13.74 4a2 2 0 00-3.48 0L3.33 16a2 2 0 001.74 3z" /></svg>
          <svg v-else class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
        </div>
        <div class="flex-1 min-w-0">
          <p v-if="t.title" class="text-sm font-semibold text-gray-900">{{ t.title }}</p>
          <p class="text-sm text-gray-700" :class="t.title ? 'mt-0.5' : ''">{{ t.message }}</p>
        </div>
        <button
          type="button"
          class="flex-shrink-0 text-gray-400 hover:text-gray-600"
          @click="toast.dismiss(t.id)"
          aria-label="Dismiss"
        >
          <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
        </button>
      </div>
    </transition-group>
  </div>
</template>

<script setup>
import { useToast } from '../../stores/toast';

const toast = useToast();

const borderClass = (type) => {
  switch (type) {
    case 'success': return 'border-green-500';
    case 'error':   return 'border-red-500';
    case 'warning': return 'border-amber-500';
    default:        return 'border-indigo-500';
  }
};

const iconClass = (type) => {
  switch (type) {
    case 'success': return 'text-green-500';
    case 'error':   return 'text-red-500';
    case 'warning': return 'text-amber-500';
    default:        return 'text-indigo-500';
  }
};
</script>

<style scoped>
.toast-enter-active,
.toast-leave-active {
  transition: all 0.25s ease;
}
.toast-enter-from {
  opacity: 0;
  transform: translateX(20px);
}
.toast-leave-to {
  opacity: 0;
  transform: translateX(20px);
}
</style>
