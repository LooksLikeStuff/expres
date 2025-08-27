<?php

namespace App\Services\Chats;

use App\DTO\Briefs\ReadReceiptDTO;
use App\Exceptions\Chats\MessageReadException;
use App\Models\Chats\ReadReceipt;

class ReadReceiptService
{
    /**
     * @throws MessageReadException
     */
    public function readMessage(ReadReceiptDTO $readReceiptDTO): ReadReceipt
    {
        $readReceipt = ReadReceipt::with('message')->firstOrCreate(
            [
                'message_id' => $readReceiptDTO->messageId,
                'user_id' => $readReceiptDTO->userId,
            ],
            [
                'read_at' => $readReceiptDTO->readAt,
            ]
        );

        // Если запись уже существовала, а read_at ещё не установлен — обновим
        if (!$readReceipt->read_at) {
            $readReceipt->read_at = $readReceiptDTO->readAt;
            $readReceipt->save();
        }

        return $readReceipt;
    }

    public function create(ReadReceiptDTO $readReceiptDTO): ReadReceipt
    {
        return ReadReceipt::create($readReceiptDTO->toArray());
    }
}
