<template>
  <div class="py-6 px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto">
    <div class="mb-6 md:flex md:items-center md:justify-between">
      <div class="flex-1 min-w-0">
        <h2 class="text-2xl font-bold leading-7 text-gray-900 sm:text-3xl sm:truncate">
          System Users
        </h2>
        <p class="mt-1 text-sm text-gray-500">Manage administrator and editor accounts.</p>
      </div>
      <div class="mt-4 flex md:mt-0 md:ml-4">
        <button @click="showModal = true" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700">
          Add New User
        </button>
      </div>
    </div>

    <!-- Reusable Data Table -->
    <AppDataTable 
        :columns="columns" 
        :data="users" 
        :loading="loading" 
        :pagination="pagination"
        :actions="true"
        @page="fetchUsers"
    >
        <template #cell-active="{ item }">
            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full" :class="item.active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'">
                {{ item.active ? 'Active' : 'Disabled' }}
            </span>
        </template>
        <template #cell-roles="{ item }">
            <span class="text-xs uppercase tracking-wide font-medium text-indigo-600 bg-indigo-50 px-2 py-1 rounded">
                {{ item.roles?.[0]?.name || 'User' }}
            </span>
        </template>
        <template #cell-created_at="{ item }">
            {{ new Date(item.created_at).toLocaleDateString() }}
        </template>
        <template #actions="{ item }">
            <button @click="deleteUser(item.id)" class="text-red-600 hover:text-red-900">Delete</button>
        </template>
    </AppDataTable>

    <!-- Create User Modal -->
    <div v-if="showModal" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div @click="showModal = false" class="fixed inset-0 bg-gray-500/75 transition-opacity" aria-hidden="true"></div>
        <div class="relative z-10 flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <form @submit.prevent="createUser">
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4" id="modal-title">Add New User</h3>
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Name</label>
                                <input v-model="form.name" type="text" required class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Email</label>
                                <input v-model="form.email" type="email" required class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Password</label>
                                <input v-model="form.password" type="password" required class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Role</label>
                                <select v-model="form.role" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                    <option value="admin">Admin</option>
                                    <option value="editor">Editor</option>
                                </select>
                            </div>
                            <div class="flex items-center">
                                <input v-model="form.active" type="checkbox" id="user-active" class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                                <label for="user-active" class="ml-2 block text-sm text-gray-900">Active</label>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button type="submit" :disabled="saving" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none sm:ml-3 sm:w-auto sm:text-sm">
                            {{ saving ? 'Saving...' : 'Save User' }}
                        </button>
                        <button @click="showModal = false" type="button" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                            Cancel
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from '../axios';
import AppDataTable from '../components/common/AppDataTable.vue';
import { useToast } from '../stores/toast';
import { useConfirm } from '../stores/confirm';

const toast = useToast();
const confirmDialog = useConfirm();

const users = ref([]);
const loading = ref(true);
const saving = ref(false);
const showModal = ref(false);
const pagination = ref({});

const form = ref({
    name: '',
    email: '',
    password: '',
    role: 'editor',
    active: true
});

const columns = [
    { key: 'name', label: 'Name' },
    { key: 'email', label: 'Email Address' },
    { key: 'roles', label: 'Role' },
    { key: 'active', label: 'Status' },
    { key: 'created_at', label: 'Joined On' }
];

const fetchUsers = async (page = 1) => {
    loading.value = true;
    try {
        const res = await axios.get(`/users?page=${page}`);
        users.value = res.data.data;
        pagination.value = res.data.pagination;
    } catch (err) {
        console.error('Failed to load users');
    } finally {
        loading.value = false;
    }
};

const createUser = async () => {
    saving.value = true;
    try {
        await axios.post('/users', form.value);
        showModal.value = false;
        form.value = { name: '', email: '', password: '', role: 'editor', active: true };
        fetchUsers();
        toast.success('User created.');
    } catch (err) {
        toast.error(err.response?.data?.message || 'Failed to create user.');
    } finally {
        saving.value = false;
    }
};

const deleteUser = async (id) => {
    const ok = await confirmDialog.ask({
        title: 'Delete this user?',
        message: 'This will deactivate the account and remove the user from the system.',
        confirmLabel: 'Delete',
        variant: 'danger',
    });
    if (!ok) return;
    try {
        await axios.delete(`/users/${id}`);
        fetchUsers(pagination.value.current_page);
        toast.success('User deleted.');
    } catch (err) {
        toast.error(err.response?.data?.errors?.[0] || 'Delete failed.');
    }
};

onMounted(() => fetchUsers());
</script>
