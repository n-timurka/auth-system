<template>
    <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-gray-100">
        <div class="w-full sm:max-w-md mt-6 px-6 py-4 bg-white shadow-md overflow-hidden sm:rounded-lg">
             <h2 class="text-2xl font-bold mb-6 text-center text-gray-800">Sign Up</h2>

            <form @submit.prevent="submit">
                <div>
                    <label class="block font-medium text-sm text-gray-700" for="name">
                        Name
                    </label>
                    <Input 
                        id="name" 
                        type="text" 
                        v-model="form.name" 
                        required 
                        autofocus 
                        autocomplete="name"
                        :error="form.errors.name"
                    />
                    <div v-if="form.errors.name" class="text-red-600 text-sm mt-1">
                        {{ form.errors.name }}
                    </div>
                </div>

                <div class="mt-4">
                     <label class="block font-medium text-sm text-gray-700" for="email">
                        Email
                    </label>
                    <Input 
                        id="email" 
                        type="email" 
                        v-model="form.email" 
                        required 
                        autocomplete="username"
                        :error="form.errors.email"
                    />
                    <div v-if="form.errors.email" class="text-red-600 text-sm mt-1">{{ form.errors.email }}</div>
                </div>

                <div class="mt-4">
                     <label class="block font-medium text-sm text-gray-700" for="password">
                        Password
                    </label>
                    <InputPassword 
                        id="password" 
                        v-model="form.password" 
                        required 
                        autocomplete="new-password"
                        :error="form.errors.password"
                    />
                     <div v-if="form.errors.password" class="text-red-600 text-sm mt-1">{{ form.errors.password }}</div>
                </div>

                <div class="flex items-center justify-end mt-8">
                    <Link href="/login" variant="underline" class="text-sm mr-4">
                        Already registered?
                    </Link>

                    <Button 
                        type="submit" 
                        variant="primary" 
                        :disabled="form.processing"
                    >
                        Register
                    </Button>
                </div>
            </form>
        </div>
    </div>
</template>

<script setup>
import { useForm } from '@inertiajs/vue3';
import Input from '@/Components/Input.vue';
import InputPassword from '@/Components/InputPassword.vue';
import Button from '@/Components/Button.vue';
import Link from '@/Components/Link.vue';

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
