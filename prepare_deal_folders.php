<?php
/**
 * Скрипт для подготовки структуры директорий для хранения файлов сделок
 */
// Базовая директория для хранения файлов сделок
$baseDir = __DIR__ . '/storage/app/public/deals';

// Создаем базовую директорию, если она не существует
if (!file_exists($baseDir)) {
    mkdir($baseDir, 0755, true);
    echo "Создана базовая директория: {$baseDir}\n";
}

// Получаем все ID сделок из базы данных
try {
    // Загружаем конфигурацию базы данных
    require __DIR__ . '/vendor/autoload.php';
    
    // Подключение к базе данных
    $app = require_once __DIR__ . '/bootstrap/app.php';
    $app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();
    
    // Получаем все ID сделок
    $dealIds = DB::table('deals')->pluck('id')->toArray();
    
    echo "Найдено " . count($dealIds) . " сделок.\n";
    
    // Создаем директории для каждой сделки
    $fileFields = [
        'contract_attachment', 'chat_screenshot', 'archicad_file', 
        'plan_final', 'final_project_file', 'work_act', 'project_photos'
    ];
    
    foreach ($dealIds as $dealId) {
        $dealDir = "{$baseDir}/{$dealId}";
        
        // Создаем директорию для сделки
        if (!file_exists($dealDir)) {
            mkdir($dealDir, 0755, true);
            echo "Создана директория для сделки #{$dealId}: {$dealDir}\n";
        }
        
        // Создаем поддиректории для разных типов файлов
        foreach ($fileFields as $field) {
            $fieldDir = "{$dealDir}/{$field}";
            if (!file_exists($fieldDir)) {
                mkdir($fieldDir, 0755, true);
                echo "Создана директория для {$field} в сделке #{$dealId}\n";
            }
        }
    }
    
    echo "Структура директорий успешно подготовлена.\n";
} catch (Exception $e) {
    echo "Ошибка при создании структуры директорий: " . $e->getMessage() . "\n";
}

echo "Завершено.\n";
