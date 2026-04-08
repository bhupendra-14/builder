<template>
  <div class="py-16 px-4 bg-white sm:px-6 lg:px-8">
      <div class="max-w-4xl mx-auto">
        <InlineRichText
          v-if="editable"
          :model-value="content.body || ''"
          editable
          @update:model-value="(v) => patch({ body: v })"
        />
        <div v-else class="prose prose-lg prose-indigo text-gray-600" v-html="content.body"></div>
      </div>
  </div>
</template>

<script setup>
import InlineRichText from '../inline/InlineRichText.vue';

const props = defineProps({
  content: { type: Object, default: () => ({ body: '' }) },
  editable: { type: Boolean, default: false },
});

const emit = defineEmits(['update:content', 'pick-asset']);

const patch = (obj) => emit('update:content', { ...props.content, ...obj });
</script>
