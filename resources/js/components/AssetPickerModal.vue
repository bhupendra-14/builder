<template>
  <div v-if="isOpen" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="fixed inset-0 bg-gray-500/75 transition-opacity" @click="$emit('close')" aria-hidden="true"></div>
    <div class="relative z-10 flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
      <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

      <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full">
        <!-- Header -->
        <div class="bg-gray-50 px-4 py-3 border-b border-gray-200 sm:px-6 flex justify-between items-center">
            <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                {{ type === 'image' ? 'Select Image' : type === 'video' ? 'Select Video' : 'Select Media' }}
            </h3>
            <button @click="$emit('close')" type="button" class="text-gray-400 hover:text-gray-500 focus:outline-none">
                <span class="sr-only">Close</span>
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
        
        <!-- Body -->
        <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4 max-h-[70vh] overflow-y-auto">
            
            <div class="mb-4 flex items-center justify-between">
                <input type="text" v-model="searchQuery" @input="debounceSearch" placeholder="Search files..." class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-1/2 sm:text-sm border-gray-300 rounded-md">
            </div>

            <div v-if="loading" class="text-center py-10 text-gray-500">Loading assets...</div>
            <div v-else-if="errorMessage" class="text-center py-10">
                <p class="text-red-600 mb-3">{{ errorMessage }}</p>
                <button @click="fetchAssets(pagination.current_page || 1)" type="button" class="text-sm px-3 py-1 border border-gray-300 rounded-md hover:bg-gray-50">Retry</button>
            </div>
            <div v-else-if="assets.length === 0" class="text-center py-10 text-gray-500">No assets found.</div>

            <ul v-else role="list" class="grid grid-cols-2 gap-x-4 gap-y-8 sm:grid-cols-3 md:grid-cols-4">
                <li v-for="asset in assets" :key="asset.id" @click="selectAsset(asset)" class="relative cursor-pointer">
                    <div class="group block w-full aspect-w-10 aspect-h-7 rounded-lg bg-gray-100 overflow-hidden" :class="selectedAsset?.id === asset.id ? 'ring-2 ring-offset-2 ring-indigo-500' : 'focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-indigo-500'">
                        <img v-if="asset.asset_type === 'image'" :src="asset.url" alt="" class="object-cover group-hover:opacity-75 h-32 w-full">
                        <div v-else class="flex items-center justify-center h-32 w-full bg-gray-200 group-hover:opacity-75">
                            <svg class="h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                        </div>
                    </div>
                </li>
            </ul>

            <!-- Pagination -->
            <div v-if="pagination.last_page > 1" class="mt-4 flex justify-between items-center py-2">
                <button @click="fetchAssets(pagination.current_page - 1)" :disabled="pagination.current_page === 1" class="text-sm px-3 py-1 border rounded-md disabled:opacity-50">Previous</button>
                <span class="text-sm text-gray-700">Page {{ pagination.current_page }} of {{ pagination.last_page }}</span>
                <button @click="fetchAssets(pagination.current_page + 1)" :disabled="pagination.current_page === pagination.last_page" class="text-sm px-3 py-1 border rounded-md disabled:opacity-50">Next</button>
            </div>
        </div>
        
        <!-- Footer -->
        <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse border-t border-gray-200">
            <button @click="confirmSelection" :disabled="!selectedAsset" type="button" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:ml-3 sm:w-auto sm:text-sm disabled:opacity-50">
            Select
            </button>
            <button @click="$emit('close')" type="button" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
            Cancel
            </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue';
import axios from '../axios';

const props = defineProps({
    isOpen: Boolean,
    // Optional asset type filter: 'image' | 'video' | 'document' | null
    type: { type: String, default: null },
});

const emit = defineEmits(['close', 'select']);

const assets = ref([]);
const pagination = ref({});
const loading = ref(false);
const errorMessage = ref('');
const searchQuery = ref('');
const selectedAsset = ref(null);
let searchTimeout = null;

const fetchAssets = async (page = 1) => {
    loading.value = true;
    errorMessage.value = '';
    try {
        const params = { page, search: searchQuery.value, per_page: 8 };
        if (props.type) params.type = props.type;
        const res = await axios.get('/assets', { params });
        assets.value = res.data.data;
        pagination.value = res.data.pagination;
    } catch (err) {
        assets.value = [];
        const apiErrors = err?.response?.data?.errors;
        errorMessage.value = Array.isArray(apiErrors)
            ? apiErrors[0]
            : (apiErrors || 'Failed to load assets. Please try again.');
    } finally {
        loading.value = false;
    }
};

watch(() => props.isOpen, (newVal) => {
    if (newVal) {
        selectedAsset.value = null; // Reset on open
        fetchAssets(1);
    }
});

const debounceSearch = () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => fetchAssets(1), 500);
};

const selectAsset = (asset) => {
    selectedAsset.value = asset;
};

const confirmSelection = () => {
    if (selectedAsset.value) {
        emit('select', selectedAsset.value);
        emit('close');
    }
};
</script>
