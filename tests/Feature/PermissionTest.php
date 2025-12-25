<?php

namespace Tests\Feature;

use App\Enums\Permission;
use App\Enums\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PermissionTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_has_all_permissions(): void
    {
        $admin = User::factory()->create(['role' => Role::ADMIN]);

        $this->assertTrue($admin->hasPermission(Permission::USERS_VIEW));
        $this->assertTrue($admin->hasPermission(Permission::USERS_ADD));
        $this->assertTrue($admin->hasPermission(Permission::USERS_EDIT));
        $this->assertTrue($admin->hasPermission(Permission::USERS_DELETE));
        $this->assertTrue($admin->hasPermission(Permission::USERS_RESTORE));
    }

    public function test_user_has_no_permissions(): void
    {
        $user = User::factory()->create(['role' => Role::USER]);

        $this->assertFalse($user->hasPermission(Permission::USERS_VIEW));
        $this->assertFalse($user->hasPermission(Permission::USERS_ADD));
        $this->assertFalse($user->hasPermission(Permission::USERS_EDIT));
        $this->assertFalse($user->hasPermission(Permission::USERS_DELETE));
        $this->assertFalse($user->hasPermission(Permission::USERS_RESTORE));
    }

    public function test_has_permission_works_with_string(): void
    {
        $admin = User::factory()->create(['role' => Role::ADMIN]);
        $user = User::factory()->create(['role' => Role::USER]);

        $this->assertTrue($admin->hasPermission('users.view'));
        $this->assertFalse($user->hasPermission('users.view'));
    }

    public function test_get_permissions_returns_correct_array(): void
    {
        $admin = User::factory()->create(['role' => Role::ADMIN]);
        $user = User::factory()->create(['role' => Role::USER]);

        $adminPermissions = $admin->getPermissions();
        $userPermissions = $user->getPermissions();

        $this->assertIsArray($adminPermissions);
        $this->assertIsArray($userPermissions);
        $this->assertCount(5, $adminPermissions);
        $this->assertCount(0, $userPermissions);
        $this->assertContains('users.view', $adminPermissions);
        $this->assertContains('users.delete', $adminPermissions);
    }

    public function test_default_role_is_user(): void
    {
        $user = User::factory()->create();

        $this->assertEquals(Role::USER, $user->role);
    }
}
