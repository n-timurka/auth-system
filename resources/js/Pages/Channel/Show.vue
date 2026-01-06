<template>
    <div class="min-h-screen bg-gray-100">
        <Header />

        <main class="py-6">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">
                
                <div class="mb-4">
                    <Link href="/channels" class="text-indigo-600 hover:text-indigo-800 flex items-center gap-1 font-medium">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                        </svg>
                        Back to Channels
                    </Link>
                </div>

                <div class="bg-white shadow overflow-hidden sm:rounded-lg">
                    <div class="px-4 py-5 sm:px-6 relative">
                        <div class="flex flex-col sm:flex-row items-center sm:items-end mb-4 sm:mb-0">
                            <img :src="channel.thumbnail_url" class="w-24 h-24 sm:w-32 sm:h-32 rounded-full border-4 border-white shadow-lg z-10 bg-white" alt="Channel Logo">
                            
                            <div class="mt-4 sm:mt-0 sm:ml-4 text-center sm:text-left flex-1">
                                <h1 class="text-3xl font-bold text-gray-900">{{ channel.name }}</h1>
                                <p v-if="channel.custom_url" class="text-sm text-gray-500">{{ channel.custom_url }}</p>
                            </div>
                            
                           <div class="mt-4 sm:mt-0 flex gap-2">
                                <a :href="`https://youtube.com/channel/${channel.platform_channel_id}`" target="_blank" class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-500 active:bg-red-700 focus:outline-none focus:border-red-700 focus:shadow-outline-red transition ease-in-out duration-150">
                                    Visit on YouTube
                                </a>
                                <button @click="reloadChannel" :disabled="loading" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:text-gray-500 focus:outline-none focus:border-blue-300 focus:shadow-outline-blue active:text-gray-800 active:bg-gray-50 transition ease-in-out duration-150">
                                     <svg v-if="loading" class="animate-spin -ml-1 mr-2 h-4 w-4 text-gray-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    Refresh Data
                                </button>
                                <button @click="syncVideos" :disabled="syncing" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-500 active:bg-indigo-700 focus:outline-none focus:border-indigo-700 focus:shadow-outline-indigo transition ease-in-out duration-150">
                                     <svg v-if="syncing" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    {{ nextPageToken ? 'Load More Videos' : 'Sync Videos' }}
                                </button>
                           </div>
                        </div>

                        <div class="mt-6 border-t border-gray-100 pt-6">
                             <dl class="grid grid-cols-1 gap-x-4 gap-y-8 sm:grid-cols-3 text-center sm:text-left">
                                <div class="sm:col-span-1">
                                    <dt class="text-sm font-medium text-gray-500">Subscribers</dt>
                                    <dd class="mt-1 text-2xl font-semibold text-gray-900">{{ formatNumber(channel.statistics.subscriberCount) }}</dd>
                                </div>
                                <div class="sm:col-span-1">
                                    <dt class="text-sm font-medium text-gray-500">Total Videos</dt>
                                    <dd class="mt-1 text-2xl font-semibold text-gray-900">{{ formatNumber(channel.statistics.videoCount) }}</dd>
                                </div>
                                <div class="sm:col-span-1">
                                    <dt class="text-sm font-medium text-gray-500">Total Views</dt>
                                    <dd class="mt-1 text-2xl font-semibold text-gray-900">{{ formatNumber(channel.statistics.viewCount) }}</dd>
                                </div>
                                <div class="sm:col-span-3">
                                    <dt class="text-sm font-medium text-gray-500">About</dt>
                                    <dd class="mt-1 text-sm text-gray-900 whitespace-pre-wrap">{{ channel.description }}</dd>
                                </div>
                                 <div class="sm:col-span-3">
                                    <dt class="text-sm font-medium text-gray-500">Last Synced</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ formatDate(channel.last_synced_at, true) }}</dd>
                                 </div>
                            </dl>
                        </div>
                    </div>
                </div>

                <div class="bg-white shadow sm:rounded-lg p-6">
                    <h3 class="text-lg leading-6 font-medium text-gray-900 mb-6">
                        Videos ({{ videos.length }})
                    </h3>
                     <div v-if="videos.length === 0" class="text-gray-500 text-center py-4">
                        No videos found locally. Click "Sync Videos" to fetch from YouTube.
                    </div>
                    <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                        <div v-for="video in videos" :key="video.id || video.platform_video_id" class="group flex flex-col">
                             <Link :href="`/videos/${video.id}`" class="block mb-2 overflow-hidden rounded-lg relative aspect-w-16 aspect-h-9 bg-gray-100">
                                <img :src="video.thumbnail_url" class="w-full h-full object-cover transform group-hover:scale-105 transition-transform duration-200" />
                                <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-10 transition-colors"></div>
                            </Link>
                            <div class="flex-1 flex flex-col">
                                <Link :href="`/videos/${video.id}`" class="text-sm font-bold text-gray-900 hover:text-indigo-600 line-clamp-2 mb-1" :title="video.title">
                                    {{ video.title }}
                                </Link>
                                <div class="text-xs text-gray-500 mt-auto">{{ formatDate(video.published_at) }}</div>
                            </div>
                        </div>
                    </div>
                    
                    <div v-if="videos.length > 0" class="mt-8 text-center">
                        <button @click="syncVideos" :disabled="syncing" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:text-gray-500 focus:outline-none transition">
                             <svg v-if="syncing" class="animate-spin -ml-1 mr-2 h-4 w-4 text-gray-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            {{ nextPageToken ? 'Load More from YouTube' : 'Sync Latest' }}
                        </button>
                    </div>
                </div>

            </div>
        </main>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import axios from 'axios';
import Header from '@/Components/Header.vue';

const props = defineProps({
    channel: Object,
});

const loading = ref(false);
const syncing = ref(false);
const videos = ref(props.channel.videos || []);
const nextPageToken = ref(null);

const reloadChannel = () => {
    loading.value = true;
    router.post(`/channels/${props.channel.id}/refresh`, {}, {
        preserveScroll: true,
        onFinish: () => {
             loading.value = false;
        }
    });
};

const syncVideos = async () => {
    syncing.value = true;
    try {
        const response = await axios.post(`/channels/${props.channel.id}/sync-videos`, {
            pageToken: nextPageToken.value
        });
        
        if (response.data.videos) {
            // Avoid duplicates if any, though likely new
            const newVideos = response.data.videos;
            // Append new videos
            // We might want to filter out existing ones if we are just "syncing latest" but here we trust the API or DB constraints
            // But for the UI list, let's just push them if they aren't there.
            
            // Actually, simplest is to push, Vue handles key defaults usually, but let's be safe.
            // We are using DB models in props, but separate objects from JSON in response.
            // They should share structure roughly.
            
            // Check based on platform_video_id
            const currentIds = new Set(videos.value.map(v => v.platform_video_id));
            response.data.videos.forEach(v => {
                if (!currentIds.has(v.platform_video_id)) {
                    videos.value.push(v);
                }
            });
            
            nextPageToken.value = response.data.nextPageToken;
        }
    } catch (error) {
        console.error("Sync failed", error);
        alert("Failed to sync videos.");
    } finally {
        syncing.value = false;
    }
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
