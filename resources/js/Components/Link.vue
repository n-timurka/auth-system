<template>
    <Link
        :href="href"
        class="rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition"
        :class="linkClasses"
    >
        <slot />
    </Link>
</template>

<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    href: {
        type: String,
        required: true,
    },
    variant: {
        type: String,
        default: 'default',
        validator: (value) => ['default', 'primary', 'underline'].includes(value),
    },
});

const linkClasses = computed(() => {
    const variantClasses = {
        default: 'text-gray-600 hover:text-gray-900',
        primary: 'text-indigo-600 hover:text-indigo-700 font-medium',
        underline: 'underline text-gray-600 hover:text-gray-900',
    };

    return variantClasses[props.variant];
});
</script>
