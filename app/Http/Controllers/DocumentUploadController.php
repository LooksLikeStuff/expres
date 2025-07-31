<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Exception;

class DocumentUploadController extends Controller
{
    /**
     * Загрузка документов
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function uploadDocuments(Request $request)
    {
        try {
            Log::info('Запрос на загрузку документов', [
                'files_count' => $request->hasFile('documents') ? count($request->file('documents')) : 0,
                'user_id' => auth()->id()
            ]);

            // Валидация
            $request->validate([
                'documents.*' => 'required|file|mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png,zip,rar|max:102400', // 100MB
            ]);

            if (!$request->hasFile('documents')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Файлы для загрузки не найдены'
                ]);
            }

            $uploadedFiles = [];
            $errors = [];

            foreach ($request->file('documents') as $index => $file) {
                try {
                    $originalName = $file->getClientOriginalName();
                    $extension = $file->getClientOriginalExtension();
                    $size = $file->getSize();
                    
                    // Генерируем уникальное имя файла
                    $filename = time() . '_' . uniqid() . '_' . $originalName;
                    
                    // Определяем путь для сохранения
                    $path = 'documents/' . date('Y/m');
                    
                    // Сохраняем файл
                    $filePath = $file->storeAs($path, $filename, 'public');
                    
                    if ($filePath) {
                        $uploadedFiles[] = [
                            'original_name' => $originalName,
                            'filename' => $filename,
                            'path' => $filePath,
                            'url' => Storage::url($filePath),
                            'size' => $size,
                            'extension' => $extension,
                            'uploaded_at' => now()
                        ];
                        
                        Log::info('Файл успешно загружен', [
                            'original_name' => $originalName,
                            'path' => $filePath,
                            'size' => $size
                        ]);
                    } else {
                        $errors[] = "Не удалось сохранить файл: {$originalName}";
                    }
                } catch (Exception $e) {
                    $errors[] = "Ошибка при загрузке файла {$originalName}: " . $e->getMessage();
                    Log::error('Ошибка загрузки файла', [
                        'file' => $originalName,
                        'error' => $e->getMessage()
                    ]);
                }
            }

            // Формируем ответ
            if (count($uploadedFiles) > 0) {
                return response()->json([
                    'success' => true,
                    'message' => 'Файлы успешно загружены: ' . count($uploadedFiles) . ' из ' . count($request->file('documents')),
                    'uploaded_files' => $uploadedFiles,
                    'errors' => $errors
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Не удалось загрузить ни одного файла',
                    'errors' => $errors
                ], 400);
            }

        } catch (Exception $e) {
            Log::error('Общая ошибка при загрузке документов: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Произошла ошибка при загрузке документов: ' . $e->getMessage()
            ], 500);
        }
    }
}
