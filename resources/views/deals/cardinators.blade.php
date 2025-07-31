<script>
// Обработчик глобальных ошибок JavaScript
window.addEventListener('error', function(e) {
    console.error('JavaScript Error:', e.error || e.message, 'at', e.filename, ':', e.lineno);
    
    // Если ошибка связана с LargeFileUploader, пытаемся переинициализировать
    if (e.message && e.message.includes('LargeFileUploader')) {
        console.warn('Пытаемся переинициализировать LargeFileUploader');
        setTimeout(function() {
            if (typeof window.LargeFileUploader !== 'undefined' && !window.largeFileUploader) {
                try {
                    window.largeFileUploader = new window.LargeFileUploader();
                    window.largeFileUploader.init();
                    console.log('LargeFileUploader успешно переинициализирован');
                } catch (err) {
                    console.error('Не удалось переинициализировать LargeFileUploader:', err);
                }
            }
        }, 1000);
    }
});

// Обработчик необработанных промисов
window.addEventListener('unhandledrejection', function(e) {
    console.error('Unhandled Promise Rejection:', e.reason);
});
</script>

<div class="brifs" id="brifs">
    <div class="page-header-with-admin">
        <h1 class="flex">Ваши сделки</h1>
        
        @if(Auth::user()->status === 'admin')
        <!-- Быстрый доступ к логам для админа -->
        <div class="admin-quick-access">
            <div class="dropdown">
                <a href="{{ route('deal.global_logs') }}" class="admin-logs-btn dropdown-toggle" 
                   title="Перейти к логам действий со сделками" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="fas fa-clipboard-list"></i>
                    <span>Логи действий</span>
                    <div class="logs-counter">
                        @php
                            $todayLogs = \App\Models\DealChangeLog::whereDate('created_at', today())->count();
                        @endphp
                        {{ $todayLogs }}
                    </div>
                    <i class="fas fa-chevron-down ms-1" style="font-size: 0.7rem;"></i>
                </a>
                
                <ul class="dropdown-menu dropdown-menu-end admin-logs-dropdown">
                    <li>
                        <a class="dropdown-item" href="{{ route('deal.global_logs') }}">
                            <i class="fas fa-list-alt text-primary"></i>
                            Все логи действий
                        </a>
                    </li>
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <a class="dropdown-item" href="{{ route('deal.global_logs') }}?action_type=create">
                            <i class="fas fa-plus-circle text-success"></i>
                            Создание сделок
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item" href="{{ route('deal.global_logs') }}?action_type=update">
                            <i class="fas fa-edit text-warning"></i>
                            Редактирование
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item" href="{{ route('deal.global_logs') }}?action_type=delete">
                            <i class="fas fa-trash-alt text-danger"></i>
                            Удаление сделок
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item" href="{{ route('deal.global_logs') }}?action_type=status_change">
                            <i class="fas fa-exchange-alt text-info"></i>
                            Смена статусов
                        </a>
                    </li>
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <a class="dropdown-item" href="{{ route('deal.global_logs') }}?date_from={{ date('Y-m-d') }}">
                            <i class="fas fa-calendar-day text-secondary"></i>
                            Только сегодня
                        </a>
                    </li>
                </ul>
            </div>
        </div>
        @endif
    </div>

    @if(Auth::user()->status === 'admin')
    <!-- Расширенная админ-панель для глобального мониторинга сделок -->
    <div class="admin-deals-monitoring-panel mb-4">
        <div class="card border-danger">
            <div class="card-header bg-danger text-white">
                <h5 class="mb-0">
                    <i class="fas fa-shield-alt"></i> Административный контроль и мониторинг сделок
                </h5>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-8">
                        <div class="row">
                            <div class="col-md-6 mb-2">
                                <a href="{{ route('deal.global_logs') }}" class="btn btn-outline-danger btn-lg w-100">
                                    <i class="fas fa-list-alt"></i> Глобальные логи действий
                                </a>
                                <small class="text-muted d-block mt-1">
                                    Просмотр всех действий: создание, редактирование, удаление сделок
                                </small>
                            </div>
                            <div class="col-md-6 mb-2">
                                <a href="{{ route('deal.global_logs') }}?action_type=delete" class="btn btn-outline-warning btn-lg w-100">
                                    <i class="fas fa-trash-alt"></i> Логи удалений
                                </a>
                                <small class="text-muted d-block mt-1">
                                    Отслеживание всех удалённых сделок
                                </small>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-2">
                                <a href="{{ route('deal.global_logs') }}?action_type=update" class="btn btn-outline-info btn-lg w-100">
                                    <i class="fas fa-edit"></i> Логи редактирования
                                </a>
                                <small class="text-muted d-block mt-1">
                                    История всех изменений в сделках
                                </small>
                            </div>
                            <div class="col-md-6 mb-2">
                                <a href="{{ route('deal.global_logs') }}?action_type=create" class="btn btn-outline-success btn-lg w-100">
                                    <i class="fas fa-plus-circle"></i> Логи создания
                                </a>
                                <small class="text-muted d-block mt-1">
                                    Отслеживание новых сделок
                                </small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="admin-stats">
                            @php
                                $stats = \App\Helpers\DealLogHelper::getLogStatistics();
                            @endphp
                            <div class="stat-item">
                                <span class="stat-value">{{ $stats['total_logs'] }}</span>
                                <span class="stat-label">Всего записей логов</span>
                            </div>
                            <div class="stat-item">
                                <span class="stat-value">{{ $stats['today_logs'] }}</span>
                                <span class="stat-label">Действий сегодня</span>
                            </div>
                            <div class="stat-item">
                                <span class="stat-value text-warning">{{ $stats['delete_actions'] }}</span>
                                <span class="stat-label">Удалённых сделок</span>
                            </div>
                            <div class="stat-item">
                                <span class="stat-value text-info">{{ $stats['week_logs'] }}</span>
                                <span class="stat-label">За неделю</span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Быстрые фильтры -->
                <div class="row">
                    <div class="col-12">
                        <div class="card bg-light">
                            <div class="card-body py-2">
                                <h6 class="card-title mb-2">
                                    <i class="fas fa-filter"></i> Быстрые фильтры
                                </h6>
                                <div class="btn-group btn-group-sm" role="group">
                                    <a href="{{ route('deal.global_logs') }}?date_from={{ date('Y-m-d') }}" 
                                       class="btn btn-outline-primary btn-sm">
                                        <i class="fas fa-calendar-day"></i> Сегодня
                                    </a>
                                    <a href="{{ route('deal.global_logs') }}?date_from={{ date('Y-m-d', strtotime('-7 days')) }}" 
                                       class="btn btn-outline-primary btn-sm">
                                        <i class="fas fa-calendar-week"></i> За неделю
                                    </a>
                                    <a href="{{ route('deal.global_logs') }}?date_from={{ date('Y-m-d', strtotime('-30 days')) }}" 
                                       class="btn btn-outline-primary btn-sm">
                                        <i class="fas fa-calendar-alt"></i> За месяц
                                    </a>
                                    <a href="{{ route('deal.global_logs') }}?action_type=status_change" 
                                       class="btn btn-outline-secondary btn-sm">
                                        <i class="fas fa-exchange-alt"></i> Смена статусов
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Кнопка-переключатель для фильтров -->
    <div class="filter-toggle" id="filter-toggle" data-target="#filter-panel"
        title="Нажмите, чтобы открыть или скрыть панель фильтров и поиска">
        <div class="filter-toggle-text">
            <i class="fas fa-filter"></i> Фильтры и поиск
            <span class="filter-counter" id="filter-counter" title="Количество активных фильтров">0</span>
        </div>
        <div class="filter-toggle-icon">
            <i class="fas fa-chevron-down"></i>
        </div>
    </div>

    <div class="filter filter-panel" id="filter-panel">
        <form method="GET" action="{{ route('deal.cardinator') }}">
        
            <div class="search">
                <!-- Панель фильтров -->
                <div class="filter-panels">
                    <div class="search__input search__input-styled">
                        <i class="fas fa-search search-icon"></i>
                        <input type="text" name="search" value="{{ request('search') }}"
                            placeholder="Поиск по имени, телефону, email, № проекта..."
                            title="Введите текст для поиска по данным сделок">
                    </div>

                    <!-- Первая строка фильтров -->
                    <div class="filter-container">
                        <!-- Фильтр по статусу -->
                        <div class="filter-group">
                            <label class="filter-label" title="Фильтр сделок по их текущему статус"><i
                                    class="fas fa-tag"></i> Статус</label>
                            <div class="select-container">
                                <div class="custom-multiselect">
                                    <div class="multiselect-selected" id="status-selected">Выберите статусы</div>
                                    <i class="fas fa-chevron-down select-icon"></i>
                                    <div class="multiselect-dropdown">
                                        <div class="multiselect-item">
                                            <input type="checkbox" id="status-all" class="status-checkbox"
                                                data-status-all>
                                            <label for="status-all">Все статусы</label>
                                        </div>
                                        @foreach ($statuses as $option)
                                            <div class="multiselect-item">
                                                <input type="checkbox" id="status-{{ $loop->index }}"
                                                    class="status-checkbox" name="statuses[]"
                                                    value="{{ $option }}"
                                                    {{ is_array(request('statuses')) && in_array($option, request('statuses')) ? 'checked' : '' }}>
                                                <label for="status-{{ $loop->index }}">{{ $option }}</label>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                                <!-- Скрытый input для обратной совместимости -->
                                <input type="hidden" name="status" id="status-hidden" value="{{ request('status') }}">
                            </div>
                        </div>
                        <!-- Фильтр по услуге из прайса -->
                        <div class="filter-group">
                            <label class="filter-label"><i class="fas fa-list-check"></i> Услуга</label>
                            <div class="select-container">
                                <select name="price_service_option" class="filter-select">
                                    <option value="">Все услуги</option>
                                    <option value="Визуализация на одну комнату"
                                        {{ request('price_service_option') == 'Визуализация на одну комнату' ? 'selected' : '' }}>
                                        Визуализация на одну комнату</option>
                                    <option value="экспресс планировка"
                                        {{ request('price_service_option') == 'экспресс планировка' ? 'selected' : '' }}>
                                        Экспресс планировка</option>
                                    <option value="экспресс планировка с коллажами"
                                        {{ request('price_service_option') == 'экспресс планировка с коллажами' ? 'selected' : '' }}>
                                        Экспресс планировка с коллажами</option>
                                    <option value="экспресс проект с электрикой"
                                        {{ request('price_service_option') == 'экспресс проект с электрикой' ? 'selected' : '' }}>
                                        Экспресс проект с электрикой</option>
                                    <option value="экспресс планировка с электрикой и коллажами"
                                        {{ request('price_service_option') == 'экспресс планировка с электрикой и коллажами' ? 'selected' : '' }}>
                                        Экспресс планировка с электрикой и коллажами</option>
                                
                                    
                                    <option value="экспресс рабочий проект"
                                        {{ request('price_service_option') == 'экспресс рабочий проект' ? 'selected' : '' }}>
                                        Экспресс рабочий проект</option>
                                    <option value="экспресс эскизный проект с рабочей документацией"
                                        {{ request('price_service_option') == 'экспресс эскизный проект с рабочей документацией' ? 'selected' : '' }}>
                                        Экспресс эскизный проект с рабочей документацией</option>
                                    <option value="экспресс 3Dвизуализация с коллажами"
                                        {{ request('price_service_option') == 'экспресс 3Dвизуализация с коллажами' ? 'selected' : '' }}>
                                        экспресс 3Dвизуализация с коллажами</option>
                                    <option value="экспресс полный дизайн-проект"
                                        {{ request('price_service_option') == 'экспресс полный дизайн-проект' ? 'selected' : '' }}>
                                        Экспресс полный дизайн-проект</option>
                                    <option value="360 градусов"
                                        {{ request('price_service_option') == '360 градусов' ? 'selected' : '' }}>360
                                        градусов</option>
                                    <option value="Ландшафт"
                                        {{ request('price_service_option') == 'Ландшафт' ? 'selected' : '' }}>
                                        Ландшафт</option>
                                    <option value="экспресс экстерьер"
                                        {{ request('price_service_option') == 'экспресс экстерьер' ? 'selected' : '' }}>
                                        Экспресс экстерьер</option>
                                    <option value="экспресс эскизный экстерьер"
                                        {{ request('price_service_option') == 'экспресс эскизный экстерьер' ? 'selected' : '' }}>
                                        Экспресс эскизный экстерьер</option>
                                </select>
                                <i class="fas fa-chevron-down select-icon"></i>
                            </div>
                        </div>
                        <div class="filter-group">
                            <label class="filter-label" title="Фильтрация по периоду создания сделок"><i
                                    class="fas fa-calendar-alt"></i> Период создания</label>
                            <div class="date-filter-container">
                                <div class="date-input-wrapper">
                                    <i class="fas fa-calendar-day date-icon"></i>
                                    <input type="date" name="date_from" value="{{ request('date_from') }}"
                                        placeholder="Дата с" class="filter-date" title="Дата начала периода поиска">
                                </div>
                                <span class="date-separator"><i class="fas fa-arrow-right"></i></span>
                                <div class="date-input-wrapper">
                                    <i class="fas fa-calendar-day date-icon"></i>
                                    <input type="date" name="date_to" value="{{ request('date_to') }}"
                                        placeholder="Дата по" class="filter-date" title="Дата окончания периода поиска">
                                </div>
                            </div>
                        </div>


                    </div>
                    <!-- Вторая строка фильтров -->
                    <div class="filter-container-flex">
                        @if (Auth::user()->status == 'admin' || Auth::user()->status == 'coordinator')
                            <!-- Фильтр по партнеру (только для админа и координатора) с поиском -->
                            <div class="filter-group">
                                <label class="filter-label"><i class="fas fa-user-tie"></i> Партнер</label>
                                <div class="select-container">
                                    <select name="partner_id" id="partner_filter"
                                        class="filter-select select2-search" data-status="partner">
                                        <option value="">Все партнеры</option>
                                        @foreach (\App\Models\User::where('status', 'partner')->orderBy('name')->get() as $partner)
                                            <option value="{{ $partner->id }}"
                                                {{ request('partner_id') == $partner->id ? 'selected' : '' }}>
                                                {{ $partner->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <!-- Фильтр по координатору (только для админа и координатора) с поиском -->
                            <div class="filter-group">
                                <label class="filter-label"><i class="fas fa-user-cog"></i> Координатор</label>
                                <div class="select-container">
                                    <select name="coordinator_id" id="coordinator_filter"
                                        class="filter-select select2-search" data-status="coordinator">
                                        <option value="">Все координаторы</option>
                                        @foreach (\App\Models\User::where('status', 'coordinator')->orderBy('name')->get() as $coordinator)
                                            <option value="{{ $coordinator->id }}"
                                                {{ request('coordinator_id') == $coordinator->id ? 'selected' : '' }}>
                                                {{ $coordinator->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        @endif

                        <!-- Сортировка по полям -->

                    </div>
                </div>
            </div>

            <!-- Панель действий фильтра -->
            <div class="filter-actions">
                <button type="submit" class="filter-button" title="Применить выбранные фильтры">
                    <i class="fas fa-filter"></i> Применить
                </button>
                <a href="{{ route('deal.cardinator') }}" class="filter-reset"
                    title="Сбросить все фильтры и параметры поиска">
                    <i class="fas fa-undo"></i> Сбросить
                </a>

                <!-- Переключение вида отображения -->
                <div class="variate__view">
                    <button type="submit" name="view_type" value="blocks"
                        title="Переключиться на отображение блоками"
                        class="view-button {{ $viewType === 'blocks' ? 'active-button' : '' }}">
                        <i class="fas fa-th-large"></i>
                    </button>
                    <button type="submit" name="view_type" value="table"
                        title="Переключиться на отображение таблицей"
                        class="view-button {{ $viewType === 'table' ? 'active-button' : '' }}">
                        <i class="fas fa-table"></i>
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>


<!-- JavaScript для подсчета активных фильтров и управления раскрывающимися фильтрами -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Используем глобальные функции из app.blade.php
        if (typeof updateFilterCounters === 'function') {
            updateFilterCounters();
        }

        // Обработчики для подсветки полей с фильтрами
        const dateFields = document.querySelectorAll('.filter-date');
        dateFields.forEach(field => {
            field.addEventListener('change', function() {
                if (this.value) {
                    this.classList.add('filter-active');
                } else {
                    this.classList.remove('filter-active');
                }
                if (typeof updateFilterCounters === 'function') {
                    updateFilterCounters();
                }
            });

            // Инициализация
            if (field.value) {
                field.classList.add('filter-active');
            }
        });

        // Подсветка селектов при изменении
        const selectFields = document.querySelectorAll('.filter-select');
        selectFields.forEach(field => {
            field.addEventListener('change', function() {
                if (this.value) {
                    this.classList.add('filter-active');
                } else {
                    this.classList.remove('filter-active');
                }
                if (typeof updateFilterCounters === 'function') {
                    updateFilterCounters();
                }
            });

            // Инициализация
            if (field.value) {
                field.classList.add('filter-active');
            }
        });

        // Инициализация всплывающих подсказок Bootstrap
        if (typeof $().tooltip === 'function') {
            $('[title]').tooltip({
                placement: 'auto',
                trigger: 'hover',
                delay: {
                    show: 1500,
                    hide: 0
                }, // Changed to 1.5 seconds with no hide delay
                animation: false, // Disable animations
                container: 'body', // Ensure proper positioning
                template: '<div class="tooltip custom-tooltip" role="tooltip"><div class="tooltip-arrow"></div><div class="tooltip-inner"></div></div>'
            });
        }

        // Инициализация Select2 для фильтров с поиском
        if (typeof $.fn.select2 !== 'undefined') {
            initializeSearchableFilters();
        } else {
            console.error('Select2 plugin not loaded');
        }

        // Обработка мультиселекта для статусов
        const multiselect = document.querySelector('.custom-multiselect');
        const dropdown = multiselect.querySelector('.multiselect-dropdown');
        const selected = multiselect.querySelector('#status-selected');
        const checkboxes = multiselect.querySelectorAll('.status-checkbox:not([data-status-all])');
        const checkboxAll = multiselect.querySelector('[data-status-all]');
        const hiddenInput = document.getElementById('status-hidden');

        // Инициализация текста выбранных элементов
        updateSelectedText();

        // При клике на селект открываем/закрываем дропдаун
        multiselect.addEventListener('click', function(e) {
            if (!e.target.closest('.multiselect-dropdown') || e.target.tagName === 'LABEL') {
                dropdown.classList.toggle('open');
                e.stopPropagation();
            }
        });

        // При клике вне мультиселекта - закрываем его
        document.addEventListener('click', function(e) {
            if (!e.target.closest('.custom-multiselect')) {
                dropdown.classList.remove('open');
            }
        });

        // Обработка выбора "Все статусы"
        checkboxAll.addEventListener('change', function() {
            const isChecked = this.checked;
            checkboxes.forEach(checkbox => {
                checkbox.checked = isChecked;
            });
            updateSelectedText();
        });

        // Обработка выбора отдельных статусов
        checkboxes.forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                // Если все чекбоксы выбраны - отмечаем "Все статусы"
                const allChecked = Array.from(checkboxes).every(cb => cb.checked);
                checkboxAll.checked = allChecked;

                // Если ни один не выбран - помечаем "Все статусы"
                if (Array.from(checkboxes).filter(cb => cb.checked).length === 0) {
                    checkboxAll.checked = true;
                }

                updateSelectedText();
            });
        });

        // Обновление текста выбранных элементов
        function updateSelectedText() {
            const checkedItems = Array.from(checkboxes).filter(cb => cb.checked);

            if (checkedItems.length === 0 || checkedItems.length === checkboxes.length) {
                selected.textContent = 'Все статусы';
                hiddenInput.value = '';
            } else {
                const texts = checkedItems.map(cb => {
                    return cb.nextElementSibling.textContent;
                });

                selected.textContent = texts.join(', ');

                // Обновляем скрытый input для совместимости
                hiddenInput.value = texts[0]; // записываем первый выбранный статус для совместимости
            }

            // Помечаем класс, если есть выбранные элементы
            if (checkedItems.length > 0 && checkedItems.length < checkboxes.length) {
                selected.classList.add('filter-active');
            } else {
                selected.classList.remove('filter-active');
            }

            // Обновляем счётчики фильтров
            if (typeof updateFilterCounters === 'function') {
                updateFilterCounters();
            }
        }
    });

    // Функция инициализации поисковых фильтров
    function initializeSearchableFilters() {
        $('.select2-search').each(function() {
            var $select = $(this);
            var status = $select.data('status');

            // Находим родительский контейнер
            var $parent = $select.closest('.filter-group');
            if (!$parent.length) {
                $parent = $select.parent();
            }

            // Установка position: relative для корректного позиционирования dropdown
            $parent.css({
                'position': 'relative',
                'z-index': '100'
            });

            // Сохраняем ширину родительского элемента
            var parentWidth = $parent.width();

            $select.select2({
                width: '100%',
                placeholder: "Поиск...",
                allowClear: true,
                dropdownParent: $parent,
                language: 'ru',
                minimumInputLength: 1,
                ajax: {
                    url: '/search-users',
                    dataType: 'json',
                    delay: 300,
                    data: function(params) {
                        return {
                            q: params.term || '',
                            status: status,
                            page: params.page || 1
                        };
                    },
                    processResults: function(data, params) {
                        params.page = params.page || 1;

                        return {
                            results: $.map(data, function(user) {
                                return {
                                    id: user.id,
                                    text: user.name,
                                    email: user.email
                                };
                            }),
                            pagination: {
                                more: false
                            }
                        };
                    },
                    cache: true
                },
                templateResult: formatUserResult,
                templateSelection: formatUserSelection
            }).on('select2:open', function() {
                // Устанавливаем корректную ширину выпадающего списка
                setTimeout(function() {
                    $('.select2-container--open .select2-dropdown').css({
                        'width': parentWidth + 'px',
                        'min-width': parentWidth + 'px',
                        'max-width': parentWidth + 'px'
                    });
                    
                    // Автоматически ставим фокус на поле поиска при открытии Select2
                    var searchField = $('.select2-container--open .select2-search__field');
                    if (searchField.length) {
                        searchField.focus();
                    }
                }, 0);
            });

            // Применение классов при изменении значения
            $select.on('change', function() {
                if ($(this).val()) {
                    $(this).addClass('filter-active');
                } else {
                    $(this).removeClass('filter-active');
                }
                if (typeof updateFilterCounters === 'function') {
                    updateFilterCounters();
                }
            });

            // Инициализация - если значение уже выбрано
            if ($select.val()) {
                $select.addClass('filter-active');
            }
        });
    }

    // Форматирование результата поиска пользователя
    function formatUserResult(user) {
        if (!user.id) return user.text;
        var $result = $(
            '<div class="select2-user-result">' +
            '<div class="select2-user-name">' + user.text + '</div>' +
            (user.email ? '<div class="select2-user-email">' + user.email + '</div>' : '') +
            '</div>'
        );
        return $result;
    }

    // Форматирование выбранного пользователя
    function formatUserSelection(user) {
        return user.text;
    }
</script>

<!-- Добавляем скрипт для функции confirmDeleteDeal -->
<script>
    // Глобальная функция для подтверждения удаления сделки
    window.confirmDeleteDeal = function(dealId) {
        if (confirm('ВНИМАНИЕ! Вы собираетесь удалить сделку. Это действие нельзя отменить.\n\nСвязи с брифами и другими элементами будут сохранены.\n\nВы уверены, что хотите удалить эту сделку?')) {
            console.log('Отправка запроса на удаление сделки #' + dealId);
            
            // Создаем форму для отправки запроса методом DELETE
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = `/deal/${dealId}/delete`;
            if (form && form.style) {
                form.style.display = 'none';
            }
            
            // Добавляем CSRF токен
            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            const csrfInput = document.createElement('input');
            csrfInput.type = 'hidden';
            csrfInput.name = '_token';
            csrfInput.value = csrfToken;
            form.appendChild(csrfInput);
            
            // Добавляем метод DELETE
            const methodInput = document.createElement('input');
            methodInput.type = 'hidden';
            methodInput.name = '_method';
            methodInput.value = 'DELETE';
            form.appendChild(methodInput);
            
            // Добавляем форму в документ и отправляем
            document.body.appendChild(form);
            form.submit();
            
            // Показываем индикатор загрузки
            const loadingScreen = document.createElement('div');
            loadingScreen.style.position = 'fixed';
            loadingScreen.style.top = '0';
            loadingScreen.style.left = '0';
            loadingScreen.style.width = '100%';
            loadingScreen.style.height = '100%';
            loadingScreen.style.backgroundColor = 'rgba(255, 255, 255, 0.8)';
            loadingScreen.style.display = 'flex';
            loadingScreen.style.justifyContent = 'center';
            loadingScreen.style.alignItems = 'center';
            loadingScreen.style.zIndex = '9999';
            loadingScreen.innerHTML = '<div>Удаление сделки...</div>';
            document.body.appendChild(loadingScreen);
        }
    };
</script>

<div class="deal" id="deal">
    <div class="deal__body">
        <div class="deal__cardinator__lists">
            @if ($viewType === 'table')
                <div class="table-container">
                    <table id="dealTable" class="deal-table display">
                        <thead>
                            <tr>
                       
                                <th>Номер сделки</th>
                                <th>Клиент</th>
                                <th>Номер клиента</th>
                                <th>Координатор</th>
                                <th>Сумма сделки</th>
                                <th>Статус</th>
                                <th>Партнер</th>
                                <th>Средняя оценка</th>
                                <th>Действия</th>
                            </tr>
                        </thead>
                        <tbody class="flex_table__format_table">
                            @foreach ($deals as $dealItem)
                                <tr>
                                    <td class="deal-name">{{ $dealItem->project_number ?? 'Не указан' }}</td>
    
                                    <td class="deal-client">
                                        @if ($dealItem->user_id)
                                            <a href="{{ route('profile.view', $dealItem->user_id) }}">
                                                {{ $dealItem->client_name ?? 'Не указан' }}
                                            </a>
                                        @else
                                            {{ $dealItem->client_name ?? 'Не указан' }}
                                        @endif
                                    </td>
                                    
                                    <td class="deal-phone">
                                        <a href="tel:{{ $dealItem->client_phone }}">
                                            {{ $dealItem->client_phone }}
                                        </a>
                                    </td>

                                    <td class="deal-coordinator">
                                        @if ($dealItem->coordinator_id)
                                            <a href="{{ route('profile.view', $dealItem->coordinator_id) }}">
                                                {{ \App\Models\User::find($dealItem->coordinator_id)->name ?? 'Не указан' }}
                                            </a>
                                        @else
                                            Не указан
                                        @endif
                                    </td>
                                    
                                    <td class="deal-sum">
                                        {{ number_format($dealItem->total_sum, 0, '.', ' ') ?? 'Отсутствует' }} ₽</td>
                                    <td
                                        class="deal-status status-{{ strtolower(str_replace(' ', '-', $dealItem->status)) }}">
                                        {{ $dealItem->status }}</td>
                                    <td class="deal-partner">
                                        @if ($dealItem->office_partner_id)
                                            <a href="{{ route('profile.view', $dealItem->office_partner_id) }}">
                                                {{ \App\Models\User::find($dealItem->office_partner_id)->name ?? 'Не указан' }}
                                            </a>
                                        @else
                                            Не указан
                                        @endif
                                    </td>
                                    <td class="deal-rating">
                                        @if ($dealItem->status === 'Проект завершен')
                                            <div class="rating-block">
                                                @if ($dealItem->client_average_rating)
                                                    <div class="deal-rating-stars client-rating"
                                                        title="Оценка от клиента: {{ $dealItem->client_average_rating }} ({{ $dealItem->client_ratings_count }} оценок)">
                                                        <i class="fas fa-user-tie"></i>
                                                        @for ($i = 1; $i <= 5; $i++)
                                                            @if ($i <= floor($dealItem->client_average_rating))
                                                                <i class="fas fa-star"></i>
                                                            @elseif($i - 0.5 <= $dealItem->client_average_rating)
                                                                <i class="fas fa-star-half-alt"></i>
                                                            @else
                                                                <i class="far fa-star"></i>
                                                            @endif
                                                        @endfor
                                                        <span
                                                            class="rating-value">{{ $dealItem->client_average_rating }}</span>
                                                    </div>
                                                @endif

                                                @if ($dealItem->average_rating && !$dealItem->client_average_rating)
                                                    <div class="deal-rating-stars overall-rating"
                                                        title="Общая средняя оценка: {{ $dealItem->average_rating }} ({{ $dealItem->ratings_count }} оценок)">
                                                        <i class="fas fa-users"></i>
                                                        @for ($i = 1; $i <= 5; $i++)
                                                            @if ($i <= floor($dealItem->average_rating))
                                                                <i class="fas fa-star"></i>
                                                            @elseif($i - 0.5 <= $dealItem->average_rating)
                                                                <i class="fas fa-star-half-alt"></i>
                                                            @else
                                                                <i class="far fa-star"></i>
                                                            @endif
                                                        @endfor
                                                        <span
                                                            class="rating-value">{{ $dealItem->average_rating }}</span>
                                                    </div>
                                                @endif

                                                @if (!$dealItem->average_rating && !$dealItem->client_average_rating)
                                                    <span class="no-rating">Нет оценок</span>
                                                @endif
                                            </div>
                                        @else
                                            <span class="no-rating">—</span>
                                        @endif
                                    </td>
                                    <td class="link__deistv">
                                        @if ($dealItem->registration_token)
                                            <a href="{{ $dealItem->registration_token ? route('register_by_deal', ['token' => $dealItem->registration_token]) : '' }}"
                                                onclick="event.preventDefault(); copyRegistrationLink(this.href)"
                                                title="Скопировать регистрационную ссылку">
                                                <img src="/storage/icon/link.svg" alt="Регистрационная ссылка">
                                            </a>
                                        @else
                                            <a href="#" title="Регистрационная ссылка отсутствует">
                                                <img src="/storage/icon/link.svg" alt="Регистрационная ссылка">
                                            </a>
                                        @endif

                                        <a href="{{ $dealItem->link ? url($dealItem->link) : '#' }}" title="Бриф">
                                            <img src="/storage/icon/brif.svg" alt="Бриф">
                                        </a>

                                        @if (in_array(Auth::user()->status, ['coordinator', 'admin']))
                                            <a href="{{ route('deal.change_logs.deal', ['deal' => $dealItem->id]) }}"
                                                title="Логи сделки">
                                                <img src="/storage/icon/log.svg" alt="Логи">
                                            </a>
                                        @endif

                                        @if (in_array(Auth::user()->status, ['coordinator', 'admin', 'partner']))
                                            <button type="button" class="edit-deal-btn"
                                                data-id="{{ $dealItem->id }}" title="Редактировать сделку">
                                                <img src="/storage/icon/add.svg" alt="Редактировать">
                                            </button>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <!-- Блочный вид -->
                <div class="faq__body__deal" id="all-deals-container">
                    <h4 class="flex">Все сделки</h4>
                    @if ($deals->isEmpty())
                        <div class="faq_block__deal faq_block-blur brifs__button__create-faq_block__deal"
                            onclick="window.location.href='{{ route('deals.create') }}'">

                            @if (Auth::check() &&
                                    (Auth::user()->status == 'architect' ||
                                        Auth::user()->status == 'designer' ||
                                        Auth::user()->status == 'visualizer'))
                                <h3
                                    style="    text-align: center;width: 100%;display: flex;align-items: center;justify-content: center;align-content: center;">
                                    Тут будут отображаться ваши сделки </br> к которым вы относитесь!</h3>
                            @elseif (Auth::check() && in_array(Auth::user()->status, ['coordinator', 'admin', 'partner']))
                                <button>
                                    <img src="/storage/icon/add.svg" alt="Создать сделку">
                                </button>
                            @endif
                        </div>
                    @else
                       <div class="faq_block__deal faq_block-blur brifs__button__create-faq_block__deal"
                            onclick="window.location.href='{{ route('deals.create') }}'">
    @if (Auth::check() &&
                                    (Auth::user()->status == 'architect' ||
                                        Auth::user()->status == 'designer' ||
                                        Auth::user()->status == 'visualizer'))
                                <h3
                                    style="    text-align: center;width: 100%;display: flex;align-items: center;justify-content: center;align-content: center;">
                                    Тут будут отображаться ваши сделки </br> к которым вы относитесь!</h3>
                            @elseif (Auth::check() && in_array(Auth::user()->status, ['coordinator', 'admin', 'partner']))
                                <button>
                                    <img src="/storage/icon/add.svg" alt="Создать сделку">
                                </button>
                            @endif
                        </div>
                        @foreach ($deals as $dealItem)
                            <div class="faq_block__deal" data-id="{{ $dealItem->id }}"
                                data-status="{{ $dealItem->status }}">
                                <div class="faq_item__deal">
                                    <div class="faq_question__deal flex between">
                                        <div class="faq_question__deal__info">

                                            <div class="deal__avatar deal__avatar__cardinator">
                                                <img src="{{ $dealItem->avatar_path ? asset('storage/' . $dealItem->avatar_path) : asset('storage/icon/deal_default.jpg') }}" 
                                                     alt="Avatar" title="{{ $dealItem->avatar_path ? 'Логотип сделки' : 'Дефолтный логотип' }}">
                                            </div>
                                            
                                            <div class="deal__cardinator__info">
                                                <div class="ctatus__deal___info">
                                                    <div class="div__status_info">{{ $dealItem->status }}</div>
                                                </div>
                                                <h4>{{ $dealItem->project_number  ?? 'Не указан'}}</h4>
                                                
                                                <p>Клиент:
                                                   
                                                        {{ $dealItem->client_name ?? 'Не указан' }}
                                                  
                                                </p>
                                               
                                                <p>Телефон:
                                                    <a href="tel:{{ $dealItem->client_phone }}">
                                                        {{ $dealItem->client_phone }}
                                                    </a>
                                                </p>
                                                <p>Координатор:
                                                    @if ($dealItem->coordinator_id)
                                                        <a href="{{ route('profile.view', $dealItem->coordinator_id) }}">
                                                            {{ \App\Models\User::find($dealItem->coordinator_id)->name ?? 'Не указан' }}
                                                        </a>
                                                    @else
                                                        Не указан
                                                    @endif
                                                </p>
                                                <p>Партнер:
                                                    @if ($dealItem->office_partner_id)
                                                        <a
                                                            href="{{ route('profile.view', $dealItem->office_partner_id) }}">
                                                            {{ \App\Models\User::find($dealItem->office_partner_id)->name ?? 'Не указан' }}
                                                        </a>
                                                    @else
                                                        Не указан
                                                    @endif
                                                </p>
                                                <!-- Добавляем отображение средней оценки -->
                                                @if ($dealItem->status === 'Проект завершен')
                                                    <div class="deal-rating-block">
                                                        @if ($dealItem->client_average_rating)
                                                            <p>Оценка клиента:
                                                                <span class="deal-rating-stars client-rating"
                                                                    title="Оценка от клиента ({{ $dealItem->client_ratings_count }} оценок)">
                                                                    <i class="fas fa-user-tie"></i>
                                                                    @for ($i = 1; $i <= 5; $i++)
                                                                        @if ($i <= floor($dealItem->client_average_rating))
                                                                            <i class="fas fa-star"></i>
                                                                        @elseif($i - 0.5 <= $dealItem->client_average_rating)
                                                                            <i class="fas fa-star-half-alt"></i>
                                                                        @else
                                                                            <i class="far fa-star"></i>
                                                                        @endif
                                                                    @endfor
                                                                    <span
                                                                        class="rating-value">{{ $dealItem->client_average_rating }}</span>
                                                                </span>
                                                            </p>
                                                        @endif

                                                        @if ($dealItem->average_rating && !$dealItem->client_average_rating)
                                                            <p>Общая оценка:
                                                                <span class="deal-rating-stars overall-rating"
                                                                    title="Общая средняя оценка ({{ $dealItem->ratings_count }} оценок)">
                                                                    <i class="fas fa-users"></i>
                                                                    @for ($i = 1; $i <= 5; $i++)
                                                                        @if ($i <= floor($dealItem->average_rating))
                                                                            <i class="fas fa-star"></i>
                                                                        @elseif($i - 0.5 <= $dealItem->average_rating)
                                                                            <i class="fas fa-star-half-alt"></i>
                                                                        @else
                                                                            <i class="far fa-star"></i>
                                                                        @endif
                                                                    @endfor
                                                                    <span
                                                                        class="rating-value">{{ $dealItem->average_rating }}</span>
                                                                </span>
                                                            </p>
                                                        @endif

                                                        @if (!$dealItem->average_rating && !$dealItem->client_average_rating)
                                                            <p>Нет оценок</p>
                                                        @endif
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                        <ul>
                                            <li>
                                                @php
                                                    // Убираем переменную $groupChat
                                                @endphp

                                            </li>
                                            <li>
                                                @if (in_array(Auth::user()->status, ['coordinator', 'admin', 'partner']))
                                                    <button type="button" class="edit-deal-btn"
                                                        data-id="{{ $dealItem->id }}">
                                                        <img src="/storage/icon/create__blue.svg" alt="">
                                                        <span>Изменить</span>
                                                    </button>
                                                @endif

                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    @endif
                    <div class="pagination" id="all-deals-pagination"></div>
                </div>
            @endif
        </div>
    </div>
</div>
<div id="dealModalContainer"></div>

<!-- Добавляем fullscreen-loader для отображения загрузки файлов -->
<div id="fullscreen-loader" class="fullscreen-loader">
    <div class="loader-wrapper">
        <div class="loader-container">
            <div class="loader-animation">
                <div class="loader-circle"></div>
                <div class="loader-circle"></div>
                <div class="loader-circle"></div>
            </div>
            <div class="loader-text">
                <h4>Загрузка файлов</h4>
                <p>Пожалуйста, подождите. Ваши файлы загружаются на сервер.</p>
                <div class="loader-progress">
                    <div class="loader-progress-bar"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    $(function() {
        // Инициализация DataTable для табличного вида
        if ($('#dealTable').length) {
            $('#dealTable').DataTable({
                language: {
                    url: 'https://cdn.datatables.net/plug-ins/1.13.4/i18n/ru.json'
                },
                paging: true,
                ordering: true,
                info: true,
                autoWidth: false,
                responsive: true,
                dom: '<"table-header"<"table-title"l><"table-search"f>><"table-content"rt><"table-footer"<"table-info"i><"table-pagination"p>>',
                lengthMenu: [
                    [10, 25, 50, -1],
                    [10, 25, 50, "Все"]
                ]
            });

            // Добавляем обработчик для окрашивания ячеек статуса
            $('#dealTable tbody tr').each(function() {
                var statusCell = $(this).find('td:nth-child(4)');
                var status = statusCell.text().trim();

                // Добавляем нужный класс в зависимости от статуса
                if (status === 'Новая заявка') {
                    statusCell.addClass('status-new');
                } else if (status === 'В процессе') {
                    statusCell.addClass('status-processing');
                } else if (status === 'Проект завершен') {
                    statusCell.addClass('status-completed');
                }
            });
        }

        // Пагинация для блочного вида
        function paginateContainer(container, paginationContainer, perPage = 6) {
            var $container = $(container);
            var $blocks = $container.find('.faq_block__deal');
            var total = $blocks.length;

            if (total <= perPage) {
                $blocks.show();
                return;
            }

            $blocks.hide();
            $blocks.slice(0, perPage).show();

            $(paginationContainer).pagination({
                items: total,
                itemsOnPage: perPage,
                cssStyle: 'light-theme',
                prevText: 'Предыдущая',
                nextText: 'Следующая',
                onPageClick: function(pageNumber, event) {
                    var start = (pageNumber - 1) * perPage;
                    var end = start + perPage;
                    $blocks.hide().slice(start, end).show();
                }
            });
        }

        // Вызов функции пагинации для блочного представления
        paginateContainer('#all-deals-container', '#all-deals-pagination', 6);

        var $editModal = $('#editModal'),
            $editForm = $('#editForm');

        // Функция инициализации Select2, вызывается после загрузки модального окна
        function initSelect2() {
            $('.select2-field:not(.select2-hidden-accessible)').each(function() {
                // Находим родительский контейнер для каждого Select2
                var $parent = $(this).closest('.form-group-deal');
                if (!$parent.length) {
                    $parent = $(this).parent();
                }

                // Устанавливаем position: relative для правильного позиционирования
                $parent.css({
                    'position': 'relative',
                    'width': '100%',
                    'overflow': 'visible'
                });

                // Сохраняем ширину родителя для использования в dropdownCssClass
                var parentWidth = $parent.width();

                // Добавляем уникальный идентификатор для родительского контейнера
                var uniqueId = 'parent-' + Math.random().toString(36).substr(2, 9);
                $parent.attr('data-select2-id', uniqueId);

                // Инициализируем Select2 с указанием родителя для dropdown
                $(this).select2({
                    width: '100%',
                    placeholder: $(this).attr('placeholder') || "Выберите значение",
                    allowClear: true,
                    dropdownParent: $parent, // Важно: dropdownParent указывает на родителя
                    language: 'ru',
                    // Добавляем CSS класс для дальнейшей стилизации
                    dropdownCssClass: 'select2-dropdown-in-parent'
                });

                // Применяем фиксированную ширину к выпадающему списку после открытия
                $(this).on('select2:open', function() {
                    setTimeout(function() {
                        $('.select2-container--open .select2-dropdown').css({
                            'width': parentWidth + 'px',
                            'min-width': '100%',
                            'max-width': parentWidth + 'px'
                        });
                    }, 0);
                });
            });
        }

        var modalCache = {}; // Объект для кэширования модальных окон

        // Обработчик клика для открытия модального окна с данными сделки
        $('.edit-deal-btn').on('click', function() {
            var dealId = $(this).data('id');
            var $modalContainer = $("#dealModalContainer");

            // Проверяем, есть ли модальное окно в кэше
            if (modalCache[dealId]) {
                // Если есть, показываем его из кэша
                try {
                    $modalContainer.empty().html(modalCache[dealId]);
                    setTimeout(function() {
                        try {
                            initSelect2();
                        } catch (e) {
                            console.error('Ошибка инициализации Select2 из кэша:', e);
                        }
                    }, 300);
                    $("#editModal").show().addClass('show');
                    try {
                        initModalFunctions();
                    } catch (e) {
                        console.error('Ошибка инициализации модальных функций из кэша:', e);
                    }
                } catch (error) {
                    console.error('Ошибка загрузки из кэша:', error);
                    // Очищаем кэш и загружаем заново
                    delete modalCache[dealId];
                    $(this).trigger('click');
                    return;
                }
            } else {
                // Если нет, загружаем с сервера
                // Показываем индикатор загрузки
                $modalContainer.html('<div class="loading">Загрузка...</div>');

                $.ajax({
                    url: "/deal/" + dealId + "/modal",
                    type: "GET",
                    success: function(response) {
                        try {
                            // Сохраняем модальное окно в кэш
                            modalCache[dealId] = response.html;

                            // Безопасная вставка HTML модального окна
                            $modalContainer.empty().html(response.html);

                            // Устанавливаем задержку для корректной инициализации Select2
                            setTimeout(function() {
                                try {
                                    initSelect2();
                                } catch (e) {
                                    console.error('Ошибка инициализации Select2:', e);
                                }
                            }, 300);

                            // Показываем модальное окно
                            $("#editModal").show().addClass('show');

                            // Обработчики закрытия модального окна
                            $('#closeModalBtn').off('click').on('click', function() {
                                $("#editModal").removeClass('show').hide();
                            });

                            $("#editModal").off('click').on('click', function(e) {
                                if (e.target === this) $(this).removeClass('show').hide();
                            });

                            // Инициализация других JS-функций для модального окна
                            try {
                                initModalFunctions();
                            } catch (e) {
                                console.error('Ошибка инициализации модальных функций:', e);
                            }
                        } catch (error) {
                            console.error('Ошибка обработки ответа сервера:', error);
                            $modalContainer.html('<div class="alert alert-danger">Ошибка загрузки данных</div>');
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error("Ошибка загрузки данных сделки:", status, error);
                        alert(
                            "Ошибка загрузки данных сделки. Попробуйте обновить страницу."
                        );
                    },
                    complete: function() {
                        // Скрываем индикатор загрузки
                        $('.loading').remove();
                    }
                });
            }

            // Динамическое изменение URL
            history.pushState(null, null, "#editDealModal");
        });

        // Обработчик закрытия модального окна
        $('#dealModalContainer').on('click', '#closeModalBtn', function() {
            $("#editModal").removeClass('show').hide();
            history.pushState("", document.title, window.location.pathname + window.location.search);
        });

        $('#dealModalContainer').on('click', '#editModal', function(e) {
            if (e.target === this) {
                $(this).removeClass('show').hide();
                history.pushState("", document.title, window.location.pathname + window.location
                    .search);
            }
        });

    // Обработчик для админ-кнопки логов
    document.addEventListener('DOMContentLoaded', function() {
        const adminLogsBtn = document.querySelector('.admin-logs-btn');
        const adminDropdown = document.querySelector('.admin-logs-dropdown');
        
        if (adminLogsBtn && adminDropdown) {
            // Предотвращаем переход по основной ссылке при клике на стрелку
            adminLogsBtn.addEventListener('click', function(e) {
                const chevron = e.target.closest('.fa-chevron-down');
                if (chevron) {
                    e.preventDefault();
                    e.stopPropagation();
                    // Запускаем Bootstrap dropdown
                    const dropdown = new bootstrap.Dropdown(this);
                    dropdown.toggle();
                }
            });
            
            // Добавляем подсказки для элементов меню
            const dropdownItems = adminDropdown.querySelectorAll('.dropdown-item');
            dropdownItems.forEach(item => {
                const icon = item.querySelector('i');
                const text = item.textContent.trim();
                
                let tooltip = '';
                if (text.includes('Все логи')) {
                    tooltip = 'Просмотр всех действий со сделками';
                } else if (text.includes('Создание')) {
                    tooltip = 'Логи создания новых сделок';
                } else if (text.includes('Редактирование')) {
                    tooltip = 'Логи изменения существующих сделок';
                } else if (text.includes('Удаление')) {
                    tooltip = 'Логи удаленных сделок';
                } else if (text.includes('Смена статусов')) {
                    tooltip = 'Логи изменения статусов сделок';
                } else if (text.includes('сегодня')) {
                    tooltip = 'Только действия за сегодня';
                }
                
                if (tooltip) {
                    item.setAttribute('title', tooltip);
                }
            });
        }
        
        // Обновление счетчика логов каждые 30 секунд
        const logsCounter = document.querySelector('.logs-counter');
        if (logsCounter) {
            setInterval(function() {
                fetch('/api/deal-logs-count')
                    .then(response => response.json())
                    .then(data => {
                        if (data.count !== undefined) {
                            logsCounter.textContent = data.count;
                        }
                    })
                    .catch(error => {
                        console.log('Не удалось обновить счетчик логов:', error);
                    });
            }, 30000); // 30 секунд
        }
    });

    // Функция инициализации дополнительных JS-функций для модального окна
        function initModalFunctions() {
            console.log('initModalFunctions: делегирование к единой системе вкладок');
            
            // Используем единую систему вкладок
            if (typeof window.TabsSystem !== 'undefined') {
                window.TabsSystem.reinit();
            }

            // Обработчик отправки формы ленты
            $("#feed-form").on("submit", function(e) {
                e.preventDefault();
                var content = $("#feed-content").val().trim();
                if (!content) {
                    alert("Введите текст сообщения!");
                    return;
                }
                var dealId = $("#dealIdField").val();
                if (dealId) {
                    $.ajax({
                        url: "/deal/" + dealId + "/feed",
                        type: "POST",
                        data: {
                            _token: $('meta[name="csrf-token"]').attr('content'),
                            content: content
                        },
                        success: function(response) {
                            // ...existing code...
                        },
                        error: function(xhr) {
                            alert("Ошибка при добавлении записи: " + xhr.responseText);
                        }
                    });
                } else {
                    alert("Не удалось определить сделку. Пожалуйста, обновите страницу.");
                }
            });

            // Обработчик для файловых полей
            $('input[type="file"]').on('change', function() {
                var file = this.files[0];
                var fileName = file ? file.name : "";
                var fieldName = $(this).attr('id');
                var linkDiv = $('#' + fieldName + 'Link');

                if (fileName) {
                    linkDiv.html('<a href="' + URL.createObjectURL(file) + '" target="_blank">' +
                        fileName + '</a>');
                }
            });
        }

        $('#closeModalBtn').on('click', function() {
            $("#editModal").removeClass('show').hide();
        });
        $("#editModal").on('click', function(e) {
            if (e.target === this) $(this).removeClass('show').hide();
        });

        $.getJSON('/cities.json', function(data) {
            var grouped = {};
            $.each(data, function(i, item) {
                grouped[item.region] = grouped[item.region] || [];
                grouped[item.region].push({
                    id: item.city,
                    text: item.city
                });
            });
            var selectData = $.map(grouped, function(cities, region) {
                return {
                    text: region,
                    children: cities
                };
            });
            $('#client_timezone, #cityField').select2({
                data: selectData,
                placeholder: "-- Выберите город/часовой пояс --", // Изменён placeholder
                allowClear: true,
                minimumInputLength: 1, // Включён поиск по городам
                dropdownParent: $('#editModal').find(
                    '.modal-content') // Используем более точный селектор
            });
            
            // Устанавливаем текущее значение для поля client_timezone
            var currentTimezone = $('#client_timezone').val() || $('#client_timezone').data('current-value') || $('#client_timezone').attr('data-current-value');
            if (currentTimezone && currentTimezone.trim() !== '') {
                setTimeout(function() {
                    var existingOption = $('#client_timezone option[value="' + currentTimezone + '"]');
                    if (existingOption.length === 0) {
                        var newOption = new Option(currentTimezone, currentTimezone, true, true);
                        $('#client_timezone').append(newOption);
                    } else {
                        $('#client_timezone').val(currentTimezone);
                    }
                    $('#client_timezone').trigger('change');
                    console.log('Город установлен в cardinators:', currentTimezone);
                }, 150);
            }
        }).fail(function(err) {
            console.error("Ошибка загрузки городов", err);
        });

        $('#responsiblesField').select2({
            placeholder: "Выберите ответственных",
            allowClear: true,
            dropdownParent: $('#editModal').find('.modal-content')
        });
        
        $('.select2-field').select2({
            width: '100%',
            placeholder: "Выберите значение",
            allowClear: true,
            dropdownParent: $('#editModal').find('.modal-content') // Используем более точный селектор
        });

        $("#feed-form").on("submit", function(e) {
            e.preventDefault();
            var content = $("#feed-content").val().trim();
            if (!content) {
                alert("Введите текст сообщения!");
                return;
            }
            var dealId = $("#dealIdField").val();
            if (dealId) {
                $.ajax({
                    url: "{{ url('/deal') }}/" + dealId + "/feed",
                    type: "POST",
                    data: {
                        _token: "{{ csrf_token() }}",
                        content: content
                    },
                    success: function(response) {
                        $("#feed-content").val("");
                        var avatarUrl = response.avatar_url ? response.avatar_url :
                            "/storage/icon/profile.svg";
                        $("#feed-posts-container").prepend(`
                        <div class="feed-post">
                            <div class="feed-post-avatar">
                                <img src="${avatarUrl}" alt="${response.user_name}">
                            </div>
                            <div class="feed-post-text">
                                <div class="feed-author">${response.user_name}</div>
                                <div class="feed-content">${response.content}</div>
                                <div class="feed-date">${response.date}</div>
                            </div>
                        </div>
                    `);
                    },
                    error: function(xhr) {
                        alert("Ошибка при добавлении записи: " + xhr.responseText);
                    }
                });
            } else {
                alert("Не удалось определить сделку. Пожалуйста, обновите страницу.");
            }
        });

        // Обработчик отправки формы редактирования сделки с поддержкой AJAX
        $('#dealModalContainer').on('submit', '#editForm', function(e) {
            e.preventDefault();
            var form = $(this);
            var url = form.attr('action');
            var formData = new FormData(this);

            // Проверяем, есть ли файлы для загрузки
            var hasFiles = false;
            var fileInputs = form.find('input[type="file"]');

            fileInputs.each(function() {
                if (this.files && this.files.length > 0) {
                    hasFiles = true;
                    return false; // прерываем цикл, если нашли хотя бы один файл
                }
            });

            // Если есть файлы для загрузки, показываем анимацию загрузки
            if (hasFiles) {
                const loader = document.getElementById('fullscreen-loader');
                loader.classList.add('show');

                // Анимация прогресс-бара
                let width = 0;
                const progressBar = document.querySelector('.loader-progress-bar');
                const progressInterval = setInterval(function() {
                    if (width >= 90) {
                        clearInterval(progressInterval);
                    } else {
                        width += Math.random() * 3;
                        if (progressBar && progressBar.style) {
                            progressBar.style.width = width + '%';
                        }
                    }
                }, 300);
            }

            $.ajax({
                url: url,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    // Скрываем анимацию загрузки
                    if (hasFiles) {
                        const loader = document.getElementById('fullscreen-loader');
                        // Быстро заполняем прогресс-бар до 100%
                        const progressBar = document.querySelector('.loader-progress-bar');
                        if (progressBar && progressBar.style) {
                            progressBar.style.width = '100%';
                        }

                        // Задержка перед скрытием для плавности
                        setTimeout(function() {
                            loader.classList.remove('show');
                        }, 500);
                    }

                    $("#editModal").removeClass('show').hide();

                    if (response.success) {
                        // Показываем сообщение об успехе
                        $('<div class="success-message">Сделка успешно обновлена</div>')
                            .appendTo('body')
                            .fadeIn('fast')
                            .delay(3000)
                            .fadeOut('slow', function() {
                                $(this).remove();
                            });

                        // Если статус изменен на "Проект завершен", проверяем необходимость оценок
                        if (response.status_changed_to_completed ||
                            (response.deal && response.deal.status === 'Проект завершен')) {
                            
                            console.log('[Сделка] Статус изменен на "Проект завершен", сохраняем ID сделки:', response.deal.id);
                            
                            // Сохраняем ID завершенной сделки в localStorage для проверки рейтингов
                            localStorage.setItem('completed_deal_id', response.deal.id);
                            
                            // Вызываем событие обновления сделки
                            window.dispatchEvent(new CustomEvent('dealUpdated', {
                                detail: {
                                    dealId: response.deal.id,
                                    statusChanged: true
                                }
                            }));

                            // Непосредственно вызываем функцию проверки оценок
                            if (typeof window.runRatingCheck === 'function') {
                                console.log('[Сделка] Вызов runRatingCheck для сделки:', response.deal.id);
                                window.runRatingCheck(response.deal.id);
                            } else if (typeof window.checkPendingRatings === 'function') {
                                setTimeout(() => {
                                    console.log('[Сделка] Проверка необходимости оценок для сделки:', response.deal.id);
                                    window.checkPendingRatings(response.deal.id);
                                }, 500);
                            } else {
                                console.warn('[Сделка] Функции рейтингов не найдены, перезагрузка страницы');
                                // Если функции рейтингов не найдены, перезагружаем страницу через 2 секунды
                                setTimeout(function() {
                                    location.reload();
                                }, 2000);
                            }
                        } else {
                            // Обновляем страницу только если статус НЕ изменился на "Проект завершен"
                            setTimeout(function() {
                                location.reload();
                            }, 1000);
                        }
                    }
                },
                error: function(xhr) {
                    // Скрываем анимацию загрузки в случае ошибки
                    if (hasFiles) {
                        const loader = document.getElementById('fullscreen-loader');
                        loader.classList.remove('show');
                    }
                    alert('Произошла ошибка при обновлении сделки.');
                    console.error(xhr.responseText);
                }
            });
        });

        // Добавляем код для проверки завершенных сделок при загрузке страницы
        document.addEventListener('DOMContentLoaded', function() {
            // Небольшая задержка для уверенности, что ratings.js загружен
            setTimeout(function() {
                if (typeof window.checkPendingRatings !== 'function') {
                    console.error('[Сделки] Функция checkPendingRatings не определена!');
                    return;
                }

                console.log('[Сделки] Поиск завершенных сделок для проверки оценок...');

                // Собираем ID завершенных сделок
                const completedDealIds = [];

                // Проверяем блочное представление
                document.querySelectorAll('.faq_block__deal[data-status="Проект завершен"]').forEach(
                    block => {
                        const dealId = block.dataset.id;
                        if (dealId) completedDealIds.push(dealId);
                    });

                // Проверяем табличное представление
                document.querySelectorAll('#dealTable td').forEach(cell => {
                    if (cell.textContent.trim() === 'Проект завершен') {
                        const row = cell.closest('tr');
                        const editBtn = row.querySelector('.edit-deal-btn');
                        if (editBtn && editBtn.dataset.id) {
                            completedDealIds.push(editBtn.dataset.id);
                        }
                    }
                });

                console.log('[Сделки] Найдено завершенных сделок:', completedDealIds.length);

                // Проверяем localStorage
                const completedDealId = localStorage.getItem('completed_deal_id');
                if (completedDealId) {
                    console.log('[Сделки] Найден ID завершенной сделки в localStorage:', completedDealId);
                    window.checkPendingRatings(completedDealId);
                    localStorage.removeItem('completed_deal_id');
                }
                // Если есть завершенные сделки на странице, проверяем первую из них
                else if (completedDealIds.length > 0) {
                    console.log('[Сделки] Проверка оценок для первой найденной сделки:', completedDealIds[
                        0]);
                    window.checkPendingRatings(completedDealIds[0]);
                }
            }, 800);
        });
    });

    // ...existing code...
</script>

<!-- CSS стили для fullscreen-loader -->
<style>
    input,
    textarea,
    select,
    .multiselect-selected {
        min-height: 38px !important;
        height: 38px;
    }
</style>

<!-- Заменяем скрипт для автоматического расчета даты завершения проекта -->
<script>
    // Функция для расчета даты завершения проекта
    function initProjectDateCalculator() {

        // Находим поля разными способами для повышения надежности
        const startDateField = document.getElementById('start_date') ||
            document.querySelector('input[name="start_date"]') ||
            document.querySelector('input[id*="start_date"]');

        const durationField = document.getElementById('project_duration') ||
            document.querySelector('input[name="project_duration"]') ||
            document.querySelector('input[id*="duration"]');

        const endDateField = document.getElementById('project_end_date') ||
            document.querySelector('input[name="project_end_date"]') ||
            document.querySelector('input[id*="end_date"]');



        // Проверяем, найдены ли все необходимые поля
        if (startDateField && durationField && endDateField) {


            // Функция для расчета даты завершения с учетом только рабочих дней
            function calculateEndDate() {
                console.log('[DateCalculator] Запуск расчета даты завершения:', {
                    'Начальная дата': startDateField.value,
                    'Срок проекта (дней)': durationField.value
                });

                // Проверяем, есть ли значения в обоих полях
                if (!startDateField.value || !durationField.value) {

                    return;
                }

                const workDays = parseInt(durationField.value);
                // Если введено некорректное значение, очищаем поле даты завершения
                if (isNaN(workDays) || workDays <= 0) {

                    endDateField.value = '';
                    return;
                }

                // Преобразуем дату начала в объект Date
                let startDate;

                // Поддержка разных форматов даты
                if (startDateField.value.includes('-')) { // формат YYYY-MM-DD
                    const [year, month, day] = startDateField.value.split('-');
                    startDate = new Date(year, month - 1, day);
                } else if (startDateField.value.includes('.')) { // формат DD.MM.YYYY
                    const [day, month, year] = startDateField.value.split('.');
                    startDate = new Date(year, month - 1, day);
                } else {
                    startDate = new Date(startDateField.value);
                }

                // Если дата некорректная, выходим
                if (isNaN(startDate.getTime())) {

                    endDateField.value = '';
                    return;
                }

                // Отладочная информация о начальной дате


                let remainingWorkDays = workDays;
                let currentDate = new Date(startDate);

                // Цикл для добавления рабочих дней
                while (remainingWorkDays > 0) {
                    // Добавляем 1 день к текущей дате
                    currentDate.setDate(currentDate.getDate() + 1);

                    // Проверяем, является ли день рабочим (не суббота и не воскресенье)
                    const dayOfWeek = currentDate.getDay(); // 0 - воскресенье, 6 - суббота
                    if (dayOfWeek !== 0 && dayOfWeek !== 6) {
                        remainingWorkDays--; // Уменьшаем счетчик рабочих дней
                    }
                }

                // Определяем формат выходной даты на основе формата входной
                let formattedDate;

                if (endDateField.type === 'date' || startDateField.value.includes('-')) {
                    // Для полей типа date или если входной формат YYYY-MM-DD
                    formattedDate = currentDate.toISOString().split('T')[0]; // YYYY-MM-DD
                } else if (startDateField.value.includes('.')) {
                    // Если входной формат DD.MM.YYYY
                    const day = String(currentDate.getDate()).padStart(2, '0');
                    const month = String(currentDate.getMonth() + 1).padStart(2, '0');
                    const year = currentDate.getFullYear();
                    formattedDate = `${day}.${month}.${year}`;
                } else {
                    // Используем локальный формат даты как запасной вариант
                    formattedDate = currentDate.toLocaleDateString();
                }

                // Устанавливаем дату завершения проекта
                endDateField.value = formattedDate;

            }

            // Удаляем все существующие обработчики событий (для избежания дубликатов)
            startDateField.removeEventListener('change', calculateEndDate);
            startDateField.removeEventListener('input', calculateEndDate);
            durationField.removeEventListener('change', calculateEndDate);
            durationField.removeEventListener('input', calculateEndDate);
            durationField.removeEventListener('keyup', calculateEndDate);

            // Добавляем слушатели событий для полей ввода

            startDateField.addEventListener('change', calculateEndDate);
            startDateField.addEventListener('input', calculateEndDate);

            // Добавляем несколько типов событий для надежности
            durationField.addEventListener('change', calculateEndDate);
            durationField.addEventListener('input', calculateEndDate);
            durationField.addEventListener('keyup', calculateEndDate); // Дополнительный обработчик

            // Запускаем расчет при загрузке страницы, если поля уже заполнены

            calculateEndDate();

            // Устанавливаем прямое отслеживание изменений значения поля duration
            // для случаев, когда стандартные события могут не срабатывать
            const originalValue = durationField.value;
            setInterval(() => {
                if (durationField.value !== originalValue && durationField.value) {

                    calculateEndDate();
                }
            }, 500);
        } else {

        }
    }

    // Инициализация при загрузке страницы
    document.addEventListener('DOMContentLoaded', function() {

        initProjectDateCalculator();

        // Также инициализируем при появлении модального окна
        const observer = new MutationObserver(function(mutations) {
            mutations.forEach(function(mutation) {
                if (mutation.addedNodes.length) {
                    for (let node of mutation.addedNodes) {
                        if (node.id === 'editModal' || (node.querySelector && node
                                .querySelector('#editModal'))) {

                            setTimeout(initProjectDateCalculator, 300);
                        }
                    }
                }
            });
        });

        // Начинаем наблюдение за документом
        observer.observe(document.body, {
            childList: true,
            subtree: true
        });
    });

    // Повторная инициализация при появлении модального окна
    $(document).on('shown.bs.modal', '#editModal', function() {

        setTimeout(initProjectDateCalculator, 300);
    });

    // Добавляем глобальную функцию для ручной инициализации
    window.initProjectDateCalculator = initProjectDateCalculator;
</script>

<!-- Добавьте этот скрипт перед закрывающим тегом body -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Небольшая задержка для уверенности, что RatingSystem загружен
        setTimeout(function() {
            // Проверяем, есть ли ID завершенной сделки в localStorage
            const completedDealId = localStorage.getItem('completed_deal_id');
            if (completedDealId) {
                console.log('[Кардинатор] Найден ID завершенной сделки в localStorage:',
                    completedDealId);

                if (typeof window.RatingSystem !== 'undefined' && typeof window.RatingSystem
                    .checkPendingRatings === 'function') {
                    window.RatingSystem.checkPendingRatings(completedDealId);
                } else if (typeof window.checkPendingRatings === 'function') {
                    window.checkPendingRatings(completedDealId);
                } else {
                    console.error('[Кардинатор] Система рейтингов не инициализирована');
                }
            }
        }, 1000);

        // Обработчик события обновления сделки
        window.addEventListener('dealUpdated', function(event) {
            if (event.detail && event.detail.dealId) {
                console.log('[Кардинатор] Событие обновления сделки, проверка рейтингов:', event.detail
                    .dealId);
                if (typeof window.RatingSystem !== 'undefined' && typeof window.RatingSystem
                    .checkPendingRatings === 'function') {
                    window.RatingSystem.checkPendingRatings(event.detail.dealId);
                }
            }
        });
    });
</script>
<style>
    .faq_item__deal {
   
    min-height: 250px;

}div#all-deals-container .faq_block__deal.faq_block-blur.brifs__button__create-faq_block__deal {
    min-height: 250px;
}
</style><style>
/* Уникальные стили для модального окна поиска брифа, которые не будут конфликтовать с другими стилями */
.brief-search-modal {
    font-family: 'Roboto', sans-serif;
}

.brief-search-modal .brief-modal-dialog {
    max-width: 600px;
    margin: 1.75rem auto;
    border-radius: 12px;
}
.brief-search-modal .brief-current button {
    width: max-content;
    min-height: 50px;
}
.brief-search-modal .brief-modal-content {
    border: none;
    border-radius: 12px;
    box-shadow: 0 8px 30px rgba(0, 0, 0, 0.2);
    background: #fff;
    overflow: hidden;
}

.brief-search-modal .brief-modal-header {
    /* background: linear-gradient(135deg, #3498db, #2980b9); */
    color: rgb(0, 0, 0);
    display: flex;
    border: none;
    padding: 0;
    justify-content: space-between;
}

.brief-search-modal .brief-modal-title {
    font-weight: 600;
    font-size: 1.25rem;
    margin: 0;
    display: flex;
    align-items: center;
}

.brief-search-modal .brief-modal-title::before {
    content: "";
    display: inline-block;
    width: 24px;
    height: 24px;
    margin-right: 10px;
    background: url('/storage/icon/brif.svg') no-repeat center;
    background-size: contain;
}

.brief-search-modal .brief-close-button {
    background: none;
    border: 1px solid #000;
    color: #000;
    width: 50px;
    min-height: 50px;
    font-size: 1.5rem;
    line-height: 1;
    padding: 0;
    margin: 0;
    opacity: 0.7;
    cursor: pointer;
    transition: opacity 0.2s;
    font-weight: 300;
}

.brief-search-modal .brief-close-button:hover {
    opacity: 1;
}

.brief-search-modal .brief-modal-body {
    padding: 20px 0;
    min-height: 100px;
    max-height: 70vh;
    overflow-y: auto;
}

.brief-search-modal .brief-spinner-container {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 30px 0;
}

.brief-search-modal .brief-spinner {
    border: 3px solid #f3f3f3;
    border-top: 3px solid #3498db;
    border-radius: 50%;
    width: 40px;
    height: 40px;
    animation: brief-spin 1s linear infinite;
}

@keyframes brief-spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

.brief-search-modal .brief-spinner-text {
    margin-top: 15px;
    color: #555;
    font-size: 1rem;
}

.brief-search-modal .brief-results {
    margin-top: 10px;
}

.brief-search-modal .brief-section-title {
    color: #333;
    font-size: 1.1rem;
    font-weight: 600;
    margin-bottom: 10px;
    padding-bottom: 5px;
    border-bottom: 1px solid #eee;
}

.brief-search-modal .brief-list {
    list-style: none;
    padding: 0;
    margin: 0 0 20px 0;
}

.brief-search-modal .brief-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 12px 15px;
    background-color: #f9f9f9;
    border-radius: 8px;
    margin-bottom: 8px;
    transition: all 0.2s;
}

.brief-search-modal .brief-item:hover {
    background-color: #f0f0f0;
    transform: translateX(3px);
}

.brief-search-modal .brief-item-info {
    flex: 1;
}

.brief-search-modal .brief-item-id {
    font-weight: 600;
    color: #2c3e50;
}

.brief-search-modal .brief-item-date {
    font-size: 0.85rem;
    color: #7f8c8d;
    display: block;
    margin-top: 3px;
}

.brief-search-modal .brief-link-btn {
    background: linear-gradient(135deg, #3498db, #2980b9);
    border: none;
    color: white;
    padding: 8px 14px;
    border-radius: 6px;
    font-size: 0.875rem;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.25s;
    text-transform: uppercase;
}

.brief-search-modal .brief-link-btn:hover {
    background: linear-gradient(135deg, #2980b9, #2471a3);
    transform: translateY(-2px);
    box-shadow: 0 3px 10px rgba(0, 0, 0, 0.1);
}

.brief-search-modal .brief-link-btn:disabled {
    background: #bdc3c7;
    color: #7f8c8d;
    cursor: not-allowed;
    transform: none;
    box-shadow: none;
}

.brief-search-modal .brief-modal-footer {
    padding: 15px 24px;
    border-top: 1px solid #eee;
    display: flex;
    justify-content: flex-end;
}

.brief-search-modal .brief-close-modal-btn {
    background: #e0e0e0;
    color: #333;
    border: none;
    padding: 8px 16px;
    border-radius: 6px;
    cursor: pointer;
    transition: all 0.2s;
    font-weight: 500;
}

.brief-search-modal .brief-close-modal-btn:hover {
    background: #d0d0d0;
}

.brief-search-modal .brief-alert {
    padding: 15px;
    margin-bottom: 20px;
    border-radius: 8px;
    font-weight: 500;
}

.brief-search-modal .brief-alert-info {
    background-color: #e3f2fd;
    color: #0d47a1;
    border: 1px solid #bbdefb;
}

.brief-search-modal .brief-alert-danger {
    background-color: #ffebee;
    color: #b71c1c;
    border: 1px solid #ffcdd2;
}

.brief-search-modal .brief-commercial {
    border-left: 3px solid #2ecc71;
}

.brief-search-modal .brief-common {
    border-left: 3px solid #3498db;
}

.brief-search-modal .brief-type-badge {
    display: inline-block;
    font-size: 0.75rem;
    padding: 2px 6px;
    border-radius: 4px;
    margin-right: 5px;
    font-weight: 600;
    text-transform: uppercase;
}
.brief-search-modal .brief-common button { width: max-content;
    min-height: 50px;}
li.brief-item.brief-common.brief-linked button {
    width: max-content;
    min-height: 50px;
    display: flex;
    align-items: center;
    justify-content: center;
    align-content: center;
}

.brief-item-info {
    display: flex;
    flex-direction: column;
    align-content: flex-start;
    align-items: flex-start;
}
.brief-search-modal .brief-type-common {
    background-color: #e3f2fd;
    color: #0d47a1;
}

.brief-search-modal .brief-type-commercial {
    background-color: #e8f5e9;
    color: #1b5e20;
}

/* Анимация появления модального окна */
.brief-search-modal.show .brief-modal-dialog {
    animation: briefModalIn 0.3s ease-out;
}

@keyframes briefModalIn {
    0% { transform: scale(0.8); opacity: 0; }
    100% { transform: scale(1); opacity: 1; }
}

/* Стили для блока найденных пользователей */
.brief-search-modal .brief-users-found {
    margin-bottom: 20px;
}

.brief-search-modal .brief-type-user {
    background-color: #e8f4fd;
    color: #0366d6;
}

.brief-search-modal .brief-user {
    border-left: 3px solid #0366d6;
}

.brief-search-modal .brief-item-email {
    font-size: 0.85rem;
    color: #0366d6;
    display: block;
    margin-top: 3px;
}

.brief-search-modal .brief-item-owner {
    font-size: 0.85rem;
    color: #555;
    display: block;
    margin-top: 3px;
    font-style: italic;
}

.brief-search-modal .brief-linked {
    opacity: 0.7;
    background-color: #f0f0f0;
    border-left: 3px solid #9e9e9e;
}

.brief-search-modal .brief-item-linked {
    display: inline-block;
    margin-top: 5px;
    font-size: 0.85rem;
    background-color: #e0e0e0;
    color: #424242;
    padding: 2px 8px;
    border-radius: 4px;
    font-weight: 500;
}

.brief-search-modal .brief-link-btn:disabled {
    background: #bdc3c7;
    color: #7f8c8d;
    cursor: not-allowed;
    transform: none;
    box-shadow: none;
}

.brief-search-modal .brief-current-block {
    margin-bottom: 20px;
    padding-bottom: 15px;
}

.brief-search-modal .brief-current {
    border-left-width: 5px !important;
    background-color: #f8f9fa !important;
}

.brief-search-modal .brief-divider {
    border: 0;
    border-top: 1px dashed #ddd;
    margin: 20px 0;
}

.brief-search-modal .brief-unlink-btn {
    background: #dc3545;
    color: white;
    border: none;
    padding: 8px 14px;
       border-radius: 6px;
    font-size: 0.875rem;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.25s;
    text-transform: uppercase;
}

.brief-search-modal .brief-unlink-btn:hover {
    background: #c82333;
    transform: translateY(-2px);
    box-shadow: 0 3px 10px rgba(0, 0, 0, 0.1);
}

.brief-search-modal .brief-unlink-btn:disabled {
    background: #f8bac1;
    color: #7f8c8d;
    cursor: not-allowed;
    transform: none;
    box-shadow: none;
}.form-group-deal, .fieldset-content, .fieldset-body, .module__deal {
    overflow: visible !important;
    opacity: 1 !important;
}

/* Заголовок страницы с админ-панелью */
.page-header-with-admin {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1rem;
    flex-wrap: wrap;
    gap: 1rem;
}

.page-header-with-admin h1 {
    margin: 0;
    flex: 1;
}

/* Быстрый доступ к логам */
.admin-quick-access {
    display: flex;
    align-items: center;
}

.admin-logs-btn {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.75rem 1.5rem;
    background: linear-gradient(135deg, #dc3545, #c82333);
    color: white;
    text-decoration: none;
    border-radius: 8px;
    font-weight: 600;
    font-size: 0.9rem;
    transition: all 0.3s ease;
    box-shadow: 0 2px 4px rgba(220, 53, 69, 0.2);
    position: relative;
    border: 2px solid transparent;
}

.admin-logs-btn:hover {
    color: white;
    text-decoration: none;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(220, 53, 69, 0.3);
    border-color: rgba(255, 255, 255, 0.2);
}

.admin-logs-btn i {
    font-size: 1.1rem;
}

.logs-counter {
    background: rgba(255, 255, 255, 0.2);
    color: white;
    border-radius: 12px;
    padding: 0.2rem 0.6rem;
    font-size: 0.75rem;
    font-weight: bold;
    min-width: 1.5rem;
    text-align: center;
    backdrop-filter: blur(4px);
}

/* Выпадающее меню логов */
.admin-logs-dropdown {
    min-width: 220px;
    border: none;
    box-shadow: 0 8px 30px rgba(0, 0, 0, 0.15);
    border-radius: 8px;
    overflow: hidden;
}

.admin-logs-dropdown .dropdown-item {
    padding: 0.75rem 1rem;
    display: flex;
    align-items: center;
    gap: 0.75rem;
    font-size: 0.9rem;
    transition: all 0.2s ease;
    border: none;
}

.admin-logs-dropdown .dropdown-item:hover {
    background-color: #f8f9fa;
    transform: translateX(3px);
}

.admin-logs-dropdown .dropdown-item i {
    width: 16px;
    text-align: center;
}

.admin-logs-dropdown .dropdown-divider {
    margin: 0.25rem 0;
    border-color: #dee2e6;
}

/* Адаптивность */
@media (max-width: 768px) {
    .page-header-with-admin {
        flex-direction: column;
        align-items: flex-start;
    }
    
    .admin-logs-btn {
        font-size: 0.8rem;
        padding: 0.6rem 1.2rem;
    }
}

/* Расширенные стили для админ-панели мониторинга */
.admin-deals-monitoring-panel .card {
    border: 2px solid #dc3545;
    box-shadow: 0 4px 6px rgba(220, 53, 69, 0.1);
}

.admin-deals-monitoring-panel .card-header {
    border-bottom: 2px solid #c82333;
    background: linear-gradient(135deg, #dc3545, #c82333) !important;
}

.admin-deals-monitoring-panel .btn-outline-danger,
.admin-deals-monitoring-panel .btn-outline-warning,
.admin-deals-monitoring-panel .btn-outline-info,
.admin-deals-monitoring-panel .btn-outline-success {
    border-width: 2px;
    padding: 0.75rem 1.5rem;
    font-weight: 600;
    transition: all 0.3s ease;
    min-height: 60px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
}

.admin-deals-monitoring-panel .btn-outline-danger:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(220, 53, 69, 0.3);
}

.admin-deals-monitoring-panel .btn-outline-warning:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(255, 193, 7, 0.3);
}

.admin-deals-monitoring-panel .btn-outline-info:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(23, 162, 184, 0.3);
}

.admin-deals-monitoring-panel .btn-outline-success:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(40, 167, 69, 0.3);
}

.admin-stats {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1rem;
    height: 100%;
}

.admin-stats .stat-item {
    text-align: center;
    padding: 0.5rem;
    background: rgba(248, 249, 250, 0.8);
    border-radius: 8px;
    border: 1px solid #dee2e6;
}

.admin-stats .stat-value {
    display: block;
    font-size: 1.5rem;
    font-weight: bold;
    color: #dc3545;
    line-height: 1.2;
}

.admin-stats .stat-value.text-warning {
    color: #ffc107 !important;
}

.admin-stats .stat-value.text-info {
    color: #17a2b8 !important;
}

.admin-stats .stat-label {
    display: block;
    font-size: 0.75rem;
    color: #6c757d;
    margin-top: 0.25rem;
    font-weight: 500;
}

.admin-deals-monitoring-panel .btn-group-sm .btn {
    padding: 0.25rem 0.5rem;
    font-size: 0.75rem;
    border-radius: 4px;
    margin-right: 0.25rem;
}

@media (max-width: 768px) {
    .admin-stats {
        grid-template-columns: 1fr;
        gap: 0.5rem;
    }
    
    .admin-deals-monitoring-panel .row {
        flex-direction: column;
    }
    
    .admin-deals-monitoring-panel .btn-lg {
        font-size: 0.9rem;
        padding: 0.5rem 1rem;
        min-height: 50px;
    }
}
</style>