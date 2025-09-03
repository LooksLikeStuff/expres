<!DOCTYPE html>
<html lang="ru">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Общий бриф #{{ $brief->id }}</title>
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
        
        .section-title {
            margin-top: 30px;
            font-size: 18px;
            color: #3498db;
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
    
    <h1>Общий бриф #{{ $brief->id }}</h1>
    
    <table>
        <tr>
            <td><strong>Название:</strong></td>
            <td>{{ $brief->title ?? 'Не указано' }}</td>
        </tr>
        <tr>
            <td><strong>Артикль:</strong></td>
            <td>{{ $brief->article ?? 'Не указан' }}</td>
        </tr>
        <tr>
            <td><strong>Описание:</strong></td>
            <td>{{ $brief->description ?? 'Не указано' }}</td>
        </tr>
        <tr>
            <td><strong>Общая сумма:</strong></td>
            <td>{{ number_format($brief->price ?? 0, 0, ',', ' ') }} руб</td>
        </tr>
        <tr>
            <td><strong>Статус:</strong></td>
            <td>{{ $brief->status->value ?? 'Не указан' }}</td>
        </tr>
        <tr>
            <td><strong>Создатель брифа:</strong></td>
            <td>{{ $user->name ?? 'Не указан' }}</td>
        </tr>
        <tr>
            <td><strong>Номер клиента:</strong></td>
            <td>{{ $user->phone ?? 'Не указан' }}</td>
        </tr>
    </table>

    {{-- Выбранные комнаты --}}
    @if(isset($rooms) && $rooms->count() > 0)
        <h3 class="section-title">Выбранные помещения</h3>
        <table>
            <thead>
                <tr>
                    <th>Помещения</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>
                        @foreach($rooms as $index => $room)
                            {{ $room->title }}@if(!$loop->last), @endif
                        @endforeach
                    </td>
                </tr>
            </tbody>
        </table>
    @endif
    
    {{-- Вопросы и ответы по страницам --}}
    @if(isset($questions) && is_array($questions))
        @foreach($questions as $page => $pageQuestions)
            @if(isset($pageTitles[$page - 1]))
                <h3 class="section-title">{{ $pageTitles[$page - 1] }}</h3>
            @else
                <h3 class="section-title">Страница {{ $page }}</h3>
            @endif
            
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

    {{-- Ответы по комнатам --}}
    @if(isset($roomAnswers) && $roomAnswers->count() > 0)
        <h3 class="section-title">Ответы по помещениям</h3>
        @foreach($roomAnswers as $roomId => $roomAnswerGroup)
            @php
                $room = $rooms->firstWhere('id', $roomId);
                $roomTitle = $room ? $room->title : "Комната ID: {$roomId}";
            @endphp
            
            <h4>{{ $roomTitle }}</h4>
            <table>
                <thead>
                    <tr>
                        <th>Вопрос</th>
                        <th>Ответ</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($roomAnswerGroup as $answer)
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
        <h3 class="section-title">Прикрепленные документы</h3>
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