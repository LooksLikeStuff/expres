@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="main__flex">
        <div class="main__ponel">
            @include('layouts/ponel')
        </div>
        <div class="main__module">
            @include('layouts/header')
            
            <div class="container">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h1 class="text-danger">
                        <i class="fas fa-shield-alt"></i> {{ $title_site }}
                    </h1>
                    <a href="{{ route('deal.cardinator') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Вернуться к сделкам
                    </a>
                </div>

                <!-- Статистика -->
                <div class="row mb-4">
                    <div class="col-md-2">
                        <div class="card bg-primary text-white">
                            <div class="card-body text-center">
                                <h2 class="mb-0">{{ $stats['total_logs'] }}</h2>
                                <p class="mb-0">Всего записей</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="card bg-success text-white">
                            <div class="card-body text-center">
                                <h2 class="mb-0">{{ $stats['today_logs'] }}</h2>
                                <p class="mb-0">Сегодня</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="card bg-warning text-white">
                            <div class="card-body text-center">
                                <h2 class="mb-0">{{ $stats['week_logs'] }}</h2>
                                <p class="mb-0">За неделю</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="card bg-info text-white">
                            <div class="card-body text-center">
                                <h2 class="mb-0">{{ $stats['month_logs'] }}</h2>
                                <p class="mb-0">За месяц</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="card bg-danger text-white">
                            <div class="card-body text-center">
                                <h2 class="mb-0">{{ $stats['delete_actions'] ?? 0 }}</h2>
                                <p class="mb-0">Удалений</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="card bg-success text-white">
                            <div class="card-body text-center">
                                <h2 class="mb-0">{{ $stats['restore_actions'] ?? 0 }}</h2>
                                <p class="mb-0">Восстановлений</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Фильтры -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-filter"></i> Фильтры и поиск
                            <button class="btn btn-sm btn-outline-secondary float-end" type="button" data-bs-toggle="collapse" data-bs-target="#filterPanel" aria-expanded="false">
                                Показать/скрыть фильтры
                            </button>
                        </h5>
                    </div>
                    <div class="collapse show" id="filterPanel">
                        <div class="card-body">
                            <form method="GET" action="{{ route('deal.global_logs') }}">
                                <div class="row">
                                    <div class="col-md-4 mb-3">
                                        <label for="search" class="form-label">Поиск</label>
                                        <input type="text" name="search" id="search" class="form-control" 
                                               value="{{ $request->search }}" 
                                               placeholder="По имени, ID сделки, телефону...">
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label for="action_type" class="form-label">Тип действия</label>
                                        <select name="action_type" id="action_type" class="form-control">
                                            <option value="">Все действия</option>
                                            <option value="create" {{ $request->action_type == 'create' ? 'selected' : '' }}>Создание сделки</option>
                                            <option value="update" {{ $request->action_type == 'update' ? 'selected' : '' }}>Редактирование</option>
                                            <option value="delete" {{ $request->action_type == 'delete' ? 'selected' : '' }}>Удаление</option>
                                            <option value="restore" {{ $request->action_type == 'restore' ? 'selected' : '' }}>Восстановление</option>
                                            <option value="status_change" {{ $request->action_type == 'status_change' ? 'selected' : '' }}>Изменение статуса</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label for="user_id" class="form-label">Пользователь</label>
                                        <select name="user_id" id="user_id" class="form-control">
                                            <option value="">Все пользователи</option>
                                            @foreach($users as $user)
                                                <option value="{{ $user->id }}" {{ $request->user_id == $user->id ? 'selected' : '' }}>
                                                    {{ $user->name }} ({{ $user->status }})
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-3 mb-3">
                                        <label for="deal_id" class="form-label">ID сделки</label>
                                        <input type="number" name="deal_id" id="deal_id" class="form-control" 
                                               value="{{ $request->deal_id }}" placeholder="Номер сделки">
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <label for="date_from" class="form-label">Дата с</label>
                                        <input type="date" name="date_from" id="date_from" class="form-control" 
                                               value="{{ $request->date_from }}">
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <label for="date_to" class="form-label">Дата по</label>
                                        <input type="date" name="date_to" id="date_to" class="form-control" 
                                               value="{{ $request->date_to }}">
                                    </div>
                                    <div class="col-md-3 mb-3 d-flex align-items-end">
                                        <button type="submit" class="btn btn-primary me-2">
                                            <i class="fas fa-search"></i> Найти
                                        </button>
                                        <a href="{{ route('deal.global_logs') }}" class="btn btn-secondary">
                                            <i class="fas fa-times"></i> Сбросить
                                        </a>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Таблица логов -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-list"></i> Результаты поиска 
                            <span class="badge bg-secondary">{{ $logs->total() }} записей</span>
                        </h5>
                    </div>
                    <div class="card-body p-0">
                        @if($logs->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-striped table-hover mb-0">
                                    <thead class="table-dark">
                                        <tr>
                                            <th>ID</th>
                                            <th>Дата/время</th>
                                            <th>Пользователь</th>
                                            <th>Сделка</th>
                                            <th>Тип действия</th>
                                            <th>Изменения</th>
                                            <th>IP/User Agent</th>
                                            <th>Действия</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($logs as $log)
                                        <tr>
                                            <td><span class="badge bg-primary">#{{ $log->id }}</span></td>
                                            <td>
                                                <strong>{{ $log->created_at->format('d.m.Y') }}</strong><br>
                                                <small class="text-muted">{{ $log->created_at->format('H:i:s') }}</small>
                                            </td>
                                            <td>
                                                @if($log->user)
                                                    <strong>{{ $log->user->name }}</strong><br>
                                                    <span class="badge bg-info">{{ $log->user->status }}</span>
                                                @else
                                                    <em class="text-muted">{{ $log->user_name ?: 'Неизвестно' }}</em>
                                                @endif
                                            </td>
                                            <td>
                                                @if($log->deal)
                                                    <a href="{{ route('deal.change_logs.deal', $log->deal->id) }}" class="text-decoration-none">
                                                        <strong>#{{ $log->deal->id }}</strong>
                                                    </a><br>
                                                    <small class="text-muted">{{ Str::limit($log->deal->project_name ?? $log->deal->client_name ?? 'Без названия', 30) }}</small>
                                                @else
                                                    <span class="text-danger">
                                                        <i class="fas fa-trash"></i> Сделка #{{ $log->deal_id }} удалена
                                                    </span>
                                                @endif
                                            </td>
                                            <td>
                                                @php
                                                    $actionType = $log->action_type ?? 'update';
                                                    $actionBadge = 'warning';
                                                    $actionIcon = 'edit';
                                                    $actionText = 'Редактирование';
                                                    
                                                    switch ($actionType) {
                                                        case 'create':
                                                            $actionBadge = 'success';
                                                            $actionIcon = 'plus';
                                                            $actionText = 'Создание';
                                                            break;
                                                        case 'delete':
                                                            $actionBadge = 'danger';
                                                            $actionIcon = 'trash';
                                                            $actionText = 'Удаление';
                                                            break;
                                                        case 'restore':
                                                            $actionBadge = 'success';
                                                            $actionIcon = 'undo';
                                                            $actionText = 'Восстановление';
                                                            break;
                                                        case 'status_change':
                                                            $actionBadge = 'info';
                                                            $actionIcon = 'exchange-alt';
                                                            $actionText = 'Изменение статуса';
                                                            break;
                                                        case 'update':
                                                        default:
                                                            $actionBadge = 'warning';
                                                            $actionIcon = 'edit';
                                                            $actionText = 'Редактирование';
                                                            break;
                                                    }
                                                @endphp
                                                <span class="badge bg-{{ $actionBadge }}">
                                                    <i class="fas fa-{{ $actionIcon }}"></i> {{ $actionText }}
                                                </span>
                                                @if($log->description)
                                                    <br><small class="text-muted">{{ Str::limit($log->description, 50) }}</small>
                                                @endif
                                            </td>
                                            <td>
                                                <button class="btn btn-sm btn-outline-secondary" type="button" 
                                                        data-bs-toggle="collapse" data-bs-target="#changes-{{ $log->id }}" 
                                                        aria-expanded="false">
                                                    <i class="fas fa-eye"></i> Показать изменения
                                                </button>
                                                <div class="collapse mt-2" id="changes-{{ $log->id }}">
                                                    <div class="card card-body small">
                                                        @if($log->changes && count($log->changes) > 0)
                                                            @foreach($log->changes as $field => $change)
                                                                <div class="mb-2">
                                                                    <strong class="text-primary">{{ $field }}:</strong><br>
                                                                    @if(isset($change['old']))
                                                                        <span class="text-muted">Было:</span> 
                                                                        <span class="text-decoration-line-through">{{ is_array($change['old']) ? json_encode($change['old'], JSON_UNESCAPED_UNICODE) : ($change['old'] ?: 'пусто') }}</span><br>
                                                                    @endif
                                                                    @if(isset($change['new']))
                                                                        <span class="text-muted">Стало:</span> 
                                                                        <span class="text-success fw-bold">{{ is_array($change['new']) ? json_encode($change['new'], JSON_UNESCAPED_UNICODE) : ($change['new'] ?: 'пусто') }}</span>
                                                                    @endif
                                                                </div>
                                                            @endforeach
                                                        @else
                                                            <em class="text-muted">Нет данных об изменениях</em>
                                                        @endif
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                @if($log->ip_address)
                                                    <strong class="text-primary">IP:</strong> {{ $log->ip_address }}<br>
                                                @endif
                                                @if($log->user_agent)
                                                    <strong class="text-secondary">UA:</strong> 
                                                    <small class="text-muted" title="{{ $log->user_agent }}">
                                                        {{ Str::limit($log->user_agent, 30) }}
                                                    </small>
                                                @endif
                                                @if(!$log->ip_address && !$log->user_agent)
                                                    <span class="text-muted">Не записано</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($log->deal)
                                                    <a href="{{ route('deal.change_logs.deal', $log->deal->id) }}" 
                                                       class="btn btn-sm btn-outline-primary" title="Все логи этой сделки">
                                                        <i class="fas fa-list"></i>
                                                    </a>
                                                @else
                                                    @if($log->action_type == 'delete')
                                                        <!-- Кнопка восстановления сделки для админов -->
                                                        <form method="POST" action="{{ route('deal.restore', $log->deal_id) }}" 
                                                              style="display: inline;" 
                                                              onsubmit="return confirm('Вы уверены, что хотите восстановить сделку #{{ $log->deal_id }}? Все данные сделки будут восстановлены.')">
                                                            @csrf
                                                            <button type="submit" class="btn btn-sm btn-outline-success" 
                                                                    title="Восстановить удаленную сделку #{{ $log->deal_id }}">
                                                                <i class="fas fa-undo"></i> Восстановить
                                                            </button>
                                                        </form>
                                                    @endif
                                                @endif
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            
                            <!-- Пагинация -->
                            <div class="d-flex justify-content-center mt-3">
                                {{ $logs->links() }}
                            </div>
                        @else
                            <div class="text-center py-5">
                                <i class="fas fa-search fa-3x text-muted mb-3"></i>
                                <h5 class="text-muted">Логи не найдены</h5>
                                <p class="text-muted">Попробуйте изменить параметры поиска</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.card {
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    border: 1px solid rgba(0, 0, 0, 0.125);
}

.table-responsive {
    max-height: 600px;
    overflow-y: auto;
}

.table th {
    position: sticky;
    top: 0;
    z-index: 10;
}

.badge {
    font-size: 0.75em;
}

.collapse .card {
    border: 1px solid #dee2e6;
    background-color: #f8f9fa;
}

.text-decoration-line-through {
    text-decoration: line-through;
    opacity: 0.7;
}

.fw-bold {
    font-weight: bold !important;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Инициализация всплывающих подсказок
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
    
    // Автоматическая отправка формы при изменении типа действия
    document.getElementById('action_type').addEventListener('change', function() {
        this.form.submit();
    });
    
    // Автоматическая отправка формы при изменении пользователя
    document.getElementById('user_id').addEventListener('change', function() {
        this.form.submit();
    });
});
</script>
@endsection
