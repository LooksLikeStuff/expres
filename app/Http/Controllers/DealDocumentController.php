<?php

namespace App\Http\Controllers;

use App\Models\Deal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Response;

class DealDocumentController extends Controller
{
    /**
     * Скачивание документа сделки
     * 
     * @param Request $request
     * @param int $dealId
     * @param string $filename
     * @return \Illuminate\Http\Response|\Illuminate\Http\JsonResponse
     */
    public function downloadDocument(Request $request, $dealId, $filename)
    {
        try {
            // Проверяем авторизацию
            if (!Auth::check()) {
                return response()->json(['error' => 'Не авторизован'], 401);
            }

            // Получаем сделку
            $deal = Deal::findOrFail($dealId);
            
            // Проверяем права доступа к сделке
            if (!$this->canUserAccessDeal(Auth::user(), $deal)) {
                return response()->json(['error' => 'Нет доступа к документу'], 403);
            }

            // Декодируем имя файла (на случай, если оно было закодировано)
            $filename = urldecode($filename);
            
            Log::info("Попытка скачать документ", [
                'deal_id' => $dealId,
                'filename' => $filename,
                'user_id' => Auth::id()
            ]);
            
            // Формируем путь к файлу - проверяем несколько возможных локаций
            $possiblePaths = [
                "deals/{$dealId}/documents/{$filename}",
                "public/deals/{$dealId}/documents/{$filename}",
                "deals/{$dealId}/{$filename}",
                "public/deals/{$dealId}/{$filename}"
            ];
            
            $filePath = null;
            foreach ($possiblePaths as $path) {
                if (Storage::exists($path)) {
                    $filePath = $path;
                    break;
                }
            }
            
            if (!$filePath) {
                // Попробуем найти файл по частичному совпадению имени
                $searchPattern = "deals/{$dealId}";
                try {
                    $files = Storage::allFiles($searchPattern);
                    foreach ($files as $file) {
                        if (basename($file) === $filename) {
                            $filePath = $file;
                            break;
                        }
                    }
                } catch (\Exception $e) {
                    Log::warning("Ошибка при поиске файлов в директории {$searchPattern}: " . $e->getMessage());
                }
            }
            
            // Проверяем существование файла
            if (!$filePath) {
                Log::warning("Файл не найден ни в одной из возможных локаций", [
                    'deal_id' => $dealId,
                    'filename' => $filename,
                    'searched_paths' => $possiblePaths,
                    'user_id' => Auth::id()
                ]);
                return response()->json(['error' => 'Файл не найден'], 404);
            }

            // Получаем содержимое файла
            $fileContent = Storage::get($filePath);
            $mimeType = Storage::mimeType($filePath);
            $fileSize = Storage::size($filePath);
            
            // Логируем успешное скачивание
            Log::info("Скачивание документа сделки", [
                'file_path' => $filePath,
                'deal_id' => $dealId,
                'user_id' => Auth::id(),
                'file_size' => $fileSize
            ]);

            // Возвращаем файл для скачивания
            return response($fileContent)
                ->header('Content-Type', $mimeType)
                ->header('Content-Disposition', 'attachment; filename="' . $filename . '"')
                ->header('Content-Length', $fileSize);

        } catch (\Exception $e) {
            Log::error("Ошибка при скачивании документа сделки", [
                'exception' => $e->getMessage(),
                'deal_id' => $dealId,
                'filename' => $filename,
                'user_id' => Auth::id() ?? 'guest'
            ]);
            
            return response()->json([
                'error' => 'Ошибка при скачивании файла'
            ], 500);
        }
    }

    /**
     * Просмотр документа в браузере (для изображений и PDF)
     * 
     * @param Request $request
     * @param int $dealId
     * @param string $filename
     * @return \Illuminate\Http\Response|\Illuminate\Http\JsonResponse
     */
    public function viewDocument(Request $request, $dealId, $filename)
    {
        try {
            // Проверяем авторизацию
            if (!Auth::check()) {
                return response()->json(['error' => 'Не авторизован'], 401);
            }

            // Получаем сделку
            $deal = Deal::findOrFail($dealId);
            
            // Проверяем права доступа к сделке
            if (!$this->canUserAccessDeal(Auth::user(), $deal)) {
                return response()->json(['error' => 'Нет доступа к документу'], 403);
            }

            // Декодируем имя файла
            $filename = urldecode($filename);
            
            // Формируем путь к файлу
            $filePath = "deals/{$dealId}/documents/{$filename}";
            
            // Проверяем существование файла
            if (!Storage::exists($filePath)) {
                return response()->json(['error' => 'Файл не найден'], 404);
            }

            // Получаем содержимое файла
            $fileContent = Storage::get($filePath);
            $mimeType = Storage::mimeType($filePath);
            
            // Возвращаем файл для просмотра в браузере
            return response($fileContent)
                ->header('Content-Type', $mimeType)
                ->header('Content-Disposition', 'inline; filename="' . $filename . '"');

        } catch (\Exception $e) {
            Log::error("Ошибка при просмотре документа сделки", [
                'exception' => $e->getMessage(),
                'deal_id' => $dealId,
                'filename' => $filename,
                'user_id' => Auth::id() ?? 'guest'
            ]);
            
            return response()->json([
                'error' => 'Ошибка при просмотре файла'
            ], 500);
        }
    }

    /**
     * Скачивание документа по Яндекс.Диск URL
     * 
     * @param Request $request
     * @return \Illuminate\Http\Response|\Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function downloadFromYandex(Request $request)
    {
        try {
            $url = $request->input('url');
            $filename = $request->input('filename', 'document');
            
            if (!$url) {
                return response()->json(['error' => 'URL не указан'], 400);
            }

            // Проверяем авторизацию
            if (!Auth::check()) {
                return response()->json(['error' => 'Не авторизован'], 401);
            }

            // Проверяем, что это действительно ссылка на Яндекс.Диск
            if (!$this->isYandexDiskUrl($url)) {
                return response()->json(['error' => 'Некорректная ссылка на Яндекс.Диск'], 400);
            }

            // Преобразуем ссылку для прямого скачивания
            $downloadUrl = $this->convertToDirectDownloadUrl($url);
            
            // Логируем попытку скачивания
            Log::info("Скачивание документа с Яндекс.Диска", [
                'url' => $url,
                'download_url' => $downloadUrl,
                'filename' => $filename,
                'user_id' => Auth::id()
            ]);

            // Перенаправляем на прямое скачивание
            return redirect($downloadUrl);

        } catch (\Exception $e) {
            Log::error("Ошибка при скачивании с Яндекс.Диска", [
                'exception' => $e->getMessage(),
                'url' => $request->input('url'),
                'user_id' => Auth::id() ?? 'guest'
            ]);
            
            return response()->json([
                'error' => 'Ошибка при скачивании файла с Яндекс.Диска'
            ], 500);
        }
    }

    /**
     * Проверяет, может ли пользователь получить доступ к сделке
     * 
     * @param \App\Models\User $user
     * @param \App\Models\Deal $deal
     * @return bool
     */
    private function canUserAccessDeal($user, $deal)
    {
        // Администраторы и координаторы имеют доступ ко всем сделкам
        if (in_array($user->status, ['admin', 'coordinator'])) {
            return true;
        }

        // Партнеры имеют доступ к сделкам, где они назначены партнером
        if ($user->status === 'partner' && $deal->getAttribute('office_partner_id') == $user->id) {
            return true;
        }

        // Специалисты имеют доступ к сделкам, где они назначены исполнителями
        if (in_array($user->status, ['architect', 'designer', 'visualizer'])) {
            if ($deal->getAttribute('architect_id') == $user->id || 
                $deal->getAttribute('designer_id') == $user->id || 
                $deal->getAttribute('visualizer_id') == $user->id) {
                return true;
            }
        }

        // Клиенты имеют доступ к своим сделкам
        if ($user->status === 'client') {
            // Проверяем по номеру телефона или email
            $clientPhone = $deal->getAttribute('client_phone');
            $clientEmail = $deal->getAttribute('client_email');
            if (($clientPhone && $clientPhone == ($user->phone ?? null)) || 
                ($clientEmail && $clientEmail == $user->email)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Проверяет, является ли URL ссылкой на Яндекс.Диск
     * 
     * @param string $url
     * @return bool
     */
    private function isYandexDiskUrl($url)
    {
        return strpos($url, 'disk.yandex.ru') !== false || strpos($url, 'yadi.sk') !== false;
    }

    /**
     * Преобразует публичную ссылку Яндекс.Диска в ссылку для прямого скачивания
     * 
     * @param string $url
     * @return string
     */
    private function convertToDirectDownloadUrl($url)
    {
        // Если это уже прямая ссылка для скачивания, возвращаем как есть
        if (strpos($url, '/download') !== false) {
            return $url;
        }

        // Преобразуем публичную ссылку в ссылку для скачивания
        if (strpos($url, 'yadi.sk') !== false) {
            return str_replace('yadi.sk/d/', 'yadi.sk/d/', $url) . '/download';
        }

        if (strpos($url, 'disk.yandex.ru') !== false) {
            // Для ссылок вида https://disk.yandex.ru/i/...
            if (strpos($url, '/i/') !== false) {
                return str_replace('/i/', '/d/', $url);
            }
            // Для других ссылок добавляем /download
            return $url . (strpos($url, '?') !== false ? '&' : '?') . 'download=1';
        }

        return $url;
    }

    /**
     * Удаление документа сделки
     * 
     * @param Request $request
     * @param int $dealId
     * @param string $filename
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteDocument(Request $request, $dealId, $filename)
    {
        try {
            // Проверяем авторизацию
            if (!Auth::check()) {
                return response()->json(['error' => 'Не авторизован'], 401);
            }

            // Проверяем права доступа (только admin и coordinator могут удалять)
            $user = Auth::user();
            if (!in_array($user->status, ['admin', 'coordinator'])) {
                return response()->json(['error' => 'Недостаточно прав для удаления документа'], 403);
            }

            // Получаем сделку
            $deal = Deal::findOrFail($dealId);
            
            // Проверяем права доступа к сделке
            if (!$this->canUserAccessDeal($user, $deal)) {
                return response()->json(['error' => 'Нет доступа к сделке'], 403);
            }

            // Декодируем имя файла
            $filename = urldecode($filename);
            
            // Формируем путь к файлу
            $filePath = "deals/{$dealId}/documents/{$filename}";
            
            // Удаляем файл из storage, если он существует
            if (Storage::exists($filePath)) {
                Storage::delete($filePath);
                
                Log::info("Удален локальный файл документа сделки", [
                    'file_path' => $filePath,
                    'deal_id' => $dealId,
                    'user_id' => $user->id
                ]);
            }

            // Также нужно удалить ссылку из базы данных сделки
            // Проверяем, передано ли поле в запросе
            $field = $request->input('field');
            if ($field) {
                $yandexUrlField = "yandex_url_{$field}";
                $originalNameField = "original_name_{$field}";
                
                $deal->$yandexUrlField = null;
                $deal->$originalNameField = null;
                $deal->save();
                
                Log::info("Удалена ссылка на документ из базы данных", [
                    'field' => $field,
                    'deal_id' => $dealId,
                    'user_id' => $user->id
                ]);
            } else {
                // Если поле не передано, ищем его по имени файла
                $fieldToUpdate = $this->findFieldByFilename($deal, $filename);
                if ($fieldToUpdate) {
                    $yandexUrlField = "yandex_url_{$fieldToUpdate}";
                    $originalNameField = "original_name_{$fieldToUpdate}";
                    
                    $deal->$yandexUrlField = null;
                    $deal->$originalNameField = null;
                    $deal->save();
                    
                    Log::info("Удалена ссылка на документ из базы данных (найдено по имени файла)", [
                        'field' => $fieldToUpdate,
                        'deal_id' => $dealId,
                        'user_id' => $user->id
                    ]);
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Документ успешно удален'
            ]);

        } catch (\Exception $e) {
            Log::error("Ошибка при удалении документа сделки", [
                'exception' => $e->getMessage(),
                'deal_id' => $dealId,
                'filename' => $filename,
                'user_id' => Auth::id() ?? 'guest'
            ]);
            
            return response()->json([
                'error' => 'Ошибка при удалении файла'
            ], 500);
        }
    }

    /**
     * Находит поле сделки по имени файла
     * 
     * @param Deal $deal
     * @param string $filename
     * @return string|null
     */
    private function findFieldByFilename(Deal $deal, $filename)
    {
        $fileFields = [
            'execution_order_file', 'measurements_file', 'final_floorplan', 'final_collage',
            'final_project_file', 'work_act', 'archicad_file', 'contract_attachment', 
            'plan_final', 'chat_screenshot'
        ];
        
        foreach ($fileFields as $field) {
            $originalNameField = "original_name_{$field}";
            if (isset($deal->$originalNameField) && $deal->$originalNameField === $filename) {
                return $field;
            }
        }
        
        return null;
    }
}
