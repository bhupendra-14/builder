<template>
  <div class="py-6 px-4 sm:px-6 lg:px-8 max-w-6xl mx-auto">
    <div class="mb-6">
      <h2 class="text-2xl font-bold leading-7 text-gray-900 sm:text-3xl">Publish</h2>
      <p class="mt-1 text-sm text-gray-500">Promote your draft content to the Dark preview environment, then to the public Live site.</p>
    </div>

    <!-- Current status grid -->
    <div class="bg-white shadow sm:rounded-lg mb-8">
      <div class="px-4 py-5 sm:p-6">
        <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Current section status</h3>
        <div v-if="loadingSections" class="text-sm text-gray-500">Loading sections...</div>
        <div v-else-if="sections.length === 0" class="text-sm text-gray-500 italic">
          No sections to publish. Go to Page Builder and add some first.
        </div>
        <ul v-else class="divide-y divide-gray-200">
          <li v-for="s in sections" :key="s.id" class="py-3 flex items-center justify-between">
            <div class="flex items-center">
              <span class="text-sm font-medium text-gray-900">{{ s.label }}</span>
              <span class="ml-2 text-xs uppercase text-gray-500">{{ s.type }}</span>
              <span v-if="!s.enabled" class="ml-2 text-xs text-red-600">disabled</span>
            </div>
            <div class="flex items-center space-x-2">
              <span class="px-2 py-1 text-xs rounded" :class="badge(s.status)">{{ statusLabel(s) }}</span>
            </div>
          </li>
        </ul>
        <div v-if="sections.length > 0" class="mt-4 pt-4 border-t border-gray-100 text-xs text-gray-500">
          <p v-if="countDraft > 0">{{ countDraft }} section(s) have draft changes not yet in Dark.</p>
          <p v-if="countDark > 0">{{ countDark }} section(s) are in Dark but not yet Live.</p>
          <p v-if="countDraft === 0 && countDark === 0 && sections.length > 0">Everything is published to Live.</p>
        </div>
      </div>
    </div>

    <!-- Publish actions -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
      <!-- Dark card -->
      <div class="bg-white shadow sm:rounded-lg p-6">
        <div class="flex items-center mb-3">
          <div class="h-3 w-3 rounded-full bg-yellow-400 mr-2"></div>
          <h3 class="text-lg font-medium text-gray-900">Publish to Dark (preview)</h3>
        </div>
        <p class="text-sm text-gray-500 mb-4">
          Promotes every enabled section's current draft into the Dark preview environment.
          Share the dark link with reviewers before publishing to Live.
        </p>
        <div class="mb-3">
          <label class="block text-xs font-medium text-gray-700 mb-1">Release notes (optional)</label>
          <textarea v-model="darkNotes" rows="2" class="block w-full border border-gray-300 rounded-md py-2 px-3 text-sm" placeholder="What changed in this preview?"></textarea>
        </div>
        <div class="mb-4">
          <label class="block text-xs font-medium text-gray-700 mb-1">Schedule for later (optional)</label>
          <input type="datetime-local" v-model="darkSchedule" class="block w-full border border-gray-300 rounded-md py-2 px-3 text-sm">
        </div>
        <button
          type="button"
          :disabled="publishing === 'dark'"
          @click="runPublish('dark')"
          class="w-full inline-flex justify-center py-2 px-4 border border-transparent rounded-md text-sm font-medium text-white bg-yellow-500 hover:bg-yellow-600 disabled:opacity-50"
        >
          {{ publishing === 'dark' ? 'Publishing...' : (darkSchedule ? 'Schedule Dark publish' : 'Publish to Dark now') }}
        </button>

        <!-- Preview token management -->
        <div class="mt-4 pt-4 border-t border-gray-100">
          <label class="block text-xs font-medium text-gray-700 mb-1">Dark preview token</label>
          <div class="flex items-center gap-2">
            <input
              v-model="previewToken"
              :type="showToken ? 'text' : 'password'"
              @change="savePreviewToken"
              placeholder="Paste the PREVIEW_TOKEN value"
              class="flex-1 block border border-gray-300 rounded-md py-2 px-3 text-sm"
            >
            <button type="button" @click="showToken = !showToken" class="text-xs text-gray-500 hover:text-gray-700">
              {{ showToken ? 'Hide' : 'Show' }}
            </button>
          </div>
          <p class="text-xs text-gray-400 mt-1">Stored in your browser only. Ask your developer for the server's <code class="font-mono">PREVIEW_TOKEN</code> value.</p>
        </div>

        <button
          type="button"
          :disabled="!previewToken"
          @click="openPreview"
          class="mt-3 w-full inline-flex justify-center py-2 px-4 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 disabled:opacity-50"
        >
          Open dark preview &rarr;
        </button>
      </div>

      <!-- Live card -->
      <div class="bg-white shadow sm:rounded-lg p-6">
        <div class="flex items-center mb-3">
          <div class="h-3 w-3 rounded-full bg-green-500 mr-2"></div>
          <h3 class="text-lg font-medium text-gray-900">Publish to Live (public)</h3>
        </div>
        <p class="text-sm text-gray-500 mb-4">
          Promotes Dark → Live. Any section that is currently disabled or deleted
          will have its live content cleared when you publish.
        </p>
        <div class="mb-3">
          <label class="block text-xs font-medium text-gray-700 mb-1">Release notes (optional)</label>
          <textarea v-model="liveNotes" rows="2" class="block w-full border border-gray-300 rounded-md py-2 px-3 text-sm" placeholder="Summary for the audit trail"></textarea>
        </div>
        <div class="mb-4">
          <label class="block text-xs font-medium text-gray-700 mb-1">Schedule for later (optional)</label>
          <input type="datetime-local" v-model="liveSchedule" class="block w-full border border-gray-300 rounded-md py-2 px-3 text-sm">
        </div>
        <button
          type="button"
          :disabled="publishing === 'live'"
          @click="confirmLive"
          class="w-full inline-flex justify-center py-2 px-4 border border-transparent rounded-md text-sm font-medium text-white bg-green-600 hover:bg-green-700 disabled:opacity-50"
        >
          {{ publishing === 'live' ? 'Publishing...' : (liveSchedule ? 'Schedule Live publish' : 'Publish to Live now') }}
        </button>
      </div>
    </div>

    <!-- History -->
    <div class="bg-white shadow sm:rounded-lg">
      <div class="px-4 py-5 sm:p-6">
        <div class="flex items-center justify-between mb-4">
          <h3 class="text-lg leading-6 font-medium text-gray-900">Publish history</h3>
          <select v-model="historyFilter" @change="fetchHistory()" class="text-sm border border-gray-300 rounded-md py-1 px-2">
            <option value="">All environments</option>
            <option value="dark">Dark only</option>
            <option value="live">Live only</option>
          </select>
        </div>
        <div v-if="loadingHistory" class="text-sm text-gray-500">Loading history...</div>
        <div v-else-if="history.length === 0" class="text-sm text-gray-500 italic">No publishes yet.</div>
        <ul v-else class="divide-y divide-gray-200">
          <li v-for="h in history" :key="h.id" class="py-3">
            <div class="flex items-start justify-between">
              <div>
                <div class="flex items-center gap-2">
                  <span class="px-2 py-0.5 text-xs rounded font-medium" :class="h.environment === 'live' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'">
                    {{ h.environment }}
                  </span>
                  <span class="px-2 py-0.5 text-xs rounded font-medium" :class="statusBadgeClass(h.status)">
                    {{ h.status || 'completed' }}
                  </span>
                  <span class="text-sm text-gray-500">by {{ h.user?.name || 'Unknown' }}</span>
                </div>
                <p v-if="h.release_notes" class="mt-1 text-sm text-gray-700">{{ h.release_notes }}</p>
                <p v-if="h.error" class="mt-1 text-sm text-red-600">Error: {{ h.error }}</p>
                <p class="mt-1 text-xs text-gray-400">
                  {{ formatDate(h.created_at) }}
                  <template v-if="h.scheduled_at"> &middot; scheduled for {{ formatDate(h.scheduled_at) }}</template>
                </p>
              </div>
            </div>
          </li>
        </ul>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import axios from '../axios';
import { useToast } from '../stores/toast';
import { useConfirm } from '../stores/confirm';

const toast = useToast();
const confirmDialog = useConfirm();

const sections = ref([]);
const history = ref([]);
const loadingSections = ref(true);
const loadingHistory = ref(true);
const publishing = ref(null); // 'dark' | 'live' | null
const historyFilter = ref('');

const darkNotes = ref('');
const liveNotes = ref('');
const darkSchedule = ref('');
const liveSchedule = ref('');

// Dark preview token — persisted in localStorage per browser so admins
// don't have to paste the PREVIEW_TOKEN on every visit.
const previewToken = ref(localStorage.getItem('preview_token') || '');
const showToken = ref(false);

const savePreviewToken = () => {
  if (previewToken.value) localStorage.setItem('preview_token', previewToken.value);
  else localStorage.removeItem('preview_token');
};

const fetchSections = async () => {
  loadingSections.value = true;
  try {
    const res = await axios.get('/sections');
    sections.value = res.data.data;
  } catch (err) {
    console.error('Failed to load sections', err);
  } finally {
    loadingSections.value = false;
  }
};

const fetchHistory = async () => {
  loadingHistory.value = true;
  try {
    const res = await axios.get('/publish/history', {
      params: historyFilter.value ? { environment: historyFilter.value } : {}
    });
    history.value = res.data.data;
  } catch (err) {
    console.error('Failed to load publish history', err);
  } finally {
    loadingHistory.value = false;
  }
};

const runPublish = async (env) => {
  publishing.value = env;
  try {
    const payload = { environment: env };
    const notes = env === 'dark' ? darkNotes.value : liveNotes.value;
    const schedule = env === 'dark' ? darkSchedule.value : liveSchedule.value;
    if (notes) payload.notes = notes;
    if (schedule) payload.scheduled_at = new Date(schedule).toISOString();

    const res = await axios.post('/publish', payload);
    toast.success(res.data.message || `Published to ${env}.`);

    // Reset form for that environment
    if (env === 'dark') { darkNotes.value = ''; darkSchedule.value = ''; }
    else { liveNotes.value = ''; liveSchedule.value = ''; }

    await Promise.all([fetchSections(), fetchHistory()]);
  } catch (err) {
    const apiErr = err.response?.data?.errors;
    toast.error(Array.isArray(apiErr) ? apiErr[0] : (apiErr || `Publish to ${env} failed.`));
  } finally {
    publishing.value = null;
  }
};

const confirmLive = async () => {
  const ok = await confirmDialog.ask({
    title: liveSchedule.value ? 'Schedule this Live publish?' : 'Publish to Live now?',
    message: liveSchedule.value
      ? 'A pending publish will be created and run automatically at the scheduled time.'
      : 'This will replace the current public website with your latest content. Visible to everyone immediately.',
    confirmLabel: liveSchedule.value ? 'Schedule' : 'Publish to Live',
    variant: 'danger',
  });
  if (ok) runPublish('live');
};

const openPreview = () => {
  if (!previewToken.value) return;
  const url = `/preview?token=${encodeURIComponent(previewToken.value)}`;
  window.open(url, '_blank');
};

const countDraft = computed(() => sections.value.filter(s => s.status === 'draft' && s.enabled).length);
const countDark = computed(() => sections.value.filter(s => s.status === 'dark' && s.enabled).length);

const statusLabel = (s) => {
  if (!s.enabled) return 'disabled';
  return s.status || 'draft';
};

const badge = (status) => {
  switch (status) {
    case 'live': return 'bg-green-100 text-green-800';
    case 'dark': return 'bg-yellow-100 text-yellow-800';
    default: return 'bg-gray-100 text-gray-700';
  }
};

const statusBadgeClass = (status) => {
  switch (status) {
    case 'pending': return 'bg-blue-100 text-blue-800';
    case 'failed': return 'bg-red-100 text-red-800';
    default: return 'bg-gray-100 text-gray-700';
  }
};

const formatDate = (d) => d ? new Date(d).toLocaleString() : '';

onMounted(() => {
  fetchSections();
  fetchHistory();
});
</script>
