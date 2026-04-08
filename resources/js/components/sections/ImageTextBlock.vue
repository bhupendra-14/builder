<template>
  <div class="py-16 bg-gray-50 overflow-hidden lg:py-24">
    <div class="relative max-w-xl mx-auto px-4 sm:px-6 lg:px-8 lg:max-w-7xl">
      <div class="relative lg:grid lg:grid-cols-2 lg:gap-8 items-center">
        <div class="relative" :class="{'lg:col-start-2': content.image_position === 'left', 'lg:col-start-1': content.image_position === 'right'}">
          <InlineText
            :model-value="content.headline || ''"
            :editable="editable"
            tag="h3"
            class="text-2xl font-extrabold text-gray-900 tracking-tight sm:text-3xl"
            placeholder="Section headline"
            @update:model-value="(v) => patch({ headline: v })"
          />
          <InlineText
            :model-value="content.text || ''"
            :editable="editable"
            tag="p"
            multiline
            class="mt-3 text-lg text-gray-500"
            placeholder="Short summary"
            @update:model-value="(v) => patch({ text: v })"
          />
          <div class="mt-5">
            <InlineRichText
              v-if="editable"
              :model-value="content.body || ''"
              editable
              @update:model-value="(v) => patch({ body: v })"
            />
            <div v-else class="prose prose-indigo text-gray-500" v-html="content.body"></div>
          </div>
        </div>

        <div class="mt-10 -mx-4 relative lg:mt-0" :class="{'lg:col-start-1': content.image_position === 'left', 'lg:col-start-2': content.image_position === 'right'}">
          <InlineImage
            :asset="content.image"
            :editable="editable"
            img-class="relative mx-auto rounded-lg shadow-xl max-h-96 w-auto"
            alt="Image"
            @pick="$emit('pick-asset', 'image')"
            @remove="patch({ image: null })"
          />
          <button
            v-if="editable"
            type="button"
            @click="flipPosition"
            class="absolute bottom-2 left-1/2 -translate-x-1/2 bg-indigo-600 text-white text-xs px-3 py-1 rounded-full shadow-lg hover:bg-indigo-700 flex items-center gap-1"
            :title="`Flip image to ${content.image_position === 'left' ? 'right' : 'left'}`"
          >
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
            </svg>
            Flip to {{ content.image_position === 'left' ? 'right' : 'left' }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import InlineText from '../inline/InlineText.vue';
import InlineRichText from '../inline/InlineRichText.vue';
import InlineImage from '../inline/InlineImage.vue';

const props = defineProps({
  content: { type: Object, default: () => ({ image_position: 'left' }) },
  editable: { type: Boolean, default: false },
});

const emit = defineEmits(['update:content', 'pick-asset']);

const patch = (obj) => emit('update:content', { ...props.content, ...obj });

const flipPosition = () => {
  patch({ image_position: props.content.image_position === 'left' ? 'right' : 'left' });
};
</script>
