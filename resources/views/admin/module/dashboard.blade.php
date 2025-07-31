<div class="container-fluid py-4">
    <!-- Заголовок страницы -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Панель управления</h1>
        <div class="mt-2 mt-sm-0">
            <button class="btn btn-primary btn-sm" id="refreshDashboard">
                <i class="fas fa-sync-alt fa-sm"></i> Обновить данные
            </button>
        </div>
    </div>

    <!-- Фильтры периодов -->
    <div class="card mb-4">
        <div class="card-body">
            <div class="d-flex flex-wrap align-items-center">
                <label class="mr-2 mb-2 mb-md-0 font-weight-bold text-primary">Период:</label>
                <div class="btn-group period-filter" role="group">
                    <button type="button" class="btn btn-outline-primary btn-sm" data-period="7days">7 дней</button>
                    <button type="button" class="btn btn-outline-primary btn-sm active" data-period="30days">30 дней</button>
                    <button type="button" class="btn btn-outline-primary btn-sm" data-period="90days">90 дней</button>
                    <button type="button" class="btn btn-outline-primary btn-sm" data-period="year">Год</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Общая статистика - карточки -->
    <div class="row">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary  h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center flex-bottom-pc">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Пользователей</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $usersCount }}</div>
                        </div>
                        <div class="col-auto">
                            <div class="text-success small">
                                <i class="fas fa-arrow-up mr-1"></i> {{ $newUsersLast30Days }} за 30 дней
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success  h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center flex-bottom-pc">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Всего сделок</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $dealsCount }}</div>
                        </div>
                        <div class="col-auto">
                            <div class="text-success small">
                                <i class="fas fa-arrow-up mr-1"></i> {{ $newDealsLast30Days }} за 30 дней
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info  h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center flex-bottom-pc">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Общая сумма</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($totalDealAmount, 0, '.', ' ') }} ₽</div>
                        </div>
                        <div class="col-auto">
                            <div class="text-success small">
                                <i class="fas fa-arrow-up mr-1"></i> 12% с прошлого месяца
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning  h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center flex-bottom-pc">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Средний рейтинг</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($avgRating, 1) }}</div>
                        </div>
                        <div class="col-auto">
                            <div class="text-success small">
                                <i class="fas fa-arrow-up mr-1"></i> 0.2 с прошлого месяца
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Навигация по вкладкам -->
    <ul class="nav nav-tabs flex-nowrap overflow-auto dashboard-tabs" id="dashboardTabs" role="tablist">
        <li class="nav-item">
            <a class="nav-link active" id="analytics-tab" data-toggle="tab" href="#analytics" role="tab"
               aria-controls="analytics" aria-selected="true">
               <i class="fas fa-chart-line mr-2"></i><span class="tab-title">Аналитика</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" id="tables-tab" data-toggle="tab" href="#tables" role="tab"
               aria-controls="tables" aria-selected="false">
               <i class="fas fa-table mr-2"></i><span class="tab-title">Таблицы</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" id="summary-tab" data-toggle="tab" href="#summary" role="tab"
               aria-controls="summary" aria-selected="false">
               <i class="fas fa-info-circle mr-2"></i><span class="tab-title">Общие сведения</span>
            </a>
        </li>
    </ul>

    <!-- Содержимое вкладок -->
    <div class="tab-content mt-3" id="dashboardTabContent">
        <!-- Вкладка: Аналитика -->
        <div class="tab-pane fade show active" id="analytics" role="tabpanel" aria-labelledby="analytics-tab">
            <!-- Графики в сетке Bootstrap 4 -->
            <div class="row">
                <div class="col-xl-6 col-lg-6">
                    <div class="card  mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">Динамика пользователей</h6>
                        </div>
                        <div class="card-body">
                            <div class="chart-container" style="height: 300px;">
                                <canvas id="usersGrowthChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-6 col-lg-6">
                    <div class="card  mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">Динамика сделок</h6>
                        </div>
                        <div class="card-body">
                            <div class="chart-container" style="height: 300px;">
                                <canvas id="dealsGrowthChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-6 col-lg-6">
                    <div class="card  mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">Пользователи по статусам</h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-8">
                                    <div class="chart-container" style="height: 260px;">
                                        <canvas id="userRolesChart"></canvas>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="chart-legend mt-3" id="userRolesLegend"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-6 col-lg-6">
                    <div class="card  mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">Сделки по статусам</h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-8">
                                    <div class="chart-container" style="height: 260px;">
                                        <canvas id="dealStatusChart"></canvas>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="chart-legend mt-3" id="dealStatusLegend"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Вкладка: Таблицы -->
        <div class="tab-pane fade" id="tables" role="tabpanel" aria-labelledby="tables-tab">
            <div class="row">
                <div class="col-lg-6">
                    <div class="card  mb-4">
                        <div class="card-header py-3 d-flex justify-content-between align-items-center">
                            <h6 class="m-0 font-weight-bold text-primary">Недавно зарегистрированные пользователи</h6>
                            <a href="{{ route('admin.users') }}" class="btn btn-sm btn-outline-primary">Все пользователи</a>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered" id="recentUsersTable" width="100%" cellspacing="0">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Имя</th>
                                            <th>Email</th>
                                            <th>Статус</th>
                                            <th>Дата</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($recentUsers as $recentUser)
                                            <tr>
                                                <td>{{ $recentUser->id }}</td>
                                                <td>
                                                    <a href="{{ route('profile.view', $recentUser->id) }}">
                                                        {{ $recentUser->name }}
                                                    </a>
                                                </td>
                                                <td>{{ $recentUser->email }}</td>
                                                <td>{{ ucfirst($recentUser->status) }}</td>
                                                <td>{{ $recentUser->created_at->format('d.m.Y') }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="card  mb-4">
                        <div class="card-header py-3 d-flex justify-content-between align-items-center">
                            <h6 class="m-0 font-weight-bold text-primary">Недавно созданные сделки</h6>
                            <a href="{{ route('deal.cardinator') }}" class="btn btn-sm btn-primary">Все сделки</a>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover" width="100%" cellspacing="0">
                                    <thead>
                                        <tr>
                                            <th>Название</th>
                                            <th>Статус</th>
                                            <th>Сумма</th>
                                            <th>Дата создания</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($recentDeals as $deal)
                                        <tr>
                                            <td>{{ $deal->name }}</td>
                                            <td>
                                                <span class="badge badge-{{ $deal->status == 'Проект завершен' ? 'success' : 
                                                    ($deal->status == 'Проект на паузе' ? 'warning' : 'primary') }}">
                                                    {{ $deal->status }}
                                                </span>
                                            </td>
                                            <td>{{ number_format($deal->total_sum, 0, '.', ' ') }} ₽</td>
                                            <td>{{ $deal->created_at->format('d.m.Y') }}</td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Брифы без сделок -->
            <div class="card  mb-4">
                <div class="card-header py-3 d-flex align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-file-alt mr-2"></i>Брифы без сделок
                    </h6>
                    <span class="badge badge-warning ml-2">Требуют внимания</span>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Тип</th>
                                    <th>Название</th>
                                    <th>Пользователь</th>
                                       <th>Номер</th>
                                    <th>Сумма</th>
                                    <th>Создан</th>
                                    <th>Статус</th>
                                    <th>Действия</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $briefsWithoutDeals = [];
                                    
                                    // Получаем общие брифы без сделок
                                    $commonBriefs = \App\Models\Common::whereNull('deal_id')
                                        ->where('status', 'Завершенный')
                                        ->orderBy('created_at', 'desc')
                                        ->take(10)
                                        ->get();
                                        
                                    foreach ($commonBriefs as $brief) {
                                        $briefUser = \App\Models\User::find($brief->user_id);
                                        $briefsWithoutDeals[] = [
                                            'id' => $brief->id,
                                            'type' => 'Общий',
                                            'title' => $brief->title,
                                            'user' => \App\Models\User::find($brief->user_id)->name ?? 'Неизвестно',
                                            'user_id' => $brief->user_id,
                                            'phone' => $briefUser && $briefUser->phone ? $briefUser->phone : 'Не указан',
                                            'price' => $brief->price ?? 0,
                                            'created_at' => $brief->created_at,
                                            'status' => $brief->status,
                                            'edit_route' => route('admin.brief.common.edit', $brief->id),
                                            'brief_type' => 'common'
                                        ];
                                    }
                                    
                                    // Получаем коммерческие брифы без сделок
                                    $commercialBriefs = \App\Models\Commercial::whereNull('deal_id')
                                        ->where('status', 'Завершенный')
                                        ->orderBy('created_at', 'desc')
                                        ->take(10)
                                        ->get();
                                        
                                    foreach ($commercialBriefs as $brief) {
                                        $briefUser = \App\Models\User::find($brief->user_id);
                                        $briefsWithoutDeals[] = [
                                            'id' => $brief->id,
                                            'type' => 'Коммерческий',
                                            'title' => $brief->title,
                                            'user' => \App\Models\User::find($brief->user_id)->name ?? 'Неизвестно',
                                            'user_id' => $brief->user_id,
                                            'phone' => $briefUser && $briefUser->phone ? $briefUser->phone : 'Не указан',
                                            'price' => $brief->price ?? 0,
                                            'created_at' => $brief->created_at,
                                            'status' => $brief->status,
                                            'edit_route' => route('admin.brief.commercial.edit', $brief->id),
                                            'brief_type' => 'commercial'
                                        ];
                                    }
                                    
                                    // Сортируем все брифы по дате создания
                                    usort($briefsWithoutDeals, function($a, $b) {
                                        return $b['created_at'] <=> $a['created_at'];
                                    });
                                    
                                    // Берем только первые 10
                                    $briefsWithoutDeals = array_slice($briefsWithoutDeals, 0, 10);
                                @endphp
                                
                                @if(count($briefsWithoutDeals) > 0)
                                    @foreach($briefsWithoutDeals as $brief)
                                        <tr>
                                            <td>{{ $brief['id'] }}</td>
                                            <td><span class="badge badge-{{ $brief['type'] == 'Общий' ? 'info' : 'primary' }}">{{ $brief['type'] }}</span></td>
                                            <td>{{ $brief['title'] }}</td>
                                            <td>
                                                <a href="{{ route('admin.user.briefs', $brief['user_id']) }}">
                                                    {{ $brief['user'] }}
                                                </a>
                                            </td>
                                            <td>
                                                {{ !empty($brief['phone']) ? $brief['phone'] : 'Не указан' }}
                                            </td>
                                            <td>{{ number_format($brief['price'], 0, '.', ' ') }} ₽</td>
                                            <td>{{ $brief['created_at']->format('d.m.Y H:i') }}</td>
                                            <td><span class="badge badge-success">{{ $brief['status'] }}</span></td>
                                            <td>
                                                <a href="{{ $brief['edit_route'] }}" class="btn btn-sm btn-outline-primary">
                                                    <i class="fas fa-edit"></i> Ред.
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                @else
                                    <tr>
                                        <td colspan="8" class="text-center">Брифов без сделок не найдено</td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Вкладка: Общие сведения -->
        <div class="tab-pane fade" id="summary" role="tabpanel" aria-labelledby="summary-tab">
            <div class="row">
                <div class="col-lg-6">
                    <div class="card  mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">Статистика брифов</h6>
                        </div>
                        <div class="card-body">
                            <div class="row align-items-center">
                                <div class="col-md-6">
                                    <div class="chart-container" style="height: 200px;">
                                        <canvas id="briefsChart"></canvas>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="text-center">
                                        <div class="mb-3">
                                            <h5>Общие брифы</h5>
                                            <h3>{{ $commonsCount }}</h3>
                                        </div>
                                        <div class="mb-3">
                                            <h5>Коммерческие брифы</h5>
                                            <h3>{{ $commercialsCount }}</h3>
                                        </div>
                                        <div>
                                            <h5>Конверсия в сделки</h5>
                                            <h3>
                                                @php
                                                    $totalBriefs = $commonsCount + $commercialsCount;
                                                    $conversionRate = $totalBriefs > 0 ? round(($dealsCount / $totalBriefs) * 100) : 0;
                                                @endphp
                                                {{ $conversionRate }}%
                                            </h3>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="card  mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">Прогноз роста</h6>
                        </div>
                        <div class="card-body">
                            <div class="chart-container" style="height: 300px;">
                                <canvas id="growthForecastChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="row">
                <div class="col-lg-6">
                    <div class="card  mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">Пользователи по статусам</h6>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered table-sm">
                                    <thead class="thead-light">
                                        <tr>
                                            <th>Статус</th>
                                            <th>Количество</th>
                                            <th>Процент</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($usersByStatus as $status)
                                        <tr>
                                            <td>{{ ucfirst($status->status) }}</td>
                                            <td>{{ $status->count }}</td>
                                            <td>{{ round(($status->count / $usersCount) * 100, 1) }}%</td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="card  mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">Активность за последние 30 дней</h6>
                        </div>
                        <div class="card-body">
                            <div class="row text-center">
                                <div class="col-md-4">
                                    <div class="bg-light rounded p-3 mb-2">
                                        <h4>{{ $newUsersLast30Days }}</h4>
                                        <p class="text-muted mb-0">Новых пользователей</p>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="bg-light rounded p-3 mb-2">
                                        <h4>{{ $newDealsLast30Days }}</h4>
                                        <p class="text-muted mb-0">Новых сделок</p>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="bg-light rounded p-3 mb-2">
                                        <h4>{{ $newMessagesLast30Days }}</h4>
                                        <p class="text-muted mb-0">Новых сообщений</p>
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

<!-- JavaScript для инициализации графиков -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Инициализация Bootstrap tabs
    $('#dashboardTabs a').on('click', function (e) {
        e.preventDefault();
        $(this).tab('show');
    });
    
    // Хранилище для объектов графиков
    const charts = {};
    
    // Система вкладок (она уже реализована через Bootstrap)
    
    // Фильтры периода
    document.querySelectorAll('.btn-group .btn').forEach(button => {
        button.addEventListener('click', function() {
            document.querySelectorAll('.btn-group .btn').forEach(btn => {
                btn.classList.remove('active');
            });
            this.classList.add('active');
            updateDashboardData(this.dataset.period);
        });
    });

    // Обновление данных дашборда
    function updateDashboardData(period = '30days') {
        // Анимация кнопки обновления
        const refreshBtn = document.getElementById('refreshDashboard');
        const originalContent = refreshBtn.innerHTML;
        refreshBtn.innerHTML = '<i class="fas fa-spinner fa-spin fa-sm"></i> Обновление...';
        refreshBtn.disabled = true;
        
        // AJAX запрос к серверу для получения актуальных данных
        fetch(`/admin/analytics/data?period=${period}`)
            .then(response => {
                if (!response.ok) {
                    throw new Error('Ошибка при получении данных');
                }
                return response.json();
            })
            .then(data => {
                // Обновляем графики с новыми данными
                updateCharts(data);
                
                // Восстанавливаем кнопку
                refreshBtn.innerHTML = originalContent;
                refreshBtn.disabled = false;
                
                // Показываем уведомление об успехе
                showToast('Данные успешно обновлены', 'success');
            })
            .catch(error => {
                console.error('Ошибка при обновлении данных:', error);
                refreshBtn.innerHTML = originalContent;
                refreshBtn.disabled = false;
                
                // Показываем уведомление об ошибке
                showToast('Не удалось получить актуальные данные', 'error');
            });
    }

    // Обработчик кнопки обновления
    document.getElementById('refreshDashboard').addEventListener('click', function() {
        const activePeriod = document.querySelector('.btn-group .btn.active').dataset.period;
        updateDashboardData(activePeriod);
    });

    // Инициализация графиков
    initializeCharts();
    
    // Функция для обновления графиков после получения новых данных
    function updateCharts(data) {
        // Обновление KPI карточек
        if (data.kpi) {
            updateKPICards(data.kpi);
        }
        
        // Обновление графика роста пользователей
        if (data.userGrowth && charts.usersGrowthChart) {
            charts.usersGrowthChart.data.labels = data.userGrowth.labels;
            charts.usersGrowthChart.data.datasets[0].data = data.userGrowth.data;
            charts.usersGrowthChart.update();
        }
        
        // Обновление графика роста сделок
        if (data.dealGrowth && charts.dealsGrowthChart) {
            charts.dealsGrowthChart.data.labels = data.dealGrowth.labels;
            charts.dealsGrowthChart.data.datasets[0].data = data.dealGrowth.data;
            charts.dealsGrowthChart.update();
        }
        
        // Обновление прогноза роста
        if (data.forecast && charts.forecastChart) {
            updateForecastChart(charts.forecastChart, data.forecast);
        }
        
        // Если есть данные для статусов сделок - обновляем круговую диаграмму
        if (data.dealStatus && charts.dealStatusChart) {
            charts.dealStatusChart.data.labels = data.dealStatus.labels;
            charts.dealStatusChart.data.datasets[0].data = data.dealStatus.data;
            charts.dealStatusChart.update();
            
            // Обновляем легенду
            createChartLegend('dealStatusLegend', data.dealStatus.labels, charts.dealStatusChart.data.datasets[0].backgroundColor);
        }
        
        // Если есть данные о пользователях - обновляем круговую диаграмму ролей
        if (data.userRoles && charts.userRolesChart) {
            charts.userRolesChart.data.datasets[0].data = data.userRoles.data;
            charts.userRolesChart.update();
        }
    }
    
    // Обновление KPI карточек
    function updateKPICards(kpiData) {
        // Пользователи
        if (kpiData.users) {
            document.querySelector('.col-xl-3:nth-child(1) .h5').textContent = kpiData.users.total;
            const usersTrend = document.querySelector('.col-xl-3:nth-child(1) .text-success');
            usersTrend.innerHTML = `<i class="fas fa-arrow-${kpiData.users.trend_direction} mr-1"></i> ${kpiData.users.trend_value} за ${kpiData.period_text}`;
            usersTrend.className = `small text-${kpiData.users.trend_direction === 'up' ? 'success' : 'danger'}`;
        }
        
        // Сделки
        if (kpiData.deals) {
            document.querySelector('.col-xl-3:nth-child(2) .h5').textContent = kpiData.deals.total;
            const dealsTrend = document.querySelector('.col-xl-3:nth-child(2) .text-success');
            dealsTrend.innerHTML = `<i class="fas fa-arrow-${kpiData.deals.trend_direction} mr-1"></i> ${kpiData.deals.trend_value} за ${kpiData.period_text}`;
            dealsTrend.className = `small text-${kpiData.deals.trend_direction === 'up' ? 'success' : 'danger'}`;
        }
        
        // Общая сумма
        if (kpiData.amount) {
            document.querySelector('.col-xl-3:nth-child(3) .h5').textContent = kpiData.amount.formatted;
            const amountTrend = document.querySelector('.col-xl-3:nth-child(3) .text-success');
            amountTrend.innerHTML = `<i class="fas fa-arrow-${kpiData.amount.trend_direction} mr-1"></i> ${kpiData.amount.trend_percent}% с прошлого месяца`;
            amountTrend.className = `small text-${kpiData.amount.trend_direction === 'up' ? 'success' : 'danger'}`;
        }
        
        // Средний рейтинг
        if (kpiData.rating) {
            document.querySelector('.col-xl-3:nth-child(4) .h5').textContent = kpiData.rating.value;
            const ratingTrend = document.querySelector('.col-xl-3:nth-child(4) .text-success');
            ratingTrend.innerHTML = `<i class="fas fa-arrow-${kpiData.rating.trend_direction} mr-1"></i> ${kpiData.rating.trend_value} с прошлого месяца`;
            ratingTrend.className = `small text-${kpiData.rating.trend_direction === 'up' ? 'success' : 'danger'}`;
        }
    }
    
    // Обновление графика прогноза с корректными историческими данными
    function updateForecastChart(chart, forecastData) {
        // Обновление данных для исторического периода
        chart.data.labels = forecastData.labels;
        
        // Обновление данных для прогноза пользователей
        chart.data.datasets[0].data = forecastData.users.historical.concat(forecastData.users.forecast);
        
        // Обновление данных для прогноза сделок
        chart.data.datasets[1].data = forecastData.deals.historical.concat(forecastData.deals.forecast);
        
        // Обновляем конфигурацию для визуального разделения исторических и прогнозных данных
        chart.update();
    }
    
    // Функция показа уведомлений в Bootstrap стиле
    function showToast(message, type = 'success') {
        const toastId = 'dashboard-toast-' + Date.now();
        const toastHTML = `
            <div id="${toastId}" class="toast" role="alert" aria-live="assertive" aria-atomic="true" data-delay="3000">
                <div class="toast-header bg-${type === 'success' ? 'success' : 'danger'} text-white">
                    <strong class="mr-auto">${type === 'success' ? 'Успешно' : 'Ошибка'}</strong>
                    <button type="button" class="ml-2 mb-1 close" data-dismiss="toast" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="toast-body">
                    ${message}
                </div>
            </div>
        `;
        
        // Добавить контейнер для тостов, если он не существует
        if (!document.getElementById('toast-container')) {
            const toastContainer = document.createElement('div');
            toastContainer.id = 'toast-container';
            toastContainer.className = 'position-fixed bottom-0 right-0 p-3';
            toastContainer.style.zIndex = '5';
            toastContainer.style.right = '0';
            toastContainer.style.bottom = '0';
            document.body.appendChild(toastContainer);
        }
        
        const container = document.getElementById('toast-container');
        container.insertAdjacentHTML('beforeend', toastHTML);
        
        const toast = document.getElementById(toastId);
        $('.toast').toast('show');
        
        // Удалить toast после закрытия
        $(toast).on('hidden.bs.toast', function() {
            this.remove();
        });
    }

    // Функция для инициализации графиков
    function initializeCharts() {
        // Получаем контексты всех графиков
        const charts = {};
        
        // График роста пользователей
        const usersCtx = document.getElementById('usersGrowthChart').getContext('2d');
        charts.usersGrowthChart = new Chart(usersCtx, {
            type: 'line',
            data: {
                labels: {!! json_encode($userGrowthData['labels']) !!},
                datasets: [{
                    label: 'Новые пользователи',
                    data: {!! json_encode($userGrowthData['data']) !!},
                    borderColor: '#4e73df',
                    backgroundColor: 'rgba(78, 115, 223, 0.05)',
                    tension: 0.3,
                    borderWidth: 3,
                    fill: true,
                    pointBackgroundColor: '#4e73df',
                    pointBorderColor: '#fff',
                    pointRadius: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        backgroundColor: 'rgb(255, 255, 255)',
                        bodyColor: '#858796',
                        titleColor: '#6e707e',
                        titleMarginBottom: 10,
                        borderColor: '#dddfeb',
                        borderWidth: 1,
                        padding: 15,
                        displayColors: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            precision: 0
                        }
                    }
                }
            }
        });

        // График роста сделок
        const dealsCtx = document.getElementById('dealsGrowthChart').getContext('2d');
        charts.dealsGrowthChart = new Chart(dealsCtx, {
            type: 'line',
            data: {
                labels: {!! json_encode($dealGrowthData['labels']) !!},
                datasets: [{
                    label: 'Новые сделки',
                    data: {!! json_encode($dealGrowthData['data']) !!},
                    borderColor: '#1cc88a',
                    backgroundColor: 'rgba(28, 200, 138, 0.05)',
                    tension: 0.3,
                    borderWidth: 3,
                    fill: true,
                    pointBackgroundColor: '#1cc88a',
                    pointBorderColor: '#fff',
                    pointRadius: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        backgroundColor: 'rgb(255, 255, 255)',
                        bodyColor: '#858796',
                        titleColor: '#6e707e',
                        titleMarginBottom: 10,
                        borderColor: '#dddfeb',
                        borderWidth: 1,
                        padding: 15,
                        displayColors: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            precision: 0
                        }
                    }
                }
            }
        });

        // Круговая диаграмма пользователей по ролям
        const userRolesCtx = document.getElementById('userRolesChart').getContext('2d');
        const userRoles = {
            labels: ['Клиенты', 'Координаторы', 'Партнеры', 'Архитекторы', 'Дизайнеры', 'Визуализаторы', 'Администраторы'],
            datasets: [{
                data: [
                    {{ $userRoles['client'] ?? $userRoles['user'] ?? 0 }}, 
                    {{ $userRoles['coordinator'] ?? 0 }}, 
                    {{ $userRoles['partner'] ?? 0 }}, 
                    {{ $userRoles['architect'] ?? 0 }}, 
                    {{ $userRoles['designer'] ?? 0 }}, 
                    {{ $userRoles['visualizer'] ?? 0 }}, 
                    {{ $userRoles['admin'] ?? 0 }}
                ],
                backgroundColor: ['#4e73df', '#1cc88a', '#36b9cc', '#f6c23e', '#e74a3b', '#6f42c1', '#fd7e14'],
                hoverBackgroundColor: ['#2e59d9', '#17a673', '#2c9faf', '#f4b619', '#e02d1b', '#5a32a3', '#fd6500'],
                hoverOffset: 5
            }]
        };

        charts.userRolesChart = new Chart(userRolesCtx, {
            type: 'doughnut',
            data: userRoles,
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '70%',
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        backgroundColor: 'rgb(255, 255, 255)',
                        bodyColor: '#858796',
                        titleColor: '#6e707e',
                        titleMarginBottom: 10,
                        borderColor: '#dddfeb',
                        borderWidth: 1,
                        padding: 15,
                        displayColors: false
                    }
                }
            }
        });

        // Создание легенды для диаграммы пользователей
        createChartLegend('userRolesLegend', userRoles.labels, userRoles.datasets[0].backgroundColor);

        // Круговая диаграмма статусов сделок
        const dealStatusCtx = document.getElementById('dealStatusChart').getContext('2d');
        const dealStatusLabels = [];
        const dealStatusData = [];
        const dealStatusColors = [
            '#4e73df', '#1cc88a', '#36b9cc', '#f6c23e', '#e74a3b', 
            '#6f42c1', '#fd7e14', '#20c9a6', '#3a3b45', '#5a5c69'
        ];
        
        @foreach($dealsByStatus as $index => $status)
            dealStatusLabels.push("{{ $status->status }}");
            dealStatusData.push({{ $status->count }});
        @endforeach

        const dealStatus = {
            labels: dealStatusLabels,
            datasets: [{
                data: dealStatusData,
                backgroundColor: dealStatusColors.slice(0, dealStatusLabels.length),
                hoverBackgroundColor: dealStatusColors.slice(0, dealStatusLabels.length).map(color => color),
                hoverOffset: 5
            }]
        };

        charts.dealStatusChart = new Chart(dealStatusCtx, {
            type: 'doughnut',
            data: dealStatus,
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '70%',
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        backgroundColor: 'rgb(255, 255, 255)',
                        bodyColor: '#858796',
                        titleColor: '#6e707e',
                        titleMarginBottom: 10,
                        borderColor: '#dddfeb',
                        borderWidth: 1,
                        padding: 15,
                        displayColors: false
                    }
                }
            }
        });

        // Создание легенды для статусов сделок
        createChartLegend('dealStatusLegend', dealStatus.labels, dealStatus.datasets[0].backgroundColor);

        // Прогноз роста с улучшенным алгоритмом
        const forecastCtx = document.getElementById('growthForecastChart').getContext('2d');
        
        // Создаем массивы с историческими данными 
        const historicalMonths = {!! json_encode($userGrowthData['labels']) !!};
        const historicalUsers = {!! json_encode($userGrowthData['data']) !!};
        const historicalDeals = {!! json_encode($dealGrowthData['data']) !!};
        
        // Добавляем прогноз на основе скользящего среднего и сезонного коэффициента
        const forecastMonths = ['Прогноз 1 мес', 'Прогноз 2 мес', 'Прогноз 3 мес'];
        
        // Улучшенный алгоритм прогнозирования
        function calculateForecast(historicalData) {
            // Если исторических данных меньше 3, используем простое среднее
            if (historicalData.length < 3) {
                const avg = historicalData.reduce((sum, val) => sum + val, 0) / historicalData.length;
                return [
                    Math.max(1, Math.round(avg * 1.05)),
                    Math.max(1, Math.round(avg * 1.1)),
                    Math.max(1, Math.round(avg * 1.15))
                ];
            }
            
            // Скользящее среднее за последние 3 месяца
            const lastValues = historicalData.slice(-3);
            const movingAvg = lastValues.reduce((sum, val) => sum + val, 0) / 3;
            
            // Расчет тренда (наклона кривой)
            const trend = (lastValues[2] - lastValues[0]) / 2;
            
            // Оценка сезонности (простой подход)
            const seasonalIndex = historicalData.length >= 12 ? 
                historicalData[historicalData.length - 12] / movingAvg : 1;
            
            // Прогноз с учетом тренда и сезонности, но не меньше 1
            return [
                Math.max(1, Math.round((movingAvg + trend) * Math.max(0.9, Math.min(1.1, seasonalIndex)))),
                Math.max(1, Math.round((movingAvg + trend * 2) * Math.max(0.85, Math.min(1.15, seasonalIndex)))),
                Math.max(1, Math.round((movingAvg + trend * 3) * Math.max(0.8, Math.min(1.2, seasonalIndex))))
            ];
        }
        
        const forecastUsers = calculateForecast(historicalUsers);
        const forecastDeals = calculateForecast(historicalDeals);
        
        // Объединяем исторические данные и прогноз
        const allMonths = [...historicalMonths, ...forecastMonths];
        const allUsers = [...historicalUsers, ...forecastUsers];
        const allDeals = [...historicalDeals, ...forecastDeals];
        
        // Индекс, разделяющий исторические данные и прогноз
        const separationIndex = historicalMonths.length - 1;
        
        // Создаем график прогноза с визуальным разделением исторических и прогнозных данных
        charts.forecastChart = new Chart(forecastCtx, {
            type: 'line',
            data: {
                labels: allMonths,
                datasets: [
                    {
                        label: 'Пользователи',
                        data: allUsers,
                        borderColor: '#4e73df',
                        backgroundColor: 'rgba(78, 115, 223, 0.05)',
                        tension: 0.3,
                        borderWidth: 2,
                        fill: false,
                        pointBackgroundColor: function(context) {
                            const index = context.dataIndex;
                            return index >= historicalMonths.length ? '#4e73df' : 'rgba(78, 115, 223, 0.8)';
                        },
                        pointBorderColor: '#fff',
                        pointRadius: function(context) {
                            const index = context.dataIndex;
                            return index >= historicalMonths.length ? 5 : 3;
                        },
                        pointStyle: function(context) {
                            const index = context.dataIndex;
                            return index >= historicalMonths.length ? 'rectRot' : 'circle';
                        },
                        segment: {
                            borderDash: ctx => ctx.p1DataIndex >= separationIndex ? [5, 5] : undefined
                        }
                    },
                    {
                        label: 'Сделки',
                        data: allDeals,
                        borderColor: '#1cc88a',
                        backgroundColor: 'rgba(28, 200, 138, 0.05)',
                        tension: 0.3,
                        borderWidth: 2,
                        fill: false,
                        pointBackgroundColor: function(context) {
                            const index = context.dataIndex;
                            return index >= historicalMonths.length ? '#1cc88a' : 'rgba(28, 200, 138, 0.8)';
                        },
                        pointBorderColor: '#fff',
                        pointRadius: function(context) {
                            const index = context.dataIndex;
                            return index >= historicalMonths.length ? 5 : 3;
                        },
                        pointStyle: function(context) {
                            const index = context.dataIndex;
                            return index >= historicalMonths.length ? 'rectRot' : 'circle';
                        },
                        segment: {
                            borderDash: ctx => ctx.p1DataIndex >= separationIndex ? [5, 5] : undefined
                        }
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    tooltip: {
                        backgroundColor: 'rgb(255, 255, 255)',
                        bodyColor: '#858796',
                        titleColor: '#6e707e',
                        titleMarginBottom: 10,
                        borderColor: '#dddfeb',
                        borderWidth: 1,
                        padding: 15,
                        displayColors: true,
                        callbacks: {
                            title: function(tooltipItems) {
                                const index = tooltipItems[0].dataIndex;
                                return index >= historicalMonths.length ? 
                                    `${tooltipItems[0].label} (прогноз)` : tooltipItems[0].label;
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            precision: 0
                        }
                    }
                }
            }
        });

        // График статистики брифов
        const briefsCtx = document.getElementById('briefsChart').getContext('2d');
        charts.briefsChart = new Chart(briefsCtx, {
            type: 'pie',
            data: {
                labels: ['Общие брифы', 'Коммерческие брифы'],
                datasets: [{
                    data: [{{ $commonsCount }}, {{ $commercialsCount }}],
                    backgroundColor: ['#4e73df', '#1cc88a'],
                    hoverBackgroundColor: ['#2e59d9', '#17a673'],
                    hoverOffset: 5
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            generateLabels: function(chart) {
                                const data = chart.data;
                                if (data.labels.length && data.datasets.length) {
                                    return data.labels.map((label, i) => {
                                        const dataset = data.datasets[0];
                                        const value = dataset.data[i];
                                        const backgroundColor = dataset.backgroundColor[i];

                                        return {
                                            text: `${label}: ${value}`,
                                            fillStyle: backgroundColor,
                                            strokeStyle: '#fff',
                                            lineWidth: 2,
                                            index: i
                                        };
                                    });
                                }
                                return [];
                            }
                        }
                    },
                    tooltip: {
                        backgroundColor: 'rgb(255, 255, 255)',
                        bodyColor: '#858796',
                        titleColor: '#6e707e',
                        titleMarginBottom: 10,
                        borderColor: '#dddfeb',
                        borderWidth: 1,
                        padding: 15,
                        displayColors: false
                    }
                }
            }
        });
        
        // Возвращаем объект со всеми графиками для дальнейшего обновления
        return charts;
    }

    // Функция для создания легенды для графиков в стиле Bootstrap
    function createChartLegend(containerId, labels, colors) {
        const container = document.getElementById(containerId);
        if (!container) return;
        
        container.innerHTML = '';
        
        const legendList = document.createElement('ul');
        legendList.className = 'list-unstyled mb-0';
        
        labels.forEach((label, index) => {
            if (index >= colors.length) return; // Защита от ошибок индексации
            
            const legendItem = document.createElement('li');
            legendItem.className = 'd-flex align-items-center mb-2';
            
            const colorBox = document.createElement('span');
            colorBox.className = 'mr-2';
            colorBox.style.width = '12px';
            colorBox.style.height = '12px';
            colorBox.style.display = 'inline-block';
            colorBox.style.backgroundColor = colors[index];
            colorBox.style.borderRadius = '2px';
            
            const labelText = document.createElement('span');
            labelText.className = 'small';
            labelText.textContent = label;
            
            legendItem.appendChild(colorBox);
            legendItem.appendChild(labelText);
            legendList.appendChild(legendItem);
        });
        
        container.appendChild(legendList);
    }
    
    // Адаптивность графиков для мобильных устройств
    function adjustChartSizesForMobile() {
        const isMobile = window.innerWidth < 768;
        const chartContainers = document.querySelectorAll('.chart-container');
        
        chartContainers.forEach(container => {
            if (isMobile) {
                container.style.height = '250px';
            } else {
                // Восстанавливаем исходную высоту
                if (container.dataset.originalHeight) {
                    container.style.height = container.dataset.originalHeight;
                }
            }
        });
        
        // Обновляем все графики после изменения размера контейнеров
        for (const key in charts) {
            if (charts[key] && typeof charts[key].update === 'function') {
                charts[key].update();
            }
        }
    }
    
    // Сохраняем исходные размеры для восстановления
    document.querySelectorAll('.chart-container').forEach(container => {
        container.dataset.originalHeight = container.style.height;
    });
    
    // Вызываем функцию при загрузке и изменении размера окна
    adjustChartSizesForMobile();
    window.addEventListener('resize', adjustChartSizesForMobile);
    
    // Оптимизация для мобильных устройств
    function optimizeForMobile() {
        const isMobile = window.innerWidth < 768;
        
        // Адаптация легенд графиков
        if (isMobile) {
            document.querySelectorAll('.chart-legend').forEach(legend => {
                legend.classList.add('d-flex', 'flex-wrap', 'justify-content-center');
            });
            
            // Изменение отображения кнопок периодов для мобильных
            document.querySelector('.period-filter').classList.add('btn-group-sm');
        } else {
            document.querySelectorAll('.chart-legend').forEach(legend => {
                legend.classList.remove('d-flex', 'flex-wrap', 'justify-content-center');
            });
            
            document.querySelector('.period-filter').classList.remove('btn-group-sm');
        }
    }
    
    // Инициализация и обработка изменения размера окна
    optimizeForMobile();
    window.addEventListener('resize', optimizeForMobile);
});
</script>

<!-- Стили для адаптивности -->
<style>
    /* Адаптация для мобильных устройств */
    @media (max-width: 767.98px) {
        /* Стили для заголовка страницы */
        .d-sm-flex {
            flex-direction: column;
            align-items: flex-start !important;
        }
        
        /* Адаптация карточек статистики */
        .col-xl-3 {
            margin-bottom: 1rem;
        }
        
        /* Адаптация табов */
        .dashboard-tabs {
            white-space: nowrap;
            overflow-x: auto;
        }
        
        .dashboard-tabs::-webkit-scrollbar {
            height: 4px;
        }
        
        .dashboard-tabs::-webkit-scrollbar-thumb {
            background-color: rgba(0,0,0,.2);
            border-radius: 4px;
        }
        
        .nav-tabs .nav-link {
            font-size: 14px;
            padding: .5rem .75rem;
        }
        
        /* Адаптация таблиц */
        .table-responsive {
            border: 0;
        }
        
        /* Уменьшаем размеры текста в карточках */
        .card-body .text-xs {
            font-size: 11px;
        }
        
        .card-body .h5 {
            font-size: 16px;
        }
        
        .card-body .small {
            font-size: 11px;
        }
        
        /* Адаптация для кнопок фильтра периода */
        .period-filter {
            flex-wrap: wrap;
            width: 100%;
            margin-top: 8px;
        }
        
        .period-filter .btn {
            flex: 1;
            padding: .25rem .5rem;
            font-size: 12px;
        }
        
        /* Вертикальное расположение графиков на мобильных */
        .col-xl-6, .col-lg-6 {
            flex: 0 0 100%;
            max-width: 100%;
        }
        
        /* Оптимизация таблиц для мобильных */
        .table th, .table td {
            padding: .5rem;
            font-size: 12px;
        }
        
        .badge {
            font-size: 11px;
            padding: 3px 6px;
        }
        
        /* Оптимизация легенд для мобильных */
        .chart-legend .list-unstyled {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
        }
        
        .chart-legend .list-unstyled li {
            margin-right: 15px;
            margin-bottom: 8px;
        }
    }
    
    /* Для средних устройств */
    @media (min-width: 768px) and (max-width: 991.98px) {
        .col-xl-6 {
            flex: 0 0 100%;
            max-width: 100%;
        }
        
        .col-xl-3 {
            flex: 0 0 50%;
            max-width: 50%;
        }
    }
    
    /* Стили для корректного отображения графиков */
    .chart-container {
        position: relative;
        margin: auto;
        width: 100%;
    }
    
    /* Общие улучшения для всех размеров экранов */
    .table-responsive {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }
    
    /* Улучшения для визуальной иерархии */
    .card-header h6 {
        margin-bottom: 0;
    }
</style>

<!-- Проверка наличия jQuery и Bootstrap JS -->
<script>
    // Проверка загрузки jQuery
    if (typeof jQuery === 'undefined') {
        console.error('jQuery не загружен! Вкладки Bootstrap не будут работать');
        document.write('<script src="https://code.jquery.com/jquery-3.6.0.min.js" integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=" crossorigin="anonymous"><\/script>');
    }
    
    // Проверка загрузки Bootstrap JS
    if (typeof bootstrap === 'undefined' && typeof $().tab !== 'function') {
        console.error('Bootstrap JS не загружен! Добавляем его для работы вкладок');
        document.write('<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js" integrity="sha384-Piv4xVNRyMGpqkS2by6br4gNJ7DXjqk09RmUpJ8jgGtD7zP9yug3goQfGII0yAns" crossorigin="anonymous"><\/script>');
    }
    
    // Альтернативный обработчик вкладок (на случай проблем с Bootstrap)
    document.addEventListener('DOMContentLoaded', function() {
        const tabLinks = document.querySelectorAll('.nav-tabs .nav-link');
        
        tabLinks.forEach(tab => {
            tab.addEventListener('click', function(e) {
                e.preventDefault();
                
                // Если Bootstrap Tab доступен, пусть он обрабатывает клики
                if (typeof $().tab === 'function') return;
                
                // Ручное переключение вкладок если Bootstrap Tab не доступен
                const targetId = this.getAttribute('href');
                
                // Скрыть все вкладки и убрать активные классы
                document.querySelectorAll('.tab-pane').forEach(pane => {
                    pane.classList.remove('show', 'active');
                });
                
                document.querySelectorAll('.nav-tabs .nav-link').forEach(link => {
                    link.classList.remove('active');
                });
                
                // Показать выбранную вкладку и добавить активный класс
                this.classList.add('active');
                document.querySelector(targetId).classList.add('show', 'active');
            });
        });
    });
</script>

