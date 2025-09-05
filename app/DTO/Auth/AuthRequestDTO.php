<?php

namespace App\DTO\Auth;

use App\Http\Requests\Auth\LoginCodeRequest;
use App\Http\Requests\Auth\LoginPasswordRequest;
use App\Traits\ToArrayTrait;

class AuthRequestDTO
{
    use ToArrayTrait;
    public function __construct(
        public readonly string $phone,
        public readonly string $password,
        public readonly ?string $code = null,
    ) {
    }

    public static function fromLoginPasswordRequest(LoginPasswordRequest $request): self
    {
        return new self(
            phone: normalizePhone($request->validated('phone')),
            password: $request->validated('password')
        );

    }

    public static function fromLoginCodeRequest(LoginCodeRequest $request): self
    {
        return new self(
            phone: normalizePhone($request->validated('phone')),
            password: '',
            code: $request->validated('code')
        );
    }

    public static function fromArray(array $data): self
    {
        return new self(
            phone: normalizePhone($data['phone']),
            password: $data['password'],
            code: $data['code'] ?? null,
        );
    }


}
