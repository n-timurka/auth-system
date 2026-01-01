<?php

namespace App\Http\Controllers;

use App\Services\GoogleService;
use Inertia\Inertia;
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

    public function redirectToGoogle()
    {
        return $this->googleService->getAuthUrl();
    }

    public function handleGoogleCallback()
    {
        try {
            $googleUser = $this->googleService->getUser();

            $user = User::where('google_id', $googleUser->id)->first();

            // If user is already logged in, connect this google account
            if (Auth::check()) {
                $currentUser = Auth::user();
                $currentUser->update([
                    'google_id' => $googleUser->id,
                    'google_token' => $googleUser->token,
                    'google_refresh_token' => $googleUser->refreshToken,
                    'avatar' => $googleUser->avatar,
                ]);

                // Also update session token for immediate API use
                session(['google_access_token' => $googleUser->token]);

                return redirect()->route('channels.index');
            }

            // If user exists with google_id, log them in
            if ($user) {
                // Update tokens
                $user->update([
                    'google_token' => $googleUser->token,
                    'google_refresh_token' => $googleUser->refreshToken,
                    'avatar' => $googleUser->avatar,
                ]);

                Auth::login($user);
                session(['google_access_token' => $googleUser->token]);

                return redirect()->route('dashboard');
            }

            // Check if user exists with same email
            $existingUser = User::where('email', $googleUser->email)->first();

            if ($existingUser) {
                // Determine if we should auto-link. 
                // Security Note: Only auto-link if we trust the provider's email verification.
                // Google emails are verified.
                $existingUser->update([
                    'google_id' => $googleUser->id,
                    'google_token' => $googleUser->token,
                    'google_refresh_token' => $googleUser->refreshToken,
                    'avatar' => $googleUser->avatar,
                ]);

                Auth::login($existingUser);
                session(['google_access_token' => $googleUser->token]);

                return redirect()->route('dashboard');
            }

            // Create new user
            $newUser = User::create([
                'name' => $googleUser->name,
                'email' => $googleUser->email,
                'google_id' => $googleUser->id,
                'google_token' => $googleUser->token,
                'google_refresh_token' => $googleUser->refreshToken,
                'avatar' => $googleUser->avatar,
                'password' => Hash::make(Str::random(16)), // Random password
                'email_verified_at' => now(), // Auto-verify email from Google
                'role' => Role::USER, // Default role
            ]);

            Auth::login($newUser);
            session(['google_access_token' => $googleUser->token]);

            return redirect()->route('dashboard');

        } catch (\Exception $e) {
            Log::error('Google Login Error: ' . $e->getMessage());
            return redirect()->route('login')->with('error', 'Failed to login with Google.');
        }
    }

    public function index()
    {
        // Prioritize session token, fallback to user's stored token
        $token = session('google_access_token');

        if (!$token && Auth::check()) {
            $token = Auth::user()->google_token;
            if ($token) {
                session(['google_access_token' => $token]);
            }
        }

        $channelData = null;
        if ($token) {
            $channelData = $this->googleService->getYouTubeChannelData($token);

            if (!$channelData) {
                // Token might be expired or invalid
                session()->forget('google_access_token');
            }
        }

        return Inertia::render('Channels', [
            'channelData' => $channelData,
            'isConnected' => !empty($token) && !empty($channelData),
        ]);
    }
}
