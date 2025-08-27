<?php

namespace App\Services;

use App\DTO\Auth\VerificationRequestDTO;
use App\Enums\VerificationType;
use App\Models\User;
use App\Models\VerificationCode;
use Illuminate\Support\Carbon;

class VerificationService
{
    public function __construct(
        private readonly SmsService $smsService
    ) {
    }

    public function sendCode(string $phone, VerificationType $type = VerificationType::LOGIN): bool
    {
        $user = User::where('phone', $phone)->first();

        if (!$user) {
            return false;
        }

        // Удаляем старые коды для этого пользователя и типа
        $this->deleteExpiredCodes($user->id, $type);

        // Генерируем новый код
        $code = $this->generateCode();

        // Создаем запись в БД
        VerificationCode::create([
            'user_id' => $user->id,
            'code' => $code,
            'type' => $type,
            'expires_at' => now()->addMinutes(10),
        ]);

        // Отправляем SMS
        return $this->smsService->sendSms($phone, $code);
    }

    public function verifyCode(VerificationRequestDTO $dto): bool
    {
        $user = User::where('phone', $dto->phone)->first();

        if (!$user) {
            return false;
        }

        $verificationCode = VerificationCode::where('user_id', $user->id)
            ->where('code', $dto->code)
            ->where('type', $dto->type)
            ->where('expires_at', '>', now())
            ->whereNull('used_at')
            ->first();

        if (!$verificationCode) {
            return false;
        }

        // Помечаем код как использованный
        $verificationCode->markAsUsed();

        return true;
    }

    public function generateCode(): string
    {
        return str_pad((string) rand(1000, 9999), 4, '0', STR_PAD_LEFT);
    }

    public function deleteExpiredCodes(int $userId, VerificationType $type): int
    {
        return VerificationCode::where('user_id', $userId)
            ->where('type', $type)
            ->where(function ($query) {
                $query->where('expires_at', '<', now())
                    ->orWhereNotNull('used_at');
            })
            ->delete();
    }

    public function cleanupExpiredCodes(): int
    {
        return VerificationCode::where('expires_at', '<', now())
            ->orWhereNotNull('used_at')
            ->delete();
    }
}
