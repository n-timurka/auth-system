<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class RememberMeTest extends TestCase
{
    use RefreshDatabase;

    public function test_users_can_login_with_remember_me()
    {
        $user = User::factory()->create([
            'password' => Hash::make('password'),
            'remember_token' => null, // Ensure starts null
        ]);

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
            'remember' => 'on',
        ]);

        $response->assertRedirect('/dashboard');

        $this->assertAuthenticatedAs($user);

        $user->refresh();
        $this->assertNotNull($user->remember_token);

        // $response->assertCookie('remember_web_' . sha1(env('APP_KEY') . 'User'));
    }

    public function test_users_can_login_without_remember_me()
    {
        $user = User::factory()->create([
            'password' => Hash::make('password'),
            'remember_token' => null,
        ]);

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
            'remember' => '',
        ]);

        $response->assertRedirect('/dashboard');

        $this->assertAuthenticatedAs($user);

        $user->refresh();
        // If we didn't ask to remember, and it was null, it should stay null (or at least no remember cookie)
        $this->assertNull($user->remember_token);
        $response->assertCookieMissing('remember_web_' . sha1(env('APP_KEY') . 'User'));
    }

    public function test_user_resource_includes_is_remembered_status()
    {
        // User with token
        $user1 = User::factory()->create([
            'remember_token' => 'test-token',
        ]);

        // User without token
        $user2 = User::factory()->create([
            'remember_token' => null,
        ]);

        $this->actingAs($user1);

        $response = $this->get('/dashboard');

        $response->assertStatus(200);
        $response->assertInertia(
            fn($page) => $page
                ->component('Dashboard')
                ->has('users.data', 2)
                ->where('users.data.0.id', $user1->id)
                ->where('users.data.0.is_remembered', true)
                ->where('users.data.1.id', $user2->id)
                ->where('users.data.1.is_remembered', false)
        );
    }
}
