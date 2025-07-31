<div class="container-fluid py-4">
    <!-- Заголовок страницы с действиями -->
    <div class="d-flex align-items-center justify-content-between mb-4 flex-bottom">
        <h1 class="h3 mb-0 text-gray-800">Редактирование коммерческого брифа <span class="badge badge-primary">#{{ $brief->id }}</span></h1>
        <div class="admin-actions ">
            <a href="{{ route('commercial.show', $brief->id) }}" class="btn btn-sm btn-outline-primary" target="_blank">
                <i class="fas fa-eye"></i> Просмотреть бриф
            </a>
          
            @if($brief->deal_id)
            <div class="deal-exists-notice mt-2">
                <small class="text-warning"><i class="fas fa-exclamation-triangle"></i> Для данного брифа уже создана сделка #{{ $brief->deal_id }}</small>
            </div>
            @endif
            <button type="button" class="btn btn-success create-deal-btn" 
                data-brief-id="{{ $brief->id }}" 
                data-client-id="{{ $brief->user_id }}"
                data-brief-type="commercial"
                {{ $brief->deal_id ? 'disabled' : '' }}>
                <i class="fas {{ $brief->deal_id ? 'fa-ban' : 'fa-handshake' }}"></i> 
                {{ $brief->deal_id ? 'Сделка уже создана' : 'Создать сделку по брифу' }}
            </button>
        </div>
    </div>

    <form action="{{ route('admin.brief.commercial.update', $brief->id) }}" method="POST" id="briefEditForm">
        @csrf
        @method('PUT')

        <!-- Навигация по вкладкам в стиле Bootstrap -->
        <ul class="nav nav-tabs mb-3" id="briefTabs" role="tablist">
            <li class="nav-item">
                <a class="nav-link active" id="info-tab" data-toggle="tab" href="#info-content" role="tab" 
                   aria-controls="info-content" aria-selected="true">
                   <i class="fas fa-info-circle mr-2"></i>Основная информация
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="zones-tab" data-toggle="tab" href="#zones-content" role="tab" 
                   aria-controls="zones-content" aria-selected="false">
                   <i class="fas fa-building mr-2"></i>Зоны и помещения
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="answers-tab" data-toggle="tab" href="#answers-content" role="tab" 
                   aria-controls="answers-content" aria-selected="false">
                   <i class="fas fa-question-circle mr-2"></i>Ответы по зонам
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="files-tab" data-toggle="tab" href="#files-content" role="tab" 
                   aria-controls="files-content" aria-selected="false">
                   <i class="fas fa-file-alt mr-2"></i>Файлы и референсы
                </a>
            </li>
        </ul>

        <!-- Содержимое вкладок -->
        <div class="tab-content" id="briefTabContent">
            <!-- Вкладка основной информации -->
            <div class="tab-pane fade show active" id="info-content" role="tabpanel" aria-labelledby="info-tab">
                <div class="row">
                    <div class="col-md-8">
                        <div class="card  mb-4">
                            <div class="card-header py-3">
                                <h6 class="m-0 font-weight-bold text-primary">Основная информация</h6>
                            </div>
                            <div class="card-body">
                                <div class="form-group">
                                    <label for="title">Название брифа</label>
                                    <input type="text" name="title" id="title" class="form-control" value="{{ $brief->title }}" required>
                                </div>
                                
                                <div class="form-group">
                                    <label for="description">Описание</label>
                                    <textarea name="description" id="description" class="form-control" rows="3">{{ $brief->description }}</textarea>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="status">Статус</label>
                                            <select name="status" id="status" class="form-control">
                                                <option value="Активный" {{ $brief->status == 'Активный' ? 'selected' : '' }}>Активный</option>
                                                <option value="Завершенный" {{ $brief->status == 'Завершенный' ? 'selected' : '' }}>Завершенный</option>
                                                <option value="completed" {{ $brief->status == 'completed' ? 'selected' : '' }}>Выполнен</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="price">Общий бюджет (₽)</label>
                                            <input type="number" name="price" id="price" class="form-control" value="{{ $brief->price }}">
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="total_area">Общая площадь (м²)</label>
                                            <input type="number" name="total_area" id="total_area" class="form-control" value="{{ $brief->total_area }}" step="0.01">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="projected_area">Проектируемая площадь (м²)</label>
                                            <input type="number" name="projected_area" id="projected_area" class="form-control" value="{{ $brief->projected_area }}" step="0.01">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="card  mb-4">
                            <div class="card-header py-3">
                                <h6 class="m-0 font-weight-bold text-primary">Информация о клиенте</h6>
                            </div>
                            <div class="card-body">
                                @if(isset($brief->user_id))
                                    <div class="user-info">
                                        <p><strong>ФИО клиента:</strong> 
                                            <a href="{{ route('profile.view', $brief->user_id) }}">
                                                {{ $brief->user->name ?? 'Не указано' }}
                                            </a>
                                        </p>
                                        <p><strong>Email:</strong> {{ $brief->user->email ?? 'Не указано' }}</p>
                                        <p><strong>Телефон:</strong> {{ $brief->user->phone ?? 'Не указано' }}</p>
                                        
                                        <div class="card bg-light mb-0 mt-3">
                                            <div class="card-body py-2">
                                                <div class="d-flex justify-content-between">
                                                    <span>Создан:</span>
                                                    <strong>{{ $brief->created_at->format('d.m.Y H:i') }}</strong>
                                                </div>
                                                <div class="d-flex justify-content-between mt-2">
                                                    <span>Обновлен:</span>
                                                    <strong>{{ $brief->updated_at->format('d.m.Y H:i') }}</strong>
                                                </div>
                                                @if($brief->deal_id)
                                                <hr class="my-2">
                                                <div class="d-flex justify-content-between">
                                                    <span>Сделка:</span>
                                                    <a href="{{ route('deal.edit', $brief->deal_id) }}">
                                                        <strong>#{{ $brief->deal_id }}</strong>
                                                    </a>
                                                </div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @else
                                    <div class="alert alert-warning">
                                        <i class="fas fa-exclamation-triangle mr-2"></i> Клиент не найден или удалён.
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Вкладка с зонами -->
            <div class="tab-pane fade" id="zones-content" role="tabpanel" aria-labelledby="zones-tab">
                <div class="card  mb-4">
                    <div class="card-header py-3 d-flex justify-content-between align-items-center">
                        <h6 class="m-0 font-weight-bold text-primary">Зоны и помещения</h6>
                        <button type="button" class="btn btn-sm btn-primary" id="addZoneBtn">
                            <i class="fas fa-plus"></i> Добавить зону
                        </button>
                    </div>
                    <div class="card-body">
                        <!-- Пояснение по вопросам для зон -->
                        <div class="alert alert-info mb-4">
                            <h5 class="mb-3"><i class="fas fa-info-circle mr-2"></i> Вопросы для каждой зоны</h5>
                            <div class="row">
                                <div class="col-md-6">
                                    <ul class="list-unstyled mb-0">
                                        @foreach(array_slice($titles, 1, 7, true) as $key => $title)
                                            <li class="mb-1"><strong>{{ $key }}.</strong> {{ $title }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                                <div class="col-md-6">
                                    <ul class="list-unstyled mb-0">
                                        @foreach(array_slice($titles, 8, null, true) as $key => $title)
                                            <li class="mb-1"><strong>{{ $key }}.</strong> {{ $title }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <div id="zonesContainer">
                            @if(is_array($zones) && count($zones) > 0)
                                <div class="table-responsive">
                                    <table class="table table-bordered table-hover" id="zonesTable">
                                        <thead>
                                            <tr>
                                                <th width="5%">#</th>
                                                <th width="25%">Название зоны</th>
                                                <th width="35%">Описание</th>
                                                <th width="15%">Ответов</th>
                                                <th width="20%">Действия</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($zones as $index => $zone)
                                                @php
                                                $answersCount = isset($preferences[$index]) ? count(array_filter($preferences[$index], function($value) {
                                                    return !empty(trim($value));
                                                })) : 0;
                                                @endphp
                                                <tr data-zone-index="{{ $index }}">
                                                    <td>{{ $index + 1 }}</td>
                                                    <td>
                                                        <input type="hidden" name="zones[{{ $index }}][name]" value="{{ $zone['name'] ?? '' }}">
                                                        <span class="font-weight-bold">{{ $zone['name'] ?? 'Без названия' }}</span>
                                                    </td>
                                                    <td>
                                                        <input type="hidden" name="zones[{{ $index }}][description]" value="{{ $zone['description'] ?? '' }}">
                                                        <span class="text-muted">{{ Str::limit($zone['description'] ?? 'Нет описания', 50) }}</span>
                                                    </td>
                                                    <td>
                                                        <span class="badge badge-{{ $answersCount > 0 ? 'success' : 'secondary' }} p-2">
                                                            {{ $answersCount }} {{ trans_choice('ответ|ответа|ответов', $answersCount) }}
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <button type="button" class="btn btn-sm btn-outline-primary edit-zone" data-zone-index="{{ $index }}">
                                                            <i class="fas fa-edit"></i> Редактировать
                                                        </button>
                                                        <button type="button" class="btn btn-sm btn-outline-danger delete-zone" data-zone-index="{{ $index }}">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <div class="text-center py-5">
                                    <i class="fas fa-building fa-4x text-muted mb-3"></i>
                                    <h5>Нет добавленных зон</h5>
                                    <p class="text-muted">Нажмите кнопку "Добавить зону", чтобы создать первую зону.</p>
                                </div>
                            @endif

                            <!-- Скрытое поле для передачи данных о предпочтениях зон -->
                            <input type="hidden" id="zone-preferences-data" name="zone_preferences_data" value="{{ json_encode($preferences) }}">
                        </div>
                    </div>
                </div>
            </div>

            <!-- ВКЛАДКА: Ответы по зонам -->
            <div class="tab-pane fade" id="answers-content" role="tabpanel" aria-labelledby="answers-tab">
                <div class="card  mb-4">
                    <div class="card-header py-3 d-flex justify-content-between align-items-center">
                        <h6 class="m-0 font-weight-bold text-primary">Ответы по зонам</h6>
                        <!-- Кнопка для копирования ответов -->
                        <button type="button" class="btn btn-sm btn-outline-info" id="copyPreferencesBtn" 
                                data-toggle="tooltip" title="Скопировать JSON структуру ответов">
                            <i class="fas fa-copy mr-1"></i> Скопировать ответы
                        </button>
                    </div>
                    <div class="card-body">
                        @php
                        $titles = [
                            1  => "Название зоны",
                            2  => "Метраж зон",
                            3  => "Зоны и их стиль оформления",
                            4  => "Меблировка зон",
                            5  => "Предпочтения отделочных материалов",
                            6  => "Освещение зон",
                            7  => "Кондиционирование зон",
                            8  => "Напольное покрытие зон",
                            9  => "Отделка стен зон",
                            10 => "Отделка потолков зон",
                            11 => "Категорически неприемлемо или нет",
                            12 => "Бюджет на помещения",
                            13 => "Пожелания и комментарии",
                        ];
                        $zones = json_decode($brief->zones ?? '[]', true);
                        $zoneBudgets = json_decode($brief->zone_budgets ?? '[]', true) ?: [];
                        $preferences = json_decode($brief->preferences ?? '[]', true) ?: [];

                        // Группируем вопросы по категориям для более удобной навигации
                        $categories = [
                            'general' => ['name' => 'Основная информация', 'questions' => [1, 2]],
                            'design' => ['name' => 'Дизайн и стиль', 'questions' => [3, 4, 5]],
                            'technical' => ['name' => 'Технические характеристики', 'questions' => [6, 7, 8, 9, 10]],
                            'other' => ['name' => 'Дополнительная информация', 'questions' => [11, 12, 13]]
                        ];
                        @endphp
                        
                        <!-- Отладочная панель -->
                        <div class="card mb-4">
                            <div class="card-header py-2 bg-light">
                                <a class="btn btn-link p-0" data-toggle="collapse" href="#debugPanel">
                                    <i class="fas fa-code mr-1"></i> Структура данных ответов
                                </a>
                            </div>
                            <div class="collapse" id="debugPanel">
                                <div class="card-body">
                                    <div class="form-group">
                                        <label for="preferencesJson">JSON структура ответов на вопросы:</label>
                                        <textarea id="preferencesJson" class="form-control code-area" rows="7" readonly>{{ json_encode($preferences, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE) }}</textarea>
                                    </div>
                                    <button type="button" class="btn btn-sm btn-outline-secondary copy-json-btn" data-target="#preferencesJson">
                                        <i class="fas fa-copy mr-1"></i> Скопировать
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Если нет зон, показываем сообщение -->
                        @if(empty($zones))
                            <div class="text-center py-5">
                                <i class="fas fa-question-circle fa-4x text-muted mb-3"></i>
                                <h5>Нет добавленных зон</h5>
                                <p class="text-muted">Сначала добавьте зоны во вкладке "Зоны и помещения".</p>
                            </div>
                        @else
                            <!-- Селектор зон для быстрой навигации -->
                            <div class="form-group zone-selector mb-4">
                                <label for="zone-selector" class="font-weight-bold">Выбрать зону:</label>
                                <select id="zone-selector" class="form-control form-control-lg">
                                    @foreach($zones as $index => $zone)
                                        @php
                                        // Проверяем существование ключа zone_{$index} в preferences
                                        $zoneKey = "zone_{$index}";
                                        $hasAnswers = isset($preferences[$zoneKey]) && is_array($preferences[$zoneKey]);
                                        $answersCount = $hasAnswers ? count(array_filter($preferences[$zoneKey])) : 0;
                                        @endphp
                                        <option value="zone-{{ $index }}">
                                            {{ $zone['name'] ?? 'Зона '.($index+1) }} 
                                            ({{ $answersCount }} {{ trans_choice('ответ|ответа|ответов', $answersCount) }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Аккордеон с зонами и ответами -->
                            <div class="accordion" id="zonesAccordion">
                                @foreach($zones as $index => $zone)
                                    <div class="card mb-3 zone-card" id="zone-{{ $index }}-card">
                                        <div class="card-header d-flex justify-content-between align-items-center" id="heading-{{ $index }}">
                                            <h5 class="mb-0">
                                                <button class="btn btn-link" type="button" data-toggle="collapse" 
                                                        data-target="#collapse-{{ $index }}" aria-expanded="{{ $loop->first ? 'true' : 'false' }}" 
                                                        aria-controls="collapse-{{ $index }}">
                                                    <i class="fas fa-building mr-2"></i>
                                                    <span class="font-weight-bold">{{ $zone['name'] ?? 'Зона '.($index+1) }}</span>
                                                    <span class="text-muted ml-2">({{ $zone['total_area'] ?? '?' }})</span>
                                                </button>
                                            </h5>
                                            <div>
                                                @php
                                                // Проверяем существование ключа zone_{$index} в preferences
                                                $zoneKey = "zone_{$index}";
                                                $zonePrefs = isset($preferences[$zoneKey]) && is_array($preferences[$zoneKey]) ? $preferences[$zoneKey] : [];
                                                $totalAnswers = count(array_filter($zonePrefs));
                                                $totalQuestions = count(range(3, 11)); // Вопросы 3-11 для каждой зоны
                                                $percentComplete = $totalQuestions > 0 ? round(($totalAnswers / $totalQuestions) * 100) : 0;
                                                @endphp
                                                <div class="progress" style="width: 100px; height: 10px;">
                                                    <div class="progress-bar bg-{{ $percentComplete > 75 ? 'success' : ($percentComplete > 30 ? 'warning' : 'danger') }}" 
                                                         role="progressbar" style="width: {{ $percentComplete }}%" 
                                                         aria-valuenow="{{ $percentComplete }}" aria-valuemin="0" aria-valuemax="100">
                                                    </div>
                                                </div>
                                                <small class="text-muted">{{ $percentComplete }}% заполнено</small>
                                            </div>
                                        </div>

                                        <div id="collapse-{{ $index }}" class="collapse {{ $loop->first ? 'show' : '' }}" aria-labelledby="heading-{{ $index }}" data-parent="#zonesAccordion">
                                            <div class="card-body">
                                                <!-- Навигация по категориям вопросов -->
                                                <ul class="nav nav-pills mb-4" id="zone-{{ $index }}-pills-tab" role="tablist">
                                                    @foreach($categories as $catKey => $category)
                                                        <li class="nav-item">
                                                            <a class="nav-link {{ $loop->first ? 'active' : '' }}" 
                                                               id="zone-{{ $index }}-{{ $catKey }}-tab" 
                                                               data-toggle="pill" 
                                                               href="#zone-{{ $index }}-{{ $catKey }}-content" 
                                                               role="tab">
                                                                {{ $category['name'] }}
                                                                @php
                                                                $zoneKey = "zone_{$index}";
                                                                $zonePrefs = isset($preferences[$zoneKey]) && is_array($preferences[$zoneKey]) ? $preferences[$zoneKey] : [];
                                                                $catAnswers = 0;
                                                                foreach($category['questions'] as $qNum) {
                                                                    $questionKey = "question_{$qNum}";
                                                                    if(isset($zonePrefs[$questionKey]) && !empty(trim($zonePrefs[$questionKey]))) {
                                                                        $catAnswers++;
                                                                    }
                                                                }
                                                                $catTotal = count($category['questions']);
                                                                @endphp
                                                                <span class="badge badge-{{ $catAnswers == $catTotal ? 'success' : ($catAnswers > 0 ? 'warning' : 'light') }} ml-1">
                                                                    {{ $catAnswers }}/{{ $catTotal }}
                                                                </span>
                                                            </a>
                                                        </li>
                                                    @endforeach
                                                </ul>

                                                <!-- Содержимое вкладок с вопросами -->
                                                <div class="tab-content" id="zone-{{ $index }}-pills-tabContent">
                                                    @foreach($categories as $catKey => $category)
                                                        <div class="tab-pane fade {{ $loop->first ? 'show active' : '' }}" 
                                                             id="zone-{{ $index }}-{{ $catKey }}-content" 
                                                             role="tabpanel">
                                                             
                                                            <div class="table-responsive">
                                                                <table class="table table-bordered">
                                                                    <thead class="thead-light">
                                                                        <tr>
                                                                            <th width="40%">Вопрос</th>
                                                                            <th>Ответ</th>
                                                                        </tr>
                                                                    </thead>
                                                                    <tbody>
                                                                        @foreach($category['questions'] as $questionId)
                                                                            <tr>
                                                                                <td class="align-middle">
                                                                                    <strong>{{ $titles[$questionId] }}</strong>
                                                                                </td>
                                                                                <td>
                                                                                    @php
                                                                                    // Формируем правильные ключи для доступа к ответам
                                                                                    $zoneKey = "zone_{$index}";
                                                                                    $questionKey = "question_{$questionId}";
                                                                                    $answer = isset($preferences[$zoneKey][$questionKey]) ? $preferences[$zoneKey][$questionKey] : '';
                                                                                    @endphp
                                                                                    
                                                                                    @if($questionId == 1)
                                                                                        <!-- Название зоны -->
                                                                                        <input type="text" class="form-control zone-answer-field" 
                                                                                              name="zones[{{ $index }}][name]" 
                                                                                              value="{{ $zone['name'] ?? '' }}"
                                                                                              data-zone-index="{{ $index }}"
                                                                                              data-question-key="name">
                                                                                    @elseif($questionId == 2)
                                                                                        <!-- Метраж зоны -->
                                                                                        <div class="input-group">
                                                                                            <input type="text" class="form-control zone-answer-field" 
                                                                                                  name="zones[{{ $index }}][total_area]" 
                                                                                                  value="{{ $zone['total_area'] ?? '' }}"
                                                                                                  data-zone-index="{{ $index }}"
                                                                                                  data-question-key="total_area">
                                                                                            <div class="input-group-append">
                                                                                                <span class="input-group-text">м²</span>
                                                                                            </div>
                                                                                        </div>
                                                                                    @elseif($questionId == 12)
                                                                                        <!-- Бюджет на помещение -->
                                                                                        <div class="input-group">
                                                                                            <input type="number" class="form-control zone-budget-field" 
                                                                                                  name="zone_budgets[{{ $index }}]" 
                                                                                                  value="{{ $zoneBudgets[$index] ?? '' }}"
                                                                                                  data-zone-index="{{ $index }}">
                                                                                            <div class="input-group-append">
                                                                                                <span class="input-group-text">₽</span>
                                                                                            </div>
                                                                                        </div>
                                                                                    @else
                                                                                        <!-- Обычный ответ на вопрос -->
                                                                                        <textarea class="form-control zone-preference-field" rows="3"
                                                                                                 name="preferences[{{ $zoneKey }}][{{ $questionKey }}]"
                                                                                                 data-zone-key="{{ $zoneKey }}"
                                                                                                 data-question-key="{{ $questionKey }}">{{ $answer }}</textarea>
                                                                                    @endif
                                                                                </td>
                                                                            </tr>
                                                                        @endforeach
                                                                    </tbody>
                                                                </table>
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                        
                        <!-- Скрытое поле для хранения всех предпочтений зон в формате JSON -->
                        <input type="hidden" id="zone-preferences-data" name="zone_preferences_data" value="{{ json_encode($preferences) }}">
                    </div>
                </div>
            </div>

            <!-- Вкладка с файлами и референсами -->
            <div class="tab-pane fade" id="files-content" role="tabpanel" aria-labelledby="files-tab">
                <div class="row">
               

                    <!-- Документы -->
                    <div class="col-md-6">
                        <div class="card  mb-4">
                            <div class="card-header py-3">
                                <h6 class="m-0 font-weight-bold text-primary">Документы</h6>
                            </div>
                            <div class="card-body">
                                <div id="documents-container">
                                    @php
                                    $documents = json_decode($brief->documents ?? '[]', true);
                                    @endphp
                                    
                                    @if(is_array($documents) && count($documents) > 0)
                                        <div class="list-group documents-list">
                                            @foreach($documents as $index => $document)
                                                <div class="list-group-item document-item" data-url="{{ $document }}">
                                                    <div class="d-flex justify-content-between align-items-center">
                                                        <div class="d-flex align-items-center">
                                                            @php
                                                            $extension = pathinfo(parse_url($document, PHP_URL_PATH), PATHINFO_EXTENSION);
                                                            $filename = pathinfo(parse_url($document, PHP_URL_PATH), PATHINFO_BASENAME);
                                                            
                                                            // Определяем иконку на основе расширения
                                                            $iconClass = 'fa-file';
                                                            switch(strtolower($extension)) {
                                                                case 'pdf': $iconClass = 'fa-file-pdf'; break;
                                                                case 'doc': case 'docx': $iconClass = 'fa-file-word'; break;
                                                                case 'xls': case 'xlsx': $iconClass = 'fa-file-excel'; break;
                                                                case 'jpg': case 'jpeg': case 'png': case 'gif': $iconClass = 'fa-file-image'; break;
                                                                case 'zip': case 'rar': $iconClass = 'fa-file-archive'; break;
                                                            }
                                                            @endphp
                                                            
                                                            <div class="mr-3">
                                                                <i class="far {{ $iconClass }} fa-2x text-muted"></i>
                                                            </div>
                                                            <div>
                                                                <div class="text-truncate" style="max-width: 200px;">{{ $filename }}</div>
                                                                <small class="text-muted">{{ strtoupper($extension) }}</small>
                                                            </div>
                                                        </div>
                                                        <div class="btn-group">
                                                            <a href="{{ $document }}" class="btn btn-sm btn-outline-primary" target="_blank">
                                                                <i class="fas fa-download"></i>
                                                            </a>
                                                            <button type="button" class="btn btn-sm btn-outline-danger delete-document" data-url="{{ $document }}">
                                                                <i class="fas fa-trash"></i>
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    @else
                                        <div class="text-center py-5">
                                            <i class="far fa-file-alt fa-4x text-muted mb-3"></i>
                                            
                                            <p class="text-muted">Документы появятся, когда пользователь загрузит их</p>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12 mb-4">
                <div class="card ">
                    <div class="card-body d-flex justify-content-between align-items-center">
                        <div class="d-flex">
                            <button type="submit" class="btn btn-primary mr-2">
                                <i class="fas fa-save"></i> Сохранить изменения
                            </button>
                        </div>
                        <button type="button" class="btn btn-success create-deal-btn" 
                            data-brief-id="{{ $brief->id }}" 
                            data-client-id="{{ $brief->user_id }}"
                            data-brief-type="commercial"
                            {{ $brief->deal_id ? 'disabled' : '' }}>
                            <i class="fas {{ $brief->deal_id ? 'fa-ban' : 'fa-handshake' }}"></i> 
                            {{ $brief->deal_id ? 'Сделка уже создана' : 'Создать сделку по брифу' }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<!-- Модальное окно для добавления новой зоны -->
<div class="modal fade" id="addZoneModal" tabindex="-1" aria-labelledby="addZoneModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="addZoneModalLabel">Добавить новую зону</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <div class="form-group">
          <label for="new-zone-name">Название зоны</label>
          <input type="text" class="form-control" id="new-zone-name" placeholder="Например: Гостиная, Кухня, Спальня...">
        </div>
        <div class="form-group">
          <label for="new-zone-description">Описание зоны</label>
          <textarea class="form-control" id="new-zone-description" rows="3" placeholder="Опишите особенности этой зоны..."></textarea>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Отмена</button>
        <button type="button" class="btn btn-primary" id="saveNewZoneBtn">Добавить зону</button>
      </div>
    </div>
  </div>
</div>

<!-- Модальное окно для редактирования зоны и ответов на вопросы -->
<div class="modal fade" id="editZoneModal" tabindex="-1" aria-labelledby="editZoneModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="editZoneModalLabel">Редактирование зоны</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <input type="hidden" id="edit-zone-index">
        
        <ul class="nav nav-tabs" id="zoneEditTabs" role="tablist">
          <li class="nav-item">
            <a class="nav-link active" id="basics-tab" data-toggle="tab" href="#basics" role="tab" 
               aria-controls="basics" aria-selected="true">Основная информация</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" id="answers-tab" data-toggle="tab" href="#answers" role="tab" 
               aria-controls="answers" aria-selected="false">Ответы на вопросы</a>
          </li>
        </ul>
        
        <div class="tab-content pt-3" id="zoneEditTabContent">
          <div class="tab-pane fade show active" id="basics" role="tabpanel" aria-labelledby="basics-tab">
            <div class="form-group">
              <label for="edit-zone-name">Название зоны</label>
              <input type="text" class="form-control" id="edit-zone-name">
            </div>
            <div class="form-group">
              <label for="edit-zone-description">Описание зоны</label>
              <textarea class="form-control" id="edit-zone-description" rows="3"></textarea>
            </div>
          </div>
          <div class="tab-pane fade" id="answers" role="tabpanel" aria-labelledby="answers-tab">
            <div class="answers-container">
              <!-- Здесь будут выводиться поля для ответов на вопросы -->
            </div>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Отмена</button>
        <button type="button" class="btn btn-primary" id="saveZoneChangesBtn">Сохранить изменения</button>
      </div>
    </div>
  </div>
</div>

<!-- Модальное окно для уведомлений -->
<div class="modal fade" id="notificationModal" tabindex="-1" aria-labelledby="notificationModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="notificationModalLabel">Уведомление</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="notificationModalBody">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Закрыть</button>
                <button type="button" class="btn btn-primary" id="redirectBtn" style="display: none;">Перейти</button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Проверяем наличие jQuery
    if (typeof jQuery === 'undefined') {
        console.error('jQuery не загружен! Загружаем jQuery для работы вкладок');
        const jqueryScript = document.createElement('script');
        jqueryScript.src = 'https://code.jquery.com/jquery-3.6.0.min.js';
        jqueryScript.onload = initAllFunctions;
        document.head.appendChild(jqueryScript);
    } else {
        // Проверяем наличие Bootstrap Tabs API
        if (typeof jQuery.fn.tab === 'undefined') {
            console.error('Bootstrap JS не загружен! Загружаем Bootstrap JS для работы вкладок');
            const bootstrapScript = document.createElement('script');
            bootstrapScript.src = 'https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js';
            bootstrapScript.onload = initAllFunctions;
            document.head.appendChild(bootstrapScript);
        } else {
            // Если всё нормально загружено, инициализируем функционал
            initAllFunctions();
        }
    }

    function initAllFunctions() {
        // Инициализация вкладок с использованием Bootstrap Tab API
        $('#briefTabs a').on('click', function(e) {
            e.preventDefault();
            $(this).tab('show');
        });

        // Существующие обработчики событий
        // ...existing code...

        // Новый код для управления зонами и ответами
        initZoneSelector();
        initAnswersTabs();
    }

    // Функция инициализации селектора зон
    function initZoneSelector() {
        const zoneSelector = document.getElementById('zone-selector');
        if (!zoneSelector) return;

        // Добавляем обработчик изменения выбранной зоны
        zoneSelector.addEventListener('change', function() {
            const selectedZone = this.value;
            
            // Закрываем все карточки зон
            document.querySelectorAll('.zone-card').forEach(card => {
                card.classList.remove('show');
                const collapseEl = card.querySelector('.collapse');
                if (collapseEl) $(collapseEl).collapse('hide');
            });
            
            // Открываем выбранную зону
            const selectedCard = document.getElementById(selectedZone + '-card');
            if (selectedCard) {
                const collapseEl = selectedCard.querySelector('.collapse');
                if (collapseEl) {
                    setTimeout(() => {
                        $(collapseEl).collapse('show');
                        selectedCard.scrollIntoView({ behavior: 'smooth', block: 'start' });
                    }, 100);
                }
            }
        });
    }

    // Функция инициализации вкладок с ответами
    function initAnswersTabs() {
        // Инициализируем все вкладки с ответами
        document.querySelectorAll('[id$="-pills-tab"] .nav-link').forEach(tab => {
            tab.addEventListener('click', function(e) {
                e.preventDefault();
                $(this).tab('show');
            });
        });
    }

    // Обработчик для кнопки добавления зоны
    document.getElementById('addZoneBtn')?.addEventListener('click', function() {
        $('#addZoneModal').modal('show');
    });

    // Обработчик для сохранения новой зоны
    document.getElementById('saveNewZoneBtn')?.addEventListener('click', function() {
        const name = document.getElementById('new-zone-name').value.trim();
        const description = document.getElementById('new-zone-description').value.trim();
        
        if (!name) {
            alert('Название зоны обязательно для заполнения');
            return;
        }
        
        // Получаем текущие зоны
        const zonesContainer = document.getElementById('zonesContainer');
        let zonesTable = document.getElementById('zonesTable');
        let nextIndex = 0;
        
        // Если таблица уже существует
        if (zonesTable) {
            const tbody = zonesTable.querySelector('tbody');
            const rows = tbody.querySelectorAll('tr');
            if (rows.length > 0) {
                // Получаем максимальный индекс + 1
                nextIndex = Math.max(...Array.from(rows).map(row => parseInt(row.dataset.zoneIndex))) + 1;
            }
        } else {
            // Создаем таблицу, если её нет
            const tableWrapper = document.createElement('div');
            tableWrapper.className = 'table-responsive';
            tableWrapper.innerHTML = `
                <table class="table table-bordered table-hover" id="zonesTable">
                    <thead>
                        <tr>
                            <th width="5%">#</th>
                            <th width="25%">Название зоны</th>
                            <th width="35%">Описание</th>
                            <th width="15%">Ответов</th>
                            <th width="20%">Действия</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            `;
            
            // Удаляем сообщение "Нет зон" если оно есть
            const emptyState = zonesContainer.querySelector('.text-center');
            if (emptyState) {
                emptyState.remove();
            }
            
            zonesContainer.appendChild(tableWrapper);
            zonesTable = document.getElementById('zonesTable');
        }
        
        // Добавляем новую строку
        const tbody = zonesTable.querySelector('tbody');
        const newRow = document.createElement('tr');
        newRow.dataset.zoneIndex = nextIndex;
        
        newRow.innerHTML = `
            <td>${nextIndex + 1}</td>
            <td>
                <input type="hidden" name="zones[${nextIndex}][name]" value="${name}">
                <span class="font-weight-bold">${name}</span>
            </td>
            <td>
                <input type="hidden" name="zones[${nextIndex}][description]" value="${description}">
                <span class="text-muted">${description ? (description.length > 50 ? description.substring(0, 50) + '...' : description) : 'Нет описания'}</span>
            </td>
            <td>
                <span class="badge badge-secondary p-2">0 ответов</span>
            </td>
            <td>
                <button type="button" class="btn btn-sm btn-outline-primary edit-zone" data-zone-index="${nextIndex}">
                    <i class="fas fa-edit"></i> Редактировать
                </button>
                <button type="button" class="btn btn-sm btn-outline-danger delete-zone" data-zone-index="${nextIndex}">
                    <i class="fas fa-trash"></i>
                </button>
            </td>
        `;
        
        tbody.appendChild(newRow);
        
        // Обновляем скрытое поле с данными о предпочтениях
        updateZonePreferencesData();
        
        // Добавляем обработчики для новых кнопок
        addZoneButtonHandlers();
        
        // Закрываем модальное окно и очищаем поля
        $('#addZoneModal').modal('hide');
        document.getElementById('new-zone-name').value = '';
        document.getElementById('new-zone-description').value = '';
        
        // Показываем уведомление
        showToast('Зона успешно добавлена', 'success');
    });

    // Обработчик для редактирования и удаления зон
    function addZoneButtonHandlers() {
        // Редактирование зон
        document.querySelectorAll('.edit-zone').forEach(btn => {
            btn.onclick = function() {
                const zoneIndex = this.dataset.zoneIndex;
                const row = document.querySelector(`tr[data-zone-index="${zoneIndex}"]`);
                if (!row) return;
                
                const nameInput = row.querySelector(`input[name="zones[${zoneIndex}][name]"]`);
                const descInput = row.querySelector(`input[name="zones[${zoneIndex}][description]"]`);
                
                document.getElementById('edit-zone-index').value = zoneIndex;
                document.getElementById('edit-zone-name').value = nameInput.value;
                document.getElementById('edit-zone-description').value = descInput.value;
                
                // Загружаем вопросы и ответы
                loadZoneAnswers(zoneIndex);
                
                // Показываем модальное окно
                $('#editZoneModal').modal('show');
            };
        });
        
        // Удаление зон
        document.querySelectorAll('.delete-zone').forEach(btn => {
            btn.onclick = function() {
                if (confirm('Вы уверены, что хотите удалить эту зону?')) {
                    const zoneIndex = this.dataset.zoneIndex;
                    const row = document.querySelector(`tr[data-zone-index="${zoneIndex}"]`);
                    if (row) {
                        row.remove();
                        
                        // Проверяем, остались ли ещё зоны
                        const tbody = document.querySelector('#zonesTable tbody');
                        if (tbody && tbody.children.length === 0) {
                            const zonesContainer = document.getElementById('zonesContainer');
                            zonesContainer.innerHTML = `
                                <div class="text-center py-5">
                                    <i class="fas fa-building fa-4x text-muted mb-3"></i>
                                    <h5>Нет добавленных зон</h5>
                                    <p class="text-muted">Нажмите кнопку "Добавить зону", чтобы создать первую зону.</p>
                                </div>
                            `;
                        }
                        
                        // Обновляем данные о предпочтениях
                        updateZonePreferencesData();
                        
                        // Показываем уведомление
                        showToast('Зона успешно удалена', 'success');
                    }
                }
            };
        });
    }

    // Загрузка вопросов и ответов для зоны
    function loadZoneAnswers(zoneIndex) {
        const preferencesDataInput = document.getElementById('zone-preferences-data');
        const answersContainer = document.querySelector('.answers-container');
        
        // Очищаем контейнер
        answersContainer.innerHTML = '';
        
        // Получаем данные о предпочтениях
        let preferences = {};
        try {
            preferences = JSON.parse(preferencesDataInput.value || '{}');
        } catch (e) {
            console.error('Ошибка при разборе данных о предпочтениях:', e);
        }
        
        // Заголовки вопросов
        const titles = {
            'zone_1': "Зоны и их функционал",
            'zone_2': "Метраж зон",
            'zone_3': "Зоны и их стиль оформления",
            'zone_4': "Мебилировка зон",
            'zone_5': "Предпочтения отделочных материалов",
            'zone_6': "Освещение зон",
            'zone_7': "Кондиционирование зон",
            'zone_8': "Напольное покрытие зон",
            'zone_9': "Отделка стен зон",
            'zone_10': "Отделка потолков зон",
            'zone_11': "Категорически неприемлемо или нет",
            'zone_12': "Бюджет на помещения",
            'zone_13': "Пожелания и комментарии"
        };
        
        // Создаем поля для ответов
        for (let i = 1; i <= 13; i++) {
            const questionKey = `zone_${i}`;
            const zonePrefs = preferences[zoneIndex] || {};
            const answer = zonePrefs[`question_${i}`] || '';
            
            const answerCard = document.createElement('div');
            answerCard.className = 'card mb-3';
            
            answerCard.innerHTML = `
                <div class="card-header bg-light py-2">
                    <strong>${titles[questionKey] || `Вопрос ${i}`}</strong>
                </div>
                <div class="card-body">
                    <div class="form-group mb-0">
                        <textarea class="form-control" 
                                  rows="${answer.length > 100 ? 3 : 2}" 
                                  data-question="${i}">${answer}</textarea>
                    </div>
                </div>
            `;
            
            answersContainer.appendChild(answerCard);
        }
    }

    // Обработчик для сохранения изменений зоны
    document.getElementById('saveZoneChangesBtn')?.addEventListener('click', function() {
        const zoneIndex = document.getElementById('edit-zone-index').value;
        const zoneName = document.getElementById('edit-zone-name').value.trim();
        const zoneDesc = document.getElementById('edit-zone-description').value.trim();
        
        if (!zoneName) {
            alert('Название зоны обязательно для заполнения');
            return;
        }
        
        // Обновляем данные в таблице
        const row = document.querySelector(`tr[data-zone-index="${zoneIndex}"]`);
        if (!row) return;
        
        const nameInput = row.querySelector(`input[name="zones[${zoneIndex}][name]"]`);
        const descInput = row.querySelector(`input[name="zones[${zoneIndex}][description]"]`);
        
        nameInput.value = zoneName;
        descInput.value = zoneDesc;
        
        row.querySelector('td:nth-child(2) span').textContent = zoneName;
        row.querySelector('td:nth-child(3) span').textContent = zoneDesc ? (zoneDesc.length > 50 ? zoneDesc.substring(0, 50) + '...' : zoneDesc) : 'Нет описания';
        
        // Сохраняем ответы на вопросы
        const answers = {};
        document.querySelectorAll('.answers-container textarea').forEach(ta => {
            const qNum = ta.dataset.question;
            answers[`question_${qNum}`] = ta.value;
        });
        
        // Обновляем данные о предпочтениях
        let preferences = {};
        try {
            preferences = JSON.parse(document.getElementById('zone-preferences-data').value || '{}');
        } catch (e) {
            console.error('Ошибка при разборе данных о предпочтениях:', e);
        }
        
        // Обновляем предпочтения для текущей зоны
        preferences[zoneIndex] = answers;
        document.getElementById('zone-preferences-data').value = JSON.stringify(preferences);
        
        // Обновляем счетчик ответов
        const answersCount = Object.values(answers).filter(a => a.trim()).length;
        const badge = row.querySelector('td:nth-child(4) .badge');
        badge.textContent = `${answersCount} ${answersCount === 1 ? 'ответ' : (answersCount >= 2 && answersCount <= 4 ? 'ответа' : 'ответов')}`;
        badge.className = `badge badge-${answersCount > 0 ? 'success' : 'secondary'} p-2`;
        
        // Важная проверка: обновляем скрытое поле для передачи предпочтений
        const updatedPreferences = document.getElementById('zone-preferences-data').value;
        console.log('Обновленные предпочтения:', updatedPreferences);
        
        // Закрываем модальное окно
        $('#editZoneModal').modal('hide');
        
        // Показываем уведомление
        showToast('Зона успешно обновлена', 'success');
    });

    // Обновление данных о предпочтениях зон
    function updateZonePreferencesData() {
        const preferencesDataInput = document.getElementById('zone-preferences-data');
        
        // Получаем текущие данные
        let preferences = {};
        try {
            preferences = JSON.parse(preferencesDataInput.value || '{}');
        } catch (e) {
            console.error('Ошибка при разборе данных о предпочтениях:', e);
        }
        
        // Проверяем существующие зоны
        const zones = document.querySelectorAll('#zonesTable tbody tr');
        const zoneIndices = Array.from(zones).map(row => row.dataset.zoneIndex);
        
        // Удаляем предпочтения для несуществующих зон
        const updatedPreferences = {};
        for (const zoneIndex in preferences) {
            if (zoneIndices.includes(zoneIndex)) {
                updatedPreferences[zoneIndex] = preferences[zoneIndex];
            }
        }
        
        // Сохраняем обновленные данные
        preferencesDataInput.value = JSON.stringify(updatedPreferences);
    }

    // Обработчики для удаления референсов
    document.querySelectorAll('.delete-reference').forEach(btn => {
        btn.addEventListener('click', function() {
            if (confirm('Вы действительно хотите удалить этот референс?')) {
                const url = this.dataset.url;
                const item = this.closest('.reference-item');
                
                // AJAX запрос для удаления
                fetch(`/commercial/${{{ $brief->id }}}/delete-file`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        file_url: url,
                        type: 'reference'
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        item.remove();
                        
                        // Проверяем, остались ли еще референсы
                        const container = document.getElementById('references-container');
                        if (container.querySelectorAll('.reference-item').length === 0) {
                            container.innerHTML = `
                                <div class="text-center py-5">
                                    <i class="far fa-images fa-4x text-muted mb-3"></i>
                                   
                                    <p class="text-muted">Референсы появятся, когда пользователь загрузит их</p>
                                </div>
                            `;
                        }
                        
                        showToast('Референс успешно удален', 'success');
                    } else {
                        showToast('Ошибка при удалении референса', 'error');
                    }
                })
                .catch(error => {
                    console.error('Ошибка:', error);
                    showToast('Произошла ошибка при удалении референса', 'error');
                });
            }
        });
    });

    // Обработчики для удаления документов
    document.querySelectorAll('.delete-document').forEach(btn => {
        btn.addEventListener('click', function() {
            if (confirm('Вы действительно хотите удалить этот документ?')) {
                const url = this.dataset.url;
                const item = this.closest('.document-item');
                
                // AJAX запрос для удаления
                fetch(`/commercial/${{{ $brief->id }}}/delete-file`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        file_url: url,
                        type: 'document'
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        item.remove();
                        
                        // Проверяем, остались ли еще документы
                        const container = document.getElementById('documents-container');
                        if (container.querySelectorAll('.document-item').length === 0) {
                            container.innerHTML = `
                                <div class="text-center py-5">
                                    <i class="far fa-file-alt fa-4x text-muted mb-3"></i>
                                   
                                    <p class="text-muted">Документы появятся, когда пользователь загрузит их</p>
                                </div>
                            `;
                        }
                        
                        showToast('Документ успешно удален', 'success');
                    } else {
                        showToast('Ошибка при удалении документа', 'error');
                    }
                })
                .catch(error => {
                    console.error('Ошибка:', error);
                    showToast('Произошла ошибка при удалении документа', 'error');
                });
            }
        });
    });

    // Обработчик для кнопки создания сделки
    document.querySelectorAll('.create-deal-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            if (this.disabled) {
                alert('Для этого брифа уже создана сделка');
                return;
            }
            
            if (confirm('Вы уверены, что хотите создать сделку на основе этого брифа?')) {
                this.disabled = true;
                this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Создание сделки...';
                
                // Получаем значения из атрибутов
                const briefId = this.getAttribute('data-brief-id');
                const clientId = this.getAttribute('data-client-id');
                const briefType = this.getAttribute('data-brief-type') || 'commercial';
                
                console.log('Создание сделки для брифа:', briefId, 'клиент:', clientId, 'тип брифа:', briefType);
                
                // AJAX запрос для создания сделки
                fetch('{{ route("deals.create-from-brief") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        brief_id: briefId,
                        brief_type: briefType,
                        client_id: clientId
                    })
                })
                .then(response => {
                    console.log('Статус ответа:', response.status);
                    return response.json();
                })
                .then(data => {
                    console.log('Ответ сервера:', data);
                    if (data.success) {
                        // При успехе перенаправляем на карточку сделок вместо редактирования
                        if (data.redirect_url) {
                            window.location.href = data.redirect_url;
                        } else {
                            // Перенаправляем на карточку сделок
                            window.location.href = '/deal-cardinator';
                        }
                    } else {
                        this.disabled = false;
                        this.innerHTML = '<i class="fas fa-handshake"></i> Создать сделку по брифу';
                        alert(data.message || 'Ошибка при создании сделки');
                    }
                })
                .catch(error => {
                    console.error('Ошибка:', error);
                    this.disabled = false;
                    this.innerHTML = '<i class="fas fa-handshake"></i> Создать сделку по брифу';
                    alert('Произошла ошибка при создании сделки');
                });
            }
        });
    });

    // Функция для отображения уведомлений
    function showNotification(title, message, callback) {
        // Используем Bootstrap modal вместо SweetAlert
        const modal = document.getElementById('notificationModal');
        const modalTitle = document.getElementById('notificationModalLabel');
        const modalBody = document.getElementById('notificationModalBody');
        const redirectBtn = document.getElementById('redirectBtn');
        
        modalTitle.textContent = title;
        modalBody.textContent = message;
        
        if (callback && typeof callback === 'function') {
            redirectBtn.style.display = 'block';
            redirectBtn.onclick = function() {
                // Закрываем модальное окно перед выполнением callback
                if (typeof bootstrap !== 'undefined') {
                    const modalInstance = bootstrap.Modal.getInstance(modal);
                    if (modalInstance) {
                        modalInstance.hide();
                    }
                } else {
                    // Если bootstrap недоступен, просто скрываем модальное окно
                    modal.style.display = 'none';
                    modal.classList.remove('show');
                    document.body.classList.remove('modal-open');
                    const backdrop = document.querySelector('.modal-backdrop');
                    if (backdrop) backdrop.remove();
                }
                
                // Затем выполняем callback
                setTimeout(callback, 300);
            };
        } else {
            redirectBtn.style.display = 'none';
        }
        
        // Показываем модальное окно через Bootstrap API или напрямую
        if (typeof bootstrap !== 'undefined') {
            const modalInstance = new bootstrap.Modal(modal);
            modalInstance.show();
        } else {
            // Ручное открытие модального окна, если bootstrap недоступен
            modal.style.display = 'block';
            modal.classList.add('show');
            document.body.classList.add('modal-open');
            const backdrop = document.createElement('div');
            backdrop.className = 'modal-backdrop fade show';
            document.body.appendChild(backdrop);
        }
    }

    // Функция для отображения уведомлений
    function showToast(message, type = 'success') {
        const toastId = 'toast-' + Date.now();
        const backgroundColor = type === 'success' ? '#d4edda' : 
                               type === 'error' ? '#f8d7da' :
                               type === 'info' ? '#d1ecf1' : '#fff3cd';
        const textColor = type === 'success' ? '#155724' : 
                         type === 'error' ? '#721c24' :
                         type === 'info' ? '#0c5460' : '#856404';
        const iconClass = type === 'success' ? 'fa-check-circle' :
                          type === 'error' ? 'fa-exclamation-circle' :
                          type === 'info' ? 'fa-info-circle' : 'fa-exclamation-triangle';
        
        const toastHTML = `
            <div id="${toastId}" class="toast" role="alert" aria-live="assertive" aria-atomic="true" style="position: fixed; top: 20px; right: 20px; z-index: 9999;">
                <div class="toast-header" style="background-color: ${backgroundColor}; color: ${textColor};">
                    <i class="fas ${iconClass} mr-2"></i>
                    <strong class="mr-auto">${type === 'success' ? 'Успешно' : type === 'error' ? 'Ошибка' : type === 'info' ? 'Информация' : 'Предупреждение'}</strong>
                    <button type="button" class="ml-2 mb-1 close" data-dismiss="toast" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="toast-body" style="background-color: #fff;">
                    ${message}
                </div>
            </div>
        `;
        
        // Добавляем тост в DOM
        document.body.insertAdjacentHTML('beforeend', toastHTML);
        
        // Показываем тост
        const toast = document.getElementById(toastId);
        
        if (typeof $ !== 'undefined' && typeof $.fn.toast === 'function') {
            // Если доступен Bootstrap Toast API
            $(toast).toast({delay: 5000}).toast('show');
            
            // Удаляем элемент после скрытия
            $(toast).on('hidden.bs.toast', function() {
                this.remove();
            });
        } else {
            // Если Bootstrap Toast API недоступен, используем собственную реализацию
            toast.style.opacity = '1';
            
            setTimeout(() => {
                toast.style.opacity = '0';
                setTimeout(() => {
                    toast.remove();
                }, 300);
            }, 5000);
        }
    }
    
    // Исправление для формы обновления коммерческого брифа
    const briefForm = document.getElementById('briefEditForm');
    if (briefForm) {
        // Удаляем любые обработчики событий, которые могут отправлять форму через AJAX
        const oldSubmitHandler = briefForm.onsubmit;
        briefForm.onsubmit = function(e) {
            // Добавляем индикатор загрузки на кнопку отправки формы
            const submitButton = this.querySelector('button[type="submit"]');
            if (submitButton) {
                const originalText = submitButton.innerHTML;
                submitButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Сохранение...';
                submitButton.disabled = true;
                
                // Сохраняем оригинальный текст кнопки для восстановления в случае ошибки
                submitButton.setAttribute('data-original-text', originalText);
                
                // Возвращаем обычный вид кнопки, если форма не отправится по какой-то причине через 5 секунд
                setTimeout(() => {
                    if (!this.classList.contains('submitted')) {
                        submitButton.innerHTML = originalText;
                        submitButton.disabled = false;
                    }
                }, 5000);
            }
            
            this.classList.add('submitted');
            
            // Позволяем форме отправиться обычным способом (без AJAX)
            return true;
        };
    }

    // Исправляем функцию showToast, чтобы использовать глобальную функцию, если она доступна
    function showToast(message, type = 'success') {
        if (typeof window.showToast === 'function') {
            window.showToast(message, type);
        } else {
            const toastId = 'toast-' + Date.now();
            const backgroundColor = type === 'success' ? '#d4edda' : 
                                   type === 'error' ? '#f8d7da' :
                                   type === 'info' ? '#d1ecf1' : '#fff3cd';
            const textColor = type === 'success' ? '#155724' : 
                             type === 'error' ? '#721c24' :
                             type === 'info' ? '#0c5460' : '#856404';
            const iconClass = type === 'success' ? 'fa-check-circle' :
                              type === 'error' ? 'fa-exclamation-circle' :
                              type === 'info' ? 'fa-info-circle' : 'fa-exclamation-triangle';
            
            const toastHTML = `
                <div id="${toastId}" class="toast" role="alert" aria-live="assertive" aria-atomic="true" style="position: fixed; top: 20px; right: 20px; z-index: 9999;">
                    <div class="toast-header" style="background-color: ${backgroundColor}; color: ${textColor};">
                        <i class="fas ${iconClass} mr-2"></i>
                        <strong class="mr-auto">${type === 'success' ? 'Успешно' : type === 'error' ? 'Ошибка' : type === 'info' ? 'Информация' : 'Предупреждение'}</strong>
                        <button type="button" class="ml-2 mb-1 close" data-dismiss="toast" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="toast-body" style="background-color: #fff;">
                        ${message}
                    </div>
                </div>
            `;
            
            // Поиск toast-container или создание его
            let toastContainer = document.querySelector('.toast-container');
            if (!toastContainer) {
                toastContainer = document.createElement('div');
                toastContainer.className = 'toast-container';
                toastContainer.style.cssText = 'position: fixed; top: 15px; right: 15px; z-index: 1060;';
                document.body.appendChild(toastContainer);
            }
            
            // Добавляем тост в контейнер
            toastContainer.insertAdjacentHTML('beforeend', toastHTML);
            
            // Показываем тост
            const toast = document.getElementById(toastId);
            
            if (typeof $ !== 'undefined' && typeof $.fn.toast === 'function') {
                // Если доступен Bootstrap Toast API
                $(toast).toast({delay: 5000}).toast('show');
                
                // Удаляем элемент после скрытия
                $(toast).on('hidden.bs.toast', function() {
                    this.remove();
                });
            } else {
                // Если Bootstrap Toast API недоступен, используем собственную реализацию
                toast.style.opacity = '1';
                
                setTimeout(() => {
                    toast.style.opacity = '0';
                    setTimeout(() => {
                        toast.remove();
                    }, 300);
                }, 5000);
            }
        }
    }

    // Обработчик отправки формы редактирования брифа
    const briefEditForm = document.getElementById('briefEditForm');
    if (briefEditForm) {
        briefEditForm.addEventListener('submit', function(e) {
            // Убедимся, что данные о предпочтениях корректно сформированы
            const preferencesField = document.getElementById('zone-preferences-data');
            if (preferencesField) {
                try {
                    // Проверяем, валидный ли JSON
                    JSON.parse(preferencesField.value || '{}');
                } catch (error) {
                    e.preventDefault();
                    alert('Ошибка в данных предпочтений. Пожалуйста, проверьте формат данных и попробуйте снова.');
                    console.error('Ошибка JSON в zone-preferences-data:', error);
                    return false;
                }
            }

            // Добавляем анимацию загрузки
            const submitButton = this.querySelector('button[type="submit"]');
            if (submitButton) {
                const originalText = submitButton.innerHTML;
                submitButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Сохранение...';
                submitButton.disabled = true;
                
                // Устанавливаем таймаут для восстановления кнопки в случае ошибки
                setTimeout(() => {
                    if (!this.classList.contains('submitted')) {
                        submitButton.innerHTML = originalText;
                        submitButton.disabled = false;
                    }
                }, 10000); // 10 секунд на отправку и получение ответа
            }
            
            this.classList.add('submitted');
            return true;
        });
    }
});
</script>

<!-- Диагностика загрузки скриптов -->
<script>
    // Эта функция проверяет загрузку необходимых библиотек и выдаёт предупреждение, если что-то не загружено
    function checkDependencies() {
        const issues = [];
        
        if (typeof jQuery === 'undefined') {
            issues.push('jQuery не загружен - вкладки и динамические функции не будут работать');
        }
        
        if (typeof jQuery !== 'undefined' && typeof jQuery.fn.tab === 'undefined') {
            issues.push('Bootstrap JS не загружен - вкладки не будут работать');
        }
        
        if (typeof jQuery !== 'undefined' && typeof jQuery.fn.modal === 'undefined') {
            issues.push('Bootstrap Modal не загружен - модальные окна не будут работать');
        }
        
        if (issues.length > 0) {
            console.error('Обнаружены проблемы с зависимостями:', issues);
            
            // Добавим предупреждение на страницу
            const warningDiv = document.createElement('div');
            warningDiv.style.cssText = 'position:fixed; top:10px; left:50%; transform:translateX(-50%); background:#fff3cd; border:1px solid #ffeeba; padding:10px; border-radius:4px; z-index:9999;';
            warningDiv.innerHTML = `
                <strong>Внимание!</strong> Обнаружены проблемы, которые могут повлиять на работу страницы:
                <ul style="padding-left:20px; margin-bottom:0;">${issues.map(i => `<li>${i}</li>`).join('')}</ul>
            `;
            document.body.appendChild(warningDiv);
            
            // Автоматически скроем через 10 секунд
            setTimeout(() => {
                warningDiv.style.opacity = '0';
                setTimeout(() => warningDiv.remove(), 1000);
            }, 10000);
        }
    }
    
    // Проверим зависимости после полной загрузки страницы
    window.addEventListener('load', checkDependencies);
</script>

<style>

</style>
