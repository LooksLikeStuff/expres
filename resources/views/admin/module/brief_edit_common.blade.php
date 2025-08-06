<div class="container-fluid py-4">
    <!-- Заголовок страницы с действиями -->
    <div class="d-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Редактирование общего брифа <span class="badge badge-primary">#{{ $brief->id }}</span></h1>
        <div class="admin-actions">
            <a href="{{ route('common.show', $brief->id) }}" class="btn btn-sm btn-outline-primary" target="_blank">
                <i class="fas fa-eye"></i> Просмотреть бриф
            </a>
           
            @if($brief->deal_id)
            <div class="deal-exists-notice mt-2">
                <small class="text-warning"><i class="fas fa-exclamation-triangle"></i> Для данного брифа уже создана сделка #{{ $brief->deal_id }}</small>
            </div>
            @endif
        </div>
    </div>

    <form action="{{ route('admin.brief.common.update', $brief->id) }}" method="POST" id="briefEditForm">
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
                <a class="nav-link" id="answers-tab" data-toggle="tab" href="#answers-content" role="tab" 
                   aria-controls="answers-content" aria-selected="false">
                   <i class="fas fa-question-circle mr-2"></i>Ответы на вопросы
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="files-tab" data-toggle="tab" href="#files-content" role="tab" 
                   aria-controls="files-content" aria-selected="false">
                   <i class="fas fa-file-alt mr-2"></i>Файлы и референсы
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="rooms-tab" data-toggle="tab" href="#rooms-content" role="tab" 
                   aria-controls="rooms-content" aria-selected="false">
                   <i class="fas fa-home mr-2"></i>Комнаты и помещения
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
                                            <label for="current_page">Текущая страница</label>
                                            <input type="number" name="current_page" id="current_page" class="form-control" value="{{ $brief->current_page }}" min="1" max="5">
                                            <small class="form-text text-muted">Из 5 страниц</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="card  mb-4">
                            <div class="card-header py-3">
                                <h6 class="m-0 font-weight-bold text-primary">Информация о брифе</h6>
                            </div>
                            <div class="card-body">
                                <div class="form-group">
                                    <label for="article">ID брифа</label>
                                    <input type="text" name="article" id="article" class="form-control" value="{{ $brief->article }}" readonly>
                                    <small class="form-text text-muted">Уникальный идентификатор брифа</small>
                                </div>

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
                                        @if($brief->user_id)
                                        <hr class="my-2">
                                        <div class="d-flex justify-content-between">
                                            <span>Пользователь:</span>
                                            <a href="{{ route('profile.view', $brief->user_id) }}">
                                                <strong>{{ $user ? $user->name : 'Пользователь #'.$brief->user_id }}</strong>
                                            </a>
                                        </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Вкладка ответов на вопросы -->
            <div class="tab-pane fade" id="answers-content" role="tabpanel" aria-labelledby="answers-tab">
                <div class="card  mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Ответы на вопросы</h6>
                    </div>
                    <div class="card-body">
                        <div id="answersAccordion">
                            @php
                            // $answers уже должен содержать структурированные ответы из контроллера
                            $pageTitles = [
                                'page1' => 'Общая информация',
                                'page2' => 'Интерьер: стиль и предпочтения',
                                'page3' => 'Пожелания по помещениям',
                                'page4' => 'Пожелания по отделке помещений',
                                'page5' => 'Пожелания по оснащению помещений',
                            ];
                            
                            // Проверяем наличие ответов
                            $hasAnyAnswers = false;
                            foreach ($answers as $pageKey => $pageData) {
                                if (!empty($pageData)) {
                                    $hasAnyAnswers = true;
                                    break;
                                }
                            }
                            @endphp

                            @if($hasAnyAnswers)
                                @foreach($pageTitles as $pageKey => $pageTitle)
                                    @php
                                    $pageAnswers = $answers[$pageKey] ?? [];
                                    $pageNum = substr($pageKey, 4); // Получаем номер страницы из ключа
                                    $hasAnswersInPage = !empty($pageAnswers);
                                    // Добавляем подсчет количества заполненных ответов
                                    $answersCount = count($pageAnswers);
                                    @endphp
                                    
                                    <div class="card mb-2">
                                        <div class="card-header py-2" id="heading{{ $pageNum }}">
                                            <h2 class="mb-0">
                                                <button class="btn special-style btn-link btn-block text-left d-flex justify-content-between {{ $pageNum != 1 ? 'collapsed' : '' }}" 
                                                        type="button" 
                                                        data-toggle="collapse" 
                                                        data-target="#collapse{{ $pageNum }}" 
                                                        aria-expanded="{{ $pageNum == 1 ? 'true' : 'false' }}" 
                                                        aria-controls="collapse{{ $pageNum }}">
                                                    <div>
                                                        <span class="badge badge-primary mr-2">{{ $pageNum }}</span>
                                                        <span>{{ $pageTitle }}</span>
                                                    </div>
                                                    <div>
                                                        @if($hasAnswersInPage)
                                                            <span class="badge badge-success">
                                                                {{ $answersCount }} {{ trans_choice('ответ|ответа|ответов', $answersCount) }}
                                                                <i class="fas fa-check-circle ml-1"></i>
                                                            </span>
                                                        @else
                                                            <span class="badge badge-secondary">
                                                                Нет ответов
                                                                <i class="fas fa-times-circle ml-1"></i>
                                                            </span>
                                                        @endif
                                                    </div>
                                                </button>
                                            </h2>
                                        </div>

                                        <div id="collapse{{ $pageNum }}" 
                                             class="collapse {{ $pageNum == 1 ? 'show' : '' }}" 
                                             aria-labelledby="heading{{ $pageNum }}" 
                                             data-parent="#answersAccordion">
                                            <div class="card-body">
                                                @if($hasAnswersInPage)
                                                    @foreach($pageAnswers as $question => $answer)
                                                        <div class="card mb-3 bg-light">
                                                            <div class="card-header py-2 bg-light">
                                                                <strong>{{ $question }}</strong>
                                                            </div>
                                                            <div class="card-body py-2">
                                                                @if(is_array($answer))
                                                                    <textarea class="form-control" 
                                                                              name="answers[{{ $pageKey }}][{{ $question }}]" 
                                                                              rows="3">{{ json_encode($answer, JSON_UNESCAPED_UNICODE) }}</textarea>
                                                                @else
                                                                    @if(strlen($answer) > 100)
                                                                        <textarea class="form-control" 
                                                                                name="answers[{{ $pageKey }}][{{ $question }}]" 
                                                                                rows="3">{{ $answer }}</textarea>
                                                                    @else
                                                                        <input type="text" class="form-control" 
                                                                               name="answers[{{ $pageKey }}][{{ $question }}]" 
                                                                               value="{{ $answer }}">
                                                                    @endif
                                                                @endif
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                @else
                                                    <div class="alert alert-info mb-0">
                                                        <i class="fas fa-info-circle mr-2"></i>
                                                        На вопросы этой страницы еще не были даны ответы
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            @else
                                <div class="text-center py-5">
                                    <i class="fas fa-comments fa-4x text-muted mb-3"></i>
                                    <h5>Еще нет ответов на вопросы в этом брифе</h5>
                                    <p class="text-muted">Ответы появятся, когда пользователь заполнит бриф</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Вкладка файлов и референсов -->
            <div class="tab-pane fade" id="files-content" role="tabpanel" aria-labelledby="files-tab">
                <div class="row">
                    <!-- Референсы -->
                    <div class="col-md-6">
                        <div class="card  mb-4">
                            <div class="card-header py-3">
                                <h6 class="m-0 font-weight-bold text-primary">Референсы</h6>
                            </div>
                            <div class="card-body">
                                <div id="references-container">
                                    @php
                                    $references = json_decode($brief->references ?? '[]', true);
                                    @endphp
                                    
                                    @if(is_array($references) && count($references) > 0)
                                        <div class="row references-gallery">
                                            @foreach($references as $index => $reference)
                                                <div class="col-md-4 mb-3 reference-item" data-url="{{ $reference }}">
                                                    <div class="card h-100">
                                                        <div class="card-img-top reference-preview">
                                                            @if(preg_match('/\.(jpg|jpeg|png|gif|webp)$/i', $reference))
                                                                <img src="{{ $reference }}" alt="Референс {{ $index + 1 }}" class="img-fluid">
                                                            @else
                                                                <div class="file-icon text-center p-4">
                                                                    <i class="far fa-file fa-3x text-muted"></i>
                                                                </div>
                                                            @endif
                                                        </div>
                                                        <div class="card-body p-2 d-flex justify-content-between">
                                                            <a href="{{ $reference }}" class="btn btn-sm btn-outline-primary" target="_blank">
                                                                <i class="fas fa-eye"></i>
                                                            </a>
                                                            <button type="button" class="btn btn-sm btn-outline-danger delete-reference" data-url="{{ $reference }}">
                                                                <i class="fas fa-trash"></i>
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    @else
                                        <div class="text-center py-5">
                                            <i class="far fa-images fa-4x text-muted mb-3"></i>
                                          
                                            <p class="text-muted">Референсы появятся, когда пользователь загрузит их</p>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

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

            <!-- Вкладка комнат и помещений -->
            <div class="tab-pane fade" id="rooms-content" role="tabpanel" aria-labelledby="rooms-tab">
                <div class="card  mb-4">
                    <div class="card-header py-3 d-flex justify-content-between align-items-center">
                        <h6 class="m-0 font-weight-bold text-primary">Комнаты и помещения</h6>
                        <button type="button" class="btn btn-sm btn-primary add-room">
                            <i class="fas fa-plus"></i> Добавить комнату
                        </button>
                    </div>
                    <div class="card-body">
                        @php
                        $rooms = json_decode($brief->rooms ?? '[]', true);
                        @endphp

                        @if(is_array($rooms) && count($rooms) > 0)
                            <div class="rooms-container">
                                <div class="row">
                                    @foreach($rooms as $roomKey => $roomName)
                                        <div class="col-md-4 mb-3">
                                            <div class="card">
                                                <div class="card-header bg-light py-2 d-flex justify-content-between align-items-center">
                                                    <h6 class="mb-0 room-title">{{ $roomName }}</h6>
                                                    <button type="button" class="btn btn-sm btn-outline-danger delete-room" data-room-key="{{ $roomKey }}">
                                                        <i class="fas fa-times"></i>
                                                    </button>
                                                </div>
                                                <div class="card-body">
                                                    <div class="form-group mb-0">
                                                        <label for="room-{{ $roomKey }}">Название комнаты</label>
                                                        <input type="text" id="room-{{ $roomKey }}" name="rooms[{{ $roomKey }}]" class="form-control" value="{{ $roomName }}">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @else
                            <div class="text-center py-5 rooms-empty-state">
                                <i class="fas fa-home fa-4x text-muted mb-3"></i>
                                <h5>Нет добавленных комнат</h5>
                                <p class="text-muted">Нажмите кнопку "Добавить комнату", чтобы начать</p>
                            </div>
                        @endif
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
                            data-brief-type="common"
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

<!-- Модальное окно для добавления комнаты -->
<div class="modal fade" id="addRoomModal" tabindex="-1" aria-labelledby="addRoomModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="addRoomModalLabel">Добавление новой комнаты</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <div class="form-group">
          <label for="newRoomName">Название комнаты</label>
          <input type="text" class="form-control" id="newRoomName" placeholder="Введите название комнаты">
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Отмена</button>
        <button type="button" class="btn btn-primary" id="confirmAddRoom">Добавить</button>
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
        jqueryScript.onload = initBootstrapTabs;
        document.head.appendChild(jqueryScript);
    } else {
        // Проверяем наличие Bootstrap Tabs API
        if (typeof jQuery.fn.tab === 'undefined') {
            console.error('Bootstrap JS не загружен! Загружаем Bootstrap JS для работы вкладок');
            const bootstrapScript = document.createElement('script');
            bootstrapScript.src = 'https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js';
            bootstrapScript.onload = initBootstrapTabs;
            document.head.appendChild(bootstrapScript);
        } else {
            // Если всё нормально загружено, инициализируем вкладки
            initBootstrapTabs();
        }
    }

    function initBootstrapTabs() {
        // Инициализация вкладок с использованием Bootstrap Tab API
        $('#briefTabs a').on('click', function(e) {
            e.preventDefault();
            $(this).tab('show');
        });

        // Резервный механизм на случай, если Bootstrap Tab API не сработает
        const tabItems = document.querySelectorAll('#briefTabs .nav-link');
        tabItems.forEach(tab => {
            tab.addEventListener('click', function(e) {
                // Если Bootstrap Tab API работает, позволим ему обработать событие
                if (typeof jQuery.fn.tab !== 'undefined') return;

                e.preventDefault();
                
                // Ручное переключение вкладок
                const targetId = this.getAttribute('href');
                
                // Деактивировать все вкладки
                document.querySelectorAll('#briefTabs .nav-link').forEach(t => {
                    t.classList.remove('active');
                    t.setAttribute('aria-selected', 'false');
                });
                
                // Скрыть все панели контента
                document.querySelectorAll('.tab-pane').forEach(p => {
                    p.classList.remove('show', 'active');
                });
                
                // Активировать выбранную вкладку
                this.classList.add('active');
                this.setAttribute('aria-selected', 'true');
                
                // Показать соответствующий контент
                const targetPane = document.querySelector(targetId);
                if (targetPane) {
                    targetPane.classList.add('show', 'active');
                }
            });
        });
    }

    function addDeleteRoomHandlers() {
        document.querySelectorAll('.delete-room').forEach(button => {
            button.onclick = function() {
                if (confirm('Вы уверены, что хотите удалить эту комнату?')) {
                    const roomCol = this.closest('.col-md-4');
                    roomCol.remove();
                    
                    // Если комнат не осталось, показываем пустое состояние
                    if (document.querySelectorAll('.rooms-container .row .col-md-4').length === 0) {
                        const emptyState = document.createElement('div');
                        emptyState.className = 'text-center py-5 rooms-empty-state';
                        emptyState.innerHTML = `
                            <i class="fas fa-home fa-4x text-muted mb-3"></i>
                            <h5>Нет добавленных комнат</h5>
                            <p class="text-muted">Нажмите кнопку "Добавить комнату", чтобы начать</p>
                        `;
                        const roomsContainer = document.querySelector('.rooms-container');
                        if (roomsContainer) {
                            roomsContainer.parentNode.replaceChild(emptyState, roomsContainer);
                        }
                    }
                }
            };
        });
    }

    // Остальной код инициализации страницы
    // Кнопка "Добавить комнату"
    document.querySelector('.add-room')?.addEventListener('click', function() {
        $('#addRoomModal').modal('show');
    });
    
    // Подтверждение добавления комнаты
    document.getElementById('confirmAddRoom')?.addEventListener('click', function() {
        const roomName = document.getElementById('newRoomName').value.trim();
        
        if (!roomName) {
            alert('Введите название комнаты');
            return;
        }
        
        const roomKey = 'new_' + Date.now();
        const roomsContainer = document.querySelector('.rooms-container');
        const emptyState = document.querySelector('.rooms-empty-state');
        
        // Если контейнер для комнат не существует, создаем его
        if (!roomsContainer) {
            const container = document.createElement('div');
            container.className = 'rooms-container';
            
            const row = document.createElement('div');
            row.className = 'row';
            container.appendChild(row);
            
            // Удаляем сообщение "Нет комнат"
            if (emptyState) {
                emptyState.parentNode.replaceChild(container, emptyState);
            } else {
                document.querySelector('#rooms-content .card-body').appendChild(container);
            }
        }
        
        // Получаем строку для комнат или создаем, если её нет
        const roomsRow = document.querySelector('.rooms-container .row');
        
        // Создаем новую комнату и добавляем в контейнер
        const roomCol = document.createElement('div');
        roomCol.className = 'col-md-4 mb-3';
        roomCol.innerHTML = `
            <div class="card">
                <div class="card-header bg-light py-2 d-flex justify-content-between align-items-center">
                    <h6 class="mb-0 room-title">${roomName}</h6>
                    <button type="button" class="btn btn-sm btn-outline-danger delete-room" data-room-key="${roomKey}">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div class="card-body">
                    <div class="form-group mb-0">
                        <label for="room-${roomKey}">Название комнаты</label>
                        <input type="text" id="room-${roomKey}" name="rooms[${roomKey}]" class="form-control" value="${roomName}">
                    </div>
                </div>
            </div>
        `;
        
        roomsRow.appendChild(roomCol);
        
        // Добавляем обработчики для удаления новой комнаты
        addDeleteRoomHandlers();
        
        // Закрываем модальное окно и очищаем поле ввода
        $('#addRoomModal').modal('hide');
        document.getElementById('newRoomName').value = '';
    });
    
    // ... оставшийся код функций ...
    // Функция для добавления обработчиков удаления комнат
    addDeleteRoomHandlers();
    
    // Инициализация обработчиков удаления комнат
    addDeleteRoomHandlers();
    
    // Обработчики для удаления файлов
    document.querySelectorAll('.delete-document').forEach(btn => {
        btn.addEventListener('click', function() {
            if (confirm('Вы действительно хотите удалить этот документ?')) {
                const url = this.dataset.url;
                const item = this.closest('.document-item');
                
                // AJAX запрос для удаления файла
                fetch('{{ route("common.delete-file", $brief->id) }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
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
                        
                        // Проверить, остались ли еще документы
                        const container = document.getElementById('documents-container');
                        if (container.querySelectorAll('.document-item').length === 0) {
                            container.innerHTML = `
                                <div class="text-center py-5">
                                    <i class="far fa-file-alt fa-4x text-muted mb-3"></i>
                                   
                                    <p class="text-muted">Документы появятся, когда пользователь загрузит их</p>
                                </div>
                            `;
                        }
                        
                        // Показать уведомление
                        if (typeof window.showToast === 'function') {
                            window.showToast('Документ успешно удален', 'success');
                        } else {
                            alert('Документ успешно удален');
                        }
                    } else {
                        if (typeof window.showToast === 'function') {
                            window.showToast('Ошибка при удалении документа', 'error');
                        } else {
                            alert('Ошибка при удалении документа');
                        }
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    if (typeof window.showToast === 'function') {
                        window.showToast('Произошла ошибка при удалении файла', 'error');
                    } else {
                        alert('Произошла ошибка при удалении файла');
                    }
                });
            }
        });
    });

    // Остальные обработчики для референсов и создания сделки остаются без изменений

    // Исправление для формы обновления брифа - гарантируем традиционную отправку формы
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

    // Обработчик для кнопки создания сделки
    document.querySelectorAll('.create-deal-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const briefId = this.getAttribute('data-brief-id');
            const clientId = this.getAttribute('data-client-id');
            const briefType = this.getAttribute('data-brief-type') || 'common';
            
            if (!briefId || !clientId) {
                showToast('Ошибка: не указан ID брифа или клиента', 'error');
                return;
            }
            
            // Отключаем кнопку на время запроса
            this.disabled = true;
            const originalText = this.innerHTML;
            this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Создание...';
            
            // Получаем CSRF токен
            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            
            // Отправляем AJAX запрос на создание сделки
            fetch('/deal/create-from-brief', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    brief_id: briefId,
                    client_id: clientId,
                    brief_type: briefType
                })
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Ошибка сервера: ' + response.status);
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    showToast('Сделка успешно создана!', 'success');
                    // При успехе перенаправляем на страницу сделок или показываем ссылку
                    setTimeout(() => {
                        window.location.href = data.redirect_url || '/admin';
                    }, 1500);
                } else {
                    showToast('Ошибка: ' + data.message, 'error');
                    this.disabled = false;
                    this.innerHTML = originalText;
                }
            })
            .catch(error => {
                console.error('Ошибка при создании сделки:', error);
                showToast('Ошибка при создании сделки: ' + error.message, 'error');
                this.disabled = false;
                this.innerHTML = originalText;
            });
        });
    });
    
    // Обработчик для кнопки создания сделки
    document.querySelectorAll('.create-deal-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            if (this.disabled) {
                showNotification('Информация', 'Для этого брифа уже создана сделка');
                return;
            }
            
            if (confirm('Вы уверены, что хотите создать сделку на основе этого брифа?')) {
                this.disabled = true;
                this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Создание сделки...';
                
                // Получаем значения из атрибутов
                const briefId = this.getAttribute('data-brief-id');
                const clientId = this.getAttribute('data-client-id');
                
                console.log('Создание сделки для брифа:', briefId, 'клиент:', clientId);
                
                // AJAX запрос для создания сделки
                fetch('{{ route("deals.create-from-brief") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        brief_id: briefId,
                        brief_type: 'common', // Изменено с 'commercial' на 'common'
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
                        showNotification('Успех', data.message || 'Сделка успешно создана', function() {
                            // Перенаправляем на страницу сделки, если указан URL
                            if (data.redirect_url) {
                                window.location.href = data.redirect_url;
                            } else {
                                // Обновляем страницу
                                window.location.reload();
                            }
                        });
                    } else {
                        this.disabled = false;
                        this.innerHTML = '<i class="fas fa-handshake"></i> Создать сделку по брифу';
                        showNotification('Ошибка', data.message || 'Ошибка при создании сделки');
                    }
                })
                .catch(error => {
                    console.error('Ошибка:', error);
                    this.disabled = false;
                    this.innerHTML = '<i class="fas fa-handshake"></i> Создать сделку по брифу';
                    showNotification('Ошибка', 'Произошла ошибка при создании сделки');
                });
            }
        });
    });

    // Добавляем специальную обработку отправки формы для гарантии правильного сбора данных
    const briefForm = document.getElementById('briefEditForm');
    if (briefForm) {
        briefForm.addEventListener('submit', function(e) {
            // Предотвращаем стандартную отправку формы
            e.preventDefault();

            // Собираем все введенные данные
            const formData = new FormData(this);
            
            // Собираем ответы из textarea в структурированный объект
            const answers = {};
            document.querySelectorAll('textarea[name^="answers["]').forEach(textarea => {
                const nameParts = textarea.name.match(/answers\[(page\d+)\]\[(.*?)\]/);
                if (nameParts && nameParts.length === 3) {
                    const page = nameParts[1];
                    const question = nameParts[2];
                    
                    if (!answers[page]) {
                        answers[page] = {};
                    }
                    answers[page][question] = textarea.value;
                }
            });
            
            // Добавляем собранные ответы в JSON формате
            formData.append('answers_json', JSON.stringify(answers));
            
            // Собираем данные о комнатах
            const rooms = {};
            document.querySelectorAll('input[name^="rooms["]').forEach(input => {
                const roomKey = input.name.match(/rooms\[(.*?)\]/)[1];
                rooms[roomKey] = input.value;
            });
            
            // Добавляем комнаты в виде JSON
            formData.append('rooms_json', JSON.stringify(rooms));
            
            // Показываем индикатор загрузки
            const submitBtn = this.querySelector('button[type="submit"]');
            if (submitBtn) {
                const originalText = submitBtn.innerHTML;
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Сохранение...';
                
                // Восстановление кнопки через 10 секунд (если что-то пошло не так)
                setTimeout(() => {
                    if (submitBtn.disabled) {
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = originalText;
                    }
                }, 10000);
            }
            
            // Отправляем форму через fetch API для лучшего контроля
            fetch(this.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => {
                if (!response.ok) {
                    return response.text().then(text => {
                        throw new Error('Ошибка сервера: ' + text);
                    });
                }
                return response.text();
            })
            .then(html => {
                // Показываем уведомление об успехе
                showNotification('Успех', 'Бриф успешно обновлен', function() {
                    // Перезагружаем страницу через 1 секунду
                    setTimeout(() => {
                        location.reload();
                    }, 1000);
                });
            })
            .catch(error => {
                console.error('Ошибка при отправке формы:', error);
                showNotification('Ошибка', 'Произошла ошибка при сохранении: ' + error.message);
                
                // Восстанавливаем кнопку отправки
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalText;
                }
            });
        });
    }
    
    // Функция для отображения уведомлений
    function showToast(message, type = 'info') {
        const toastId = 'toast-' + Date.now();
        const toast = document.createElement('div');
        toast.id = toastId;
        toast.className = 'toast';
        toast.style.position = 'fixed';
        toast.style.top = '20px';
        toast.style.right = '20px';
        toast.style.minWidth = '250px';
        toast.style.padding = '15px 20px';
        toast.style.background = type === 'success' ? '#1cc88a' : 
                                type === 'error' ? '#e74a3b' : '#36b9cc';
        toast.style.color = '#fff';
        toast.style.borderRadius = '5px';
        toast.style.boxShadow = '0 0.46875rem 2.1875rem rgba(4,9,20,0.03), 0 0.9375rem 1.40625rem rgba(4,9,20,0.03), 0 0.25rem 0.53125rem rgba(4,9,20,0.05), 0 0.125rem 0.1875rem rgba(4,9,20,0.03)';
        toast.style.zIndex = '9999';
        toast.style.opacity = '0';
        toast.style.transition = 'opacity 0.3s ease';
        
        toast.innerHTML = `
            <div style="display: flex; align-items: center; justify-content: space-between;">
                <span>${message}</span>
                <button type="button" style="background: none; border: none; color: white; font-size: 20px; cursor: pointer; padding: 0; margin-left: 10px;" onclick="document.getElementById('${toastId}').remove();">&times;</button>
            </div>
        `;
        
        document.body.appendChild(toast);
        
        // Показываем toast с анимацией
        setTimeout(() => {
            toast.style.opacity = '1';
        }, 10);
        
        // Скрываем и удаляем через 5 секунд
        setTimeout(() => {
            toast.style.opacity = '0';
            setTimeout(() => {
                if (toast.parentNode) {
                    toast.parentNode.removeChild(toast);
                }
            }, 300);
        }, 5000);
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
/* Дополнительные стили, специфичные для страницы брифа */
.references-gallery .card-img-top {
    height: 140px;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
    background-color: #f8f9fc;
}

.references-gallery .card-img-top img {
    max-height: 100%;
    max-width: 100%;
    object-fit: contain;
}

.room-title {
    max-width: 180px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

/* Toast стили (в случае если Bootstrap Toast не работает) */
.toast {
    max-width: 350px;
    overflow: hidden;
    font-size: .875rem;
    background-clip: padding-box;
    border: 1px solid rgba(0,0,0,.1);
    box-: 0 0.25rem 0.75rem rgba(0,0,0,.1);
    backdrop-filter: blur(10px);
    opacity: 0;
    border-radius: .25rem;
    transition: opacity .15s linear;
}

.toast.show {
    opacity: 1;
}

.toast-header {
    display: flex;
    align-items: center;
    padding: .25rem .75rem;
    background-clip: padding-box;
    border-bottom: 1px solid rgba(0,0,0,.05);
}

.toast-body {
    padding: .75rem;
}

/* Исправления для адаптивности */
@media (max-width: 768px) {
    .references-gallery .col-md-4 {
        margin-bottom: 15px;
    }
    
    .document-item .text-truncate {
        max-width: 120px;
    }
}

/* Очень важный стиль для исправления вкладок */
#briefTabs .nav-link {
    cursor: pointer;
}
</style>


 <script>
        $(document).ready(function() {
            // Обработчик нажатия на кнопку создания сделки из брифа
            $('.create-deal-btn').on('click', function() {
                var briefId = $(this).data('brief-id');
                var clientId = $(this).data('client-id');
                var button = $(this);
                
                if (button.prop('disabled')) {
                    return false;
                }
                
                // Блокируем кнопку на время выполнения запроса
                button.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Создание сделки...');
                
                // Отправляем AJAX-запрос для создания сделки
                $.ajax({
                    url: '{{ route("deals.create-from-brief") }}',
                    method: 'POST',
                    data: {
                        brief_id: briefId,
                        client_id: clientId,
                        brief_type: 'common', // Добавляем тип брифа
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        if (response.success) {
                            // Просто перезагружаем страницу или перенаправляем на новую сделку
                            if (response.redirect_url) {
                                window.location.href = response.redirect_url;
                            } else {
                                window.location.reload();
                            }
                        } else {
                            button.prop('disabled', false).html('<i class="fas fa-handshake"></i> Создать сделку по брифу');
                            alert(response.message || 'Не удалось создать сделку');
                        }
                    },
                    error: function(xhr) {
                        button.prop('disabled', false).html('<i class="fas fa-handshake"></i> Создать сделку по брифу');
                        var errorMessage = 'Произошла ошибка при создании сделки';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMessage = xhr.responseJSON.message;
                        }
                        alert(errorMessage);
                    }
                });
            });
        });
    </script>

<script>
    $(document).ready(function() {
        // Обработчик нажатия на кнопку создания сделки из брифа
        $('.create-deal-btn').on('click', function() {
            // ...existing code...
            
            // Отправляем AJAX-запрос для создания сделки
            $.ajax({
                url: '{{ route("deals.create-from-brief") }}',
                method: 'POST',
                data: {
                    brief_id: briefId,
                    client_id: clientId,
                    brief_type: briefType,
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    if (response.success) {
                        // При успехе перенаправляем на карточку сделок вместо редактирования
                        if (response.redirect_url) {
                            window.location.href = response.redirect_url;
                        } else {
                            // Перенаправляем на карточку сделок
                            window.location.href = '/deal-cardinator';
                        }
                    } else {
                        button.prop('disabled', false).html('<i class="fas fa-handshake"></i> Создать сделку по брифу');
                        alert(response.message || 'Не удалось создать сделку');
                    }
                },
                // ...existing code...
            });
        });
        
        // ...existing code...
    });
</script>