<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware для обработки больших файлов
 */
class LargeFileUploadMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Увеличиваем лимиты для больших файлов
        if ($request->is('api/yandex-disk/upload') || $request->hasFile('file')) {
            
            // Увеличиваем время выполнения
            set_time_limit(0); // Убираем ограничение времени выполнения
            ini_set('max_execution_time', 0);
            
            // Увеличиваем лимит памяти
            ini_set('memory_limit', '1G');
            
            // Настройки для больших файлов
            ini_set('upload_max_filesize', '2G');
            ini_set('post_max_size', '2G');
            ini_set('max_input_time', 0);
            
            // Настройки буферизации
            ini_set('output_buffering', 'Off');
            ini_set('zlib.output_compression', 'Off');
            
            // Отключаем прерывание при отключении пользователя
            ignore_user_abort(true);
            
            // Логируем начало обработки большого файла
            if ($request->hasFile('file')) {
                $file = $request->file('file');
                Log::info('🚀 Middleware: Начало обработки большого файла', [
                    'file_name' => $file->getClientOriginalName(),
                    'file_size' => $file->getSize(),
                    'memory_limit' => ini_get('memory_limit'),
                    'max_execution_time' => ini_get('max_execution_time'),
                    'upload_max_filesize' => ini_get('upload_max_filesize'),
                    'post_max_size' => ini_get('post_max_size')
                ]);
            }
        }
        
        return $next($request);
    }
}
