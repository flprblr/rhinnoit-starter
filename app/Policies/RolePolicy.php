<?php

namespace App\Policies;

use App\Models\Role;
use App\Models\User;

class RolePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('roles.index');
    }

    public function view(User $user, Role $role): bool
    {
        return $user->can('roles.show');
    }

    public function create(User $user): bool
    {
        return $user->can('roles.create');
    }

    public function update(User $user, Role $role): bool
    {
        return $user->can('roles.edit');
    }

    public function delete(User $user, Role $role): bool
    {
        return $user->can('roles.destroy');
    }

    public function export(User $user): bool
    {
        return $user->can('roles.export');
    }

    public function import(User $user): bool
    {
        return $user->can('roles.import');
    }
}
