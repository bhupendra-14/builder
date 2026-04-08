<template>
  <div class="bg-gray-900 py-16 sm:py-24">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
      <div v-if="editable || content.headline || content.subheadline" class="text-center mb-10">
        <InlineText
          :model-value="content.headline || ''"
          :editable="editable"
          tag="h2"
          class="text-3xl font-extrabold text-white sm:text-4xl"
          placeholder="Video headline"
          @update:model-value="(v) => patch({ headline: v })"
        />
        <InlineText
          :model-value="content.subheadline || ''"
          :editable="editable"
          tag="p"
          class="mt-4 text-lg text-gray-300"
          placeholder="Subheadline"
          @update:model-value="(v) => patch({ subheadline: v })"
        />
      </div>

      <div class="aspect-w-16 aspect-h-9 rounded-lg overflow-hidden shadow-2xl bg-black relative">
        <iframe
          v-if="!editable && embedUrl"
          :src="embedUrl"
          class="w-full h-full"
          frameborder="0"
          allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
          allowfullscreen
        ></iframe>
        <video
          v-else-if="!editable && content.video?.url"
          :src="content.video.url"
          :poster="content.poster?.url"
          controls
          class="w-full h-full"
        ></video>
        <div v-else class="flex flex-col items-center justify-center h-64 text-gray-300 gap-3 w-full">
          <div v-if="editable" class="w-full max-w-md space-y-2 p-6">
            <label class="block text-xs text-gray-400 uppercase">Embed URL (YouTube / Vimeo)</label>
            <InlineText
              :model-value="content.embed_url || ''"
              editable
              tag="div"
              class="bg-gray-800 text-white px-3 py-2 rounded text-sm"
              placeholder="https://youtu.be/..."
              @update:model-value="(v) => patch({ embed_url: v })"
            />
            <div class="text-xs text-gray-500 text-center my-1">— or —</div>
            <button type="button" @click="$emit('pick-asset', 'video')" class="w-full px-4 py-2 bg-indigo-600 text-white rounded text-sm">
              {{ content.video ? 'Replace video asset' : 'Upload video asset' }}
            </button>
            <button v-if="content.poster || content.video" type="button" @click="$emit('pick-asset', 'poster')" class="w-full px-4 py-2 bg-gray-700 text-white rounded text-sm">
              {{ content.poster ? 'Replace poster' : 'Set poster image' }}
            </button>
          </div>
          <span v-else>No video configured</span>
        </div>
      </div>

      <InlineText
        :model-value="content.caption || ''"
        :editable="editable"
        tag="p"
        class="mt-4 text-center text-sm text-gray-400"
        placeholder="Caption"
        @update:model-value="(v) => patch({ caption: v })"
      />
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';
import InlineText from '../inline/InlineText.vue';

const props = defineProps({
  content: { type: Object, default: () => ({}) },
  editable: { type: Boolean, default: false },
});

const emit = defineEmits(['update:content', 'pick-asset']);

const patch = (obj) => emit('update:content', { ...props.content, ...obj });

const embedUrl = computed(() => {
  const url = props.content.embed_url;
  if (!url) return null;
  let m = url.match(/(?:youtube\.com\/watch\?v=|youtu\.be\/)([\w-]{11})/);
  if (m) return `https://www.youtube.com/embed/${m[1]}`;
  m = url.match(/vimeo\.com\/(\d+)/);
  if (m) return `https://player.vimeo.com/video/${m[1]}`;
  return url;
});
</script>
