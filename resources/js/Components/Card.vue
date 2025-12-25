<template>
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
        <div v-if="$slots.header || title" class="px-6 py-4 border-b border-gray-200">
            <slot name="header">
                <h3 v-if="title" class="text-lg font-medium text-gray-900">{{ title }}</h3>
            </slot>
        </div>
        
        <div :class="padding ? 'p-6' : ''">
            <slot />
        </div>

        <div v-if="$slots.footer" class="px-6 py-4 border-t border-gray-200 bg-gray-50">
            <slot name="footer" />
        </div>
    </div>
</template>

<script setup>
defineProps({
    title: {
        type: String,
        default: null,
    },
    padding: {
        type: Boolean,
        default: true,
    },
});
</script>
