<?php

namespace App\Enums;

enum Permission: string
{
    case USERS_VIEW = 'users.view';
    case USERS_ADD = 'users.add';
    case USERS_EDIT = 'users.edit';
    case USERS_DELETE = 'users.delete';
    case USERS_RESTORE = 'users.restore';
    case SETTINGS_VIEW = 'settings.view';
    case SETTINGS_EDIT = 'settings.edit';

    /**
     * Get all permissions for a given role
     *
     * @param Role $role
     * @return array<string>
     */
    public static function forRole(Role $role): array
    {
        return match ($role) {
            Role::ADMIN => [
                self::USERS_VIEW->value,
                self::USERS_ADD->value,
                self::USERS_EDIT->value,
                self::USERS_DELETE->value,
                self::USERS_RESTORE->value,
                self::SETTINGS_VIEW->value,
                self::SETTINGS_EDIT->value,
            ],
            Role::USER => [],
        };
    }
}
