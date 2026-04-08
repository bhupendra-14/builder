<template>
  <div class="min-h-screen bg-gray-50 font-sans">
    <header v-if="!loading" class="bg-white shadow-sm sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 flex justify-between items-center">
            <router-link :to="{ name: 'public' }" class="text-xl font-bold tracking-tight text-gray-900 hover:opacity-80">
                {{ settingsStore.site_title }}
            </router-link>
            <nav class="hidden md:flex items-center gap-x-6">
                <a href="#" @click.prevent="scrollToTop" class="text-gray-600 hover:text-gray-900 font-medium">Home</a>
                <a
                    v-for="item in nav"
                    :key="item.anchor"
                    :href="`#${item.anchor}`"
                    class="text-gray-600 hover:text-gray-900 font-medium"
                >{{ item.label }}</a>
            </nav>
            <!-- Mobile nav toggle -->
            <button
                v-if="nav.length"
                type="button"
                class="md:hidden p-2 text-gray-600"
                @click="mobileNavOpen = !mobileNavOpen"
                aria-label="Toggle navigation"
            >
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="mobileNavOpen ? 'M6 18L18 6M6 6l12 12' : 'M4 6h16M4 12h16M4 18h16'" />
                </svg>
            </button>
        </div>
        <!-- Mobile nav drawer -->
        <div v-if="mobileNavOpen" class="md:hidden border-t border-gray-100 bg-white">
            <nav class="px-4 py-3 space-y-1">
                <a href="#" @click.prevent="scrollToTop(); mobileNavOpen = false" class="block py-2 text-gray-700 font-medium">Home</a>
                <a
                    v-for="item in nav"
                    :key="item.anchor"
                    :href="`#${item.anchor}`"
                    @click="mobileNavOpen = false"
                    class="block py-2 text-gray-700 font-medium"
                >{{ item.label }}</a>
            </nav>
        </div>
    </header>

    <main>
        <div v-if="loading" class="flex justify-center items-center h-screen">
            <p class="text-gray-500 animate-pulse">Loading experience...</p>
        </div>
        <FrontendRenderer v-else :sections="sections" />
    </main>

    <footer v-if="!loading" class="bg-gray-900 text-white mt-auto">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8 md:gap-12">
                <!-- Column 1 — Brand / About -->
                <div>
                    <p class="text-xl font-semibold tracking-tight" :style="{ color: 'var(--brand-color)' }">
                        {{ settingsStore.site_title }}
                    </p>
                    <p v-if="settingsStore.site_tagline" class="mt-1 text-sm text-gray-400">
                        {{ settingsStore.site_tagline }}
                    </p>
                    <p v-if="settingsStore.footer_about" class="mt-4 text-sm text-gray-300 leading-relaxed max-w-md">
                        {{ settingsStore.footer_about }}
                    </p>
                </div>

                <!-- Column 2 — Contact -->
                <div>
                    <h3 class="text-sm font-semibold uppercase tracking-wide text-gray-200">Get in touch</h3>
                    <ul class="mt-4 space-y-3 text-sm text-gray-300">
                        <li v-if="settingsStore.footer_contact_email" class="flex items-start gap-3">
                            <svg class="h-5 w-5 text-gray-500 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                            </svg>
                            <a :href="`mailto:${settingsStore.footer_contact_email}`" class="hover:text-white">
                                {{ settingsStore.footer_contact_email }}
                            </a>
                        </li>
                        <li v-if="settingsStore.footer_contact_phone" class="flex items-start gap-3">
                            <svg class="h-5 w-5 text-gray-500 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                            </svg>
                            <a :href="`tel:${settingsStore.footer_contact_phone.replace(/[^+\d]/g, '')}`" class="hover:text-white">
                                {{ settingsStore.footer_contact_phone }}
                            </a>
                        </li>
                        <li v-if="settingsStore.footer_contact_address" class="flex items-start gap-3">
                            <svg class="h-5 w-5 text-gray-500 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                            <span>{{ settingsStore.footer_contact_address }}</span>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Bottom bar -->
            <div class="mt-10 pt-6 border-t border-gray-800 text-center text-sm text-gray-500">
                &copy; {{ new Date().getFullYear() }} {{ settingsStore.site_title }}. All rights reserved.
            </div>
        </div>
    </footer>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from '../axios';
import FrontendRenderer from '../components/FrontendRenderer.vue';
import { useSettingsStore } from '../stores/settings';

const sections = ref([]);
const nav = ref([]);
const loading = ref(true);
const mobileNavOpen = ref(false);
const settingsStore = useSettingsStore();

onMounted(async () => {
    try {
        const res = await axios.get('/public/live');
        // The live endpoint returns { sections, settings, nav }
        sections.value = res.data.data.sections || [];
        nav.value = res.data.data.nav || [];
        if (res.data.data.settings) settingsStore.apply(res.data.data.settings);
    } catch (err) {
        console.error('Failed to load public site content', err);
    } finally {
        loading.value = false;
    }
});

const scrollToTop = () => {
    window.scrollTo({ top: 0, behavior: 'smooth' });
};
</script>
