<template>
    <div class="min-h-screen bg-gray-100">
        <Header />

        <main>
            <div class="py-12">
                <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    <Card>
                        <h2 class="text-2xl font-bold mb-4">Welcome, {{ user?.name }}!</h2>
                        <p class="text-gray-600 mb-6">You are logged in to the Auth System.</p>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div v-if="user?.permissions?.includes('users.view')" class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow">
                                <h3 class="text-lg font-semibold mb-2">User Management</h3>
                                <p class="text-gray-600 text-sm mb-4">View and manage all users in the system.</p>
                                <Link href="/users" variant="primary" class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-md hover:bg-indigo-700">
                                    Go to Users
                                </Link>
                            </div>
                            
                            <div class="border border-gray-200 rounded-lg p-4">
                                <h3 class="text-lg font-semibold mb-2">Your Profile</h3>
                                <p class="text-gray-600 text-sm mb-2"><strong>Email:</strong> {{ user?.email }}</p>
                                <p class="text-gray-600 text-sm mb-2">
                                    <strong>Role:</strong> 
                                    <Badge :variant="user?.role === 'admin' ? 'primary' : 'info'">
                                        {{ user?.role }}
                                    </Badge>
                                </p>
                                <p class="text-gray-600 text-sm"><strong>Email Verified:</strong> {{ user?.email_verified_at ? 'Yes' : 'No' }}</p>
                            </div>
                        </div>
                    </Card>
                </div>
            </div>
        </main>
    </div>
</template>

<script setup>
import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';
import Header from '@/Components/Header.vue';
import Card from '@/Components/Card.vue';
import Badge from '@/Components/Badge.vue';
import Link from '@/Components/Link.vue';

const page = usePage();
const user = computed(() => page.props.auth?.user.data);
</script>
