<template>
  <div class="py-6 px-4 sm:px-6 lg:px-8 max-w-3xl mx-auto">
    <div class="mb-6 md:flex md:items-center md:justify-between">
      <div class="flex-1 min-w-0">
        <h2 class="text-2xl font-bold leading-7 text-gray-900 sm:text-3xl sm:truncate">
          Global Settings
        </h2>
        <p class="mt-1 text-sm text-gray-500">Configure global website constraints and SEO markers.</p>
      </div>
    </div>

    <div class="bg-white shadow sm:rounded-lg mb-6">
        <div class="px-4 py-5 sm:p-6 text-center text-gray-500 py-10" v-if="loading">
            Loading configuration...
        </div>
        <div class="px-4 py-5 sm:p-6" v-else>
            <form @submit.prevent="saveSettings" class="space-y-8">
                <!-- General -->
                <section>
                    <h3 class="text-base font-semibold text-gray-900 mb-4">General</h3>
                    <div class="grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-6">
                        <div class="sm:col-span-6">
                            <label class="block text-sm font-medium text-gray-700">Site Title</label>
                            <input type="text" v-model="settings.site_title" class="mt-1 block w-full border border-gray-300 rounded-md py-2 px-3 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                        </div>
                        <div class="sm:col-span-6">
                            <label class="block text-sm font-medium text-gray-700">Site Tagline</label>
                            <input type="text" v-model="settings.site_tagline" class="mt-1 block w-full border border-gray-300 rounded-md py-2 px-3 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                        </div>
                        <div class="sm:col-span-6">
                            <label class="block text-sm font-medium text-gray-700">Global Meta Description</label>
                            <textarea rows="3" v-model="settings.meta_description" class="mt-1 block w-full border border-gray-300 rounded-md py-2 px-3 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"></textarea>
                        </div>
                        <div class="sm:col-span-6">
                            <label class="block text-sm font-medium text-gray-700">Primary Brand Color</label>
                            <input type="color" v-model="settings.primary_color" class="mt-1 h-10 block w-full border border-gray-300 rounded-md py-1 px-2 focus:ring-indigo-500 sm:text-sm cursor-pointer">
                        </div>
                    </div>
                </section>

                <!-- Footer -->
                <section class="pt-6 border-t border-gray-200">
                    <h3 class="text-base font-semibold text-gray-900 mb-1">Footer</h3>
                    <p class="text-xs text-gray-500 mb-4">These appear in the two-column footer on the public site.</p>
                    <div class="grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-6">
                        <div class="sm:col-span-6">
                            <label class="block text-sm font-medium text-gray-700">Column 1 — About</label>
                            <textarea rows="3" v-model="settings.footer_about" placeholder="Short description of your business" class="mt-1 block w-full border border-gray-300 rounded-md py-2 px-3 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"></textarea>
                            <p class="text-xs text-gray-400 mt-1">Shown on the left side of the footer under your site title.</p>
                        </div>
                        <div class="sm:col-span-6">
                            <label class="block text-sm font-medium text-gray-700">Column 2 — Contact email</label>
                            <input type="email" v-model="settings.footer_contact_email" placeholder="hello@example.com" class="mt-1 block w-full border border-gray-300 rounded-md py-2 px-3 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                        </div>
                        <div class="sm:col-span-3">
                            <label class="block text-sm font-medium text-gray-700">Column 2 — Phone</label>
                            <input type="text" v-model="settings.footer_contact_phone" placeholder="+1 (555) 123-4567" class="mt-1 block w-full border border-gray-300 rounded-md py-2 px-3 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                        </div>
                        <div class="sm:col-span-3">
                            <label class="block text-sm font-medium text-gray-700">Column 2 — Address</label>
                            <input type="text" v-model="settings.footer_contact_address" placeholder="123 Main St, City, ZIP" class="mt-1 block w-full border border-gray-300 rounded-md py-2 px-3 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                        </div>
                    </div>
                </section>

                <div class="pt-5 border-t border-gray-200">
                    <div class="flex justify-end">
                        <button type="submit" :disabled="saving" class="ml-3 inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            {{ saving ? 'Saving...' : 'Save Configuration' }}
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from '../axios';
import { useSettingsStore } from '../stores/settings';
import { useToast } from '../stores/toast';

const settingsStore = useSettingsStore();
const toast = useToast();

const settings = ref({
    site_title: '',
    site_tagline: '',
    meta_description: '',
    primary_color: '#4f46e5',
    footer_about: '',
    footer_contact_email: '',
    footer_contact_phone: '',
    footer_contact_address: '',
});
const loading = ref(true);
const saving = ref(false);

const fetchSettings = async () => {
    loading.value = true;
    try {
        const res = await axios.get('/settings');
        // Merge into the form so missing keys keep their defaults
        settings.value = { ...settings.value, ...res.data.data };
    } catch (err) {
        toast.error('Failed to load settings');
    } finally {
        loading.value = false;
    }
};

const saveSettings = async () => {
    saving.value = true;
    try {
        await axios.post('/settings', { settings: settings.value });
        // Update the global store so the change is reflected everywhere
        // (sidebar brand, document title, brand color CSS var, etc.) without
        // requiring a page reload.
        settingsStore.apply(settings.value);
        toast.success('Settings saved.');
    } catch (err) {
        toast.error('Failed to save settings');
    } finally {
        saving.value = false;
    }
};

onMounted(() => fetchSettings());
</script>
