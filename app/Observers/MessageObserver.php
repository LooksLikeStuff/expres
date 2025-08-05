<?php

namespace App\Observers;

use App\DTO\ReadReceiptDTO;
use App\Models\Chats\Message;
use App\Services\Chats\ReadReceiptService;

class MessageObserver
{
    /**
     * Handle the Message "created" event.
     */
    public function created(Message $message, ReadReceiptService $readReceiptService): void
    {
        $readReceiptService->create(ReadReceiptDTO::fromMessage($message));
    }

    /**
     * Handle the Message "updated" event.
     */
    public function updated(Message $message): void
    {
        //
    }

    /**
     * Handle the Message "deleted" event.
     */
    public function deleted(Message $message): void
    {
        //
    }

    /**
     * Handle the Message "restored" event.
     */
    public function restored(Message $message): void
    {
        //
    }

    /**
     * Handle the Message "force deleted" event.
     */
    public function forceDeleted(Message $message): void
    {
        //
    }
}
