<?php

namespace App\Observers;

use App\DTO\Briefs\ReadReceiptDTO;
use App\Jobs\SendMessagePushNotificationJob;
use App\Models\Chats\Message;
use App\Services\Chats\ReadReceiptService;

class MessageObserver
{

    /**
     * Handle the Message "created" event.
     */
    public function created(Message $message): void
    {
        $service = app(ReadReceiptService::class);
        $service->create(ReadReceiptDTO::fromMessage($message));

        // Диспатчим уведомление (асинхронно, без тормозов для основного потока)
        dispatch(new SendMessagePushNotificationJob($message))->onQueue('notifications');
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
