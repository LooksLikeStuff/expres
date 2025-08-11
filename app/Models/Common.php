<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Services\YandexDiskService;
use Illuminate\Support\Facades\Log;

class Common extends Model
{
    use HasFactory;

    /**
     * Имя таблицы модели.
     *
     * @var string
     */
    protected $table = 'commons';

    /**
     * Атрибуты, которые можно массово присваивать.
     *
     * @var array
     */
    protected $fillable = [
        'title', 'description', 'status', 'article', 'user_id', 'deal_id', 'answers', 
        'current_page', 'skipped_pages', 'references', 'custom_rooms', 'custom_room_answers', 'edit_status'
    ]; 

    // Связь с координатором
    public function coordinator()
    {
        return $this->belongsTo(User::class, 'coordinator_id');
    }

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
        return $this->hasMany(Deal::class, 'common_id', 'id');
    }
    
    /**
     * Отношение к сделке
     */
    public function deal()
    {
        return $this->belongsTo(Deal::class);
    }
    
    /**
     * Связь с историей изменений брифа
     */
    public function histories()
    {
        return $this->hasMany(CommonBriefHistory::class);
    }
    
    /**
     * Проверяет, редактировался ли бриф
     *
     * @return bool
     */
    public function hasEditHistory()
    {
        return $this->histories()->exists();
    }
    
    /**
     * Загружает референсы на Яндекс.Диск
     *
     * @param array $files Массив файлов для загрузки
     * @return array Результаты загрузки
     */
    public function uploadReferences($files)
    {
        try {
            $yandexDiskService = app(YandexDiskService::class);
            $uploadPath = "commons/{$this->id}/references";
            
            // Логируем начало загрузки
            Log::info('Начало загрузки референсов', [
                'common_id' => $this->id,
                'files_count' => count($files),
                'upload_path' => $uploadPath
            ]);
            
            // Предварительно создаем директорию для загрузки
            $results = $yandexDiskService->uploadFiles($files, $uploadPath);
            
            // Получаем существующие референсы
            $existingReferences = $this->references ? json_decode($this->references, true) : [];
            if (!is_array($existingReferences)) {
                $existingReferences = [];
            }
            
            // Логируем результаты загрузки каждого файла
            foreach ($results as $index => $result) {
                if (!isset($result['success'])) {
                    Log::error('Загрузка референса: отсутствует флаг успешности', [
                        'common_id' => $this->id,
                        'result' => $result
                    ]);
                    continue;
                }
                
                if (!$result['success']) {
                    Log::error('Загрузка референса не удалась', [
                        'common_id' => $this->id,
                        'result' => $result
                    ]);
                    continue;
                }
                
                if (empty($result['url'])) {
                    Log::warning('Загрузка референса: отсутствует URL', [
                        'common_id' => $this->id,
                        'result' => $result
                    ]);
                    continue;
                }
                
                $existingReferences[] = $result['url'];
                Log::info('Референс успешно загружен', [
                    'common_id' => $this->id,
                    'url' => $result['url']
                ]);
            }
            
            // Сохраняем обновленные референсы
            $this->references = json_encode(array_values(array_filter($existingReferences)));
            $this->save();
            
            Log::info('Загрузка референсов завершена', [
                'common_id' => $this->id,
                'references_count' => count($existingReferences)
            ]);
            
            return $results;
        } catch (\Exception $e) {
            Log::error('Исключение при загрузке референсов', [
                'common_id' => $this->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    
    /**
     * Загружает документы на Яндекс.Диск
     *
     * @param array $files Массив файлов
     * @return array Результаты загрузки
     */
    public function uploadDocuments($files)
    {
        // Используем аналогичный подход как в uploadReferences, но для документов
        try {
            $yandexDiskService = app(YandexDiskService::class);
            $uploadPath = "commons/{$this->id}/documents";
            
            // Логируем начало загрузки
            Log::info('Начало загрузки документов', [
                'common_id' => $this->id,
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
                        'common_id' => $this->id,
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
                'common_id' => $this->id,
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
            if ($this->references) {
                $references = json_decode($this->references, true);
                $references = array_filter($references, function($url) use ($fileUrl) {
                    return $url !== $fileUrl;
                });
                $this->references = json_encode($references);
            }
            
            if ($this->documents) {
                $documents = json_decode($this->documents, true);
                $documents = array_filter($documents, function($url) use ($fileUrl) {
                    return $url !== $fileUrl;
                });
                $this->documents = json_encode($documents);
            }
            
            $this->save();
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

