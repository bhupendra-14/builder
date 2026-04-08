<template>
  <div class="py-16 bg-gray-50">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
      <div v-if="editable || content.headline" class="text-center mb-12">
        <InlineText
          :model-value="content.headline || ''"
          :editable="editable"
          tag="h2"
          class="text-3xl font-extrabold text-gray-900"
          placeholder="Section headline"
          @update:model-value="(v) => patch({ headline: v })"
        />
      </div>

      <div class="space-y-4">
        <div v-for="(item, index) in (content.items || [])" :key="index" class="border border-gray-200 rounded-lg bg-white overflow-hidden shadow-sm">
          <div class="w-full flex justify-between items-center px-6 py-4 text-left" :class="!editable && openIndex === index ? 'bg-indigo-50' : ''">
            <button v-if="!editable" @click="toggle(index)" class="flex-1 text-left focus:outline-none flex justify-between items-center">
              <span class="text-lg font-medium text-gray-900">{{ item.title }}</span>
              <svg class="h-5 w-5 text-gray-500 transform transition-transform" :class="openIndex === index ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
              </svg>
            </button>
            <InlineText
              v-else
              :model-value="item.title || ''"
              editable
              tag="span"
              class="text-lg font-medium text-gray-900 flex-1"
              placeholder="Question"
              @update:model-value="(v) => updateItem(index, { title: v })"
            />
            <button
              v-if="editable"
              type="button"
              @click="removeItem(index)"
              class="ml-2 text-gray-400 hover:text-red-500 text-xl"
              aria-label="Remove"
            >&times;</button>
          </div>
          <div v-if="editable || openIndex === index" class="px-6 py-4 border-t border-gray-100">
            <InlineRichText
              v-if="editable"
              :model-value="item.content || ''"
              editable
              @update:model-value="(v) => updateItem(index, { content: v })"
            />
            <div v-else class="prose prose-indigo max-w-none" v-html="item.content"></div>
          </div>
        </div>
        <button
          v-if="editable"
          type="button"
          @click="addItem"
          class="w-full py-3 border-2 border-dashed border-indigo-300 rounded-lg text-sm text-indigo-600 hover:bg-indigo-50"
        >+ Add entry</button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import InlineText from '../inline/InlineText.vue';
import InlineRichText from '../inline/InlineRichText.vue';

const props = defineProps({
  content: { type: Object, default: () => ({ items: [] }) },
  editable: { type: Boolean, default: false },
});

const emit = defineEmits(['update:content', 'pick-asset']);

const openIndex = ref(null);
const toggle = (index) => { openIndex.value = openIndex.value === index ? null : index; };

const patch = (obj) => emit('update:content', { ...props.content, ...obj });

const updateItem = (idx, patchObj) => {
  const items = [...(props.content.items || [])];
  items[idx] = { ...items[idx], ...patchObj };
  patch({ items });
};

const addItem = () => {
  const items = [...(props.content.items || []), { title: 'New question', content: '' }];
  patch({ items });
};

const removeItem = (idx) => {
  const items = [...(props.content.items || [])];
  items.splice(idx, 1);
  patch({ items });
};
</script>
