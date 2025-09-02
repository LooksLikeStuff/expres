<?php

namespace Database\Seeders;

use App\DTO\ChatDTO;
use App\Enums\ChatType;
use App\Services\Chats\ChatService;
use Illuminate\Database\Seeder;

class ChatSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(ChatService $chatService): void
    {
        $chats = [
            [
                'type' => ChatType::PRIVATE,
                'title' => 'private_chat_title',
            ],
            [
                'type' =>ChatType::GROUP,
                'title' =>'group_chat_title',
            ]
        ];

        foreach ($chats as $chat) {
            $chatService->updateOrCreate(ChatDTO::fromArray($chat));
        }
    }
}
