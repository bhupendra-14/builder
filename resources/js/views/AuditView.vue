<template>
  <div class="py-6 px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto">
    <div class="mb-6 md:flex md:items-center md:justify-between">
      <div class="flex-1 min-w-0">
        <h2 class="text-2xl font-bold leading-7 text-gray-900 sm:text-3xl sm:truncate">
          System Audit Logs
        </h2>
        <p class="mt-1 text-sm text-gray-500">Security history of all actions performed inside the CMS.</p>
      </div>
    </div>

    <AppDataTable 
        :columns="columns" 
        :data="logs" 
        :loading="loading" 
        :pagination="pagination"
        :actions="false"
        @page="fetchLogs"
    >
        <template #cell-user_id="{ item }">
            {{ item.user?.name || 'System / Unknown' }}
        </template>
        <template #cell-action="{ item }">
            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-800 capitalize">
                {{ item.action.replace(/_/g, ' ') }}
            </span>
        </template>
        <template #cell-created_at="{ item }">
            {{ new Date(item.created_at).toLocaleString() }}
        </template>
    </AppDataTable>

  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from '../axios';
import AppDataTable from '../components/common/AppDataTable.vue';

const logs = ref([]);
const loading = ref(true);
const pagination = ref({});

const columns = [
    { key: 'created_at', label: 'Timestamp' },
    { key: 'user_id', label: 'User' },
    { key: 'action', label: 'Action Taken' },
    { key: 'target_type', label: 'Module' },
    { key: 'ip', label: 'IP Address' }
];

const fetchLogs = async (page = 1) => {
    loading.value = true;
    try {
        const res = await axios.get(`/audit?page=${page}`);
        logs.value = res.data.data;
        pagination.value = res.data.pagination;
    } catch (err) {
        console.error('Failed to load audit logs');
    } finally {
        loading.value = false;
    }
};

onMounted(() => fetchLogs());
</script>
