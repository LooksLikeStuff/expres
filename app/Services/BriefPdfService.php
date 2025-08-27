<?php

namespace App\Services;

use App\Models\Brief;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use PDF;

class BriefPdfService
{
    /**
     * Генерировать PDF для брифа
     */
    public function generatePdf(Brief $brief): \Illuminate\Http\Response
    {
        try {
            // Получаем владельца брифа
            $user = $this->getBriefOwner($brief);

            // Подготавливаем данные в зависимости от типа брифа
            $data = $this->preparePdfData($brief, $user);

            // Определяем шаблон
            $template = $brief->isCommon() ? 'common.pdf' : 'commercial.pdf';

            // Генерируем PDF
            $pdf = PDF::loadView($template, $data);

            // Настраиваем PDF
            $this->configurePdf($pdf);

            // Формируем имя файла
            $filename = $this->generateFilename($brief);

            return $pdf->download($filename);

        } catch (\Exception $e) {
            $this->logError($brief, $e);
            
            throw new \Exception('Не удалось сгенерировать PDF. Ошибка: ' . $e->getMessage());
        }
    }

    /**
     * Получить владельца брифа
     */
    private function getBriefOwner(Brief $brief): User
    {
        $user = User::find($brief->user_id);
        
        if (!$user) {
            $user = Auth::user();
        }

        return $user;
    }

    /**
     * Подготовить данные для PDF в зависимости от типа брифа
     */
    private function preparePdfData(Brief $brief, User $user): array
    {
        $baseData = [
            'brif' => $brief, // Сохраняем старое название переменной для совместимости с шаблонами
            'user' => $user,
        ];

        if ($brief->isCommon()) {
            return $this->prepareCommonBriefData($brief, $baseData);
        }

        if ($brief->isCommercial()) {
            return $this->prepareCommercialBriefData($brief, $baseData);
        }

        return $baseData;
    }

    /**
     * Подготовить данные для общего брифа
     */
    private function prepareCommonBriefData(Brief $brief, array $baseData): array
    {
        $pageTitlesCommon = [
            'Общая информация',
            'Интерьер: стиль и предпочтения',
            'Пожелания по помещениям',
            'Пожелания по отделке помещений',
            'Пожелания по оснащению помещений',
        ];

        // Получаем вопросы для всех страниц
        $questions = $this->getFullCommonQuestions();

        // Фильтруем вопросы для комнат на основе выбранных комнат
        if (!empty($brief->getRoomsData()) && isset($questions[3])) {
            $questions[3] = $this->filterRoomQuestions($questions[3], $brief->getRoomsData());
        }

        // Преобразуем ссылки на документы в полные URL
        $this->convertDocumentUrlsToAbsolute($brief);

        return array_merge($baseData, [
            'pageTitlesCommon' => $pageTitlesCommon,
            'questions' => $questions,
        ]);
    }

    /**
     * Подготовить данные для коммерческого брифа
     */
    private function prepareCommercialBriefData(Brief $brief, array $baseData): array
    {
        $zones = $brief->getZonesData();
        $preferences = $brief->getPreferencesData();
        $questions = $brief->getCommercialQuestions();

        // Формируем предпочтения с названиями вопросов
        $preferencesFormatted = $this->formatCommercialPreferences($zones, $preferences, $questions);

        // Преобразуем ссылки на документы в полные URL
        $this->convertDocumentUrlsToAbsolute($brief);

        return array_merge($baseData, [
            'zones' => $zones,
            'preferencesFormatted' => $preferencesFormatted,
            'price' => $brief->price ?? 0,
        ]);
    }

    /**
     * Фильтровать вопросы по комнатам
     */
    private function filterRoomQuestions(array $questions, array $selectedRooms): array
    {
        $roomTitles = array_values($selectedRooms);
        
        return array_filter($questions, function($question) use ($roomTitles) {
            if (($question['format'] ?? '') == 'faq') {
                foreach ($roomTitles as $roomTitle) {
                    if ($question['title'] == $roomTitle || $question['title'] == 'Другое') {
                        return true;
                    }
                }
                return false;
            }
            return true;
        });
    }

    /**
     * Форматировать предпочтения для коммерческого брифа
     */
    private function formatCommercialPreferences(array $zones, array $preferences, array $questions): array
    {
        $preferencesFormatted = [];
        
        foreach ($zones as $index => $zone) {
            $zoneName = $zone['name'] ?? "Без названия";
            $preferencesFormatted[$zoneName] = [];
            
            if (isset($preferences["zone_$index"])) {
                foreach ($preferences["zone_$index"] as $questionKey => $answer) {
                    $questionNumber = str_replace('question_', '', $questionKey);
                    $questionTitle = $questions[$questionNumber] ?? "Вопрос $questionNumber";
                    $preferencesFormatted[$zoneName][] = [
                        'question' => $questionTitle,
                        'answer' => $answer,
                    ];
                }
            }
        }

        return $preferencesFormatted;
    }

    /**
     * Преобразовать ссылки на документы в абсолютные URL
     */
    private function convertDocumentUrlsToAbsolute(Brief $brief): void
    {
        // Загружаем связанные документы
        $brief->load('documents');
        
        // Документы теперь доступны через отношение $brief->documents
        // Каждый документ имеет метод getFullUrlAttribute() для получения полного URL
    }

    /**
     * Настроить параметры PDF
     */
    private function configurePdf($pdf): void
    {
        $pdf->getDomPDF()->set_option('defaultFont', 'DejaVu Sans');
        $pdf->getDomPDF()->set_option('isRemoteEnabled', true);
        $pdf->getDomPDF()->set_option('isPhpEnabled', true);
    }

    /**
     * Сгенерировать имя файла
     */
    private function generateFilename(Brief $brief): string
    {
        $type = $brief->isCommon() ? 'common' : 'commercial';
        return "{$type}_brief_{$brief->id}.pdf";
    }

    /**
     * Логировать ошибку
     */
    private function logError(Brief $brief, \Exception $e): void
    {
        $type = $brief->isCommon() ? 'общего' : 'коммерческого';
        
        Log::error("Ошибка генерации PDF для {$type} брифа: " . $e->getMessage(), [
            'brief_id' => $brief->id,
            'brief_type' => $brief->type->value,
            'trace' => $e->getTraceAsString()
        ]);
    }

    /**
     * Получить полный набор вопросов для общего брифа
     */
    private function getFullCommonQuestions(): array
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
            3 => [
                ['key' => 'question_3_1', 'title' => 'Прихожая', 'subtitle' => 'Пожелания по наполнению и дизайну', 'type' => 'textarea', 'placeholder' => 'Какую мебель и оборудование вы планируете разместить в прихожей? Опишите детали и расстановку мебели.', 'format' => 'faq'],
                ['key' => 'question_3_2', 'title' => 'Детская', 'subtitle' => 'Пожелания по наполнению и дизайну', 'type' => 'textarea', 'placeholder' => 'Какую мебель и оборудование вы планируете разместить в детской? Опишите детали и расстановку мебели.', 'format' => 'faq'],
                ['key' => 'question_3_3', 'title' => 'Кладовая', 'subtitle' => 'Пожелания по наполнению и дизайну', 'type' => 'textarea', 'placeholder' => 'Какую мебель и оборудование вы планируете разместить в кладовой? Опишите детали и расстановку мебели.', 'format' => 'faq'],
                ['key' => 'question_3_4', 'title' => 'Кухня и гостиная', 'subtitle' => 'Пожелания по наполнению и дизайну', 'type' => 'textarea', 'placeholder' => 'Какую мебель и оборудование вы планируете разместить в кухне и гостиной? Опишите детали и расстановку мебели.', 'format' => 'faq'],
                ['key' => 'question_3_5', 'title' => 'Гостевой санузел', 'subtitle' => 'Пожелания по наполнению и дизайну', 'type' => 'textarea', 'placeholder' => 'Перечислите предпочтения по выбору душа, раковины с тумбой, унитаза и других элементов.', 'format' => 'faq'],
                ['key' => 'question_3_6', 'title' => 'Гостиная', 'subtitle' => 'Пожелания по наполнению и дизайну', 'type' => 'textarea', 'placeholder' => 'Какую мебель и оборудование вы планируете разместить в гостиной? Опишите детали и расстановку мебели.', 'format' => 'faq'],
                ['key' => 'question_3_7', 'title' => 'Рабочее место', 'subtitle' => 'Пожелания по наполнению и дизайну', 'type' => 'textarea', 'placeholder' => 'Какую мебель и оборудование вы планируете разместить в рабочей зоне? Опишите детали и расстановку мебели.', 'format' => 'faq'],
                ['key' => 'question_3_8', 'title' => 'Столовая', 'subtitle' => 'Пожелания по наполнению и дизайну', 'type' => 'textarea', 'placeholder' => 'Какую мебель и оборудование вы планируете разместить в столовой? Опишите детали и расстановку мебели.', 'format' => 'faq'],
                ['key' => 'question_3_9', 'title' => 'Ванная комната', 'subtitle' => 'Пожелания по наполнению и дизайну', 'type' => 'textarea', 'placeholder' => 'Укажите предпочтения по выбору ванны/душа, раковины с тумбой, унитаза, полотенцесушителя и стиральной машины.', 'format' => 'faq'],
                ['key' => 'question_3_10', 'title' => 'Кухня', 'subtitle' => 'Пожелания по наполнению и дизайну', 'type' => 'textarea', 'placeholder' => 'Укажите тип плиты, наличие посудомоечной машины, микроволновой печи, духового шкафа, мойки, холодильника и других приборов. Опишите детали и расстановку мебели.', 'format' => 'faq'],
                ['key' => 'question_3_11', 'title' => 'Кабинет', 'subtitle' => 'Пожелания по наполнению и дизайну', 'type' => 'textarea', 'placeholder' => 'Какую мебель и оборудование вы планируете разместить в кабинете? Опишите детали и расстановку мебели.', 'format' => 'faq'],
                ['key' => 'question_3_12', 'title' => 'Спальня', 'subtitle' => 'Пожелания по наполнению и дизайну', 'type' => 'textarea', 'placeholder' => 'Какую мебель и оборудование вы планируете разместить в спальне? Опишите детали и расстановку мебели.', 'format' => 'faq'],
                ['key' => 'question_3_13', 'title' => 'Гардеробная', 'subtitle' => 'Пожелания по наполнению и дизайну', 'type' => 'textarea', 'placeholder' => 'Какую мебель и оборудование вы планируете разместить в гардеробной? Опишите детали и расстановку мебели.', 'format' => 'faq'],
                ['key' => 'question_3_14', 'title' => 'Другое', 'subtitle' => 'Пожелания по наполнению и дизайну', 'type' => 'textarea', 'placeholder' => 'Какие пожелания у вас есть по наполнению в других помещениях? Опишите детали и расстановку мебели.', 'format' => 'faq'],
            ],
            4 => [
                ['key' => 'question_4_1', 'title' => 'Напольные покрытия', 'subtitle' => 'Укажите, какие материалы вы предпочитаете (ламинат, паркет, плитка и т.д.) и в каких помещениях они будут использоваться', 'type' => 'textarea', 'placeholder' => 'Укажите, какие материалы вы предпочитаете (ламинат, паркет, плитка и т.д.) и в каких помещениях они будут использоваться', 'format' => 'default'],
                ['key' => 'question_4_2', 'title' => 'Двери', 'subtitle' => 'Опишите ваши пожелания относительно дверей: обычные, складные, раздвижные, распашные, стеклянные перегородки, скрытого монтажа, нестандартной высоты, стеклянные и т п', 'type' => 'textarea', 'placeholder' => 'Опишите ваши пожелания относительно дверей: обычные, складные, раздвижные, распашные, стеклянные перегородки, скрытого монтажа, нестандартной высоты, стеклянные и т.п.', 'format' => 'default'],
                ['key' => 'question_4_3', 'title' => 'Отделка стен', 'subtitle' => 'Опишите ваши пожелания по материалам (обои, краска, декоративная штукатурка) и стилю оформления стен', 'type' => 'textarea', 'placeholder' => 'Опишите ваши пожелания по материалам (обои, краска, декоративная штукатурка) и стилю оформления стен', 'format' => 'default'],
                ['key' => 'question_4_4', 'title' => 'Освещение и электрика', 'subtitle' => 'Укажите, какие типы освещения вам нравятся (встраиваемые светильники, люстры, бра) и в каких зонах они должны быть установлены', 'type' => 'textarea', 'placeholder' => 'Укажите, какие типы освещения вам нравятся (встраиваемые светильники, люстры, бра) и в каких зонах они должны быть установлены', 'format' => 'default'],
                ['key' => 'question_4_5', 'title' => 'Потолки', 'subtitle' => 'Укажите, хотите ли вы использовать натяжные потолки, гипсокартонные конструкции или оставить стандартные потолки', 'type' => 'textarea', 'placeholder' => 'Укажите, хотите ли вы использовать натяжные потолки, гипсокартонные конструкции или оставить стандартные потолки', 'format' => 'default'],
                ['key' => 'question_4_6', 'title' => 'Дополнительные пожелания', 'subtitle' => 'Перечислите все моменты, которые вы считаете важными по отделке', 'type' => 'textarea', 'placeholder' => 'Перечислите все моменты, которые вы считаете важными по отделке', 'format' => 'default'],
            ],
            5 => [
                ['key' => 'question_5_1', 'title' => 'Пожелания по звукоизоляции', 'subtitle' => 'Уточните, какие источники шума вас беспокоят и в каких зонах вы хотели бы улучшить звукоизоляцию', 'type' => 'textarea', 'placeholder' => 'Уточните, какие источники шума вас беспокоят и в каких зонах вы хотели бы улучшить звукоизоляцию', 'format' => 'default'],
                ['key' => 'question_5_2', 'title' => 'Теплые полы', 'subtitle' => 'Укажите, предпочитаете ли вы электрические или водяные теплые полы, а также в каких помещениях они должны быть установлены ', 'type' => 'textarea', 'placeholder' => 'Укажите, предпочитаете ли вы электрические или водяные теплые полы, а также в каких помещениях они должны быть установлены', 'format' => 'default'],
                ['key' => 'question_5_3', 'title' => 'Предпочтения по размещению и типу радиаторов', 'subtitle' => 'Укажите, хотите ли вы заменить стандартные радиаторы на более современные или изменить их расположение', 'type' => 'textarea', 'placeholder' => 'Укажите, хотите ли вы заменить стандартные радиаторы на более современные или изменить их расположение', 'format' => 'default'],
                ['key' => 'question_5_4', 'title' => 'Водоснабжение', 'subtitle' => 'Опишите ваши пожелания по установке фильтров очистки воды, водонагревателей и других элементов системы водоснабжения', 'type' => 'textarea', 'placeholder' => 'Опишите ваши пожелания по установке фильтров очистки воды, водонагревателей и других элементов системы водоснабжения', 'format' => 'default'],
                ['key' => 'question_5_5', 'title' => 'Кондиционирование и вентиляция', 'subtitle' => 'Пропишите зоны для установки систем вентиляции и кондиционирования', 'type' => 'textarea', 'placeholder' => 'Пропишите зоны для установки систем вентиляции и кондиционирования', 'format' => 'default'],
                ['key' => 'question_5_6', 'title' => 'Сети', 'subtitle' => 'Укажите, в каких помещениях необходимы розетки для интернета и телевидения, а также интересуют ли вас системы "умный дом", сигнализация и другие современные технологии.', 'type' => 'textarea', 'placeholder' => 'Укажите, в каких помещениях необходимы розетки для интернета и телевидения, а также интересуют ли вас системы "умный дом", сигнализация и другие современные технологии', 'format' => 'default'],
            ],
        ];
    }
}
