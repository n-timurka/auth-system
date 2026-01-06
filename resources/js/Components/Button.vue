<template>
    <button
        :type="type"
        :disabled="disabled"
        class="inline-flex items-center border border-transparent rounded-md font-semibold uppercase tracking-widest focus:outline-none focus:ring-2 focus:ring-offset-2 transition ease-in-out duration-150"
        :class="buttonClasses"
    >
        <slot />
    </button>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
    variant: {
        type: String,
        default: 'primary',
        validator: (value) => ['primary', 'secondary', 'danger'].includes(value),
    },
    size: {
        type: String,
        default: 'md',
        validator: (value) => ['sm', 'md', 'lg'].includes(value),
    },
    type: {
        type: String,
        default: 'button',
    },
    disabled: {
        type: Boolean,
        default: false,
    },
});

const buttonClasses = computed(() => {
    const baseClasses = props.disabled ? 'opacity-25 cursor-not-allowed' : 'cursor-pointer';
    
    const variantClasses = {
        primary: 'bg-indigo-600 text-indigo-50 hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:ring-indigo-500',
        secondary: 'bg-gray-200 text-gray-800 hover:bg-gray-300 focus:bg-gray-300 active:bg-gray-400 focus:ring-gray-500',
        danger: 'bg-red-600 text-red-50 hover:bg-red-700 focus:bg-red-700 active:bg-red-900 focus:ring-red-500',
    };

    const sizeClasses = {
        sm: 'text-sm px-2 py-1',
        md: 'text-base px-3 py-2',
        lg: 'text-lg px-4 py-3',
    };

    return `${baseClasses} ${variantClasses[props.variant]} ${sizeClasses[props.size]}`;
});
</script>
