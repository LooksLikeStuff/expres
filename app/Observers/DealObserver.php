<?php

namespace App\Observers;

use App\DTO\ChatDTO;
use App\Models\Deal;
use App\Services\Chats\ChatService;
use App\Services\Chats\UserChatService;

class DealObserver
{
    public function __construct(
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

        $this->userChatService->createChatParticipants($chat->id, $deal->getMemberIds());
    }

    /**
     * Handle the Deal "updated" event.
     */
    public function updated(Deal $deal): void
    {
        //
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
