<!-- Компонент фильтров и поиска для cardinators.blade.php -->

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
