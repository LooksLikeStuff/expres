<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Тест системы документов</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f5f5f5;
        }
        .test-section {
            background: white;
            padding: 20px;
            margin-bottom: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .test-url {
            background: #f8f9fa;
            padding: 10px;
            border-radius: 4px;
            font-family: monospace;
            word-break: break-all;
            margin: 10px 0;
        }
        .test-link {
            display: inline-block;
            background: #007bff;
            color: white;
            padding: 8px 16px;
            text-decoration: none;
            border-radius: 4px;
            margin-right: 10px;
            margin-bottom: 10px;
        }
        .test-link:hover {
            background: #0056b3;
        }
        .error {
            color: #dc3545;
            background: #f8d7da;
            padding: 10px;
            border-radius: 4px;
            margin: 10px 0;
        }
        .success {
            color: #155724;
            background: #d4edda;
            padding: 10px;
            border-radius: 4px;
            margin: 10px 0;
        }
    </style>
</head>
<body>
    <h1>Тест системы документов сделок</h1>
    
    <div class="test-section">
        <h2>Проблемный URL</h2>
        <div class="test-url">{{ $problemUrl ?? 'https://lk.express-diz.ru/storage/deals/215/documents/ChatGPT_Image_18_______._2025___.__10_54_26_1753103484_687e3c7ca0b08.png' }}</div>
        
        <h3>Диагностика:</h3>
        @php
            $testUrl = $problemUrl ?? 'https://lk.express-diz.ru/storage/deals/215/documents/ChatGPT_Image_18_______._2025___.__10_54_26_1753103484_687e3c7ca0b08.png';
            $dealId = 215;
            $filename = 'ChatGPT_Image_18_______._2025___.__10_54_26_1753103484_687e3c7ca0b08.png';
            
            // Проверяем существование сделки
            $deal = \App\Models\Deal::find($dealId);
            $dealExists = $deal !== null;
            
            // Проверяем существование файла в storage
            $storagePathFromUrl = str_replace('https://lk.express-diz.ru/storage/', '', $testUrl);
            $fileExistsInStorage = \Illuminate\Support\Facades\Storage::exists($storagePathFromUrl);
            
            // Проверяем альтернативный путь
            $alternativePath = "deals/{$dealId}/documents/{$filename}";
            $fileExistsAlternative = \Illuminate\Support\Facades\Storage::exists($alternativePath);
            
            // Проверяем публичную папку
            $publicPath = public_path('storage/' . $storagePathFromUrl);
            $fileExistsInPublic = file_exists($publicPath);
        @endphp
        
        <ul>
            <li>Сделка #{{ $dealId }} {{ $dealExists ? '✓ существует' : '✗ не найдена' }}</li>
            <li>Файл в storage ({{ $storagePathFromUrl }}): {{ $fileExistsInStorage ? '✓ найден' : '✗ не найден' }}</li>
            <li>Файл по альтернативному пути ({{ $alternativePath }}): {{ $fileExistsAlternative ? '✓ найден' : '✗ не найден' }}</li>
            <li>Файл в public/storage ({{ $publicPath }}): {{ $fileExistsInPublic ? '✓ найден' : '✗ не найден' }}</li>
        </ul>
        
        @if($dealExists)
            <h3>Информация о сделке:</h3>
            <ul>
                <li>ID: {{ $deal->id }}</li>
                <li>Название: {{ $deal->title ?? 'Не указано' }}</li>
                <li>Клиент: {{ $deal->client_name ?? 'Не указан' }}</li>
            </ul>
            
            <h3>Файловые поля сделки:</h3>
            @php
                $fileFields = [
                    'execution_order_file', 'measurements_file', 'final_floorplan', 'final_collage',
                    'final_project_file', 'work_act', 'archicad_file', 'contract_attachment', 
                    'plan_final', 'chat_screenshot'
                ];
            @endphp
            <ul>
                @foreach($fileFields as $field)
                    @php
                        $yandexUrlField = "yandex_url_{$field}";
                        $originalNameField = "original_name_{$field}";
                        $hasUrl = isset($deal->$yandexUrlField) && !empty($deal->$yandexUrlField);
                        $hasName = isset($deal->$originalNameField) && !empty($deal->$originalNameField);
                    @endphp
                    @if($hasUrl || $hasName)
                        <li>
                            <strong>{{ $field }}:</strong><br>
                            URL: {{ $deal->$yandexUrlField ?? 'Не указан' }}<br>
                            Имя: {{ $deal->$originalNameField ?? 'Не указано' }}
                        </li>
                    @endif
                @endforeach
            </ul>
        @endif
    </div>

    <div class="test-section">
        <h2>Тестовые ссылки</h2>
        
        @if($dealExists)
            <a href="{{ route('deal.document.download', ['deal' => $dealId, 'filename' => $filename]) }}" 
               class="test-link" target="_blank">
                Скачать через новый маршрут
            </a>
            
            <a href="{{ route('deal.document.view', ['deal' => $dealId, 'filename' => $filename]) }}" 
               class="test-link" target="_blank">
                Просмотр через новый маршрут
            </a>
        @endif
        
        <a href="{{ $testUrl }}" class="test-link" target="_blank">
            Оригинальная ссылка (должна давать 404)
        </a>
    </div>

    <div class="test-section">
        <h2>Рекомендации по исправлению</h2>
        
        @if(!$dealExists)
            <div class="error">
                Сделка #{{ $dealId }} не найдена. Проверьте правильность ID сделки.
            </div>
        @endif
        
        @if(!$fileExistsInStorage && !$fileExistsAlternative && !$fileExistsInPublic)
            <div class="error">
                Файл не найден ни в одном из ожидаемых мест. Возможно, файл был удален или перемещен.
            </div>
        @endif
        
        @if($fileExistsInPublic && !$fileExistsInStorage)
            <div class="success">
                Файл найден в public/storage. Создайте симлинк: <code>php artisan storage:link</code>
            </div>
        @endif
        
        @if($dealExists && (!$fileExistsInStorage && !$fileExistsAlternative))
            <div class="error">
                Необходимо загрузить файл заново или исправить путь в базе данных.
            </div>
        @endif
    </div>

    <script>
        // Тест AJAX запросов
        document.addEventListener('DOMContentLoaded', function() {
            console.log('Тестовая страница загружена');
            console.log('CSRF Token:', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));
        });
    </script>
</body>
</html>
