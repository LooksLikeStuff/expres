<?php

namespace App\DTO;

use Carbon\Carbon;

class UserChatDTO
{

    public function __construct(
        public readonly int $chatId,
        public readonly int $userId,
        public readonly Carbon $joinedAt,
        public readonly ?Carbon $leftAt,
    )
    {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            chatId: $data['chat_id'],
            userId: $data['user_id'],
            joinedAt: $data['joined_at'],
            leftAt: $data['left_at'] ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            'chat_id' => $this->chatId,
            'user_id' => $this->userId,
            'joined_at' => $this->joinedAt,
            'left_at' => $this->leftAt,
        ];
    }
}
