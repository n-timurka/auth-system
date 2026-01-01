<?php

namespace App\Services;

use Laravel\Socialite\Facades\Socialite;
use Google\Client;
use Google\Service\YouTube;
use Illuminate\Support\Facades\Log;

class GoogleService
{
    /**
     * Get the Google OAuth redirect URL.
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function getAuthUrl()
    {
        return Socialite::driver('google')
            ->scopes(['https://www.googleapis.com/auth/youtube.readonly'])
            ->with(['access_type' => 'offline', 'prompt' => 'consent select_account'])
            ->setHttpClient(new \GuzzleHttp\Client(['verify' => false])) // Fix for local SSL error
            ->redirect();
    }

    /**
     * Get the Google User from the callback.
     *
     * @return \Laravel\Socialite\Two\User
     */
    public function getUser()
    {
        return Socialite::driver('google')
            ->setHttpClient(new \GuzzleHttp\Client(['verify' => false])) // Fix for local SSL error
            ->user();
    }

    /**
     * Fetch YouTube channel data using the access token.
     *
     * @param string $token
     * @return array|null
     */
    public function getYouTubeChannelData(string $token)
    {
        try {
            $client = new Client();
            $client->setHttpClient(new \GuzzleHttp\Client(['verify' => false])); // Fix for local SSL error
            $client->setAccessToken($token);
            $youtube = new YouTube($client);

            // Get Channel Info
            $channelsResponse = $youtube->channels->listChannels('snippet,statistics,contentDetails', [
                'mine' => true
            ]);

            if (empty($channelsResponse->items)) {
                return null;
            }

            $channel = $channelsResponse->items[0];
            $channelData = [
                'title' => $channel->snippet->title,
                'description' => $channel->snippet->description,
                'customUrl' => $channel->snippet->customUrl,
                'publishedAt' => $channel->snippet->publishedAt,
                'thumbnails' => $channel->snippet->thumbnails,
                'statistics' => $channel->statistics,
                'contentDetails' => $channel->contentDetails,
                'videos' => [],
            ];

            // Get Recent Videos (from uploads playlist)
            $uploadsPlaylistId = $channel->contentDetails->relatedPlaylists->uploads;

            if ($uploadsPlaylistId) {
                $playlistItemsResponse = $youtube->playlistItems->listPlaylistItems('snippet,contentDetails', [
                    'playlistId' => $uploadsPlaylistId,
                    'maxResults' => 10
                ]);

                foreach ($playlistItemsResponse->items as $video) {
                    $channelData['videos'][] = [
                        'id' => $video->contentDetails->videoId,
                        'title' => $video->snippet->title,
                        'description' => $video->snippet->description,
                        'thumbnail' => $video->snippet->thumbnails->medium->url ?? $video->snippet->thumbnails->default->url,
                        'publishedAt' => $video->snippet->publishedAt,
                    ];
                }
            }

            return $channelData;

        } catch (\Exception $e) {
            Log::error('YouTube API Error: ' . $e->getMessage());
            return null;
        }
    }
}
