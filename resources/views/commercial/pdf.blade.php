<!DOCTYPE html>
<html lang="ru">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Коммерческий бриф #{{ $brief->id }}</title>
    <style>
        @font-face {
            font-family: 'DejaVu Sans';
            src: url('{{ storage_path('fonts/DejaVuSans.ttf') }}') format('truetype');
            font-weight: normal;
            font-style: normal;
        }
        
        body {
            font-family: 'DejaVu Sans', sans-serif;
            line-height: 1.6;
            color: #333;
        }
        
        h1 {
            color: #2c3e50;
            border-bottom: 1px solid #eee;
            padding-bottom: 10px;
        }
        
        h2 {
            color: #3498db;
            margin-top: 30px;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        
        table, th, td {
            border: 1px solid #ddd;
        }
        
        th, td {
            padding: 12px;
            text-align: left;
        }
        
        th {
            background-color: #f2f2f2;
        }
        
        .logo {
            text-align: center;
            margin-bottom: 20px;
        }
        
        .logo img {
            max-width: 150px;
            height: auto;
        }
        
        .header-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="logo">
        <img src="{{ public_path('storage/icon/fool_logo.svg') }}" alt="Логотип">
    </div>
    
    <h1>Коммерческий бриф #{{ $brief->id }}</h1>
    
    <table>
        <thead>
            <tr>
                <th>Поле</th>
                <th>Значение</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>ID</td>
                <td>{{ $brief->id }}</td>
            </tr>
            <tr>
                <td>Название</td>
                <td>{{ $brief->title ?? 'Не указано' }}</td>
            </tr>
            <tr>
                <td>Описание</td>
                <td>{{ $brief->description ?? 'Не указано' }}</td>
            </tr>
            <tr>
                <td>Статус</td>
                <td>{{ $brief->status->value ?? 'Не указан' }}</td>
            </tr>
            <tr>
                <td>Имя пользователя</td>
                <td>{{ $user->name ?? 'Не указан' }}</td>
            </tr>
            <tr>
                <td>Номер клиента</td>
                <td>{{ $user->phone ?? 'Не указан' }}</td>
            </tr>
            <tr>
                <td>Дата создания</td>
                <td>{{ $brief->created_at->format('d.m.Y H:i') }}</td>
            </tr>
            <tr>
                <td>Дата обновления</td>
                <td>{{ $brief->updated_at->format('d.m.Y H:i') }}</td>
            </tr>
        </tbody>
    </table>

    <h2>Общий бюджет: {{ number_format($brief->price ?? 0, 0, ',', ' ') }} ₽</h2>

    @if(isset($zones) && is_array($zones) && count($zones) > 0)
        <h2>Зоны</h2>
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Название</th>
                    <th>Описание</th>
                    <th>Общая площадь</th>
                    <th>Проектируемая площадь</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($zones as $index => $zone)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $zone['name'] ?? 'Без названия' }}</td>
                        <td>{{ $zone['description'] ?? '' }}</td>
                        <td>{{ isset($zone['total_area']) ? $zone['total_area'] . ' м²' : 'Не указана' }}</td>
                        <td>{{ isset($zone['projected_area']) ? $zone['projected_area'] . ' м²' : 'Не указана' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    {{-- Вопросы и ответы по страницам --}}
    @if(isset($questions) && is_array($questions))
        @foreach($questions as $page => $pageQuestions)
            <h2>Страница {{ $page }}</h2>
            
            <table>
                <thead>
                    <tr>
                        <th>Вопрос</th>
                        <th>Ответ</th>
                    </tr>
                </thead>
                <tbody>
                    @if(is_array($pageQuestions) && count($pageQuestions) > 0)
                        @foreach($pageQuestions as $question)
                            @php
                                $hasAnswer = false;
                                if (isset($question['answer'])) {
                                    if (is_array($question['answer'])) {
                                        $hasAnswer = !empty(array_filter($question['answer']));
                                    } else {
                                        $hasAnswer = !empty(trim((string) $question['answer']));
                                    }
                                }
                            @endphp
                            @if($hasAnswer)
                                <tr>
                                    <td>{{ $question['title'] ?? 'Без названия' }}</td>
                                    <td>
                                        @php
                                            $answer = $question['answer'] ?? '';
                                            if (is_array($answer)) {
                                                $displayAnswer = implode(', ', $answer);
                                            } elseif (is_string($answer)) {
                                                $displayAnswer = $answer;
                                            } else {
                                                $displayAnswer = (string) $answer;
                                            }
                                        @endphp
                                        {{ $displayAnswer }}
                                    </td>
                                </tr>
                            @endif
                        @endforeach
                    @else
                        <tr>
                            <td colspan="2">Нет данных для этого раздела</td>
                        </tr>
                    @endif
                </tbody>
            </table>
        @endforeach
    @endif

    {{-- Ответы по зонам --}}
    @if(isset($zoneAnswers) && $zoneAnswers->count() > 0)
        <h2>Ответы по зонам</h2>
        @foreach($zoneAnswers as $zoneId => $zoneAnswerGroup)
            @php
                $zone = collect($zones)->firstWhere('id', $zoneId);
                $zoneTitle = $zone['name'] ?? "Зона ID: {$zoneId}";
            @endphp
            
            <h3>{{ $zoneTitle }}</h3>
            <table>
                <thead>
                    <tr>
                        <th>Вопрос</th>
                        <th>Ответ</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($zoneAnswerGroup as $answer)
                        @if($answer->question && !empty($answer->answer_text))
                            <tr>
                                <td>{{ $answer->question->title ?? 'Без названия' }}</td>
                                <td>{{ $answer->answer_text }}</td>
                            </tr>
                        @endif
                    @endforeach
                </tbody>
            </table>
        @endforeach
    @endif

    {{-- Показываем прикрепленные документы --}}
    @if($brief->documents && $brief->documents->count() > 0)
        <h2>Прикрепленные документы</h2>
        <table>
            <thead>
                <tr>
                    <th>Название файла</th>
                    <th>Ссылка</th>
                </tr>
            </thead>
            <tbody>
                @foreach($brief->documents as $document)
                    <tr>
                        <td>{{ $document->original_name ?? basename($document->file_path) }}</td>
                        <td>{{ $document->getFullUrlAttribute() ?? $document->file_path }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    <div style="margin-top: 30px; font-size: 12px; color: #777; text-align: center;">
        <p>Дата создания: {{ $brief->created_at->format('d.m.Y H:i') }}</p>
        <p>Дата обновления: {{ $brief->updated_at->format('d.m.Y H:i') }}</p>
    </div>
</body>
</html>