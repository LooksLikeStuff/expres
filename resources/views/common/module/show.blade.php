<div class="flex-h1">
    <h1>Детали общего брифа</h1>
    <div class="button-group">
        <button onclick="window.open('{{ route('common.download.pdf', $brif->id) }}')" class="btn btn-primary">
            Скачать PDF
        </button>
        
    
        
        @php
            $canViewHistory = auth()->user()->status == 'admin' || auth()->user()->status == 'coordinator' || auth()->user()->status == 'partner';
        @endphp
      
        @if($canViewHistory)
            <a href="{{ route('common.history', $brif->id) }}" class="btn btn-info">
                История изменений
            </a>
        @endif
    </div>
</div>

<div class="brief-details">
  
    <div class="card mb-4">
        <div class="card-header">
            <h2>Основная информация</h2>
        </div>
        
        @php
            $hasHistory = \App\Models\CommonBriefHistory::where('common_id', $brif->id)->exists();
        @endphp
        
        @if($hasHistory && $canViewHistory)
            <div class="edit-history-notification">
                <div class="alert alert-info">
                    <i class="fas fa-history"></i> Этот бриф редактировался. 
                    <a href="{{ route('common.history', $brif->id) }}" class="alert-link">Посмотреть историю изменений</a>
                </div>
            </div>
        @endif
        
        <div class="card-body">
            <table class="table table-striped">
                <tbody>
                    <tr>
                        <th>ID</th>
                        <td>{{ $brif->id }}</td>
                    </tr>
                    <tr>
                        <th>Название</th>
                        <td>{{ $brif->title }}</td>
                    </tr>
                    <tr>
                        <th>Описание</th>
                        <td>{{ $brif->description }}</td>
                    </tr>
                    <tr>
                        <th>Статус</th>
                        <td><span class="badge {{ in_array($brif->status, ['Завершенный', 'Отредактированный']) ? 'bg-success' : 'bg-warning' }}">{{ $brif->status }}</span></td>
                    </tr>
                    <tr>
                        <th>Бюджет</th>
                        <td>{{ number_format($brif->price, 0, '.', ' ') }} ₽</td>
                    </tr>
                    <tr>
                        <th>Пользователь</th>
                        <td>{{ $user->name ?? 'Неизвестно' }}</td>
                    </tr>
                     <tr>
            <td>Номер клиента</td>
            <td>{{ $user->phone }}</td>
        </tr>
                    <tr>
                        <th>Дата создания</th>
                        <td>{{ $brif->created_at->format('d.m.Y H:i') }}</td>
                    </tr>
                    <tr>
                        <th>Дата обновления</th>
                        <td>{{ $brif->updated_at->format('d.m.Y H:i') }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Выбранные помещения -->
    <div class="card mb-4">
        <div class="card-header">
            <h2>Выбранные помещения</h2>
        </div>
        <div class="card-body">
            @if((isset($brif->rooms) && !empty($brif->rooms)) || (isset($brif->custom_rooms) && !empty($brif->custom_rooms)))
                <div class="selected-rooms">
                    @if(isset($brif->rooms) && !empty($brif->rooms))
                        @foreach(json_decode($brif->rooms, true) as $roomKey => $roomName)
                            <div class="room-badge">{{ $roomName }}</div>
                        @endforeach
                    @endif
                    
                    @if(isset($brif->custom_rooms) && !empty($brif->custom_rooms))
                        @foreach(json_decode($brif->custom_rooms, true) as $customRoom)
                            <div class="room-badge custom-room-badge">{{ $customRoom }}</div>
                        @endforeach
                    @endif
                </div>
            @else
                <p class="text-muted">Нет выбранных помещений</p>
            @endif
        </div>
    </div>

    <!-- Ответы на вопросы -->
    @for($pageNum = 1; $pageNum <= 5; $pageNum++)
        <div class="card mb-4">
            <div class="card-header">
                <h2>
                    @switch($pageNum)
                        @case(1)
                            Общая информация
                            @break
                        @case(2)
                            Интерьер: стиль и предпочтения
                            @break
                        @case(3)
                            Пожелания по помещениям
                            @break
                        @case(4)
                            Пожелания по отделке помещений
                            @break
                        @case(5)
                            Пожелания по оснащению помещений
                            @break
                        @default
                            Раздел {{ $pageNum }}
                    @endswitch
                </h2>
            </div>
            <div class="card-body">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th width="40%">Вопрос</th>
                            <th>Ответ</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $hasAnswers = false;
                            $currentPageQuestions = $questions[$pageNum] ?? [];
                            
                            // Для страницы 3 (Пожелания по помещениям) добавляем пользовательские комнаты
                            if ($pageNum == 3 && isset($brif->custom_rooms) && !empty($brif->custom_rooms)) {
                                $customRooms = json_decode($brif->custom_rooms, true);
                                foreach ($customRooms as $index => $roomName) {
                                    $customKey = 'custom_room_' . $index;
                                    $currentPageQuestions[] = [
                                        'key' => $customKey,
                                        'title' => $roomName,
                                        'subtitle' => 'Пожелания по наполнению и дизайну',
                                        'format' => 'faq'
                                    ];
                                }
                            }
                        @endphp

                        @foreach($currentPageQuestions as $question)
                            @php
                                $key = $question['key'];
                                $answer = $brif->$key ?? null;
                                
                                // Для пользовательских комнат ищем ответы по ключу
                                if (strpos($key, 'custom_room_') === 0 && $pageNum == 3) {
                                    $index = str_replace('custom_room_', '', $key);
                                    $customAnswersKey = 'custom_room_answers';
                                    $customAnswers = json_decode($brif->$customAnswersKey ?? '{}', true);
                                    $answer = $customAnswers[$index] ?? null;
                                }
                            @endphp

                            @if($answer)
                                @php $hasAnswers = true; @endphp
                                <tr>
                                    <td><strong>{{ $question['title'] }}</strong>
                                        @if(isset($question['subtitle']))
                                            <div class="text-muted small">{{ $question['subtitle'] }}</div>
                                        @endif
                                    </td>
                                    <td>{{ $answer }}</td>
                                </tr>
                            @endif
                        @endforeach

                        @if(!$hasAnswers)
                            <tr>
                                <td colspan="2" class="text-center">Нет ответов в этом разделе</td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
    @endfor

    <!-- Загруженные документы (если есть) -->
    @if(isset($brif->documents) && !empty($brif->documents))
        <div class="card mb-4">
            <div class="card-header">
                <h2>Документы</h2>
            </div>
            <div class="card-body">
                <div class="documents-grid">
                    @foreach(json_decode($brif->documents, true) ?? [] as $document)
                        <div class="document-item">
                            <div class="document-icon">
                                @php
                                    $extension = pathinfo(basename($document), PATHINFO_EXTENSION);
                                    $iconClass = 'fa-file';
                                    
                                    switch(strtolower($extension)) {
                                        case 'pdf': $iconClass = 'fa-file-pdf'; break;
                                        case 'doc': case 'docx': $iconClass = 'fa-file-word'; break;
                                        case 'xls': case 'xlsx': $iconClass = 'fa-file-excel'; break;
                                        case 'jpg': case 'jpeg': case 'png': case 'gif': case 'heic': 
                                            $iconClass = 'fa-file-image'; break;
                                        case 'mp4': case 'mov': case 'avi': case 'wmv': 
                                            $iconClass = 'fa-file-video'; break;
                                    }
                                @endphp
                                <i class="fas {{ $iconClass }}"></i>
                            </div>
                            <div class="document-info">
                                <a href="{{ $document }}" target="_blank" class="document-link">
                                    {{ basename($document) }}
                                </a>
                                <span class="document-type">{{ strtoupper($extension) }}</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif

    <!-- Референсы (если есть) -->
    @if(isset($brif->references) && !empty($brif->references))
        <div class="card mb-4">
            <div class="card-header">
                <h2>Референсы</h2>
            </div>
            <div class="card-body">
                <div class="references-grid">
                    @foreach(json_decode($brif->references, true) ?? [] as $reference)
                        <div class="reference-item">
                            @php
                                $extension = pathinfo(basename($reference), PATHINFO_EXTENSION);
                                $isImage = in_array(strtolower($extension), ['jpg', 'jpeg', 'png', 'gif', 'webp', 'heic']);
                                
                                // Определение класса иконки на основе расширения файла
                                $iconClass = 'fa-file'; // Иконка по умолчанию
                                $extension = strtolower($extension);
                                
                                if (in_array($extension, ['pdf'])) {
                                    $iconClass = 'fa-file-pdf';
                                } elseif (in_array($extension, ['doc', 'docx'])) {
                                    $iconClass = 'fa-file-word';
                                } elseif (in_array($extension, ['xls', 'xlsx'])) {
                                    $iconClass = 'fa-file-excel';
                                } elseif (in_array($extension, ['ppt', 'pptx'])) {
                                    $iconClass = 'fa-file-powerpoint';
                                } elseif (in_array($extension, ['zip', 'rar', '7z', 'tar', 'gz'])) {
                                    $iconClass = 'fa-file-archive';
                                } elseif (in_array($extension, ['txt', 'rtf'])) {
                                    $iconClass = 'fa-file-alt';
                                } elseif (in_array($extension, ['mp3', 'wav', 'ogg'])) {
                                    $iconClass = 'fa-file-audio';
                                } elseif (in_array($extension, ['mp4', 'avi', 'mov', 'wmv'])) {
                                    $iconClass = 'fa-file-video';
                                } elseif (in_array($extension, ['dwg', 'pln'])) {
                                    $iconClass = 'fa-drafting-compass';
                                } elseif (in_array($extension, ['html', 'css', 'js', 'php'])) {
                                    $iconClass = 'fa-file-code';
                                }
                            @endphp

                            @if($isImage)
                                <a href="{{ $reference }}" target="_blank" class="reference-image-link">
                                    <img src="{{ $reference }}" alt="Референс" class="reference-image">
                                </a>
                            @else
                                <a href="{{ $reference }}" target="_blank" class="reference-file-link">
                                    <div class="document-icon">
                                        <i class="fas {{ $iconClass }}"></i>
                                    </div>
                                    <div class="document-info">
                                        {{ basename($reference) }}
                                        <span class="document-type">{{ strtoupper($extension) }}</span>
                                    </div>
                                </a>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif
        @if(in_array($brif->status, ['Завершенный', 'Отредактированный']) && $brif->user_id === auth()->id() && !$brif->edit_status && auth()->user()->status !== 'user')
            <a href="{{ route('common.startEdit', $brif->id) }}" class="btn btn-warning">
                Редактировать бриф
            </a>
        @endif
</div>

<style>
    .flex-h1 {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
    }
    
    .button-group {
        display: flex;
        gap: 10px;
    }
    
    .brief-details .card {
        border-radius: 8px;
        overflow: hidden;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        margin-bottom: 25px;
    }
    
    .brief-details .card-header {
        background-color: #f7f7f7;
        padding: 15px 20px;
        border-bottom: 1px solid #ddd;
    }
    
    .brief-details .card-header h2 {
        margin: 0;
        font-size: 1.4rem;
        color: #333;
    }
    
    .selected-rooms {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
    }
    
    .room-badge {
        background-color: #e9f5fe;
        color: #0085ff;
        border-radius: 20px;
        padding: 6px 12px;
        font-size: 14px;
        font-weight: 500;
    }
    
    .documents-grid, .references-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
        gap: 15px;
    }
    
    .document-item, .reference-item {
        display: flex;
        align-items: center;
        padding: 10px;
        background-color: #f8f9fa;
        border-radius: 8px;
        transition: all 0.2s ease;
    }
    
    .document-item:hover, .reference-item:hover {
        background-color: #e9f5fe;
        transform: translateY(-2px);
    }
    
    .document-icon {
        width: 40px;
        height: 40px;
        background-color: #e3e6e9;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: 12px;
    }
    
    .document-icon i {
        font-size: 20px;
        color: #617084;
    }
    
    .document-info {
        flex: 1;
        overflow: hidden;
    }
    
    .document-link {
        display: block;
        font-weight: 500;
        color: #333;
        text-decoration: none;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    
    .document-type {
        display: block;
        font-size: 12px;
        color: #999;
    }
    
    .reference-image {
        width: 100%;
        height: 150px;
        object-fit: cover;
        border-radius: 8px;
    }
    
    .reference-image-link {
        display: block;
        width: 100%;
    }
    
    .reference-file-link {
        display: flex;
        align-items: center;
        color: inherit;
        text-decoration: none;
        padding: 10px;
    }
    
    .custom-room-badge {
        background-color: #fff0dc;
        color: #ff6a00;
        border: 1px solid #ff6a00;
    }
    
    .edit-history-notification {
        margin: 15px;
        padding: 10px;
    }
    
    .alert i {
        margin-right: 5px;
    }
</style>