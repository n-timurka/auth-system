<?php

namespace App\Http\Controllers;

use App\Models\Channel;
use App\Services\GoogleService;
use App\Http\Requests\Auth\ChannelStoreRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class ChannelController extends Controller
{
    public function __construct(protected GoogleService $googleService)
    {
    }

    /**
     * List of channels
     * 
     * @return Response
     */
    public function index(): Response
    {
        $user = Auth::user();

        $personalChannel = $user
            ? Channel::where('user_id', $user->id)
                ->where('type', 'personal')
                ->first()
            : null;

        $publicChannels = Channel::where('type', 'public')
            ->orderBy('created_at', 'desc')
            ->get();

        return Inertia::render('Channels', [
            'personalChannel' => $personalChannel,
            'publicChannels' => $publicChannels,
            'isConnected' => $user && !!$user->google_token,
        ]);
    }

    /**
     * Store a new channel (personal or public)
     * 
     * @param ChannelStoreRequest $request
     * @return RedirectResponse
     */
    public function store(ChannelStoreRequest $request): RedirectResponse
    {
        $type = $request->input('type');
        $user = Auth::user();

        if ($type === 'personal' && !$user->google_token) {
            return back()->withErrors(['message' => 'Please connect your Google account first.']);
        }

        $channelData = $type === 'personal'
            ? $this->googleService->getYouTubeChannelData($user->google_token)
            : $this->googleService->fetchChannelByUrlOrId($request->input('channel_input'));

        if (!$channelData) {
            return back()->withErrors([
                'message' => $type === 'personal'
                    ? 'Could not find a YouTube channel associated with your specific Google account.'
                    : 'Could not find channel or invalid input. Make sure you are connected to Google to use the search.'
            ]);
        }

        // Check if exists
        $channel = Channel::where('platform_channel_id', $channelData['platform_channel_id'])->first();
        if ($channel) {
            if ($channel->type === 'personal' && $channel->user_id === Auth::id()) {
                return back()->with('message', 'You already have this as your personal channel.');
            }
            if ($channel->type === 'public') {
                return back()->with('message', 'This channel is already in the public list.');
            }

            return back()->with('message', 'Channel already exists in the system.');
        }

        try {
            Channel::updateOrCreate(
                [
                    'user_id' => $user->id,
                    'type' => $type,
                ],
                [
                    'platform_channel_id' => $channelData['platform_channel_id'],
                    'name' => $channelData['title'],
                    'description' => $channelData['description'],
                    'custom_url' => $channelData['customUrl'],
                    'thumbnail_url' => $channelData['thumbnails']->medium->url ?? $channelData['thumbnails']->default->url ?? null,
                    'statistics' => $channelData['statistics'],
                    'videos' => $channelData['videos'],
                    'published_at' => $channelData['publishedAt'],
                    'last_synced_at' => now(),
                ]
            );

            return back()->with('message', $type === 'personal'
                ? 'Personal channel added successfully.'
                : 'Public channel added successfully.');
        } catch (\Exception $e) {
            return back()->withErrors(['message' => 'Failed to fetch channel data: ' . $e->getMessage()]);
        }
    }

    /**
     * Channel details page
     * 
     * @param mixed $platform_channel_id
     * @return Response
     */
    public function show($platform_channel_id): Response
    {
        $channel = Channel::where('platform_channel_id', $platform_channel_id)->firstOrFail();

        return Inertia::render('Channel/Show', [
            'channel' => $channel
        ]);
    }


    /**
     * Refresh channel data
     * 
     * @param Channel $channel
     * @return RedirectResponse
     */
    public function refresh(Channel $channel): RedirectResponse
    {
        if ($channel->user_id !== Auth::id()) {
            return back()->withErrors(['message' => 'Unauthorized to refresh this channel.']);
        }

        $token = session('google_access_token') ?? Auth::user()->google_token;
        if (!$token) {
            return back()->withErrors(['message' => 'No access token available to refresh data. Please connect Google account.']);
        }

        $newData = $this->googleService->fetchChannelByUrlOrId($channel->platform_channel_id);

        if (!$newData) {
            return back()->withErrors(['message' => 'Failed to refresh channel data.']);
        }

        $channel->update([
            'name' => $newData['title'],
            'description' => $newData['description'],
            'custom_url' => $newData['customUrl'],
            'thumbnail_url' => $newData['thumbnails']->medium->url ?? $newData['thumbnails']->default->url ?? null,
            'statistics' => $newData['statistics'],
            'videos' => $newData['videos'],
            'last_synced_at' => now(),
        ]);

        return back()->with('message', 'Channel data refreshed.');
    }

    /**
     * Delete channel from public list
     * 
     * @param Channel $channel
     * @return RedirectResponse
     */
    public function destroy(Channel $channel): RedirectResponse
    {
        $channel->delete();

        return back()->with('message', 'Channel deleted successfully.');
    }
}
