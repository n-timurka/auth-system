<template>
    <div class="min-h-screen bg-gray-100">
        <header class="bg-white border-b border-gray-100">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16">
                    <div class="flex">
                        <div class="shrink-0 flex items-center">
                            <span class="font-bold text-xl text-indigo-600">Auth System</span>
                        </div>
                    </div>
                    <div class="flex items-center">
                        {{ user.name }}
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
                            <h2 class="text-2xl font-bold mb-4">Welcome, {{ user.name }}!</h2>
                            <p class="text-gray-600 mb-6">You are logged in to the Auth System.</p>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div v-if="user.permissions?.includes('users.view')" class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow">
                                    <h3 class="text-lg font-semibold mb-2">User Management</h3>
                                    <p class="text-gray-600 text-sm mb-4">View and manage all users in the system.</p>
                                    <a href="/users" class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-md hover:bg-indigo-700">
                                        Go to Users
                                    </a>
                                </div>
                                
                                <div class="border border-gray-200 rounded-lg p-4">
                                    <h3 class="text-lg font-semibold mb-2">Your Profile</h3>
                                    <p class="text-gray-600 text-sm mb-2"><strong>Email:</strong> {{ user.email }}</p>
                                    <p class="text-gray-600 text-sm mb-2"><strong>Role:</strong> <span :class="user.role === 'admin' ? 'text-purple-600' : 'text-blue-600'" class="font-semibold">{{ user.role }}</span></p>
                                    <p class="text-gray-600 text-sm"><strong>Email Verified:</strong> {{ user.email_verified_at ? 'Yes' : 'No' }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</template>

<script setup>
import { computed } from 'vue';
import { router, usePage } from '@inertiajs/vue3';

const page = usePage();

const user = computed(() => page.props.auth.user.data);
const logout = () => {
    router.post('/logout');
};
</script>
