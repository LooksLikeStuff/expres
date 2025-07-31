<!-- Подключение CSS DataTables -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap4.min.css">

<div class="container-fluid py-4">    <!-- Заголовок страницы и статистика -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">Управление пользователями</h1>
        <ol class="breadcrumb bg-transparent mb-0 pb-0">
            <li class="breadcrumb-item"><a href="{{ route('admin') }}">Панель управления</a></li>
            <li class="breadcrumb-item active">Пользователи</li>
        </ol>
    </div>    <!-- Отладочная информация (временная) -->


    <!-- Краткая статистика -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="card border-left-primary  h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Всего пользователей</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $usersCount ?? 0 }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-users fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card border-left-success  h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Активных клиентов</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $users->where('status', 'client')->count() }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-user-check fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card border-left-info  h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Новых за 30 дней</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $users->where('created_at', '>=', now()->subDays(30))->count() }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-user-plus fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card border-left-warning  h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Брифов от пользователей</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $commonsCount + $commercialsCount ?? 0 }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-clipboard-list fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Улучшенная секция фильтров -->
    <div class="card mb-4 filter-card">
        <div class="card-header py-3 d-flex justify-content-between align-items-center" id="filter-header">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="fas fa-filter mr-2"></i> Фильтры
                <span id="filter-counter" class="badge badge-light ml-2">0</span>
            </h6>
            <a data-toggle="collapse" href="#filterCollapse" role="button" aria-expanded="true" aria-controls="filterCollapse">
                <i class="fas fa-chevron-down"></i>
            </a>
        </div>
        <div class="collapse show" id="filterCollapse">
            <div class="card-body pb-0">
                <form id="users-filter-form" method="GET" action="{{ route('admin.users') }}">
                    <div class="row">
                        <!-- Фильтр по статусу пользователя -->
                        <div class="col-md-3 col-sm-6 mb-3">
                            <div class="filter-group">
                                <label for="status-filter" class="small font-weight-bold text-gray-700">
                                    <i class="fas fa-user-tag mr-1"></i> Статус
                                </label>
                                <select id="status-filter" name="status[]" class="form-control form-control-sm selectpicker" data-style="btn-white" multiple data-selected-text-format="count > 2" title="Все статусы">
                                    <option value="admin" {{ in_array('admin', (array)request('status')) ? 'selected' : '' }}>Администратор</option>
                                    <option value="user" {{ in_array('user', (array)request('status')) ? 'selected' : '' }}>Пользователь</option>
                                    <option value="architect" {{ in_array('architect', (array)request('status')) ? 'selected' : '' }}>Архитектор</option>
                                    <option value="designer" {{ in_array('designer', (array)request('status')) ? 'selected' : '' }}>Дизайнер</option>
                                    <option value="visualizer" {{ in_array('visualizer', (array)request('status')) ? 'selected' : '' }}>Визуализатор</option>
                                    <option value="coordinator" {{ in_array('coordinator', (array)request('status')) ? 'selected' : '' }}>Координатор</option>
                                    <option value="partner" {{ in_array('partner', (array)request('status')) ? 'selected' : '' }}>Партнер</option>
                                </select>
                            </div>
                        </div>

                        <!-- Фильтр по дате регистрации -->
                        <div class="col-md-3 col-sm-6 mb-3">
                            <div class="filter-group">
                                <label for="date-filter" class="small font-weight-bold text-gray-700">
                                    <i class="fas fa-calendar-alt mr-1"></i> Дата регистрации
                                </label>
                                <select id="date-filter" name="date_range" class="form-control form-control-sm">
                                    <option value="">Все время</option>
                                    <option value="today" {{ request()->input('date_range') == 'today' ? 'selected' : '' }}>Сегодня</option>
                                    <option value="week" {{ request()->input('date_range') == 'week' ? 'selected' : '' }}>За неделю</option>
                                    <option value="month" {{ request()->input('date_range') == 'month' ? 'selected' : '' }}>За месяц</option>
                                    <option value="quarter" {{ request()->input('date_range') == 'quarter' ? 'selected' : '' }}>За 3 месяца</option>
                                    <option value="year" {{ request()->input('date_range') == 'year' ? 'selected' : '' }}>За год</option>
                                    <option value="custom" {{ request()->input('date_range') == 'custom' ? 'selected' : '' }}>Выбрать период</option>
                                </select>
                            </div>
                        </div>
                        
                        <!-- Фильтр по количеству брифов -->
                        <div class="col-md-3 col-sm-6 mb-3">
                            <div class="filter-group">
                                <label for="briefs-filter" class="small font-weight-bold text-gray-700">
                                    <i class="fas fa-file-alt mr-1"></i> Брифы
                                </label>
                                <select id="briefs-filter" name="briefs" class="form-control form-control-sm">
                                    <option value="">Любое количество</option>
                                    <option value="has" {{ request()->input('briefs') == 'has' ? 'selected' : '' }}>Есть брифы</option>
                                    <option value="no" {{ request()->input('briefs') == 'no' ? 'selected' : '' }}>Нет брифов</option>
                                    <option value="many" {{ request()->input('briefs') == 'many' ? 'selected' : '' }}>Более 5 брифов</option>
                                </select>
                            </div>
                        </div>
                        
                        <!-- Фильтр по активности -->
                        <div class="col-md-3 col-sm-6 mb-3">
                            <div class="filter-group">
                                <label for="activity-filter" class="small font-weight-bold text-gray-700">
                                    <i class="fas fa-clock mr-1"></i> Активность
                                </label>
                                <select id="activity-filter" name="activity" class="form-control form-control-sm">
                                    <option value="">Любая активность</option>
                                    <option value="active" {{ request()->input('activity') == 'active' ? 'selected' : '' }}>Активны сейчас</option>
                                    <option value="recent" {{ request()->input('activity') == 'recent' ? 'selected' : '' }}>За последний день</option>
                                    <option value="inactive" {{ request()->input('activity') == 'inactive' ? 'selected' : '' }}>Неактивны более 30 дней</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Дополнительные фильтры (скрытые по умолчанию) -->
                    <div class="collapse" id="advancedFilters">
                        <div class="row mt-2">
                            <!-- Фильтр по email-домену -->
                            <div class="col-md-3 col-sm-6 mb-3">
                                <div class="filter-group">
                                    <label for="email-domain-filter" class="small font-weight-bold text-gray-700">
                                        <i class="fas fa-at mr-1"></i> Email домен
                                    </label>
                                    <input type="text" id="email-domain-filter" name="email_domain" class="form-control form-control-sm" value="{{ request()->input('email_domain') }}" placeholder="например: gmail.com">
                                </div>
                            </div>
                            
                            <!-- Фильтр по наличию сделок -->
                            <div class="col-md-3 col-sm-6 mb-3">
                                <div class="filter-group">
                                    <label for="deals-filter" class="small font-weight-bold text-gray-700">
                                        <i class="fas fa-handshake mr-1"></i> Сделки
                                    </label>
                                    <select id="deals-filter" name="deals" class="form-control form-control-sm">
                                        <option value="">Любое количество</option>
                                        <option value="has" {{ request()->input('deals') == 'has' ? 'selected' : '' }}>Есть сделки</option>
                                        <option value="no" {{ request()->input('deals') == 'no' ? 'selected' : '' }}>Нет сделок</option>
                                        <option value="completed" {{ request()->input('deals') == 'completed' ? 'selected' : '' }}>С завершенными сделками</option>
                                    </select>
                                </div>
                            </div>
                            
                            <!-- Фильтр по полю телефона -->
                            <div class="col-md-3 col-sm-6 mb-3">
                                <div class="filter-group">
                                    <label for="phone-filter" class="small font-weight-bold text-gray-700">
                                        <i class="fas fa-phone mr-1"></i> Телефон
                                    </label>                                    <input type="text" id="phone-filter" name="phone" class="form-control form-control-sm phone-mask" 
                                           placeholder="+7 (___) ___-__-__" value="{{ urldecode(request()->input('phone', '')) }}">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Поля для выбора конкретных дат, которые появляются при выборе 'custom' в date_range -->
                    <div class="row custom-date-inputs" style="display: {{ request()->input('date_range') == 'custom' ? 'flex' : 'none' }};">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="date_from" class="small">С даты</label>
                                <input type="date" id="date_from" name="date_from" class="form-control form-control-sm" value="{{ request()->input('date_from') }}">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="date_to" class="small">По дату</label>
                                <input type="date" id="date_to" name="date_to" class="form-control form-control-sm" value="{{ request()->input('date_to') }}">
                            </div>
                        </div>
                    </div>

                    <!-- Кнопки управления фильтрами -->
                    <div class="row mt-3 mb-3">
                        <div class="col-12 d-flex justify-content-between align-items-center flex-wrap">
                            <div>
                                <button type="button" class="btn btn-link text-primary px-0" data-toggle="collapse" data-target="#advancedFilters">
                                    <i class="fas fa-sliders-h mr-1"></i> Дополнительные фильтры
                                </button>
                            </div>
                            <div class="btn-group filter-buttons mt-2 mt-md-0">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-search mr-1"></i> Применить фильтры
                                </button>
                                <button type="button" id="reset-filters" class="btn btn-outline-secondary">
                                    <i class="fas fa-times mr-1"></i> Сбросить
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Активные фильтры -->
                    <div class="active-filters mt-2" id="activeFilters" style="display: none;">
                        <div class="d-flex flex-wrap align-items-center">
                            <span class="small text-muted mr-2">Активные фильтры:</span>
                            <div class="active-filter-tags d-flex flex-wrap">
                                <!-- Тэги активных фильтров будут добавлены через JavaScript -->
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Карточка с таблицей пользователей -->
    <div class="card mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="fas fa-users mr-2"></i> Список пользователей
            </h6>
            <a href="{{ route('admin.users.trashed') }}" class="btn btn-sm btn-outline-danger">
                <i class="fas fa-trash-alt mr-1"></i> Удаленные пользователи
            </a>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="usersDataTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Имя</th>
                           
                            <th>Телефон</th>
                            <th>Статус</th>
                            <th>Брифы</th>
                            <th>Дата регистрации</th>
                            <th>Действия</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($users as $user)
                            <tr>
                                <td>{{ $user->id }}</td>
                                <td> <a href="{{ route('profile.view', $user->id) }}">{{ $user->name }}</a></td>
                              
                                <td>{{ $user->phone ?? 'Не указан' }}</td>
                                <td>
                                    @php
                                        $statusClass = [
                                            'admin' => 'badge-danger',
                                            'client' => 'badge-success',
                                            'user' => 'badge-info',
                                            'designer' => 'badge-primary',
                                            'architect' => 'badge-warning',
                                            'visualizer' => 'badge-dark',
                                            'coordinator' => 'badge-secondary',
                                            'partner' => 'badge-light'
                                        ][$user->status] ?? 'badge-secondary';
                                    @endphp
                                    <span class="badge {{ $statusClass }}">{{ ucfirst($user->status) }}</span>
                                </td>
                                <td class="text-center">
                                    @php
                                        $commonCount = $user->commons()->count();
                                        $commercialCount = $user->commercials()->count();
                                        $totalBriefs = $commonCount + $commercialCount;
                                    @endphp
                                    @if($totalBriefs > 0)
                                        <a href="{{ route('admin.user.briefs', $user->id) }}" class="badge badge-pill badge-primary">
                                            {{ $totalBriefs }} {{ trans_choice('бриф|брифа|брифов', $totalBriefs) }}
                                        </a>
                                    @else
                                        <span class="badge badge-pill badge-light">0 брифов</span>
                                    @endif
                                </td>
                                <td>{{ $user->created_at->format('d.m.Y H:i') }}</td>
                                <td>
                                    <div class="btn-group">
                                        <button type="button" class="btn btn-sm btn-outline-primary edit-user" 
                                                data-id="{{ $user->id }}" data-name="{{ $user->name }}" 
                                                data-email="{{ $user->email }}" data-phone="{{ $user->phone }}" 
                                                data-status="{{ $user->status }}"
                                                title="Редактировать пользователя">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        @if($user->status != 'admin' || Auth::user()->id != $user->id)
                                            <button type="button" class="btn btn-sm btn-outline-danger delete-user" 
                                                    data-id="{{ $user->id }}" data-name="{{ $user->name }}"
                                                    title="Удалить пользователя">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        @endif
                                        <a href="{{ route('admin.user.briefs', $user->id) }}" 
                                           class="btn btn-sm btn-outline-info" title="Брифы пользователя">
                                            <i class="fas fa-clipboard-list"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Кастомные модальные окна -->
<!-- Модальное окно для редактирования пользователя -->
<div class="custom-modal" id="editUserModal">
    <div class="custom-modal-backdrop"></div>
    <div class="custom-modal-container">
        <div class="custom-modal-header">
            <h5 class="custom-modal-title">Редактирование пользователя</h5>
            <button type="button" class="custom-modal-close" data-modal-close="editUserModal">×</button>
        </div>
        <div class="custom-modal-body">
            <form id="edit-user-form">
                <input type="hidden" id="edit-user-id">
                <div class="form-group">
                    <label for="edit-name">Имя</label>
                    <input type="text" class="form-control" id="edit-name" name="name" required>
                </div>
                <div class="form-group">
                    <label for="edit-email">Email</label>
                    <input type="email" class="form-control" id="edit-email" name="email" readonly>
                </div>
                <div class="form-group">
                    <label for="edit-phone">Телефон</label>
                    <input type="tel" class="form-control" id="edit-phone" name="phone">
                </div>
                <div class="form-group">
                    <label for="edit-status">Статус</label>
                    <select class="form-control" id="edit-status" name="status" required>
                        <option value="admin">Администратор</option>
                        <option value="client">Клиент</option>
                        <option value="user">Пользователь</option>
                        <option value="designer">Дизайнер</option>
                        <option value="architect">Архитектор</option>
                        <option value="visualizer">Визуализатор</option>
                        <option value="coordinator">Координатор</option>
                        <option value="partner">Партнер</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="edit-password">Новый пароль (оставьте пустым, чтобы не менять)</label>
                    <input type="password" class="form-control" id="edit-password" name="password">
                </div>
            </form>
        </div>
        <div class="custom-modal-footer">
            <button type="button" class="btn btn-secondary" data-modal-close="editUserModal">Отмена</button>
            <button type="button" class="btn btn-primary" id="save-user">Сохранить</button>
        </div>
    </div>
</div>

<!-- Модальное окно для подтверждения удаления -->
<div class="custom-modal" id="deleteUserModal">
    <div class="custom-modal-backdrop"></div>
    <div class="custom-modal-container">
        <div class="custom-modal-header">
            <h5 class="custom-modal-title">Подтверждение удаления</h5>
            <button type="button" class="custom-modal-close" data-modal-close="deleteUserModal">×</button>
        </div>
        <div class="custom-modal-body">
            <p>Вы действительно хотите удалить пользователя <strong id="delete-user-name"></strong>?</p>
            <p class="text-danger">
                <i class="fas fa-exclamation-triangle"></i> 
                Это действие перенесет пользователя в архив и отвяжет его от активных брифов и сделок.
            </p>
        </div>
        <div class="custom-modal-footer">
            <button type="button" class="btn btn-secondary" data-modal-close="deleteUserModal">Отмена</button>
            <button type="button" class="btn btn-danger" id="confirm-delete">Удалить</button>
        </div>
    </div>
</div>

<!-- Модальное окно для создания пользователя -->
<div class="custom-modal" id="createUserModal">
    <div class="custom-modal-backdrop"></div>
    <div class="custom-modal-container">
        <div class="custom-modal-header">
            <h5 class="custom-modal-title">Создание нового пользователя</h5>
            <button type="button" class="custom-modal-close" data-modal-close="createUserModal">×</button>
        </div>
        <div class="custom-modal-body">
            <form id="create-user-form">
                <div class="form-group">
                    <label for="create-name">Имя</label>
                    <input type="text" class="form-control" id="create-name" name="name" required>
                </div>
                <div class="form-group">
                    <label for="create-email">Email</label>
                    <input type="email" class="form-control" id="create-email" name="email" required>
                </div>
                <div class="form-group">
                    <label for="create-phone">Телефон</label>
                    <input type="tel" class="form-control" id="create-phone" name="phone">
                </div>
                <div class="form-group">
                    <label for="create-status">Статус</label>
                    <select class="form-control" id="create-status" name="status" required>
                        <option value="client">Клиент</option>
                        <option value="user">Пользователь</option>
                        <option value="designer">Дизайнер</option>
                        <option value="architect">Архитектор</option>
                        <option value="visualizer">Визуализатор</option>
                        <option value="coordinator">Координатор</option>
                        <option value="partner">Партнер</option>
                        <option value="admin">Администратор</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="create-password">Пароль</label>
                    <input type="password" class="form-control" id="create-password" name="password" required>
                </div>
            </form>
        </div>
        <div class="custom-modal-footer">
            <button type="button" class="btn btn-secondary" data-modal-close="createUserModal">Отмена</button>
            <button type="button" class="btn btn-success" id="create-user">Создать</button>
        </div>
    </div>
</div>

<!-- Добавляем кнопку открытия модального окна для создания пользователя -->
<div class="mb-3 d-flex justify-content-end">
    <button type="button" class="btn btn-success" data-modal-open="createUserModal">
        <i class="fas fa-user-plus mr-1"></i> Создать пользователя
    </button>
</div>

<!-- Добавляем стили для кастомных модальных окон -->
<style>
/* Стили для кастомных модальных окон */
.custom-modal {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    display: none;
    z-index: 2000;
}

.custom-modal.active {
    display: flex;
    align-items: center;
    justify-content: center;
    animation: fadeIn 0.3s ease-out;
}

.custom-modal-backdrop {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.5);
    z-index: 2001;
}

.custom-modal-container {
    position: relative;
    background-color: #fff;
    width: 100%;
    max-width: 500px;
    border-radius: 10px;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
    z-index: 2002;
    overflow: hidden;
    opacity: 0;
    transform: translateY(-20px);
    animation: slideIn 0.3s ease-out forwards;
}

.custom-modal-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 15px 20px;
    border-bottom: 1px solid #e3e6f0;
    background-color: #f8f9fc;
}

.custom-modal-title {
    margin: 0;
    font-size: 1.25rem;
    font-weight: 600;
    color: #4e73df;
    display: flex;
    align-items: center;
}

.custom-modal-title i {
    margin-right: 10px;
}

.custom-modal-close {
    background: none;
    border: none;
    font-size: 24px;
    font-weight: 700;
    color: #858796;
    cursor: pointer;
    padding: 0;
    line-height: 1;
    transition: color 0.2s;
}

.custom-modal-close:hover {
    color: #e74a3b;
}

.custom-modal-body {
    padding: 20px;
    max-height: calc(100vh - 200px);
    overflow-y: auto;
}

.custom-modal-footer {
    display: flex;
    justify-content: flex-end;
    padding: 15px 20px;
    border-top: 1px solid #e3e6f0;
    background-color: #f8f9fc;
}

.custom-modal-footer button {
    margin-left: 10px;
}

/* Анимации */
@keyframes fadeIn {
    from {
        opacity: 0;
    }
    to {
        opacity: 1;
    }
}

@keyframes slideIn {
    from {
        opacity: 0;
        transform: translateY(-20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Дополнительные стили для полей ввода в модальных окнах */
.custom-modal .form-group {
    margin-bottom: 15px;
}

.custom-modal .form-control {
    border: 1px solid #d1d3e2;
    border-radius: 5px;
    padding: 8px 12px;
    width: 100%;
    font-size: 0.9rem;
}

.custom-modal select.form-control {
    appearance: none;
    background: #fff url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='8' height='4' viewBox='0 0 8 4'%3E%3Cpath fill='%23333' d='M0 0h8L4 4z'/%3E%3C/svg%3E") no-repeat right 12px center;
    padding-right: 24px;
}

.custom-modal label {
    font-weight: 500;
    color: #5a5c69;
    margin-bottom: 5px;
    display: block;
}

/* Фиксирование прокрутки при открытии модального окна */
body.modal-open {
    overflow: hidden;
}

/* Адаптивность для мобильных устройств */
@media (max-width: 576px) {
    .custom-modal-container {
        width: 95%;
        max-height: 90vh;
    }
    
    .custom-modal-body {
        max-height: calc(90vh - 130px);
    }
    
    .custom-modal-footer {
        flex-direction: column;
    }
    
    .custom-modal-footer button {
        margin: 5px 0 0;
        width: 100%;
    }
    
    .custom-modal-footer button:first-child {
        margin-top: 0;
    }
}
</style>

<!-- Добавляем JavaScript для работы с кастомными модальными окнами -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    /* Инициализация модальных окон */
    initCustomModals();
    
    /* Обработчики для действий с пользователями */
    initUserActions();
    
    /* Функция для DataTables остается без изменений */
    // ...existing code...
});

/* Функция инициализации кастомных модальных окон */
function initCustomModals() {
    console.log('Инициализация кастомных модальных окон');
    
    // Добавляем обработчики для открытия модальных окон
    document.querySelectorAll('[data-modal-open]').forEach(button => {
        button.addEventListener('click', event => {
            const modalId = button.getAttribute('data-modal-open');
            openModal(modalId);
        });
    });
    
    // Добавляем обработчики для закрытия модальных окон
    document.querySelectorAll('[data-modal-close]').forEach(button => {
        button.addEventListener('click', event => {
            const modalId = button.getAttribute('data-modal-close');
            closeModal(modalId);
        });
    });
    
    // Добавляем обработчики для клика по фону модального окна
    document.querySelectorAll('.custom-modal-backdrop').forEach(backdrop => {
        backdrop.addEventListener('click', event => {
            const modal = backdrop.closest('.custom-modal');
            if (modal) {
                closeModal(modal.id);
            }
        });
    });
    
    // Добавляем обработчик клавиши Escape для закрытия модальных окон
    document.addEventListener('keydown', event => {
        if (event.key === 'Escape') {
            const openModal = document.querySelector('.custom-modal.active');
            if (openModal) {
                closeModal(openModal.id);
            }
        }
    });
}

/* Функция открытия модального окна */
function openModal(modalId) {
    console.log('Открытие модального окна:', modalId);
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.add('active');
        document.body.classList.add('modal-open');
    } else {
        console.error('Модальное окно не найдено:', modalId);
    }
}

/* Функция закрытия модального окна */
function closeModal(modalId) {
    console.log('Закрытие модального окна:', modalId);
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.remove('active');
        document.body.classList.remove('modal-open');
    } else {
        console.error('Модальное окно не найдено:', modalId);
    }
}

/* Инициализация обработчиков действий с пользователями */
function initUserActions() {
    // Обработчик для кнопок редактирования пользователя
    document.querySelectorAll('.edit-user').forEach(button => {
        button.addEventListener('click', function() {
            var userId = this.getAttribute('data-id');
            var userName = this.getAttribute('data-name');
            var userEmail = this.getAttribute('data-email');
            var userPhone = this.getAttribute('data-phone');
            var userStatus = this.getAttribute('data-status');
            
            document.getElementById('edit-user-id').value = userId;
            document.getElementById('edit-name').value = userName;
            document.getElementById('edit-email').value = userEmail;
            document.getElementById('edit-phone').value = userPhone;
            document.getElementById('edit-status').value = userStatus;
            document.getElementById('edit-password').value = '';
            
            openModal('editUserModal');
        });
    });
    
    // Обработчик для кнопок удаления пользователя
    document.querySelectorAll('.delete-user').forEach(button => {
        button.addEventListener('click', function() {
            var userId = this.getAttribute('data-id');
            var userName = this.getAttribute('data-name');
            
            document.getElementById('delete-user-name').textContent = userName;
            document.getElementById('confirm-delete').setAttribute('data-id', userId);
            
            openModal('deleteUserModal');
        });
    });
    
    // Обработчик сохранения пользователя
    document.getElementById('save-user').addEventListener('click', function() {
        var userId = document.getElementById('edit-user-id').value;
        var formData = {
            name: document.getElementById('edit-name').value,
            phone: document.getElementById('edit-phone').value,
            status: document.getElementById('edit-status').value,
            password: document.getElementById('edit-password').value,
            _token: document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        };
        
        fetch('/admin/users/' + userId, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json'
            },
            body: JSON.stringify(formData)
        })
        .then(response => response.json())
        .then(data => {
            closeModal('editUserModal');
            
            // Показываем уведомление об успешном обновлении
            showToast('Пользователь успешно обновлен', 'success');
            
            // Обновляем страницу через 1 секунду
            setTimeout(function() {
                location.reload();
            }, 1000);
        })
        .catch(error => {
            console.error('Ошибка при обновлении пользователя:', error);
            showToast('Ошибка при обновлении пользователя', 'error');
        });
    });
    
    // Обработчик подтверждения удаления пользователя
    document.getElementById('confirm-delete').addEventListener('click', function() {
        var userId = this.getAttribute('data-id');
        
        fetch('/admin/users/' + userId, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            closeModal('deleteUserModal');
            
            // Показываем уведомление об успешном удалении
            showToast('Пользователь успешно удален', 'success');
            
            // Обновляем страницу через 1 секунду
            setTimeout(function() {
                location.reload();
            }, 1000);
        })
        .catch(error => {
            console.error('Ошибка при удалении пользователя:', error);
            showToast('Ошибка при удалении пользователя', 'error');
        });
    });
    
    // Обработчик создания нового пользователя
    document.getElementById('create-user').addEventListener('click', function() {
        var formData = {
            name: document.getElementById('create-name').value,
            email: document.getElementById('create-email').value,
            phone: document.getElementById('create-phone').value,
            status: document.getElementById('create-status').value,
            password: document.getElementById('create-password').value,
            _token: document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        };
        
        fetch('/admin/users', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json'
            },
            body: JSON.stringify(formData)
        })
        .then(response => {
            if (!response.ok) {
                return response.json().then(error => Promise.reject(error));
            }
            return response.json();
        })
        .then(data => {
            closeModal('createUserModal');
            
            // Показываем уведомление об успешном создании
            showToast('Пользователь успешно создан', 'success');
            
            // Обновляем страницу через 1 секунду
            setTimeout(function() {
                location.reload();
            }, 1000);
        })
        .catch(error => {
            console.error('Ошибка при создании пользователя:', error);
            
            let errorMessage = 'Ошибка при создании пользователя';
            if (error.errors) {
                errorMessage = Object.values(error.errors).flat().join('<br>');
            }
            
            showToast(errorMessage, 'error');
        });
    });
}

/* Функция отображения уведомлений (toast) */
function showToast(message, type = 'info') {
    // Создаем элемент toast
    const toast = document.createElement('div');
    toast.className = `custom-toast toast-${type}`;
    
    // Создаем содержимое toast
    const content = document.createElement('div');
    content.className = 'custom-toast-content';
    content.innerHTML = message;
    
    // Добавляем кнопку закрытия
    const closeBtn = document.createElement('button');
    closeBtn.className = 'custom-toast-close';
    closeBtn.innerHTML = '&times;';
    closeBtn.addEventListener('click', function() {
        document.body.removeChild(toast);
    });
    
    // Собираем toast
    toast.appendChild(content);
    toast.appendChild(closeBtn);
    
    // Добавляем в DOM
    document.body.appendChild(toast);
    
    // Показываем toast с анимацией
    setTimeout(() => {
        toast.classList.add('show');
    }, 10);
    
    // Автоматически скрываем через 5 секунд
    setTimeout(() => {
        toast.classList.remove('show');
        setTimeout(() => {
            if (toast.parentNode) {
                document.body.removeChild(toast);
            }
        }, 300);
    }, 5000);
}

/* Добавляем стили для toast уведомлений */
document.head.insertAdjacentHTML('beforeend', `
<style>
    .custom-toast {
        position: fixed;
        top: 20px;
        right: 20px;
        min-width: 250px;
        max-width: 350px;
        padding: 15px;
        border-radius: 5px;
        box-shadow: 0 3px 10px rgba(0, 0, 0, 0.2);
        z-index: 3000;
        display: flex;
        align-items: center;
        justify-content: space-between;
        transform: translateX(400px);
        opacity: 0;
        transition: transform 0.3s ease, opacity 0.3s ease;
    }
    
    .custom-toast.show {
        transform: translateX(0);
        opacity: 1;
    }
    
    .custom-toast-content {
        flex: 1;
        padding-right: 15px;
    }
    
    .custom-toast-close {
        background: none;
        border: none;
        font-size: 20px;
        cursor: pointer;
        opacity: 0.7;
        transition: opacity 0.2s;
        padding: 0 5px;
    }
    
    .custom-toast-close:hover {
        opacity: 1;
    }
    
    .toast-success {
        background-color: #1cc88a;
        color: white;
    }
    
    .toast-error {
        background-color: #e74a3b;
        color: white;
    }
    
    .toast-info {
        background-color: #4e73df;
        color: white;
    }
    
    .toast-warning {
        background-color: #f6c23e;
        color: #333;
    }
    
    @media (max-width: 576px) {
        .custom-toast {
            top: 10px;
            right: 10px;
            left: 10px;
            max-width: calc(100% - 20px);
        }
    }
</style>
`);
</script>

<script>
$(document).ready(function() {
    // Проверяем, инициализирована ли уже таблица
    if ($.fn.dataTable.isDataTable('#usersDataTable')) {
        // Если да, то уничтожаем существующий экземпляр
        $('#usersDataTable').DataTable().destroy();
    }
    
    // Инициализируем таблицу с нужными параметрами
    $('#usersDataTable').DataTable({
        "language": {
            "url": "https://cdn.datatables.net/plug-ins/1.10.21/i18n/Russian.json"
        },
        "pageLength": 10,
        "ordering": true,
        "order": [[6, "desc"]], // Сортировка по дате регистрации по умолчанию (по убыванию)
        "columnDefs": [
            { "orderable": false, "targets": [0, 7] } // Отключаем сортировку для колонок с чекбоксами и кнопками действий
        ],
        "responsive": true,
        "stateSave": false // Не сохраняем состояние таблицы между перезагрузками
    });
    
    // Инициализация тултипов Bootstrap
    $('[data-toggle="tooltip"]').tooltip();
    
    // Остальная логика для работы с таблицей
    // ...existing code...

    // Инициализация Bootstrap Select
    $('.selectpicker').selectpicker();
    
    // Обработчик для сброса фильтров
    $('#reset-filters').click(function() {
        $('#search').val('');
        $('#statuses').selectpicker('deselectAll');
        $('#date-from, #date-to').val('');
        $('#sort').val('created_at_desc');
        $('#filter-form').submit();
    });
    
    // Обработчик для отображения/скрытия фильтров
    $('#filter-header a').click(function() {
        var icon = $(this).find('.fas.fa-chevron-down');
        var text = $(this).find('span');
        
        if ($(this).attr('aria-expanded') === 'true') {
            icon.removeClass('fa-chevron-up').addClass('fa-chevron-down');
            text.text('Показать фильтры');
        } else {
            icon.removeClass('fa-chevron-down').addClass('fa-chevron-up');
            text.text('Скрыть фильтры');
        }
    });
    
    // Проверяем, есть ли активные фильтры
    function checkActiveFilters() {
        var hasFilters = false;
        
        if ($('#search').val() !== '') hasFilters = true;
        if ($('#statuses').val() && $('#statuses').val().length > 0) hasFilters = true;
        if ($('#date-from').val() !== '') hasFilters = true;
        if ($('#date-to').val() !== '') hasFilters = true;
        if ($('#sort').val() !== 'created_at_desc') hasFilters = true;
        
        if (hasFilters) {
            $('#filter-header a').trigger('click');
        }
    }
    
    // Проверяем активные фильтры при загрузке страницы
    checkActiveFilters();
    
    // Обработчики для модального окна редактирования
    $('.edit-user').click(function() {
        var userId = $(this).data('id');
        var userName = $(this).data('name');
        var userEmail = $(this).data('email');
        var userPhone = $(this).data('phone');
        var userStatus = $(this).data('status');
        
        $('#edit-user-id').val(userId);
        $('#edit-name').val(userName);
        $('#edit-email').val(userEmail);
        $('#edit-phone').val(userPhone);
        $('#edit-status').val(userStatus);
        $('#edit-password').val('');
        
        $('#editUserModal').modal('show');
    });
    
    // Сохранение изменений пользователя
    $('#save-user').click(function() {
        var userId = $('#edit-user-id').val();
        var formData = {
            name: $('#edit-name').val(),
            phone: $('#edit-phone').val(),
            status: $('#edit-status').val(),
            password: $('#edit-password').val(),
            _token: $('meta[name="csrf-token"]').attr('content')
        };
        
        $.ajax({
            url: '/admin/users/' + userId,
            type: 'PUT',
            data: formData,
            success: function(response) {
                $('#editUserModal').modal('hide');
                
                // Показываем уведомление об успешном обновлении
                var toast = `
                    <div class="toast" role="alert" aria-live="assertive" aria-atomic="true" data-delay="3000">
                        <div class="toast-header bg-success text-white">
                            <strong class="mr-auto">Успех</strong>
                            <button type="button" class="ml-2 mb-1 close" data-dismiss="toast">&times;</button>
                        </div>
                        <div class="toast-body">
                            Пользователь успешно обновлен
                        </div>
                    </div>
                `;
                
                $('.toast-container').append(toast);
                $('.toast').toast('show');
                
                // Обновляем страницу через 1 секунду
                setTimeout(function() {
                    location.reload();
                }, 1000);
            },
            error: function(xhr) {
                console.error('Ошибка при обновлении пользователя:', xhr.responseText);
                alert('Ошибка при обновлении пользователя. Проверьте консоль для подробностей.');
            }
        });
    });
    
    // Подготовка модального окна для удаления
    $('.delete-user').click(function() {
        var userId = $(this).data('id');
        var userName = $(this).data('name');
        
        $('#delete-user-name').text(userName);
        $('#confirm-delete').data('id', userId);
        
        $('#deleteUserModal').modal('show');
    });
    
    // Подтверждение удаления пользователя
    $('#confirm-delete').click(function() {
        var userId = $(this).data('id');
        
        $.ajax({
            url: '/admin/users/' + userId,
            type: 'DELETE',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                $('#deleteUserModal').modal('hide');
                
                // Показываем уведомление об успешном удалении
                var toast = `
                    <div class="toast" role="alert" aria-live="assertive" aria-atomic="true" data-delay="3000">
                        <div class="toast-header bg-success text-white">
                            <strong class="mr-auto">Успех</strong>
                            <button type="button" class="ml-2 mb-1 close" data-dismiss="toast">&times;</button>
                        </div>
                        <div class="toast-body">
                            Пользователь успешно удален
                        </div>
                    </div>
                `;
                
                $('.toast-container').append(toast);
                $('.toast').toast('show');
                
                // Обновляем страницу через 1 секунду
                setTimeout(function() {
                    location.reload();
                }, 1000);
            },
            error: function(xhr) {
                console.error('Ошибка при удалении пользователя:', xhr.responseText);
                alert('Ошибка при удалении пользователя. Проверьте консоль для подробностей.');
            }
        });
    });
    
    // Создание нового пользователя
    $('#create-user').click(function() {
        var formData = {
            name: $('#create-name').val(),
            email: $('#create-email').val(),
            phone: $('#create-phone').val(),
            status: $('#create-status').val(),
            password: $('#create-password').val(),
            _token: $('meta[name="csrf-token"]').attr('content')
        };
        
        $.ajax({
            url: '/admin/users',
            type: 'POST',
            data: formData,
            success: function(response) {
                $('#createUserModal').modal('hide');
                
                // Показываем уведомление об успешном создании
                var toast = `
                    <div class="toast" role="alert" aria-live="assertive" aria-atomic="true" data-delay="3000">
                        <div class="toast-header bg-success text-white">
                            <strong class="mr-auto">Успех</strong>
                            <button type="button" class="ml-2 mb-1 close" data-dismiss="toast">&times;</button>
                        </div>
                        <div class="toast-body">
                            Пользователь успешно создан
                        </div>
                    </div>
                `;
                
                $('.toast-container').append(toast);
                $('.toast').toast('show');
                
                // Обновляем страницу через 1 секунду
                setTimeout(function() {
                    location.reload();
                }, 1000);
            },
            error: function(xhr) {
                console.error('Ошибка при создании пользователя:', xhr.responseText);
                
                var errorMessage = 'Ошибка при создании пользователя';
                
                if (xhr.responseJSON && xhr.responseJSON.errors) {
                    errorMessage = Object.values(xhr.responseJSON.errors).flat().join('<br>');
                }
                
                // Показываем ошибку
                var toast = `
                    <div class="toast" role="alert" aria-live="assertive" aria-atomic="true" data-delay="5000">
                        <div class="toast-header bg-danger text-white">
                            <strong class="mr-auto">Ошибка</strong>
                            <button type="button" class="ml-2 mb-1 close" data-dismiss="toast">&times;</button>
                        </div>
                        <div class="toast-body">
                            ${errorMessage}
                        </div>
                    </div>
                `;
                
                $('.toast-container').append(toast);
                $('.toast').toast('show');
            }
        });
    });
});
</script>

<!-- JavaScript для обработки фильтров с обновленными селекторами -->
<script>
$(document).ready(function() {
    // Инициализация bootstrap-select, если есть
    if($.fn.selectpicker) {
        $('.filter-card .selectpicker').selectpicker();
    }
    
    // Функция для обновления счетчика активных фильтров
    function updateFilterCounter() {
        let activeFilters = 0;
        const activeTags = [];
        
        // Проверяем все поля формы фильтров
        $('#users-filter-form').find('select, input').each(function() {
            const input = $(this);
            const value = input.val();
            const name = input.attr('name');
            
            // Если поле не пустое, увеличиваем счетчик
            if(value && value !== '' && (!Array.isArray(value) || value.length > 0)) {
                activeFilters++;
                
                // Получаем название фильтра для отображения
                let filterLabel = input.closest('.filter-group').find('label').text().trim();
                let valueLabel = '';
                
                // Для селектов берем текст выбранного option
                if(input.is('select')) {
                    valueLabel = input.find('option:selected').text();
                } else {
                    valueLabel = value;
                }
                
                // Формируем тэг активного фильтра
                activeTags.push({
                    name: name,
                    label: `${filterLabel}: ${valueLabel}`,
                });
            }
        });
        
        // Обновляем счетчик
        $('#filter-counter').text(activeFilters);
        
        // Если есть активные фильтры, показываем их
        if(activeFilters > 0) {
            $('#filter-counter').removeClass('badge-light').addClass('badge-primary');
            
            // Очищаем и заполняем контейнер тэгов
            const tagsContainer = $('.active-filter-tags').empty();
            
            // Добавляем тэги активных фильтров
            activeTags.forEach(tag => {
                tagsContainer.append(`
                    <span class="badge badge-info mr-2 mb-1 p-2 active-filter-tag">
                        ${tag.label}
                        <a href="#" class="text-white ml-1 remove-filter" data-name="${tag.name}">
                            <i class="fas fa-times"></i>
                        </a>
                    </span>
                `);
            });
            
            // Показываем блок активных фильтров
            $('#activeFilters').show();
        } else {
            $('#filter-counter').removeClass('badge-primary').addClass('badge-light');
            $('#activeFilters').hide();
        }
    }
    
    // Инициализация состояния фильтров
    updateFilterCounter();
    
    // Обработчик для сброса фильтров
    $('#reset-filters').click(function(e) {
        e.preventDefault();
        
        // Очищаем все поля формы
        const form = $('#users-filter-form');
        form.find('select, input').val('');
        
        // Если есть bootstrap-select, обновляем его
        if($.fn.selectpicker) {
            $('.selectpicker').selectpicker('refresh');
        }
        
        // Обновляем счетчик
        updateFilterCounter();
        
        // Отправляем форму для обновления результатов
        form.submit();
    });
    
    // Обработчик для удаления отдельного фильтра
    $(document).on('click', '.remove-filter', function(e) {
        e.preventDefault();
        const name = $(this).data('name');
        
        // Находим и очищаем поле фильтра
        const input = $('#users-filter-form').find(`[name="${name}"]`);
        input.val('');
        
        // Если есть bootstrap-select, обновляем его
        if(input.hasClass('selectpicker')) {
            input.selectpicker('refresh');
        }
        
        // Обновляем счетчик и отправляем форму
        updateFilterCounter();
        $('#users-filter-form').submit();
    });
    
    // Обработчики изменения полей фильтров для визуальной обратной связи
    $('#users-filter-form').find('select, input').change(function() {
        updateFilterCounter();
    });
    
    // Автоматически раскрываем дополнительные фильтры, если они активны
    const hasAdvancedFilters = $('#advancedFilters').find('select, input').filter(function() {
        const value = $(this).val();
        return value && value !== '' && (!Array.isArray(value) || value.length > 0);
    }).length > 0;
    
    if(hasAdvancedFilters) {
        $('#advancedFilters').collapse('show');
    }

    // Показываем/скрываем поля ввода кастомных дат
    $('#date-filter').change(function() {
        if ($(this).val() === 'custom') {
            $('.custom-date-inputs').show();
        } else {
            $('.custom-date-inputs').hide();
        }
    });
});
</script>

<!-- Стили для фильтров -->
<style>
    .filter-card {
        border-color: rgba(0, 123, 255, 0.2);
    }
    
    .filter-card .card-header {
        background-color: rgba(0, 123, 255, 0.05);
        cursor: pointer;
    }
    
    #filter-header a {
        color: #4e73df;
    }
    
    .filter-group label {
        margin-bottom: 0.3rem;
    }
    
    .active-filter-tag {
        display: inline-flex;
        align-items: center;
    }
    
    .active-filter-tag a {
        text-decoration: none;
    }
    
    .active-filter-tag a:hover {
        opacity: 0.8;
    }
    
    /* Улучшенная стилизация bootstrap-select */
    .bootstrap-select .dropdown-toggle {
        background-color: #fff;
        border: 1px solid #d1d3e2 !important;
    }
    
    .bootstrap-select .dropdown-toggle:focus {
        outline: 0 !important;
        box-: 0 0 0 0.2rem rgba(78, 115, 223, 0.25) !important;
    }
    
    /* Адаптация для мобильных устройств */
    @media (max-width: 576px) {
        .filter-group label {
            font-size: 0.7rem;
        }
        
        .btn-sm {
            padding: 0.25rem 0.5rem;
            font-size: 0.75rem;
        }
    }
</style>

<!-- Контейнер для уведомлений (toast) -->
<div aria-live="polite" aria-atomic="true" class="toast-container" style="position: fixed; top: 15px; right: 15px; z-index: 1060;"></div>

<!-- Добавляем jQuery Mask Plugin после основных скриптов -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js"></script>

<script>
$(document).ready(function() {
   
      // Инициализация маски для телефона
    $('.phone-mask').mask('+7 (000) 000-00-00', {
        placeholder: "+7 (___) ___-__-__"
    });
    
    // Отладка отправки формы фильтров
    $('#users-filter-form').on('submit', function(e) {
        const phoneValue = $('#phone-filter').val();
        if (phoneValue) {
            console.log('Отправка формы с номером телефона:', phoneValue);
            console.log('URL encoded:', encodeURIComponent(phoneValue));
        }
    });
    
    // Отладка изменения поля телефона
    $('#phone-filter').on('change', function() {
        const value = $(this).val();
        console.log('Изменение поля телефона:', value);
        console.log('Длина строки:', value.length);
    });
    
    // ...existing code...
});
</script>