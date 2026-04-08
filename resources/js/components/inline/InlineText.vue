<template>
  <component
    :is="tag"
    ref="el"
    :contenteditable="editable"
    spellcheck="false"
    :class="[
      editable ? 'inline-editable' : '',
      $attrs.class
    ]"
    :data-placeholder="placeholder"
    @blur="onBlur"
    @keydown="onKeydown"
    @paste="onPaste"
  />
</template>

<script setup>
import { ref, watch, onMounted, nextTick } from 'vue';

const props = defineProps({
  modelValue: { type: String, default: '' },
  editable: { type: Boolean, default: false },
  tag: { type: String, default: 'span' },
  placeholder: { type: String, default: 'Click to edit' },
  multiline: { type: Boolean, default: false },
});

const emit = defineEmits(['update:modelValue']);
const el = ref(null);

const writeDom = () => {
  if (el.value && el.value.innerText !== props.modelValue) {
    el.value.innerText = props.modelValue || '';
  }
};

onMounted(writeDom);
watch(() => props.modelValue, () => {
  // Only write if element is not currently focused (avoid caret jumps).
  if (document.activeElement !== el.value) writeDom();
});
watch(() => props.editable, async () => {
  await nextTick();
  writeDom();
});

const onBlur = () => {
  if (!props.editable || !el.value) return;
  const text = el.value.innerText.replace(/\u00a0/g, ' ');
  if (text !== props.modelValue) emit('update:modelValue', text);
};

const onKeydown = (e) => {
  if (!props.multiline && e.key === 'Enter') {
    e.preventDefault();
    el.value?.blur();
  }
  if (e.key === 'Escape') el.value?.blur();
};

// Plain-text paste only.
const onPaste = (e) => {
  if (!props.editable) return;
  e.preventDefault();
  const text = (e.clipboardData || window.clipboardData).getData('text');
  document.execCommand('insertText', false, text);
};
</script>

<style>
.inline-editable {
  outline: 1px dashed rgba(99, 102, 241, 0.4);
  outline-offset: 2px;
  border-radius: 2px;
  cursor: text;
  transition: outline-color 0.15s;
  min-width: 1rem;
  min-height: 1em;
}
.inline-editable:hover {
  outline-color: rgba(99, 102, 241, 0.8);
}
.inline-editable:focus {
  outline: 2px solid rgb(99, 102, 241);
  outline-offset: 2px;
}
.inline-editable:empty::before {
  content: attr(data-placeholder);
  color: rgba(99, 102, 241, 0.6);
  font-style: italic;
  pointer-events: none;
}
</style>
