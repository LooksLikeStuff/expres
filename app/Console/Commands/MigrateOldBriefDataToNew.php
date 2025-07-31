<?php

namespace App\Console\Commands;

use App\Models\Common;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class MigrateOldBriefDataToNew extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'brief:migrate-data';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Мигрирует данные из старой структуры брифа в новую структуру';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('Начало миграции данных брифов...');
        
        // Получаем все брифы
        $briefs = Common::all();
        $count = 0;

        $this->info("Найдено {$briefs->count()} брифов для миграции");

        // Проходим по каждому брифу
        foreach ($briefs as $brief) {
            $this->info("Обработка брифа #{$brief->id}...");
            
            try {
                // Страница 1: Общая информация
                $brief->residents = $brief->question_1_1 ?? null;
                $brief->pets_plants = $brief->question_1_2 ?? null;
                $brief->hobbies = $brief->question_2_5 ?? null;
                $brief->layout_changes = $brief->question_2_4 ?? null;
                $brief->guests = $brief->question_2_6 ?? null;
                
                // Страница 2: Стиль и предпочтения
                $brief->style_colors = $brief->question_4_4 ?? null;
                $brief->existing_furniture = $brief->question_4_5 ?? null;
                $brief->unwanted_elements = $brief->question_4_6 ?? null;
                
                // Отделка и оснащение
                // Определяем какую информацию можно извлечь из старых полей
                // Дополнительная логика может быть добавлена по необходимости
                
                // Отмечаем выбранные комнаты на основе заполненных полей
                if ($brief->question_3_1) $brief->room_hallway = true;
                if ($brief->question_3_2) $brief->room_children = true;
                if ($brief->question_3_3) $brief->room_storage = true;
                if ($brief->question_3_4) $brief->room_living = true; // кухня и гостиная
                if ($brief->question_3_5) $brief->room_guest_toilet = true;
                if ($brief->question_3_6) $brief->room_living = true;
                if ($brief->question_3_7) $brief->room_office = true; // рабочее место/кабинет
                if ($brief->question_3_9) $brief->room_bathroom = true;
                if ($brief->question_3_10) $brief->room_kitchen = true;
                if ($brief->question_3_11) $brief->room_office = true;
                if ($brief->question_3_12) $brief->room_bedroom = true;
                if ($brief->question_3_13) $brief->room_wardrobe = true;
                
                // Копируем детали помещений
                $brief->hallway_details = $brief->question_3_1 ?? null;
                $brief->children_details = $brief->question_3_2 ?? null;
                $brief->storage_details = $brief->question_3_3 ?? null;
                $brief->living_details = $brief->question_3_6 ?? $brief->question_3_4 ?? null;
                $brief->guest_toilet_details = $brief->question_3_5 ?? null;
                $brief->office_details = $brief->question_3_11 ?? $brief->question_3_7 ?? null;
                $brief->bathroom_details = $brief->question_3_9 ?? null;
                $brief->kitchen_details = $brief->question_3_10 ?? null;
                $brief->bedroom_details = $brief->question_3_12 ?? null;
                $brief->wardrobe_details = $brief->question_3_13 ?? null;
                $brief->other_details = $brief->question_3_14 ?? null;
                
                // Сохраняем изменения
                $brief->save();
                $count++;
                
                $this->info("Бриф #{$brief->id} успешно обновлен");
            } catch (\Exception $e) {
                $this->error("Ошибка при обновлении брифа #{$brief->id}: {$e->getMessage()}");
                Log::error("Ошибка миграции данных брифа #{$brief->id}: {$e->getMessage()}");
                Log::error($e->getTraceAsString());
            }
        }

        $this->info("Миграция данных завершена. Успешно обновлено {$count} из {$briefs->count()} брифов");
        return 0;
    }
}
