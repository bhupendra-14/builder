<template>
  <div :class="['inline-image-wrapper', editable ? 'is-editable' : '']" :style="wrapperStyle">
    <img
      v-if="asset?.url"
      :src="asset.url"
      :alt="asset.alt_text || alt"
      :class="imgClass"
    />
    <div v-else-if="editable" class="inline-image-placeholder" :class="imgClass">
      <span>{{ placeholder }}</span>
    </div>

    <div v-if="editable" class="inline-image-overlay">
      <button type="button" @click.stop.prevent="$emit('pick')" class="inline-image-btn">
        {{ asset ? 'Replace' : 'Choose' }}
      </button>
      <button v-if="asset && removable" type="button" @click.stop.prevent="$emit('remove')" class="inline-image-btn danger">
        Remove
      </button>
    </div>
  </div>
</template>

<script setup>
defineProps({
  asset: { type: Object, default: null },
  editable: { type: Boolean, default: false },
  alt: { type: String, default: '' },
  imgClass: { type: String, default: '' },
  wrapperStyle: { type: Object, default: () => ({}) },
  placeholder: { type: String, default: 'Click to add image' },
  removable: { type: Boolean, default: true },
});

defineEmits(['pick', 'remove']);
</script>

<style>
.inline-image-wrapper {
  position: relative;
  display: inline-block;
  width: 100%;
  height: 100%;
}
.inline-image-wrapper.is-editable:hover .inline-image-overlay {
  opacity: 1;
}
.inline-image-wrapper.is-editable {
  outline: 1px dashed rgba(99, 102, 241, 0.4);
  outline-offset: 2px;
  border-radius: 2px;
}
.inline-image-placeholder {
  display: flex;
  align-items: center;
  justify-content: center;
  background: rgba(99, 102, 241, 0.08);
  color: rgba(99, 102, 241, 0.8);
  font-style: italic;
  min-height: 120px;
  width: 100%;
}
.inline-image-overlay {
  position: absolute;
  inset: 0;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
  background: rgba(17, 24, 39, 0.5);
  opacity: 0;
  transition: opacity 0.15s;
  pointer-events: none;
}
.inline-image-overlay > * {
  pointer-events: auto;
}
.inline-image-btn {
  background: white;
  color: #111827;
  padding: 0.4rem 0.75rem;
  border-radius: 4px;
  font-size: 0.75rem;
  font-weight: 600;
  border: none;
  cursor: pointer;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
}
.inline-image-btn.danger {
  background: #dc2626;
  color: white;
}
.inline-image-btn:hover {
  transform: scale(1.05);
}
</style>
