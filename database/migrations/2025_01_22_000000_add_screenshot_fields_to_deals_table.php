<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('deals', function (Blueprint $table) {
            // Добавляем поля для скриншотов в разделе "Работа с проектом" (3 поля)
            $table->text('screenshot_work_1')->nullable()->comment('Первый скриншот работы над проектом');
            $table->text('yandex_url_screenshot_work_1')->nullable()->comment('URL первого скриншота работы на Яндекс.Диске');
            $table->string('original_name_screenshot_work_1')->nullable()->comment('Оригинальное имя первого скриншота работы');
            $table->text('yandex_disk_path_screenshot_work_1')->nullable()->comment('Путь первого скриншота работы на Яндекс.Диске');
            
            $table->text('screenshot_work_2')->nullable()->comment('Второй скриншот работы над проектом');
            $table->text('yandex_url_screenshot_work_2')->nullable()->comment('URL второго скриншота работы на Яндекс.Диске');
            $table->string('original_name_screenshot_work_2')->nullable()->comment('Оригинальное имя второго скриншота работы');
            $table->text('yandex_disk_path_screenshot_work_2')->nullable()->comment('Путь второго скриншота работы на Яндекс.Диске');
            
            $table->text('screenshot_work_3')->nullable()->comment('Третий скриншот работы над проектом');
            $table->text('yandex_url_screenshot_work_3')->nullable()->comment('URL третьего скриншота работы на Яндекс.Диске');
            $table->string('original_name_screenshot_work_3')->nullable()->comment('Оригинальное имя третьего скриншота работы');
            $table->text('yandex_disk_path_screenshot_work_3')->nullable()->comment('Путь третьего скриншота работы на Яндекс.Диске');
            
            // Добавляем поле для скриншота в разделе "Финал проекта" (1 поле)
            $table->text('screenshot_final')->nullable()->comment('Скриншот финального этапа проекта');
            $table->text('yandex_url_screenshot_final')->nullable()->comment('URL скриншота финального этапа на Яндекс.Диске');
            $table->string('original_name_screenshot_final')->nullable()->comment('Оригинальное имя скриншота финального этапа');
            $table->text('yandex_disk_path_screenshot_final')->nullable()->comment('Путь скриншота финального этапа на Яндекс.Диске');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('deals', function (Blueprint $table) {
            // Удаляем добавленные поля скриншотов
            $table->dropColumn([
                'screenshot_work_1',
                'yandex_url_screenshot_work_1',
                'original_name_screenshot_work_1',
                'yandex_disk_path_screenshot_work_1',
                
                'screenshot_work_2',
                'yandex_url_screenshot_work_2',
                'original_name_screenshot_work_2',
                'yandex_disk_path_screenshot_work_2',
                
                'screenshot_work_3',
                'yandex_url_screenshot_work_3',
                'original_name_screenshot_work_3',
                'yandex_disk_path_screenshot_work_3',
                
                'screenshot_final',
                'yandex_url_screenshot_final',
                'original_name_screenshot_final',
                'yandex_disk_path_screenshot_final',
            ]);
        });
    }
};
