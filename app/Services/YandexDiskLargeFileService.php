<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Utils;
use Exception;

/**
 * Надежный сервис для загрузки больших файлов на Яндекс.Диск
 * Поддерживает файлы до 2GB без таймаутов
 */
class YandexDiskLargeFileService
{
    private const YANDEX_DISK_API_URL = 'https://cloud-api.yandex.net';
    private const CHUNK_SIZE = 2 * 1024 * 1024; // 2MB chunks
    private const MAX_RETRIES = 3;
    private const TIMEOUT_SECONDS = 0; // Без ограничений
    private const CONNECT_TIMEOUT = 60;
    
    private string $token;
    private Client $httpClient;
    
    public function __construct()
    {
        $this->token = config('services.yandex_disk.token');
        
        if (!$this->token) {
            throw new Exception('Yandex Disk token не настроен');
        }
        
        $this->initializeHttpClient();
    }
    
    /**
     * Инициализация HTTP клиента с оптимизированными настройками
     */
    private function initializeHttpClient(): void
    {
        // Проверяем, что cURL доступен
        if (!extension_loaded('curl')) {
            throw new Exception('Расширение cURL не установлено');
        }

        $this->httpClient = new Client([
            'timeout' => 0, // Отключаем таймауты полностью
            'connect_timeout' => 30, // Уменьшаем timeout подключения
            'read_timeout' => 0, // Отключаем timeout чтения
            'headers' => [
                'Authorization' => 'OAuth ' . $this->token,
                'User-Agent' => 'YandexDiskLargeFileUploader/3.1-Fixed',
                'Accept' => 'application/json',
                'Accept-Encoding' => 'gzip, deflate',
            ],
            'curl' => $this->getSafeCurlOptions(),
            'stream' => true,
            'verify' => false, // Отключаем проверку сертификатов для отладки
            'http_errors' => false, // Не выбрасываем исключения на HTTP ошибки
        ]);
    }

    /**
     * Получение безопасных настроек cURL с проверкой констант
     */
    private function getSafeCurlOptions(): array
    {
        $options = [];

        // Базовые настройки cURL, которые должны быть доступны всегда
        if (defined('CURLOPT_FOLLOWLOCATION')) {
            $options[CURLOPT_FOLLOWLOCATION] = true;
        }
        
        if (defined('CURLOPT_MAXREDIRS')) {
            $options[CURLOPT_MAXREDIRS] = 5;
        }
        
        if (defined('CURLOPT_SSL_VERIFYPEER')) {
            $options[CURLOPT_SSL_VERIFYPEER] = false; // Отключаем для отладки
        }
        
        if (defined('CURLOPT_SSL_VERIFYHOST')) {
            $options[CURLOPT_SSL_VERIFYHOST] = 0; // Отключаем для отладки
        }
        
        if (defined('CURLOPT_NOPROGRESS')) {
            $options[CURLOPT_NOPROGRESS] = false;
        }
        
        // Изменяем стратегию соединения для борьбы с error 52
        if (defined('CURLOPT_FRESH_CONNECT')) {
            $options[CURLOPT_FRESH_CONNECT] = false; // Разрешаем переиспользование соединений
        }
        
        if (defined('CURLOPT_FORBID_REUSE')) {
            $options[CURLOPT_FORBID_REUSE] = false; // Разрешаем переиспользование соединения
        }
        
        if (defined('CURL_HTTP_VERSION_1_1')) {
            $options[CURLOPT_HTTP_VERSION] = CURL_HTTP_VERSION_1_1; // Используем HTTP/1.1
        }
        
        if (defined('CURLOPT_USERAGENT')) {
            $options[CURLOPT_USERAGENT] = 'YandexDiskLargeFileUploader/3.1-Fixed';
        }

        // Настройки для борьбы с error 52 (Empty reply from server)
        if (defined('CURLOPT_TIMEOUT')) {
            $options[CURLOPT_TIMEOUT] = 0; // Отключаем общий таймаут
        }
        
        if (defined('CURLOPT_CONNECTTIMEOUT')) {
            $options[CURLOPT_CONNECTTIMEOUT] = 30; // 30 секунд на подключение
        }
        
        if (defined('CURLOPT_LOW_SPEED_LIMIT')) {
            $options[CURLOPT_LOW_SPEED_LIMIT] = 1024; // Минимум 1KB/s
        }
        
        if (defined('CURLOPT_LOW_SPEED_TIME')) {
            $options[CURLOPT_LOW_SPEED_TIME] = 30; // В течение 30 секунд
        }

        // Добавляем опциональные настройки
        $optionalOptions = $this->getOptionalCurlOptions();
        foreach ($optionalOptions as $option => $value) {
            $options[$option] = $value;
        }

        return $options;
    }
    
    /**
     * Загрузка файла на Яндекс.Диск
     */
    public function uploadFile(UploadedFile $file, string $dealId, string $fieldName): array
    {
        $startTime = microtime(true);
        $fileName = $this->generateFileName($file, $dealId, $fieldName);
        $remotePath = $this->getRemotePath($dealId, $fieldName, $fileName);
        $fileSize = $file->getSize();
        
        Log::info('🚀 Начало загрузки большого файла на Яндекс.Диск', [
            'file_name' => $fileName,
            'file_size' => $this->formatBytes($fileSize),
            'remote_path' => $remotePath,
            'deal_id' => $dealId,
            'field_name' => $fieldName
        ]);
        
        try {
            // Создаем папку сделки если не существует
            $this->createDealFolder($dealId);
            
            // Получаем ссылку для загрузки
            $uploadUrl = $this->getUploadUrl($remotePath, true); // overwrite = true
            
            // Загружаем файл с потоковой передачей
            $result = $this->uploadFileStream($file, $uploadUrl, $fileName, $fileSize);
            
            // Получаем публичную ссылку
            $publicUrl = $this->makeFilePublic($remotePath);
            
            $uploadTime = round(microtime(true) - $startTime, 2);
            $speed = $fileSize > 0 ? round($fileSize / ($uploadTime ?: 1) / 1024 / 1024, 2) : 0;
            
            $response = [
                'success' => true,
                'data' => [
                    'yandex_disk_url' => $publicUrl,
                    'original_name' => $file->getClientOriginalName(),
                    'file_size' => $fileSize,
                    'file_size_formatted' => $this->formatBytes($fileSize),
                    'upload_time' => $uploadTime,
                    'upload_speed' => $speed . ' MB/s',
                    'remote_path' => $remotePath,
                    'field_name' => $fieldName,
                ]
            ];
            
            Log::info('✅ Файл успешно загружен на Яндекс.Диск', [
                'file_name' => $fileName,
                'upload_time' => $uploadTime . 's',
                'upload_speed' => $speed . ' MB/s',
                'public_url' => $publicUrl
            ]);
            
            return $response;
            
        } catch (Exception $e) {
            Log::error('❌ Ошибка загрузки файла на Яндекс.Диск', [
                'file_name' => $fileName,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Создание папки для сделки
     */
    private function createDealFolder(string $dealId): void
    {
        $folderPath = "/deal_{$dealId}";
        
        try {
            $response = $this->httpClient->put(self::YANDEX_DISK_API_URL . '/v1/disk/resources', [
                'query' => [
                    'path' => $folderPath
                ]
            ]);
            
            if ($response->getStatusCode() === 201) {
                Log::info("📁 Создана папка для сделки: {$folderPath}");
            }
        } catch (Exception $e) {
            // Папка уже существует или другая ошибка - не критично
            Log::debug("Папка {$folderPath} уже существует или ошибка создания: " . $e->getMessage());
        }
    }
    
    /**
     * Получение ссылки для загрузки файла
     */
    private function getUploadUrl(string $remotePath, bool $overwrite = false): string
    {
        $attempt = 0;
        
        while ($attempt < self::MAX_RETRIES) {
            try {
                $response = $this->httpClient->get(self::YANDEX_DISK_API_URL . '/v1/disk/resources/upload', [
                    'query' => [
                        'path' => $remotePath,
                        'overwrite' => $overwrite ? 'true' : 'false'
                    ]
                ]);
                
                $data = json_decode($response->getBody()->getContents(), true);
                
                if (isset($data['href'])) {
                    Log::info('🔗 Получена ссылка для загрузки файла', [
                        'remote_path' => $remotePath,
                        'upload_url' => substr($data['href'], 0, 100) . '...'
                    ]);
                    return $data['href'];
                }
                
                throw new Exception('Не удалось получить ссылку для загрузки');
                
            } catch (Exception $e) {
                $attempt++;
                Log::warning("Попытка {$attempt} получения ссылки для загрузки не удалась: " . $e->getMessage());
                
                if ($attempt >= self::MAX_RETRIES) {
                    throw new Exception("Не удалось получить ссылку для загрузки после {$attempt} попыток: " . $e->getMessage());
                }
                
                sleep(2 * $attempt); // Экспоненциальная задержка
            }
        }
        
        throw new Exception('Неожиданная ошибка при получении ссылки для загрузки');
    }
    
    /**
     * Загрузка файла с потоковой передачей
     */
    private function uploadFileStream(UploadedFile $file, string $uploadUrl, string $fileName, int $fileSize): bool
    {
        $filePath = $file->getPathname();
        $attempt = 0;
        
        while ($attempt < self::MAX_RETRIES) {
            try {
                Log::info("📤 Попытка " . ($attempt + 1) . " загрузки файла {$fileName} размером " . $this->formatBytes($fileSize));
                Log::info("🔗 URL для загрузки: " . substr($uploadUrl, 0, 100) . "...");
                
                // Открываем файл для чтения
                $fileHandle = fopen($filePath, 'rb');
                if (!$fileHandle) {
                    throw new Exception("Не удалось открыть файл для чтения: {$filePath}");
                }
                
                // Создаем поток
                $fileStream = Utils::streamFor($fileHandle);
                
                // Создаем отдельный клиент для загрузки без авторизационных заголовков
                $uploadClient = new Client([
                    'timeout' => 0, // Отключаем общий таймаут
                    'connect_timeout' => 30, // 30 секунд на подключение
                    'read_timeout' => 0, // Отключаем таймаут чтения
                    'curl' => $this->getUploadCurlOptions($fileName, $fileSize),
                    'verify' => false, // Отключаем проверку сертификатов
                    'http_errors' => false, // Не выбрасываем исключения на HTTP ошибки
                ]);
                
                Log::info("🔄 Начинаем потоковую загрузку файла {$fileName}...");
                
                // Выполняем загрузку
                $response = $uploadClient->put($uploadUrl, [
                    'body' => $fileStream,
                    'headers' => [
                        'Content-Type' => 'application/octet-stream',
                        'Content-Length' => (string)$fileSize,
                    ]
                ]);
                
                // Закрываем файл
                fclose($fileHandle);
                
                $statusCode = $response->getStatusCode();
                $responseBody = $response->getBody()->getContents();
                
                Log::info("📊 Ответ сервера: Статус {$statusCode}, Тело: " . substr($responseBody, 0, 200));
                
                if ($statusCode >= 200 && $statusCode < 300) {
                    Log::info("✅ Файл {$fileName} успешно загружен на попытке " . ($attempt + 1));
                    return true;
                }
                
                throw new Exception("HTTP статус {$statusCode}: " . $responseBody);
                
            } catch (Exception $e) {
                if (isset($fileHandle) && is_resource($fileHandle)) {
                    fclose($fileHandle);
                }
                
                $attempt++;
                $errorMessage = $e->getMessage();
                
                Log::warning("❌ Попытка {$attempt} загрузки файла {$fileName} не удалась: " . $errorMessage);
                
                // Специальная обработка cURL error 52
                if (strpos($errorMessage, 'cURL error 52') !== false || strpos($errorMessage, 'Empty reply from server') !== false) {
                    Log::warning("🔄 Обнаружена ошибка cURL 52 (Empty reply from server) - получаем новую ссылку для загрузки");
                    
                    // Получаем новую ссылку для загрузки
                    try {
                        // Используем изначальную логику для получения нового URL
                        $parts = explode('/', trim($uploadUrl, '/'));
                        if (count($parts) >= 2) {
                            $dealId = $parts[count($parts) - 2] ?? '';
                            $fieldName = $parts[count($parts) - 1] ?? '';
                            if ($dealId && $fieldName) {
                                $remotePath = $this->getRemotePath($dealId, $fieldName, $fileName);
                                $uploadUrl = $this->getUploadUrl($remotePath, true);
                                Log::info("🔗 Получена новая ссылка для загрузки: " . substr($uploadUrl, 0, 100) . "...");
                            }
                        }
                    } catch (Exception $urlError) {
                        Log::error("❌ Не удалось получить новую ссылку для загрузки: " . $urlError->getMessage());
                    }
                }
                
                if ($attempt >= self::MAX_RETRIES) {
                    throw new Exception("Не удалось загрузить файл после {$attempt} попыток: " . $errorMessage);
                }
                
                // Экспоненциальная задержка
                $delay = min(10, 2 * $attempt); // Уменьшаем максимальную задержку
                Log::info("⏳ Ожидание {$delay} секунд перед следующей попыткой...");
                sleep($delay);
            }
        }
        
        return false;
    }
    
    /**
     * Создание публичной ссылки на файл
     */
    private function makeFilePublic(string $remotePath): string
    {
        try {
            // Делаем файл публичным
            $response = $this->httpClient->put(self::YANDEX_DISK_API_URL . '/v1/disk/resources/publish', [
                'query' => [
                    'path' => $remotePath
                ]
            ]);
            
            if ($response->getStatusCode() === 200) {
                // Получаем публичную ссылку
                $response = $this->httpClient->get(self::YANDEX_DISK_API_URL . '/v1/disk/resources', [
                    'query' => [
                        'path' => $remotePath,
                        'fields' => 'public_url'
                    ]
                ]);
                
                $data = json_decode($response->getBody()->getContents(), true);
                
                if (isset($data['public_url'])) {
                    Log::info('🌍 Создана публичная ссылка для файла', [
                        'remote_path' => $remotePath,
                        'public_url' => $data['public_url']
                    ]);
                    return $data['public_url'];
                }
            }
            
            throw new Exception('Не удалось создать публичную ссылку');
            
        } catch (Exception $e) {
            Log::error('❌ Ошибка создания публичной ссылки: ' . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Удаление файла с Яндекс.Диска
     */
    public function deleteFile(string $dealId, string $fieldName): bool
    {
        try {
            // Получаем информацию о файле из базы данных или по пути
            $remotePath = $this->getRemotePathPattern($dealId, $fieldName);
            
            // Ищем файл в папке сделки
            $files = $this->listDealFiles($dealId);
            $fileToDelete = null;
            
            foreach ($files as $file) {
                if (strpos($file['path'], $fieldName) !== false) {
                    $fileToDelete = $file['path'];
                    break;
                }
            }
            
            if (!$fileToDelete) {
                Log::warning("Файл для удаления не найден: {$remotePath}");
                return false;
            }
            
            $response = $this->httpClient->delete(self::YANDEX_DISK_API_URL . '/v1/disk/resources', [
                'query' => [
                    'path' => $fileToDelete,
                    'permanently' => 'true'
                ]
            ]);
            
            if ($response->getStatusCode() === 204) {
                Log::info('🗑️ Файл успешно удален с Яндекс.Диска', [
                    'remote_path' => $fileToDelete
                ]);
                return true;
            }
            
            return false;
            
        } catch (Exception $e) {
            Log::error('❌ Ошибка удаления файла с Яндекс.Диска: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Получение списка файлов в папке сделки
     */
    private function listDealFiles(string $dealId): array
    {
        try {
            $folderPath = "/deal_{$dealId}";
            
            $response = $this->httpClient->get(self::YANDEX_DISK_API_URL . '/v1/disk/resources', [
                'query' => [
                    'path' => $folderPath,
                    'limit' => 100
                ]
            ]);
            
            $data = json_decode($response->getBody()->getContents(), true);
            
            if (isset($data['_embedded']['items'])) {
                return $data['_embedded']['items'];
            }
            
            return [];
            
        } catch (Exception $e) {
            Log::error('❌ Ошибка получения списка файлов: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Проверка состояния здоровья сервиса
     */
    public function healthCheck(): array
    {
        try {
            $response = $this->httpClient->get(self::YANDEX_DISK_API_URL . '/v1/disk', [
                'timeout' => 10
            ]);
            
            if ($response->getStatusCode() === 200) {
                $data = json_decode($response->getBody()->getContents(), true);
                
                return [
                    'status' => 'ok',
                    'total_space' => $this->formatBytes($data['total_space'] ?? 0),
                    'used_space' => $this->formatBytes($data['used_space'] ?? 0),
                    'free_space' => $this->formatBytes(($data['total_space'] ?? 0) - ($data['used_space'] ?? 0)),
                ];
            }
            
            return ['status' => 'error', 'message' => 'API недоступен'];
            
        } catch (Exception $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }
    
    /**
     * Генерация имени файла
     */
    private function generateFileName(UploadedFile $file, string $dealId, string $fieldName): string
    {
        $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $extension = $file->getClientOriginalExtension();
        $timestamp = date('Y-m-d_H-i-s');
        
        // Очищаем имя файла от недопустимых символов
        $cleanName = preg_replace('/[^a-zA-Z0-9_\-.]/', '_', $originalName);
        
        return "deal_{$dealId}_{$fieldName}_{$timestamp}_{$cleanName}.{$extension}";
    }
    
    /**
     * Получение удаленного пути файла
     */
    private function getRemotePath(string $dealId, string $fieldName, string $fileName): string
    {
        return "/deal_{$dealId}/{$fileName}";
    }
    
    /**
     * Получение паттерна пути для поиска файлов
     */
    private function getRemotePathPattern(string $dealId, string $fieldName): string
    {
        return "/deal_{$dealId}/deal_{$dealId}_{$fieldName}_";
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
    
    /**
     * Получение информации о файле
     */
    public function getFileInfo(string $dealId, string $fieldName): ?array
    {
        try {
            $files = $this->listDealFiles($dealId);
            
            foreach ($files as $file) {
                if (strpos($file['path'], $fieldName) !== false) {
                    return [
                        'name' => $file['name'],
                        'size' => $this->formatBytes($file['size']),
                        'created' => $file['created'],
                        'modified' => $file['modified'],
                        'public_url' => $file['public_url'] ?? null,
                        'path' => $file['path']
                    ];
                }
            }
            
            return null;
            
        } catch (Exception $e) {
            Log::error('❌ Ошибка получения информации о файле: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Получение опциональных CURL настроек
     * Проверяет доступность констант перед использованием
     */
    private function getOptionalCurlOptions(): array
    {
        $options = [];

        // Проверяем доступность TCP оптимизаций
        if (defined('CURLOPT_TCP_NODELAY')) {
            $options[CURLOPT_TCP_NODELAY] = true;
        }
        
        if (defined('CURLOPT_TCP_KEEPALIVE')) {
            $options[CURLOPT_TCP_KEEPALIVE] = 1;
        }
        
        if (defined('CURLOPT_TCP_KEEPIDLE')) {
            $options[CURLOPT_TCP_KEEPIDLE] = 60;
        }
        
        if (defined('CURLOPT_TCP_KEEPINTVL')) {
            $options[CURLOPT_TCP_KEEPINTVL] = 30;
        }
        
        if (defined('CURLOPT_BUFFERSIZE')) {
            $options[CURLOPT_BUFFERSIZE] = self::CHUNK_SIZE;
        }

        return $options;
    }

    /**
     * Получение настроек cURL для загрузки файлов
     */
    private function getUploadCurlOptions(string $fileName, int $fileSize): array
    {
        $options = $this->getSafeCurlOptions();

        // Дополнительные настройки для загрузки
        if (defined('CURLOPT_UPLOAD')) {
            $options[CURLOPT_UPLOAD] = true;
        }
        
        if (defined('CURLOPT_INFILESIZE')) {
            $options[CURLOPT_INFILESIZE] = $fileSize;
        }
        
        if (defined('CURLOPT_BUFFERSIZE')) {
            $options[CURLOPT_BUFFERSIZE] = self::CHUNK_SIZE;
        }

        // Специальные настройки для борьбы с error 52
        if (defined('CURLOPT_TIMEOUT')) {
            $options[CURLOPT_TIMEOUT] = 0; // Отключаем общий таймаут
        }
        
        if (defined('CURLOPT_CONNECTTIMEOUT')) {
            $options[CURLOPT_CONNECTTIMEOUT] = 30; // 30 секунд на подключение
        }
        
        if (defined('CURLOPT_NOSIGNAL')) {
            $options[CURLOPT_NOSIGNAL] = true; // Отключаем сигналы
        }
        
        // Отключаем Expect: 100-continue для больших файлов
        if (defined('CURLOPT_HTTPHEADER')) {
            $options[CURLOPT_HTTPHEADER] = ['Expect:'];
        }

        // Callback для отслеживания прогресса (если поддерживается)
        if (defined('CURLOPT_PROGRESSFUNCTION')) {
            $lastLoggedPercent = 0;
            $options[CURLOPT_PROGRESSFUNCTION] = function($downloadTotal, $downloaded, $uploadTotal, $uploaded) use ($fileName, &$lastLoggedPercent) {
                if ($uploadTotal > 0) {
                    $percent = round(($uploaded / $uploadTotal) * 100, 1);
                    // Логируем каждые 25%
                    if ($percent > 0 && $percent >= $lastLoggedPercent + 25) {
                        Log::info("📊 Прогресс загрузки {$fileName}: {$percent}% ({$this->formatBytes($uploaded)}/{$this->formatBytes($uploadTotal)})");
                        $lastLoggedPercent = $percent;
                    }
                }
                return 0; // Продолжаем загрузку
            };
        }

        return $options;
    }
}
