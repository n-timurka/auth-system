<?php

namespace App\Http\Controllers;

use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\SignupRequest;
use App\Models\SiteSetting;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;
use Inertia\Response;

class AuthController extends Controller
{
    /**
     * SignUp page
     * 
     * @return \Inertia\Response
     */
    public function create(): Response
    {
        return Inertia::render('Auth/SignUp');
    }

    /**
     * SignUp
     * 
     * @param Request $request
     * @return RedirectResponse
     */
    public function store(SignupRequest $request): RedirectResponse
    {
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        Auth::login($user);

        if (SiteSetting::get('email_verification_enabled', true)) {
            $user->sendEmailVerificationNotification();
        }

        return redirect()->intended('/dashboard');
    }

    /**
     * Login page
     *
     * @return \Inertia\Response
     */
    public function login(): Response
    {
        $rememberMeEnabled = SiteSetting::get('remember_me_enabled', true);

        return Inertia::render('Auth/Login', [
            'rememberMeEnabled' => $rememberMeEnabled,
        ]);
    }

    /**
     * Handle an incoming authentication request.
     */
    public function authenticate(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        return redirect()->intended('/dashboard');
    }

    /**
     * Logout
     * 
     * @param Request $request
     * @return RedirectResponse
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
