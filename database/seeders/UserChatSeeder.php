<?php

namespace Database\Seeders;

use App\DTO\Briefs\UserChatDTO;
use App\Services\Chats\ChatService;
use App\Services\Chats\UserChatService;
use App\Services\UserService;
use Illuminate\Database\Seeder;

class UserChatSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(
        UserService $userService,
        ChatService $chatService,
        UserChatService $userChatService
    ): void
    {
        $userGroups = [
            'private_chat_title' => ['user_first', 'user_second'], //Для чата 1 на 1
            'group_chat_title' => ['user_first', 'user_second', 'user_third'] //Групповой чат
        ];

        foreach ($userGroups as $chatTitle => $users) {
                $data['chat_id'] = $chatService->getIdByTitle($chatTitle);

            foreach ($users as $user) {
                $data['user_id'] = $userService->getIdByName($user);
                $data['joined_at'] = now();

                $userChatService->updateOrCreate(UserChatDTO::fromArray($data));
            }
        }
    }
}
