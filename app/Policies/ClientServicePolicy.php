<?php

namespace App\Policies;

use App\Enums\Role;
use App\Models\ClientService;
use App\Models\User;

class ClientServicePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return in_array($user->role, [Role::SuperAdmin, Role::ServerManager]);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, ClientService $clientService): bool
    {
        return in_array($user->role, [Role::SuperAdmin, Role::ServerManager]);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return in_array($user->role, [Role::SuperAdmin, Role::ServerManager]);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, ClientService $clientService): bool
    {
        return in_array($user->role, [Role::SuperAdmin, Role::ServerManager]);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, ClientService $clientService): bool
    {
        return in_array($user->role, [Role::SuperAdmin, Role::ServerManager]);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, ClientService $clientService): bool
    {
        return in_array($user->role, [Role::SuperAdmin, Role::ServerManager]);
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, ClientService $clientService): bool
    {
        return in_array($user->role, [Role::SuperAdmin, Role::ServerManager]);
    }
}
