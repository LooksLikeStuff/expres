<?php

namespace App\DTO;

use App\Enums\UserStatus;

class UserDTO
{
    public function __construct(
        public readonly ?int $id,
        public readonly string $name,
        public readonly string $phone,
        public readonly UserStatus|string $status,
        public readonly ?string $email = null,
        public readonly ?string $password = null,
//        public readonly ?string $avatarUrl = null,
//        public readonly ?string $city = null,
//        public readonly ?string $contractNumber = null,
//        public readonly ?string $comment = null,
//        public readonly ?string $portfolioLink = null,
//        public readonly ?int $experience = null,
//        public readonly ?float $rating = null,
//        public readonly ?int $activeProjectsCount = null,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'] ?? null,
            name: $data['name'],
            phone: $data['phone'],
            status: is_string($data['status']) ? UserStatus::from($data['status']) : $data['status'],
            password: $data['password']  ? bcrypt($data['password']) : null,
            email: $data['email'] ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'phone' => $this->phone,
            'status' => $this->status instanceof UserStatus ? $this->status->value : $this->status,
            'password' => $this->password,
            'email' => $this->email,
        ];
    }
}
