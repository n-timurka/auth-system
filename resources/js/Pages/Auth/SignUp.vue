<template>
    <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-gray-100">
        <div class="w-full sm:max-w-md mt-6 px-6 py-4 bg-white shadow-md overflow-hidden sm:rounded-lg">
             <h2 class="text-2xl font-bold mb-6 text-center text-gray-800">Sign Up</h2>

            <form @submit.prevent="submit">
                <div>
                    <label class="block font-medium text-sm text-gray-700" for="name">
                        Name
                    </label>
                    <input id="name" type="text" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm mt-1 block w-full px-1 py-2" v-model="form.name" required autofocus autocomplete="name" />
                    <div v-if="form.errors.name" class="text-red-600 text-sm mt-1">
                        {{ form.errors.name }}
                    </div>
                </div>

                <div class="mt-4">
                     <label class="block font-medium text-sm text-gray-700" for="email">
                        Email
                    </label>
                    <input id="email" type="email" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm mt-1 block w-full px-1 py-2" v-model="form.email" required autocomplete="username" />
                    <div v-if="form.errors.email" class="text-red-600 text-sm mt-1">{{ form.errors.email }}</div>
                </div>

                <div class="mt-4">
                     <label class="block font-medium text-sm text-gray-700" for="password">
                        Password
                    </label>
                    <input id="password" type="password" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm mt-1 block w-full px-1 py-2" v-model="form.password" required autocomplete="new-password" />
                     <div v-if="form.errors.password" class="text-red-600 text-sm mt-1">{{ form.errors.password }}</div>
                </div>

                <div class="flex items-center justify-end mt-8">
                    <button class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150" :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                        Register
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>

<script setup>
import { useForm } from '@inertiajs/vue3';

const form = useForm({
    name: '',
    email: '',
    password: '',
});

const submit = () => {
    form.post('/signup', {
        onFinish: () => form.reset('password'),
    });
};
</script>
