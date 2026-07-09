<?php

namespace App\Policies;

use App\Models\Admin;
use App\Models\User;

class UserPolicy
{
    public function viewAny(Admin $admin): bool
    {
        return $admin->can('users.view');
    }

    public function view(Admin $admin, User $user): bool
    {
        return $admin->can('users.view');
    }

    public function create(Admin $admin): bool
    {
        return $admin->can('users.update');
    }

    public function update(Admin $admin, User $user): bool
    {
        return $admin->can('users.update');
    }

    public function delete(Admin $admin, User $user): bool
    {
        return $admin->can('users.delete');
    }

    public function ban(Admin $admin, User $user): bool
    {
        return $admin->can('users.ban');
    }

    public function revokeTokens(Admin $admin, User $user): bool
    {
        return $admin->can('users.revoke-tokens');
    }

    public function verify(Admin $admin, User $user): bool
    {
        return $admin->can('users.verify');
    }
}
