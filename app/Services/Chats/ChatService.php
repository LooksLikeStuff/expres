<?php

namespace App\Services\Chats;

use App\DTO\ChatDTO;
use App\Models\Chat;

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
}
