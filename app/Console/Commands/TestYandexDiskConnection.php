<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\YandexDiskLargeFileService;
use Exception;

class TestYandexDiskConnection extends Command
{
    protected $signature = 'yandex:test-connection';
    protected $description = 'Проверка подключения к Яндекс.Диску';

    public function handle()
    {
        $this->info('🔍 Проверка подключения к Яндекс.Диску...');
        
        try {
            $service = new YandexDiskLargeFileService();
            $healthCheck = $service->healthCheck();
            
            if ($healthCheck['status'] === 'ok') {
                $this->info('✅ Подключение к Яндекс.Диску успешно!');
                $this->info("📊 Общее место: {$healthCheck['total_space']}");
                $this->info("📈 Используется: {$healthCheck['used_space']}");
                $this->info("💾 Свободно: {$healthCheck['free_space']}");
            } else {
                $this->error('❌ Ошибка подключения: ' . $healthCheck['message']);
            }
            
        } catch (Exception $e) {
            $this->error('❌ Критическая ошибка: ' . $e->getMessage());
            return 1;
        }
        
        return 0;
    }
}
