<template>
  <div class="py-6 px-4 sm:px-6 lg:px-8">
    <div class="mb-6 md:flex md:items-center md:justify-between">
      <div class="flex-1 min-w-0">
        <h2 class="text-2xl font-bold leading-7 text-gray-900 sm:text-3xl sm:truncate">
          Asset Manager
        </h2>
      </div>
      <div class="mt-4 flex md:mt-0 md:ml-4 space-x-2 flex-wrap gap-2">
        <select v-model="folderFilter" @change="fetchAssets(1)" class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block sm:text-sm border-gray-300 rounded-md">
            <option value="">All folders</option>
            <option v-for="f in folders" :key="f" :value="f">{{ f }}</option>
        </select>
        <input type="text" v-model="searchQuery" @input="debounceSearch" placeholder="Search files..." class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md">
        <input type="text" v-model="tagFilter" @input="debounceSearch" placeholder="Filter by tag..." class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md">
      </div>
    </div>

    <!-- Uploader -->
    <div class="mb-8 bg-white p-6 rounded-lg shadow">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Upload New Media</h3>
        <AssetUploader @uploadComplete="fetchAssets(1)" />
    </div>

    <!-- Asset Grid -->
    <div class="bg-white px-4 py-5 border-b border-gray-200 sm:px-6 rounded-t-lg shadow">
        <h3 class="text-lg leading-6 font-medium text-gray-900">Media Library</h3>
    </div>
    
    <div v-if="loading" class="text-center py-10 bg-white shadow rounded-b-lg">
        <p class="text-gray-500">Loading assets...</p>
    </div>
    
    <div v-else-if="assets.length === 0" class="text-center py-10 bg-white shadow rounded-b-lg">
        <p class="text-gray-500">No assets found. Upload some media to get started.</p>
    </div>
    
    <ul v-else role="list" class="grid grid-cols-2 gap-x-4 gap-y-8 sm:grid-cols-3 sm:gap-x-6 lg:grid-cols-4 xl:gap-x-8 bg-white p-6 shadow rounded-b-lg">
      <li v-for="asset in assets" :key="asset.id" class="relative">
        <div class="group block w-full aspect-w-10 aspect-h-7 rounded-lg bg-gray-100 focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-offset-gray-100 focus-within:ring-indigo-500 overflow-hidden">
          <img v-if="asset.asset_type === 'image'" :src="asset.url" alt="" class="object-cover pointer-events-none group-hover:opacity-75 h-48 w-full">
          <div v-else class="flex items-center justify-center h-48 w-full bg-gray-200">
            <svg class="h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
               <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
          </div>
          <button type="button" @click="openEditModal(asset)" class="absolute top-2 left-2 bg-indigo-600 text-white rounded-full p-1 hover:bg-indigo-700 focus:outline-none z-10" title="Edit metadata">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
            </svg>
          </button>
          <button type="button" @click="deleteConfirm(asset)" class="absolute top-2 right-2 bg-red-600 text-white rounded-full p-1 hover:bg-red-700 focus:outline-none z-10" title="Delete">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>
        <p class="mt-2 block text-sm font-medium text-gray-900 truncate">{{ asset.title || asset.original_name }}</p>
        <p class="block text-xs text-gray-500">{{ formatBytes(asset.size_bytes) }} &middot; {{ asset.folder }}</p>
        <p v-if="asset.tags?.length" class="mt-1 flex flex-wrap gap-1">
          <span v-for="tag in asset.tags" :key="tag" class="text-xs bg-gray-100 text-gray-700 px-1.5 py-0.5 rounded">{{ tag }}</span>
        </p>
      </li>
    </ul>

    <!-- Edit Metadata Modal -->
    <div v-if="editing" class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
        <div @click="closeEditModal" class="fixed inset-0 bg-gray-500/75 transition-opacity"></div>
        <div class="relative z-10 flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
            <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <form @submit.prevent="saveMetadata">
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Edit asset</h3>
                        <div class="mb-4 flex items-center gap-3">
                            <img v-if="editing.asset_type === 'image'" :src="editing.url" class="h-20 w-20 object-cover rounded">
                            <div v-else class="h-20 w-20 bg-gray-200 rounded flex items-center justify-center text-gray-400">FILE</div>
                            <div>
                                <p class="text-sm font-medium text-gray-900">{{ editing.original_name }}</p>
                                <p class="text-xs text-gray-500">{{ formatBytes(editing.size_bytes) }} · {{ editing.folder }}</p>
                            </div>
                        </div>
                        <div class="space-y-3">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Title</label>
                                <input v-model="editForm.title" type="text" class="mt-1 block w-full border border-gray-300 rounded-md py-2 px-3 sm:text-sm">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Alt text</label>
                                <input v-model="editForm.alt_text" type="text" class="mt-1 block w-full border border-gray-300 rounded-md py-2 px-3 sm:text-sm" placeholder="Describe the image for accessibility">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Tags (comma-separated)</label>
                                <input v-model="tagsInput" type="text" class="mt-1 block w-full border border-gray-300 rounded-md py-2 px-3 sm:text-sm" placeholder="e.g. hero, featured, summer-sale">
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button type="submit" :disabled="savingEdit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 sm:ml-3 sm:w-auto sm:text-sm disabled:opacity-50">
                            {{ savingEdit ? 'Saving...' : 'Save' }}
                        </button>
                        <button @click="closeEditModal" type="button" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                            Cancel
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Pagination -->
    <div v-if="pagination.last_page > 1" class="mt-4 flex justify-between items-center bg-white p-4 shadow rounded-lg">
        <button @click="fetchAssets(pagination.current_page - 1)" :disabled="pagination.current_page === 1" class="px-4 py-2 border rounded-md disabled:opacity-50">Previous</button>
        <span class="text-sm text-gray-700">Page {{ pagination.current_page }} of {{ pagination.last_page }}</span>
        <button @click="fetchAssets(pagination.current_page + 1)" :disabled="pagination.current_page === pagination.last_page" class="px-4 py-2 border rounded-md disabled:opacity-50">Next</button>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from '../axios';
import AssetUploader from '../components/AssetUploader.vue';
import { useToast } from '../stores/toast';
import { useConfirm } from '../stores/confirm';

const toast = useToast();
const confirmDialog = useConfirm();

const assets = ref([]);
const pagination = ref({});
const loading = ref(true);
const searchQuery = ref('');
const tagFilter = ref('');
const folderFilter = ref('');
const folders = ref([]);
let searchTimeout = null;

const fetchFolders = async () => {
    try {
        const res = await axios.get('/assets/folders');
        folders.value = res.data.data || [];
    } catch (err) {
        console.error('Failed to load folders', err);
    }
};

const fetchAssets = async (page = 1) => {
    loading.value = true;
    try {
        const res = await axios.get('/assets', {
            params: {
                page,
                search: searchQuery.value,
                tag: tagFilter.value || undefined,
                folder: folderFilter.value || undefined,
                per_page: 12
            }
        });
        assets.value = res.data.data;
        pagination.value = res.data.pagination;
    } catch (err) {
        console.error('Failed to fetch assets', err);
    } finally {
        loading.value = false;
    }
};

onMounted(() => {
    fetchAssets(1);
    fetchFolders();
});

const debounceSearch = () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        fetchAssets(1);
    }, 500);
};

const deleteConfirm = async (asset) => {
    const ok = await confirmDialog.ask({
        title: 'Delete this asset?',
        message: `${asset.original_name} will be removed from the library and any sections that reference it will show a broken image.`,
        confirmLabel: 'Delete',
        variant: 'danger',
    });
    if (!ok) return;
    try {
        await axios.delete(`/assets/${asset.id}`);
        fetchAssets(pagination.value.current_page);
        toast.success('Asset deleted.');
    } catch (err) {
        toast.error('Failed to delete asset');
    }
};

// --- Metadata edit modal ---
const editing = ref(null);
const editForm = ref({ title: '', alt_text: '' });
const tagsInput = ref('');
const savingEdit = ref(false);

const openEditModal = (asset) => {
    editing.value = asset;
    editForm.value = {
        title: asset.title || '',
        alt_text: asset.alt_text || '',
    };
    tagsInput.value = Array.isArray(asset.tags) ? asset.tags.join(', ') : '';
};

const closeEditModal = () => {
    editing.value = null;
    savingEdit.value = false;
};

const saveMetadata = async () => {
    if (!editing.value) return;
    savingEdit.value = true;
    try {
        const tags = tagsInput.value
            .split(',')
            .map(t => t.trim())
            .filter(Boolean);
        await axios.put(`/assets/${editing.value.id}`, {
            title: editForm.value.title || null,
            alt_text: editForm.value.alt_text || null,
            tags,
        });
        closeEditModal();
        fetchAssets(pagination.value.current_page || 1);
        toast.success('Asset updated.');
    } catch (err) {
        toast.error(err.response?.data?.errors?.[0] || 'Failed to save metadata');
    } finally {
        savingEdit.value = false;
    }
};

const formatBytes = (bytes, decimals = 2) => {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const dm = decimals < 0 ? 0 : decimals;
    const sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(dm)) + ' ' + sizes[i];
};
</script>
