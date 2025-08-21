<?php

namespace App\Enums;

enum UserStatus: string
{
    case ADMIN = 'admin';
    case COORDINATOR = 'coordinator';
    case PARTNER = 'partner';
    case ARCHITECT = 'architect';
    case DESIGNER = 'designer';
    case VISUALIZER = 'visualizer';
    case CLIENT = 'client';

    public function label(): string
    {
        return match ($this) {
            self::ADMIN => 'Администратор',
            self::COORDINATOR => 'Координатор',
            self::PARTNER => 'Партнёр',
            self::ARCHITECT => 'Архитектор',
            self::DESIGNER => 'Дизайнер',
            self::VISUALIZER => 'Визуализатор',
            self::CLIENT => 'Клиент',
        };
    }

}
