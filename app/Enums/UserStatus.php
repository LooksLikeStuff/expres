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

    /**
     * Вернёт список кейсов, которые являются исполнителями.
     *
     * @return array<string>
     */
    public static function executors(): array
    {
        return [
            self::ARCHITECT->value,
            self::DESIGNER->value,
            self::VISUALIZER->value,
        ];
    }

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
