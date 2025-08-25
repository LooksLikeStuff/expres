<?php

namespace App\Models;

use App\Enums\Briefs\BriefStatus;
use App\Enums\Briefs\BriefType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Brief extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'deal_id',
        'type',
        'title',
        'description',
        'status',
        'article',
        'zones',
        'total_area',
        'price',
        'preferences',
    ];

    protected $casts = [
        'status' => BriefStatus::class,
        'type' => BriefType::class,
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function rooms()
    {
        return $this->hasMany(BriefRoom::class);
    }

    public function answers()
    {
        return $this->hasMany(BriefAnswer::class);
    }

    public function isActive()
    {
        return $this->status->isActive();
    }

    public function isFinished(): bool
    {
        return $this->status === BriefStatus::COMPLETED;
    }
    public function isCommon(): bool
    {
        return $this->type === BriefType::COMMON;
    }
    public function isCommercial(): bool
    {
        return $this->type === BriefType::COMMERCIAL;
    }

    // Заголовки страниц для брифов
    public function getPageTitles(): array
    {
        if ($this->isCommon()) {
            // Общие заголовки для страниц
            return [
                1 => [
                    'title'    => 'Общая информация',
                    'subtitle' => 'Пожалуйста, предоставьте следующую информацию'
                ],
                2 => [
                    'title'    => 'Интерьер: стиль и предпочтения',
                    'subtitle' => 'Определитесь с общим стилем и цветовыми решениями'
                ],
                3 => [
                    'title'    => 'Пожелания по помещениям',
                    'subtitle' => 'Опишите пожелания по наполнению, деталям и расстановке по каждому помещению'
                ],
                4 => [
                    'title'    => 'Пожелания по отделке помещений',
                    'subtitle' => 'Опишите предпочтения по отделке помещений'
                ],
                5 => [
                    'title'    => 'Пожелания по оснащению помещений',
                    'subtitle' => 'Укажите, какие устройства или системы вы хотите установить, чтобы обеспечить комфорт и функциональность'
                ]
            ];


        }

        if ($this->isCommercial()) {
            return  [
                'Зоны и их функционал', 'Метраж зон', 'Зоны и их ', 'Мебилировка зон',
                'Отделочные материалы', 'Освещение зон', 'Кондиционирование',
                'Напольное покрытие зон', 'Отделка стен зон', 'Отделка потолков зон',
                'Категорически неприемлемо', 'Бюджет на помещения', 'Пожелания',
            ];
        }

        return [];
    }

    public function isEditing()
    {
        return $this->status === BriefStatus::EDITING;
    }

}
