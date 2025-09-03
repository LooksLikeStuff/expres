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

    public static function fromLabel(string $label): ?self
    {
        return match ($label) {
            'Черновик', 'draft' => self::DRAFT,
            'Активный', 'active' => self::ACTIVE,
            'Редактируется', 'editing' => self::EDITING,
            'Завершенный', 'completed' => self::COMPLETED,
            'Есть пропущенные страницы', 'skipped_pages' => self::SKIPPED_PAGES,
            default => SELF::DRAFT,
        };
    }
}
