<?php

namespace App\Http\Controllers;

use App\Services\GoogleService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Enums\Role;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class GoogleController extends Controller
{
    public function __construct(protected GoogleService $googleService)
    {
    }

    /**
     * Redirect to Google login page
     * 
     * @return RedirectResponse
     */
    public function redirectToGoogle(): RedirectResponse
    {
        return $this->googleService->getAuthUrl();
    }

    public function handleGoogleCallback()
    {
        try {
            $googleUser = $this->googleService->getUser();
            $token = $googleUser->token;

            $user = User::where('google_id', $googleUser->id)->first();
            $redirectRoute = 'dashboard';

            // If user is already logged in, connect this google account
            if (Auth::check()) {
                $currentUser = Auth::user();
                $currentUser->update([
                    'google_id' => $googleUser->id,
                    'google_token' => $token,
                    'google_refresh_token' => $googleUser->refreshToken, // Note: refreshToken might be null if already approved once
                    'avatar' => $googleUser->avatar,
                ]);
                $redirectRoute = 'channels.index';
            }
            // If user exists with google_id, log them in
            elseif ($user) {
                $user->update([
                    'google_token' => $token,
                    'google_refresh_token' => $googleUser->refreshToken,
                    'avatar' => $googleUser->avatar,
                ]);
                Auth::login($user);
            }
            // Check if user exists with same email
            elseif ($existingUser = User::where('email', $googleUser->email)->first()) {
                $existingUser->update([
                    'google_id' => $googleUser->id,
                    'google_token' => $token,
                    'google_refresh_token' => $googleUser->refreshToken,
                    'avatar' => $googleUser->avatar,
                ]);
                Auth::login($existingUser);
            }
            // Create new user
            else {
                $newUser = User::create([
                    'name' => $googleUser->name,
                    'email' => $googleUser->email,
                    'google_id' => $googleUser->id,
                    'google_token' => $token,
                    'google_refresh_token' => $googleUser->refreshToken,
                    'avatar' => $googleUser->avatar,
                    'password' => Hash::make(Str::random(16)), // Random password
                    'email_verified_at' => now(), // Auto-verify email from Google
                    'role' => Role::USER, // Default role
                ]);
                Auth::login($newUser);
            }

            // Common actions after auth
            session(['google_access_token' => $token]);
            $this->updatePersonalChannel($token);

            return redirect()->route($redirectRoute);
        } catch (\Exception $e) {
            Log::error('Google Login Error: ' . $e->getMessage());
            return redirect()->route('login')->with('error', 'Failed to login with Google.');
        }
    }

    private function updatePersonalChannel($token)
    {
        try {
            $channelData = $this->googleService->getYouTubeChannelData($token);

            if ($channelData) {
                $userId = Auth::id();

                \App\Models\Channel::updateOrCreate(
                    [
                        'user_id' => $userId,
                        'type' => 'personal'
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
            }
        } catch (\Exception $e) {
            Log::error('Failed to update personal channel: ' . $e->getMessage());
        }
    }
}
