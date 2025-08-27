<?php

namespace App\Enums;

enum VerificationType: string
{
    case LOGIN = 'login';
    case REGISTRATION = 'registration';
    case PASSWORD_RESET = 'password_reset';
    case PHONE_UPDATE = 'phone_update';
    case ACCOUNT_DELETE = 'account_delete';

    public function label(): string
    {
        return match ($this) {
            self::LOGIN => 'Вход в систему',
            self::REGISTRATION => 'Регистрация',
            self::PASSWORD_RESET => 'Сброс пароля',
            self::PHONE_UPDATE => 'Обновление номера телефона',
            self::ACCOUNT_DELETE => 'Удаление аккаунта',
        };
    }
}
