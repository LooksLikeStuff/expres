<?php

namespace App\Services\Chats;

use App\DTO\UserChatDTO;
use App\Models\UserChat;

class UserChatService
{
    public function updateOrCreate(UserChatDTO $userChatDTO)
    {
        return UserChat::updateOrCreate([
            'chat_id' => $userChatDTO->chatId,
            'user_id' => $userChatDTO->userId,
        ], $userChatDTO->toArray());
    }
}
