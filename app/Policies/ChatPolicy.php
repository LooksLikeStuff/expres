<?php

namespace App\Policies;

use App\Models\Chat;
use App\Models\User;

class ChatPolicy
{
    /**
     * Create a new policy instance.
     */
    public function __construct()
    {
        //
    }

    public function access(User $user, Chat $chat)
    {
        return $chat->users()->where('user_id', $user->id)->exists();
    }
}
