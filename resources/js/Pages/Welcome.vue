<template>
  <div class="flex items-center justify-center min-h-screen bg-gray-100">
    <div class="bg-white p-8 rounded-lg shadow-md max-w-md w-full">
      <h1 class="text-3xl font-bold mb-4 text-center text-indigo-600">Welcome to Laravel 12 + Inertia + Vue</h1>
      <p class="text-gray-700 text-center mb-6">
        This is a manually configured installation without Breeze.
        <br>
        <span class="font-semibold text-sm mt-2 block text-green-600">Vue 3 is working!</span>
      </p>
      <div class="flex justify-center">
        <button class="px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700 transition" @click="count++">
          Count is: {{ count }}
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';

const count = ref(0);
</script>
