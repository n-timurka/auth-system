<?php

namespace Tests\Feature;

use App\Enums\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleBasedAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_access_users_page(): void
    {
        $admin = User::factory()->create(['role' => Role::ADMIN]);

        $response = $this->actingAs($admin)->get('/users');

        $response->assertStatus(200);
        $response->assertInertia(fn($page) => $page->component('Users'));
    }

    public function test_regular_user_cannot_access_users_page(): void
    {
        $user = User::factory()->create(['role' => Role::USER]);

        $response = $this->actingAs($user)->get('/users');

        $response->assertStatus(403);
    }

    public function test_guest_cannot_access_users_page(): void
    {
        $response = $this->get('/users');

        $response->assertRedirect('/login');
    }

    public function test_admin_can_delete_users(): void
    {
        $admin = User::factory()->create(['role' => Role::ADMIN]);
        $userToDelete = User::factory()->create();

        $response = $this->actingAs($admin)->delete("/users/{$userToDelete->id}");

        $response->assertRedirect();
        $this->assertSoftDeleted('users', ['id' => $userToDelete->id]);
    }

    public function test_regular_user_cannot_delete_users(): void
    {
        $user = User::factory()->create(['role' => Role::USER]);
        $userToDelete = User::factory()->create();

        $response = $this->actingAs($user)->delete("/users/{$userToDelete->id}");

        $response->assertStatus(403);
        $this->assertDatabaseHas('users', [
            'id' => $userToDelete->id,
            'deleted_at' => null,
        ]);
    }

    public function test_admin_can_restore_users(): void
    {
        $admin = User::factory()->create(['role' => Role::ADMIN]);
        $deletedUser = User::factory()->create();
        $deletedUser->delete();

        $response = $this->actingAs($admin)->put("/users/{$deletedUser->id}/restore");

        $response->assertRedirect();
        $this->assertDatabaseHas('users', [
            'id' => $deletedUser->id,
            'deleted_at' => null,
        ]);
    }

    public function test_regular_user_cannot_restore_users(): void
    {
        $user = User::factory()->create(['role' => Role::USER]);
        $deletedUser = User::factory()->create();
        $deletedUser->delete();

        $response = $this->actingAs($user)->put("/users/{$deletedUser->id}/restore");

        $response->assertStatus(403);
        $this->assertNotNull($deletedUser->fresh()->deleted_at);
    }

    public function test_dashboard_accessible_to_all_authenticated_users(): void
    {
        $admin = User::factory()->create(['role' => Role::ADMIN]);
        $user = User::factory()->create(['role' => Role::USER]);

        $adminResponse = $this->actingAs($admin)->get('/dashboard');
        $userResponse = $this->actingAs($user)->get('/dashboard');

        $adminResponse->assertStatus(200);
        $userResponse->assertStatus(200);
        $adminResponse->assertInertia(fn($page) => $page->component('Dashboard'));
        $userResponse->assertInertia(fn($page) => $page->component('Dashboard'));
    }
}
