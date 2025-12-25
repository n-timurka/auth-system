<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class UserListTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_view_dashboard_with_users_list()
    {
        $user = User::factory()->create();
        $otherUsers = User::factory(3)->create();

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertStatus(200);
        $response->assertInertia(
            fn(Assert $page) => $page
                ->component('Dashboard')
                ->has('users.data', 4) // 3 others + current user
                ->has(
                    'users.data.0',
                    fn(Assert $json) => $json
                        ->has('id')
                        ->has('name')
                        ->has('email')
                        ->has('deleted_at')
                        ->missing('password')
                        ->etc()
                )
        );
    }

    public function test_user_can_soft_delete_user()
    {
        $admin = User::factory()->create();
        $targetUser = User::factory()->create();

        $response = $this->actingAs($admin)->delete("/users/{$targetUser->id}");

        $response->assertRedirect();
        $this->assertSoftDeleted($targetUser);
    }

    public function test_user_can_restore_soft_deleted_user()
    {
        $admin = User::factory()->create();
        $targetUser = User::factory()->create();
        $targetUser->delete();

        $this->assertSoftDeleted($targetUser);

        $response = $this->actingAs($admin)->put("/users/{$targetUser->id}/restore");

        $response->assertRedirect();
        $this->assertNotSoftDeleted($targetUser);
    }
}
