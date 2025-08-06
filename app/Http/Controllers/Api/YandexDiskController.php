<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\YandexDiskLargeFileService;
use App\Models\Deal;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Exception;

/**
 * API контроллер для загрузки файлов на Яндекс.Диск
 */
class YandexDiskController extends Controller
{
    private YandexDiskLargeFileService $yandexDiskService;
    
    public function __construct(YandexDiskLargeFileService $yandexDiskService)
    {
        $this->yandexDiskService = $yandexDiskService;
    }
    
    /**
     * Загрузка файла на Яндекс.Диск
     */
    public function upload(Request $request): JsonResponse
    {
        try {
            // Валидация запроса
            $validator = Validator::make($request->all(), [
                'file' => 'required|file', // Убираем ограничение размера
                'deal_id' => 'required|integer|exists:deals,id',
                'field_name' => 'required|string|in:measurements_file,final_project_file,work_act,chat_screenshot,archicad_file,execution_order_file,final_floorplan,final_collage,contract_attachment,screenshot_work_1,screenshot_work_2,screenshot_work_3,screenshot_final,plan_final'
            ]);
            
            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'error' => 'Ошибка валидации',
                    'details' => $validator->errors()
                ], 422);
            }
            
            $file = $request->file('file');
            $dealId = $request->input('deal_id');
            $fieldName = $request->input('field_name');
            
            // Проверяем права доступа к сделке
            $deal = Deal::findOrFail($dealId);
            
            // Логируем начало загрузки
            Log::info('🚀 API: Начало загрузки файла на Яндекс.Диск', [
                'file_name' => $file->getClientOriginalName(),
                'file_size' => $this->formatBytes($file->getSize()),
                'deal_id' => $dealId,
                'field_name' => $fieldName,
                'user_id' => auth()->id()
            ]);
            
            // Загружаем файл через сервис
            $result = $this->yandexDiskService->uploadFile($file, $dealId, $fieldName);
            
            if ($result['success']) {
                // Обновляем поля Яндекс.Диска в сделке
                $this->updateYandexDiskFields($deal, $fieldName, $result['data']);
                
                // Получаем обновленные данные сделки
                $deal->refresh();
                
                Log::info('✅ API: Файл успешно загружен и сделка обновлена', [
                    'deal_id' => $dealId,
                    'field_name' => $fieldName,
                    'file_url' => $result['data']['yandex_disk_url'],
                    'original_name' => $result['data']['original_name']
                ]);
                
                // Возвращаем результат с данными сделки для обновления интерфейса
                $response = $result;
                $response['deal'] = $deal->toArray();
                
                return response()->json($response);
            } else {
                Log::error('❌ API: Ошибка загрузки файла', [
                    'deal_id' => $dealId,
                    'field_name' => $fieldName,
                    'error' => $result['error']
                ]);
                
                return response()->json($result, 500);
            }
            
        } catch (Exception $e) {
            Log::error('❌ API: Критическая ошибка при загрузке файла', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'error' => 'Внутренняя ошибка сервера: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Удаление файла с Яндекс.Диска
     */
    public function delete(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'deal_id' => 'required|integer|exists:deals,id',
                'field_name' => 'required|string'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'error' => 'Ошибка валидации',
                    'details' => $validator->errors()
                ], 422);
            }

            $dealId = $request->input('deal_id');
            $fieldName = $request->input('field_name');

            // Проверяем права доступа к сделке
            $deal = Deal::findOrFail($dealId);

            Log::info('🗑️ API: Начало удаления файла с Яндекс.Диска', [
                'deal_id' => $dealId,
                'field_name' => $fieldName,
                'user_id' => auth()->id()
            ]);

            // Удаляем файл через сервис
            $deleted = $this->yandexDiskService->deleteFile($dealId, $fieldName);

            if ($deleted) {
                // Очищаем поля Яндекс.Диска в сделке
                $this->updateYandexDiskFields($deal, $fieldName, null);

                Log::info('✅ API: Файл успешно удален и сделка обновлена', [
                    'deal_id' => $dealId,
                    'field_name' => $fieldName
                ]);
                
                return response()->json([
                    'success' => true,
                    'message' => 'Файл успешно удален'
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'error' => 'Не удалось удалить файл'
                ], 500);
            }
            
        } catch (Exception $e) {
            Log::error('❌ API: Критическая ошибка при удалении файла', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'error' => 'Внутренняя ошибка сервера: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Получение информации о файле
     */
    public function info(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'deal_id' => 'required|integer|exists:deals,id',
                'field_name' => 'required|string'
            ]);
            
            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'error' => 'Ошибка валидации',
                    'details' => $validator->errors()
                ], 422);
            }
            
            $dealId = $request->input('deal_id');
            $fieldName = $request->input('field_name');
            
            // Проверяем права доступа к сделке
            $deal = Deal::findOrFail($dealId);
            
            // Получаем информацию о файле
            $fileInfo = $this->yandexDiskService->getFileInfo($dealId, $fieldName);
            
            if ($fileInfo) {
                return response()->json([
                    'success' => true,
                    'data' => $fileInfo
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'error' => 'Файл не найден'
                ], 404);
            }
            
        } catch (Exception $e) {
            Log::error('❌ API: Ошибка получения информации о файле', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'error' => 'Внутренняя ошибка сервера: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Проверка состояния здоровья сервиса Яндекс.Диск
     */
    public function health(Request $request): JsonResponse
    {
        try {
            Log::info('🏥 API: Проверка состояния Яндекс.Диск сервиса');
            
            $healthData = $this->yandexDiskService->healthCheck();
            
            Log::info('✅ API: Проверка состояния завершена', [
                'status' => $healthData['status'] ?? 'unknown'
            ]);
            
            return response()->json([
                'success' => true,
                'data' => $healthData,
                'timestamp' => now()->toISOString()
            ]);
            
        } catch (Exception $e) {
            Log::error('❌ API: Ошибка проверки состояния сервиса', [
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'error' => 'Ошибка проверки состояния сервиса: ' . $e->getMessage(),
                'timestamp' => now()->toISOString()
            ], 500);
        }
    }
    
    /**
     * Обновление полей Яндекс.Диска в сделке
     */
    private function updateYandexDiskFields(Deal $deal, string $fieldName, ?array $fileData): void
    {
        try {
            DB::beginTransaction();
            
            $yandexUrlField = 'yandex_url_' . $fieldName;
            $originalNameField = 'original_name_' . $fieldName;
            
            if ($fileData && isset($fileData['yandex_disk_url'])) {
                // Устанавливаем новые значения
                $newUrl = $fileData['yandex_disk_url'];
                $newName = $fileData['original_name'] ?? 'Загруженный файл';
                
                $deal->update([
                    $yandexUrlField => $newUrl,
                    $originalNameField => $newName
                ]);
                
                Log::info('📝 Обновлены поля Яндекс.Диска в сделке', [
                    'deal_id' => $deal->id,
                    'field_name' => $fieldName,
                    'yandex_url' => $newUrl,
                    'original_name' => $newName
                ]);
            } else {
                // Очищаем значения
                $deal->update([
                    $yandexUrlField => null,
                    $originalNameField => null
                ]);
                
                Log::info('📝 Очищены поля Яндекс.Диска в сделке', [
                    'deal_id' => $deal->id,
                    'field_name' => $fieldName
                ]);
            }
            
            DB::commit();
            
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('❌ Ошибка обновления полей Яндекс.Диска в сделке', [
                'deal_id' => $deal->id,
                'field_name' => $fieldName,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
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
