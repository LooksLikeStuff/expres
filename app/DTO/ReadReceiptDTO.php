<?php

namespace App\DTO;

use App\Http\Requests\Chats\UpdateReadReceiptRequest;
use App\Models\Chats\Message;
use Carbon\Carbon;

class ReadReceiptDTO
{
    public function __construct(
        public readonly int $messageId,
        public readonly int $userId,
        public readonly ?Carbon $readAt,
    )
    {
    }

    public static function fromRequest(UpdateReadReceiptRequest $request): self
    {
        return new self(
            messageId: $request->validated('message_id'),
            userId: $request->validated('user_id'),
            readAt: Carbon::parse($request->validated('read_at')),
        );
    }

    public static function fromMessage(Message $message): self
    {
        return new self(
            messageId: $message->id,
            userId: $message->sender_id,
            readAt: $message->created_at,
        );
    }

    public function toArray(): array
    {
        return [
            'message_id' => $this->messageId,
            'user_id' => $this->userId,
            'read_at' => $this->readAt,
        ];
    }
}
