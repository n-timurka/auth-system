<?php

namespace Tests\Feature;

use App\Enums\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class UserListTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_users_page_with_users_list()
    {
        $admin = User::factory()->create(['role' => Role::ADMIN]);
        $otherUsers = User::factory(3)->create();

        $response = $this->actingAs($admin)->get('/users');

        $response->assertStatus(200);
        $response->assertInertia(
            fn(Assert $page) => $page
                ->component('Users')
                ->has('users.data', 4) // 3 others + admin user
                ->has(
                    'users.data.0',
                    fn(Assert $json) => $json
                        ->has('id')
                        ->has('name')
                        ->has('email')
                        ->has('role')
                        ->has('permissions')
                        ->has('deleted_at')
                        ->missing('password')
                        ->etc()
                )
        );
    }

    public function test_admin_can_soft_delete_user()
    {
        $admin = User::factory()->create(['role' => Role::ADMIN]);
        $targetUser = User::factory()->create();

        $response = $this->actingAs($admin)->delete("/users/{$targetUser->id}");

        $response->assertRedirect();
        $this->assertSoftDeleted($targetUser);
    }

    public function test_admin_can_restore_soft_deleted_user()
    {
        $admin = User::factory()->create(['role' => Role::ADMIN]);
        $targetUser = User::factory()->create();
        $targetUser->delete();

        $this->assertSoftDeleted($targetUser);

        $response = $this->actingAs($admin)->put("/users/{$targetUser->id}/restore");

        $response->assertRedirect();
        $this->assertNotSoftDeleted($targetUser);
    }
}
