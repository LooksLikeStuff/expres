@extends('layouts.admin')
@section('content')
<div class="container">
    <div class="main__flex">
        <div class="main__ponel">
            @include('layouts/ponel')
        </div>
        <div class="main__module">
            @include('layouts/header')
            
            <div class="admin-dashboard">
                <h1 class="page-title">Панель управления</h1>
                
                <div class="row">
                    <!-- Карточки KPI -->
                    <div class="col-md-3">
                        <div class="admin-card kpi-card">
                            <div class="kpi-icon">
                                <i class="fas fa-users"></i>
                            </div>
                            <h3 class="kpi-value">{{ $usersCount }}</h3>
                            <div class="kpi-label">Пользователей</div>
                            <div class="kpi-trend trend-up">
                                <i class="fas fa-arrow-up"></i> {{ $newUsers }} за неделю
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-3">
                        <div class="admin-card kpi-card">
                            <div class="kpi-icon">
                                <i class="fas fa-handshake"></i>
                            </div>
                            <h3 class="kpi-value">{{ $dealsCount }}</h3>
                            <div class="kpi-label">Сделок</div>
                            <div class="kpi-trend trend-up">
                                <i class="fas fa-arrow-up"></i> {{ $newDeals }} за неделю
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-3">
                        <div class="admin-card kpi-card">
                            <div class="kpi-icon">
                                <i class="fas fa-file-alt"></i>
                            </div>
                            <h3 class="kpi-value">{{ $briefsCount }}</h3>
                            <div class="kpi-label">Брифов</div>
                            <div class="kpi-trend trend-up">
                                <i class="fas fa-arrow-up"></i> {{ $newBriefs }} за неделю
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-3">
                        <div class="admin-card kpi-card">
                            <div class="kpi-icon">
                                <i class="fas fa-ruble-sign"></i>
                            </div>
                            <h3 class="kpi-value">{{ number_format($totalRevenue, 0, '.', ' ') }}</h3>
                            <div class="kpi-label">Выручка</div>
                            <div class="kpi-trend trend-up">
                                <i class="fas fa-arrow-up"></i> {{ number_format($revenueGrowth, 1) }}% за месяц
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Недавние пользователи -->
                <div class="admin-card">
                    <div class="admin-card-header">
                        <h2>Недавно зарегистрированные пользователи</h2>
                        <div class="header-actions">
                            <a href="{{ route('admin.users') }}" class="btn btn-primary btn-sm">
                                <i class="fas fa-users"></i> Все пользователи
                            </a>
                        </div>
                    </div>
                    <div class="admin-card-body">
                        <div class="table-responsive">
                            <table class="admin-table table-striped">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Имя</th>
                                        <th>Email</th>
                                        <th>Статус</th>
                                        <th>Дата регистрации</th>
                                        <th>Действия</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($recentUsers as $user)
                                        <tr>
                                            <td>{{ $user->id }}</td>
                                            <td>{{ $user->name }}</td>
                                            <td>{{ $user->email }}</td>
                                            <td>
                                                @php
                                                    $statusClass = [
                                                        'user' => 'badge-primary',
                                                        'admin' => 'badge-danger',
                                                        'coordinator' => 'badge-success',
                                                        'partner' => 'badge-info',
                                                        'architect' => 'badge-warning',
                                                        'designer' => 'badge-secondary',
                                                        'visualizer' => 'badge-dark'
                                                    ][$user->status] ?? 'badge-secondary';
                                                @endphp
                                                <span class="badge {{ $statusClass }}">{{ ucfirst($user->status) }}</span>
                                            </td>
                                            <td>{{ $user->created_at->format('d.m.Y H:i') }}</td>
                                            <td class="actions">
                                                <a href="{{ route('user.briefs', $user->id) }}" class="btn btn-sm btn-outline-primary" title="Брифы пользователя">
                                                    <i class="fas fa-file-alt"></i>
                                                </a>
                                                <button class="btn btn-sm btn-outline-danger" title="Удалить пользователя" onclick="deleteUser({{ $user->id }})">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                
                <!-- Недавние сделки -->
                <div class="admin-card">
                    <div class="admin-card-header">
                        <h2>Недавние сделки</h2>
                        <div class="header-actions">
                            <a href="{{ route('admin.deals') }}" class="btn btn-primary btn-sm">
                                <i class="fas fa-handshake"></i> Все сделки
                            </a>
                        </div>
                    </div>
                    <div class="admin-card-body">
                        <div class="table-responsive">
                            <table class="admin-table table-striped">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Название</th>
                                        <th>Клиент</th>
                                        <th>Сумма</th>
                                        <th>Статус</th>
                                        <th>Дата создания</th>
                                        <th>Действия</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($recentDeals as $deal)
                                        <tr>
                                            <td>{{ $deal->id }}</td>
                                            <td>{{ $deal->title }}</td>
                                            <td>{{ $deal->client->name ?? 'Клиент удален' }}</td>
                                            <td>{{ number_format($deal->total_sum, 0, '.', ' ') }} ₽</td>
                                            <td>
                                                <span class="badge badge-{{ $deal->status == 'Завершен' ? 'success' : 'info' }}">
                                                    {{ $deal->status }}
                                                </span>
                                            </td>
                                            <td>{{ $deal->created_at->format('d.m.Y H:i') }}</td>
                                            <td class="actions">
                                                <a href="{{ route('admin.deals.view', $deal->id) }}" class="btn btn-sm btn-outline-primary" title="Просмотр сделки">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="{{ route('admin.deals.edit', $deal->id) }}" class="btn btn-sm btn-outline-secondary" title="Редактировать сделку">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- JavaScript для обработки действий -->
<script>
function deleteUser(userId) {
    // Реализация функции удаления пользователя через модальное окно
    if (confirm('Вы действительно хотите удалить этого пользователя?')) {
        $.ajax({
            url: `/admin/users/${userId}`,
            type: 'DELETE',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.success) {
                    showNotification('success', 'Пользователь успешно удален');
                    setTimeout(() => window.location.reload(), 1000);
                } else {
                    showNotification('error', 'Произошла ошибка при удалении пользователя');
                }
            },
            error: function() {
                showNotification('error', 'Произошла ошибка при удалении пользователя');
            }
        });
    }
}

function showNotification(type, message) {
    const notificationId = 'notification-' + Date.now();
    const notificationClass = type === 'success' ? 'success' : 'error';
    
    const notification = `
        <div id="${notificationId}" class="notification ${notificationClass}">
            <div class="notification-content">
                <span class="notification-message">${message}</span>
            </div>
        </div>
    `;
    
    $('body').append(notification);
    
    setTimeout(() => {
        $(`#${notificationId}`).addClass('show');
    }, 100);
    
    setTimeout(() => {
        $(`#${notificationId}`).removeClass('show');
        setTimeout(() => {
            $(`#${notificationId}`).remove();
        }, 300);
    }, 5000);
}
</script>

<style>
/* Стили для карточек KPI */
.kpi-card {
    text-align: center;
    padding: var(--p20);
    border-radius: var(--radius);
    margin-bottom: var(--m20);
    transition: transform 0.3s, box-shadow 0.3s;
}

.kpi-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 20px rgba(0,0,0,0.1);
}

.kpi-icon {
    font-size: 40px;
    margin-bottom: 15px;
    color: var(--bluetext);
}

.kpi-value {
    font-size: 32px;
    font-weight: 700;
    margin: 0;
    color: var(--black);
}

.kpi-label {
    font-size: 16px;
    color: var(--ser);
    margin-bottom: 10px;
}

.kpi-trend {
    font-size: 14px;
    font-weight: 500;
}

.trend-up {
    color: #28a745;
}

.trend-down {
    color: #dc3545;
}
</style>
@endsection
