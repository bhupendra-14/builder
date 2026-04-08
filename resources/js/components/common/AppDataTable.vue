<template>
  <div class="flex flex-col mb-8">
    <div class="-my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
      <div class="py-2 align-middle inline-block min-w-full sm:px-6 lg:px-8">
        <div class="shadow overflow-hidden border-b border-gray-200 sm:rounded-lg">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th v-for="col in columns" :key="col.key" scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  {{ col.label }}
                </th>
                <th v-if="actions" scope="col" class="relative px-6 py-3"><span class="sr-only">Actions</span></th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-if="loading">
                <td :colspan="columns.length + (actions ? 1 : 0)" class="px-6 py-10 text-center text-sm text-gray-500 animate-pulse">Loading data...</td>
              </tr>
              <tr v-else-if="!data || data.length === 0">
                <td :colspan="columns.length + (actions ? 1 : 0)" class="px-6 py-10 text-center text-sm text-gray-500">No records found.</td>
              </tr>
              <tr v-else v-for="(item, idx) in data" :key="item.id || idx" class="hover:bg-gray-50 transition-colors">
                <td v-for="col in columns" :key="col.key" class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                   <slot :name="'cell-'+col.key" :item="item">{{ String(item[col.key] ?? '') }}</slot>
                </td>
                <td v-if="actions" class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                   <slot name="actions" :item="item"></slot>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
        <!-- Pagination Controls -->
        <div v-if="pagination && pagination.total > pagination.per_page" class="mt-4 flex items-center justify-between">
            <div class="text-sm text-gray-700">
                Showing <span class="font-medium">{{ pagination.from }}</span> to <span class="font-medium">{{ pagination.to }}</span> of <span class="font-medium">{{ pagination.total }}</span> results
            </div>
            <div class="flex space-x-2">
                <button :disabled="pagination.current_page === 1" @click="$emit('page', pagination.current_page - 1)" class="px-3 py-1 border border-gray-300 rounded text-sm disabled:opacity-50 hover:bg-gray-50">Previous</button>
                <button :disabled="pagination.current_page === pagination.last_page" @click="$emit('page', pagination.current_page + 1)" class="px-3 py-1 border border-gray-300 rounded text-sm disabled:opacity-50 hover:bg-gray-50">Next</button>
            </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
defineProps({
    columns: { type: Array, required: true },
    data: { type: Array, required: true },
    loading: { type: Boolean, default: false },
    actions: { type: Boolean, default: false },
    pagination: { type: Object, default: null }
});
defineEmits(['page']);
</script>
