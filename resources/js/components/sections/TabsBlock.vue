<template>
  <div class="py-16 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div v-if="editable || content.headline" class="text-center mb-12">
        <InlineText
          :model-value="content.headline || ''"
          :editable="editable"
          tag="h2"
          class="text-3xl font-extrabold text-gray-900 sm:text-4xl"
          placeholder="Section headline"
          @update:model-value="(v) => patch({ headline: v })"
        />
      </div>

      <!-- Mobile select (view mode only) -->
      <div v-if="!editable && content.items?.length" class="sm:hidden mb-4">
        <label for="tab-select" class="sr-only">Select a tab</label>
        <select id="tab-select" v-model="activeTab" class="block w-full border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500">
          <option v-for="(item, index) in content.items" :key="index" :value="index">{{ item.title }}</option>
        </select>
      </div>

      <div class="border-b border-gray-200" :class="!editable ? 'hidden sm:block' : ''">
        <nav class="-mb-px flex space-x-8 justify-center flex-wrap" aria-label="Tabs">
          <div
            v-for="(item, index) in (content.items || [])"
            :key="index"
            class="relative"
          >
            <button
              v-if="!editable"
              @click="activeTab = index"
              class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors"
              :class="activeTab === index ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
            >
              {{ item.title }}
            </button>
            <div v-else class="flex items-center py-4 px-1 border-b-2" :class="activeTab === index ? 'border-indigo-500' : 'border-transparent'">
              <InlineText
                :model-value="item.title || ''"
                editable
                tag="span"
                class="font-medium text-sm text-gray-700 mr-1"
                placeholder="Tab"
                @update:model-value="(v) => updateItem(index, { title: v })"
              />
              <button type="button" @click="activeTab = index" class="text-xs text-indigo-600">edit</button>
              <button type="button" @click="removeItem(index)" class="ml-1 text-gray-400 hover:text-red-500">&times;</button>
            </div>
          </div>
          <button
            v-if="editable"
            type="button"
            @click="addItem"
            class="py-4 px-3 text-sm font-medium text-indigo-600 hover:text-indigo-900"
          >+ Add tab</button>
        </nav>
      </div>

      <div class="mt-8">
        <template v-if="content.items?.[activeTab]">
          <InlineRichText
            v-if="editable"
            :model-value="content.items[activeTab].content || ''"
            editable
            @update:model-value="(v) => updateItem(activeTab, { content: v })"
          />
          <div v-else class="prose prose-indigo max-w-none" v-html="content.items[activeTab].content"></div>
        </template>
        <div v-else class="text-center text-gray-400 italic py-10">Select a tab to view content</div>
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

const activeTab = ref(0);

const patch = (obj) => emit('update:content', { ...props.content, ...obj });

const updateItem = (idx, patchObj) => {
  const items = [...(props.content.items || [])];
  items[idx] = { ...items[idx], ...patchObj };
  patch({ items });
};

const addItem = () => {
  const items = [...(props.content.items || []), { title: 'New tab', content: '' }];
  patch({ items });
  activeTab.value = items.length - 1;
};

const removeItem = (idx) => {
  const items = [...(props.content.items || [])];
  items.splice(idx, 1);
  patch({ items });
  if (activeTab.value >= items.length) activeTab.value = Math.max(0, items.length - 1);
};
</script>
