<div class="container-fluid py-4">
    <h1 class="h3 mb-2 text-gray-800">Удаленные пользователи</h1>
    <p class="mb-4">Управление удаленными учетными записями пользователей системы.</p>
    
    <!-- Карточка с таблицей удаленных пользователей -->
    <div class="card mb-4 filter-card">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">Список удаленных пользователей</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="trashedUsersDataTable" class="table table-bordered table-striped" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th width="5%">ID</th>
                            <th width="20%">Имя</th>
                            <th width="20%">Email</th>
                            <th width="15%">Телефон</th>
                            <th width="10%">Статус</th>
                            <th width="15%">Удален</th>
                            <th width="15%">Действия</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($trashed_users as $user)
                            <tr data-id="{{ $user->id }}">
                                <td>{{ $user->id }}</td>
                                <td>
                                    <a href="{{ route('profile.view', $user->id) }}" class="user-profile-link">
                                        {{ $user->name }}
                                    </a>
                                </td>
                                <td>{{ $user->email }}</td>
                                <td>{{ $user->phone ?? 'Не указан' }}</td>
                                <td>
                                    @php
                                        $statusClass = [
                                            'admin' => 'badge-danger',
                                            'user' => 'badge-primary',
                                            'architect' => 'badge-warning',
                                            'visualizer' => 'badge-dark',
                                            'coordinator' => 'badge-secondary',
                                            'partner' => 'badge-light'
                                        ][$user->status] ?? 'badge-info';
                                        
                                        $statusLabels = [
                                            'admin' => 'Администратор',
                                            'user' => 'Пользователь',
                                            'architect' => 'Архитектор',
                                            'visualizer' => 'Визуализатор',
                                            'coordinator' => 'Координатор',
                                            'partner' => 'Партнер'
                                        ];
                                    @endphp
                                    <span class="badge {{ $statusClass }}">
                                        {{ $statusLabels[$user->status] ?? ucfirst($user->status) }}
                                    </span>
                                </td>
                                <td>{{ $user->deleted_at->format('d.m.Y H:i') }}</td>
                                <td>
                                    <div class="btn-group">
                                        <button type="button" onclick="restoreUser({{ $user->id }})" class="btn btn-sm btn-outline-success" data-toggle="tooltip" title="Восстановить пользователя">
                                            <i class="fas fa-trash-restore"></i>
                                        </button>
                                        <button type="button" onclick="deletePermanently({{ $user->id }})" class="btn btn-sm btn-outline-danger" data-toggle="tooltip" title="Удалить окончательно">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center">Удаленных пользователей не найдено</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Модальное окно подтверждения восстановления пользователя -->
<div class="modal fade" id="restoreUserModal" tabindex="-1" aria-labelledby="restoreUserModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="restoreUserModalLabel">Подтверждение восстановления</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>Вы действительно хотите восстановить пользователя <strong id="userNameToRestore"></strong>?</p>
                <p>После восстановления пользователь снова получит доступ к системе.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Отмена</button>
                <button type="button" class="btn btn-success" id="confirmRestoreBtn">Восстановить</button>
            </div>
        </div>
    </div>
</div>

<!-- Модальное окно подтверждения окончательного удаления пользователя -->
<div class="modal fade" id="permanentDeleteModal" tabindex="-1" aria-labelledby="permanentDeleteModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="permanentDeleteModalLabel">Подтверждение удаления</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>Вы действительно хотите <strong>окончательно удалить</strong> пользователя <strong id="userNameToDelete"></strong>?</p>
                <p class="text-danger"><i class="fas fa-exclamation-triangle"></i> Внимание! Это действие нельзя будет отменить. Все данные пользователя будут безвозвратно потеряны.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Отмена</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteBtn">Удалить окончательно</button>
            </div>
        </div>
    </div>
</div>

<!-- Toast для уведомлений -->
<div aria-live="polite" aria-atomic="true" style="position: fixed; top: 1rem; right: 1rem; z-index: 1050;">
    <div id="toastContainer" style="position: absolute; top: 0; right: 0;"></div>
</div>

<!-- Подключение JS DataTables -->
<script>
$(document).ready(function() {
    // Инициализация тултипов Bootstrap
    try {
        $('[data-toggle="tooltip"]').tooltip();
    } catch (e) {
        console.warn('Не удалось инициализировать тултипы:', e);
    }

    // Улучшенная инициализация DataTables с надежной проверкой
    try {
        // Проверка наличия элемента таблицы
        const table = document.getElementById('trashedUsersDataTable');
        if (!table) {
            console.error('Элемент таблицы #trashedUsersDataTable не найден в DOM');
            return;
        }

        // Проверяем, инициализирована ли уже таблица
        let dataTable;
        if ($.fn.DataTable.isDataTable('#trashedUsersDataTable')) {
            dataTable = $('#trashedUsersDataTable').DataTable();
            dataTable.destroy();
            console.log('Существующая инициализация DataTable уничтожена');
        }
        
        // Проверка наличия данных в таблице
        const rowCount = $('#trashedUsersDataTable tbody tr').length;
        const hasEmptyMessage = $('#trashedUsersDataTable tbody tr td[colspan="7"]').length > 0;
        
        // Настройки таблицы
        const tableConfig = {
            "language": {
                "url": "//cdn.datatables.net/plug-ins/1.10.21/i18n/Russian.json",
                "emptyTable": "Удаленных пользователей не найдено"
            },
            "columnDefs": [
                { "orderable": false, "targets": 6 } // Столбец с действиями не сортируется
            ],
            "stateSave": false // Отключаем сохранение состояния
        };
        
        // Если нет данных, отключаем некоторые функции
        if (rowCount === 0 || hasEmptyMessage) {
            tableConfig.paging = false;
            tableConfig.searching = false;
            tableConfig.info = false;
        } else {
            tableConfig.pageLength = 25;
            tableConfig.order = [[0, "desc"]]; // Сортировка по ID в порядке убывания
            tableConfig.responsive = true;
        }
        
        // Инициализация таблицы
        dataTable = $('#trashedUsersDataTable').DataTable(tableConfig);
        console.log('DataTable успешно инициализирована');
    } catch (e) {
        console.error('Произошла ошибка при инициализации таблицы:', e);
        
        // Показываем сообщение об ошибке на странице
        if (!document.getElementById('dtErrorMessage')) {
            $('#trashedUsersDataTable').after('<div id="dtErrorMessage" class="alert alert-danger mt-3">Произошла ошибка при инициализации таблицы: ' + e.message + '. Попробуйте перезагрузить страницу.</div>');
        }
    }
});

// Функция для восстановления пользователя
function restoreUser(userId) {
    const userName = $(`tr[data-id="${userId}"] td:eq(1)`).text();
    $('#userNameToRestore').text(userName);
    
    // Показываем модальное окно
    $('#restoreUserModal').modal('show');
    
    // Настраиваем обработчик для кнопки подтверждения
    $('#confirmRestoreBtn').off('click').on('click', function() {
        $.ajax({
            url: `/admin/users/${userId}/restore`,
            type: 'POST',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.success) {
                    // Закрываем модальное окно
                    $('#restoreUserModal').modal('hide');
                    
                    // Убираем строку пользователя из таблицы, если DataTable доступен
                    try {
                        if ($.fn.dataTable.isDataTable('#trashedUsersDataTable')) {
                            const table = $('#trashedUsersDataTable').DataTable();
                            const row = $(`tr[data-id="${userId}"]`).closest('tr');
                            table.row(row).remove().draw();
                        } else {
                            // Иначе просто удаляем строку из DOM
                            $(`tr[data-id="${userId}"]`).fadeOut(300, function() { 
                                $(this).remove();
                                
                                // Проверяем, остались ли строки
                                if ($('#trashedUsersDataTable tbody tr').length === 0) {
                                    $('#trashedUsersDataTable tbody').html(
                                        '<tr><td colspan="7" class="text-center">Удаленных пользователей не найдено</td></tr>'
                                    );
                                }
                            });
                        }
                    } catch (e) {
                        console.error('Ошибка при удалении строки из таблицы:', e);
                        // Запасной вариант - просто перезагрузить страницу
                        setTimeout(() => location.reload(), 1000);
                    }
                    
                    // Показываем уведомление
                    showToast('Пользователь успешно восстановлен', 'success');
                    
                    // Если таблица стала пустой, перезагружаем страницу
                    if ($('#trashedUsersDataTable tbody tr').length <= 1) {
                        setTimeout(() => {
                            location.reload();
                        }, 1500);
                    }
                } else {
                    showToast(response.message || 'Произошла ошибка при восстановлении пользователя', 'error');
                }
            },
            error: function() {
                showToast('Произошла ошибка при восстановлении пользователя', 'error');
            }
        });
    });
}

// Функция для окончательного удаления пользователя
function deletePermanently(userId) {
    const userName = $(`tr[data-id="${userId}"] td:eq(1)`).text();
    $('#userNameToDelete').text(userName);
    
    // Показываем модальное окно
    $('#permanentDeleteModal').modal('show');
    
    // Настраиваем обработчик для кнопки подтверждения
    $('#confirmDeleteBtn').off('click').on('click', function() {
        $.ajax({
            url: `/admin/users/${userId}/force-delete`,
            type: 'DELETE',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.success) {
                    // Закрываем модальное окно
                    $('#permanentDeleteModal').modal('hide');
                    
                    // Убираем строку пользователя из таблицы
                    try {
                        if ($.fn.dataTable.isDataTable('#trashedUsersDataTable')) {
                            const table = $('#trashedUsersDataTable').DataTable();
                            const row = $(`tr[data-id="${userId}"]`).closest('tr');
                            table.row(row).remove().draw();
                        } else {
                            // Если DataTables не инициализирована, просто удаляем строку
                            $(`tr[data-id="${userId}"]`).fadeOut(300, function() {
                                $(this).remove();
                                
                                // Проверяем, остались ли строки
                                if ($('#trashedUsersDataTable tbody tr').length === 0) {
                                    $('#trashedUsersDataTable tbody').html(
                                        '<tr><td colspan="7" class="text-center">Удаленных пользователей не найдено</td></tr>'
                                    );
                                }
                            });
                        }
                    } catch (e) {
                        console.error('Ошибка при удалении строки из таблицы:', e);
                        setTimeout(() => location.reload(), 1000);
                    }
                    
                    // Показываем уведомление
                    showToast('Пользователь успешно удален окончательно', 'success');
                    
                    // Если таблица стала пустой, перезагружаем страницу
                    if ($('#trashedUsersDataTable tbody tr').length <= 1) {
                        setTimeout(() => {
                            location.reload();
                        }, 1500);
                    }
                } else {
                    showToast(response.message || 'Произошла ошибка при удалении пользователя', 'error');
                }
            },
            error: function() {
                showToast('Произошла ошибка при удалении пользователя', 'error');
            }
        });
    });
}

// Функция для отображения уведомлений (тостов)
function showToast(message, type) {
    // Создаем элемент Toast
    const toastId = `toast-${Date.now()}`;
    const toast = `
        <div id="${toastId}" class="toast" role="alert" aria-live="assertive" aria-atomic="true" data-delay="5000">
            <div class="toast-header bg-${type === 'success' ? 'success' : 'danger'} text-white">
                <strong class="mr-auto">${type === 'success' ? 'Успешно' : 'Ошибка'}</strong>
                <button type="button" class="ml-2 mb-1 close text-white" data-dismiss="toast" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="toast-body">
                ${message}
            </div>
        </div>
    `;
    
    // Добавляем тост в контейнер
    $('#toastContainer').append(toast);
    
    // Показываем тост
    try {
        $(`#${toastId}`).toast('show');
        
        // Удаляем тост после скрытия
        $(`#${toastId}`).on('hidden.bs.toast', function() {
            $(this).remove();
        });
    } catch (e) {
        console.warn('Ошибка при показе toast уведомления:', e);
        
        // Резервный вариант если Bootstrap Toast не работает
        const toastElement = document.getElementById(toastId);
        if (toastElement) {
            toastElement.style.opacity = '1';
            setTimeout(() => {
                toastElement.style.opacity = '0';
                setTimeout(() => {
                    toastElement.remove();
                }, 300);
            }, 5000);
        }
    }
}
</script>

<!-- Добавляем CSRF-токен, если его нет -->
@if(!$__env->yieldContent('meta'))
<meta name="csrf-token" content="{{ csrf_token() }}">
@endif

<!-- Стили для страницы удаленных пользователей -->
<style>
    /* Специфические стили для страницы удаленных пользователей */
    .badge {
        font-size: 85%;
        font-weight: 600;
        padding: 0.35em 0.6em;
    }
    
    .table .btn-group {
        white-space: nowrap;
    }
    
    .table .btn {
        padding: 0.25rem 0.5rem;
        margin: 0 1px;
    }
    
    /* Улучшение отображения на мобильных устройствах */
    @media (max-width: 768px) {
        .table .btn span {
            display: none;
        }
        
        .table .btn {
            padding: 0.25rem;
        }
        
        .table td {
            white-space: nowrap;
        }
    }
</style>
