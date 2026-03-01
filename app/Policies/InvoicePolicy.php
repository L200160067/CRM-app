<?php

namespace App\Policies;

use App\Enums\InvoiceStatus;
use App\Enums\Role;
use App\Models\Invoice;
use App\Models\User;

class InvoicePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return in_array($user->role, [Role::SuperAdmin, Role::Admin]);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Invoice $invoice): bool
    {
        return in_array($user->role, [Role::SuperAdmin, Role::Admin]);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return in_array($user->role, [Role::SuperAdmin, Role::Admin]);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Invoice $invoice): bool
    {
        $status = $invoice->status instanceof InvoiceStatus ? $invoice->status->value : $invoice->status;

        if (in_array($status, ['paid', 'canceled'])) {
            return $user->role === Role::SuperAdmin;
        }

        return in_array($user->role, [Role::SuperAdmin, Role::Admin]);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Invoice $invoice): bool
    {
        $status = $invoice->status instanceof InvoiceStatus ? $invoice->status->value : $invoice->status;

        if (in_array($status, ['paid', 'canceled'])) {
            return $user->role === Role::SuperAdmin;
        }

        return in_array($user->role, [Role::SuperAdmin, Role::Admin]);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Invoice $invoice): bool
    {
        $status = $invoice->status instanceof InvoiceStatus ? $invoice->status->value : $invoice->status;

        if (in_array($status, ['paid', 'canceled'])) {
            return $user->role === Role::SuperAdmin;
        }

        return in_array($user->role, [Role::SuperAdmin, Role::Admin]);
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Invoice $invoice): bool
    {
        return $user->role === Role::SuperAdmin;
    }
}
