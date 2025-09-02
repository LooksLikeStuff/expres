<div class="flex-h1">
    <h1>Детали {{ $brief->isCommon() ? 'общего' : 'коммерческого' }} брифа</h1>
    <div class="button-group">
        <button 
            data-action="download-pdf" 
            data-url="{{ route('briefs.pdf', $brief) }}"
            data-brief-id="{{ $brief->id }}"
            data-brief-type="{{ $brief->type->value }}"
            class="btn btn-primary"
        >
            Скачать PDF
        </button>
    </div>
</div>

<div class="brief-details">
    <!-- Основная информация -->
    <div class="card mb-4">
        <div class="card-header">
            <h2>Основная информация</h2>
        </div>
        <div class="card-body">
            <table class="table table-striped">
                <tbody>
                    <tr>
                        <th>ID</th>
                        <td>{{ $brief->id }}</td>
                    </tr>
                    <tr>
                        <th>Тип</th>
                        <td>
                            <span class="badge {{ $brief->isCommon() ? 'bg-primary' : 'bg-success' }}">
                                {{ $brief->isCommon() ? 'Общий' : 'Коммерческий' }}
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <th>Название</th>
                        <td>{{ $brief->title }}</td>
                    </tr>
                    <tr>
                        <th>Описание</th>
                        <td>{{ $brief->description }}</td>
                    </tr>
                    <tr>
                        <th>Статус</th>
                        <td>
                            <span class="badge {{ $brief->isCompleted() ? 'bg-success' : ($brief->isActive() ? 'bg-warning' : 'bg-secondary') }}">
                                {{ $brief->status->value }}
                            </span>
                        </td>
                    </tr>
                    @if($brief->total_area)
                        <tr>
                            <th>Общая площадь</th>
                            <td>{{ $brief->total_area }} м²</td>
                        </tr>
                    @endif
                    @if($brief->price)
                        <tr>
                            <th>Бюджет</th>
                            <td>{{ number_format($brief->price, 0, '.', ' ') }} ₽</td>
                        </tr>
                    @endif
                    <tr>
                        <th>Пользователь</th>
                        <td>{{ $brief->user->name ?? 'Неизвестно' }}</td>
                    </tr>
                    <tr>
                        <th>Номер клиента</th>
                        <td>{{ $brief->user->phone ?? 'Не указан' }}</td>
                    </tr>
                    <tr>
                        <th>Дата создания</th>
                        <td>{{ $brief->created_at->format('d.m.Y H:i') }}</td>
                    </tr>
                    <tr>
                        <th>Дата обновления</th>
                        <td>{{ $brief->updated_at->format('d.m.Y H:i') }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    @if($brief->rooms->isNotEmpty())
        <!-- Зоны/Комнаты -->
        <div class="card mb-4">
            <div class="card-header">
                <h2>{{ $brief->isCommon() ? 'Выбранные помещения' : 'Зоны проекта' }}</h2>
            </div>
            <div class="card-body">
                <div class="selected-rooms">
                    @foreach($brief->rooms as $room)
                        <div class="room-badge {{ $room->isCustom() ? 'custom-room-badge' : '' }}">
                            {{ $room->title }}
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif

    <!-- Ответы на вопросы по страницам -->
    @foreach($questionsByPage as $pageNumber => $pageQuestions)
        <div class="card mb-4">
            <div class="card-header">
                <h2>
                    {{ $pageTitles[$pageNumber]['title'] ?? "Страница $pageNumber" }}
                </h2>
                @if(isset($pageTitles[$pageNumber]['subtitle']))
                    <p class="text-muted">{{ $pageTitles[$pageNumber]['subtitle'] }}</p>
                @endif
            </div>
            <div class="card-body">
                @if($pageNumber == 3 && $brief->isCommon())
                    <!-- Особая обработка для страницы с комнатами в общем брифе -->
                    @include('briefs.partials.room-answers', ['roomAnswers' => $roomAnswers ?? collect()])
                @else
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th width="40%">Вопрос</th>
                                <th>Ответ</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php $hasAnswers = false; @endphp
                            @foreach($pageQuestions as $question)
                                @if(isset($answersMap[$question->key]))
                                    @php $hasAnswers = true; @endphp
                                    <tr>
                                        <td>
                                            <strong>{{ $question->title }}</strong>
                                            @if($question->subtitle)
                                                <br><small class="text-muted">{{ $question->subtitle }}</small>
                                            @endif
                                        </td>
                                        <td>
                                            @if($answersMap[$question->key]->answer_text)
                                                {!! nl2br(e($answersMap[$question->key]->answer_text)) !!}
                                            @elseif($answersMap[$question->key]->answer_json)
                                                @php
                                                    $jsonAnswer = json_decode($answersMap[$question->key]->answer_json, true);
                                                @endphp
                                                @if(is_array($jsonAnswer))
                                                    @foreach($jsonAnswer as $key => $value)
                                                        <div><strong>{{ ucfirst($key) }}:</strong> {{ $value }}</div>
                                                    @endforeach
                                                @else
                                                    {{ $jsonAnswer }}
                                                @endif
                                            @else
                                                <span class="text-muted">Нет ответа</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endif
                            @endforeach
                            
                            @if(!$hasAnswers)
                                <tr>
                                    <td colspan="2" class="text-center text-muted">
                                        На этой странице нет ответов
                                    </td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                @endif
            </div>
        </div>
    @endforeach

    @if($brief->isCommercial() && isset($zoneAnswers))
        <!-- Особая обработка для коммерческих брифов - ответы по зонам -->
        @include('briefs.partials.zone-answers', ['zoneAnswers' => $zoneAnswers])
    @endif

    @if($brief->documents->isNotEmpty())
        <!-- Документы -->
        <div class="card mb-4">
            <div class="card-header">
                <h2>Прикрепленные документы</h2>
            </div>
            <div class="card-body">
                <div class="row">
                    @foreach($brief->documents as $document)
                        <div class="col-md-4 mb-3">
                            <div class="card">
                                <div class="card-body text-center">
                                    <i class="fas fa-file fa-2x mb-2"></i>
                                    <h6 class="card-title">{{ $document->original_name }}</h6>
                                    <p class="card-text">
                                        <small class="text-muted">
                                            {{ number_format($document->file_size / 1024, 2) }} KB
                                        </small>
                                    </p>
                                    <a href="{{ $document->full_path }}" 
                                       class="btn btn-primary btn-sm document-link" 
                                       target="_blank"
                                       data-file-name="{{ $document->original_name }}"
                                       data-file-type="{{ $document->mime_type }}">
                                        Скачать
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif
</div>
