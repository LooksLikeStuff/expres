<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\YandexDiskLargeFileService;
use Illuminate\Support\Facades\Http;
use Exception;

class CheckYandexDiskSystemCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'yandex-disk:check-system 
                            {--full : Выполнить полную проверку системы}
                            {--fix : Попытаться исправить найденные проблемы}';

    /**
     * The console command description.
     */
    protected $description = 'Проверка системы загрузки файлов на Яндекс.Диск v3.0';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔍 Проверка системы загрузки файлов на Яндекс.Диск v3.0');
        $this->newLine();

        $checks = [
            'checkConfiguration',
            'checkDependencies', 
            'checkAPI',
            'checkRoutes',
            'checkServices',
            'checkFiles',
        ];

        if ($this->option('full')) {
            $checks[] = 'checkYandexDiskConnection';
            $checks[] = 'checkPermissions';
        }

        $passed = 0;
        $failed = 0;

        foreach ($checks as $check) {
            if ($this->$check()) {
                $passed++;
            } else {
                $failed++;
            }
        }

        $this->newLine();
        $this->info("📊 Результаты проверки:");
        $this->info("✅ Пройдено: {$passed}");
        
        if ($failed > 0) {
            $this->error("❌ Не пройдено: {$failed}");
            return 1;
        } else {
            $this->info("🎉 Все проверки пройдены успешно!");
            return 0;
        }
    }

    /**
     * Проверка конфигурации
     */
    private function checkConfiguration(): bool
    {
        $this->info('📋 Проверка конфигурации...');

        $checks = [
            'YANDEX_DISK_TOKEN' => config('services.yandex_disk.token'),
            'Timeout настройка' => config('services.yandex_disk.timeout') === 0,
            'Chunk size' => config('services.yandex_disk.chunk_size', 0) > 0,
            'Max retries' => config('services.yandex_disk.max_retries', 0) > 0,
        ];

        $allPassed = true;

        foreach ($checks as $name => $value) {
            if ($value) {
                $this->line("  ✅ {$name}: OK");
            } else {
                $this->line("  ❌ {$name}: НЕ НАСТРОЕНО");
                $allPassed = false;

                if ($this->option('fix')) {
                    $this->warn("  🔧 Для исправления добавьте в .env: YANDEX_DISK_TOKEN=your_token");
                }
            }
        }

        return $allPassed;
    }

    /**
     * Проверка зависимостей PHP
     */
    private function checkDependencies(): bool
    {
        $this->info('📦 Проверка зависимостей PHP...');

        $checks = [
            'cURL' => extension_loaded('curl'),
            'JSON' => extension_loaded('json'),
            'FileInfo' => extension_loaded('fileinfo'),
            'OpenSSL' => extension_loaded('openssl'),
            'GuzzleHttp' => class_exists('GuzzleHttp\Client'),
        ];

        $phpSettings = [
            'upload_max_filesize' => $this->parseSize(ini_get('upload_max_filesize')) >= (2 * 1024 * 1024 * 1024),
            'post_max_size' => $this->parseSize(ini_get('post_max_size')) >= (2 * 1024 * 1024 * 1024),
            'memory_limit' => ini_get('memory_limit') === '-1' || $this->parseSize(ini_get('memory_limit')) >= (1024 * 1024 * 1024),
            'max_execution_time' => ini_get('max_execution_time') == 0,
        ];

        $allPassed = true;

        foreach ($checks as $name => $value) {
            if ($value) {
                $this->line("  ✅ {$name}: Установлено");
            } else {
                $this->line("  ❌ {$name}: НЕ НАЙДЕНО");
                $allPassed = false;
            }
        }

        foreach ($phpSettings as $name => $value) {
            if ($value) {
                $this->line("  ✅ {$name}: " . ini_get($name));
            } else {
                $this->line("  ❌ {$name}: " . ini_get($name) . " (недостаточно)");
                $allPassed = false;
            }
        }

        return $allPassed;
    }

    /**
     * Проверка API endpoints
     */
    private function checkAPI(): bool
    {
        $this->info('🌐 Проверка API endpoints...');

        $endpoints = [
            '/api/yandex-disk/health',
            '/api/ping',
            '/api/keepalive',
        ];

        $allPassed = true;
        $baseUrl = config('app.url', 'http://localhost');

        foreach ($endpoints as $endpoint) {
            try {
                $response = Http::timeout(10)->get($baseUrl . $endpoint);
                
                if ($response->successful()) {
                    $this->line("  ✅ {$endpoint}: OK");
                } else {
                    $this->line("  ❌ {$endpoint}: HTTP {$response->status()}");
                    $allPassed = false;
                }
            } catch (Exception $e) {
                $this->line("  ❌ {$endpoint}: " . $e->getMessage());
                $allPassed = false;
            }
        }

        return $allPassed;
    }

    /**
     * Проверка маршрутов
     */
    private function checkRoutes(): bool
    {
        $this->info('🛣️ Проверка маршрутов...');

        $routes = [
            'api/yandex-disk/upload',
            'api/yandex-disk/delete', 
            'api/yandex-disk/info',
            'api/yandex-disk/health',
        ];

        $allPassed = true;
        $routeCollection = app('router')->getRoutes();

        foreach ($routes as $route) {
            $found = false;
            foreach ($routeCollection as $r) {
                if (str_contains($r->uri(), $route)) {
                    $found = true;
                    break;
                }
            }

            if ($found) {
                $this->line("  ✅ {$route}: Зарегистрирован");
            } else {
                $this->line("  ❌ {$route}: НЕ НАЙДЕН");
                $allPassed = false;
            }
        }

        return $allPassed;
    }

    /**
     * Проверка сервисов
     */
    private function checkServices(): bool
    {
        $this->info('⚙️ Проверка сервисов...');

        try {
            $service = app(YandexDiskLargeFileService::class);
            $this->line("  ✅ YandexDiskLargeFileService: Создан успешно");

            $healthCheck = $service->healthCheck();
            if ($healthCheck['status'] === 'ok') {
                $this->line("  ✅ Состояние сервиса: OK");
                $this->line("  📊 Свободное место: " . ($healthCheck['free_space'] ?? 'неизвестно'));
            } else {
                $this->line("  ❌ Состояние сервиса: " . ($healthCheck['message'] ?? 'ошибка'));
                return false;
            }

            return true;
        } catch (Exception $e) {
            $this->line("  ❌ Сервис: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Проверка файлов системы
     */
    private function checkFiles(): bool
    {
        $this->info('📁 Проверка файлов системы...');

        $files = [
            'app/Services/YandexDiskLargeFileService.php',
            'app/Http/Controllers/Api/YandexDiskController.php',
            'app/Http/Middleware/LargeFileUploadMiddleware.php',
            'public/js/yandex-disk-uploader-v3.js',
            'public/css/yandex-disk-uploader-v3.css',
            'public/js/yandex-disk-modal-integration.js',
            'resources/views/deals/partials/components/field_types/file_v3.blade.php',
            'resources/views/deals/partials/components/yandex_disk_uploader_v3.blade.php',
        ];

        $allPassed = true;
        $basePath = base_path();

        foreach ($files as $file) {
            $fullPath = $basePath . '/' . $file;
            
            if (file_exists($fullPath)) {
                $size = filesize($fullPath);
                $this->line("  ✅ {$file}: " . $this->formatBytes($size));
            } else {
                $this->line("  ❌ {$file}: НЕ НАЙДЕН");
                $allPassed = false;
            }
        }

        return $allPassed;
    }

    /**
     * Проверка соединения с Яндекс.Диском
     */
    private function checkYandexDiskConnection(): bool
    {
        $this->info('☁️ Проверка соединения с Яндекс.Диском...');

        try {
            $service = app(YandexDiskLargeFileService::class);
            $healthCheck = $service->healthCheck();

            if ($healthCheck['status'] === 'ok') {
                $this->line("  ✅ Соединение: Успешно");
                $this->line("  📊 Общее место: " . ($healthCheck['total_space'] ?? 'неизвестно'));
                $this->line("  📊 Использовано: " . ($healthCheck['used_space'] ?? 'неизвестно'));
                $this->line("  📊 Свободно: " . ($healthCheck['free_space'] ?? 'неизвестно'));
                return true;
            } else {
                $this->line("  ❌ Соединение: " . ($healthCheck['message'] ?? 'ошибка'));
                return false;
            }
        } catch (Exception $e) {
            $this->line("  ❌ Соединение: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Проверка прав доступа
     */
    private function checkPermissions(): bool
    {
        $this->info('🔐 Проверка прав доступа...');

        $directories = [
            storage_path('logs'),
            storage_path('app/public'),
            public_path('uploads'),
        ];

        $allPassed = true;

        foreach ($directories as $dir) {
            if (!is_dir($dir)) {
                @mkdir($dir, 0755, true);
            }

            if (is_writable($dir)) {
                $this->line("  ✅ " . basename($dir) . ": Доступ на запись");
            } else {
                $this->line("  ❌ " . basename($dir) . ": НЕТ доступа на запись");
                $allPassed = false;

                if ($this->option('fix')) {
                    $this->warn("  🔧 Исправление: chmod 755 {$dir}");
                    @chmod($dir, 0755);
                }
            }
        }

        return $allPassed;
    }

    /**
     * Преобразование размера в байты
     */
    private function parseSize(string $size): int
    {
        $size = trim($size);
        $last = strtolower($size[strlen($size) - 1]);
        $value = (int) $size;

        switch ($last) {
            case 'g':
                $value *= 1024;
            case 'm':
                $value *= 1024;
            case 'k':
                $value *= 1024;
        }

        return $value;
    }

    /**
     * Форматирование размера файла
     */
    private function formatBytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, $precision) . ' ' . $units[$i];
    }
}
