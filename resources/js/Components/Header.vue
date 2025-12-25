<template>
    <header class="bg-white border-b border-gray-100">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex">
                    <div class="shrink-0 flex items-center">
                        <Link href="/dashboard" class="font-bold text-xl text-indigo-600 hover:text-indigo-700 transition">
                            Auth System
                        </Link>
                    </div>
                    <nav class="ml-6 flex space-x-4 items-center">
                        <Link 
                            href="/dashboard" 
                            class="text-gray-500 hover:text-gray-700 px-3 py-2 rounded-md text-sm font-medium transition"
                            :class="{ 'text-gray-900 bg-gray-100': $page.url === '/dashboard' }"
                        >
                            Dashboard
                        </Link>
                        <Link 
                            v-if="user?.permissions?.includes('users.view')"
                            href="/users" 
                            class="text-gray-500 hover:text-gray-700 px-3 py-2 rounded-md text-sm font-medium transition"
                            :class="{ 'text-gray-900 bg-gray-100': $page.url === '/users' }"
                        >
                            Users
                        </Link>
                    </nav>
                </div>
                <div class="flex items-center">
                    <ProfileDropdown />
                </div>
            </div>
        </div>
    </header>
</template>

<script setup>
import { computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import ProfileDropdown from './ProfileDropdown.vue';

const page = usePage();
const user = computed(() => page.props.auth?.user.data);
</script>
