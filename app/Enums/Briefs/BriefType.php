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
}
