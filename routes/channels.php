<?php

// routes/channels.php

use App\Models\Chats\Chat;
use Illuminate\Support\Facades\Broadcast;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Here you may register all of the event broadcasting channels that your
| application supports. The given channel authorization callbacks are
| used to check if an authenticated user can listen to the channel.
|
*/
Broadcast::channel('chat.{chat}', function ($user, Chat $chat) {
    return Gate::allows('access', $chat);
});

Broadcast::channel('presence.global', function ($user) {
        return [
            'id' => $user->id,
            'name' => $user->name,
        ];
});
