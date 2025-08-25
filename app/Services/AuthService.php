<?php

namespace App\Services;

use App\DTO\Auth\AuthRequestDTO;
use App\DTO\Auth\RegisterRequestDTO;
use App\DTO\Auth\VerificationRequestDTO;
use App\DTO\UserDTO;
use App\Enums\VerificationType;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthService
{
    public function __construct(
        private readonly UserService $userService,
        private readonly VerificationService $verificationService
    ) {
    }

    public function loginByPassword(AuthRequestDTO $dto): bool
    {
        $user = $this->userService->findByPhone($dto->phone);

        if (!$user || !Hash::check($dto->password, $user->password)) {
            return false;
        }

        Auth::login($user);
        return true;
    }

    public function loginByCode(AuthRequestDTO $dto): bool
    {
        $user = $this->userService->findByPhone($dto->phone);

        if (!$user || !$dto->code) {
            return false;
        }

        $verificationDTO = new VerificationRequestDTO(
            phone: $dto->phone,
            code: $dto->code,
            type: VerificationType::LOGIN
        );

        if (!$this->verificationService->verifyCode($verificationDTO)) {
            return false;
        }

        Auth::login($user);
        return true;
    }

    public function register(RegisterRequestDTO $dto): User
    {
        $userDTO = new UserDTO(
            id: null,
            name: $dto->name,
            phone: $dto->phone,
            status: $dto->status,
            email: $dto->email,
        );

        $user = $this->userService->create($userDTO, $dto->password);

        Auth::login($user);

        return $user;
    }

    public function registerByDealLink(RegisterRequestDTO $dto, string $token): User
    {
        // Логика регистрации по ссылке сделки
        $userDTO = new UserDTO(
            id: null,
            name: $dto->name,
            phone: $dto->phone,
            status: $dto->status,
            email: $dto->email,
        );

        $user = $this->userService->create($userDTO, $dto->password);

        // Здесь должна быть логика привязки к сделке
        // TODO: Добавить DealService для работы со сделками

        Auth::login($user);

        return $user;
    }

    public function registerExecutor(RegisterRequestDTO $dto): User
    {
        $userDTO = new UserDTO(
            id: null,
            name: $dto->name,
            phone: $dto->phone,
            status: $dto->status,
            email: $dto->email,
        );

        $user = $this->userService->create($userDTO, $dto->password);

        Auth::login($user);

        return $user;
    }

    public function logout(): void
    {
        Auth::logout();
    }

    public function sendVerificationCode(string $phone): bool
    {
        return $this->verificationService->sendCode($phone, VerificationType::LOGIN);
    }

    public function isUserExists(string $phone): bool
    {
        return $this->userService->existsByPhone($phone);
    }
}
