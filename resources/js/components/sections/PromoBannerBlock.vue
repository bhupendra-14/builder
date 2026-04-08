<template>
  <div v-if="visible || editable">
    <div class="relative" :style="{ backgroundColor: content.background_color || '#4f46e5' }">
      <div class="max-w-7xl mx-auto py-3 px-3 sm:px-6 lg:px-8">
        <div :class="content.dismissible && !editable ? 'pr-10 sm:text-center sm:px-16' : 'sm:text-center sm:px-4'">
          <!-- View mode: render as a real link when content.link is set -->
          <a
            v-if="!editable && content.link"
            :href="content.link"
            class="font-medium text-white inline-flex flex-wrap items-center justify-center gap-x-2 gap-y-1 hover:opacity-90"
          >
            <span v-if="content.message">{{ content.message }}</span>
            <span v-if="content.link_label" class="font-bold underline">
              {{ content.link_label }} <span aria-hidden="true">&rarr;</span>
            </span>
          </a>

          <!-- View mode without a link: plain text -->
          <p
            v-else-if="!editable"
            class="font-medium text-white flex flex-wrap items-center justify-center gap-x-2 gap-y-1"
          >
            <span v-if="content.message">{{ content.message }}</span>
            <span v-if="content.link_label" class="font-bold underline">
              {{ content.link_label }} <span aria-hidden="true">&rarr;</span>
            </span>
          </p>

          <!-- Edit mode: inline-editable text (URL is edited in the dark bar below) -->
          <p v-else class="font-medium text-white flex flex-wrap items-center justify-center gap-x-2 gap-y-1">
            <InlineText
              :model-value="content.message || ''"
              editable
              tag="span"
              class="text-white"
              placeholder="Promo message"
              @update:model-value="(v) => patch({ message: v })"
            />
            <span>
              <InlineText
                :model-value="content.link_label || ''"
                editable
                tag="span"
                class="text-white font-bold underline"
                placeholder="Learn more"
                @update:model-value="(v) => patch({ link_label: v })"
              /> <span aria-hidden="true">&rarr;</span>
            </span>
          </p>
        </div>
        <div v-if="content.dismissible && !editable" class="absolute inset-y-0 right-0 pr-1 sm:pr-2 flex items-center">
          <button @click="visible = false" type="button" class="flex p-2 rounded-md hover:bg-white/10 focus:outline-none">
            <span class="sr-only">Dismiss</span>
            <svg class="h-5 w-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>
      </div>
    </div>

    <!-- Edit controls row — only in edit mode, placed below the banner -->
    <div v-if="editable" class="bg-gray-900 text-white text-xs px-4 py-2 flex flex-wrap items-center gap-4">
      <div class="flex items-center gap-2">
        <span class="text-gray-400 uppercase tracking-wide">Link URL</span>
        <InlineText
          :model-value="content.link || ''"
          editable
          tag="span"
          class="bg-gray-800 px-2 py-1 rounded min-w-[12rem] inline-block"
          placeholder="https://..."
          @update:model-value="(v) => patch({ link: v })"
        />
      </div>
      <label class="flex items-center gap-1 cursor-pointer">
        <input type="checkbox" :checked="content.dismissible" @change="patch({ dismissible: $event.target.checked })" class="rounded">
        Dismissible
      </label>
      <label class="flex items-center gap-2">
        <span class="text-gray-400 uppercase tracking-wide">Color</span>
        <input type="color" :value="content.background_color || '#4f46e5'" @input="patch({ background_color: $event.target.value })" class="h-6 w-10 border-0 bg-transparent p-0 cursor-pointer">
      </label>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import InlineText from '../inline/InlineText.vue';

const props = defineProps({
  content: { type: Object, default: () => ({}) },
  editable: { type: Boolean, default: false },
});

const emit = defineEmits(['update:content', 'pick-asset']);

const patch = (obj) => emit('update:content', { ...props.content, ...obj });

const visible = ref(true);
</script>
