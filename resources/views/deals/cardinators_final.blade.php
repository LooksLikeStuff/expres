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
    <h1 class="flex">Ваши сделки</h1>

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
                                        @if($dealItem->client_phone)
                                            <a href="tel:{{ $dealItem->client_phone }}">
                                                {{ $dealItem->formatted_client_phone ?? $dealItem->client_phone }}
                                            </a>
                                        @else
                                            Не указан
                                        @endif
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
                                            <a href="{{ route('deal.edit-page', $dealItem->id) }}" 
                                               class="edit-deal-btn" 
                                               title="Редактировать сделку">
                                                <img src="/storage/icon/add.svg" alt="Редактировать">
                                            </a>
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
                                                    <a href="{{ route('deal.edit-page', $dealItem->id) }}" 
                                                       class="edit-deal-btn">
                                                        <img src="/storage/icon/create__blue.svg" alt="">
                                                        <span>Изменить</span>
                                                    </a>
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
