<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Channel;
use App\Services\GoogleService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Mockery\MockInterface;

class ChannelStoreTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
    }

    public function test_store_validation_errors()
    {
        $user = User::factory()->create();

        // 1. Missing type
        $response = $this->actingAs($user)->postJson(route('channels.store'), []);
        $response->assertJsonValidationErrors(['type']);

        // 2. Invalid type
        $response = $this->actingAs($user)->postJson(route('channels.store'), ['type' => 'invalid']);
        $response->assertJsonValidationErrors(['type']);

        // 3. Public without channel_input
        $response = $this->actingAs($user)->postJson(route('channels.store'), ['type' => 'public']);
        $response->assertJsonValidationErrors(['channel_input']);
    }

    public function test_store_personal_channel_success()
    {
        $user = User::factory()->create([
            'google_token' => 'fake-token',
        ]);

        $mockChannelData = [
            'platform_channel_id' => 'UC_Personal_123',
            'title' => 'My Personal Channel',
            'description' => 'Desc',
            'customUrl' => '@mypersonal',
            'thumbnails' => (object) ['medium' => (object) ['url' => 'http://thumb.url']],
            'statistics' => (object) ['viewCount' => 100, 'subscriberCount' => 10, 'videoCount' => 5],
            'videos' => [],
            'publishedAt' => now()->toIso8601String(),
        ];

        $this->mock(GoogleService::class, function (MockInterface $mock) use ($mockChannelData) {
            $mock->shouldReceive('getYouTubeChannelData')
                ->once()
                ->with('fake-token')
                ->andReturn($mockChannelData);
        });

        $response = $this->actingAs($user)->post(route('channels.store'), [
            'type' => 'personal',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('message', 'Personal channel added successfully.');

        $this->assertDatabaseHas('channels', [
            'user_id' => $user->id,
            'type' => 'personal',
            'platform_channel_id' => 'UC_Personal_123',
            'name' => 'My Personal Channel',
        ]);
    }

    public function test_store_public_channel_success()
    {
        $user = User::factory()->create();

        $mockChannelData = [
            'platform_channel_id' => 'UC_Public_456',
            'title' => 'Some Public Channel',
            'description' => 'Public Desc',
            'customUrl' => '@somepublic',
            'thumbnails' => (object) ['medium' => (object) ['url' => 'http://thumb.public.url']],
            'statistics' => (object) ['viewCount' => 200, 'subscriberCount' => 50, 'videoCount' => 2],
            'videos' => [],
            'publishedAt' => now()->toIso8601String(),
        ];

        $this->mock(GoogleService::class, function (MockInterface $mock) use ($mockChannelData) {
            $mock->shouldReceive('fetchChannelByUrlOrId')
                ->once()
                ->with('UC_Public_456')
                ->andReturn($mockChannelData);
        });

        $response = $this->actingAs($user)->post(route('channels.store'), [
            'type' => 'public',
            'channel_input' => 'UC_Public_456',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('message', 'Public channel added successfully.');

        $this->assertDatabaseHas('channels', [
            'type' => 'public',
            'platform_channel_id' => 'UC_Public_456',
            'name' => 'Some Public Channel',
        ]);
    }

    public function test_store_personal_channel_no_token()
    {
        $user = User::factory()->create([
            'google_token' => null,
        ]);

        $response = $this->actingAs($user)->post(route('channels.store'), [
            'type' => 'personal',
        ]);

        $response->assertSessionHasErrors(['message' => 'Please connect your Google account first.']);
    }
}
