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

    public function documents()
    {
        return $this->hasMany(BriefDocument::class);
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

    /**
     * Получить вопросы для конкретного типа брифа и страницы
     */
    public function getQuestionsForPage(int $page): array
    {
        if ($this->isCommon()) {
            return $this->getCommonQuestions()[$page] ?? [];
        }

        if ($this->isCommercial()) {
            return $this->getCommercialQuestions();
        }

        return [];
    }

    /**
     * Получить вопросы для общего брифа
     */
    private function getCommonQuestions(): array
    {
        return [
            1 => [
                ['key' => 'question_1_1', 'title' => 'Сколько человек будет проживать в квартире?', 'subtitle' => 'Укажите количество жильцов, их пол и возраст для понимания потребностей каждого члена семьи', 'type' => 'textarea', 'placeholder' => 'Пример: семейная пара с ребенком (0 лет), в будущем планируем еще детей', 'format' => 'default'],
                ['key' => 'question_1_2', 'title' => 'Есть ли у вас домашние животные и комнатные растения?', 'subtitle' => 'Укажите вид и количество животных или растений, чтобы мы могли учесть их потребности при проектировании пространства.', 'type' => 'textarea', 'placeholder' => 'Пример: среднеразмерная собака бигль. Хотелось бы, чтобы напольное покрытие приглушало стук когтей, требуется место под лежанку и лапомойку на входе, место для еды. Растения в доме нужны, частично перевезем из старой квартиры, но хотелось бы еще добавить', 'format' => 'default'],
                ['key' => 'question_1_3', 'title' => 'Есть ли у членов семьи особые увлечения или хобби?', 'subtitle' => 'Укажите ( любимое занятие ,которое подразумевает в квартире особое место, к примеру полочки для хранения или выставки коллекции, место для швейной машинки и место для хранения принадлежностей для шитья). Это поможет нам создать функциональное пространство, соответствующее интересам и потребностям ваших близких', 'type' => 'textarea', 'placeholder' => 'Пример: Нужны зоны для хобби: место под электрогитару, усилитель и синтезатор, большой книжный шкаф', 'format' => 'default'],
                ['key' => 'question_1_4', 'title' => 'Требуется ли перепланировка? Каков состав помещений?', 'subtitle' => 'Опишите, какие изменения в планировке вы хотите осуществить.', 'type' => 'textarea', 'placeholder' => 'Пример: совместить кухню с гостиной. Спальня с гардеробной, детская, кабинет, кладовая, санузел', 'format' => 'default'],
                ['key' => 'question_1_5', 'title' => 'Как часто вы встречаете гостей?', 'subtitle' => 'Укажите, требуется ли предусмотреть дополнительные посадочные и спальные места и как часто вы ожидаете гостей', 'type' => 'textarea', 'placeholder' => 'Пример: Раз в месяц-два, на пару дней ', 'format' => 'default'],
                ['key' => 'question_1_6', 'title' => 'Адрес', 'subtitle' => 'Укажите адрес объекта (город, улица и дом, если есть - название ЖК)', 'type' => 'textarea', 'placeholder' => 'Пример: г. Грозный, ул. Лорсанова 15', 'format' => 'default'],
            ],
            2 => [
                ['key' => 'question_2_1', 'title' => 'Какой стиль Вы хотите видеть в своем интерьере? Какие цвета должны преобладать в интерьере?', 'subtitle' => 'Укажите предпочтения по стилям (например, современный, классический, минимализм) и цветам, которые вы хотите использовать в интерьере.', 'type' => 'textarea', 'placeholder' => 'Укажите предпочтения по стилям (например, современный, классический, минимализм) и цветам, которые вы хотите использовать в интерьере.', 'format' => 'default'],
                ['key' => 'question_2_2', 'title' => 'Какие имеющиеся предметы обстановки нужно включить в новый интерьер?', 'subtitle' => 'Перечислите мебель и аксессуары, которые вы хотите сохранить', 'type' => 'textarea', 'placeholder' => 'Перечислите мебель и аксессуары, которые вы хотите сохранить', 'format' => 'default'],
                ['key' => 'question_2_3', 'title' => 'В каком ценовом сегменте предполагается ремонт?', 'subtitle' => 'Укажите выбранный ценовой сегмент: эконом, средний+, бизнес или премиум', 'type' => 'textarea', 'placeholder' => 'Укажите выбранный ценовой сегмент: эконом, средний+, бизнес или премиум', 'format' => 'default'],
                ['key' => 'question_2_4', 'title' => 'Что не должно быть в вашем интерьере?', 'subtitle' => 'Перечислите элементы или материалы, которые вы не хотите видеть', 'type' => 'textarea', 'placeholder' => 'Перечислите элементы или материалы, которые вы не хотите видеть', 'format' => 'default'],
                ['key' => 'question_2_5', 'title' => 'Бюджет проекта', 'subtitle' => 'Укажите ориентировочную сумму бюджета, которую вы готовы потратить на ремонт, включая стоимость материалов', 'type' => 'textarea', 'placeholder' => 'Укажите ориентировочную сумму бюджета, которую вы готовы потратить на ремонт, включая стоимость материалов', 'format' => 'default'],
            ],
            // Добавить остальные страницы по аналогии...
        ];
    }

    /**
     * Получить вопросы для коммерческого брифа
     */
    private function getCommercialQuestions(): array
    {
        return [
            'zone_1' => "Зоны и их функционал",
            'zone_2' => "Метраж зон",
            'zone_3' => "Зоны и их стиль оформления",
            'zone_4' => "Мебилировка зон",
            'zone_5' => "Предпочтения отделочных материалов",
            'zone_6' => "Освещение зон",
            'zone_7' => "Кондиционирование зон",
            'zone_8' => "Напольное покрытие зон",
            'zone_9' => "Отделка стен зон",
            'zone_10' => "Отделка потолков зон",
            'zone_11' => "Категорически неприемлемо или нет",
            'zone_12' => "Бюджет на помещения",
            'zone_13' => "Пожелания и комментарии",
        ];
    }

    /**
     * Получить данные зон для коммерческого брифа
     */
    public function getZonesData(): array
    {
        if (!$this->isCommercial()) {
            return [];
        }

        return $this->zones ? json_decode($this->zones, true) : [];
    }

    /**
     * Получить предпочтения для коммерческого брифа
     */
    public function getPreferencesData(): array
    {
        if (!$this->isCommercial()) {
            return [];
        }

        return $this->preferences ? json_decode($this->preferences, true) : [];
    }

    /**
     * Получить комнаты для общего брифа (если данные хранятся в JSON)
     */
    public function getRoomsData(): array
    {
        if (!$this->isCommon()) {
            return [];
        }

        // Пытаемся получить из связанной таблицы rooms
        if ($this->rooms()->exists()) {
            return $this->rooms->toArray();
        }

        // Fallback на JSON поле, если используется
        return [];
    }

}
