<template>
  <div class="bg-white py-16 sm:py-24">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="text-center max-w-3xl mx-auto">
        <InlineText
          :model-value="content.eyebrow || ''"
          :editable="editable"
          tag="h2"
          class="text-base font-semibold text-indigo-600 tracking-wide uppercase"
          placeholder="Eyebrow"
          @update:model-value="(v) => patch({ eyebrow: v })"
        />
        <InlineText
          :model-value="content.headline || ''"
          :editable="editable"
          tag="p"
          class="mt-2 text-3xl font-extrabold text-gray-900 sm:text-4xl"
          placeholder="Section headline"
          @update:model-value="(v) => patch({ headline: v })"
        />
        <InlineText
          :model-value="content.subheadline || ''"
          :editable="editable"
          tag="p"
          multiline
          class="mt-4 text-lg text-gray-500"
          placeholder="Subheadline"
          @update:model-value="(v) => patch({ subheadline: v })"
        />
      </div>

      <div class="mt-12 grid gap-8 sm:grid-cols-2 lg:grid-cols-3">
        <div v-for="(item, idx) in (content.items || [])" :key="idx" class="relative">
          <button
            v-if="editable"
            type="button"
            @click="removeItem(idx)"
            class="absolute -top-2 -right-2 bg-red-600 text-white rounded-full w-6 h-6 text-xs hover:bg-red-700 z-10"
          >&times;</button>
          <div v-if="editable || item.icon" class="h-12 w-12 mb-4">
            <InlineImage
              :asset="item.icon"
              :editable="editable"
              img-class="h-12 w-12 object-contain rounded-md bg-indigo-500 p-2"
              placeholder="Icon"
              @pick="$emit('pick-asset', `items[${idx}].icon`)"
              @remove="updateItem(idx, { icon: null })"
            />
          </div>
          <InlineText
            :model-value="item.title || ''"
            :editable="editable"
            tag="h3"
            class="text-lg font-medium text-gray-900"
            placeholder="Feature title"
            @update:model-value="(v) => updateItem(idx, { title: v })"
          />
          <InlineText
            :model-value="item.description || ''"
            :editable="editable"
            tag="p"
            multiline
            class="mt-2 text-base text-gray-500"
            placeholder="Short description"
            @update:model-value="(v) => updateItem(idx, { description: v })"
          />
        </div>
        <button
          v-if="editable"
          type="button"
          @click="addItem"
          class="flex items-center justify-center p-8 border-2 border-dashed border-indigo-300 rounded-lg text-sm text-indigo-600 hover:bg-indigo-50"
        >+ Add feature</button>
      </div>
      <div v-if="!editable && (!content.items || content.items.length === 0)" class="mt-12 text-center py-10 text-gray-400 border-2 border-dashed border-gray-200 rounded-lg">
        No features added yet.
      </div>
    </div>
  </div>
</template>

<script setup>
import InlineText from '../inline/InlineText.vue';
import InlineImage from '../inline/InlineImage.vue';

const props = defineProps({
  content: { type: Object, default: () => ({ items: [] }) },
  editable: { type: Boolean, default: false },
});

const emit = defineEmits(['update:content', 'pick-asset']);

const patch = (obj) => emit('update:content', { ...props.content, ...obj });

const updateItem = (idx, patchObj) => {
  const items = [...(props.content.items || [])];
  items[idx] = { ...items[idx], ...patchObj };
  patch({ items });
};

const addItem = () => {
  const items = [...(props.content.items || []), { title: 'New feature', description: '', icon: null }];
  patch({ items });
};

const removeItem = (idx) => {
  const items = [...(props.content.items || [])];
  items.splice(idx, 1);
  patch({ items });
};
</script>
