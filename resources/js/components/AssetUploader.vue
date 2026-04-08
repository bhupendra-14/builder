<template>
  <div>
    <!-- Folder picker -->
    <div class="mb-3 flex items-center gap-2 flex-wrap">
        <label class="text-sm font-medium text-gray-700">Folder:</label>
        <select v-model="selectedFolder" class="border border-gray-300 rounded-md text-sm py-1.5 px-2">
            <option v-for="f in folders" :key="f" :value="f">{{ f }}</option>
            <option value="__new__">+ New folder…</option>
        </select>
        <input
          v-if="selectedFolder === '__new__'"
          v-model="newFolderName"
          type="text"
          placeholder="folder-name"
          class="border border-gray-300 rounded-md text-sm py-1.5 px-2 w-40"
        >
    </div>

    <div
      class="border-2 border-dashed rounded-lg p-6 flex justify-center items-center flex-col relative transition-colors"
      :class="[isDragging ? 'border-indigo-500 bg-indigo-50' : 'border-gray-300 hover:border-indigo-400']"
      @dragover.prevent="isDragging = true"
      @dragleave.prevent="isDragging = false"
      @drop.prevent="handleDrop"
    >
      <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48" aria-hidden="true">
          <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
      </svg>
      <div class="mt-4 flex text-sm text-gray-600">
        <label for="file-upload" class="relative cursor-pointer bg-white rounded-md font-medium text-indigo-600 hover:text-indigo-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-indigo-500">
          <span>Upload a file</span>
          <input id="file-upload" name="file-upload" type="file" class="sr-only" multiple @change="handleFileSelect" accept="image/*,video/*,application/pdf" />
        </label>
        <p class="pl-1">or drag and drop</p>
      </div>
      <p class="text-xs text-gray-500 mt-2">Images, videos, PDFs up to 10MB (images auto-compressed to webp)</p>

      <!-- Upload Progress -->
      <div v-if="uploadingFiles.length > 0" class="mt-4 w-full max-w-sm">
          <div v-for="file in uploadingFiles" :key="file.name" class="mt-2 text-sm text-gray-600 flex justify-between">
              <span class="truncate">{{ file.name }}</span>
              <span v-if="file.error" class="text-red-500">Failed</span>
              <span v-else-if="file.progress < 100">{{ file.progress }}%</span>
              <span v-else class="text-green-500">Done</span>
          </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import axios from '../axios';

const emit = defineEmits(['uploadComplete']);

const isDragging = ref(false);
const uploadingFiles = ref([]);
const folders = ref(['media']);
const selectedFolder = ref('media');
const newFolderName = ref('');

const effectiveFolder = computed(() => {
    if (selectedFolder.value === '__new__') {
        return (newFolderName.value || '').trim() || 'media';
    }
    return selectedFolder.value || 'media';
});

const fetchFolders = async () => {
    try {
        const res = await axios.get('/assets/folders');
        const list = res.data.data || [];
        // Always include "media" as a fallback default
        const merged = Array.from(new Set(['media', ...list]));
        folders.value = merged;
    } catch (err) {
        console.error('Failed to load folders', err);
    }
};

onMounted(fetchFolders);

const handleDrop = (e) => {
    isDragging.value = false;
    const files = Array.from(e.dataTransfer.files);
    uploadFiles(files);
};

const handleFileSelect = (e) => {
    const files = Array.from(e.target.files);
    uploadFiles(files);
    e.target.value = ''; // reset input
};

const uploadFiles = async (files) => {
    const folder = effectiveFolder.value;

    for (const file of files) {
        const fileObj = { name: file.name, progress: 0 };
        uploadingFiles.value.push(fileObj);

        const formData = new FormData();
        formData.append('file', file);
        formData.append('folder', folder);

        try {
            await axios.post('/assets', formData, {
                headers: { 'Content-Type': 'multipart/form-data' },
                onUploadProgress: (progressEvent) => {
                    const percentCompleted = Math.round((progressEvent.loaded * 100) / progressEvent.total);
                    fileObj.progress = percentCompleted;
                }
            });
            emit('uploadComplete');
        } catch (error) {
            console.error('File upload failed:', error);
            fileObj.error = true;
        }
    }

    // After uploading, refresh the folder list in case the user typed a new one
    if (selectedFolder.value === '__new__' && newFolderName.value) {
        selectedFolder.value = newFolderName.value;
        newFolderName.value = '';
        fetchFolders();
    }

    // Clear progress list after 3 seconds
    setTimeout(() => {
        uploadingFiles.value = [];
    }, 3000);
};
</script>
