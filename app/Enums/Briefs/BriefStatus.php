<?php

namespace App\Enums\Briefs;

enum BriefStatus: string
{
    case DRAFT = 'draft';
    case ACTIVE = 'active';
    case EDITING = 'editing';
    case COMPLETED = 'completed';
    case SKIPPED_PAGES = 'skipped_pages';

    public function label(): string
    {
        return match ($this) {
            self::DRAFT => 'Черновик',
            self::ACTIVE => 'Активный',
            self::EDITING => 'Редактируется',
            self::COMPLETED => 'Завершенный',
            self::SKIPPED_PAGES => 'Есть пропущенные страницы',
        };
    }

    public function isActive(): bool
    {
        return match ($this) {
            self::DRAFT, self::ACTIVE, self::SKIPPED_PAGES, self::EDITING => true,
            self::COMPLETED => false,
        };
    }
}
