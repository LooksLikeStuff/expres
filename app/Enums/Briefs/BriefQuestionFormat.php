<?php

namespace App\Enums\Briefs;

enum BriefQuestionFormat: string
{
    case DEFAULT = 'default';
    case ROOM = 'room';
    case FAQ = 'faq';
    case PRICE = 'price';

    public function label(): string
    {
        return match ($this) {
            self::DEFAULT => 'Обычный',
            self::ROOM => 'Помещения (выбор/привязка)',
            self::FAQ => 'FAQ по помещениям',
            self::PRICE => 'Цена/бюджет',
        };
    }
}


