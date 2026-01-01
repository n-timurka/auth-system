<template>
    <div class="min-h-screen bg-gray-100">
        <Header />

        <main>
            <div class="py-12">
                <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    <Card>
                        <div class="mb-6">
                            <h2 class="text-2xl font-bold text-gray-800">Site Settings</h2>
                            <p class="text-gray-600 mt-1">Manage your application settings</p>
                        </div>

                        <div v-if="successMessage" class="mb-4 p-4 bg-green-50 border border-green-200 rounded-md">
                            <p class="text-green-800 text-sm">{{ successMessage }}</p>
                        </div>

                        <form @submit.prevent="submit">
                            <!-- Tabs -->
                            <div class="border-b border-gray-200 mb-6">
                                <nav class="-mb-px flex space-x-8">
                                    <button
                                        v-for="segment in segments"
                                        :key="segment"
                                        type="button"
                                        @click="activeSegment = segment"
                                        :class="[
                                            activeSegment === segment
                                                ? 'border-indigo-600 text-indigo-600'
                                                : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300',
                                            'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm capitalize'
                                        ]"
                                    >
                                        {{ segment }}
                                    </button>
                                </nav>
                            </div>

                            <!-- Settings for active segment -->
                            <div v-for="segment in segments" :key="segment" v-show="activeSegment === segment" class="space-y-6">
                                <div v-for="(setting, key) in settings[segment]" :key="key" class="border-b border-gray-200 pb-6 last:border-b-0">
                                    <label :for="key" class="block text-sm font-medium text-gray-700 mb-1">
                                        {{ formatLabel(key) }}
                                    </label>
                                    <p v-if="setting.description" class="text-xs text-gray-500 mb-2">
                                        {{ setting.description }}
                                    </p>

                                    <!-- Boolean input (toggle) -->
                                    <Toggle
                                        v-if="setting.type === 'boolean'"
                                        :id="key"
                                        v-model="form[key]"
                                    />

                                    <!-- Integer input -->
                                    <Input
                                        v-else-if="setting.type === 'integer'"
                                        :id="key"
                                        type="number"
                                        v-model.number="form[key]"
                                        :error="form.errors[key]"
                                    />

                                    <!-- String input -->
                                    <Input
                                        v-else
                                        :id="key"
                                        type="text"
                                        v-model="form[key]"
                                        :error="form.errors[key]"
                                    />

                                    <div v-if="form.errors[key]" class="text-red-600 text-sm mt-1">
                                        {{ form.errors[key] }}
                                    </div>
                                </div>
                            </div>

                            <!-- Submit button -->
                            <div class="flex justify-end mt-6 pt-6 border-t border-gray-200">
                                <Button
                                    type="submit"
                                    variant="primary"
                                    :disabled="form.processing"
                                >
                                    {{ form.processing ? 'Saving...' : 'Save Settings' }}
                                </Button>
                            </div>
                        </form>
                    </Card>
                </div>
            </div>
        </main>
    </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { useForm, usePage } from '@inertiajs/vue3';
import Header from '@/Components/Header.vue';
import Card from '@/Components/Card.vue';
import Input from '@/Components/Input.vue';
import Button from '@/Components/Button.vue';
import Toggle from '@/Components/Toggle.vue';

const props = defineProps({
    settings: {
        type: Object,
        required: true,
    },
});

const page = usePage();
const successMessage = computed(() => page.props.flash?.success);

// Get all segments from settings
const segments = computed(() => Object.keys(props.settings));

// Active segment tab
const activeSegment = ref(segments.value[0] || 'general');

// Flatten settings for form
const flattenSettings = () => {
    const flat = {};
    Object.entries(props.settings).forEach(([segment, settingsObj]) => {
        Object.entries(settingsObj).forEach(([key, setting]) => {
            flat[key] = setting.value;
        });
    });
    return flat;
};

const form = useForm(flattenSettings());

const submit = () => {
    form.put('/settings', {
        preserveScroll: true,
        onSuccess: () => {
            // Form will be updated with new values automatically
        },
    });
};

// Format label from key (e.g., 'remember_me_enabled' -> 'Remember Me Enabled')
const formatLabel = (key) => {
    return key
        .split('_')
        .map(word => word.charAt(0).toUpperCase() + word.slice(1))
        .join(' ');
};
</script>
