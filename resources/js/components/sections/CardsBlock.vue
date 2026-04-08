<template>
  <div class="bg-gray-50 py-16 sm:py-24">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="text-center mb-12">
        <InlineText
          :model-value="content.headline || ''"
          :editable="editable"
          tag="h2"
          class="text-3xl font-extrabold text-gray-900"
          placeholder="Section headline"
          @update:model-value="(v) => patch({ headline: v })"
        />
        <InlineText
          :model-value="content.subheadline || ''"
          :editable="editable"
          tag="p"
          class="mt-4 text-lg text-gray-500"
          placeholder="Subheadline"
          @update:model-value="(v) => patch({ subheadline: v })"
        />
      </div>

      <div class="grid gap-8 sm:grid-cols-2 lg:grid-cols-3">
        <div v-for="(card, idx) in (content.items || [])" :key="idx" class="bg-white overflow-hidden shadow rounded-lg flex flex-col relative">
          <button
            v-if="editable"
            type="button"
            @click="removeItem(idx)"
            class="absolute top-2 right-2 bg-red-600 text-white rounded-full w-6 h-6 text-xs z-10 hover:bg-red-700"
          >&times;</button>
          <div class="h-48 w-full">
            <InlineImage
              :asset="card.image"
              :editable="editable"
              img-class="h-48 w-full object-cover"
              :alt="card.title || 'Card image'"
              @pick="$emit('pick-asset', `items[${idx}].image`)"
              @remove="updateItem(idx, { image: null })"
            />
          </div>
          <div class="p-6 flex-1 flex flex-col">
            <InlineText
              :model-value="card.title || ''"
              :editable="editable"
              tag="h3"
              class="text-lg font-semibold text-gray-900"
              placeholder="Card title"
              @update:model-value="(v) => updateItem(idx, { title: v })"
            />
            <InlineText
              :model-value="card.description || ''"
              :editable="editable"
              tag="p"
              multiline
              class="mt-2 text-sm text-gray-500 flex-1"
              placeholder="Description"
              @update:model-value="(v) => updateItem(idx, { description: v })"
            />
            <div v-if="editable || card.link" class="mt-4 flex items-center gap-2">
              <div class="text-sm font-medium text-indigo-600">
                <InlineText
                  :model-value="card.link_label || ''"
                  :editable="editable"
                  tag="span"
                  placeholder="Learn more"
                  @update:model-value="(v) => updateItem(idx, { link_label: v })"
                />
                &rarr;
              </div>
              <InlineText
                v-if="editable"
                :model-value="card.link || ''"
                editable
                tag="span"
                class="text-xs bg-gray-100 px-2 py-1 rounded"
                placeholder="https://..."
                @update:model-value="(v) => updateItem(idx, { link: v })"
              />
            </div>
          </div>
        </div>
        <button
          v-if="editable"
          type="button"
          @click="addItem"
          class="flex items-center justify-center p-8 border-2 border-dashed border-indigo-300 rounded-lg text-sm text-indigo-600 hover:bg-indigo-50"
        >+ Add card</button>
      </div>
      <div v-if="!editable && (!content.items || content.items.length === 0)" class="text-center py-10 text-gray-400 border-2 border-dashed border-gray-200 rounded-lg">
        No cards added yet.
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
  const items = [...(props.content.items || []), { title: 'New card', description: '', link: '', link_label: 'Learn more', image: null }];
  patch({ items });
};

const removeItem = (idx) => {
  const items = [...(props.content.items || [])];
  items.splice(idx, 1);
  patch({ items });
};
</script>
