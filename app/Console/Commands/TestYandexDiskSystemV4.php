<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Deal;

class TestYandexDiskSystemV4 extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'yandex-disk:test-v4 
                           {--deal-id= : ID сделки для тестирования}
                           {--field= : Поле для тестирования}';

    /**
     * The console command description.
     */
    protected $description = 'Тестирование обновленной системы отображения ссылок Яндекс.Диска v4.0';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔧 Тестирование системы отображения ссылок Яндекс.Диска v4.0');
        $this->newLine();
        
        // Получаем параметры
        $dealId = $this->option('deal-id') ?? $this->ask('Введите ID сделки для тестирования');
        $fieldName = $this->option('field') ?? $this->choice(
            'Выберите поле для тестирования',
            [
                'measurements_file',
                'final_project_file', 
                'work_act',
                'chat_screenshot',
                'archicad_file'
            ],
            'measurements_file'
        );
        
        if (!$dealId) {
            $this->error('❌ ID сделки не указан');
            return 1;
        }
        
        // Ищем сделку
        $deal = Deal::find($dealId);
        if (!$deal) {
            $this->error("❌ Сделка с ID {$dealId} не найдена");
            return 1;
        }
        
        $this->info("📋 Тестируем сделку: #{$deal->project_number}");
        $this->info("🎯 Поле: {$fieldName}");
        $this->newLine();
        
        // Проверяем текущие значения
        $yandexUrlField = 'yandex_url_' . $fieldName;
        $originalNameField = 'original_name_' . $fieldName;
        
        $currentUrl = $deal->{$yandexUrlField};
        $currentName = $deal->{$originalNameField};
        
        $this->info('📊 Текущие значения:');
        $this->line("  URL: " . ($currentUrl ?: 'НЕТ'));
        $this->line("  Имя: " . ($currentName ?: 'НЕТ'));
        $this->newLine();
        
        // Меню действий
        $action = $this->choice(
            'Выберите действие:',
            [
                'show' => 'Показать только текущие данные',
                'add' => 'Добавить тестовую ссылку',
                'update' => 'Обновить существующую ссылку',
                'delete' => 'Удалить ссылку',
                'test_api' => 'Тестировать API контроллер'
            ],
            'show'
        );
        
        switch ($action) {
            case 'add':
                $this->testAddLink($deal, $fieldName);
                break;
                
            case 'update':
                $this->testUpdateLink($deal, $fieldName);
                break;
                
            case 'delete':
                $this->testDeleteLink($deal, $fieldName);
                break;
                
            case 'test_api':
                $this->testApiController($dealId, $fieldName);
                break;
                
            case 'show':
            default:
                $this->showCurrentData($deal, $fieldName);
                break;
        }
        
        return 0;
    }
    
    /**
     * Показать текущие данные
     */
    private function showCurrentData($deal, $fieldName)
    {
        $yandexUrlField = 'yandex_url_' . $fieldName;
        $originalNameField = 'original_name_' . $fieldName;
        
        $this->info('📋 Полная информация о поле:');
        $this->table(
            ['Атрибут', 'Значение'],
            [
                ['ID сделки', $deal->id],
                ['Номер проекта', $deal->project_number],
                ['Поле', $fieldName],
                ['URL поле', $yandexUrlField],
                ['URL значение', $deal->{$yandexUrlField} ?: 'NULL'],
                ['Имя поле', $originalNameField],
                ['Имя значение', $deal->{$originalNameField} ?: 'NULL'],
            ]
        );
        
        if ($deal->{$yandexUrlField}) {
            $this->info('✅ Ссылка на файл настроена корректно');
        } else {
            $this->warn('⚠️ Ссылка на файл отсутствует');
        }
    }
    
    /**
     * Тестировать добавление ссылки
     */
    private function testAddLink($deal, $fieldName)
    {
        $yandexUrlField = 'yandex_url_' . $fieldName;
        $originalNameField = 'original_name_' . $fieldName;
        
        $testUrl = 'https://disk.yandex.ru/i/test_file_' . time();
        $testName = 'Тестовый файл ' . date('H:i:s');
        
        $this->info('➕ Добавляем тестовую ссылку...');
        
        $deal->update([
            $yandexUrlField => $testUrl,
            $originalNameField => $testName
        ]);
        
        $this->info('✅ Тестовая ссылка добавлена:');
        $this->line("  URL: {$testUrl}");
        $this->line("  Имя: {$testName}");
        $this->newLine();
        $this->warn('💡 Проверьте интерфейс - ссылка должна появиться в модальном окне');
    }
    
    /**
     * Тестировать обновление ссылки
     */
    private function testUpdateLink($deal, $fieldName)
    {
        $yandexUrlField = 'yandex_url_' . $fieldName;
        $originalNameField = 'original_name_' . $fieldName;
        
        if (!$deal->{$yandexUrlField}) {
            $this->error('❌ Нет существующей ссылки для обновления');
            return;
        }
        
        $newUrl = 'https://disk.yandex.ru/i/updated_file_' . time();
        $newName = 'Обновленный файл ' . date('H:i:s');
        
        $this->info('🔄 Обновляем существующую ссылку...');
        
        $deal->update([
            $yandexUrlField => $newUrl,
            $originalNameField => $newName
        ]);
        
        $this->info('✅ Ссылка обновлена:');
        $this->line("  Новый URL: {$newUrl}");
        $this->line("  Новое имя: {$newName}");
        $this->newLine();
        $this->warn('💡 Проверьте интерфейс - ссылка должна обновиться в модальном окне');
    }
    
    /**
     * Тестировать удаление ссылки
     */
    private function testDeleteLink($deal, $fieldName)
    {
        $yandexUrlField = 'yandex_url_' . $fieldName;
        $originalNameField = 'original_name_' . $fieldName;
        
        if (!$deal->{$yandexUrlField}) {
            $this->error('❌ Нет ссылки для удаления');
            return;
        }
        
        $this->info('🗑️ Удаляем ссылку...');
        
        $deal->update([
            $yandexUrlField => null,
            $originalNameField => null
        ]);
        
        $this->info('✅ Ссылка удалена');
        $this->warn('💡 Проверьте интерфейс - ссылка должна исчезнуть из модального окна');
    }
    
    /**
     * Тестировать API контроллер
     */
    private function testApiController($dealId, $fieldName)
    {
        $this->info('🔌 Тестирование API контроллера...');
        
        // Тестируем info endpoint
        $infoUrl = url("/api/yandex-disk/info?deal_id={$dealId}&field_name={$fieldName}");
        $this->line("📡 Info URL: {$infoUrl}");
        
        // Тестируем health endpoint
        $healthUrl = url('/api/yandex-disk/health');
        $this->line("💚 Health URL: {$healthUrl}");
        
        $this->newLine();
        $this->info('💡 Используйте эти URL для тестирования в браузере или через curl');
    }
}
