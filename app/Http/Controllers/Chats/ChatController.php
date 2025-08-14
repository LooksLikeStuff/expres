<?php

namespace App\Http\Controllers\Chats;

use App\DTO\ChatDTO;
use App\Enums\ChatType;
use App\Events\ChatCreated;
use App\Http\Controllers\Controller;
use App\Http\Requests\Chats\CreateChatRequest;
use App\Models\ChatGroup;
use App\Models\Chats\Chat;
use App\Models\Chats\Message;
use App\Models\User;
use App\Services\Chats\ChatService;
use App\Services\Chats\MessageService;
use App\Services\Chats\UserChatService;
use App\Services\Chats\Utilities\FileService;
use App\Services\UserService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

// Добавляем импорт модели ChatGroup

class ChatController extends Controller
{
    public function __construct(
        private readonly UserService $userService,
        private readonly ChatService $chatService,
        private readonly MessageService $messageService,
        private readonly UserChatService $userChatService,
    )
    {
    }

    public function index()
    {
        $user = auth()->user();

        $chats = $this->chatService->getUserChats($user->id);
        $user->setRelation('chats', $chats);

        if (request()->ajax()) {
            return  response()->json([
                'chats' => $chats,
            ]);
        }

        return view('chats.index', compact('user'));
    }

    public function store(CreateChatRequest $request, FileService $fileService)
    {
        $userId = \auth()->id();

        $data = $request->validated();
        $data['participants'][] = $userId; //Добавляем себя как участника

        //проверяем если чат приватный, что он еще не существует
        $existingChat = $this->chatService->privateChatByParticipants($data['participants']);
        if (
            $data['type'] == ChatType::PRIVATE->value
            && $existingChat
        ) {
            return response()
                ->json([
                    'status' => 'exists',
                    'chat_id' => $existingChat->id,
                ]);
        }

        //Сохраняем аватарку чата и сохраняем в массиве путь до нее
        if ($request->has('avatar')) {
            $data['avatar'] = $fileService->storeFileFromRequest($request->validated('avatar'), 'chats/avatars');
        }

        //Создаем чат и участников
        $chat = $this->chatService->create(ChatDTO::fromArray($data));
        $chat->title = $chat->getTitleForUser($userId);

        // Создаем участников
        $this->userChatService->createChatParticipants($chat->id, $data['participants']);

        broadcast(new ChatCreated($chat->load('users')));

        return response()->json(['chat' => $chat]);

    }

    public function show(Chat $chat)
    {
        $chat = $chat->load(['users', 'attachments']);

        return response()->json([
            'id' => $chat->id,
            'users' => $chat->users,
            'type' => $chat->type->value,
            'title' => $chat->getTitleForUser(auth()->id()),
            'avatar' => $chat->getAvatar(),
            'attachments' => $chat->attachments,
        ]);
    }

    public function getMessages(int $chatId)
    {
        $messages = $this->messageService->getPaginatedMessagesByChatId(chatId: $chatId);
        $users = $this->userService->getByChatId($chatId);

        return response()->json([
            'messages' => $messages,
            'users' => $users,
        ]);
    }


}
