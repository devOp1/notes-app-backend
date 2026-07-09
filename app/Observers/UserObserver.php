<?php

namespace App\Observers;

use App\Models\User;
use App\Models\Page;

class UserObserver
{
    /**
     * Handle the User "created" event.
     */
    public function created(User $user): void
    {
        $user->pages()->create([
            'title'   => 'Welcome to your Notes!',
            'icon'    => '👋',
            'content' => '# Hello ' . ($user->email ?? 'there') . "!\n\nThis is your first page. Feel free to edit or delete it.",
            'order'   => 0,
            // 'uuid' is handled by Page model's booted method
        ]);
    }
}
