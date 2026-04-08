<template>
  <div class="bg-indigo-700">
    <div class="max-w-7xl mx-auto py-16 px-4 sm:px-6 lg:py-20 lg:px-8 lg:flex lg:items-center lg:justify-between">
      <div>
        <InlineText
          :model-value="content.headline || ''"
          :editable="editable"
          tag="h2"
          class="block text-3xl font-extrabold tracking-tight text-white sm:text-4xl"
          placeholder="Ready to dive in?"
          @update:model-value="(v) => patch({ headline: v })"
        />
        <InlineText
          :model-value="content.subheadline || ''"
          :editable="editable"
          tag="span"
          class="block text-indigo-200 mt-2 text-xl font-medium"
          placeholder="Subheadline"
          @update:model-value="(v) => patch({ subheadline: v })"
        />
      </div>
      <div class="mt-8 flex lg:mt-0 lg:shrink-0 flex-col sm:flex-row gap-3">
        <div v-if="editable || content.primary_link" class="flex flex-col">
          <div class="inline-flex items-center justify-center px-5 py-3 border border-transparent text-base font-medium rounded-md text-indigo-700 bg-white shadow">
            <InlineText
              :model-value="content.primary_label || ''"
              :editable="editable"
              tag="span"
              placeholder="Get started"
              @update:model-value="(v) => patch({ primary_label: v })"
            />
          </div>
          <InlineText
            v-if="editable"
            :model-value="content.primary_link || ''"
            editable
            tag="span"
            class="mt-1 text-xs text-indigo-200 bg-indigo-800 px-2 py-1 rounded"
            placeholder="https://..."
            @update:model-value="(v) => patch({ primary_link: v })"
          />
        </div>
        <div v-if="editable || content.secondary_link" class="flex flex-col">
          <div class="inline-flex items-center justify-center px-5 py-3 border border-transparent text-base font-medium rounded-md text-white bg-indigo-600">
            <InlineText
              :model-value="content.secondary_label || ''"
              :editable="editable"
              tag="span"
              placeholder="Learn more"
              @update:model-value="(v) => patch({ secondary_label: v })"
            />
          </div>
          <InlineText
            v-if="editable"
            :model-value="content.secondary_link || ''"
            editable
            tag="span"
            class="mt-1 text-xs text-indigo-200 bg-indigo-800 px-2 py-1 rounded"
            placeholder="https://..."
            @update:model-value="(v) => patch({ secondary_link: v })"
          />
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import InlineText from '../inline/InlineText.vue';

const props = defineProps({
  content: { type: Object, default: () => ({}) },
  editable: { type: Boolean, default: false },
});

const emit = defineEmits(['update:content', 'pick-asset']);

const patch = (obj) => emit('update:content', { ...props.content, ...obj });
</script>
