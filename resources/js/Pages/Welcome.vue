<template>
  <div class="flex items-center justify-center min-h-screen bg-gray-100">
    <div class="bg-white p-8 rounded-lg shadow-md max-w-md w-full">
      <h1 class="text-3xl font-bold mb-4 text-center text-indigo-600">
        Welcome to Auth System based on Laravel 12 + Inertia + Vue
      </h1>
      <div class="flex flex-col items-center justify-center gap-4">
        <Link href="/signup" class="text-indigo-600 hover:underline">Sign Up</Link>
      </div>
    </div>
  </div>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';
</script>
