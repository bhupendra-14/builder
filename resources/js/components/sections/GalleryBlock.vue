<template>
  <div class="bg-white py-16 sm:py-24 px-4 sm:px-6 lg:px-8">
    <div class="max-w-7xl mx-auto">
        <div class="text-center mb-12" v-if="editable || content.headline || content.subheadline">
            <InlineText
              :model-value="content.headline || ''"
              :editable="editable"
              tag="h2"
              class="text-3xl font-extrabold text-gray-900"
              placeholder="Gallery headline"
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

        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
            <div v-for="(img, idx) in (content.images || [])" :key="idx" class="relative group aspect-w-1 aspect-h-1 rounded-lg overflow-hidden bg-gray-100 shadow-sm h-48">
                <InlineImage
                  :asset="img"
                  :editable="editable"
                  img-class="object-cover w-full h-full"
                  :alt="img.alt_text || 'Gallery Image'"
                  @pick="$emit('pick-asset', `images[${idx}]`)"
                  @remove="removeImage(idx)"
                />
            </div>
            <button
              v-if="editable"
              type="button"
              @click="$emit('pick-asset', 'images[]')"
              class="h-48 rounded-lg border-2 border-dashed border-indigo-300 text-indigo-600 font-medium hover:bg-indigo-50 flex items-center justify-center"
            >+ Add image</button>
        </div>
        <div v-if="!editable && (!content.images || content.images.length === 0)" class="text-center py-10 text-gray-400 border-2 border-dashed border-gray-300 rounded-lg">
            No images in gallery.
        </div>
    </div>
  </div>
</template>

<script setup>
import InlineText from '../inline/InlineText.vue';
import InlineImage from '../inline/InlineImage.vue';

const props = defineProps({
  content: { type: Object, default: () => ({ images: [] }) },
  editable: { type: Boolean, default: false },
});

const emit = defineEmits(['update:content', 'pick-asset']);

const patch = (obj) => emit('update:content', { ...props.content, ...obj });

const removeImage = (idx) => {
  const images = [...(props.content.images || [])];
  images.splice(idx, 1);
  patch({ images });
};
</script>
