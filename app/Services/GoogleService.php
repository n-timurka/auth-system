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
     * Fetch YouTube channel data by ID or URL.
     *
     * @param string $input
     * @return array|null
     */
    public function fetchChannelByUrlOrId(string $input)
    {
        $channelId = null;
        $username = null;
        $searchQuery = null;

        // 1. Check for strict Channel ID (in URL or raw)
        // Matches: youtube.com/channel/UC..., or just UC... (24 chars starting with UC)
        if (preg_match('/(?:channel\/|^)(UC[\w-]{21}[AQgw])/', $input, $matches)) {
            $channelId = $matches[1];
        }
        // 2. Check for Legacy Username (in URL)
        // Matches: youtube.com/user/username
        elseif (preg_match('/user\/([^\/\?]+)/', $input, $matches)) {
            $username = $matches[1];
        }
        // 3. Check for Handle (in URL or raw)
        // Matches: youtube.com/@handle, or @handle
        elseif (preg_match('/(?:@|^@)([^\/\?]+)/', $input, $matches)) {
            // For handles, we can search with the @ symbol
            $searchQuery = '@' . $matches[1];
        }
        // 4. Check for Custom URL (youtube.com/c/name or youtube.com/name) or just a name
        else {
            if (preg_match('/youtube\.com\/(?:c\/)?([^\/\?]+)/', $input, $matches)) {
                $searchQuery = $matches[1];
            } else {
                // Assume it's a channel name or search term
                $searchQuery = $input;
            }
        }

        try {
            $client = $this->getClient();
            if (!$client) {
                return null;
            }

            $youtube = new YouTube($client);

            $targetChannelId = $channelId;

            // If we don't have an ID yet, we need to resolve it
            if (!$targetChannelId) {
                if ($username) {
                    // Try legacy username lookup first
                    $channelsResponse = $youtube->channels->listChannels('id', [
                        'forUsername' => $username
                    ]);
                    if (!empty($channelsResponse->items)) {
                        $targetChannelId = $channelsResponse->items[0]->id;
                    }
                }

                // If still no ID (because it was a search query, handle, or username lookup failed)
                if (!$targetChannelId && ($searchQuery || $username)) {
                    $query = $searchQuery ?: $username;
                    $searchResponse = $youtube->search->listSearch('snippet', [
                        'q' => $query,
                        'type' => 'channel',
                        'maxResults' => 1
                    ]);

                    if (!empty($searchResponse->items)) {
                        $targetChannelId = $searchResponse->items[0]->snippet->channelId;
                    }
                }
            }

            if (!$targetChannelId) {
                return null;
            }

            // Finally, fetch full details using the ID
            $channelsResponse = $youtube->channels->listChannels('snippet,statistics,contentDetails', [
                'id' => $targetChannelId
            ]);

            if (empty($channelsResponse->items)) {
                return null;
            }

            return $this->formatChannelData($youtube, $channelsResponse->items[0]);

        } catch (\Google\Service\Exception $e) {
            // Check for 401 and try to refresh
            if ($e->getCode() == 401) {
                if ($this->refreshToken()) {
                    // Retry recursively once
                    return $this->fetchChannelByUrlOrId($input);
                }
            }
            Log::error('YouTube API Error (fetchChannel): ' . $e->getMessage());
            return null;
        } catch (\Exception $e) {
            Log::error('General Error (fetchChannel): ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Fetch YouTube channel data using the access token (for current user).
     *
     * @param string $token
     * @return array|null
     */
    public function getYouTubeChannelData(string $token)
    {
        try {
            $client = $this->getClient($token);
            if (!$client) {
                $client = new Client();
                $client->setHttpClient(new \GuzzleHttp\Client(['verify' => false]));
                $client->setAccessToken($token);
            }

            $youtube = new YouTube($client);

            $channelsResponse = $youtube->channels->listChannels('snippet,statistics,contentDetails', [
                'mine' => true
            ]);

            if (empty($channelsResponse->items)) {
                return null;
            }

            return $this->formatChannelData($youtube, $channelsResponse->items[0]);

        } catch (\Exception $e) {
            Log::error('YouTube API Error: ' . $e->getMessage());
            return null;
        }
    }

    private function getClient($manualToken = null)
    {
        $client = new Client();
        $client->setHttpClient(new \GuzzleHttp\Client(['verify' => false]));
        $client->setClientId(config('services.google.client_id'));
        $client->setClientSecret(config('services.google.client_secret'));
        $client->setAccessType('offline');

        if ($manualToken) {
            $client->setAccessToken($manualToken);
            return $client;
        }

        $user = auth()->user();
        if (!$user) {
            return null;
        }

        $token = $user->google_token;
        if (!$token) {
            return null;
        }

        $client->setAccessToken($token);

        // Check if expired and refresh if possible
        if ($client->isAccessTokenExpired() && $user->google_refresh_token) {
            $client->refreshToken($user->google_refresh_token);
            $newCard = $client->getAccessToken();

            // Update user
            $user->update([
                'google_token' => $newCard['access_token'],
            ]);
            // Save to session if needed
            session(['google_access_token' => $newCard['access_token']]);
        }

        return $client;
    }

    private function refreshToken()
    {
        try {
            $user = auth()->user();
            if (!$user || !$user->google_refresh_token) {
                return false;
            }

            $client = new Client();
            $client->setHttpClient(new \GuzzleHttp\Client(['verify' => false]));
            $client->setClientId(config('services.google.client_id'));
            $client->setClientSecret(config('services.google.client_secret'));
            $client->setAccessType('offline');

            $client->refreshToken($user->google_refresh_token);
            $newToken = $client->getAccessToken();

            if (isset($newToken['access_token'])) {
                $user->update(['google_token' => $newToken['access_token']]);
                session(['google_access_token' => $newToken['access_token']]);
                return true;
            }
            return false;
        } catch (\Exception $e) {
            Log::error('Token Refresh Error: ' . $e->getMessage());
            return false;
        }
    }

    private function formatChannelData($youtube, $channel)
    {
        $channelData = [
            'platform_channel_id' => $channel->id,
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
    }
}
