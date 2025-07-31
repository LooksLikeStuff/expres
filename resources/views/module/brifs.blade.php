@if ($activeBrifs->isEmpty() && $inactiveBrifs->isEmpty())
    {{-- Если пользователь не имеет никаких брифов --}}

    <form action="{{ route('brifs.store') }}" method="POST" class="div__create_form" id="step-3" >
        @csrf
        <input type="hidden" name="brif_type" id="brif_type_input" value="">
        <div class="div__create_block">
            <h1>
                <span class="Jikharev">Добро пожаловать!</span>
            </h1>
            <p><strong>Дорогой клиент,</strong> для продолжения требуется пройти <strong>бриф-опросник</strong> </p>
            <div class="button__create__brifs flex gap3" id="step-8">
            <button type="button" class="button__icon" data-brif-type="common" onclick="setBrifTypeAndSubmit('common')"><span>Создать Общий бриф </span> <img src="/storage/icon/plus.svg" alt=""></button>
            <button type="button" class="button__icon" data-brif-type="commercial" onclick="setBrifTypeAndSubmit('commercial')"><span>Создать Коммерческий бриф </span> <img src="/storage/icon/plus.svg" alt=""></button>
        </div>
        </div>
    </form>
    <script>
        window.onload = function() {
            console.log("Размер экрана:", window.innerWidth);
    
            if (window.innerWidth > 768) {
                console.log("Проверка обучения для десктопа...");
                if (!localStorage.getItem('tutorial_seen_desktop')) {
                    console.log("Запуск обучения для десктопа...");
                    const intro = introJs();
                    intro.setOptions({
                        steps: [{
                                element: '#step-1',
                                intro: 'Модульный контент - это основная часть интерфейса.',
                                position: 'bottom'
                            },
                            {
                                element: '#step-2',
                                intro: 'Панель вкладок.',
                                position: 'right'
                            },
                            {
                                element: '#step-3',
                                intro: 'Главная страница.',
                                position: 'right'
                            },
                            {
                                element: '#step-4',
                                intro: 'Вкладка БРИФЫ.',
                                position: 'right'
                            },
                            {
                                element: '#step-5',
                                intro: 'Вкладка Сделка.',
                                position: 'right'
                            },
                            {
                                element: '#step-6',
                                intro: 'Вкладка Мой профиль.',
                                position: 'top'
                            },
                            {
                                element: '#step-7',
                                intro: 'Вкладка Поддержка.',
                                position: 'top'
                            },
                            {
                                element: '#step-8',
                                intro: 'Кнопки которые отвечают за зполнение бриф-опросника.',
                                position: 'top'
                            }
                            
                        ],
                        showStepNumbers: true,
                        exitOnOverlayClick: false,
                        showButtons: true,
                        nextLabel: 'Далее',
                        prevLabel: 'Назад',
                    });
                    intro.start();
                    localStorage.setItem('tutorial_seen_desktop', 'true');
                }
            } else {
                console.log("Проверка обучения для мобильных устройств...");
                if (!localStorage.getItem('tutorial_seen_mobile')) {
                    console.log("Запуск обучения для мобильных устройств...");
                    const intro = introJs();
                    intro.setOptions({
                        steps: [{
                                element: '#step-mobile-1',
                                intro: 'Это основная часть интерфейса.',
                                position: 'bottom'
                            },
                            {
                                element: '#step-mobile-2',
                                intro: 'Панель навигации.',
                                position: 'bottom'
                            },
                            {
                                element: '#step-3',
                                intro: 'Главная страница.',
                                position: 'right'
                            },
                            {
                                element: '#step-mobile-4',
                                intro: 'Вкладка БРИФЫ.',
                                position: 'right'
                            },
                            {
                                element: '#step-mobile-5',
                                intro: 'Вкладка Сделка.',
                                position: 'right'
                            },
                            {
                                element: '#step-mobile-6',
                                intro: 'Вкладка Мой профиль.',
                                position: 'top'
                            },
                            {
                                element: '#step-mobile-7',
                                intro: 'Вкладка Поддержка.',
                                position: 'top'
                            }
                            ,
                            {
                                element: '#step-8',
                                intro: 'Кнопки которые отвечают за зполнение бриф-опросника.',
                                position: 'top'
                            }
                        ],
                        showStepNumbers: true,
                        exitOnOverlayClick: false,
                        showButtons: true,
                        nextLabel: 'Далее',
                        prevLabel: 'Назад',
                    });
                    intro.start();
                    localStorage.setItem('tutorial_seen_mobile', 'true');
                }
            }
        };
    
        // Функция для сброса обучения
        function clearTutorialData() {
            console.log('Очистка данных обучения...');
            localStorage.removeItem('tutorial_seen_desktop');
            localStorage.removeItem('tutorial_seen_mobile');
    
            location.reload();
        }

        // Функция для установки типа брифа и отправки формы
        function setBrifTypeAndSubmit(briefType) {
            console.log('Установка типа брифа:', briefType);
            
            // Устанавливаем значение в скрытое поле
            const hiddenInput = document.getElementById('brif_type_input');
            if (hiddenInput) {
                hiddenInput.value = briefType;
                console.log('Тип брифа установлен в скрытое поле:', briefType);
                
                // Отправляем форму
                const form = document.getElementById('step-3');
                if (form) {
                    console.log('Отправка формы...');
                    form.submit();
                } else {
                    console.error('Форма не найдена!');
                }
            } else {
                console.error('Скрытое поле brif_type_input не найдено!');
            }
        }
    </script>
    <!-- Кнопка сброса -->
{{-- <div class="question_class-button">
    <button onclick="clearTutorialData()">
        <img src="/storage/icon/qustion.svg" alt="Сбросить обучение">
    </button>
</div> --}}
@else
    {{-- Если у пользователя есть хотя бы один бриф --}}

    <div class="brifs wow fadeInLeft" data-wow-duration="1.5s" data-wow-delay="1.5s" id="brifs">
        <h1 class="flex">
            Ваши брифы
        </h1>

        <div class="brifs__button__create flex">
            <button class="button__icon" onclick="window.location.href='{{ route('common.create') }}'"><span>Создать Общий бриф </span> <img src="/storage/icon/plus.svg" alt=""></button>
            <button class="button__icon" onclick="window.location.href='{{ route('commercial.create') }}'"><span>Создать Коммерческий бриф </span> <img src="/storage/icon/plus.svg" alt=""></button>
        </div>
    </div>

    <div class="brifs__body wow fadeInLeft" data-wow-duration="1.5s" data-wow-delay="1.5s">
        <!-- Активные брифы -->
        <div class="brifs__section">
            <h2>Активные брифы</h2>

            @if ($activeBrifs->isEmpty())
                <ul class="brifs__list brifs__list__null">
                    <li class="brif" onclick="window.location.href='{{ route('common.create') }}'">
                        <p>Создать Общий бриф</p>
                    </li>
                </ul>
            @else
                <ul class="brifs__list">
                    @foreach ($activeBrifs as $brif)
                    <li class="brif"
                    onclick="window.location.href='{{ route(
                        $brif instanceof \App\Models\Common
                            ? 'common.questions'
                            : 'commercial.questions',
                        [
                            'id'   => $brif->id,
                            'page' => $brif->current_page
                        ]
                    ) }}'">
                    
                    <h4>{{ $brif->title }} #{{ $brif->id }}</h4>
                    <div class="brif__body flex">
                        <ul>
                            @foreach (
                                ($brif instanceof \App\Models\Common
                                    ? $pageTitlesCommon
                                    : $pageTitlesCommercial)
                                as $index => $title
                            )
                                <li class="{{ $index + 1 <= $brif->current_page ? 'completed' : '' }}">
                                    {{ $title }}
                                </li>
                            @endforeach
                        </ul>
                    </div>
                <div class="button__brifs-variab">
                    <div class="button__brifs flex">
                        <!-- Кнопка заполнения (без изменений) -->
                        <button class="button__variate2">
                            <img src="/storage/icon/create__info.svg" alt=""> 
                            <span>Заполнить</span>
                        </button>
                        <!-- Кнопка удаления с event.stopPropagation() и вызовом confirmDelete -->
                        <button class="button__variate2 icon"
                            onclick="event.stopPropagation(); confirmDelete({{ $brif->id }});">
                            <img src="/storage/icon/close__info.svg" alt="">
                        </button>
                    </div>
                    <p class="flex wd100 between">
                        <span>{{ $brif->created_at->format('H:i') }}</span>
                        <span>{{ $brif->created_at->format('d.m.Y') }}</span>
                    </p>
                
                </div>
                   
                    <!-- Скрытая форма для удаления -->
                    <form id="delete-form-{{ $brif->id }}" action="{{ route('brifs.destroy', $brif->id) }}" method="POST" style="display: none;">
                        @csrf
                        @method('DELETE')
                    </form>
                </li>
                
                    @endforeach
                </ul>
            @endif
        </div>

        {{-- Завершенные брифы --}}
        <div class="brifs__section brifs__section__finished">
            <h2>Завершенные брифы</h2>

            @if ($inactiveBrifs->isEmpty())
                <p>У вас нет завершенных брифов.</p>
            @else
                <ul class="brifs__list">
                    @foreach ($inactiveBrifs as $brif)
                        <li class="brif"
                            onclick="window.location.href='{{ route(
                                $brif instanceof \App\Models\Common
                                    ? 'common.show'
                                    : 'commercial.show',
                                $brif->id
                            ) }}'">
                            
                            <h4>{{ $brif->title }} #{{ $brif->id }}</h4>
                            
                            <div class="button__brifs flex">
                                <button class="button__variate2"><img src="/storage/icon/create__info.svg" alt=""> <span>Посмотреть</span></button>
                                <button class="button__variate2" onclick="event.stopPropagation(); window.location.href='{{ route(
                                    $brif instanceof \App\Models\Common
                                        ? 'common.download.pdf'
                                        : 'commercial.download.pdf',
                                    $brif->id
                                ) }}'">
                                  <span>Скачать PDF</span>
                                </button>
                            </div>
                            <p class="flex wd100 between">
                                <span>{{ $brif->created_at->format('H:i') }}</span>
                                <span>{{ $brif->created_at->format('d.m.Y') }}</span>
                            </p>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>
    </div>
@endif
<script>
    function confirmDelete(brifId) {
        if (confirm("Вы действительно хотите удалить этот бриф? Это действие нельзя будет отменить.")) {
            document.getElementById('delete-form-' + brifId).submit();
        }
    }
</script>

{{-- Модальное окно для активного брифа --}}
@if($showActiveBriefModal && $activeBrief)
<!-- Модальное окно активного брифа -->
<div class="modal fade" id="activeBriefModal" tabindex="-1" role="dialog" aria-labelledby="activeBriefModalLabel" aria-hidden="true" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content active-brief-modal">
           
                <h5 class="modal-title" id="activeBriefModalLabel">
                    <i class="fas fa-file-alt text-primary"></i>
                    У вас есть незаверенный бриф
                </h5>
           
            <div class="modal-body active-brief-body">
                <div class="brief-info-card">
                    <div class="brief-type-badge">
                        @if($activeBrief instanceof \App\Models\Common)
                            <span class="badge badge-primary">Общий бриф</span>
                        @else
                            <span class="badge badge-success">Коммерческий бриф</span>
                        @endif
                    </div>
                    
                    <h6 class="brief-title">{{ $activeBrief->title }} #{{ $activeBrief->id }}</h6>
                    
                    <div class="brief-details">
                        <div class="detail-item">
                            <i class="fas fa-calendar-alt"></i>
                            <span>Создан: {{ $activeBrief->created_at->format('d.m.Y в H:i') }}</span>
                        </div>
                        
                        <div class="detail-item">
                            <i class="fas fa-tasks"></i>
                            <span>
                                Страница: {{ $activeBrief->current_page ?? 1 }} из 
                                @if($activeBrief instanceof \App\Models\Common)
                                    {{ count($pageTitlesCommon ?? []) }}
                                @else
                                    {{ count($pageTitlesCommercial ?? []) }}
                                @endif
                            </span>
                        </div>
                        
                        @php
                            $progress = $activeBrief instanceof \App\Models\Common 
                                ? (($activeBrief->current_page ?? 1) / count($pageTitlesCommon ?? [1])) * 100
                                : (($activeBrief->current_page ?? 1) / count($pageTitlesCommercial ?? [1])) * 100;
                        @endphp
                        
                        <div class="progress-info">
                            <span>Прогресс заполнения</span>
                            <div class="progress mb-2">
                                <div class="progress-bar" role="progressbar" style="width: {{ $progress }}%" aria-valuenow="{{ $progress }}" aria-valuemin="0" aria-valuemax="100">
                                    {{ round($progress) }}%
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="modal-question">
                    <p class="text-center font-weight-bold mb-3">
                        Хотите продолжить заполнение этого брифа?
                    </p>
                </div>
            </div>
            <div class="modal-footer active-brief-footer">
                <button type="button" class="btn btn-secondary btn-stay" onclick="stayOnPage()">
                    <i class="fas fa-times"></i>
                    Остаться на этой странице
                </button>
                <button type="button" class="btn btn-primary btn-continue" onclick="continueBrief()">
                    <i class="fas fa-arrow-right"></i>
                    Продолжить заполнение
                </button>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Показываем модальное окно при загрузке страницы
    @if($showActiveBriefModal && $activeBrief)
        $('#activeBriefModal').modal('show');
    @endif
});

function continueBrief() {
    // Переходим к заполнению брифа
    @if($activeBrief)
        @if($activeBrief instanceof \App\Models\Common)
            window.location.href = "{{ route('common.questions', ['id' => $activeBrief->id, 'page' => $activeBrief->current_page ?? 1]) }}";
        @else
            window.location.href = "{{ route('commercial.questions', ['id' => $activeBrief->id, 'page' => $activeBrief->current_page ?? 1]) }}";
        @endif
    @endif
}

function stayOnPage() {
    // Отправляем AJAX запрос для сохранения состояния закрытия модального окна
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
    
    $.ajax({
        url: "{{ route('brifs.dismiss-modal') }}",
        type: 'POST',
        data: {
            brief_id: {{ $activeBrief->id ?? 0 }}
        },
        success: function(response) {
            $('#activeBriefModal').modal('hide');
        },
        error: function(xhr, status, error) {
            console.error('Ошибка при закрытии модального окна:', error);
            $('#activeBriefModal').modal('hide');
        }
    });
}
</script>

<style>
/* Стили для модального окна активного брифа */
.active-brief-modal {
    border-radius: 15px;
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
    border: none;
    overflow: hidden;
}

.active-brief-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border-bottom: none;
    padding: 20px 25px;
}

.active-brief-header .modal-title {
    font-size: 1.2rem;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 10px;
}

.active-brief-body {
    padding: 25px;
    background-color: #f8f9fa;
}

.brief-info-card {
    background: white;
    border-radius: 10px;
    padding: 20px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
    margin-bottom: 20px;
}

.brief-type-badge {
    margin-bottom: 15px;
}

.brief-type-badge .badge {
    font-size: 0.8rem;
    padding: 5px 12px;
    border-radius: 20px;
}

.brief-title {
    color: #2c3e50;
    font-weight: 600;
    margin-bottom: 15px;
    font-size: 1.1rem;
}

.brief-details .detail-item {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 10px;
    color: #6c757d;
    font-size: 0.9rem;
}

.brief-details .detail-item i {
    width: 16px;
    color: #667eea;
}

.progress-info {
    margin-top: 15px;
}

.progress-info span {
    font-size: 0.85rem;
    color: #6c757d;
    margin-bottom: 5px;
    display: block;
}

.progress {
    height: 8px;
    border-radius: 5px;
    background-color: #e9ecef;
}

.progress-bar {
    background: linear-gradient(90deg, #667eea 0%, #764ba2 100%);
    border-radius: 5px;
    font-size: 0.7rem;
    font-weight: 600;
}

.modal-question {
    background: white;
    border-radius: 10px;
    padding: 20px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
}

.modal-question p {
    color: #2c3e50;
    margin: 0;
    font-size: 1rem;
}

.active-brief-footer {
    background-color: white;
    border-top: 1px solid #e9ecef;
    padding: 20px 25px;
    display: flex;
    justify-content: space-between;
    gap: 0px;
}

.active-brief-footer .btn {
    border-radius: 8px;
    padding: 10px 20px;
    font-weight: 500;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    gap: 8px;
    min-width: 160px;
    justify-content: center;
}

.btn-stay {
    background-color: #6c757d;
    border-color: #6c757d;
    color: white;
}

.btn-stay:hover {
    background-color: #5a6268;
    border-color: #545b62;
    transform: translateY(-1px);
}

.btn-continue {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border: none;
    color: white;
}

.btn-continue:hover {
    background: linear-gradient(135deg, #5a6fd8 0%, #6a4190 100%);
    transform: translateY(-1px);
    box-shadow: 0 5px 15px rgba(102, 126, 234, 0.3);
}

/* Анимации */
.modal.fade .modal-dialog {
    transform: translate(0, -50px);
    transition: transform 0.3s ease-out;
}

.modal.show .modal-dialog {
    transform: translate(0, 0);
}

/* Адаптивность */
@media (max-width: 768px) {
    .active-brief-footer {
        flex-direction: column;
    }
    
    .active-brief-footer .btn {
        width: 100%;
        margin-bottom: 10px;
    }
    
    .brief-info-card, .modal-question {
        padding: 15px;
    }
    
    .active-brief-body {
        padding: 20px 15px;
    }
}
</style>
@endif
