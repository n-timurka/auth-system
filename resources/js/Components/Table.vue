<template>
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <slot name="header" />
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <slot />
            </tbody>
        </table>
    </div>
</template>

<script setup>
// No props needed for this simple table wrapper
</script>
