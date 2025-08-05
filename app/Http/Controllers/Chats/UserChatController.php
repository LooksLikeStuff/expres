<?php

namespace App\Http\Controllers\Chats;

use App\Http\Controllers\Controller;
use App\Http\Requests\RemoveUserFromChatRequest;
use App\Services\Chats\UserChatService;

class UserChatController extends Controller
{
    public function __construct(
        private readonly UserChatService $userChatService,
    )
    {
    }

    public function removeUserFromChat(int $chatId, RemoveUserFromChatRequest $request)
    {
        $this->userChatService->removeUser($chatId, $request->validated('user_id'));
    }
}
