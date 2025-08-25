<?php

namespace App\DTO\Auth;

use App\Enums\UserStatus;
use App\Http\Requests\Auth\RegisterRequest;
use App\Traits\ToArrayTrait;

class RegisterRequestDTO
{
    use ToArrayTrait;
    public function __construct(
        public readonly string $name,
        public readonly string $phone,
        public readonly string $password,
        public readonly UserStatus $status = UserStatus::CLIENT,
        public readonly ?string $email = null,
    ) {
    }

    public static function fromRegisterRequest(RegisterRequest $request): self
    {
        return new self(
            name: $request->validated('name'),
            phone: $request->validated('phone'),
            password: $request->validated('password'),
            status:  $request->has('role')
                ? UserStatus::from($request->validated('role'))
                : UserStatus::CLIENT,
            email: $request->validated('email') ?? null
        );
    }

    public static function fromArray(array $data): self
    {
        return new self(
            name: $data['name'],
            phone: $data['phone'],
            password: $data['password'],
            status: isset($data['status'])
                ? (is_string($data['status']) ? UserStatus::from($data['status']) : $data['status'])
                : UserStatus::CLIENT,
            email: $data['email'] ?? null,
        );
    }
}
