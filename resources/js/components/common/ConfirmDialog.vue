<template>
  <transition name="fade">
    <div v-if="confirm.open" class="fixed inset-0 z-[110] overflow-y-auto" role="dialog" aria-modal="true">
      <div class="fixed inset-0 bg-gray-500/75" @click="confirm.cancel()"></div>
      <div class="relative z-10 flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-lg shadow-xl max-w-md w-full overflow-hidden">
          <div class="p-6">
            <div class="flex items-start gap-3">
              <div :class="iconBgClass">
                <svg class="h-6 w-6" :class="iconClass" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path v-if="confirm.variant === 'danger'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M5.07 19h13.86a2 2 0 001.74-3L13.74 4a2 2 0 00-3.48 0L3.33 16a2 2 0 001.74 3z" />
                  <path v-else stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
              </div>
              <div class="flex-1">
                <h3 class="text-lg font-semibold text-gray-900">{{ confirm.title }}</h3>
                <p v-if="confirm.message" class="mt-2 text-sm text-gray-600">{{ confirm.message }}</p>
              </div>
            </div>
          </div>
          <div class="bg-gray-50 px-6 py-3 flex flex-row-reverse gap-3">
            <button
              type="button"
              @click="confirm.accept()"
              class="inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 text-sm font-medium text-white"
              :class="confirmButtonClass"
            >
              {{ confirm.confirmLabel }}
            </button>
            <button
              type="button"
              @click="confirm.cancel()"
              class="inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50"
            >
              {{ confirm.cancelLabel }}
            </button>
          </div>
        </div>
      </div>
    </div>
  </transition>
</template>

<script setup>
import { computed } from 'vue';
import { useConfirm } from '../../stores/confirm';

const confirm = useConfirm();

const iconBgClass = computed(() =>
  confirm.variant === 'danger'
    ? 'flex-shrink-0 h-10 w-10 rounded-full bg-red-100 flex items-center justify-center'
    : 'flex-shrink-0 h-10 w-10 rounded-full bg-indigo-100 flex items-center justify-center'
);

const iconClass = computed(() =>
  confirm.variant === 'danger' ? 'text-red-600' : 'text-indigo-600'
);

const confirmButtonClass = computed(() =>
  confirm.variant === 'danger'
    ? 'bg-red-600 hover:bg-red-700'
    : 'bg-indigo-600 hover:bg-indigo-700'
);
</script>

<style scoped>
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.15s ease;
}
.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
</style>
