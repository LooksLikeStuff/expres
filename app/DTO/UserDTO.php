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
        public readonly ?string $avatarUrl = null,
        public readonly ?string $city = null,
        public readonly ?string $contractNumber = null,
        public readonly ?string $comment = null,
        public readonly ?string $portfolioLink = null,
        public readonly ?int $experience = null,
        public readonly ?float $rating = null,
        public readonly ?int $activeProjectsCount = null,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'] ?? null,
            name: $data['name'],
            phone: $data['phone'],
            status: is_string($data['status']) ? UserStatus::from($data['status']) : $data['status'],
            email: $data['email'] ?? null,
            avatarUrl: $data['avatar_url'] ?? $data['avatarUrl'] ?? null,
            city: $data['city'] ?? null,
            contractNumber: $data['contract_number'] ?? $data['contractNumber'] ?? null,
            comment: $data['comment'] ?? null,
            portfolioLink: $data['portfolio_link'] ?? $data['portfolioLink'] ?? null,
            experience: $data['experience'] ?? null,
            rating: $data['rating'] ?? null,
            activeProjectsCount: $data['active_projects_count'] ?? $data['activeProjectsCount'] ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'phone' => $this->phone,
            'status' => $this->status instanceof UserStatus ? $this->status->value : $this->status,
            'email' => $this->email,
            'avatar_url' => $this->avatarUrl,
            'city' => $this->city,
            'contract_number' => $this->contractNumber,
            'comment' => $this->comment,
            'portfolio_link' => $this->portfolioLink,
            'experience' => $this->experience,
            'rating' => $this->rating,
            'active_projects_count' => $this->activeProjectsCount,
        ];
    }
}
