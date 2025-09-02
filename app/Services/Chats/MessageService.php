<?php

namespace App\Services\Chats;

use App\DTO\MessageDTO;
use App\Models\Chats\Message;

class MessageService
{
    public function create(MessageDTO $messageDTO)
    {
        return Message::create($messageDTO->toArray());
    }

    public function getPaginatedMessagesByChatId(int $chatId, int $page = 1, int $perPage = 20)
    {
        return Message::select(['id', 'sender_id', 'type', 'content', 'created_at'])
            ->with(['attachments', 'readReceipts', 'sender'])
            ->where('chat_id', $chatId)
            ->orderByDesc('created_at')
            ->paginate($perPage, ['*'], 'page', $page);
    }

    public function getMessageIdsInChatByMatch(int $chatId, string $term)
    {
        $preparedTerm = mb_strtolower(trim($term));

        return Message::where('chat_id', $chatId)
            ->where(function ($q) use ($preparedTerm) {
                $q->where('content', 'like', "%{$preparedTerm}%")
                    ->orWhereHas('attachments', function ($q) use ($preparedTerm) {
                        $q->where('original_name', 'like', "%{$preparedTerm}%");
                    });
            })
            ->orderBy('created_at', 'asc')
            ->get(['id', 'content', 'sender_id', 'created_at', 'type']);
    }

    public function getPageOfMessagesByMessageId(int $messageId, int $perPage = 20)
    {
        // Получаем created_at нужного сообщения
        $message = Message::where('id', $messageId)->firstOrFail();

        // Считаем, сколько сообщений новее него (created_at > message.created_at)
        $newerCount = Message::where('chat_id', $message->chat_id)
            ->where('created_at', '>', $message->created_at)
            ->count();

        // Позиция сообщения (0-based)
        $position = $newerCount;

        // Номер страницы (1-based)
        $page = intdiv($position, $perPage) + 1;

        return $this->getPaginatedMessagesByChatId($message->chat_id, $page);
    }
}
