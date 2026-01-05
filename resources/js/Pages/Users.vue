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
                                    <TableHeader>Name</TableHeader>
                                    <TableHeader>Email</TableHeader>
                                    <TableHeader>Role</TableHeader>
                                    <TableHeader>Created At</TableHeader>
                                    <TableHeader>Verified At</TableHeader>
                                    <TableHeader>Remembered</TableHeader>
                                    <TableHeader>Status</TableHeader>
                                    <TableHeader>Actions</TableHeader>
                                </tr>
                            </template>
                            <tr v-for="u in users.data" :key="u.id">
                                <TableItem>{{ u.name }}</TableItem>
                                <TableItem>{{ u.email }}</TableItem>
                                <TableItem>
                                    <Badge :variant="u.role === 'admin' ? 'primary' : 'info'">
                                        {{ u.role }}
                                    </Badge>
                                </TableItem>
                                <TableItem>{{ u.created_at || 'N/A' }}</TableItem>
                                <TableItem>
                                    <span v-if="u.email_verified_at" class="text-green-600">{{ u.email_verified_at }}</span>
                                    <span v-else class="text-yellow-600">Not verified</span>
                                </TableItem>
                                <TableItem>
                                    <span v-if="u.is_remembered" class="text-green-600 font-semibold">Yes</span>
                                    <span v-else class="text-gray-500">No</span>
                                </TableItem>
                                <TableItem>
                                    <Badge :variant="u.deleted_at ? 'danger' : 'success'">
                                        {{ u.deleted_at ? 'Deleted' : 'Active' }}
                                    </Badge>
                                </TableItem>
                                <TableItem>
                                    <Button v-if="!u.deleted_at" @click="deleteUser(u.id)" variant="danger" class="text-xs">
                                        Delete
                                    </Button>
                                    <Button v-else @click="restoreUser(u.id)" variant="secondary" class="text-xs">
                                        Restore
                                    </Button>
                                </TableItem>
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
import TableHeader from '@/Components/TableHeader.vue';
import TableItem from '@/Components/TableItem.vue';

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
