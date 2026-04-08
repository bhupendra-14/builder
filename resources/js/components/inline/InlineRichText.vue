<template>
  <div :class="editable ? 'inline-richtext-wrapper' : ''">
    <div v-if="editable && editor" class="inline-richtext-toolbar">
      <button type="button" @click.prevent="editor.chain().focus().toggleBold().run()" :class="btn('bold')" title="Bold"><strong>B</strong></button>
      <button type="button" @click.prevent="editor.chain().focus().toggleItalic().run()" :class="btn('italic')" title="Italic"><em>I</em></button>
      <button type="button" @click.prevent="editor.chain().focus().toggleHeading({ level: 2 }).run()" :class="btn('heading', { level: 2 })" title="Heading">H2</button>
      <button type="button" @click.prevent="editor.chain().focus().toggleHeading({ level: 3 }).run()" :class="btn('heading', { level: 3 })" title="Subheading">H3</button>
      <button type="button" @click.prevent="editor.chain().focus().toggleBulletList().run()" :class="btn('bulletList')" title="Bullet list">• List</button>
      <button type="button" @click.prevent="editor.chain().focus().toggleOrderedList().run()" :class="btn('orderedList')" title="Numbered list">1. List</button>
      <button type="button" @click.prevent="setLink" :class="editor.isActive('link') ? 'is-active' : ''" title="Link">Link</button>
      <button type="button" @click.prevent="editor.chain().focus().unsetAllMarks().clearNodes().run()" title="Clear formatting">Clear</button>
    </div>
    <editor-content :editor="editor" />
  </div>
</template>

<script setup>
import { watch, onBeforeUnmount } from 'vue';
import { useEditor, EditorContent } from '@tiptap/vue-3';
import StarterKit from '@tiptap/starter-kit';

const props = defineProps({
  modelValue: { type: String, default: '' },
  editable: { type: Boolean, default: false },
});

const emit = defineEmits(['update:modelValue']);

const editor = useEditor({
  content: props.modelValue || '',
  editable: props.editable,
  extensions: [StarterKit],
  editorProps: {
    attributes: {
      class: 'prose prose-indigo max-w-none focus:outline-none',
    },
  },
  onUpdate: ({ editor }) => {
    emit('update:modelValue', editor.getHTML());
  },
});

watch(() => props.modelValue, (val) => {
  if (!editor.value) return;
  if (val !== editor.value.getHTML()) editor.value.commands.setContent(val || '', false);
});

watch(() => props.editable, (val) => {
  editor.value?.setEditable(val);
});

onBeforeUnmount(() => editor.value?.destroy());

const btn = (name, attrs) => editor.value?.isActive(name, attrs || {}) ? 'is-active' : '';

const setLink = () => {
  const previousUrl = editor.value.getAttributes('link').href;
  const url = window.prompt('URL', previousUrl || 'https://');
  if (url === null) return;
  if (url === '') {
    editor.value.chain().focus().extendMarkRange('link').unsetLink().run();
    return;
  }
  editor.value.chain().focus().extendMarkRange('link').setLink({ href: url }).run();
};
</script>

<style>
.inline-richtext-wrapper {
  outline: 1px dashed rgba(99, 102, 241, 0.4);
  outline-offset: 4px;
  border-radius: 4px;
  padding: 0.25rem;
  transition: outline-color 0.15s;
}
.inline-richtext-wrapper:hover {
  outline-color: rgba(99, 102, 241, 0.8);
}
.inline-richtext-wrapper:focus-within {
  outline: 2px solid rgb(99, 102, 241);
}
.inline-richtext-toolbar {
  display: flex;
  flex-wrap: wrap;
  gap: 0.25rem;
  margin-bottom: 0.5rem;
  padding: 0.25rem;
  background: #f3f4f6;
  border-radius: 4px;
}
.inline-richtext-toolbar button {
  font-size: 0.75rem;
  padding: 0.25rem 0.5rem;
  background: white;
  border: 1px solid #d1d5db;
  border-radius: 3px;
  cursor: pointer;
}
.inline-richtext-toolbar button:hover {
  background: #eef2ff;
}
.inline-richtext-toolbar button.is-active {
  background: rgb(99, 102, 241);
  color: white;
  border-color: rgb(99, 102, 241);
}
</style>
