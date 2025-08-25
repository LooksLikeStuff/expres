<?php

namespace App\Enums;

enum VerificationType: string
{
    case LOGIN = 'login';
    case REGISTRATION = 'registration';
    case PASSWORD_RESET = 'password_reset';

    public function label(): string
    {
        return match ($this) {
            self::LOGIN => 'Вход в систему',
            self::REGISTRATION => 'Регистрация',
            self::PASSWORD_RESET => 'Сброс пароля',
        };
    }
}
