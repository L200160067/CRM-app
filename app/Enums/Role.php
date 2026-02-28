<?php

namespace App\Enums;

enum Role: string
{
    case SuperAdmin = 'super_admin';
    case Admin = 'admin';
    case Marketing = 'marketing';
    case ServerManager = 'server_manager';

    public function label(): string
    {
        return match ($this) {
            Role::SuperAdmin => 'Super Admin',
            Role::Admin => 'Admin',
            Role::Marketing => 'Marketing',
            Role::ServerManager => 'Server Manager',
        };
    }
}
