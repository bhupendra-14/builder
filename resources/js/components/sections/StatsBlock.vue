<template>
  <div class="bg-indigo-800 py-16 sm:py-20">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="text-center max-w-3xl mx-auto mb-12">
        <InlineText
          :model-value="content.headline || ''"
          :editable="editable"
          tag="h2"
          class="text-3xl font-extrabold text-white sm:text-4xl"
          placeholder="Section headline"
          @update:model-value="(v) => patch({ headline: v })"
        />
        <InlineText
          :model-value="content.subheadline || ''"
          :editable="editable"
          tag="p"
          class="mt-4 text-lg text-indigo-200"
          placeholder="Subheadline"
          @update:model-value="(v) => patch({ subheadline: v })"
        />
      </div>

      <dl class="grid gap-8 text-center sm:grid-cols-2 lg:grid-cols-4">
        <div v-for="(stat, idx) in (content.items || [])" :key="idx" class="flex flex-col relative">
          <button
            v-if="editable"
            type="button"
            @click="removeItem(idx)"
            class="absolute -top-2 -right-2 bg-red-600 text-white rounded-full w-6 h-6 text-xs hover:bg-red-700"
          >&times;</button>
          <dt class="order-2 mt-2 text-lg font-medium text-indigo-200">
            <InlineText
              :model-value="stat.label || ''"
              :editable="editable"
              tag="span"
              placeholder="Label"
              @update:model-value="(v) => updateItem(idx, { label: v })"
            />
          </dt>
          <dd class="order-1 text-5xl font-extrabold text-white flex items-baseline justify-center gap-1">
            <InlineText
              :model-value="stat.prefix || ''"
              :editable="editable"
              tag="span"
              placeholder="$"
              @update:model-value="(v) => updateItem(idx, { prefix: v })"
            />
            <template v-if="editable">
              <InlineText
                :model-value="String(stat.value ?? 0)"
                editable
                tag="span"
                placeholder="0"
                @update:model-value="(v) => updateItem(idx, { value: Number(v) || 0 })"
              />
            </template>
            <template v-else>
              <span>{{ animated[idx] ?? 0 }}</span>
            </template>
            <InlineText
              :model-value="stat.suffix || ''"
              :editable="editable"
              tag="span"
              placeholder="+"
              @update:model-value="(v) => updateItem(idx, { suffix: v })"
            />
          </dd>
        </div>
        <button
          v-if="editable"
          type="button"
          @click="addItem"
          class="flex items-center justify-center p-8 border-2 border-dashed border-indigo-400 rounded text-sm text-indigo-200 hover:bg-indigo-700"
        >+ Add stat</button>
      </dl>
      <div v-if="!editable && (!content.items || content.items.length === 0)" class="text-center py-10 text-indigo-300 border-2 border-dashed border-indigo-500 rounded-lg">
        No stats added yet.
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, watch, onMounted } from 'vue';
import InlineText from '../inline/InlineText.vue';

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
  const items = [...(props.content.items || []), { label: '', value: 0, prefix: '', suffix: '' }];
  patch({ items });
};

const removeItem = (idx) => {
  const items = [...(props.content.items || [])];
  items.splice(idx, 1);
  patch({ items });
};

// Animated counters (view-only mode)
const animated = ref([]);
const animate = () => {
  const items = props.content.items || [];
  animated.value = items.map(() => 0);
  items.forEach((stat, idx) => {
    const target = Number(stat.value) || 0;
    const duration = 1200;
    const start = performance.now();
    const step = (now) => {
      const progress = Math.min((now - start) / duration, 1);
      animated.value[idx] = Math.round(target * progress);
      if (progress < 1) requestAnimationFrame(step);
    };
    requestAnimationFrame(step);
  });
};
onMounted(animate);
watch(() => props.content.items, animate, { deep: true });
</script>
