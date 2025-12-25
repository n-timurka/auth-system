<template>
    <div class="min-h-screen bg-gray-100">
        <Header />

        <main>
            <div class="py-12">
                <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    <Card title="Users List">
                        <Table>
                            <template #header>
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
                            </template>
                            <tr v-for="u in users.data" :key="u.id">
                                <td class="px-6 py-4 whitespace-nowrap">{{ u.name }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">{{ u.email }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <Badge :variant="u.role === 'admin' ? 'primary' : 'info'">
                                        {{ u.role }}
                                    </Badge>
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
                                    <Badge :variant="u.deleted_at ? 'danger' : 'success'">
                                        {{ u.deleted_at ? 'Deleted' : 'Active' }}
                                    </Badge>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                                    <Button v-if="!u.deleted_at" @click="deleteUser(u.id)" variant="danger" class="text-xs">
                                        Delete
                                    </Button>
                                    <Button v-else @click="restoreUser(u.id)" variant="secondary" class="text-xs">
                                        Restore
                                    </Button>
                                </td>
                            </tr>
                        </Table>
                    </Card>
                </div>
            </div>
        </main>
    </div>
</template>

<script setup>
import { router } from '@inertiajs/vue3';
import Header from '@/Components/Header.vue';
import Card from '@/Components/Card.vue';
import Table from '@/Components/Table.vue';
import Badge from '@/Components/Badge.vue';
import Button from '@/Components/Button.vue';

defineProps({
    users: Object,
});

const deleteUser = (id) => {
    if (confirm('Are you sure you want to delete this user?')) {
        router.delete(`/users/${id}`);
    }
};

const restoreUser = (id) => {
    router.put(`/users/${id}/restore`);
};
</script>
