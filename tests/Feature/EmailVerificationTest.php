<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class EmailVerificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_verification_email_is_sent_on_registration(): void
    {
        Notification::fake();

        $response = $this->post('/signup', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        $response->assertRedirect('/dashboard');

        $user = User::where('email', 'test@example.com')->first();
        $this->assertNotNull($user);

        // Verify notification was sent
        Notification::assertSentTo($user, VerifyEmail::class);
    }

    public function test_user_can_verify_email_with_valid_link(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => null,
        ]);

        $this->assertNull($user->email_verified_at);

        // Generate signed verification URL
        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $user->id, 'hash' => sha1($user->email)]
        );

        $response = $this->actingAs($user)->get($verificationUrl);

        $response->assertRedirect('/dashboard');

        $user->refresh();
        $this->assertNotNull($user->email_verified_at);
    }

    public function test_user_cannot_verify_with_invalid_hash(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => null,
        ]);

        // Generate URL with invalid hash
        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $user->id, 'hash' => 'invalid-hash']
        );

        $response = $this->actingAs($user)->get($verificationUrl);

        $response->assertStatus(403); // Forbidden

        $user->refresh();
        $this->assertNull($user->email_verified_at);
    }

    public function test_user_resource_includes_verification_dates(): void
    {
        $verifiedUser = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $unverifiedUser = User::factory()->create([
            'email_verified_at' => null,
        ]);

        $response = $this->actingAs($verifiedUser)->get('/dashboard');

        $response->assertStatus(200);
        $response->assertInertia(
            fn($page) => $page
                ->component('Dashboard')
                ->has('users.data', 2)
                ->where('users.data.0.email_verified_at', fn($value) => $value !== null)
                ->where('users.data.0.created_at', fn($value) => $value !== null)
                ->where('users.data.1.email_verified_at', null)
        );
    }

    public function test_resend_verification_email(): void
    {
        Notification::fake();

        $user = User::factory()->create([
            'email_verified_at' => null,
        ]);

        $response = $this->actingAs($user)->post('/email/verification-notification');

        $response->assertRedirect();

        Notification::assertSentTo($user, VerifyEmail::class);
    }
}
