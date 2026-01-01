<template>
    <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-gray-100">
        <div class="w-full sm:max-w-4xl mt-6 px-6 py-4 bg-white shadow-md overflow-hidden sm:rounded-lg">
            <h2 class="text-2xl font-bold mb-6 text-center text-gray-800">Log In</h2>

            <div v-if="status" class="mb-4 font-medium text-sm text-green-600">
                {{ status }}
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <!-- Regular Login Form -->
                <div class="md:border-r md:border-gray-200 md:pr-8">
                    <form @submit.prevent="submit">
                        <div>
                            <label class="block font-medium text-sm text-gray-700" for="email">
                                Email
                            </label>
                            <Input 
                                id="email" 
                                type="email" 
                                v-model="form.email" 
                                required 
                                autofocus 
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
                                autocomplete="current-password"
                                :error="form.errors.password"
                            />
                            <div v-if="form.errors.password" class="text-red-600 text-sm mt-1">{{ form.errors.password }}</div>
                        </div>

                        <div v-if="rememberMeEnabled" class="block mt-4">
                            <label class="flex items-center">
                                <input type="checkbox" name="remember" v-model="form.remember" class="border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500 rounded" />
                                <span class="ml-2 text-sm text-gray-600">Remember me</span>
                            </label>
                        </div>

                        <div class="flex items-center justify-end mt-4">
                            <Link href="/signup" variant="underline" class="text-sm">
                                Sign up?
                            </Link>

                            <Button 
                                type="submit" 
                                variant="primary" 
                                :disabled="form.processing"
                                class="ml-4"
                            >
                                Log in
                            </Button>
                        </div>
                    </form>
                </div>

                <!-- Social Login -->
                <div class="flex flex-col justify-center items-center">
                    <h3 class="text-lg font-medium text-gray-700 mb-4">Or sign in with</h3>
                     <a href="/auth/google" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150 w-full justify-center">
                        <svg class="w-5 h-5 mr-2" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M12.545,10.239v3.821h5.445c-0.712,2.315-2.647,3.972-5.445,3.972c-3.332,0-6.033-2.701-6.033-6.032s2.701-6.032,6.033-6.032c1.498,0,2.866,0.549,3.921,1.453l2.814-2.814C17.503,2.988,15.139,2,12.545,2C7.021,2,2.543,6.477,2.543,12s4.478,10,10.002,10c8.396,0,10.249-7.85,9.426-11.748L12.545,10.239z"/>
                        </svg>
                        Google
                     </a>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { useForm } from '@inertiajs/vue3';
import Input from '@/Components/Input.vue';
import InputPassword from '@/Components/InputPassword.vue';
import Button from '@/Components/Button.vue';
import Link from '@/Components/Link.vue';

defineProps({
    status: String,
    rememberMeEnabled: {
        type: Boolean,
        default: true,
    },
});

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post('/login', {
        onFinish: () => form.reset('password'),
    });
};
</script>
