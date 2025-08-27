<?php

namespace App\DTO\Auth;

use App\Enums\VerificationType;

class VerificationRequestDTO
{
    public function __construct(
        public readonly string $phone,
        public readonly string $code,
        public readonly VerificationType|string $type,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            phone: $data['phone'],
            code: $data['code'],
            type: is_string($data['type']) ? VerificationType::from($data['type']) : $data['type'],
        );
    }

    public function toArray(): array
    {
        return [
            'phone' => $this->phone,
            'code' => $this->code,
            'type' => $this->type instanceof VerificationType ? $this->type->value : $this->type,
        ];
    }
}
