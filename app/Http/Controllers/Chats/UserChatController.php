<?php

namespace App\Http\Controllers\Chats;

use App\Events\UserAddedToChat;
use App\Events\UserRemovedFromChat;
use App\Http\Controllers\Controller;
use App\Http\Requests\Chats\AddUserToChatRequest;
use App\Http\Requests\ChatsAddUserToChatRequest;
use App\Http\Requests\RemoveUserFromChatRequest;
use App\Services\Chats\UserChatService;
use App\Services\UserService;

class UserChatController extends Controller
{
    public function __construct(
        private readonly UserChatService $userChatService,
        private readonly UserService $userService,
    )
    {
    }

    public function removeUserFromChat(int $chatId, RemoveUserFromChatRequest $request)
    {
        $result = $this->userChatService->removeUser($chatId, $request->validated('user_id'));

        if ($result) {
            broadcast(new UserRemovedFromChat())->toOthers();
        }

        return response()
            ->json([
                'result' => $result,
            ]);
    }


    public function addUserToChat(int $chatId, AddUserToChatRequest $request)
    {
        $userId = $request->validated('user_id');

        $result = $this->userChatService->addUserToChat($chatId, $userId);
        $user = $this->userService->getById($userId);

        if ($result) {
            broadcast(new UserAddedToChat())->toOthers();
        }

        return response()
            ->json([
                'user' => $user,
                'result' => $result,
            ]);
    }

}
