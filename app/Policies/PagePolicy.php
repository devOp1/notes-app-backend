<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Page;

class PagePolicy
{
    public function update(User $user, Page $page): bool
    {
        return $user->id === $page->user_id;
    }

    public function delete(User $user, Page $page): bool
    {
        return $user->id === $page->user_id;
    }

    public function view(User $user, Page $page): bool
    {
        return $user->id === $page->user_id;
    }
}
