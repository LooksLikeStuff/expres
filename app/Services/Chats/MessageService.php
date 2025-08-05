<?php

namespace App\Services\Chats;

use App\DTO\MessageDTO;
use App\Models\Message;

class MessageService
{
    public function create(MessageDTO $messageDTO)
    {
        return Message::create($messageDTO->toArray());
    }

    public function getPaginatedMessagesByChatId(int $chatId, int $page = 1, int $perPage = 20)
    {
        return Message::select(['id', 'sender_id', 'type', 'content', 'created_at'])
            ->with(['attachments'])
            ->where('chat_id', $chatId)
            ->orderByDesc('created_at')
            ->forPage($page)
            ->paginate($perPage);
    }
}
