<div class="flex-h1">
    <h1>Детали брифа</h1>
    <button onclick="window.open('{{ route('commercial.download.pdf', $brif->id) }}')" class="btn btn-primary" style=" ">
        Скачать PDF
    </button>
</div>

<table class="table table-bordered">
    <thead>
        <tr>
            <th>Поле</th>
            <th>Значение</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>ID</td>
            <td>{{ $brif->id }}</td>
        </tr>
        <tr>
            <td>Название</td>
            <td>{{ $brif->title }}</td>
        </tr>
        <tr>
            <td>Описание</td>
            <td>{{ $brif->description }}</td>
        </tr>
        <tr>
            <td>Статус</td>
            <td>{{ $brif->status }}</td>
        </tr>
        <tr>
            <td>Имя пользователя</td>
            <td>{{ $user->name }}</td>
        </tr>
         <tr>
            <td>Номер клиента:</td>
            <td>{{ $user->phone }}</td>
        </tr>
        <tr>
            <td>Дата создания</td>
            <td>{{ $brif->created_at }}</td>
        </tr>
        <tr>
            <td>Дата обновления</td>
            <td>{{ $brif->updated_at }}</td>
        </tr>
    </tbody>
</table>

<!-- Отображаем общий бюджет -->
<h2><strong>Общий бюджет:</strong> {{ number_format($brif->price, 2, ',', ' ') }} ₽</h2>

<!-- Если бюджет разбивается по зонам -->
@if($zones)
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>#</th>
                <th>Название зоны</th>
                <th>Бюджет</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($zones as $index => $zone)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $zone['name'] ?? 'Без названия' }}</td>
                    <td>
                        @if (isset($price[$index]))
                            {{ number_format($price[$index], 2, ',', ' ') }} ₽ <!-- Форматируем цену для каждой зоны -->
                        @else
                            0 ₽
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endif

<h2>Зоны</h2>
<table class="table table-bordered">
    <thead>
        <tr>
            <th>#</th>
            <th>Название</th>
            <th>Описание</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($zones as $index => $zone)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $zone['name'] ?? 'Без названия' }}</td>
                <td>{{ $zone['description'] ?? '' }}</td>
            </tr>
        @endforeach
    </tbody>
</table>

<!-- Добавляем секцию для отображения документов -->
@if(isset($brif->documents) && is_array(json_decode($brif->documents, true)) && count(json_decode($brif->documents, true)) > 0)
    <h2>Загруженные документы</h2>
    <div class="documents-container">
        <ul class="documents-list">
            @foreach(json_decode($brif->documents, true) as $document)
                <li class="document-item">
                    <a href="{{ $document }}" target="_blank" class="document-link">
                        <div class="document-icon">
                            @php
                                $extension = pathinfo(basename($document), PATHINFO_EXTENSION);
                                $iconClass = 'fa-file';
                                
                                // Определяем иконку в зависимости от расширения файла
                                switch(strtolower($extension)) {
                                    case 'pdf': $iconClass = 'fa-file-pdf'; break;
                                    case 'doc': case 'docx': $iconClass = 'fa-file-word'; break;
                                    case 'xls': case 'xlsx': $iconClass = 'fa-file-excel'; break;
                                    case 'jpg': case 'jpeg': case 'png': case 'gif': case 'heic': case 'heif': 
                                        $iconClass = 'fa-file-image'; break;
                                    case 'mp4': case 'mov': case 'avi': case 'wmv': case 'flv': case 'mkv': case 'webm': case '3gp': 
                                        $iconClass = 'fa-file-video'; break;
                                }
                            @endphp
                            <i class="fas {{ $iconClass }}"></i>
                        </div>
                        <div class="document-info">
                            <span class="document-name">{{ basename($document) }}</span>
                            <span class="document-type">{{ strtoupper($extension) }}</span>
                        </div>
                    </a>
                </li>
            @endforeach
        </ul>
    </div>
@endif

<!-- Добавляем секцию для вывода предпочтений по зонам -->
<h2>Предпочтения для зон</h2>
@php
    // Обновление массива с правильными названиями страниц в соответствии с контроллером
    $pageNames = [
        'question_1' => "Название зоны",
        'question_2' => "Метраж зон",
        'question_3' => "Стиль оформления и меблировка зон",
        'question_4' => "Отделочные материалы и поверхности",
        'question_5' => "Инженерные системы и коммуникации",
        'question_6' => "Предпочтения и ограничения",
        'question_7' => "Бюджет и документы",
        'question_8' => "Дополнительные пожелания"
    ];
    
    // Проверяем существование функции перед объявлением
    if (!function_exists('extractQuestionNumberShow')) {
        function extractQuestionNumberShow($questionText) {
            if (preg_match('/Вопрос (\d+)/', $questionText, $matches)) {
                return 'question_' . $matches[1];
            }
            return $questionText;
        }
    }
@endphp

@if(isset($preferencesFormatted) && count($preferencesFormatted) > 0)
    @foreach($preferencesFormatted as $zoneName => $zonePreferences)
        <div class="card mb-4">
            <div class="card-header">
                <h3>{{ $zoneName }}</h3>
            </div>
            <div class="card-body">
                @if(count($zonePreferences) > 0)
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Вопрос</th>
                                <th>Ответ</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($zonePreferences as $preference)
                                @php
                                    // Используем переименованную функцию
                                    $questionKey = extractQuestionNumberShow($preference['question']);
                                    $displayName = $pageNames[$questionKey] ?? $preference['question'];
                                @endphp
                                <tr>
                                    <td><strong>{{ $displayName }}</strong></td>
                                    <td>{{ $preference['answer'] }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <p class="text-muted">Нет предпочтений для этой зоны</p>
                @endif
            </div>
        </div>
    @endforeach
@else
    <div class="alert alert-info">
        Нет доступных предпочтений для зон
    </div>
@endif

<style>
    /* Стили для отображения документов */
    .documents-container {
        margin: 20px 0;
        padding: 15px;
        background-color: #f9f9f9;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
    }
    
    .documents-list {
        list-style: none;
        padding: 0;
        margin: 0;
        display: flex;
        flex-wrap: wrap;
        gap: 15px;
    }
    
    .document-item {
        width: calc(33.333% - 15px);
        min-width: 250px;
        background-color: white;
        border-radius: 6px;
        overflow: hidden;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    
    .document-item:hover {
        transform: translateY(-3px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    }
    
    .document-link {
        display: flex;
        align-items: center;
        padding: 12px;
        color: #333;
        text-decoration: none;
    }
    
    .document-icon {
        width: 40px;
        height: 40px;
        border-radius: 4px;
        background-color: #f0f4f8;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: 12px;
        font-size: 20px;
    }
    
    .document-icon i.fa-file-pdf { color: #e74c3c; }
    .document-icon i.fa-file-word { color: #3498db; }
    .document-icon i.fa-file-excel { color: #2ecc71; }
    .document-icon i.fa-file-image { color: #9b59b6; }
    .document-icon i.fa-file-video { color: #e67e22; }
    .document-icon i.fa-file { color: #7f8c8d; }
    
    .document-info {
        flex: 1;
        overflow: hidden;
    }
    
    .document-name {
        display: block;
        font-size: 14px;
        font-weight: 500;
        margin-bottom: 2px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }
    
    .document-type {
        display: block;
        font-size: 12px;
        color: #777;
        text-transform: uppercase;
    }
    
    /* Медиа-запросы для адаптивности */
    @media (max-width: 992px) {
        .document-item {
            width: calc(50% - 15px);
        }
    }
    
    @media (max-width: 576px) {
        .document-item {
            width: 100%;
        }
    }
    
    /* Улучшение стилей для таблицы */
    .table {
        margin-bottom: 30px;
        border-collapse: collapse;
    }
    
    .table thead th {
        background-color: #f5f5f5;
        font-weight: 600;
    }
    
    .table tbody tr:hover {
        background-color: #f9f9f9;
    }
    
    .flex-h1 {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
    }
    
    .flex-h1 h1 {
        margin: 0;
    }
    
    .flex-h1 .btn {
        margin-left: 15px;
    }
</style>

