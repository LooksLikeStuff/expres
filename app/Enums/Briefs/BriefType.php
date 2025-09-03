<?php

namespace App\Enums\Briefs;

enum BriefType: string
{
    case COMMON = 'common';
    case COMMERCIAL = 'commercial';

    public function label(): string
    {
        return match ($this) {
            self::COMMON => 'Общий бриф',
            self::COMMERCIAL => 'Коммерческий бриф',
        };
    }

    public static function fromLabel(string $label): ?self
    {
        return match ($label) {
            'Общий бриф', 'common' => self::COMMON,
            'Коммерческий бриф', 'commercial' => self::COMMERCIAL,
            default => null,
        };
    }
}
