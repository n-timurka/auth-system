<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Channel;
use App\Services\GoogleService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Mockery\MockInterface;

class VideoSyncTest extends TestCase
{
    use RefreshDatabase;

    public function test_sync_videos_success()
    {
        $user = User::factory()->create();
        $channel = Channel::create([
            'user_id' => $user->id,
            'platform_channel_id' => 'UC_Test',
            'upload_playlist_id' => 'UU_Test',
            'name' => 'Test Channel',
            'type' => 'personal',
            'statistics' => [],
            'published_at' => now(),
            'last_synced_at' => now(),
        ]);

        $mockVideos = [
            'videos' => [
                [
                    'platform_video_id' => 'vid_123',
                    'title' => 'Video 1',
                    'description' => 'Desc 1',
                    'thumbnail_url' => 'http://thumb1',
                    'published_at' => now()->toIso8601String(),
                ],
                [
                    'platform_video_id' => 'vid_456',
                    'title' => 'Video 2',
                    'description' => 'Desc 2',
                    'thumbnail_url' => 'http://thumb2',
                    'published_at' => now()->subDay()->toIso8601String(),
                ]
            ],
            'nextPageToken' => 'next_token_abc'
        ];

        $this->mock(GoogleService::class, function (MockInterface $mock) use ($mockVideos) {
            $mock->shouldReceive('fetchVideos')
                ->once()
                ->with('UU_Test', null)
                ->andReturn($mockVideos);
        });

        $response = $this->actingAs($user)->postJson(route('channels.syncVideos', $channel));

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Videos synced successfully.',
                'nextPageToken' => 'next_token_abc'
            ]);

        $response->assertJsonCount(2, 'videos');

        $this->assertDatabaseHas('videos', [
            'channel_id' => $channel->id,
            'platform_video_id' => 'vid_123',
            'title' => 'Video 1',
        ]);

        $this->assertDatabaseHas('videos', [
            'channel_id' => $channel->id,
            'platform_video_id' => 'vid_456',
        ]);
    }

    public function test_sync_videos_pagination()
    {
        $user = User::factory()->create();
        $channel = Channel::create([
            'user_id' => $user->id,
            'platform_channel_id' => 'UC_Test_Page',
            'upload_playlist_id' => 'UU_Test_Page',
            'name' => 'Test Channel Page',
            'type' => 'personal',
            'statistics' => [],
            'published_at' => now(),
            'last_synced_at' => now(),
        ]);

        $mockVideos = [
            'videos' => [],
            'nextPageToken' => 'page_2'
        ];

        $this->mock(GoogleService::class, function (MockInterface $mock) use ($mockVideos) {
            $mock->shouldReceive('fetchVideos')
                ->once()
                ->with('UU_Test_Page', 'page_1')
                ->andReturn($mockVideos);
        });

        $response = $this->actingAs($user)->postJson(route('channels.syncVideos', $channel), [
            'pageToken' => 'page_1'
        ]);

        $response->assertStatus(200);
    }
    public function test_sync_videos_public_channel_access_allowed()
    {
        // Public channel created by User A
        $userA = User::factory()->create();
        $channel = Channel::create([
            'user_id' => $userA->id,
            'platform_channel_id' => 'UC_Public',
            'upload_playlist_id' => 'UU_Public',
            'name' => 'Public Channel',
            'type' => 'public',
            'statistics' => [],
            'published_at' => now(),
            'last_synced_at' => now(),
        ]);

        // User B tries to sync it
        $userB = User::factory()->create();

        $mockVideos = [
            'videos' => [],
            'nextPageToken' => null
        ];

        $this->mock(GoogleService::class, function (MockInterface $mock) use ($mockVideos) {
            $mock->shouldReceive('fetchVideos')
                ->once()
                ->with('UU_Public', null)
                ->andReturn($mockVideos);
        });

        $response = $this->actingAs($userB)->postJson(route('channels.syncVideos', $channel));

        $response->assertStatus(200);
    }

    public function test_sync_videos_personal_channel_access_denied_for_non_owner()
    {
        $userA = User::factory()->create();
        $channel = Channel::create([
            'user_id' => $userA->id,
            'platform_channel_id' => 'UC_Personal',
            'upload_playlist_id' => 'UU_Personal',
            'name' => 'Personal Channel',
            'type' => 'personal',
            'statistics' => [],
            'published_at' => now(),
            'last_synced_at' => now(),
        ]);

        $userB = User::factory()->create();

        $response = $this->actingAs($userB)->postJson(route('channels.syncVideos', $channel));

        $response->assertStatus(403);
    }
}
