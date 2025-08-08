<?php

namespace App\Services\Chats;

use App\DTO\ChatDTO;
use App\Models\Chats\Chat;
use Illuminate\Support\Facades\DB;

class ChatService
{
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
            ->get();

        return $chats;
    }

}
