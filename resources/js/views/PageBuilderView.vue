<template>
  <div class="py-6 px-4 sm:px-6 lg:px-8">
    <div class="mb-6 md:flex md:items-center md:justify-between">
      <div class="flex-1 min-w-0">
        <h2 class="text-2xl font-bold leading-7 text-gray-900 sm:text-3xl sm:truncate">
          Page Builder Overview
        </h2>
        <p class="mt-1 text-sm text-gray-500">Manage structure, reorder sections, and edit content.</p>
      </div>
      <div class="mt-4 flex md:mt-0 md:ml-4 space-x-3">
        <router-link :to="{ name: 'publish' }" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
          Publish
        </router-link>
        <button @click="showAddModal = true" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700">
          Add New Section
        </button>
      </div>
    </div>

    <!-- Sections List (Draggable) -->
    <div class="bg-white shadow sm:rounded-md mb-8">
      <ul role="list" class="divide-y divide-gray-200">
        <VueDraggableNext v-model="sections" class="w-full" handle=".handle" @change="onReorder">
            <li v-for="section in sections" :key="section.id" class="relative bg-white hover:bg-gray-50 py-5 px-4 sm:px-6 transition-colors">
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <div class="handle cursor-move text-gray-400 hover:text-gray-600 mr-4">
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                               <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-indigo-600 truncate">{{ section.label }}</p>
                            <p class="text-sm text-gray-500 mt-1 flex items-center flex-wrap gap-2">
                                Type: <span class="uppercase text-xs bg-gray-100 rounded px-2 py-0.5">{{ section.type }}</span>
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full" :class="section.status === 'live' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'">
                                    {{ section.status }}
                                </span>
                                <span v-if="section.show_in_nav" class="px-2 inline-flex text-xs leading-5 font-medium rounded-full bg-indigo-100 text-indigo-800">
                                    in nav
                                </span>
                            </p>
                        </div>
                    </div>
                    <!-- Actions -->
                    <div class="flex items-center space-x-4">
                        <button @click="toggleEnabled(section)" class="text-sm font-medium" :class="section.enabled ? 'text-green-600 hover:text-green-900' : 'text-gray-400 hover:text-gray-500'">
                            {{ section.enabled ? 'Enabled' : 'Disabled' }}
                        </button>

                        <router-link :to="{ name: 'builder.edit', params: { id: section.id }}" class="text-indigo-600 hover:text-indigo-900 font-medium text-sm">
                            Edit Content
                        </router-link>

                        <button @click="duplicateSection(section)" class="text-gray-600 hover:text-gray-900 font-medium text-sm">
                            Duplicate
                        </button>

                        <button @click="deleteConfirm(section)" class="text-red-600 hover:text-red-900 font-medium text-sm">
                            Delete
                        </button>
                    </div>
                </div>

                <!-- Nav settings sub-row -->
                <div class="mt-3 ml-10 flex items-center gap-3 text-sm">
                    <label class="inline-flex items-center text-gray-600 cursor-pointer">
                        <input
                            type="checkbox"
                            :checked="section.show_in_nav"
                            @change="updateNav(section, { show_in_nav: $event.target.checked })"
                            class="rounded border-gray-300 text-indigo-600 mr-2"
                        >
                        Show in navigation
                    </label>
                    <input
                        v-if="section.show_in_nav"
                        type="text"
                        :value="section.nav_label || ''"
                        @change="updateNav(section, { nav_label: $event.target.value })"
                        :placeholder="section.label"
                        maxlength="60"
                        class="border border-gray-300 rounded-md text-sm py-1 px-2 w-48"
                    >
                </div>
            </li>
        </VueDraggableNext>
        <li v-if="sections.length === 0 && !loading" class="text-center py-6 text-gray-500">
            No sections added yet. Click "Add New Section" to start building.
        </li>
      </ul>
    </div>
    
    <!-- Add Modal Placeholder -->
    <div v-if="showAddModal" class="fixed inset-0 z-50 overflow-y-auto" aria-modal="true">
        <div class="fixed inset-0 bg-gray-500/75 transition-opacity" @click="showAddModal = false"></div>
        <div class="relative z-10 flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
            <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <!-- Add Form Content Here -->
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Add New Section</h3>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700">Section Label</label>
                        <input type="text" v-model="newSection.label" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700">Section Type</label>
                        <select v-model="newSection.type" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                            <option value="hero">Hero Header</option>
                            <option value="text">Rich Text</option>
                            <option value="image_text">Image + Text</option>
                            <option value="gallery">Gallery Grid</option>
                            <option value="carousel">Carousel / Slider</option>
                            <option value="tabs">Tabs</option>
                            <option value="accordion">Accordion / FAQ</option>
                            <option value="cta">Call to Action</option>
                            <option value="video">Video</option>
                            <option value="feature_grid">Feature Grid</option>
                            <option value="cards">Cards</option>
                            <option value="testimonials">Testimonials</option>
                            <option value="stats">Stats Counter</option>
                            <option value="promo_banner">Promo Banner</option>
                        </select>
                    </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button @click="createSection" type="button" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none sm:ml-3 sm:w-auto sm:text-sm">Save</button>
                    <button @click="showAddModal = false" type="button" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">Cancel</button>
                </div>
            </div>
        </div>
    </div>

  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { VueDraggableNext } from 'vue-draggable-next';
import axios from '../axios';
import { useToast } from '../stores/toast';
import { useConfirm } from '../stores/confirm';

const toast = useToast();
const confirmDialog = useConfirm();

const sections = ref([]);
const loading = ref(true);
const showAddModal = ref(false);
const newSection = ref({ label: '', type: 'hero', order: 0 });

const fetchSections = async () => {
    loading.value = true;
    try {
        const res = await axios.get('/sections');
        sections.value = res.data.data;
    } catch (err) {
        console.error('Failed to load sections');
    } finally {
        loading.value = false;
    }
};

onMounted(fetchSections);

const onReorder = async () => {
    const reordered = sections.value.map((s, index) => ({ id: s.id, order: index }));
    try {
        await axios.post('/sections/reorder', { sections: reordered });
    } catch (err) {
        toast.error('Failed to save order');
        fetchSections();
    }
};

const createSection = async () => {
    newSection.value.order = sections.value.length;
    try {
        await axios.post('/sections', newSection.value);
        showAddModal.value = false;
        newSection.value = { label: '', type: 'hero', order: 0 };
        fetchSections();
        toast.success('Section created.');
    } catch (err) {
        toast.error('Failed to create section');
    }
};

const toggleEnabled = async (section) => {
    try {
        await axios.put(`/sections/${section.id}`, { enabled: !section.enabled });
        section.enabled = !section.enabled;
    } catch (err) {
        toast.error('Update failed');
    }
};

const updateNav = async (section, patch) => {
    try {
        await axios.put(`/sections/${section.id}`, patch);
        Object.assign(section, patch);
    } catch (err) {
        toast.error('Failed to update navigation settings');
    }
};

const duplicateSection = async (section) => {
    try {
        await axios.post(`/sections/${section.id}/duplicate`);
        fetchSections();
        toast.success('Section duplicated.');
    } catch (err) {
        toast.error('Duplicate failed');
    }
};

const deleteConfirm = async (section) => {
    const ok = await confirmDialog.ask({
        title: `Delete "${section.label}"?`,
        message: 'The section will be hidden immediately. You\'ll need to publish to Live to remove it from the public site.',
        confirmLabel: 'Delete',
        variant: 'danger',
    });
    if (!ok) return;
    try {
        await axios.delete(`/sections/${section.id}`);
        fetchSections();
        toast.success('Section deleted.');
    } catch (err) {
        toast.error('Delete failed');
    }
};
</script>
