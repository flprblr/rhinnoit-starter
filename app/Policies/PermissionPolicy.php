<?php

namespace App\Policies;

use App\Models\Permission;
use App\Models\User;

class PermissionPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('permissions.index');
    }

    public function view(User $user, Permission $permission): bool
    {
        return $user->can('permissions.show');
    }

    public function create(User $user): bool
    {
        return $user->can('permissions.create');
    }

    public function update(User $user, Permission $permission): bool
    {
        return $user->can('permissions.edit');
    }

    public function delete(User $user, Permission $permission): bool
    {
        return $user->can('permissions.destroy');
    }

    public function export(User $user): bool
    {
        return $user->can('permissions.export');
    }

    public function import(User $user): bool
    {
        return $user->can('permissions.import');
    }
}
