<template>
    <div class="min-h-screen bg-gray-100">
        <Header />

        <main class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <Card>
                    <div v-if="!isConnected" class="text-center py-12">
                        <h2 class="text-2xl font-bold text-gray-900 mb-4">Connect your YouTube Channel</h2>
                        <p class="text-gray-600 mb-8">Authorize with Google to view your channel statistics and videos.</p>
                        <a href="/auth/google" class="inline-flex items-center px-4 py-2 bg-red-600 text-white text-sm font-medium rounded-md hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-colors">
                            <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path fill-rule="evenodd" d="M19.812 5.418c.861.23 1.538.907 1.768 1.768C21.998 8.746 22 12 22 12s0 3.255-.418 4.814a2.504 2.504 0 0 1-1.768 1.768c-1.56.419-7.814.419-7.814.419s-6.255 0-7.814-.419a2.505 2.505 0 0 1-1.768-1.768C2 15.255 2 12 2 12s0-3.254.418-4.814a2.507 2.507 0 0 1 1.768-1.768C5.744 5 11.998 5 11.998 5s6.255 0 7.814.418ZM15.194 12 10 15V9l5.194 3Z" clip-rule="evenodd" />
                            </svg>
                            Connect with Google
                        </a>
                    </div>

                    <div v-else-if="channelData">
                        <div class="flex items-center mb-8">
                            <img :src="channelData.thumbnails.medium.url" alt="Channel Avatar" class="w-16 h-16 rounded-full mr-4 border-2 border-gray-200">
                            <div>
                                <h2 class="text-2xl font-bold text-gray-900">{{ channelData.title }}</h2>
                                <div class="flex items-center text-sm text-gray-600 mt-1">
                                    <span class="mr-4"><strong>{{ formatNumber(channelData.statistics.subscriberCount) }}</strong> subscribers</span>
                                    <span class="mr-4"><strong>{{ formatNumber(channelData.statistics.videoCount) }}</strong> videos</span>
                                    <span><strong>{{ formatNumber(channelData.statistics.viewCount) }}</strong> views</span>
                                </div>
                            </div>
                        </div>
                        
                        <p class="text-gray-700 mb-8 whitespace-pre-wrap max-w-3xl">{{ channelData.description }}</p>

                        <h3 class="text-xl font-semibold mb-4 border-b pb-2">Recent Uploads</h3>
                        <div v-if="channelData.videos.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            <div v-for="video in channelData.videos" :key="video.id" class="bg-white border rounded-lg overflow-hidden shadow-sm hover:shadow-md transition-shadow flex flex-col">
                                <a :href="`https://www.youtube.com/watch?v=${video.id}`" target="_blank" class="block relative group">
                                    <img :src="video.thumbnail" :alt="video.title" class="w-full h-48 object-cover">
                                    <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-10 transition-opacity flex items-center justify-center">
                                         <svg class="w-12 h-12 text-white opacity-0 group-hover:opacity-80 transition-opacity" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM9.555 7.168A1 1 0 008 8v4a1 1 0 001.555.832l3-2a1 1 0 000-1.664l-3-2z" clip-rule="evenodd" /></svg>
                                    </div>
                                </a>
                                <div class="p-4 flex-1 flex flex-col">
                                    <h4 class="font-semibold text-lg mb-2 line-clamp-2 text-gray-900">
                                        <a :href="`https://www.youtube.com/watch?v=${video.id}`" target="_blank" class="hover:text-indigo-600">
                                            {{ video.title }}
                                        </a>
                                    </h4>
                                    <p class="text-gray-500 text-xs mb-3">{{ formatDate(video.publishedAt) }}</p>
                                    <p class="text-gray-600 text-sm line-clamp-3 mb-4 flex-1">{{ video.description }}</p>
                                </div>
                            </div>
                        </div>
                         <div v-else class="text-gray-500 italic">No recent videos found.</div>
                    </div>
                </Card>
            </div>
        </main>
    </div>
</template>

<script setup>
import Header from '@/Components/Header.vue';
import Card from '@/Components/Card.vue';

const props = defineProps({
    channelData: Object,
    isConnected: Boolean,
});

const formatNumber = (num) => {
    return new Intl.NumberFormat('en-US', { notation: "compact", compactDisplay: "short" }).format(num);
};

const formatDate = (dateString) => {
    return new Date(dateString).toLocaleDateString(undefined, { year: 'numeric', month: 'long', day: 'numeric' });
};
</script>
