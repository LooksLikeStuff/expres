<?php

namespace App\Enums\Briefs;

enum BriefStatus: string
{
    case DRAFT = 'draft';
    case ACTIVE = 'active';
    case EDITING = 'editing';
    case COMPLETED = 'completed';

    public function label(): string
    {
        return match ($this) {
            self::DRAFT => 'Черновик',
            self::ACTIVE => 'Активный',
            self::EDITING => 'Редактируется',
            self::COMPLETED => 'Завершенный',
        };
    }

    public function isActive(): bool
    {
        return match ($this) {
            self::DRAFT, self::ACTIVE, self::EDITING => true,
            self::COMPLETED => false,
        };
    }
}
