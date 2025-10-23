<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('users.index');
    }

    public function view(User $user, User $model): bool
    {
        return $user->can('users.show');
    }

    public function create(User $user): bool
    {
        return $user->can('users.create');
    }

    public function update(User $user, User $model): bool
    {
        return $user->can('users.edit');
    }

    public function delete(User $user, User $model): bool
    {
        return $user->can('users.destroy');
    }

    public function export(User $user): bool
    {
        return $user->can('users.export');
    }

    public function import(User $user): bool
    {
        return $user->can('users.import');
    }
}
