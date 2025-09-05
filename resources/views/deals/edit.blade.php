@extends('layouts.admin')

@section('title', 'Редактирование сделки #' . ($deal->project_number ?? $deal->id))

<!-- Дополнительные стили и скрипты для страницы редактирования сделки -->
<link rel="stylesheet" href="{{ asset('css/p/select2.min.css') }}">
<!-- Простые стили для формы сделки -->
<link rel="stylesheet" href="{{ asset('css/deal-form-simple.css') }}?v={{ time() }}">

<!-- AJAX система редактирования сделки -->
<link rel="stylesheet" href="{{ asset('css/deal-edit-ajax.css') }}?v={{ time() }}">
<script src="{{ asset('js/deal-edit-ajax.js') }}?v={{ time() }}"></script>

<!-- Локальная система загрузки документов (заменяет Яндекс.Диск) -->
<link rel="stylesheet" href="{{ asset('css/local-documents-upload.css') }}?v={{ time() }}">
<script src="{{ asset('js/local-documents-upload.js') }}?v={{ time() }}"></script>

<!-- СИСТЕМА ПОИСКА БРИФОВ -->
<script src="{{ asset('js/simple-brief-system.js') }}?v={{ time() }}"></script>

<!-- СИСТЕМА ЗАГРУЗКИ ЯНДЕКС.ДИСКА v3.0 -->
<link rel="stylesheet" href="{{ asset('css/yandex-disk-uploader-v3.css') }}?v={{ time() }}">
<script src="{{ asset('js/yandex-disk-uploader-v3.js') }}?v={{ time() }}"></script>

<!-- ИСПРАВЛЕНИЕ ОТОБРАЖЕНИЯ ССЫЛОК ЯНДЕКС.ДИСКА -->
<link rel="stylesheet" href="{{ asset('css/yandex-links-fix-final.css') }}?v={{ time() }}">
<script src="{{ asset('js/yandex-links-fix-final.js') }}?v={{ time() }}"></script>

<!-- КАЛЬКУЛЯТОР ПЛАНОВОЙ ДАТЫ ЗАВЕРШЕНИЯ ПРОЕКТА -->
<link rel="stylesheet" href="{{ asset('css/project-date-calculator.css') }}?v={{ time() }}">

<!-- ОТЛАДОЧНЫЙ СКРИПТ для диагностики формы -->
<script src="{{ asset('js/form-debug.js') }}?v={{ time() }}"></script>

<!-- ТЕСТОВЫЙ СКРИПТ для диагностики Яндекс.Диска -->
<script src="{{ asset('js/yandex-debug-test.js') }}?v={{ time() }}"></script>

<!-- КАЛЬКУЛЯТОР ПЛАНОВОЙ ДАТЫ ЗАВЕРШЕНИЯ ПРОЕКТА -->
<script src="{{ asset('js/project-date-calculator.js') }}?v={{ time() }}"></script>

<script>
// Временная защита от ошибок jQuery до его загрузки
if (typeof window.$ === 'undefined') {
    window.$ = function() {
        console.warn('jQuery еще не загружен, вызов отложен');
        return { ready: function() {} };
    };
}

// Безопасная инициализация Select2 с проверкой jQuery
document.addEventListener('DOMContentLoaded', function() {
    // Ждем загрузки jQuery с таймаутом
    var initAttempts = 0;
    var maxAttempts = 50;

    function waitForJquery() {
        initAttempts++;

        if (typeof $ !== 'undefined' && typeof $.fn.select2 !== 'undefined') {
            console.log('✅ jQuery и Select2 загружены, настраиваем язык');
            configureSelect2Language();
        } else if (initAttempts < maxAttempts) {
            console.log('⏳ Ожидание jQuery и Select2... попытка ' + initAttempts);
            setTimeout(waitForJquery, 100);
        } else {
            console.error('❌ Не удалось дождаться загрузки jQuery или Select2');
        }
    }

    function configureSelect2Language() {
        try {
            $.fn.select2.defaults.set('language', {
                errorLoading: function () {
                    return 'Невозможно загрузить результаты';
                },
                inputTooLong: function (args) {
                    var overChars = args.input.length - args.maximum;
                    var message = 'Пожалуйста, удалите ' + overChars + ' символ';
                    if (overChars >= 2 && overChars <= 4) {
                        message += 'а';
                    } else if (overChars >= 5) {
                        message += 'ов';
                    }
                    return message;
                },
                inputTooShort: function (args) {
                    var remainingChars = args.minimum - args.input.length;
                    var message = 'Пожалуйста, введите еще ' + remainingChars + ' символ';
                    if (remainingChars >= 2 && remainingChars <= 4) {
                        message += 'а';
                    } else if (remainingChars >= 5) {
                        message += 'ов';
                    }
                    return message;
                },
                loadingMore: function () {
                    return 'Загрузка данных...';
                },
                maximumSelected: function (args) {
                    var message = 'Вы можете выбрать не более ' + args.maximum + ' элемент';
                    if (args.maximum >= 2 && args.maximum <= 4) {
                        message += 'а';
                    } else if (args.maximum >= 5) {
                        message += 'ов';
                    }
                    return message;
                },
                noResults: function () {
                    return 'Совпадений не найдено';
                },
                searching: function () {
                    return 'Поиск...';
                }
            });
            console.log('✅ Язык Select2 настроен');
        } catch (error) {
            console.error('❌ Ошибка настройки языка Select2:', error);
        }
    }

    // Начинаем ожидание
    waitForJquery();
});
</script>

<!-- Подключение стилей для страницы редактирования сделки -->
@include('deals.partials.components.styles')

@section('content')
<style>.form-control {
    display: block;
    width: 100%;
    height: calc(2.5em + .75rem + 2px);
    padding: 1.375rem 1.75rem;
    font-size: 1rem;
    font-weight: 400;
    line-height: 1.5;
    color: #495057;
    background-color: #fff;
    background-clip: padding-box;
    border: 1px solid #ced4da;
    border-radius: .25rem;
    transition: border-color .15s ease-in-out, box-shadow .15s ease-in-out;
}</style>
<div class="container">
    <div class="main__flex">
        <div class="main__ponel">
            @include('layouts/ponel')
        </div>
        <div class="main__module">
            @include('layouts/header')
           <div class="container-fluid py-4">

    <!-- Отображение flash-сообщений -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('warning'))
        <div class="alert alert-warning alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-triangle me-2"></i>{{ session('warning') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('info'))
        <div class="alert alert-info alert-dismissible fade show" role="alert">
            <i class="fas fa-info-circle me-2"></i>{{ session('info') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Хедер страницы -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card  ">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div style="display: flex; align-items: flex-start; flex-direction: column;">
                            <h2 class="card-title  mb-1">
                                <i class="fas fa-edit me-2"></i>
                                Сделка #{{ $deal->project_number ?? $deal->id }} | {{ $deal->project_number }}
                            </h2>

                        </div>
                        <div>
                            <a href="{{ route('deal.cardinator') }}" class="btn btn-light">
                                <i class="fas fa-arrow-left me-1"></i> Назад к сделкам
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Основная форма с вкладками Bootstrap -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header pe-3" >
                    <!-- Кастомные вкладки с улучшенным дизайном -->
                    <div class="deal-tabs-container">
                        <nav class="deal-tabs-nav" id="dealTabs" role="tablist">
                            <button class="deal-tab-button active" id="zakaz-tab" data-bs-toggle="tab" data-bs-target="#zakaz" type="button" role="tab" aria-controls="zakaz" aria-selected="true">
                                <i class="fas fa-shopping-cart"></i>
                                <span>Заказ</span>
                            </button>
                            <button class="deal-tab-button" id="rabota-tab" data-bs-toggle="tab" data-bs-target="#rabota" type="button" role="tab" aria-controls="rabota" aria-selected="false">
                                <i class="fas fa-cogs"></i>
                                <span>Работа над проектом</span>
                            </button>
                            <button class="deal-tab-button" id="final-tab" data-bs-toggle="tab" data-bs-target="#final" type="button" role="tab" aria-controls="final" aria-selected="false">
                                <i class="fas fa-flag-checkered"></i>
                                <span>Финал проекта</span>
                            </button>
                            <button class="deal-tab-button" id="documents-tab" data-bs-toggle="tab" data-bs-target="#documents" type="button" role="tab" aria-controls="documents" aria-selected="false">
                                <i class="fas fa-file-alt"></i>
                                <span>Документы</span>
                            </button>
                            <button class="deal-tab-button" id="brief-tab" data-bs-toggle="tab" data-bs-target="#brief" type="button" role="tab" aria-controls="brief" aria-selected="false">
                                <i class="fas fa-clipboard-list"></i>
                                <span>Бриф</span>
                            </button>
                        </nav>
                    </div>
                </div>

                <div class="deal-tabs-content">
                    <!-- Форма редактирования -->
                    <form id="deal-edit-form"
                          action="{{ route('deal.update', $deal->id) }}"
                          method="POST"
                          enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="deal_id" value="{{ $deal->id }}">

                        <!-- Контент вкладок -->
                        <div class="tab-content" id="dealTabsContent">
                            <!-- Вкладка: Заказ -->
                            <div class="tab-pane fade show active" id="zakaz" role="tabpanel" aria-labelledby="zakaz-tab">
                                @php
                                    $userRole = Auth::user()->status;
                                @endphp

                                <div class="row">
                                    <!-- Телефон клиента -->
                                    <div class="col-md-6 mb-3">
                                        <label for="client_phone" class="form-label">
                                            <i class="fas fa-phone me-1"></i>Телефон клиента <span class="text-danger">*</span>
                                        </label>
                                        <input type="text" class="form-control maskphone" id="client_phone" name="client_phone"
                                               value="{{ $deal->client_phone }}" required>
                                    </div>

                                    <!-- Номер проекта -->
                                    <div class="col-md-6 mb-3">
                                        <label for="project_number" class="form-label">
                                            <i class="fas fa-hashtag me-1"></i>№ проекта <span class="text-danger">*</span>
                                        </label>
                                        @if($userRole === 'partner')
                                            <input type="text" class="form-control" id="project_number" name="project_number"
                                                   value="{{ $deal->project_number }}" readonly
                                                   style="background-color: #f8f9fa; border-color: #dee2e6;">
                                        @else
                                            <input type="text" class="form-control" id="project_number" name="project_number"
                                                   value="{{ $deal->project_number }}" required maxlength="150">
                                        @endif
                                    </div>

                                    <!-- Имя клиента -->
                                    <div class="col-md-6 mb-3">
                                        <label for="client_name" class="form-label">
                                            <i class="fas fa-user me-1"></i>Имя клиента <span class="text-danger">*</span>
                                        </label>
                                        <input type="text" class="form-control" id="client_name" name="client_name"
                                               value="{{ $deal->client_name }}" required maxlength="255">
                                    </div>

                                    <!-- Статус -->
                                    <div class="col-md-6 mb-3">
                                        <label for="status" class="form-label">
                                            <i class="fas fa-tag me-1"></i>Статус
                                        </label>
                                        @if($userRole === 'partner')
                                            <input type="text" class="form-control" value="{{ $deal->status }}" readonly
                                                   style="background-color: #f8f9fa; border-color: #dee2e6;">
                                            <input type="hidden" name="status" value="{{ $deal->status }}">
                                        @else
                                            <select class="form-select" id="status" name="status">
                                                <option value="">-- Выберите статус --</option>
                                                @if(isset($statuses))
                                                    @foreach($statuses as $status)
                                                        <option value="{{ $status }}" {{ $deal->status === $status ? 'selected' : '' }}>
                                                            {{ $status }}
                                                        </option>
                                                    @endforeach
                                                @endif
                                            </select>
                                        @endif
                                    </div>

                                    <!-- Координатор -->
                                    <div class="col-md-6 mb-3">
                                        <label for="coordinator_id" class="form-label">
                                            <i class="fas fa-user-tie me-1"></i>Координатор
                                        </label>
                                        @if($userRole === 'partner')
                                            @php
                                                $coordinatorName = '';
                                                if($deal->coordinator_id && isset($coordinators)) {
                                                    foreach($coordinators as $coordinator) {
                                                        if($coordinator->id == $deal->coordinator_id) {
                                                            $coordinatorName = $coordinator->name;
                                                            break;
                                                        }
                                                    }
                                                }
                                            @endphp
                                            <input type="text" class="form-control" value="{{ $coordinatorName ?: 'Не назначен' }}" readonly
                                                   style="background-color: #f8f9fa; border-color: #dee2e6;">
                                            <input type="hidden" name="coordinator_id" value="{{ $deal->coordinator_id }}">
                                        @elseif(in_array($userRole, ['coordinator', 'admin']))
                                            <select class="form-select" id="coordinator_id" name="coordinator_id">
                                                <option value="">-- Выберите координатора --</option>
                                                @if(isset($coordinators))
                                                    @foreach($coordinators as $coordinator)
                                                        <option value="{{ $coordinator->id }}" {{ $deal->coordinator_id == $coordinator->id ? 'selected' : '' }}>
                                                            {{ $coordinator->name }}
                                                        </option>
                                                    @endforeach
                                                @endif
                                            </select>
                                        @endif
                                    </div>

                                    <!-- Партнер -->
                                    <div class="col-md-6 mb-3">
                                        <label for="office_partner_id" class="form-label">
                                            <i class="fas fa-handshake me-1"></i>Партнер
                                        </label>
                                        @if($userRole === 'partner')
                                            @php
                                                $partnerName = '';
                                                if($deal->office_partner_id && isset($partners)) {
                                                    foreach($partners as $partner) {
                                                        if($partner->id == $deal->office_partner_id) {
                                                            $partnerName = $partner->name;
                                                            break;
                                                        }
                                                    }
                                                }
                                            @endphp
                                            <input type="text" class="form-control" value="{{ $partnerName ?: 'Не назначен' }}" readonly
                                                   style="background-color: #f8f9fa; border-color: #dee2e6;">
                                            <input type="hidden" name="office_partner_id" value="{{ $deal->office_partner_id }}">
                                        @elseif(in_array($userRole, ['coordinator', 'admin']))
                                            <select class="form-select" id="office_partner_id" name="office_partner_id">
                                                <option value="">-- Выберите партнера --</option>
                                                @if(isset($partners))
                                                    @foreach($partners as $partner)
                                                        <option value="{{ $partner->id }}" {{ $deal->office_partner_id == $partner->id ? 'selected' : '' }}>
                                                            {{ $partner->name }}
                                                        </option>
                                                    @endforeach
                                                @endif
                                            </select>
                                        @endif
                                    </div>

                                    <!-- Город/часовой пояс -->
                                    <div class="col-md-6 mb-3">
                                        <label for="client_timezone" class="form-label">
                                            <i class="fas fa-city me-1"></i>Город/часовой пояс
                                        </label>
                                        <select class="form-select" id="client_timezone" name="client_timezone">
                                            <option value="">-- Выберите город/часовой пояс --</option>
                                            @if(isset($russianCities))
                                                @foreach($russianCities as $city)
                                                    <option value="{{ $city['city'] ?? $city }}" {{ $deal->client_timezone === ($city['city'] ?? $city) ? 'selected' : '' }}>
                                                        {{ $city['city'] ?? $city }}{{ isset($city['timezone']) ? ' (' . $city['timezone'] . ')' : '' }}
                                                    </option>
                                                @endforeach
                                            @endif
                                        </select>
                                    </div>

                                    <!-- Пакет -->
                                    <div class="col-md-6 mb-3">
                                        <label for="package" class="form-label">
                                            <i class="fas fa-box me-1"></i>Пакет
                                        </label>
                                        <select class="form-select" id="package" name="package">
                                            <option value="">-- Выберите пакет --</option>
                                            <option value="Первый пакет 1400 м2" {{ $deal->package === 'Первый пакет 1400 м2' ? 'selected' : '' }}>Первый пакет 1400 м2</option>
                                            <option value="Второй пакет 85% комиссия" {{ $deal->package === 'Второй пакет 85% комиссия' ? 'selected' : '' }}>Второй пакет 85% комиссия</option>
                                            <option value="Третий пакет 55% комиссия" {{ $deal->package === 'Третий пакет 55% комиссия' ? 'selected' : '' }}>Третий пакет 55% комиссия</option>
                                            <option value="Партнер 75% комиссия" {{ $deal->package === 'Партнер 75% комиссия' ? 'selected' : '' }}>Партнер 75% комиссия</option>
                                        </select>
                                    </div>

                                    <!-- Услуга по прайсу -->
                                    <div class="col-md-6 mb-3">
                                        <label for="price_service_option" class="form-label">
                                            <i class="fas fa-list-check me-1"></i>Услуга по прайсу
                                        </label>
                                        <select class="form-select" id="price_service_option" name="price_service_option">
                                            <option value="">-- Выберите услугу --</option>
                                            <option value="экспресс планировка" {{ $deal->price_service_option === 'экспресс планировка' ? 'selected' : '' }}>Экспресс планировка</option>
                                            <option value="экспресс планировка с коллажами" {{ $deal->price_service_option === 'экспресс планировка с коллажами' ? 'selected' : '' }}>Экспресс планировка с коллажами</option>
                                            <option value="экспресс проект с электрикой" {{ $deal->price_service_option === 'экспресс проект с электрикой' ? 'selected' : '' }}>Экспресс проект с электрикой</option>
                                            <option value="экспресс планировка с электрикой и коллажами" {{ $deal->price_service_option === 'экспресс планировка с электрикой и коллажами' ? 'selected' : '' }}>Экспресс планировка с электрикой и коллажами</option>
                                            <option value="экспресс рабочий проект" {{ $deal->price_service_option === 'экспресс рабочий проект' ? 'selected' : '' }}>Экспресс рабочий проект</option>
                                            <option value="экспресс эскизный проект с рабочей документацией" {{ $deal->price_service_option === 'экспресс эскизный проект с рабочей документацией' ? 'selected' : '' }}>Экспресс эскизный проект с рабочей документацией</option>
                                            <option value="экспресс 3Dвизуализация с коллажами" {{ $deal->price_service_option === 'экспресс 3Dвизуализация с коллажами' ? 'selected' : '' }}>Экспресс 3Dвизуализация с коллажами</option>
                                            <option value="экспресс полный дизайн-проект" {{ $deal->price_service_option === 'экспресс полный дизайн-проект' ? 'selected' : '' }}>Экспресс полный дизайн-проект</option>
                                            <option value="Визуализация на одну комнату" {{ $deal->price_service_option === 'Визуализация на одну комнату' ? 'selected' : '' }}>Визуализация на одну комнату</option>
                                        </select>
                                    </div>

                                    <!-- Количество комнат по прайсу -->
                                    <div class="col-md-6 mb-3">
                                        <label for="rooms_count_pricing" class="form-label">
                                            <i class="fas fa-door-open me-1"></i>Кол-во комнат по прайсу
                                        </label>
                                        <input type="text" class="form-control" id="rooms_count_pricing" name="rooms_count_pricing"
                                               value="{{ $deal->rooms_count_pricing }}">
                                    </div>

                                    <!-- Кто делает комплектацию -->
                                    <div class="col-md-6 mb-3">
                                        <label for="completion_responsible" class="form-label">
                                            <i class="fas fa-clipboard-check me-1"></i>Кто делает комплектацию
                                        </label>
                                        <select class="form-select" id="completion_responsible" name="completion_responsible">
                                            <option value="">-- Выберите ответственного --</option>
                                            <option value="клиент" {{ $deal->completion_responsible === 'клиент' ? 'selected' : '' }}>Клиент</option>
                                            <option value="партнер" {{ $deal->completion_responsible === 'партнер' ? 'selected' : '' }}>Партнер</option>
                                            <option value="шопинг-лист" {{ $deal->completion_responsible === 'шопинг-лист' ? 'selected' : '' }}>Шопинг-лист</option>
                                            <option value="закупки и снабжение от УК" {{ $deal->completion_responsible === 'закупки и снабжение от УК' ? 'selected' : '' }}>Нужны закупки и снабжение от УК</option>
                                        </select>
                                    </div>

                                    <!-- Аватар сделки -->
                                    @if(in_array($userRole, ['coordinator', 'admin']))
                                    <div class="col-md-6 mb-3">
                                        <label for="avatar_path" class="form-label">
                                            <i class="fas fa-image me-1"></i>Аватар сделки
                                        </label>
                                        <div class="yandex-upload-container" data-field-name="avatar_path">
                                            <div class="upload-area">
                                                <input type="file"
                                                       class="yandex-file-input yandex-upload d-none"
                                                       id="avatar_path"
                                                       name="avatar_path"
                                                       data-upload-type="yandex"
                                                       accept="image/*">
                                                <div class="upload-hint text-center p-3" onclick="document.getElementById('avatar_path').click()">
                                                    <i class="fas fa-cloud-upload-alt fa-2x text-muted mb-2"></i>
                                                    <p class="mb-1">Нажмите для загрузки аватара</p>
                                                    <small class="text-muted">JPG, PNG, GIF до 10MB</small>
                                                </div>
                                                <div class="current-file-info" style="display: none;">
                                                    <!-- Здесь будет отображаться информация о текущем файле -->
                                                </div>
                                            </div>
                                        </div>
                                        @if($deal->avatar_path)
                                            <div class="mt-2">
                                                <img src="{{ asset('storage/' . $deal->avatar_path) }}" alt="Текущий аватар" class="img-thumbnail" style="max-height: 100px;">
                                            </div>
                                        @endif
                                        <!-- Динамический контейнер для новых ссылок -->
                                        <div class="yandex-file-links-container" data-field="avatar_path"></div>
                                    </div>
                                    @endif

                                    <!-- Сумма заказа -->
                                    <div class="col-md-6 mb-3">
                                        <label for="total_sum" class="form-label">
                                            <i class="fas fa-ruble-sign me-1"></i>Сумма заказа
                                        </label>
                                        <input type="number" class="form-control" id="total_sum" name="total_sum"
                                               value="{{ $deal->total_sum }}" step="0.01">
                                    </div>

                                    <!-- Дата создания -->
                                    <div class="col-md-6 mb-3">
                                        <label for="created_date" class="form-label">
                                            <i class="fas fa-calendar-plus me-1"></i>Дата создания
                                        </label>
                                        <input type="date" class="form-control" id="created_date" name="created_date"
                                               value="{{ $deal->created_date ? (is_string($deal->created_date) ? $deal->created_date : $deal->created_date->format('Y-m-d')) : '' }}">
                                    </div>

                                    <!-- Дата оплаты -->
                                    <div class="col-md-6 mb-3">
                                        <label for="payment_date" class="form-label">
                                            <i class="fas fa-money-check me-1"></i>Дата оплаты
                                        </label>
                                        <input type="date" class="form-control" id="payment_date" name="payment_date"
                                               value="{{ $deal->payment_date ? (is_string($deal->payment_date) ? $deal->payment_date : $deal->payment_date->format('Y-m-d')) : '' }}">
                                    </div>

                                    <!-- Комментарий -->
                                    <div class="col-12 mb-3">
                                        <label for="comment" class="form-label">
                                            <i class="fas fa-sticky-note me-1"></i>Общий комментарий
                                        </label>
                                        <textarea class="form-control" id="comment" name="comment" rows="4" maxlength="1000">{{ $deal->comment }}</textarea>
                                    </div>
                                </div>
                            </div>

                            <!-- Вкладка: Работа над проектом -->
                            <div class="tab-pane fade" id="rabota" role="tabpanel" aria-labelledby="rabota-tab">
                                <div class="row">
                                    <!-- Улучшенная система дат с автоматическим расчетом -->
                                    <div class="col-12 mb-4">
                                        <div class="card modern-date-calculator">
                                            <div class="card-header bg-gradient-primary text-white">
                                                <h6 class="mb-0">
                                                    <i class="fas fa-calendar-alt me-2"></i>
                                                    Временные рамки проекта
                                                    <span class="badge bg-light text-primary ms-2">Автоматический расчет</span>
                                                </h6>
                                            </div>
                                            <div class="card-body">
                                                <div class="row g-3">
                                                    <!-- Дата старта -->
                                                    <div class="col-md-4">
                                                        <label for="start_date" class="form-label fw-bold">
                                                            <i class="fas fa-play-circle text-success me-1"></i>
                                                            Дата старта работы
                                                        </label>
                                                        <input type="date" class="form-control enhanced-date-picker"
                                                               id="start_date" name="start_date"
                                                               value="{{ $deal->start_date ? (is_string($deal->start_date) ? $deal->start_date : $deal->start_date->format('Y-m-d')) : '' }}"
                                                               placeholder="Выберите дату начала">

                                                    </div>

                                                    <!-- Срок проекта -->
                                                    <div class="col-md-4">
                                                        <label for="project_duration" class="form-label fw-bold">
                                                            <i class="fas fa-hourglass-half text-warning me-1"></i>
                                                            Длительность (рабочих дней)
                                                        </label>
                                                        <input type="number" class="form-control"
                                                               id="project_duration" name="project_duration"
                                                               value="{{ $deal->project_duration }}"
                                                               min="1" max="365" placeholder="Введите количество дней">

                                                    </div>

                                                    <!-- Дата завершения (автоматически рассчитывается) -->
                                                    <div class="col-md-4">
                                                        <label for="project_end_date" class="form-label fw-bold">
                                                            <i class="fas fa-flag-checkered text-primary me-1"></i>
                                                            Плановая дата завершения
                                                            <span class="badge bg-success ms-1" title="Рассчитывается автоматически">
                                                                <i class="fas fa-robot"></i> AUTO
                                                            </span>
                                                        </label>
                                                        <input type="date" class="form-control auto-calculated-field"
                                                               id="project_end_date" name="project_end_date"
                                                               value="{{ $deal->project_end_date ? (is_string($deal->project_end_date) ? $deal->project_end_date : $deal->project_end_date->format('Y-m-d')) : '' }}"
                                                               readonly>

                                                    </div>
                                                </div>

                                            </div>
                                        </div>
                                    </div>

                                    <!-- Архитектор -->
                                    <div class="col-md-6 mb-3">
                                        <label for="architect_id" class="form-label">
                                            <i class="fas fa-drafting-compass me-1"></i>Архитектор
                                        </label>
                                        @if($userRole === 'partner')
                                            @php
                                                $architectName = '';
                                                if($deal->architect_id && isset($architects)) {
                                                    foreach($architects as $architect) {
                                                        if($architect->id == $deal->architect_id) {
                                                            $architectName = $architect->name;
                                                            break;
                                                        }
                                                    }
                                                }
                                            @endphp
                                            <input type="text" class="form-control" value="{{ $architectName ?: 'Не назначен' }}" readonly
                                                   style="background-color: #f8f9fa; border-color: #dee2e6;">
                                            <input type="hidden" name="architect_id" value="{{ $deal->architect_id }}">
                                        @else
                                            <select class="form-select" id="architect_id" name="architect_id">
                                                <option value="">-- Выберите архитектора --</option>
                                                @if(isset($architects))
                                                    @foreach($architects as $architect)
                                                        <option value="{{ $architect->id }}" {{ $deal->architect_id == $architect->id ? 'selected' : '' }}>
                                                            {{ $architect->name }}
                                                        </option>
                                                    @endforeach
                                                @endif
                                            </select>
                                        @endif
                                    </div>

                                    <!-- Дизайнер -->
                                    <div class="col-md-6 mb-3">
                                        <label for="designer_id" class="form-label">
                                            <i class="fas fa-palette me-1"></i>Дизайнер
                                        </label>
                                        @if($userRole === 'partner')
                                            @php
                                                $designerName = '';
                                                if($deal->designer_id && isset($designers)) {
                                                    foreach($designers as $designer) {
                                                        if($designer->id == $deal->designer_id) {
                                                            $designerName = $designer->name;
                                                            break;
                                                        }
                                                    }
                                                }
                                            @endphp
                                            <input type="text" class="form-control" value="{{ $designerName ?: 'Не назначен' }}" readonly
                                                   style="background-color: #f8f9fa; border-color: #dee2e6;">
                                            <input type="hidden" name="designer_id" value="{{ $deal->designer_id }}">
                                        @else
                                            <select class="form-select" id="designer_id" name="designer_id">
                                                <option value="">-- Выберите дизайнера --</option>
                                                @if(isset($designers))
                                                    @foreach($designers as $designer)
                                                        <option value="{{ $designer->id }}" {{ $deal->designer_id == $designer->id ? 'selected' : '' }}>
                                                            {{ $designer->name }}
                                                        </option>
                                                    @endforeach
                                                @endif
                                            </select>
                                        @endif
                                    </div>

                                    <!-- Визуализатор -->
                                    <div class="col-md-6 mb-3">
                                        <label for="visualizer_id" class="form-label">
                                            <i class="fas fa-eye me-1"></i>Визуализатор
                                        </label>
                                        @if($userRole === 'partner')
                                            @php
                                                $visualizerName = '';
                                                if($deal->visualizer_id && isset($visualizers)) {
                                                    foreach($visualizers as $visualizer) {
                                                        if($visualizer->id == $deal->visualizer_id) {
                                                            $visualizerName = $visualizer->name;
                                                            break;
                                                        }
                                                    }
                                                }
                                            @endphp
                                            <input type="text" class="form-control" value="{{ $visualizerName ?: 'Не назначен' }}" readonly
                                                   style="background-color: #f8f9fa; border-color: #dee2e6;">
                                            <input type="hidden" name="visualizer_id" value="{{ $deal->visualizer_id }}">
                                        @else
                                            <select class="form-select" id="visualizer_id" name="visualizer_id">
                                                <option value="">-- Выберите визуализатора --</option>
                                                @if(isset($visualizers))
                                                    @foreach($visualizers as $visualizer)
                                                        <option value="{{ $visualizer->id }}" {{ $deal->visualizer_id == $visualizer->id ? 'selected' : '' }}>
                                                            {{ $visualizer->name }}
                                                        </option>
                                                    @endforeach
                                                @endif
                                            </select>
                                        @endif
                                    </div>

                                    <!-- Ссылка на визуализацию -->
                                    <div class="col-md-6 mb-3">
                                        <label for="visualization_link" class="form-label">
                                            <i class="fas fa-link me-1"></i>Ссылка на визуализацию
                                        </label>
                                        <input type="url" class="form-control" id="visualization_link" name="visualization_link"
                                               value="{{ $deal->visualization_link }}" placeholder="https://">
                                    </div>

                                    <!-- Планировка финал -->
                                    <div class="col-md-6 mb-3">
                                        <label for="plan_final" class="form-label">
                                            <i class="fas fa-map me-1"></i>Планировка финал (PDF)
                                        </label>
                                        <div class="yandex-upload-container" data-field-name="plan_final">
                                            <div class="upload-area">
                                                <input type="file"
                                                       class="yandex-file-input yandex-upload d-none"
                                                       id="plan_final"
                                                       name="plan_final"
                                                       data-upload-type="yandex"
                                                       accept="application/pdf">
                                                <div class="upload-hint text-center p-3" onclick="document.getElementById('plan_final').click()">
                                                    <i class="fas fa-cloud-upload-alt fa-2x text-muted mb-2"></i>
                                                    <p class="mb-1">Нажмите для загрузки планировки</p>
                                                    <small class="text-muted">PDF до 100MB</small>
                                                </div>
                                                <div class="current-file-info" style="display: none;">
                                                    <!-- Здесь будет отображаться информация о текущем файле -->
                                                </div>
                                            </div>
                                        </div>
                                        @if($deal->yandex_url_plan_final)
                                            <div class="mt-2">
                                                <a href="{{ $deal->yandex_url_plan_final }}" target="_blank" class="btn btn-sm btn-outline-success">
                                                    <i class="fas fa-cloud-download-alt me-1"></i>{{ $deal->original_name_plan_final ?? 'Просмотр файла' }}
                                                </a>
                                            </div>
                                        @endif
                                        <!-- Динамический контейнер для новых ссылок -->
                                        <div class="yandex-file-links-container" data-field="plan_final"></div>
                                    </div>

                                    <!-- Коллаж финал -->
                                    <div class="col-md-6 mb-3">
                                        <label for="final_collage" class="form-label">
                                            <i class="fas fa-object-group me-1"></i>Коллаж финал (PDF)
                                        </label>
                                        <div class="yandex-upload-container" data-field-name="final_collage">
                                            <div class="upload-area">
                                                <input type="file"
                                                       class="yandex-file-input yandex-upload d-none"
                                                       id="final_collage"
                                                       name="final_collage"
                                                       data-upload-type="yandex"
                                                       accept="application/pdf">
                                                <div class="upload-hint text-center p-3" onclick="document.getElementById('final_collage').click()">
                                                    <i class="fas fa-cloud-upload-alt fa-2x text-muted mb-2"></i>
                                                    <p class="mb-1">Нажмите для загрузки коллажа</p>
                                                    <small class="text-muted">PDF до 100MB</small>
                                                </div>
                                                <div class="current-file-info" style="display: none;">
                                                    <!-- Здесь будет отображаться информация о текущем файле -->
                                                </div>
                                            </div>
                                        </div>
                                        @if($deal->yandex_url_final_collage)
                                            <div class="mt-2">
                                                <a href="{{ $deal->yandex_url_final_collage }}" target="_blank" class="btn btn-sm btn-outline-success">
                                                    <i class="fas fa-cloud-download-alt me-1"></i>{{ $deal->original_name_final_collage ?? 'Просмотр файла' }}
                                                </a>
                                            </div>
                                        @endif
                                        <!-- Динамический контейнер для новых ссылок -->
                                        <div class="yandex-file-links-container" data-field="final_collage"></div>
                                    </div>
                                </div>

                                <!-- Скриншоты работы над проектом -->
                                <div class="row">
                                    <div class="col-12 mb-3">
                                        <h6 class="text-muted mb-3">
                                            <i class="fas fa-camera me-2"></i>Скриншоты работы над проектом
                                        </h6>
                                    </div>

                                    <!-- Первый скриншот работы -->
                                    <div class="col-md-6 mb-3">
                                        <label for="screenshot_work_1" class="form-label">
                                            <i class="fas fa-camera me-1"></i>Скриншот работы #1
                                        </label>
                                        <div class="yandex-upload-container" data-field-name="screenshot_work_1">
                                            <div class="upload-area">
                                                <input type="file"
                                                       class="yandex-file-input yandex-upload d-none"
                                                       id="screenshot_work_1"
                                                       name="screenshot_work_1"
                                                       data-upload-type="yandex"
                                                       accept="image/*,.pdf">
                                                <div class="upload-hint text-center p-3" onclick="document.getElementById('screenshot_work_1').click()">
                                                    <i class="fas fa-cloud-upload-alt fa-2x text-muted mb-2"></i>
                                                    <p class="mb-1">Нажмите для загрузки скриншота</p>
                                                    <small class="text-muted">JPG, PNG, PDF до 100MB</small>
                                                </div>
                                                <div class="current-file-info" style="display: none;">
                                                    <!-- Здесь будет отображаться информация о текущем файле -->
                                                </div>
                                            </div>
                                        </div>
                                        @if($deal->yandex_url_screenshot_work_1)
                                            <div class="mt-2">
                                                <a href="{{ $deal->yandex_url_screenshot_work_1 }}" target="_blank" class="btn btn-sm btn-outline-success">
                                                    <i class="fas fa-cloud-download-alt me-1"></i>{{ $deal->original_name_screenshot_work_1 ?? 'Просмотр файла' }}
                                                </a>
                                            </div>
                                        @endif
                                        <!-- Динамический контейнер для новых ссылок -->
                                        <div class="yandex-file-links-container" data-field="screenshot_work_1"></div>
                                    </div>

                                    <!-- Второй скриншот работы -->
                                    <div class="col-md-6 mb-3">
                                        <label for="screenshot_work_2" class="form-label">
                                            <i class="fas fa-camera me-1"></i>Скриншот работы #2
                                        </label>
                                        <div class="yandex-upload-container" data-field-name="screenshot_work_2">
                                            <div class="upload-area">
                                                <input type="file"
                                                       class="yandex-file-input yandex-upload d-none"
                                                       id="screenshot_work_2"
                                                       name="screenshot_work_2"
                                                       data-upload-type="yandex"
                                                       accept="image/*,.pdf">
                                                <div class="upload-hint text-center p-3" onclick="document.getElementById('screenshot_work_2').click()">
                                                    <i class="fas fa-cloud-upload-alt fa-2x text-muted mb-2"></i>
                                                    <p class="mb-1">Нажмите для загрузки скриншота</p>
                                                    <small class="text-muted">JPG, PNG, PDF до 100MB</small>
                                                </div>
                                                <div class="current-file-info" style="display: none;">
                                                    <!-- Здесь будет отображаться информация о текущем файле -->
                                                </div>
                                            </div>
                                        </div>
                                        @if($deal->yandex_url_screenshot_work_2)
                                            <div class="mt-2">
                                                <a href="{{ $deal->yandex_url_screenshot_work_2 }}" target="_blank" class="btn btn-sm btn-outline-success">
                                                    <i class="fas fa-cloud-download-alt me-1"></i>{{ $deal->original_name_screenshot_work_2 ?? 'Просмотр файла' }}
                                                </a>
                                            </div>
                                        @endif
                                        <!-- Динамический контейнер для новых ссылок -->
                                        <div class="yandex-file-links-container" data-field="screenshot_work_2"></div>
                                    </div>

                                    <!-- Третий скриншот работы -->
                                    <div class="col-md-6 mb-3">
                                        <label for="screenshot_work_3" class="form-label">
                                            <i class="fas fa-camera me-1"></i>Скриншот работы #3
                                        </label>
                                        <div class="yandex-upload-container" data-field-name="screenshot_work_3">
                                            <div class="upload-area">
                                                <input type="file"
                                                       class="yandex-file-input yandex-upload d-none"
                                                       id="screenshot_work_3"
                                                       name="screenshot_work_3"
                                                       data-upload-type="yandex"
                                                       accept="image/*,.pdf">
                                                <div class="upload-hint text-center p-3" onclick="document.getElementById('screenshot_work_3').click()">
                                                    <i class="fas fa-cloud-upload-alt fa-2x text-muted mb-2"></i>
                                                    <p class="mb-1">Нажмите для загрузки скриншота</p>
                                                    <small class="text-muted">JPG, PNG, PDF до 100MB</small>
                                                </div>
                                                <div class="current-file-info" style="display: none;">
                                                    <!-- Здесь будет отображаться информация о текущем файле -->
                                                </div>
                                            </div>
                                        </div>
                                        @if($deal->yandex_url_screenshot_work_3)
                                            <div class="mt-2">
                                                <a href="{{ $deal->yandex_url_screenshot_work_3 }}" target="_blank" class="btn btn-sm btn-outline-success">
                                                    <i class="fas fa-cloud-download-alt me-1"></i>{{ $deal->original_name_screenshot_work_3 ?? 'Просмотр файла' }}
                                                </a>
                                            </div>
                                        @endif
                                        <!-- Динамический контейнер для новых ссылок -->
                                        <div class="yandex-file-links-container" data-field="screenshot_work_3"></div>
                                    </div>

                                    <!-- Скриншот финального этапа -->
                                    <div class="col-md-6 mb-3">
                                        <label for="screenshot_final" class="form-label">
                                            <i class="fas fa-flag-checkered me-1"></i>Скриншот финального этапа
                                        </label>
                                        <div class="yandex-upload-container" data-field-name="screenshot_final">
                                            <div class="upload-area">
                                                <input type="file"
                                                       class="yandex-file-input yandex-upload d-none"
                                                       id="screenshot_final"
                                                       name="screenshot_final"
                                                       data-upload-type="yandex"
                                                       accept="image/*,.pdf">
                                                <div class="upload-hint text-center p-3" onclick="document.getElementById('screenshot_final').click()">
                                                    <i class="fas fa-cloud-upload-alt fa-2x text-muted mb-2"></i>
                                                    <p class="mb-1">Нажмите для загрузки скриншота</p>
                                                    <small class="text-muted">JPG, PNG, PDF до 100MB</small>
                                                </div>
                                                <div class="current-file-info" style="display: none;">
                                                    <!-- Здесь будет отображаться информация о текущем файле -->
                                                </div>
                                            </div>
                                        </div>
                                        @if($deal->yandex_url_screenshot_final)
                                            <div class="mt-2">
                                                <a href="{{ $deal->yandex_url_screenshot_final }}" target="_blank" class="btn btn-sm btn-outline-success">
                                                    <i class="fas fa-cloud-download-alt me-1"></i>{{ $deal->original_name_screenshot_final ?? 'Просмотр файла' }}
                                                </a>
                                            </div>
                                        @endif
                                        <!-- Динамический контейнер для новых ссылок -->
                                        <div class="yandex-file-links-container" data-field="screenshot_final"></div>
                                    </div>
                                </div>
                            </div>

                            <!-- Вкладка: Финал проекта -->
                            <div class="tab-pane fade" id="final" role="tabpanel" aria-labelledby="final-tab">
                                <div class="row">
                                    <!-- Замеры с расширенными форматами -->
                                    <div class="col-md-6 mb-3">
                                        <label for="measurements_file" class="form-label">
                                            <i class="fas fa-ruler me-1"></i>Замеры (все популярные форматы)
                                        </label>
                                        <div class="yandex-upload-container" data-field-name="measurements_file">
                                            <div class="upload-area">
                                                <input type="file"
                                                       class="yandex-file-input yandex-upload d-none"
                                                       id="measurements_file"
                                                       name="measurements_file"
                                                       data-upload-type="yandex"
                                                       accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png,.gif,.webp,.bmp,.svg,.dwg,.dxf,.pln,.rvt,.skp,.3ds,.max,.obj,.fbx,.ifc,.step,.stp,.iges,.igs,.sat,.x_t,.x_b,.catpart,.catproduct,.txt,.rtf,.odt,.ods,.csv,.zip,.rar,.7z">
                                                <div class="upload-hint text-center p-3" onclick="document.getElementById('measurements_file').click()">
                                                    <i class="fas fa-cloud-upload-alt fa-2x text-muted mb-2"></i>
                                                    <p class="mb-1">Нажмите для загрузки замеров</p>
                                                    <small class="text-muted">Все форматы до 1GB</small>
                                                </div>
                                                <div class="current-file-info" style="display: none;">
                                                    <!-- Здесь будет отображаться информация о текущем файле -->
                                                </div>
                                            </div>
                                        </div>
                                        @if($deal->yandex_url_measurements_file)
                                            <div class="mt-2">
                                                <a href="{{ $deal->yandex_url_measurements_file }}" target="_blank" class="btn btn-sm btn-outline-success">
                                                    <i class="fas fa-cloud-download-alt me-1"></i>{{ $deal->original_name_measurements_file ?? 'Просмотр файла' }}
                                                </a>
                                            </div>
                                        @endif
                                        <!-- Динамический контейнер для новых ссылок -->
                                        <div class="yandex-file-links-container" data-field="measurements_file"></div>
                                    </div>

                                    <!-- Финал проекта с расширенными форматами -->
                                    <div class="col-md-6 mb-3">
                                        <label for="final_project_file" class="form-label">
                                            <i class="fas fa-file-archive me-1"></i>Финал проекта (документы и изображения)
                                        </label>
                                        <div class="yandex-upload-container" data-field-name="final_project_file">
                                            <div class="upload-area">
                                                <input type="file"
                                                       class="yandex-file-input yandex-upload d-none"
                                                       id="final_project_file"
                                                       name="final_project_file"
                                                       data-upload-type="yandex"
                                                       accept=".pdf,.doc,.docx,.ppt,.pptx,.jpg,.jpeg,.png,.gif,.webp,.bmp,.svg,.tiff,.tif,.psd,.ai,.eps,.indd,.zip,.rar,.7z,.dwg,.dxf,.pln,.rvt">
                                                <div class="upload-hint text-center p-3" onclick="document.getElementById('final_project_file').click()">
                                                    <i class="fas fa-cloud-upload-alt fa-2x text-muted mb-2"></i>
                                                    <p class="mb-1">Нажмите для загрузки финального проекта</p>
                                                    <small class="text-muted">Документы и изображения до 1GB</small>
                                                </div>
                                                <div class="current-file-info" style="display: none;">
                                                    <!-- Здесь будет отображаться информация о текущем файле -->
                                                </div>
                                            </div>
                                        </div>
                                        @if($deal->yandex_url_final_project_file)
                                            <div class="mt-2">
                                                <a href="{{ $deal->yandex_url_final_project_file }}" target="_blank" class="btn btn-sm btn-outline-success">
                                                    <i class="fas fa-cloud-download-alt me-1"></i>{{ $deal->original_name_final_project_file ?? 'Просмотр файла' }}
                                                </a>
                                            </div>
                                        @endif
                                        <!-- Динамический контейнер для новых ссылок -->
                                        <div class="yandex-file-links-container" data-field="final_project_file"></div>
                                    </div>

                                    <!-- Акт выполненных работ с расширенными форматами -->
                                    <div class="col-md-6 mb-3">
                                        <label for="work_act" class="form-label">
                                            <i class="fas fa-file-signature me-1"></i>Акт выполненных работ (документы)
                                        </label>
                                        <div class="yandex-upload-container" data-field-name="work_act">
                                            <div class="upload-area">
                                                <input type="file"
                                                       class="yandex-file-input yandex-upload d-none"
                                                       id="work_act"
                                                       name="work_act"
                                                       data-upload-type="yandex"
                                                       accept=".pdf,.doc,.docx,.xls,.xlsx,.odt,.rtf,.txt,.jpg,.jpeg,.png,.zip,.rar,.7z">
                                                <div class="upload-hint text-center p-3" onclick="document.getElementById('work_act').click()">
                                                    <i class="fas fa-cloud-upload-alt fa-2x text-muted mb-2"></i>
                                                    <p class="mb-1">Нажмите для загрузки акта работ</p>
                                                    <small class="text-muted">Документы до 100MB</small>
                                                </div>
                                                <div class="current-file-info" style="display: none;">
                                                    <!-- Здесь будет отображаться информация о текущем файле -->
                                                </div>
                                            </div>
                                        </div>
                                        @if($deal->yandex_url_work_act)
                                            <div class="mt-2">
                                                <a href="{{ $deal->yandex_url_work_act }}" target="_blank" class="btn btn-sm btn-outline-success">
                                                    <i class="fas fa-cloud-download-alt me-1"></i>{{ $deal->original_name_work_act ?? 'Просмотр файла' }}
                                                </a>
                                            </div>
                                        @endif
                                        <!-- Динамический контейнер для новых ссылок -->
                                        <div class="yandex-file-links-container" data-field="work_act"></div>
                                    </div>

                                    <!-- Скрин чата с расширенными форматами изображений -->
                                    <div class="col-md-6 mb-3">
                                        <label for="chat_screenshot" class="form-label">
                                            <i class="fas fa-camera me-1"></i>Скрин чата с оценкой (все форматы изображений)
                                        </label>
                                        <div class="yandex-upload-container" data-field-name="chat_screenshot">
                                            <div class="upload-area">
                                                <input type="file"
                                                       class="yandex-file-input yandex-upload d-none"
                                                       id="chat_screenshot"
                                                       name="chat_screenshot"
                                                       data-upload-type="yandex"
                                                       accept="image/*,.pdf,.doc,.docx">
                                                <div class="upload-hint text-center p-3" onclick="document.getElementById('chat_screenshot').click()">
                                                    <i class="fas fa-cloud-upload-alt fa-2x text-muted mb-2"></i>
                                                    <p class="mb-1">Нажмите для загрузки скриншота чата</p>
                                                    <small class="text-muted">JPG, PNG, PDF, DOC до 100MB</small>
                                                </div>
                                                <div class="current-file-info" style="display: none;">
                                                    <!-- Здесь будет отображаться информация о текущем файле -->
                                                </div>
                                            </div>
                                        </div>
                                        <small class="form-text text-muted">
                                            Поддерживаются: JPG, PNG, GIF, WebP, BMP, SVG, TIFF, PDF, DOC, DOCX
                                        </small>
                                        @if($deal->yandex_url_chat_screenshot)
                                            <div class="mt-2">
                                                <a href="{{ $deal->yandex_url_chat_screenshot }}" target="_blank" class="btn btn-sm btn-outline-success">
                                                    <i class="fas fa-cloud-download-alt me-1"></i>{{ $deal->original_name_chat_screenshot ?? 'Просмотр файла' }}
                                                </a>
                                            </div>
                                        @endif
                                        <!-- Динамический контейнер для новых ссылок -->
                                        <div class="yandex-file-links-container" data-field="chat_screenshot"></div>
                                    </div>

                                    <!-- Исходный файл архикад с множественными САПР форматами -->
                                    <div class="col-md-6 mb-3">
                                        <label for="archicad_file" class="form-label">
                                            <i class="fas fa-drafting-compass me-1"></i>Исходные файлы САПР (все форматы)
                                        </label>
                                        <div class="yandex-upload-container" data-field-name="archicad_file">
                                            <div class="upload-area">
                                                <input type="file"
                                                       class="yandex-file-input yandex-upload d-none"
                                                       id="archicad_file"
                                                       name="archicad_file"
                                                       data-upload-type="yandex"
                                                       accept=".pln,.dwg,.dxf,.rvt,.skp,.3ds,.max,.obj,.fbx,.ifc,.step,.stp,.iges,.igs,.sat,.x_t,.x_b,.catpart,.catproduct,.zip,.rar,.7z">
                                                <div class="upload-hint text-center p-3" onclick="document.getElementById('archicad_file').click()">
                                                    <i class="fas fa-cloud-upload-alt fa-2x text-muted mb-2"></i>
                                                    <p class="mb-1">Нажмите для загрузки САПР файла</p>
                                                    <small class="text-muted">PLN, DWG, RVT, SKP и другие до 1GB</small>
                                                </div>
                                                <div class="current-file-info" style="display: none;">
                                                    <!-- Здесь будет отображаться информация о текущем файле -->
                                                </div>
                                            </div>
                                        </div>
                                        <small class="form-text text-muted">
                                            САПР: PLN, DWG, DXF, RVT, SKP, 3DS, MAX, OBJ, FBX, IFC, STEP, IGES и архивы
                                        </small>
                                        @if($deal->yandex_url_archicad_file)
                                            <div class="mt-2">
                                                <a href="{{ $deal->yandex_url_archicad_file }}" target="_blank" class="btn btn-sm btn-outline-success">
                                                    <i class="fas fa-cloud-download-alt me-1"></i>{{ $deal->original_name_archicad_file ?? 'Просмотр файла' }}
                                                </a>
                                            </div>
                                        @endif
                                        <!-- Динамический контейнер для новых ссылок -->
                                        <div class="yandex-file-links-container" data-field="archicad_file"></div>
                                    </div>
                                </div>
                            </div>

                            <!-- Вкладка: Документы -->
                            <div class="tab-pane fade" id="documents" role="tabpanel" aria-labelledby="documents-tab">
                                <div class="documents-module-simple">
                                    <!-- Заголовок модуля -->
                                    <div class="documents-header mb-4">
                                        <h3><i class="fas fa-folder-open me-2"></i>Документы сделки</h3>
                                        <p class="text-muted">Загрузите документы, связанные с данной сделкой</p>
                                    </div>

                                    <!-- Область загрузки -->
                                    <!-- Улучшенная область загрузки документов -->
                                    <div class="documents-upload-area mb-4">
                                        <div class="upload-container p-4 border rounded" id="documentsUploadArea"
                                             style="border: 2px dashed #007bff; background: #f8f9fa; text-align: center; cursor: pointer; transition: all 0.3s ease;">
                                            <div class="upload-content">
                                                <i class="fas fa-cloud-upload-alt fa-4x text-primary mb-3"></i>
                                                <h5 class="mb-3">Множественная загрузка документов</h5>
                                                <p class="text-muted mb-3">
                                                    <strong>Перетащите файлы сюда или нажмите для выбора</strong><br>
                                                    Поддерживаются: PDF, DOC, DOCX, XLS, XLSX, JPG, PNG, ZIP, RAR<br>
                                                    <small>Максимальный размер: 100 МБ на файл • Неограниченное количество файлов</small>
                                                </p>

                                                <button type="button" class="btn btn-primary btn-lg" id="uploadDocumentsBtn">
                                                    <i class="fas fa-plus me-2"></i>
                                                    <span class="upload-btn-text">Выбрать файлы</span>
                                                </button>

                                                <div class="mt-3">
                                                    <small class="text-muted">
                                                        <i class="fas fa-info-circle me-1"></i>
                                                        Поддерживается перетаскивание (drag & drop) • Автоматическая загрузка в локальное хранилище
                                                    </small>
                                                </div>
                                            </div>

                                            <input type="file"
                                                   id="documentUploadInput"
                                                   class="d-none"
                                                   multiple
                                                   accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png,.zip,.rar">
                                        </div>

                                        <!-- Информация о выбранных файлах -->
                                        <div id="filesCountInfo" class="mt-3" style="display: none;">
                                            <div class="alert alert-info border-start border-4 border-primary">
                                                <div class="d-flex align-items-center mb-2">
                                                    <i class="fas fa-file-alt me-2 text-primary"></i>
                                                    <span id="filesCountText" class="fw-bold">Файлы не выбраны</span>
                                                </div>
                                                <div id="selectedFilesList" class="selected-files-container"></div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Список загруженных документов -->
                                    <div class="uploaded-documents-section">
                                        <h4><i class="fas fa-list me-2"></i>Загруженные документы</h4>

                                        @php
                                            $documentsArray = [];
                                            if (isset($deal->documents)) {
                                                if (is_string($deal->documents)) {
                                                    $documentsArray = json_decode($deal->documents, true) ?: [];
                                                } elseif (is_array($deal->documents)) {
                                                    $documentsArray = $deal->documents;
                                                }
                                            }
                                        @endphp

                                        @if(count($documentsArray) > 0)
                                            <div class="uploaded-files">
                                                @foreach($documentsArray as $index => $document)
                                                    <div class="file-item card mb-2">
                                                        <div class="card-body py-2">
                                                            <div class="d-flex justify-content-between align-items-center">
                                                                <div>
                                                                    <i class="fas fa-file me-2"></i>
                                                                    @if(is_string($document))
                                                                        <strong>{{ basename($document) }}</strong>
                                                                        <small class="text-muted">(Путь к файлу)</small>
                                                                    @else
                                                                        <strong>{{ $document['original_name'] ?? $document['name'] ?? 'Документ' }}</strong>
                                                                        <small class="text-muted">({{ $document['size'] ?? '0' }} байт)</small>
                                                                    @endif
                                                                </div>
                                                                <div>
                                                                    @if(is_string($document))
                                                                        <a href="{{ asset('storage/' . $document) }}" target="_blank" class="btn btn-sm btn-outline-primary me-1">
                                                                            <i class="fas fa-eye"></i>
                                                                        </a>
                                                                    @elseif(isset($document['url']))
                                                                        <a href="{{ $document['url'] }}" target="_blank" class="btn btn-sm btn-outline-primary me-1">
                                                                            <i class="fas fa-eye"></i>
                                                                        </a>
                                                                    @endif
                                                                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="deleteDocument({{ json_encode($index) }})">
                                                                        <i class="fas fa-trash"></i>
                                                                    </button>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @else
                                            <div class="documents-empty-state">
                                                <div class="text-center py-4">
                                                    <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                                    <h5 class="text-muted">Документы не загружены</h5>
                                                    <p class="text-muted">Используйте область загрузки выше для добавления документов</p>
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                </div>

                                <!-- Стили для модуля документов -->
                                <style>
                                .documents-module-simple {
                                    padding: 20px;
                                }

                                .documents-upload-area .upload-container:hover {
                                    border-color: #007bff !important;
                                    background: #f0f7ff !important;
                                }

                                .documents-upload-area .upload-container.drag-over {
                                    border-color: #28a745 !important;
                                    background: #f0fff4 !important;
                                    transform: scale(1.02);
                                }

                                .file-item {
                                    border-left: 4px solid #007bff;
                                    transition: all 0.3s ease;
                                }

                                .file-item:hover {
                                    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
                                    transform: translateY(-2px);
                                }

                                .documents-empty-state {
                                    border: 1px dashed #ddd;
                                    border-radius: 8px;
                                    background: #f8f9fa;
                                }

                                #filesCountInfo .alert {
                                    margin-bottom: 0;
                                }

                                .selected-files-preview {
                                    max-height: 200px;
                                    overflow-y: auto;
                                    border: 1px solid #dee2e6;
                                    border-radius: 0.375rem;
                                    padding: 0.5rem;
                                    background: #fff;
                                    margin-top: 0.5rem;
                                }

                                .selected-file-item {
                                    display: flex;
                                    align-items: center;
                                    padding: 0.25rem 0.5rem;
                                    margin: 0.25rem 0;
                                    border-radius: 0.25rem;
                                    background: #f8f9fa;
                                    font-size: 0.875rem;
                                    border-left: 3px solid #007bff;
                                }

                                .selected-file-item .file-icon {
                                    margin-right: 0.5rem;
                                    color: #007bff;
                                }

                                .selected-file-item .file-size {
                                    margin-left: auto;
                                    color: #6c757d;
                                    font-size: 0.75rem;
                                }

                                .upload-progress {
                                    position: relative;
                                    overflow: hidden;
                                }

                                .upload-progress::after {
                                    content: '';
                                    position: absolute;
                                    top: 0;
                                    left: -100%;
                                    width: 100%;
                                    height: 100%;
                                    background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
                                    animation: loading 1.5s infinite;
                                }

                                @keyframes loading {
                                    0% { left: -100%; }
                                    100% { left: 100%; }
                                }
                                </style>

                                <!-- JavaScript для модуля документов -->
                                <script>
                                document.addEventListener('DOMContentLoaded', function() {
                                    console.log('🗂️ Инициализация простого модуля документов');
                                    initSimpleDocumentsModule();
                                });

                                function initSimpleDocumentsModule() {
                                    const uploadBtn = document.getElementById('uploadDocumentsBtn');
                                    const uploadInput = document.getElementById('documentUploadInput');
                                    const uploadArea = document.getElementById('documentsUploadArea');
                                    const filesCountInfo = document.getElementById('filesCountInfo');
                                    const filesCountText = document.getElementById('filesCountText');

                                    if (!uploadBtn || !uploadInput) {
                                        console.log('Элементы загрузки документов не найдены');
                                        return;
                                    }

                                    console.log('✅ Элементы модуля документов найдены');

                                    // Дополнительная проверка существования элементов
                                    if (!filesCountInfo) {
                                        console.warn('⚠️ Элемент filesCountInfo не найден');
                                    }
                                    if (!filesCountText) {
                                        console.warn('⚠️ Элемент filesCountText не найден');
                                    }

                                    // Обработчик клика по кнопке загрузки
                                    uploadBtn.addEventListener('click', function() {
                                        console.log('Клик по кнопке выбора файлов');
                                        uploadInput.click();
                                    });

                                    // Обработчик клика по области загрузки
                                    uploadArea.addEventListener('click', function() {
                                        console.log('Клик по области загрузки');
                                        uploadInput.click();
                                    });

                                    // Обработчик выбора файлов
                                    uploadInput.addEventListener('change', function(e) {
                                        const files = e.target.files;
                                        console.log('Выбрано файлов:', files.length);

                                        if (files.length > 0) {
                                            updateFilesCountDisplay(files);
                                            showSelectedFiles(files);

                                            // Сразу начинаем загрузку
                                            uploadFiles(files);
                                        }
                                    });

                                    // Drag & Drop функциональность
                                    if (uploadArea) {
                                        setupDragAndDrop(uploadArea, uploadInput);
                                    }

                                    function updateFilesCountDisplay(files) {
                                        if (filesCountInfo && filesCountText) {
                                            const count = files.length;
                                            const word = getFileWord(count);
                                            const totalSize = Array.from(files).reduce((sum, file) => sum + file.size, 0);
                                            const formattedSize = formatFileSize(totalSize);

                                            filesCountText.textContent = `${count} ${word} выбрано (общий размер: ${formattedSize})`;
                                            if (filesCountInfo) {
                                                filesCountInfo.style.display = 'block';
                                            }
                                        }
                                    }

                                    function getFileWord(count) {
                                        if (count === 1) return 'файл';
                                        if (count >= 2 && count <= 4) return 'файла';
                                        return 'файлов';
                                    }

                                    function showSelectedFiles(files) {
                                        const selectedFilesList = document.getElementById('selectedFilesList');
                                        if (!selectedFilesList) return;

                                        selectedFilesList.innerHTML = '';

                                        if (files.length > 0) {
                                            const previewContainer = document.createElement('div');
                                            previewContainer.className = 'selected-files-preview';

                                            Array.from(files).forEach(file => {
                                                const fileItem = document.createElement('div');
                                                fileItem.className = 'selected-file-item';

                                                const icon = getFileIcon(file.name);
                                                const size = formatFileSize(file.size);

                                                fileItem.innerHTML = `
                                                    <i class="fas ${icon} file-icon"></i>
                                                    <span class="file-name">${file.name}</span>
                                                    <span class="file-size">${size}</span>
                                                `;

                                                previewContainer.appendChild(fileItem);
                                            });

                                            selectedFilesList.appendChild(previewContainer);
                                        }
                                    }

                                    function formatFileSize(bytes) {
                                        if (bytes === 0) return '0 Байт';
                                        const k = 1024;
                                        const sizes = ['Байт', 'КБ', 'МБ', 'ГБ'];
                                        const i = Math.floor(Math.log(bytes) / Math.log(k));
                                        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
                                    }

                                    function getFileIcon(fileName) {
                                        const extension = fileName.split('.').pop().toLowerCase();

                                        switch (extension) {
                                            case 'pdf': return 'fa-file-pdf';
                                            case 'doc':
                                            case 'docx': return 'fa-file-word';
                                            case 'xls':
                                            case 'xlsx': return 'fa-file-excel';
                                            case 'ppt':
                                            case 'pptx': return 'fa-file-powerpoint';
                                            case 'zip':
                                            case 'rar':
                                            case '7z': return 'fa-file-archive';
                                            case 'jpg':
                                            case 'jpeg':
                                            case 'png':
                                            case 'gif':
                                            case 'webp':
                                            case 'svg': return 'fa-file-image';
                                            case 'txt': return 'fa-file-alt';
                                            case 'mp4':
                                            case 'avi':
                                            case 'mov': return 'fa-file-video';
                                            case 'mp3':
                                            case 'wav': return 'fa-file-audio';
                                            default: return 'fa-file';
                                        }
                                    }

                                    function setupDragAndDrop(area, input) {
                                        area.addEventListener('dragover', function(e) {
                                            e.preventDefault();
                                            e.stopPropagation();
                                            area.classList.add('drag-over');
                                        });

                                        area.addEventListener('dragleave', function(e) {
                                            e.preventDefault();
                                            e.stopPropagation();
                                            area.classList.remove('drag-over');
                                        });

                                        area.addEventListener('drop', function(e) {
                                            e.preventDefault();
                                            e.stopPropagation();
                                            area.classList.remove('drag-over');

                                            const files = e.dataTransfer.files;
                                            if (files.length > 0) {
                                                // Создаем новый DataTransfer объект для установки files
                                                const dataTransfer = new DataTransfer();
                                                Array.from(files).forEach(file => {
                                                    dataTransfer.items.add(file);
                                                });

                                                input.files = dataTransfer.files;
                                                const event = new Event('change', { bubbles: true });
                                                input.dispatchEvent(event);
                                            }
                                        });
                                    }

                                    async function uploadFiles(files) {
                                        console.log('Начинаем загрузку файлов:', files.length);

                                        const btnText = uploadBtn.querySelector('.upload-btn-text');
                                        const btnIcon = uploadBtn.querySelector('i');
                                        const uploadArea = document.getElementById('documentsUploadArea');

                                        // Показываем процесс загрузки
                                        if (btnText) btnText.textContent = `Загружаем ${files.length} файл(ов)...`;
                                        if (btnIcon) {
                                            btnIcon.className = 'fas fa-spinner fa-spin';
                                        }
                                        if (uploadArea) {
                                            uploadArea.classList.add('upload-progress');
                                        }
                                        uploadBtn.disabled = true;

                                        try {
                                            const formData = new FormData();

                                            // Добавляем файлы
                                            for (let i = 0; i < files.length; i++) {
                                                formData.append('documents[]', files[i]);
                                                console.log(`Добавлен файл ${i + 1}: ${files[i].name} (${formatFileSize(files[i].size)})`);
                                            }

                                            // Добавляем CSRF токен
                                            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                                            if (csrfToken) {
                                                formData.append('_token', csrfToken);
                                            }

                                            // Добавляем ID сделки
                                            const dealId = getDealId();
                                            if (dealId) {
                                                formData.append('deal_id', dealId);
                                            }

                                            const response = await fetch(`/deal/${dealId}/upload-documents`, {
                                                method: 'POST',
                                                body: formData,
                                                headers: {
                                                    'X-Requested-With': 'XMLHttpRequest',
                                                }
                                            });

                                            const result = await response.json();

                                            if (response.ok && result.success) {
                                                const successMsg = `Успешно загружено ${files.length} файл(ов)!`;
                                                showNotification(successMsg, 'success');

                                                // Обновляем список документов
                                                if (result.documents) {
                                                    updateDocumentsList(result.documents);
                                                }

                                                // Сбрасываем форму
                                                resetUploadForm();
                                            } else {
                                                throw new Error(result.message || 'Ошибка при загрузке файлов');
                                            }

                                        } catch (error) {
                                            console.error('Ошибка загрузки:', error);
                                            showNotification('Ошибка при загрузке файлов: ' + error.message, 'error');
                                        } finally {
                                            // Возвращаем кнопку в исходное состояние
                                            if (btnText) btnText.textContent = 'Выбрать файлы';
                                            if (btnIcon) {
                                                btnIcon.className = 'fas fa-plus';
                                            }
                                            if (uploadArea) {
                                                uploadArea.classList.remove('upload-progress');
                                            }
                                            uploadBtn.disabled = false;
                                        }
                                    }

                                    function resetUploadForm() {
                                        if (uploadInput) {
                                            uploadInput.value = '';
                                        }

                                        if (filesCountInfo) {
                                            filesCountInfo.style.display = 'none';
                                        }

                                        // Очищаем список выбранных файлов
                                        const selectedFilesList = document.getElementById('selectedFilesList');
                                        if (selectedFilesList) {
                                            selectedFilesList.innerHTML = '';
                                        }
                                    }

                                    function updateDocumentsList(documents) {
                                        // Найдем контейнер для документов
                                        const emptyState = document.querySelector('.documents-empty-state');

                                        if (emptyState && documents.length > 0) {
                                            if (emptyState) {
                                                emptyState.style.display = 'none';
                                            }

                                            // Добавляем новые документы
                                            // Это базовая реализация, можно улучшить
                                            location.reload(); // Перезагружаем страницу для отображения новых документов
                                        }
                                    }

                                    function getDealId() {
                                        // Получаем ID сделки из URL или скрытого поля
                                        const url = window.location.pathname;
                                        const matches = url.match(/\/deal\/(\d+)/);
                                        return matches ? matches[1] : null;
                                    }

                                    function showNotification(message, type) {
                                        // Простая реализация уведомлений
                                        const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
                                        const icon = type === 'success' ? 'fas fa-check-circle' : 'fas fa-exclamation-triangle';

                                        const notification = document.createElement('div');
                                        notification.className = `alert ${alertClass} alert-dismissible fade show position-fixed`;
                                        notification.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
                                        notification.innerHTML = `
                                            <i class="${icon} me-2"></i>
                                            ${message}
                                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                        `;

                                        document.body.appendChild(notification);

                                        // Автоматическое удаление через 5 секунд
                                        setTimeout(() => {
                                            if (notification.parentNode) {
                                                notification.parentNode.removeChild(notification);
                                            }
                                        }, 5000);
                                    }
                                }

                                function deleteDocument(documentId) {
                                    if (confirm('Вы уверены, что хотите удалить этот документ?')) {
                                        // Здесь должна быть логика удаления документа
                                        console.log('Удаление документа:', documentId);
                                        showNotification('Функция удаления будет реализована', 'info');
                                    }
                                }
                                </script>
                            </div>

                            <!-- Вкладка: Бриф -->
                            <div class="tab-pane fade" id="brief" role="tabpanel" aria-labelledby="brief-tab">
                                <div class="row">
                                    <div class="col-12">
                                        <!-- Текущий статус брифа -->
                                        <div class="mb-4" style="border: 1px solid #dee2e6; border-radius: 8px; background: #f8f9fa; padding: 20px;">
                                            @php
                                                // Получаем привязанный бриф (новый унифицированный)
                                                $attachedBrief = $deal->getRelation('brief');
                                            @endphp

                                            @if($attachedBrief)
                                                <div class="d-flex justify-content-between align-items-start">
                                                    <div>
                                                        <h6 class="mb-2" style="color: #28a745; font-weight: 600;">
                                                            ✅ Бриф привязан к сделке
                                                        </h6>
                                                        <p class="mb-0" style="color: #6c757d; font-size: 14px;">
                                                            Тип: {{ $attachedBrief->type->label() }} #{{ $attachedBrief->id }}
                                                        </p>
                                                    </div>
                                                    <div class="d-flex gap-2">
                                                        <a href="{{ route('briefs.show', $attachedBrief->id) }}"
                                                           class="btn btn-outline-primary btn-sm"
                                                           target="_blank"
                                                           title="Открыть бриф в новой вкладке">
                                                            <i class="fas fa-eye me-1"></i>
                                                            Просмотр брифа
                                                        </a>
                                                        <button type="button" class="btn btn-outline-danger btn-sm" id="detachBriefBtn" data-deal-id="{{ $deal->id }}">
                                                            <i class="fas fa-unlink me-1"></i>
                                                            Отвязать бриф
                                                        </button>
                                                    </div>
                                                </div>
                                            @else
                                                <h6 class="mb-2" style="color: #dc3545; font-weight: 600;">
                                                    ⚠️ Бриф не привязан к сделке
                                                </h6>
                                                <p class="mb-0" style="color: #6c757d; font-size: 14px;">
                                                    Найдите и привяжите подходящий бриф по номеру телефона клиента
                                                </p>
                                            @endif
                                        </div>

                                        <!-- Поиск брифа -->
                                        @if(!$attachedBrief)
                                        <div class="mb-4">
                                            <h6 class="mb-3" style="font-weight: 600;">Поиск брифов</h6>

                                            <div class="row">
                                                <div class="col-md-8 mb-3">
                                                    <label for="client_phone_search" class="form-label">Телефон клиента</label>
                                                    <input type="text" class="form-control" id="client_phone_search"
                                                           value="{{ $deal->client_phone }}" readonly
                                                           style="background-color: #e9ecef;">
                                                </div>
                                                <div class="col-md-4 mb-3 d-flex align-items-end">
                                                    <button type="button" class="btn btn-primary w-100" id="searchBriefBtn"
                                                            data-deal-id="{{ $deal->id }}" data-client-phone="{{ $deal->client_phone }}">
                                                        Найти брифы
                                                    </button>
                                                </div>
                                            </div>

                                            <!-- Результаты поиска -->
                                            <div id="briefSearchResults" style="display: none;" class="mt-4">
                                                <div style="border-top: 1px solid #dee2e6; padding-top: 16px;">
                                                    <h6 class="mb-3" style="font-weight: 600;">Найденные брифы</h6>
                                                    <div id="briefResultsList"></div>
                                                </div>
                                            </div>
                                        </div>
                                        @endif

                                        <!-- Справочная информация -->
                                        <div style="border: 1px solid #e3f2fd; border-radius: 8px; background: #f3f8ff; padding: 16px;">
                                            <h6 class="mb-2" style="color: #1976d2; font-weight: 600;">Справка по статусам брифов</h6>
                                            <div style="font-size: 14px; color: #424242;">
                                                <div class="mb-1"><strong>Завершенный бриф</strong> — можно привязать к сделке</div>
                                                <div class="mb-1"><strong>Черновик</strong> — нельзя привязать (требует завершения)</div>
                                                <div class="mb-0"><strong>Уже привязанный</strong> — используется в другой сделке</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Кнопки управления -->
                        <div class="row mt-4">
                            <div class="col-12">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div class="flex gap-2 justify-content-start">
                                        <button type="submit" class="btn btn-primary btn-lg me-2" id="saveButton">
                                            <i class="fas fa-save me-2"></i>Сохранить изменения
                                        </button>
                                    </div>
                                    <div>
                                        @if(in_array(Auth::user()->status, ['admin']))
                                            <button type="button" class="btn btn-danger" onclick="confirmDeleteDeal({{ $deal->id }})">
                                                <i class="fas fa-trash me-1"></i>Удалить сделку
                                            </button>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

        </div>
    </div>
</div>





<!-- Подключение инициализации Select2 для страницы редактирования сделки -->
<script>
// Безопасная инициализация Select2
document.addEventListener('DOMContentLoaded', function() {
    // Ждем загрузки jQuery
    function initSelect2() {
        if (typeof $ !== 'undefined' && typeof $.fn.select2 !== 'undefined') {
            // Инициализируем Select2 для всех select элементов
            $('select').each(function() {
                if (!$(this).hasClass('select2-hidden-accessible')) {
                    $(this).select2({
                        width: '100%',
                        dropdownAutoWidth: true,
                        language: 'ru'
                    });
                }
            });
            console.log('✅ Select2 инициализирован для всех элементов');
        } else {
            console.log('⏳ Ожидание jQuery и Select2...');
            setTimeout(initSelect2, 100);
        }
    }

    initSelect2();
});
</script>

<!-- Тестовый хелпер для отладки Select2 (только в режиме разработки) -->
@if(config('app.debug'))
<script src="{{ asset('js/select2-test-helper.js') }}"></script>
@endif

<!-- Простая инициализация -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    console.log('🚀 Простая инициализация...');

    // Инициализация Bootstrap вкладок
    if (typeof bootstrap !== 'undefined') {
        const triggerTabList = [].slice.call(document.querySelectorAll('.deal-tab-button[data-bs-toggle="tab"]'));
        triggerTabList.map(function (triggerEl) {
            return new bootstrap.Tab(triggerEl);
        });
        console.log('✅ Вкладки Bootstrap инициализированы');
    }

    // Инициализация AJAX системы редактирования
    if (typeof window.dealEditAjax !== 'undefined') {
        console.log('🔧 Инициализация AJAX системы...');
        window.dealEditAjax.init();
    }
});


document.addEventListener("DOMContentLoaded", () => {
    const inputs = document.querySelectorAll("input.maskphone");
    for (let i = 0; i < inputs.length; i++) {
        const input = inputs[i];
        input.addEventListener("input", mask);
        input.addEventListener("focus", mask);
        input.addEventListener("blur", mask);
    }
    function mask(event) {
        const blank = "+_ (___) ___-__-__";
        let i = 0;
        const val = this.value.replace(/\D/g, "").replace(/^8/, "7").replace(/^9/, "79");
        this.value = blank.replace(/./g, function(char) {
            if (/[_\d]/.test(char) && i < val.length) return val.charAt(i++);
            return i >= val.length ? "" : char;
        });
        if (event.type === "blur") {
            if (this.value.length == 2) this.value = "";
        } else {
            // Добавляем проверку наличия метода setSelectionRange
            if (this.setSelectionRange) {
                this.setSelectionRange(this.value.length, this.value.length);
            }
        }
    }
});
</script>

<!-- Подключение компонента AJAX обновления сделки -->
@include('deals.partials.components.ajax_deal_update')

@endsection

