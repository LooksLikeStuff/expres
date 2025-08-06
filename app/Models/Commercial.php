<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Services\YandexDiskService;
use Illuminate\Support\Facades\Log;

class Commercial extends Model
{
    use HasFactory;

    /**
     * Имя таблицы модели.
     *
     * @var string
     */
    protected $table = 'commercials';

    /**
     * Атрибуты, которые можно массово присваивать.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'title',
        'description',
        'status',
        'article',
        'user_id',
        'deal_id',
        'zones',
        'preferences',
        'price',
        'zone_budgets',
        'documents',
        'current_page',
    ];

    /**
     * Получить пользователя, которому принадлежит этот бриф.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    /**
     * Получить сделки, связанные с этим брифом.
     */
    public function deals()
    {
        return $this->hasMany(Deal::class, 'commercial_id', 'id');
    }

    /**
     * Отношение к сделке
     */
    public function deal()
    {
        return $this->belongsTo(Deal::class);
    }
    
    /**
     * Загружает документы на Яндекс.Диск
     *
     * @param array $files Массив файлов для загрузки
     * @return array Результаты загрузки
     */
    public function uploadDocuments($files)
    {
        try {
            $yandexDiskService = app(YandexDiskService::class);
            $uploadPath = "commercials/{$this->id}/documents";
            
            // Логируем начало загрузки
            Log::info('Начало загрузки документов', [
                'commercial_id' => $this->id,
                'files_count' => count($files),
                'upload_path' => $uploadPath
            ]);
            
            $results = $yandexDiskService->uploadFiles($files, $uploadPath);
            
            // Получаем существующие документы
            $existingDocuments = $this->documents ? json_decode($this->documents, true) : [];
            if (!is_array($existingDocuments)) {
                $existingDocuments = [];
            }
            
            // Добавляем только успешно загруженные файлы
            foreach ($results as $result) {
                if (isset($result['success']) && $result['success'] && !empty($result['url'])) {
                    $existingDocuments[] = $result['url'];
                    Log::info('Документ успешно загружен', [
                        'commercial_id' => $this->id,
                        'url' => $result['url']
                    ]);
                }
            }
            
            // Сохраняем обновленные документы
            $this->documents = json_encode(array_values(array_filter($existingDocuments)));
            $this->save();
            
            return $results;
        } catch (\Exception $e) {
            Log::error('Исключение при загрузке документов', [
                'commercial_id' => $this->id,
                'error' => $e->getMessage()
            ]);
            
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    
    /**
     * Удаляет файл с Яндекс.Диска
     *
     * @param string $fileUrl URL файла
     * @return bool Успешно ли удален файл
     */
    public function deleteFileFromYandexDisk($fileUrl)
    {
        $yandexDiskService = app(YandexDiskService::class);
        
        // Получаем путь из URL
        $path = $this->getPathFromUrl($fileUrl);
        
        $success = $yandexDiskService->deleteFile($path);
        
        if ($success) {
            // Обновляем список файлов
            if ($this->documents) {
                $documents = json_decode($this->documents, true);
                $documents = array_filter($documents, function($url) use ($fileUrl) {
                    return $url !== $fileUrl;
                });
                $this->documents = json_encode(array_values($documents));
                $this->save();
            }
        }
        
        return $success;
    }
    
    /**
     * Извлекает путь из URL файла
     *
     * @param string $url URL файла
     * @return string Путь к файлу
     */
    protected function getPathFromUrl($url)
    {
        $parsed = parse_url($url);
        if (isset($parsed['query'])) {
            parse_str($parsed['query'], $query);
            if (isset($query['path'])) {
                return $query['path'];
            }
        }
        
        return $url;
    }
}
