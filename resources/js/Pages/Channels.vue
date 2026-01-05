<template>
    <div class="min-h-screen bg-gray-100">
        <Header />

        <main class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">
                
                <!-- Personal Channel Section -->
                <Card title="Personal Channel">
                    <div v-if="!isConnected" class="text-center py-8">
                        <p class="text-gray-600 mb-6">Connect your Google account to see your personal YouTube channel statistics.</p>
                        <a href="/auth/google" class="inline-flex items-center px-4 py-2 bg-red-600 text-white text-sm font-medium rounded-md hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-colors">
                            <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path fill-rule="evenodd" d="M19.812 5.418c.861.23 1.538.907 1.768 1.768C21.998 8.746 22 12 22 12s0 3.255-.418 4.814a2.504 2.504 0 0 1-1.768 1.768c-1.56.419-7.814.419-7.814.419s-6.255 0-7.814-.419a2.505 2.505 0 0 1-1.768-1.768C2 15.255 2 12 2 12s0-3.254.418-4.814a2.507 2.507 0 0 1 1.768-1.768C5.744 5 11.998 5 11.998 5s6.255 0 7.814.418ZM15.194 12 10 15V9l5.194 3Z" clip-rule="evenodd" />
                            </svg>
                            Connect with Google
                        </a>
                    </div>
                    <div v-else-if="!personalChannel" class="text-center py-8">
                        <p class="text-gray-600 mb-6">Google account connected, but no personal YouTube channel has been linked yet.</p>
                        <button @click="addPersonalChannel" :disabled="formPersonal.processing" class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors">
                             <svg v-if="formPersonal.processing" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            Sync Personal Channel
                        </button>
                    </div>
                    <div v-else>
                         <div class="flex justify-between items-start">
                            <div class="flex items-center">
                                <img
                                    :src="personalChannel.thumbnail_url"
                                    alt="Channel Avatar"
                                    class="w-16 h-16 rounded-full mr-4 border-2 border-gray-200" />
                                <div>
                                    <h3 class="text-xl font-bold text-gray-900">
                                        <Link :href="`/channels/${personalChannel.platform_channel_id}`" class="hover:text-indigo-600 hover:underline">
                                            {{ personalChannel.name }}
                                        </Link>
                                    </h3>
                                    <div class="flex items-center text-sm text-gray-600 mt-1 space-x-4">
                                        <span><strong>{{ formatNumber(personalChannel.statistics.subscriberCount) }}</strong> subscribers</span>
                                        <span><strong>{{ formatNumber(personalChannel.statistics.videoCount) }}</strong> videos</span>
                                        <span><strong>{{ formatNumber(personalChannel.statistics.viewCount) }}</strong> views</span>
                                    </div>
                                </div>
                            </div>
                            <div class="text-right flex flex-col items-end gap-2">
                                <div class="text-xs text-gray-500">Updated: {{ formatDate(personalChannel.last_synced_at, true) }}</div>
                                <div class="flex gap-2">
                                    <Link :href="`/channels/${personalChannel.platform_channel_id}`" class="inline-flex items-center px-3 py-1 bg-indigo-50 text-indigo-700 text-sm rounded hover:bg-indigo-100 transition focus:outline-none">
                                        View Details
                                    </Link>
                                    <button @click="reloadChannel(personalChannel.id)" :disabled="loadingId === personalChannel.id" class="inline-flex items-center px-3 py-1 bg-gray-200 text-gray-700 text-sm rounded hover:bg-gray-300 transition focus:outline-none">
                                        <svg v-if="loadingId === personalChannel.id" class="animate-spin -ml-1 mr-2 h-4 w-4 text-gray-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                        Reload
                                    </button>
                                </div>
                            </div>
                        </div>
                        
                        <p class="text-gray-700 mb-6 whitespace-pre-wrap max-w-4xl text-sm line-clamp-3">{{ personalChannel.description }}</p>
                    </div>
                </Card>

                <!-- Public Channels Section -->
                <Card title="Public Channels">
                    <!-- Add Public Channel Form -->
                    <div class="mb-8 p-4 bg-gray-50 rounded-lg border border-gray-200">
                        <h3 class="text-lg font-medium text-gray-800 mb-2">
                            Track a new channel
                        </h3>
                        <form @submit.prevent="addPublicChannel" class="flex gap-4">
                            <div class="flex-1">
                                <label for="channel_input" class="sr-only">Channel ID or URL</label>
                                <Input
                                    v-model="form.channel_input"
                                    id="channel_input"
                                    type="text"
                                    placeholder="Enter Channel ID or URL (e.g. UC...)"
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                    :required="true"
                                />
                                <div
                                    v-if="form.errors.channel_input"
                                    class="text-red-600 text-xs mt-1"
                                >
                                    {{ form.errors.channel_input }}
                                </div>
                            </div>
                            <Button
                                type="submit"
                                :disabled="form.processing"
                            >
                                Add Channel
                            </Button>
                        </form>
                         <div v-if="$page.props.flash?.message" class="mt-2 text-green-600 text-sm">
                            {{ $page.props.flash?.message }}
                        </div>
                    </div>

                    <!-- List of Public Channels -->
                    <div class="space-y-8">
                        <div v-if="publicChannels.length === 0" class="text-center text-gray-500 py-8 italic">
                            No public channels tracked yet. Add one above!
                        </div>

                        <Table>
                            <template #header>
                                <tr>
                                    <TableHeader>Name</TableHeader>
                                    <TableHeader>Subscribers</TableHeader>
                                    <TableHeader>Videos</TableHeader>
                                    <TableHeader>Views</TableHeader>
                                    <TableHeader>Updated</TableHeader>
                                    <TableHeader>Actions</TableHeader>
                                </tr>
                            </template>
                            
                            <tr v-for="channel in publicChannels" :key="channel.id">
                                <TableItem>
                                    <div class="flex items-center">
                                        <img
                                            :src="channel.thumbnail_url"
                                            alt="Channel Avatar"
                                            class="w-12 h-12 rounded-full mr-4 border-2 border-gray-200" />
                                        <div>
                                            <p class="font-semibold">{{ channel.name }}</p>
                                            <p class="text-xs text-gray-500">{{ channel.platform_channel_id }}</p>
                                        </div>
                                    </div>
                                </TableItem>
                                <TableItem>{{ formatNumber(channel.statistics.subscriberCount) }}</TableItem>
                                <TableItem>{{ formatNumber(channel.statistics.videoCount) }}</TableItem>
                                <TableItem>{{ formatNumber(channel.statistics.viewCount) }}</TableItem>
                                <TableItem>{{ formatDate(channel.last_synced_at, true) }}</TableItem>
                                <TableItem>
                                    <Link :href="`/channels/${channel.platform_channel_id}`" class="inline-flex items-center px-3 py-1 bg-indigo-50 text-indigo-700 text-sm rounded hover:bg-indigo-100 transition focus:outline-none">
                                        Details
                                    </Link>
                                    <!-- <Button @click="reloadChannel(channel.id)">Reload</Button> -->
                                </TableItem>
                            </tr>
                        </Table>
                    </div>
                </Card>

            </div>
        </main>
    </div>
</template>

<script setup>
import { ref } from 'vue';
import { useForm, router, Link } from '@inertiajs/vue3';
import Header from '@/Components/Header.vue';
import Card from '@/Components/Card.vue';
import Input from '@/Components/Input.vue';
import Button from '@/Components/Button.vue';
import Table from '@/Components/Table.vue';
import TableHeader from '@/Components/TableHeader.vue';
import TableItem from '@/Components/TableItem.vue';

const props = defineProps({
    personalChannel: Object, // Channel Model
    publicChannels: Array,   // Array of Channel Models
    isConnected: Boolean,
});

const form = useForm({
    channel_input: ''
});

const formPersonal = useForm({});

const addPersonalChannel = () => {
    formPersonal.transform((data) => ({
        ...data,
        type: 'personal',
    })).post(route('channels.store'), {
        preserveScroll: true,
    });
};

const loadingId = ref(null);

const addPublicChannel = () => {
    form.transform((data) => ({
        ...data,
        type: 'public',
    })).post(route('channels.store'), {
        onSuccess: () => form.reset(),
    });
};

const reloadChannel = (channelId) => {
    loadingId.value = channelId;
    router.post(route('channels.refresh', channelId), {}, {
        preserveScroll: true,
        onFinish: () => {
            loadingId.value = null;
        }
    });
};

const formatNumber = (num) => {
    if (!num) return '0';
    return new Intl.NumberFormat('en-US', { notation: "compact", compactDisplay: "short" }).format(num);
};

const formatDate = (dateString, includeTime = false) => {
    if (!dateString) return 'N/A';
    const options = { year: 'numeric', month: 'short', day: 'numeric' };
    if (includeTime) {
        options.hour = '2-digit';
        options.minute = '2-digit';
    }
    return new Date(dateString).toLocaleDateString(undefined, options);
};
</script>
