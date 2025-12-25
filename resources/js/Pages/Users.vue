<template>
    <div class="min-h-screen bg-gray-100">
        <header class="bg-white border-b border-gray-100">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16">
                    <div class="flex">
                        <div class="shrink-0 flex items-center">
                            <span class="font-bold text-xl text-indigo-600">Auth System</span>
                        </div>
                        <nav class="ml-6 flex space-x-4 items-center">
                            <a href="/dashboard" class="text-gray-500 hover:text-gray-700 px-3 py-2 rounded-md text-sm font-medium">Dashboard</a>
                            <a href="/users" class="text-gray-900 bg-gray-100 px-3 py-2 rounded-md text-sm font-medium">Users</a>
                        </nav>
                    </div>
                    <div class="flex items-center">
                        <div class="ml-3 relative">
                            <button @click="logout" class="text-sm text-gray-500 hover:text-gray-700 underline">Logout</button>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <main>
            <div class="py-12">
                <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6 text-gray-900">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Users List</h3>
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Role</th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Created At</th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Verified At</th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Remembered</th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        <tr v-for="u in users.data" :key="u.id">
                                            <td class="px-6 py-4 whitespace-nowrap">{{ u.name }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap">{{ u.email }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span :class="u.role === 'admin' ? 'bg-purple-100 text-purple-800' : 'bg-blue-100 text-blue-800'" class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full">
                                                    {{ u.role }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ u.created_at || 'N/A' }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                                <span v-if="u.email_verified_at" class="text-green-600">{{ u.email_verified_at }}</span>
                                                <span v-else class="text-yellow-600">Not verified</span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span v-if="u.is_remembered" class="text-green-600 font-semibold">Yes</span>
                                                <span v-else class="text-gray-500">No</span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span v-if="u.deleted_at" class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">Deleted</span>
                                                <span v-else class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Active</span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                <button v-if="!u.deleted_at" @click="deleteUser(u.id)" class="text-red-600 hover:text-red-900">Delete</button>
                                                <button v-else @click="restoreUser(u.id)" class="text-green-600 hover:text-green-900">Restore</button>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</template>

<script setup>
import { router } from '@inertiajs/vue3';

defineProps({
    users: Object,
});

const logout = () => {
    router.post('/logout');
};

const deleteUser = (id) => {
    if (confirm('Are you sure you want to delete this user?')) {
        router.delete(`/users/${id}`);
    }
};

const restoreUser = (id) => {
    router.put(`/users/${id}/restore`);
};
</script>
