<template>
  <div class="min-h-screen bg-gray-50 font-sans relative">
    
    <!-- Preview Banner -->
    <div class="bg-yellow-400 text-yellow-900 px-4 py-2 text-center text-sm font-semibold tracking-wider sticky top-0 z-[100] shadow-md flex justify-center items-center">
        <span>PREVIEW MODE - You are viewing draft/dark content. This is not visible to the public.</span>
    </div>

    <!-- Website Emulation -->
    <header class="bg-white shadow-sm relative z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 flex justify-between items-center">
            <h1 class="text-xl font-bold tracking-tight text-gray-900">
                {{ settingsStore.site_title }} <span class="text-xs ml-2 text-yellow-600">(Preview)</span>
            </h1>
            <nav class="hidden md:flex items-center gap-x-6">
                <a
                    v-for="item in nav"
                    :key="item.anchor"
                    :href="`#${item.anchor}`"
                    class="text-gray-600 hover:text-gray-900 font-medium"
                >{{ item.label }}</a>
            </nav>
        </div>
    </header>

    <main>
        <div v-if="loading" class="flex justify-center items-center h-[80vh]">
            <p class="text-gray-500 animate-pulse">Loading preview...</p>
        </div>
        <div v-else-if="error" class="flex justify-center items-center h-[80vh] text-red-500 font-medium">
            {{ error }}
        </div>
        <FrontendRenderer v-else :sections="sections" />
    </main>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRoute } from 'vue-router';
import axios from '../axios';
import FrontendRenderer from '../components/FrontendRenderer.vue';
import { useSettingsStore } from '../stores/settings';

const route = useRoute();
const sections = ref([]);
const nav = ref([]);
const loading = ref(true);
const error = ref('');
const settingsStore = useSettingsStore();

onMounted(async () => {
    // Add noindex meta tag dynamically
    let meta = document.createElement('meta');
    meta.name = "robots";
    meta.content = "noindex, nofollow";
    document.getElementsByTagName('head')[0].appendChild(meta);

    // Prefer URL token; fall back to localStorage (set from the Publish screen).
    const token = route.query.token || localStorage.getItem('preview_token');
    if (!token) {
        error.value = 'Missing preview token. Go to Admin → Publish and paste your PREVIEW_TOKEN.';
        loading.value = false;
        return;
    }

    try {
        const res = await axios.get('/public/preview', { params: { token } });
        sections.value = res.data.data.sections || [];
        nav.value = res.data.data.nav || [];
        if (res.data.data.settings) settingsStore.apply(res.data.data.settings);
    } catch (err) {
        error.value = err.response?.data?.message || 'Access Denied / Invalid Token';
        console.error('Failed to load preview content', err);
    } finally {
        loading.value = false;
    }
});
</script>
