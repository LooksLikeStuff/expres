<?php

namespace App\Services\Chats;

use App\DTO\UserChatDTO;
use App\Models\Chats\UserChat;

class UserChatService
{
    public function updateOrCreate(UserChatDTO $userChatDTO)
    {
        return UserChat::updateOrCreate([
            'chat_id' => $userChatDTO->chatId,
            'user_id' => $userChatDTO->userId,
        ], $userChatDTO->toArray());
    }

    public function removeUser(int $chatId, int $userId)
    {
        return UserChat::where('chat_id', $chatId)
            ->where('user_id', $userId)
            ->update(['left_at' => now()]);
    }

    public function createChatParticipants(int $chatId, array $participantIds)
    {
        // Формируем массив данных для вставки
        $data = array_map(fn($userId) => [
            'chat_id' => $chatId,
            'user_id' => $userId,
            'joined_at' => now(),
        ], $participantIds);

        // Вставляем сразу все записи
        return UserChat::insert($data);
    }

    public function addUserToChat(int $chatId, int $userId)
    {
        //Создаем или обновляем запись, устанавливаем left_at = null
        return UserChat::updateOrCreate([
            'chat_id' => $chatId,
            'user_id' => $userId,
        ], ['left_at' => null, 'joined_at' => now()]);
    }
}
