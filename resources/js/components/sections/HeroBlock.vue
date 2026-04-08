<template>
  <div class="relative bg-gray-900 border-b border-gray-800 flex items-center justify-center text-center overflow-hidden" :style="{ minHeight: content.full_height ? '100vh' : '600px' }">
      <div class="absolute inset-0">
          <InlineImage
            v-if="editable || content.bg_image"
            :asset="content.bg_image"
            :editable="editable"
            :img-class="'w-full h-full object-cover opacity-40'"
            alt="Hero Background"
            placeholder="Click to choose hero background"
            @pick="$emit('pick-asset', 'bg_image')"
            @remove="patch({ bg_image: null })"
          />
      </div>
      <div class="relative z-10 max-w-4xl px-4 sm:px-6 lg:px-8 w-full">
          <InlineText
            :model-value="content.headline || ''"
            :editable="editable"
            tag="h1"
            class="text-4xl font-extrabold tracking-tight text-white sm:text-5xl lg:text-6xl drop-shadow-lg"
            placeholder="Headline"
            @update:model-value="(v) => patch({ headline: v })"
          />
          <InlineText
            :model-value="content.text || ''"
            :editable="editable"
            tag="p"
            multiline
            class="mt-6 text-xl text-gray-300 max-w-3xl mx-auto drop-shadow-md"
            placeholder="Subtitle"
            @update:model-value="(v) => patch({ text: v })"
          />
          <div v-if="editable || (content.cta_link && content.cta_text)" class="mt-10 flex justify-center items-center gap-2">
            <a v-if="!editable" :href="content.cta_link" class="px-8 py-3 border border-transparent text-base font-medium rounded-md text-gray-900 bg-white hover:bg-gray-100 md:py-4 md:text-lg md:px-10 shadow-lg">
              {{ content.cta_text }}
            </a>
            <template v-else>
              <span class="px-8 py-3 border border-transparent text-base font-medium rounded-md text-gray-900 bg-white md:py-4 md:text-lg md:px-10 shadow-lg inline-flex items-center">
                <InlineText
                  :model-value="content.cta_text || ''"
                  editable
                  tag="span"
                  placeholder="CTA text"
                  @update:model-value="(v) => patch({ cta_text: v })"
                />
              </span>
              <InlineText
                :model-value="content.cta_link || ''"
                editable
                tag="span"
                class="text-sm text-gray-300 bg-gray-800/70 px-2 py-1 rounded"
                placeholder="https://..."
                @update:model-value="(v) => patch({ cta_link: v })"
              />
            </template>
          </div>
      </div>
  </div>
</template>

<script setup>
import InlineText from '../inline/InlineText.vue';
import InlineImage from '../inline/InlineImage.vue';

const props = defineProps({
  content: { type: Object, default: () => ({}) },
  editable: { type: Boolean, default: false },
});

const emit = defineEmits(['update:content', 'pick-asset']);

const patch = (obj) => emit('update:content', { ...props.content, ...obj });
</script>
