<script>
// Документированные скрипты для работы с брифами
$(document).ready(function() {
    // Обработчик для кнопки поиска брифа во вкладке "Бриф"
    $(document).on('click', '#searchBriefBtn', function(e) {
        e.preventDefault(); // Предотвращаем стандартное поведение кнопки
        e.stopPropagation(); // Останавливаем всплытие события
        
        console.log('[Бриф] Нажата кнопка #searchBriefBtn');
        
        // Получаем данные из атрибутов data-*
        const dealId = $(this).data('deal-id');
        const clientPhone = $(this).data('client-phone');
        
        console.log('[Бриф] Данные из атрибутов кнопки:', { dealId, clientPhone });
        
        if (!dealId) {
            console.error('[Бриф] Не удалось получить ID сделки');
            alert('Не удалось получить ID сделки. Обновите страницу и попробуйте снова.');
            return false;
        }
        
        if (!clientPhone) {
            console.error('[Бриф] Телефон клиента не указан');
            alert('Не указан номер телефона клиента. Пожалуйста, заполните поле "Телефон клиента" и сохраните сделку.');
            return false;
        }
        
        console.log('[Бриф] Начинаем поиск брифа для сделки #' + dealId + ', номер телефона клиента: ' + clientPhone);
        
        // Блокируем кнопку во избежание множественных кликов
        $(this).prop('disabled', true);
        
        // Через небольшую задержку разблокируем кнопку
        setTimeout(() => {
            $(this).prop('disabled', false);
        }, 3000);
        
        // Показываем индикатор загрузки во вкладке
        $('#brief-spinner-container').show();
        $('#brief-search-results-container').hide();
        
        // Выполняем запрос на поиск брифов
        searchBriefs(dealId, clientPhone);
    });
    
    // Функция для поиска брифов по номеру телефона, делаем её глобальной
    window.searchBriefs = function(dealId, clientPhone) {
        console.log('[Бриф] Начинаем поиск брифов. Deal ID:', dealId, 'Телефон:', clientPhone);
        
        try {
            // Показываем индикатор загрузки
            $('#brief-spinner-container').show();
            
            // Скрываем и очищаем контейнер с результатами
            $('#brief-search-results-container').hide();
            $('#brief-results-list').html(''); // Очищаем результаты
        } catch (e) {
            console.error('[Бриф] Ошибка при подготовке поиска:', e);
        }
          
        // Проверяем наличие CSRF-токена
        let token = '';
        try {
            token = $('meta[name="csrf-token"]').attr('content');
            if (!token) {
                console.error('[Бриф] CSRF-токен не найден в мета-тегах!');
            }
        } catch (e) {
            console.error('[Бриф] Ошибка при получении CSRF-токена:', e);
        }
        
        try {
            console.log('[Бриф] Выполняем AJAX-запрос на /api/deals/' + dealId + '/search-briefs');
            
            $.ajax({
                // Исправляем URL для API
                url: '/api/deals/' + dealId + '/search-briefs',
                type: 'POST',
                data: {
                    client_phone: clientPhone,
                    _token: token // Используем токен из мета-тега
                },
                dataType: 'json',
                timeout: 30000, // 30 секунд таймаут
                success: function(response) {
                    // Скрываем индикатор загрузки
                    $('#brief-spinner-container').hide();
                    $('#brief-search-results-container').show();
                    
                    // Объединяем обычные и коммерческие брифы
                    const allBriefs = [];
                    
                    if (response.briefs && response.briefs.length > 0) {
                        response.briefs.forEach(brief => {
                            allBriefs.push({
                                ...brief,
                                type: 'common'
                            });
                        });
                    }
                    
                    if (response.commercials && response.commercials.length > 0) {
                        response.commercials.forEach(brief => {
                            allBriefs.push({
                                ...brief,
                                type: 'commercial'
                            });
                        });
                    }
                    
                    if (allBriefs.length > 0) {
                        try {
                            // Отображаем найденные брифы
                            let html = '<ul class="brief-results-list">';
                            allBriefs.forEach(function(brief) {
                                const briefType = brief.type || 'common'; // 'common' или 'commercial', с защитой от undefined
                                const briefTypeText = briefType === 'common' ? 'Общий' : 'Коммерческий';
                                const briefId = brief.id || '';
                                const createdAt = brief.created_at || '';
                                const userName = brief.user_name || 'Нет данных о пользователе';
                                
                                let buttonHtml = '';
                                
                                // Если бриф уже привязан к этой сделке, показываем соответствующую метку
                                if (brief.already_linked) {
                                    buttonHtml = '<span class="brief-attached-label"><i class="fas fa-check-circle"></i> Привязан</span>';
                                } else {
                                    buttonHtml = '<button type="button" class="btn-attach-brief" data-brief-id="' + briefId + '" data-brief-type="' + briefType + '" data-deal-id="' + dealId + '">' +
                                        '<i class="fas fa-link"></i> Привязать' +
                                        '</button>';
                                }
                                
                                html += '<li class="brief-item">' + 
                                    '<div class="brief-info">' + 
                                    '<span class="brief-id">' + briefTypeText + ' бриф #' + briefId + '</span>' +
                                    '<span class="brief-date">Дата создания: ' + createdAt + '</span>' +
                                    '<span class="brief-user">' + userName + '</span>' +
                                    '</div>' +
                                    '<div class="brief-actions">' +
                                    buttonHtml +
                                    '</div>' +
                                    '</li>';
                            });
                            html += '</ul>';
                            $('#brief-results-list').html(html);
                        } catch (e) {
                            console.error('[Бриф] Ошибка при отображении брифов:', e);
                            $('#brief-results-list').html('<div class="error-message"><p><strong>Ошибка при отображении результатов</strong></p></div>');
                        }
                    } else {
                        // Брифы не найдены
                        $('#brief-results-list').html('<div class="no-briefs-found">Брифы не найдены</div>');
                    }
                },
                error: function(error) {
                    console.error('[Бриф] Ошибка при поиске брифов:', error);
                    // Скрываем индикатор загрузки в случае ошибки
                    $('#brief-spinner-container').hide();
                    $('#brief-search-results-container').show();
                    
                    // Отображаем сообщение об ошибке с дополнительной информацией
                    let errorMessage = 'Произошла ошибка при поиске брифов';
                    
                    // Добавляем информацию о статус-коде если есть
                    if (error.status) {
                        if (error.status === 404) {
                            errorMessage = 'Маршрут поиска брифов не найден. Возможно, необходимо очистить кэш маршрутов (php artisan route:clear) или перезагрузить страницу.';
                        } else if (error.status === 401 || error.status === 403) {
                            errorMessage = 'Ошибка авторизации. Возможно, вам необходимо войти в систему заново.';
                        } else if (error.status === 500) {
                            errorMessage = 'Внутренняя ошибка сервера при поиске брифов.';
                        } else {
                            errorMessage += ' (Код ошибки: ' + error.status + ')';
                        }
                    }
                    
                    try {
                        // Избегаем использования шаблонных строк в HTML для большей совместимости
                        $('#brief-results-list').html(
                            '<div class="error-message">' +
                            '<p><strong>' + errorMessage + '</strong></p>' +
                            '<p>Попробуйте обновить страницу или обратитесь к администратору.</p>' +
                            '<button type="button" class="btn-retry-search" data-deal-id="' + dealId + '" data-client-phone="' + clientPhone + '">' +
                            '<i class="fas fa-sync"></i> Повторить поиск' +
                            '</button>' +
                            '</div>'
                        );
                    } catch (e) {
                        console.error('[Бриф] Ошибка при отображении сообщения об ошибке:', e);
                        // Простое сообщение об ошибке в случае проблем с форматированием HTML
                        $('#brief-results-list').text('Произошла ошибка при отображении результатов поиска.');
                    }
                }
            });
        } catch (ajaxError) {
            console.error('[Бриф] Критическая ошибка при выполнении AJAX-запроса:', ajaxError);
            $('#brief-spinner-container').hide();
            $('#brief-search-results-container').show();
            $('#brief-results-list').text('Критическая ошибка при поиске брифов. Пожалуйста, обновите страницу и попробуйте снова.');
        }
    };

    // Обработчик для кнопки привязки брифа
    $(document).on('click', '.btn-attach-brief', function() {
        const briefId = $(this).data('brief-id');
        const dealId = $(this).data('deal-id');
        const briefType = $(this).data('brief-type'); // Добавлен тип брифа
        
        console.log('[Бриф] Привязка брифа:', { briefId, dealId, briefType });
        
        // Блокируем кнопку на время выполнения запроса
        const $button = $(this);
        $button.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Привязываем...');
        
        $.ajax({
            url: '/api/deals/' + dealId + '/attach-brief',
            type: 'POST',
            data: {
                brief_id: briefId,
                brief_type: briefType,
                _token: $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.success) {
                    // Обновляем статус брифа в интерфейсе
                    try {
                        updateBriefStatusUI(true, dealId);
                        
                        // Заменяем кнопку на статус "Привязан" во всех результатах поиска брифов
                        $button.replaceWith('<span class="brief-attached-label"><i class="fas fa-check-circle"></i> Привязан</span>');
                        
                        // Показываем уведомление об успехе
                        showBriefStatusNotification('success', 'Бриф успешно привязан к сделке');
                    } catch (e) {
                        console.error('[Бриф] Ошибка при обновлении UI после привязки брифа:', e);
                    }
                } else {
                    // Восстанавливаем кнопку
                    $button.prop('disabled', false).html('<i class="fas fa-link"></i> Привязать');
                    showBriefStatusNotification('error', 'Ошибка при привязке брифа: ' + response.message);
                }
            },
            error: function(error) {
                console.error('[Бриф] Ошибка при привязке брифа:', error);
                // Восстанавливаем кнопку
                $button.prop('disabled', false).html('<i class="fas fa-link"></i> Привязать');
                showBriefStatusNotification('error', 'Произошла ошибка при привязке брифа');
            }
        });
    });    // Обработчик для кнопки отвязки брифа
    $(document).on('click', '.btn-detach-brief', function() {
        if (!confirm('Вы уверены, что хотите отвязать бриф от этой сделки?')) {
            return;
        }
        
        const dealId = $(this).data('deal-id');
        const $button = $(this);
        
        console.log('[Бриф] Отвязка брифа от сделки:', dealId);
        
        if (!dealId) {
            console.error('[Бриф] ID сделки не указан при отвязке брифа');
            showBriefStatusNotification('error', 'Ошибка: ID сделки не указан');
            return;
        }
        
        // Блокируем кнопку на время выполнения запроса
        $button.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Отвязываем...');
        
        // Получаем токен из мета-тега
        let token = '';
        try {
            token = $('meta[name="csrf-token"]').attr('content');
            if (!token) {
                console.error('[Бриф] CSRF-токен не найден');
                showBriefStatusNotification('error', 'Ошибка: CSRF-токен не найден');
                $button.prop('disabled', false).html('<i class="fas fa-unlink"></i> Отвязать бриф');
                return;
            }
        } catch (e) {
            console.error('[Бриф] Ошибка при получении CSRF-токена:', e);
            showBriefStatusNotification('error', 'Ошибка при получении токена безопасности');
            $button.prop('disabled', false).html('<i class="fas fa-unlink"></i> Отвязать бриф');
            return;
        }
        
        // Показываем индикатор загрузки
        $('#brief-spinner-container').show();
        
        $.ajax({
            url: '/api/deals/' + dealId + '/detach-brief',
            type: 'POST',
            data: {
                _token: token
            },
            dataType: 'json', // Указываем, что ожидаем JSON в ответе
            timeout: 30000, // 30 секунд таймаут
            success: function(response) {
                // Скрываем индикатор загрузки
                $('#brief-spinner-container').hide();
                
                if (response.success) {
                    try {
                        console.log('[Бриф] Бриф успешно отвязан:', response);
                        
                        // Обновляем статус брифа в интерфейсе
                        updateBriefStatusUI(false, dealId);
                        
                        // Обновляем глобальный статус брифа в заголовке
                        $('.brief-status-badge')
                            .removeClass('brief-status-attached')
                            .addClass('brief-status-not-attached')
                            .html('<i class="fas fa-info-circle"></i> Бриф не привязан');
                        
                        // Очищаем результаты поиска, чтобы пользователь мог искать заново
                        if ($('#brief-results-list').length) {
                            $('#brief-results-list').empty();
                            $('#brief-search-results-container').hide();
                        }
                        
                        // Показываем уведомление об успехе
                        showBriefStatusNotification('success', 'Бриф успешно отвязан от сделки');
                        
                        // Разблокируем кнопку поиска
                        $('#searchBriefBtn').prop('disabled', false);
                    } catch (e) {
                        console.error('[Бриф] Ошибка при обновлении UI после отвязки брифа:', e);
                    }
                } else {
                    // Восстанавливаем кнопку
                    $button.prop('disabled', false).html('<i class="fas fa-unlink"></i> Отвязать бриф');
                    showBriefStatusNotification('error', 'Ошибка при отвязке брифа: ' + (response.message || 'Неизвестная ошибка'));
                }
            },
            error: function(error) {
                console.error('[Бриф] Ошибка при отвязке брифа:', error);
                // Скрываем индикатор загрузки
                $('#brief-spinner-container').hide();
                
                // Восстанавливаем кнопку
                $button.prop('disabled', false).html('<i class="fas fa-unlink"></i> Отвязать бриф');
                
                // Формируем сообщение об ошибке с дополнительной информацией
                let errorMessage = 'Произошла ошибка при отвязке брифа';
                
                if (error.status) {
                    if (error.status === 500) {
                        errorMessage += ': ошибка сервера. Попробуйте позже или обратитесь к администратору';
                    } else if (error.status === 404) {
                        errorMessage += ': маршрут не найден';
                    } else if (error.status === 422) {
                        errorMessage += ': некорректные данные';
                    } else {
                        errorMessage += ' (код ошибки: ' + error.status + ')';
                    }
                }
                
                // Если в ответе есть дополнительная информация, добавляем её
                if (error.responseJSON && error.responseJSON.message) {
                    errorMessage += ': ' + error.responseJSON.message;
                }
                
                showBriefStatusNotification('error', errorMessage);
            }
        });
    });

    // Обработчики для закрытия результатов поиска брифов
    $(document).on('click', '.brief-btn-cancel', function() {
        console.log('[Бриф] Закрытие результатов поиска брифов');
        $('#brief-spinner-container').hide();
        $('#brief-search-results-container').hide();
    });

    // Обработчик для кнопки повторного поиска
    $(document).on('click', '.btn-retry-search', function() {
        const dealId = $(this).data('deal-id');
        const clientPhone = $(this).data('client-phone');
        
        console.log('[Бриф] Повторный поиск брифов:', { dealId, clientPhone });
        searchBriefs(dealId, clientPhone);
    });    // Функция для обновления пользовательского интерфейса статуса брифа
    function updateBriefStatusUI(isAttached, dealId) {
        try {
            console.log('[Бриф] Обновление статуса брифа в UI:', {isAttached, dealId});
            
            // Обновляем глобальный индикатор статуса
            $('.brief-status-badge').removeClass('brief-status-attached brief-status-not-attached');
            
            if (isAttached) {
                $('.brief-status-badge').addClass('brief-status-attached').html('<i class="fas fa-check-circle"></i> Бриф привязан');
                
                // Обновляем детальную информацию о статусе
                $('.brief-status-container').html(
                    '<div class="brief-status-details">' +
                    '<div class="brief-status brief-attached">' +
                    '<i class="fas fa-check-circle"></i>' +
                    '<div class="brief-status-text">' +
                    '<span class="brief-status-label">Бриф привязан к сделке</span>' +
                    '<span class="brief-status-date">Дата привязки: ' + getCurrentDateTime() + '</span>' +
                    '</div>' +
                    '</div>' +
                    '<button type="button" class="btn-detach-brief" data-deal-id="' + dealId + '">' +
                    '<i class="fas fa-unlink"></i> Отвязать бриф' +
                    '</button>' +
                    '</div>'
                );
                
                // Обновляем состояние хедера
                if ($('.brief-header-title').length) {
                    $('.brief-header-title h3').html('<i class="fas fa-file-alt"></i> Управление брифами <span class="brief-status-text">(Привязан)</span>');
                }
            } else {
                $('.brief-status-badge').addClass('brief-status-not-attached').html('<i class="fas fa-info-circle"></i> Бриф не привязан');
                
                // Обновляем детальную информацию о статусе
                $('.brief-status-container').html(
                    '<div class="brief-status brief-not-attached">' +
                    '<i class="fas fa-info-circle"></i>' +
                    '<div class="brief-status-text">' +
                    '<span>Бриф не привязан к сделке</span>' +
                    '<span class="brief-status-hint">Используйте форму поиска, чтобы найти нужный бриф</span>' +
                    '</div>' +
                    '</div>'
                );
                
                // Обновляем состояние хедера
                if ($('.brief-header-title').length) {
                    $('.brief-header-title h3').html('<i class="fas fa-file-alt"></i> Управление брифами');
                }
            }
            
            console.log('[Бриф] Обновление статуса брифа в UI выполнено успешно');
        } catch (error) {
            console.error('[Бриф] Ошибка при обновлении статуса брифа в UI:', error);
        }
    }

    // Вспомогательная функция для получения текущей даты и времени в формате ДД.ММ.ГГГГ ЧЧ:ММ
    function getCurrentDateTime() {
        const now = new Date();
        const day = String(now.getDate()).padStart(2, '0');
        const month = String(now.getMonth() + 1).padStart(2, '0');
        const year = now.getFullYear();
        const hours = String(now.getHours()).padStart(2, '0');
        const minutes = String(now.getMinutes()).padStart(2, '0');
        
        return day + '.' + month + '.' + year + ' ' + hours + ':' + minutes;
    }    // Функция для отображения уведомлений о статусе операций с брифами
    function showBriefStatusNotification(type, message) {
        try {
            console.log('[Бриф] Показываем уведомление:', {type, message});
            
            // Создаем элемент уведомления, если его еще нет
            if ($('#brief-notification').length === 0) {
                $('<div id="brief-notification" class="brief-notification"></div>').appendTo('.brief-module');
            }
            
            // Настраиваем класс и иконку в зависимости от типа уведомления
            let notificationClass, icon;
            
            switch (type) {
                case 'success':
                    notificationClass = 'brief-notification-success';
                    icon = 'check-circle';
                    break;
                case 'error':
                    notificationClass = 'brief-notification-error';
                    icon = 'exclamation-circle';
                    break;
                case 'warning':
                    notificationClass = 'brief-notification-warning';
                    icon = 'exclamation-triangle';
                    break;
                case 'info':
                default:
                    notificationClass = 'brief-notification-info';
                    icon = 'info-circle';
                    break;
            }
            
            // Очищаем все существующие таймеры для уведомления
            if (window.briefNotificationTimer) {
                clearTimeout(window.briefNotificationTimer);
            }
            
            // Обновляем содержимое и стиль уведомления
            $('#brief-notification')
                .stop(true, true) // Останавливаем все текущие анимации
                .removeClass('brief-notification-success brief-notification-error brief-notification-warning brief-notification-info')
                .addClass(notificationClass)
                .html('<i class="fas fa-' + icon + '"></i> ' + message)
                .fadeIn(300);
            
            // Добавляем кнопку закрытия для уведомлений об ошибках
            if (type === 'error') {
                $('#brief-notification').append(' <button class="brief-notification-close"><i class="fas fa-times"></i></button>');
                
                // Обработчик для кнопки закрытия
                $('.brief-notification-close').on('click', function() {
                    $('#brief-notification').fadeOut(300);
                });
                
                // Для ошибок более долгое время отображения
                window.briefNotificationTimer = setTimeout(function() {
                    $('#brief-notification').fadeOut(500);
                }, 10000);
            } else {
                // Автоматически скрываем уведомление через 5 секунд для других типов
                window.briefNotificationTimer = setTimeout(function() {
                    $('#brief-notification').fadeOut(500);
                }, 5000);
            }
            
            console.log('[Бриф] Уведомление показано');
        } catch (error) {
            console.error('[Бриф] Ошибка при отображении уведомления:', error);
            // Аварийное отображение сообщения через alert
            if (type === 'error') {
                alert('Ошибка: ' + message);
            }
        }
    }
});
</script>

<style>
/* Стили для модального окна поиска брифов */
.brief-search-modal {
    display: none;
    position: fixed;
    z-index: 9999;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.5);
    overflow-y: auto;
}

.brief-modal-dialog {
    max-width: 800px;
    margin: 30px auto;
    position: relative;
}

.brief-modal-content {
    background-color: #fff;
    border-radius: 8px;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
    overflow: hidden;
}

.brief-modal-header {
    padding: 15px;
    border-bottom: 1px solid #e9ecef;
    background-color: #f8f9fa;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.brief-modal-title {
    margin: 0;
    font-size: 18px;
    font-weight: 600;
    color: #333;
}

.brief-close-button {
    background: none;
    border: none;
    font-size: 24px;
    cursor: pointer;
    color: #888;
}

.brief-close-button:hover {
    color: #333;
}

.brief-modal-body {
    padding: 20px;
    max-height: 500px;
    overflow-y: auto;
}

.brief-modal-footer {
    padding: 15px;
    border-top: 1px solid #e9ecef;
    background-color: #f8f9fa;
    display: flex;
    justify-content: flex-end;
}

.brief-close-modal-btn {
    padding: 8px 16px;
    background-color: #6c757d;
    color: white;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    font-size: 14px;
}

.brief-close-modal-btn:hover {
    background-color: #5a6268;
}

/* Стили для таблицы результатов поиска */
.brief-search-table {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 20px;
}

.brief-search-table th, 
.brief-search-table td {
    padding: 8px 10px;
    border: 1px solid #ddd;
    text-align: left;
}

.brief-search-table th {
    background-color: #f5f5f5;
    font-weight: 600;
}

.brief-search-table tr:nth-child(even) {
    background-color: #f9f9f9;
}

.brief-search-table tr:hover {
    background-color: #f1f1f1;
}

.brief-already-linked {
    background-color: #e8f5e9 !important;
}

/* Стили для кнопок в таблице */
.link-brief-btn, 
.unlink-brief-btn {
    padding: 5px 10px;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    font-size: 13px;
    transition: all 0.2s;
}

.link-brief-btn {
    background-color: #007bff;
    color: white;
}

.link-brief-btn:hover {
    background-color: #0069d9;
}

.unlink-brief-btn {
    background-color: #ffc107;
    color: #212529;
}

.unlink-brief-btn:hover {
    background-color: #e0a800;
}

/* Стили для контейнера таблицы */
.brief-table-container {
    overflow-x: auto;
    margin-bottom: 20px;
}

/* Стили для информации о пользователях */
.brief-users-info {
    margin-bottom: 20px;
    padding: 10px;
    background-color: #f8f9fa;
    border-radius: 4px;
    border: 1px solid #e9ecef;
}

.brief-users-list {
    margin: 5px 0 0 0;
    padding-left: 20px;
}

/* Стили для уведомлений о статусе брифов */
.brief-notification {
    display: none;
    position: fixed;
    z-index: 9999;
    left: 50%;
    top: 20px;
    transform: translateX(-50%);
    padding: 10px 20px;
    border-radius: 4px;
    color: #fff;
    font-size: 14px;
    font-weight: 500;
    transition: all 0.3s;
}

.brief-notification-success {
    background-color: #28a745;
}

.brief-notification-error {
    background-color: #dc3545;
}

/* Адаптивность */
@media (max-width: 768px) {
    .brief-modal-dialog {
        margin: 10px;
        width: auto;
    }
    
    .brief-search-table {
        font-size: 12px;
    }
}
</style>

<script>
// Функция для безопасной проверки DOM-элементов
function safelyCheckElement(selector, actionDescription) {
    try {
        const element = document.querySelector(selector);
        if (!element) {
            console.warn(`[Бриф диагностика] Элемент ${selector} не найден при попытке ${actionDescription}`);
            return null;
        }
        return element;
    } catch (error) {
        console.error(`[Бриф диагностика] Ошибка при проверке элемента ${selector}:`, error);
        return null;
    }
}

// Добавляем обработчик для отладки ошибок в консоли
console.log('[Бриф диагностика] Скрипт инициализирован и готов к работе');
$(document).ready(function() {
    // Проверяем все ключевые элементы при загрузке
    const elementsToCheck = [
        { selector: '#searchBriefBtn', description: 'Кнопка поиска брифа' },
        { selector: '#brief-spinner-container', description: 'Контейнер индикатора загрузки' },
        { selector: '#brief-search-results-container', description: 'Контейнер результатов поиска' },
        { selector: '#brief-results-list', description: 'Список результатов поиска' },
        { selector: '.brief-status-container', description: 'Контейнер статуса брифа' }
    ];
    
    console.log('[Бриф диагностика] Проверка ключевых элементов...');
    elementsToCheck.forEach(item => {
        const element = safelyCheckElement(item.selector, `проверке при загрузке`);
        console.log(`[Бриф диагностика] ${item.description}: ${element ? 'найден' : 'НЕ НАЙДЕН'}`);
    });
    
    // Мониторинг нажатий на кнопку поиска брифа
    $(document).on('click', '#searchBriefBtn', function() {
        console.log('[Бриф диагностика] Нажата кнопка поиска брифа');
        console.log('[Бриф диагностика] deal-id:', $(this).data('deal-id'));
        console.log('[Бриф диагностика] client-phone:', $(this).data('client-phone'));
    });
    
    // Мониторинг показа/скрытия контейнера для результатов поиска
    const briefResultsContainer = safelyCheckElement('#brief-search-results-container', 'мониторинге видимости');
    if (briefResultsContainer) {
        const observer = new MutationObserver(mutations => {
            mutations.forEach(mutation => {
                if (mutation.attributeName === 'style') {
                    const isVisible = briefResultsContainer.style.display !== 'none';
                    console.log(`[Бриф диагностика] Контейнер результатов поиска: ${isVisible ? 'показан' : 'скрыт'}`);
                }
            });
        });
        
        observer.observe(briefResultsContainer, { attributes: true });
    }
});
</script>
