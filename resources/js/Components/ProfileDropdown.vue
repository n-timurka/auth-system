<template>
    <Dropdown>
        <template #trigger>
            <button class="flex items-center text-sm font-medium text-gray-700 hover:text-gray-900 focus:outline-none focus:text-gray-900 transition">
                <span>{{ user?.name }}</span>
                <svg class="ml-1 h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                </svg>
            </button>
        </template>

        <div class="px-4 py-2 text-xs text-gray-500 border-b border-gray-100">
            {{ user?.email }}
        </div>

        <button
            @click="logout"
            class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 transition"
        >
            Logout
        </button>
    </Dropdown>
</template>

<script setup>
import { computed } from 'vue';
import { router, usePage } from '@inertiajs/vue3';
import Dropdown from './Dropdown.vue';

const page = usePage();
const user = computed(() => page.props.auth?.user.data);

const logout = () => {
    router.post('/logout');
};
</script>
