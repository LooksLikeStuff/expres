<?php

namespace App\DTO;

use App\Enums\ChatType;
use Carbon\Carbon;

class ChatDTO
{

    public function __construct(
        public readonly ?int $id,
        public readonly ChatType|string $type,
        public readonly ?string $title,
        public readonly ?string $avatar,
        public readonly ?Carbon $deletedAt,
    )
    {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'] ?? null,
            type: is_string($data['type']) ? ChatType::from($data['type']) : $data['type'],
            title: $data['title'] ?? null,
            avatar: $data['avatar'] ?? null,
            deletedAt: $data['deleted_at'] ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            'type' => $this->type->value,
            'title' => $this->title,
            'avatar' => $this->avatar,
            'deleted_at' => $this->deletedAt,
        ];
    }

}
