<?php

namespace App\DTO;

use App\Enums\Status;
use function Laravel\Prompts\password;

class UserDTO
{
//    protected $fillable = [
//        'name',
//        'email',
//        'password',
//        'status',
//        'city',
//        'phone',
//        'contract_number',
//        'comment',
//        'portfolio_link',
//        'experience',
//        'rating',
//        'active_projects_count',
//        'firebase_token',
//        'verification_code',
//        'verification_code_expires_at',
//        'fcm_token',
//        'last_seen_at', // Добавляем поле последней активности
//    ];
    public function __construct(
        public readonly string $name,
        public readonly string $phone,
        public readonly string $password,
        public readonly Status $status,
    )
    {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            name: $data['name'],
            phone: $data['phone'],
            password: bcrypt($data['password']),
            status: $data['status'] instanceof Status
                ? $data['status']
                : Status::from($data['status'])
        );
    }

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'phone' => $this->phone,
            'password' => $this->password,
            'status' => $this->status->value,
        ];
    }
}
