<?php

namespace App\Services\Chats;

use App\DTO\ChatDTO;
use App\Models\Chats\Chat;
use Illuminate\Support\Facades\DB;

class ChatService
{
    public function create(ChatDTO $chatDTO)
    {
        return Chat::create($chatDTO->toArray());
    }

    public function update(ChatDTO $chatDTO)
    {
        return Chat::where('id', $chatDTO->id)
            ->update($chatDTO->toArray());
    }

    //Только для сидера
    public function updateOrCreate(ChatDTO $chatDTO)
    {
        return Chat::updateOrCreate([
            'title' => $chatDTO->title,
        ], $chatDTO->toArray());
    }

    public function getIdByTitle(string $title): ?int
    {
        return Chat::select(['id', 'title'])
            ->where('title', $title)
            ->first('id')
            ?->id;
    }

    public function getUserChats(int $userId)
    {
        $chats = Chat::with([
            'users:id,name,avatar_url',
            'lastMessage',
        ])
            ->withCount([
                'messages as unread_count' => function ($query) use ($userId) {
                    $query->whereDoesntHave('readReceipts', function ($q) use ($userId) {
                        $q->where('user_id', $userId);
                    })->where('sender_id', '!=', $userId);
                }
            ])
            ->whereHas('users', function ($q) use ($userId) {
                $q->where('user_id', $userId);
            })
            ->leftJoin('messages as lm', 'lm.id', '=', \DB::raw('(SELECT id FROM messages WHERE messages.chat_id = chats.id ORDER BY created_at DESC LIMIT 1)'))
            ->orderByRaw('COALESCE(lm.created_at, chats.created_at) ASC')
            ->whereNull('deleted_at')
            ->get();

        return $chats;
    }

    public function privateChatByParticipants(array $participantIds): ?Chat
    {
        // Кол-во участников, которых ищем
        $countParticipants = count($participantIds);

        return Chat::where('type', 'private')
            ->whereNull('deleted_at')
            // Где id чата содержится в user_chats с нужными юзерами
            ->whereHas('userChats', function ($query) use ($participantIds) {
                $query->whereIn('user_id', $participantIds);
            })
            // Убедиться, что в чате ровно столько участников, сколько указано (нет лишних)
            ->withCount('userChats')
            ->having('user_chats_count', '=', $countParticipants)
            // Убедиться, что чат содержит именно всех участников
            ->whereDoesntHave('userChats', function ($query) use ($participantIds) {
                $query->whereNotIn('user_id', $participantIds);
            })
            ->first();
    }


    public function disableChat(int $chatId)
    {
        return Chat::where('id', $chatId)
            ->update(['deleted_at' => now()]);
    }
}
