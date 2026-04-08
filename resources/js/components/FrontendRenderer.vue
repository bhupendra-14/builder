<template>
  <div class="frontend-renderer">
    <div
      v-for="(section, idx) in sections"
      :key="section.id"
      :id="anchorIds[idx]"
      :data-section-type="section.type"
      class="cms-section"
    >
      <component
        :is="getComponent(section.type)"
        :content="section.content || {}"
        :editable="editable"
        @update:content="(patch) => $emit('update:content', { sectionId: section.id, content: patch })"
        @pick-asset="(field) => $emit('pick-asset', { sectionId: section.id, field })"
      />
    </div>
    <div v-if="!sections || sections.length === 0" class="py-20 text-center text-gray-500">
        No content available.
    </div>
  </div>
</template>

<script setup>
import { defineAsyncComponent, computed } from 'vue';

const props = defineProps({
  sections: { type: Array, default: () => [] },
  editable: { type: Boolean, default: false },
});

defineEmits(['update:content', 'pick-asset']);

// Dynamic Registry of block components
const componentRegistry = {
  hero: defineAsyncComponent(() => import('./sections/HeroBlock.vue')),
  text: defineAsyncComponent(() => import('./sections/TextBlock.vue')),
  image_text: defineAsyncComponent(() => import('./sections/ImageTextBlock.vue')),
  gallery: defineAsyncComponent(() => import('./sections/GalleryBlock.vue')),
  carousel: defineAsyncComponent(() => import('./sections/CarouselBlock.vue')),
  tabs: defineAsyncComponent(() => import('./sections/TabsBlock.vue')),
  accordion: defineAsyncComponent(() => import('./sections/AccordionBlock.vue')),
  cta: defineAsyncComponent(() => import('./sections/CtaBlock.vue')),
  video: defineAsyncComponent(() => import('./sections/VideoBlock.vue')),
  feature_grid: defineAsyncComponent(() => import('./sections/FeatureGridBlock.vue')),
  cards: defineAsyncComponent(() => import('./sections/CardsBlock.vue')),
  testimonials: defineAsyncComponent(() => import('./sections/TestimonialsBlock.vue')),
  stats: defineAsyncComponent(() => import('./sections/StatsBlock.vue')),
  promo_banner: defineAsyncComponent(() => import('./sections/PromoBannerBlock.vue')),
};

const getComponent = (type) => {
  return componentRegistry[type] || null;
};

/**
 * Compute a stable, human-friendly anchor id for each section.
 * The first occurrence of each type uses just the type (e.g. "cta"),
 * subsequent ones get a numeric suffix ("cta-2", "cta-3"), so admins
 * can write CTA links like #cta and have them just work.
 */
const anchorIds = computed(() => {
  const seen = {};
  return props.sections.map((s) => {
    const type = s.type || 'section';
    seen[type] = (seen[type] || 0) + 1;
    return seen[type] === 1 ? type : `${type}-${seen[type]}`;
  });
});
</script>

<style>
/* Smooth jump-to-anchor across the public site */
html { scroll-behavior: smooth; }

/* Offset anchor targets so they don't hide under the sticky header */
.cms-section {
  scroll-margin-top: 5rem;
}
</style>
