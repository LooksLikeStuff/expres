<?php

namespace App\DTO;

use App\Enums\ChatType;
use Carbon\Carbon;

class ChatDTO
{

    public function __construct(
        public readonly ChatType $type,
        public readonly string $title,
        public readonly ?Carbon $deletedAt,
    )
    {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            type: $data['type'],
            title: $data['title'],
            deletedAt: $data['deleted_at'] ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            'type' => $this->type->value,
            'title' => $this->title,
            'deleted_at' => $this->deletedAt,
        ];
    }

}
