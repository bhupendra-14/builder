<template>
  <div class="bg-gray-900 relative overflow-hidden" :style="{ minHeight: '500px' }">
    <!-- Slides -->
    <div class="relative h-[500px]">
      <transition-group name="slide" tag="div" class="absolute inset-0">
        <div
          v-for="(slide, idx) in slides"
          v-show="idx === currentIdx"
          :key="idx"
          class="absolute inset-0 flex items-center justify-center text-center"
        >
          <!-- Background image -->
          <div class="absolute inset-0">
            <InlineImage
              :asset="slide.image"
              :editable="editable"
              img-class="w-full h-full object-cover opacity-50"
              :alt="slide.headline || `Slide ${idx + 1}`"
              placeholder="Click to set slide image"
              @pick="$emit('pick-asset', `items[${idx}].image`)"
              @remove="updateItem(idx, { image: null })"
            />
          </div>

          <!-- Slide content -->
          <div class="relative z-10 max-w-3xl px-6">
            <InlineText
              :model-value="slide.headline || ''"
              :editable="editable"
              tag="h2"
              class="text-4xl sm:text-5xl font-extrabold text-white drop-shadow-lg"
              placeholder="Slide headline"
              @update:model-value="(v) => updateItem(idx, { headline: v })"
            />
            <InlineText
              :model-value="slide.text || ''"
              :editable="editable"
              tag="p"
              multiline
              class="mt-4 text-lg text-gray-200 drop-shadow"
              placeholder="Slide caption"
              @update:model-value="(v) => updateItem(idx, { text: v })"
            />

            <!-- CTA -->
            <div v-if="editable || (slide.cta_link && slide.cta_text)" class="mt-6 flex justify-center items-center gap-2">
              <a
                v-if="!editable"
                :href="slide.cta_link"
                class="px-6 py-3 bg-white text-gray-900 font-medium rounded-md hover:bg-gray-100 shadow-lg"
              >
                {{ slide.cta_text }}
              </a>
              <template v-else>
                <span class="px-6 py-3 bg-white text-gray-900 font-medium rounded-md shadow-lg inline-flex items-center">
                  <InlineText
                    :model-value="slide.cta_text || ''"
                    editable
                    tag="span"
                    placeholder="CTA text"
                    @update:model-value="(v) => updateItem(idx, { cta_text: v })"
                  />
                </span>
                <InlineText
                  :model-value="slide.cta_link || ''"
                  editable
                  tag="span"
                  class="text-xs text-gray-200 bg-black/40 px-2 py-1 rounded"
                  placeholder="https://..."
                  @update:model-value="(v) => updateItem(idx, { cta_link: v })"
                />
              </template>
            </div>
          </div>

          <!-- Edit-mode remove button per slide -->
          <button
            v-if="editable && slides.length > 1"
            type="button"
            @click="removeItem(idx)"
            class="absolute top-3 right-3 z-20 bg-red-600 text-white rounded-full w-7 h-7 hover:bg-red-700"
            title="Remove this slide"
          >&times;</button>
        </div>
      </transition-group>

      <!-- Empty state -->
      <div v-if="slides.length === 0" class="flex items-center justify-center h-full text-gray-400">
        No slides added yet.
      </div>
    </div>

    <!-- Nav arrows -->
    <template v-if="slides.length > 1">
      <button
        type="button"
        @click="prev"
        class="absolute left-2 top-1/2 -translate-y-1/2 z-10 bg-black/40 hover:bg-black/60 text-white rounded-full w-10 h-10 flex items-center justify-center"
        aria-label="Previous slide"
      >
        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" /></svg>
      </button>
      <button
        type="button"
        @click="next"
        class="absolute right-2 top-1/2 -translate-y-1/2 z-10 bg-black/40 hover:bg-black/60 text-white rounded-full w-10 h-10 flex items-center justify-center"
        aria-label="Next slide"
      >
        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
      </button>
    </template>

    <!-- Dots -->
    <div v-if="slides.length > 1" class="absolute bottom-4 left-0 right-0 flex justify-center gap-2 z-10">
      <button
        v-for="(_, idx) in slides"
        :key="idx"
        type="button"
        @click="goTo(idx)"
        class="h-2.5 w-2.5 rounded-full transition-colors"
        :class="idx === currentIdx ? 'bg-white' : 'bg-white/40 hover:bg-white/70'"
        :aria-label="`Go to slide ${idx + 1}`"
      ></button>
    </div>

    <!-- Edit-mode controls bar -->
    <div v-if="editable" class="bg-gray-900 text-white text-xs px-4 py-2 flex flex-wrap items-center gap-4">
      <button
        type="button"
        @click="addItem"
        class="px-3 py-1.5 bg-indigo-600 hover:bg-indigo-700 rounded font-medium"
      >+ Add slide</button>
      <label class="flex items-center gap-2 cursor-pointer">
        <input type="checkbox" :checked="content.autoplay" @change="patch({ autoplay: $event.target.checked })" class="rounded">
        Autoplay
      </label>
      <label v-if="content.autoplay" class="flex items-center gap-2">
        <span class="text-gray-400 uppercase tracking-wide">Interval (ms)</span>
        <input
          type="number"
          :value="content.interval || 5000"
          min="1000"
          step="500"
          @input="patch({ interval: Number($event.target.value) || 5000 })"
          class="w-24 bg-gray-800 border border-gray-700 rounded px-2 py-1"
        >
      </label>
      <span class="ml-auto text-gray-400">Slide {{ currentIdx + 1 }} / {{ slides.length }}</span>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onBeforeUnmount, watch } from 'vue';
import InlineText from '../inline/InlineText.vue';
import InlineImage from '../inline/InlineImage.vue';

const props = defineProps({
  content: { type: Object, default: () => ({ items: [] }) },
  editable: { type: Boolean, default: false },
});

const emit = defineEmits(['update:content', 'pick-asset']);

const slides = computed(() => props.content.items || []);
const currentIdx = ref(0);

const patch = (obj) => emit('update:content', { ...props.content, ...obj });

const updateItem = (idx, patchObj) => {
  const items = [...slides.value];
  items[idx] = { ...items[idx], ...patchObj };
  patch({ items });
};

const addItem = () => {
  const items = [...slides.value, { image: null, headline: 'New slide', text: '', cta_text: '', cta_link: '' }];
  patch({ items });
  currentIdx.value = items.length - 1;
};

const removeItem = (idx) => {
  const items = [...slides.value];
  items.splice(idx, 1);
  patch({ items });
  if (currentIdx.value >= items.length) currentIdx.value = Math.max(0, items.length - 1);
};

const next = () => {
  if (slides.value.length === 0) return;
  currentIdx.value = (currentIdx.value + 1) % slides.value.length;
};
const prev = () => {
  if (slides.value.length === 0) return;
  currentIdx.value = (currentIdx.value - 1 + slides.value.length) % slides.value.length;
};
const goTo = (idx) => { currentIdx.value = idx; };

// --- Autoplay (view mode only, never in edit mode to avoid distracting) ---
let timer = null;
const startAutoplay = () => {
  stopAutoplay();
  if (props.editable) return;
  if (!props.content.autoplay) return;
  if (slides.value.length < 2) return;
  const interval = Math.max(1000, Number(props.content.interval) || 5000);
  timer = setInterval(next, interval);
};
const stopAutoplay = () => {
  if (timer) {
    clearInterval(timer);
    timer = null;
  }
};

onMounted(startAutoplay);
onBeforeUnmount(stopAutoplay);
watch(
  () => [props.editable, props.content.autoplay, props.content.interval, slides.value.length],
  startAutoplay
);
</script>

<style scoped>
.slide-enter-active, .slide-leave-active {
  transition: opacity 0.5s ease;
}
.slide-enter-from, .slide-leave-to {
  opacity: 0;
}
</style>
