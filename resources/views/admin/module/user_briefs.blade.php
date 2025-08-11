<div class="container-fluid py-4">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Брифы пользователя: 
            <a href="{{ route('profile.view', $user->id) }}">{{ $user->name }}</a>
        </h1>
        <a href="{{ route('admin.users') }}" class="btn btn-sm btn-outline-primary">
            <i class="fas fa-arrow-left mr-1"></i> Назад к списку пользователей
        </a>
    </div>

    <div class="row">
        <!-- Информация о пользователе -->
        <div class="col-lg-4 mb-4">
            <div class="card">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Информация о пользователе</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <strong>Имя:</strong> 
                        <a href="{{ route('profile.view', $user->id) }}">{{ $user->name }}</a>
                    </div>
                    <div class="mb-3">
                        <strong>Email:</strong> {{ $user->email }}
                    </div>
                    @if($user->phone)
                    <div class="mb-3">
                        <strong>Телефон:</strong> {{ $user->phone }}
                    </div>
                    @endif
                    <div class="mb-3">
                        <strong>Роль:</strong> {{ $user->role ?? 'Пользователь' }}
                    </div>
                    <div class="mb-3">
                        <strong>Дата регистрации:</strong> {{ $user->created_at->format('d.m.Y H:i') }}
                    </div>
                </div>
            </div>
        </div>

        <!-- Статистика брифов -->
        <div class="col-lg-8 mb-4">
            <div class="row">
                <div class="col-md-6 mb-4">
                    <div class="card border-left-primary h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                        Общие брифы</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $commonBriefs->count() }}</div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-file-alt fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-6 mb-4">
                    <div class="card border-left-success h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                        Коммерческие брифы</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $commercialBriefs->count() }}</div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-file-invoice fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Общие брифы -->
    <div class="card mb-4">
        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
            <h6 class="m-0 font-weight-bold text-primary">Общие брифы</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="commonBriefsTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Название</th>
                            <th>Статус</th>
                            <th>Создан</th>
                            <th>Обновлен</th>
                            <th>Действия</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if($commonBriefs->count() > 0)
                            @foreach($commonBriefs as $brief)
                            <tr>
                                <td>{{ $brief->id }}</td>
                                <td>{{ $brief->title ?? 'Без названия' }}</td>
                                <td>
                                    @if($brief->status == 'Активный')
                                        <span class="badge badge-primary">Активный</span>
                                    @elseif($brief->status == 'Завершенный' || $brief->status == 'completed')
                                        <span class="badge badge-success">Завершенный</span>
                                    @else
                                        <span class="badge badge-secondary">{{ $brief->status }}</span>
                                    @endif
                                </td>
                                <td>{{ $brief->created_at->format('d.m.Y H:i') }}</td>
                                <td>{{ $brief->updated_at->format('d.m.Y H:i') }}</td>
                                <td>
                                    <div class="btn-group">
                                     
                                        <button type="button" class="btn btn-sm btn-danger delete-brief" 
                                                data-brief-id="{{ $brief->id }}" 
                                                data-brief-type="common"
                                                data-brief-name="{{ $brief->title ?? 'Бриф #'.$brief->id }}">
                                            <i class="fas fa-trash"></i> Удалить
                                        </button>
                                
                                        <a href="{{ route('admin.brief.common.edit', $brief->id) }}" class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="{{ route('common.show', $brief->id) }}" target="_blank" class="btn btn-sm btn-outline-info">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        @else
                            <tr>
                                <td colspan="6" class="text-center">Брифы отсутствуют</td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Коммерческие брифы -->
    <div class="card mb-4">
        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
            <h6 class="m-0 font-weight-bold text-primary">Коммерческие брифы</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="commercialBriefsTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Название</th>
                            <th>Статус</th>
                            <th>Бюджет</th>
                            <th>Создан</th>
                            <th>Обновлен</th>
                            <th>Действия</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if($commercialBriefs->count() > 0)
                            @foreach($commercialBriefs as $brief)
                            <tr>
                                <td>{{ $brief->id }}</td>
                                <td>{{ $brief->title ?? 'Без названия' }}</td>
                                <td>
                                    @if($brief->status == 'Активный')
                                        <span class="badge badge-primary">Активный</span>
                                    @elseif($brief->status == 'Завершенный' || $brief->status == 'completed')
                                        <span class="badge badge-success">Завершенный</span>
                                    @else
                                        <span class="badge badge-secondary">{{ $brief->status }}</span>
                                    @endif
                                </td>
                                <td>{{ number_format($brief->price, 0, '.', ' ') }} ₽</td>
                                <td>{{ $brief->created_at->format('d.m.Y H:i') }}</td>
                                <td>{{ $brief->updated_at->format('d.m.Y H:i') }}</td>
                                <td>
                                    <div class="btn-group">
                                       
                                        <button type="button" class="btn btn-sm btn-danger delete-brief" 
                                                data-brief-id="{{ $brief->id }}" 
                                                data-brief-type="commercial"
                                                data-brief-name="{{ $brief->title ?? 'Бриф #'.$brief->id }}">
                                            <i class="fas fa-trash"></i> Удалить
                                        </button>
                                        
                                
                                        <a href="{{ route('admin.brief.commercial.edit', $brief->id) }}" class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="{{ route('commercial.show', $brief->id) }}" target="_blank" class="btn btn-sm btn-outline-info">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        @else
                            <tr>
                                <td colspan="7" class="text-center">Нет коммерческих брифов</td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Инициализация тултипов Bootstrap
    $('[data-toggle="tooltip"]').tooltip();
    
    // Безопасная инициализация DataTables, предотвращающая повторную инициализацию
    function initializeDataTable(tableSelector, options) {
        const table = $(tableSelector);
        
        // Проверка существования таблицы
        if (table.length === 0) {
            return;
        }
        
        // Если таблица уже инициализирована как DataTable, уничтожаем предыдущий экземпляр
        if ($.fn.DataTable.isDataTable(table)) {
            table.DataTable().destroy();
        }
        
        // Проверка наличия строк в таблице
        const rowCount = table.find('tbody tr').length;
        if (rowCount === 0) {
            return;
        }
        
        // Инициализация с переданными опциями
        const defaultOptions = {
            "language": {
                "url": "//cdn.datatables.net/plug-ins/1.10.24/i18n/Russian.json"
            },
            "order": [[0, "desc"]],
            "pageLength": 10,
            "responsive": true
        };
        
        const mergedOptions = {...defaultOptions, ...options};
        return table.DataTable(mergedOptions);
    }
    
    // Инициализация таблиц брифов
    initializeDataTable('#commonBriefsTable');
    initializeDataTable('#commercialBriefsTable');
});

document.addEventListener('DOMContentLoaded', function() {
    // Находим все кнопки удаления брифов
    const deleteButtons = document.querySelectorAll('.delete-brief');
    
    deleteButtons.forEach(button => {
        button.addEventListener('click', function() {
            const briefId = this.getAttribute('data-brief-id');
            const briefType = this.getAttribute('data-brief-type');
            const briefName = this.getAttribute('data-brief-name');
            
            if (confirm(`Вы действительно хотите удалить бриф "${briefName}"? Связи с другими элементами будут сохранены.`)) {
                // Получаем CSRF токен
                const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
                
                // Формируем URL в зависимости от типа брифа
                const url = `/admin/brief/${briefType}/${briefId}`;
                
                // Отправляем AJAX-запрос на удаление
                fetch(url, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Скрываем строку в таблице
                        this.closest('tr').style.display = 'none';
                        
                        // Показываем уведомление об успешном удалении
                        alert(`Бриф "${briefName}" успешно удален`);
                        
                        // Перезагружаем страницу через 1 секунду
                        setTimeout(() => {
                            window.location.reload();
                        }, 1000);
                    } else {
                        alert('Ошибка при удалении брифа: ' + (data.message || 'Неизвестная ошибка'));
                    }
                })
                .catch(error => {
                    console.error('Ошибка при удалении брифа:', error);
                    alert('Произошла ошибка при удалении брифа. Подробности в консоли разработчика.');
                });
            }
        });
    });
});
</script>
