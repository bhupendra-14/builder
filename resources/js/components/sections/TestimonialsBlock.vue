<template>
  <div class="bg-white py-16 sm:py-24">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="text-center max-w-3xl mx-auto mb-12">
        <InlineText
          :model-value="content.headline || ''"
          :editable="editable"
          tag="h2"
          class="text-3xl font-extrabold text-gray-900 sm:text-4xl"
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

      <!-- Edit mode: plain grid so admins can see every testimonial at once -->
      <div v-if="editable" class="grid gap-8 lg:grid-cols-3">
        <figure v-for="(t, idx) in items" :key="idx" class="bg-gray-50 rounded-xl p-8 shadow-sm relative">
          <button
            type="button"
            @click="removeItem(idx)"
            class="absolute top-2 right-2 bg-red-600 text-white rounded-full w-6 h-6 text-xs hover:bg-red-700"
          >&times;</button>
          <blockquote class="text-gray-700 italic">
            <InlineText
              :model-value="t.quote || ''"
              editable
              tag="p"
              multiline
              placeholder="&ldquo;Quote here...&rdquo;"
              @update:model-value="(v) => updateItem(idx, { quote: v })"
            />
          </blockquote>
          <figcaption class="mt-6 flex items-center">
            <div class="h-12 w-12">
              <InlineImage
                :asset="t.avatar"
                editable
                img-class="h-12 w-12 rounded-full object-cover"
                placeholder="Avatar"
                :alt="t.name"
                @pick="$emit('pick-asset', `items[${idx}].avatar`)"
                @remove="updateItem(idx, { avatar: null })"
              />
            </div>
            <div class="ml-4 flex-1">
              <InlineText
                :model-value="t.name || ''"
                editable
                tag="div"
                class="text-base font-semibold text-gray-900"
                placeholder="Name"
                @update:model-value="(v) => updateItem(idx, { name: v })"
              />
              <InlineText
                :model-value="t.role || ''"
                editable
                tag="div"
                class="text-sm text-gray-500"
                placeholder="Role / Company"
                @update:model-value="(v) => updateItem(idx, { role: v })"
              />
            </div>
          </figcaption>
        </figure>
        <button
          type="button"
          @click="addItem"
          class="flex items-center justify-center p-8 border-2 border-dashed border-indigo-300 rounded-xl text-sm text-indigo-600 hover:bg-indigo-50"
        >+ Add testimonial</button>
      </div>

      <!-- View mode: horizontal auto-rotating carousel -->
      <div v-else-if="items.length > 0" class="relative" @mouseenter="pauseAutoplay" @mouseleave="startAutoplay">
        <div
          ref="scroller"
          class="flex gap-6 overflow-x-auto snap-x snap-mandatory scroll-smooth pb-4 testimonials-scroller"
        >
          <figure
            v-for="(t, idx) in items"
            :key="idx"
            class="snap-center shrink-0 w-[85%] sm:w-[48%] lg:w-[31%] bg-gray-50 rounded-xl p-8 shadow-sm"
          >
            <blockquote class="text-gray-700 italic min-h-[6rem]">
              <p>&ldquo;{{ t.quote }}&rdquo;</p>
            </blockquote>
            <figcaption class="mt-6 flex items-center">
              <img
                v-if="t.avatar?.url"
                :src="t.avatar.url"
                :alt="t.name"
                class="h-12 w-12 rounded-full object-cover"
              />
              <div v-else class="h-12 w-12 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-600 font-semibold">
                {{ (t.name || '?').charAt(0) }}
              </div>
              <div class="ml-4">
                <div class="text-base font-semibold text-gray-900">{{ t.name }}</div>
                <div class="text-sm text-gray-500">{{ t.role }}</div>
              </div>
            </figcaption>
          </figure>
        </div>

        <!-- Prev / Next arrows (hidden when only 1 item fits) -->
        <button
          v-if="items.length > 1"
          type="button"
          @click="scrollBy(-1)"
          class="hidden sm:flex absolute -left-3 top-1/2 -translate-y-1/2 z-10 h-10 w-10 bg-white shadow-lg rounded-full items-center justify-center text-gray-600 hover:text-gray-900 hover:scale-105 transition"
          aria-label="Previous testimonial"
        >
          <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" /></svg>
        </button>
        <button
          v-if="items.length > 1"
          type="button"
          @click="scrollBy(1)"
          class="hidden sm:flex absolute -right-3 top-1/2 -translate-y-1/2 z-10 h-10 w-10 bg-white shadow-lg rounded-full items-center justify-center text-gray-600 hover:text-gray-900 hover:scale-105 transition"
          aria-label="Next testimonial"
        >
          <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
        </button>

        <!-- Dots -->
        <div v-if="items.length > 1" class="flex justify-center gap-2 mt-6">
          <button
            v-for="(_, idx) in items"
            :key="idx"
            type="button"
            @click="scrollTo(idx)"
            class="h-2.5 w-2.5 rounded-full transition-colors"
            :class="idx === activeIdx ? 'bg-indigo-600' : 'bg-gray-300 hover:bg-gray-400'"
            :aria-label="`Go to testimonial ${idx + 1}`"
          ></button>
        </div>
      </div>

      <div v-else class="text-center py-10 text-gray-400 border-2 border-dashed border-gray-200 rounded-lg">
        No testimonials added yet.
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onBeforeUnmount, watch, nextTick } from 'vue';
import InlineText from '../inline/InlineText.vue';
import InlineImage from '../inline/InlineImage.vue';

const props = defineProps({
  content: { type: Object, default: () => ({ items: [] }) },
  editable: { type: Boolean, default: false },
});

const emit = defineEmits(['update:content', 'pick-asset']);

const items = computed(() => props.content.items || []);

const patch = (obj) => emit('update:content', { ...props.content, ...obj });

const updateItem = (idx, patchObj) => {
  const next = [...items.value];
  next[idx] = { ...next[idx], ...patchObj };
  patch({ items: next });
};

const addItem = () => {
  patch({ items: [...items.value, { quote: '', name: '', role: '', avatar: null }] });
};

const removeItem = (idx) => {
  const next = [...items.value];
  next.splice(idx, 1);
  patch({ items: next });
};

// ---- Carousel logic (view mode only) -------------------------------------

const scroller = ref(null);
const activeIdx = ref(0);
let autoplayTimer = null;

const scrollTo = (idx) => {
  if (!scroller.value) return;
  const cards = scroller.value.querySelectorAll('figure');
  if (!cards[idx]) return;
  const card = cards[idx];
  scroller.value.scrollTo({
    left: card.offsetLeft - scroller.value.offsetLeft,
    behavior: 'smooth',
  });
  activeIdx.value = idx;
};

const scrollBy = (direction) => {
  const next = (activeIdx.value + direction + items.value.length) % items.value.length;
  scrollTo(next);
};

const startAutoplay = () => {
  stopAutoplay();
  if (props.editable) return;
  if (items.value.length < 2) return;
  autoplayTimer = setInterval(() => {
    scrollBy(1);
  }, 5000);
};

const stopAutoplay = () => {
  if (autoplayTimer) {
    clearInterval(autoplayTimer);
    autoplayTimer = null;
  }
};

const pauseAutoplay = () => stopAutoplay();

// Track which card is currently centered (for dot indicator) when the user
// scrolls manually or swipes on mobile.
const onScroll = () => {
  if (!scroller.value) return;
  const cards = Array.from(scroller.value.querySelectorAll('figure'));
  const scrollLeft = scroller.value.scrollLeft;
  let closest = 0;
  let closestDist = Infinity;
  cards.forEach((card, i) => {
    const dist = Math.abs(card.offsetLeft - scroller.value.offsetLeft - scrollLeft);
    if (dist < closestDist) {
      closestDist = dist;
      closest = i;
    }
  });
  activeIdx.value = closest;
};

onMounted(async () => {
  await nextTick();
  if (scroller.value) {
    scroller.value.addEventListener('scroll', onScroll, { passive: true });
  }
  startAutoplay();
});

onBeforeUnmount(() => {
  stopAutoplay();
  if (scroller.value) {
    scroller.value.removeEventListener('scroll', onScroll);
  }
});

watch(() => [props.editable, items.value.length], () => {
  // Restart autoplay when editable flips or item count changes
  activeIdx.value = 0;
  startAutoplay();
});
</script>

<style scoped>
/* Hide scrollbar while keeping scroll functional */
.testimonials-scroller::-webkit-scrollbar {
  display: none;
}
.testimonials-scroller {
  -ms-overflow-style: none;
  scrollbar-width: none;
}
</style>
