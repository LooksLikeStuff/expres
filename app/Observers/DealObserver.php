<?php

namespace App\Observers;

use App\DTO\ChatDTO;
use App\Models\Deal;
use App\Services\Chats\ChatService;
use App\Services\Chats\UserChatService;
use App\Services\DealService;
use App\Services\UserService;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class DealObserver
{
    public function __construct(
        private readonly DealService $dealService,
        private readonly UserService $userService,
        private readonly ChatService $chatService,
        private readonly UserChatService $userChatService,
    )
    {
    }

    /**
     * Handle the Deal "created" event.
     */
    public function created(Deal $deal): void
    {
        $chat = $this->chatService->create(ChatDTO::fromDeal($deal));
        $this->dealService->bindChatToDeal($chat->id, $deal->id);

        $membersIds = $deal->getMemberIds();
        try {
            // Используем новый геттер для получения телефона клиента
            $clientPhone = $deal->client_phone;
            if ($clientPhone) {
                $client = $this->userService->getByClientPhone($clientPhone);

                if ($client) {
                    $membersIds[] = $client->id;
                }
            }
        } catch (ModelNotFoundException $exception) {
            \Log::error('client not found by phone', ['id' => $deal->id, 'phone' => $deal->client_phone]);
        }

        $this->userChatService->createChatParticipants($chat->id, $membersIds);
    }

    /**
     * Handle the Deal "updated" event.
     */
    public function updated(Deal $deal): void
    {
        if ($deal->project_number) {
            $deal->chat()->update([
                'title' => $deal->project_number
            ]);
        }
    }

    /**
     * Handle the Deal "deleted" event.
     */
    public function deleted(Deal $deal): void
    {
        //
    }

    /**
     * Handle the Deal "restored" event.
     */
    public function restored(Deal $deal): void
    {
        //
    }

    /**
     * Handle the Deal "force deleted" event.
     */
    public function forceDeleted(Deal $deal): void
    {
        //
    }
}
