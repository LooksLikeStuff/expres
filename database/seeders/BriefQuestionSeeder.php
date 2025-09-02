<?php

namespace Database\Seeders;

use App\DTO\Briefs\BriefQuestionDTO;
use App\Services\Briefs\BriefQuestionService;
use Illuminate\Database\Seeder;

class BriefQuestionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(BriefQuestionService $briefQuestionService): void
    {

        $questions = array_merge(
            $this->commonQuestions(),
            $this->commercialQuestions(),
        );

        $orderCounters = [];

        foreach ($questions as $question) {
            //Вычисляем order
            $keyForOrder = $question['brief_type'] . ':' . $question['page']; //
            $orderCounters[$keyForOrder] = ($orderCounters[$keyForOrder] ?? 0) + 1;

            $question['order'] = $question['order'] ?? $orderCounters[$keyForOrder];

            $briefQuestionService->updateOrCreate(BriefQuestionDTO::fromArray($question));
        }
    }

    private function commonQuestions(): array
    {
        return [

            // Стр.1 Общая информация
            ['brief_type' => 'common', 'page' => 1, 'key' => 'question_1_1', 'title' => 'Сколько человек будет проживать в квартире?', 'subtitle' => 'Укажите количество жильцов, их пол и возраст для понимания потребностей каждого члена семьи', 'input_type' => 'textarea', 'placeholder' => 'Пример: семейная пара с ребенком (0 лет), в будущем планируем еще детей', 'format' => 'default', 'class' => null],
            ['brief_type' => 'common', 'page' => 1, 'key' => 'question_1_2', 'title' => 'Есть ли у вас домашние животные и комнатные растения?', 'subtitle' => 'Укажите вид и количество животных или растений, чтобы мы могли учесть их потребности при проектировании пространства.', 'input_type' => 'textarea', 'placeholder' => 'Пример: среднеразмерная собака бигль...', 'format' => 'default', 'class' => null],
            ['brief_type' => 'common', 'page' => 1, 'key' => 'question_1_3', 'title' => 'Есть ли у членов семьи особые увлечения или хобби?', 'subtitle' => 'Укажите любимые занятия, требующие отдельного места хранения/размещения', 'input_type' => 'textarea', 'placeholder' => 'Пример: место под электрогитару...', 'format' => 'default', 'class' => null],
            ['brief_type' => 'common', 'page' => 1, 'key' => 'question_1_4', 'title' => 'Требуется ли перепланировка? Каков состав помещений?', 'subtitle' => 'Опишите желаемые изменения', 'input_type' => 'textarea', 'placeholder' => 'Пример: совместить кухню с гостиной...', 'format' => 'default', 'class' => null],
            ['brief_type' => 'common', 'page' => 1, 'key' => 'question_1_5', 'title' => 'Как часто вы встречаете гостей?', 'subtitle' => 'Нужны ли доп. посадочные/спальные места', 'input_type' => 'textarea', 'placeholder' => 'Пример: Раз в месяц-два, на пару дней', 'format' => 'default', 'class' => null],
            ['brief_type' => 'common', 'page' => 1, 'key' => 'question_1_6', 'title' => 'Адрес', 'subtitle' => 'Адрес объекта', 'input_type' => 'textarea', 'placeholder' => 'Пример: г. Грозный, ул. ...', 'format' => 'default', 'class' => null],

            // Стр.2 Стиль и бюджет
            ['brief_type' => 'common', 'page' => 2, 'key' => 'question_2_1', 'title' => 'Какой стиль Вы хотите видеть?', 'subtitle' => 'Стиль и цвета', 'input_type' => 'textarea', 'placeholder' => 'Укажите предпочтения по стилям (например, современный, классический, минимализм) и цветам, которые вы хотите использовать в интерьере.', 'format' => 'default', 'class' => null],
            ['brief_type' => 'common', 'page' => 2, 'key' => 'question_2_2', 'title' => 'Какие предметы обстановки включить?', 'subtitle' => 'Список мебели/аксессуаров', 'input_type' => 'textarea', 'placeholder' => 'Перечислите мебель и аксессуары, которые вы хотите сохранить', 'format' => 'default', 'class' => null],
            ['brief_type' => 'common', 'page' => 2, 'key' => 'question_2_3', 'title' => 'Ценовой сегмент ремонта', 'subtitle' => 'Эконом/средний+/бизнес/премиум', 'input_type' => 'textarea', 'placeholder' => 'Укажите выбранный ценовой сегмент: эконом, средний+, бизнес или премиум', 'format' => 'default', 'class' => null],
            ['brief_type' => 'common', 'page' => 2, 'key' => 'question_2_4', 'title' => 'Что не должно быть в интерьере?', 'subtitle' => 'Исключения', 'input_type' => 'textarea', 'placeholder' => 'Перечислите элементы или материалы, которые вы не хотите видеть', 'format' => 'default', 'class' => null],
            ['brief_type' => 'common', 'page' => 2, 'key' => 'price', 'title' => 'Бюджет проекта', 'subtitle' => 'Ориентировочная сумма', 'input_type' => 'text', 'placeholder' => 'Например: 2 000 000 руб', 'format' => 'price', 'class' => 'price-input'],

            // Стр.3 Помещения (FAQ) — базовые комнаты
            ['brief_type' => 'common', 'page' => 3, 'key' => 'room', 'title' => 'Пожелания по помещению', 'subtitle' => 'Пожелания по наполнению и дизайну', 'input_type' => 'textarea', 'placeholder' => null, 'format' => 'faq', 'class' => null],

           // Стр.4 Отделка
            ['brief_type' => 'common', 'page' => 4, 'key' => 'question_4_1', 'title' => 'Напольные покрытия', 'subtitle' => 'Укажите, какие материалы вы предпочитаете (ламинат, паркет, плитка и т.д.) и в каких помещениях они будут использоваться', 'input_type' => 'textarea', 'placeholder' => 'Укажите, какие материалы вы предпочитаете (ламинат, паркет, плитка и т.д.) и в каких помещениях они будут использоваться', 'format' => 'default', 'class' => null],
            ['brief_type' => 'common', 'page' => 4, 'key' => 'question_4_2', 'title' => 'Двери', 'subtitle' => 'Опишите ваши пожелания относительно дверей: обычные, складные, раздвижные, распашные, стеклянные перегородки, скрытого монтажа, нестандартной высоты, стеклянные и т п', 'input_type' => 'textarea', 'placeholder' => 'Опишите ваши пожелания относительно дверей: обычные, складные, раздвижные, распашные, стеклянные перегородки, скрытого монтажа, нестандартной высоты, стеклянные и т.п.', 'format' => 'default', 'class' => null],
            ['brief_type' => 'common', 'page' => 4, 'key' => 'question_4_3', 'title' => 'Отделка стен', 'subtitle' => 'Опишите ваши пожелания по материалам (обои, краска, декоративная штукатурка) и стилю оформления стен', 'input_type' => 'textarea', 'placeholder' => 'Опишите ваши пожелания по материалам (обои, краска, декоративная штукатурка) и стилю оформления стен', 'format' => 'default', 'class' => null],
            ['brief_type' => 'common', 'page' => 4, 'key' => 'question_4_4', 'title' => 'Освещение и электрика', 'subtitle' => 'Укажите, какие типы освещения вам нравятся (встраиваемые светильники, люстры, бра) и в каких зонах они должны быть установлены', 'input_type' => 'textarea', 'placeholder' => 'Укажите, какие типы освещения вам нравятся (встраиваемые светильники, люстры, бра) и в каких зонах они должны быть установлены', 'format' => 'default', 'class' => null],
            ['brief_type' => 'common', 'page' => 4, 'key' => 'question_4_5', 'title' => 'Потолки', 'subtitle' => 'Укажите, хотите ли вы использовать натяжные потолки, гипсокартонные конструкции или оставить стандартные потолки', 'input_type' => 'textarea', 'placeholder' => 'Укажите, хотите ли вы использовать натяжные потолки, гипсокартонные конструкции или оставить стандартные потолки', 'format' => 'default', 'class' => null],
            ['brief_type' => 'common', 'page' => 4, 'key' => 'question_4_6', 'title' => 'Дополнительные пожелания', 'subtitle' => 'Перечислите все моменты, которые вы считаете важными по отделке', 'input_type' => 'textarea', 'placeholder' => 'Перечислите все моменты, которые вы считаете важными по отделке', 'format' => 'default', 'class' => null],

            // Стр.5 Оснащение
            ['brief_type' => 'common', 'page' => 5, 'key' => 'question_5_1', 'title' => 'Пожелания по звукоизоляции', 'subtitle' => 'Уточните, какие источники шума вас беспокоят и в каких зонах вы хотели бы улучшить звукоизоляцию', 'input_type' => 'textarea', 'placeholder' => 'Уточните, какие источники шума вас беспокоят и в каких зонах вы хотели бы улучшить звукоизоляцию', 'format' => 'default', 'class' => null],
            ['brief_type' => 'common', 'page' => 5, 'key' => 'question_5_2', 'title' => 'Теплые полы', 'subtitle' => 'Укажите, предпочитаете ли вы электрические или водяные теплые полы, а также в каких помещениях они должны быть установлены', 'input_type' => 'textarea', 'placeholder' => 'Укажите, предпочитаете ли вы электрические или водяные теплые полы, а также в каких помещениях они должны быть установлены', 'format' => 'default', 'class' => null],
            ['brief_type' => 'common', 'page' => 5, 'key' => 'question_5_3', 'title' => 'Радиаторы', 'subtitle' => 'Укажите, хотите ли вы заменить стандартные радиаторы на более современные или изменить их расположение', 'input_type' => 'textarea', 'placeholder' => 'Укажите, хотите ли вы заменить стандартные радиаторы на более современные или изменить их расположение', 'format' => 'default', 'class' => null],
            ['brief_type' => 'common', 'page' => 5, 'key' => 'question_5_4', 'title' => 'Водоснабжение', 'subtitle' => 'Опишите ваши пожелания по установке фильтров очистки воды, водонагревателей и других элементов системы водоснабжения', 'input_type' => 'textarea', 'placeholder' => 'Опишите ваши пожелания по установке фильтров очистки воды, водонагревателей и других элементов системы водоснабжения', 'format' => 'default', 'class' => null],
            ['brief_type' => 'common', 'page' => 5, 'key' => 'question_5_5', 'title' => 'Кондиционирование и вентиляция', 'subtitle' => 'Пропишите зоны для установки систем вентиляции и кондиционирования', 'input_type' => 'textarea', 'placeholder' => 'Пропишите зоны для установки систем вентиляции и кондиционирования', 'format' => 'default', 'class' => null],
            ['brief_type' => 'common', 'page' => 5, 'key' => 'question_5_6', 'title' => 'Сети', 'subtitle' => 'Укажите, в каких помещениях необходимы розетки для интернета и телевидения, а также интересуют ли вас системы "умный дом", сигнализация и другие современные технологии', 'input_type' => 'textarea', 'placeholder' => 'Укажите, в каких помещениях необходимы розетки для интернета и телевидения, а также интересуют ли вас системы "умный дом", сигнализация и другие современные технологии', 'format' => 'default', 'class' => null],
        ];
    }

    private function commercialQuestions(): array
    {
        return [
            ['brief_type' => 'commercial', 'page' => 1, 'key' => 'zone_names', 'title' => 'Название зоны', 'subtitle' => 'Укажите название каждой зоны', 'input_type' => 'textarea', 'placeholder' => null, 'format' => 'default', 'class' => null],
            ['brief_type' => 'commercial', 'page' => 2, 'key' => 'total_area', 'title' => 'Общая площадь', 'subtitle' => 'Общая площадь (м²)', 'input_type' => 'textarea', 'placeholder' => null, 'format' => 'default', 'class' => null],
            ['brief_type' => 'commercial', 'page' => 2, 'key' => 'project_area', 'title' => 'Проектная Площадь', 'subtitle' => 'Проектная площадь (м²)', 'input_type' => 'textarea', 'placeholder' => null, 'format' => 'default', 'class' => null],
            ['brief_type' => 'commercial', 'page' => 3, 'key' => 'zone_style_furniture', 'title' => 'Стиль и меблировка зон', 'subtitle' => 'Предпочтения по стилю и меблировке', 'input_type' => 'textarea', 'placeholder' => null, 'format' => 'default', 'class' => null],
            ['brief_type' => 'commercial', 'page' => 4, 'key' => 'finishes', 'title' => 'Отделочные материалы и поверхности', 'subtitle' => 'Материалы пола, стен и потолка', 'input_type' => 'textarea', 'placeholder' => null, 'format' => 'default', 'class' => null],
            ['brief_type' => 'commercial', 'page' => 5, 'key' => 'engineering', 'title' => 'Инженерные системы и коммуникации', 'subtitle' => 'Освещение, электрика, вентиляция, кондиционирование', 'input_type' => 'textarea', 'placeholder' => null, 'format' => 'default', 'class' => null],
            ['brief_type' => 'commercial', 'page' => 6, 'key' => 'limitations', 'title' => 'Предпочтения и ограничения', 'subtitle' => 'Что исключить', 'input_type' => 'textarea', 'placeholder' => null, 'format' => 'default', 'class' => null],
            ['brief_type' => 'commercial', 'page' => 7, 'key' => 'price', 'title' => 'Бюджет', 'subtitle' => 'Общий бюджет проекта', 'input_type' => 'text', 'placeholder' => 'Например: 2 000 000 руб', 'format' => 'price', 'class' => 'price-input'],
            ['brief_type' => 'commercial', 'page' => 8, 'key' => 'additional', 'title' => 'Дополнительные пожелания', 'subtitle' => 'Любые комментарии по зонам', 'input_type' => 'textarea', 'placeholder' => null, 'format' => 'default', 'class' => null],
        ];
    }
}
