<template>
    <div class="min-h-screen bg-gray-100">
        <Header />

        <main class="py-6">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">
                
                <div class="mb-4">
                    <Link :href="`/channels/${channel.platform_channel_id}`" class="text-indigo-600 hover:text-indigo-800 flex items-center gap-1 font-medium">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                        </svg>
                        Back to Channel
                    </Link>
                </div>

                <div class="bg-white shadow overflow-hidden sm:rounded-lg">
                    <div class="flex justify-between items-center p-6">
                        <div class="flex-1">
                            <h1 class="text-2xl font-bold text-gray-900">{{ video.title }}</h1>
                            <div class="mt-1 text-sm text-gray-500">
                                Published on {{ formatDate(video.published_at) }}
                            </div>
                        </div>
                        <div class="ml-4">
                            <Button @click="refreshData" :disabled="refreshing">
                                <svg v-if="refreshing" class="animate-spin -ml-1 mr-2 h-4 w-4 text-gray-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                <svg v-else class="-ml-1 mr-2 h-4 w-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                                </svg>
                                Refresh Data
                            </Button>
                        </div>
                    </div>

                    <!-- Video Player Container -->
                    <div class="w-full aspect-video bg-black">
                        <iframe 
                            class="w-full h-full"
                            :src="`https://www.youtube.com/embed/${video.platform_video_id}?autoplay=0`" 
                            title="YouTube video player" 
                            frameborder="0" 
                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" 
                            allowfullscreen>
                        </iframe>
                    </div>

                    <div class="px-4 py-5 sm:px-6">
                        <!-- Statistics -->
                        <div class="mt-6 grid grid-cols-3 gap-4 border-t border-b border-gray-200 py-4">
                            <div class="text-center">
                                <div class="text-2xl font-semibold text-gray-900">{{ formatNumber(video.view_count) }}</div>
                                <div class="text-xs text-gray-500 uppercase tracking-wide">Views</div>
                            </div>
                            <div class="text-center border-l border-gray-200">
                                <div class="text-2xl font-semibold text-gray-900">{{ formatNumber(video.like_count) }}</div>
                                <div class="text-xs text-gray-500 uppercase tracking-wide">Likes</div>
                            </div>
                            <div class="text-center border-l border-gray-200">
                                <div class="text-2xl font-semibold text-gray-900">{{ formatNumber(video.comment_count) }}</div>
                                <div class="text-xs text-gray-500 uppercase tracking-wide">Comments</div>
                            </div>
                        </div>

                        <div class="mt-6">
                            <h3 class="text-sm font-medium text-gray-900">Description</h3>
                            <div class="mt-2 text-sm text-gray-700 whitespace-pre-wrap bg-gray-50 p-4 rounded-lg max-h-96 overflow-y-auto">
                                {{ video.description }}
                            </div>
                        </div>
                        
                         <div class="mt-4 text-xs text-gray-400 text-right">
                             Last updated: {{ formatDate(video.updated_at, true) }}
                         </div>
                    </div>
                </div>

            </div>
        </main>
    </div>
</template>

<script setup>
import { ref } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import Header from '@/Components/Header.vue';
import Button from '@/Components/Button.vue';

const props = defineProps({
    video: Object,
    channel: Object,
});

const refreshing = ref(false);

const refreshData = () => {
    refreshing.value = true;
    router.post(`/videos/${props.video.id}/refresh`, {}, {
        preserveScroll: true,
        onFinish: () => {
            refreshing.value = false;
        }
    });
};

const formatNumber = (num) => {
    if (num === null || num === undefined) return '-';
    return new Intl.NumberFormat('en-US', { notation: "compact", compactDisplay: "short" }).format(num);
};

const formatDate = (dateString, includeTime = false) => {
    if (!dateString) return 'N/A';
    const options = { year: 'numeric', month: 'long', day: 'numeric' };
    if (includeTime) {
        options.hour = '2-digit';
        options.minute = '2-digit';
    }
    return new Date(dateString).toLocaleDateString(undefined, options);
};
</script>
