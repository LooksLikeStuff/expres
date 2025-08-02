<?php

namespace App\DTO;

use App\Enums\MessageType;
use App\Http\Requests\MessageRequest;

class MessageDTO
{
    public function __construct(
        public readonly int $senderId,
        public readonly int $chatId,
        public readonly string $content,
        public readonly MessageType $type,
        public readonly ?int $replyToId,
    )
    {
    }

    public static function fromMessageRequest(MessageRequest $request): self
    {
        return new self(
            senderId: auth()->id(),
            chatId: $request->validated('chat_id'),
            content: $request->validated('content'),
            type: MessageType::TEXT,
            replyToId: $request->validated('reply_to_id'),
        );
    }

    public function toArray()
    {
        return [
            'chat_id' => $this->chatId,
            'sender_id' => $this->senderId,
            'content' => $this->content,
            'type' => $this->type->value,
            'reply_to_id' => $this->replyToId,
        ];
    }

}
