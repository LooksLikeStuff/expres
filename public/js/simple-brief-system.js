/**
 * Простая система работы с брифами
 * Максимально упрощенная и понятная реализация
 */

(function() {
    'use strict';

    // Глобальные переменные
    let isLoading = false;

    // Инициализация при загрузке страницы
    document.addEventListener('DOMContentLoaded', function() {
        console.log('🔍 Инициализация простой системы брифов');
        initializeBriefSystem();
    });

    /**
     * Основная функция инициализации
     */
    function initializeBriefSystem() {
        const searchBtn = document.getElementById('searchBriefBtn');
        const detachBtn = document.getElementById('detachBriefBtn');

        // Кнопка поиска брифов
        if (searchBtn) {
            searchBtn.addEventListener('click', function() {
                const dealId = this.getAttribute('data-deal-id');
                const clientPhone = this.getAttribute('data-client-phone');

                if (!clientPhone) {
                    showMessage('Номер телефона клиента не указан', 'error');
                    return;
                }

                searchBriefs(dealId, clientPhone);
            });
        }

        // Кнопка отвязки брифа
        if (detachBtn) {
            detachBtn.addEventListener('click', function() {
                const dealId = this.getAttribute('data-deal-id');

                if (confirm('Вы уверены, что хотите отвязать бриф от сделки?')) {
                    detachBrief(dealId);
                }
            });
        }

        console.log('✅ Система брифов инициализирована');
    }

    /**
     * Поиск брифов по номеру телефона
     */
    async function searchBriefs(dealId, clientPhone) {
        if (isLoading) return;

        isLoading = true;
        const searchBtn = document.getElementById('searchBriefBtn');
        const resultsContainer = document.getElementById('briefSearchResults');
        const resultsList = document.getElementById('briefResultsList');

        console.log('🔍 Начало поиска брифов', { dealId, clientPhone });

        // Показываем состояние загрузки
        if (searchBtn) {
            searchBtn.disabled = true;
            searchBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Поиск...';
        }

        try {
            const requestData = {
                client_phone: clientPhone
            };

            console.log('📤 Отправка запроса:', {
                url: `/api/deals/${dealId}/search-briefs`,
                data: requestData
            });

            const response = await fetch(`/api/deals/${dealId}/search-briefs`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': getCsrfToken(),
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify(requestData)
            });

            console.log('📥 Ответ сервера:', {
                status: response.status,
                statusText: response.statusText,
                ok: response.ok
            });

            const data = await response.json();
            console.log('📋 Данные ответа:', data);

            // Дополнительная отладка статусов брифов
            if (data.briefs && data.briefs.length > 0) {
                console.log('🔍 Общие брифы и их статусы:');
                data.briefs.forEach(brief => {
                    console.log(`  - Бриф #${brief.id}: статус "${brief.status}", можно привязать: ${brief.can_attach}`);
                });
            }

            if (data.commercials && data.commercials.length > 0) {
                console.log('🔍 Коммерческие брифы и их статусы:');
                data.commercials.forEach(brief => {
                    console.log(`  - Бриф #${brief.id}: статус "${brief.status}", можно привязать: ${brief.can_attach}`);
                });
            }

            console.log(response.ok, data.success);
            if (response.ok && data.success) {
                displayBriefResults(data, dealId);
                showMessage(`Найдено брифов: ${data.total_found || 0}`, 'success');
            } else {
                console.error('❌ Ошибка в ответе сервера:', data);
                throw new Error(data.message || 'Ошибка при поиске брифов');
            }

        } catch (error) {
            console.error('❌ Ошибка поиска брифов:', error);
            showMessage('Ошибка при поиске брифов: ' + error.message, 'error');

            if (resultsContainer) {
                resultsContainer.style.display = 'none';
            }
        } finally {
            isLoading = false;

            // Восстанавливаем кнопку
            if (searchBtn) {
                searchBtn.disabled = false;
                searchBtn.innerHTML = 'Найти брифы';
            }
        }
    }

    /**
     * Отображение результатов поиска
     */
    function displayBriefResults(data, dealId) {
        const resultsContainer = document.getElementById('briefSearchResults');
        const resultsList = document.getElementById('briefResultsList');

        if (!resultsContainer || !resultsList) {
            console.error('Контейнеры для результатов не найдены');
            return;
        }

        // Очищаем предыдущие результаты
        resultsList.innerHTML = '';

        // Собираем все брифы в один массив
        const allBriefs = [];

        // Новые унифицированные брифы
        if (data.briefs && data.briefs.length > 0) {
            data.briefs.forEach(brief => {
                allBriefs.push({
                    ...brief,
                    type: brief.type,
                    type_name: brief.type === 'common' ? 'Общий бриф' : 'Коммерческий бриф',
                    system: 'unified'
                });
            });
        }

        if (allBriefs.length === 0) {
            resultsList.innerHTML = `
                <div style="padding: 20px; text-align: center; color: #6c757d;">
                    <i class="fas fa-search" style="font-size: 24px; margin-bottom: 8px;"></i>
                    <p>Брифы не найдены по номеру телефона <strong>${data.searched_phone || ''}</strong></p>
                    <small>Попробуйте указать номер в другом формате</small>
                </div>
            `;
        } else {
            // Отображаем найденные брифы
            allBriefs.forEach(brief => {
                const briefElement = createBriefElement(brief, dealId);
                resultsList.appendChild(briefElement);
            });
        }

        // Показываем контейнер с результатами
        resultsContainer.style.display = 'block';
    }

    /**
     * Создание элемента брифа
     */
    function createBriefElement(brief, dealId) {
        const element = document.createElement('div');
        element.className = 'brief-item mb-3';
        element.style.cssText = 'border: 1px solid #dee2e6; border-radius: 8px; padding: 16px; background: #fff;';

        // Определяем можно ли привязать бриф
        let isAttachable = false;
        let canAttach = false;

        if (brief.system === 'unified') {
            // Для новых унифицированных брифов
            isAttachable = brief.status === 'completed';
            canAttach = isAttachable && !brief.already_linked;
        } else {
            // Для старых брифов (legacy)
            const attachableStatuses = ['Завершенный', 'Завершен', 'completed', 'Отредактированный'];
            isAttachable = attachableStatuses.includes(brief.status);
            canAttach = isAttachable && !brief.already_linked;
        }

        // Определяем цвет и текст статуса
        let statusColor, statusText;
        if (brief.already_linked) {
            statusColor = '#6c757d'; // серый
            statusText = 'Уже привязан';
        } else if (brief.system === 'unified' && brief.status === 'completed') {
            statusColor = '#28a745'; // зеленый
            statusText = 'Завершен - можно привязать';
        } else if (brief.system === 'legacy' && (brief.status === 'Завершенный' || brief.status === 'Завершен' || brief.status === 'completed')) {
            statusColor = '#28a745'; // зеленый
            statusText = 'Завершен - можно привязать';
        } else if (brief.system === 'legacy' && brief.status === 'Отредактированный') {
            statusColor = '#17a2b8'; // бирюзовый
            statusText = 'Отредактирован - можно привязать';
        } else if (brief.system === 'legacy' && brief.status === 'Активный') {
            statusColor = '#ffc107'; // желтый
            statusText = 'Активный - нельзя привязать';
        } else {
            statusColor = '#dc3545'; // красный
            statusText = brief.status || 'Черновик - нельзя привязать';
        }

        // Добавляем индикатор системы
        const systemBadge = brief.system === 'unified' ?
            '<span class="badge bg-primary me-1" style="font-size: 10px;">NEW</span>' :
            '<span class="badge bg-secondary me-1" style="font-size: 10px;">LEGACY</span>';

        element.innerHTML = `
            <div class="d-flex justify-content-between align-items-start">
                <div class="flex-grow-1">
                    <h6 class="mb-2" style="font-weight: 600; color: #495057;">
                        ${systemBadge}${brief.type_name} #${brief.id}
                    </h6>
                    <div class="mb-1">
                        <small class="text-muted">
                            <i class="fas fa-calendar me-1"></i>
                            Создан: ${formatDate(brief.created_at)}
                        </small>
                    </div>
                    <div class="mb-1">
                        <small class="text-muted">
                            <i class="fas fa-user me-1"></i>
                            Автор: ${brief.user_name || 'Не указан'}
                        </small>
                    </div>
                    <div>
                        <span class="badge" style="background-color: ${statusColor}; color: white; font-size: 11px;">
                            ${statusText}
                        </span>
                        <small class="text-muted ms-2" style="font-size: 10px;">
                            (Статус: ${brief.status})
                        </small>
                    </div>
                </div>
                <div class="ms-3">
                    ${canAttach ? `
                        <button type="button"
                                class="btn btn-success btn-sm attach-brief-btn"
                                data-brief-id="${brief.id}"
                                data-brief-type="${brief.type}"
                                data-brief-system="${brief.system}"
                                data-deal-id="${dealId}">
                            <i class="fas fa-link me-1"></i>
                            Привязать
                        </button>
                    ` : `
                        <button type="button" class="btn btn-secondary btn-sm" disabled>
                            <i class="fas fa-ban me-1"></i>
                            Недоступен
                        </button>
                    `}
                </div>
            </div>
        `;

        // Добавляем обработчик для кнопки привязки
        const attachBtn = element.querySelector('.attach-brief-btn');
        if (attachBtn) {
            attachBtn.addEventListener('click', function() {
                const briefId = this.getAttribute('data-brief-id');
                const briefType = this.getAttribute('data-brief-type');
                const briefSystem = this.getAttribute('data-brief-system');
                const dealId = this.getAttribute('data-deal-id');

                attachBrief(briefId, briefType, briefSystem, dealId, this);
            });
        }

        return element;
    }

    /**
     * Привязка брифа к сделке
     */
    async function attachBrief(briefId, briefType, briefSystem, dealId, button) {
        if (isLoading) return;

        isLoading = true;
        const originalText = button.innerHTML;

        // Показываем состояние загрузки
        button.disabled = true;
        button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Привязываем...';

        try {
            const response = await fetch(`/api/deals/${dealId}/attach-brief`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': getCsrfToken(),
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({
                    brief_id: briefId
                })
            });

            const data = await response.json();

            if (response.ok && data.success) {
                showMessage('Бриф успешно привязан к сделке!', 'success');

                // Обновляем кнопку
                button.className = 'btn btn-success btn-sm';
                button.innerHTML = '<i class="fas fa-check"></i> Привязан';
                button.disabled = true;

                // Перезагружаем страницу через 2 секунды для обновления статуса
                setTimeout(() => {
                    window.location.reload();
                }, 2000);

            } else {
                throw new Error(data.message || 'Ошибка при привязке брифа');
            }

        } catch (error) {
            console.error('Ошибка привязки брифа:', error);
            showMessage('Ошибка при привязке брифа: ' + error.message, 'error');

            // Восстанавливаем кнопку
            button.disabled = false;
            button.innerHTML = originalText;
        } finally {
            isLoading = false;
        }
    }

    /**
     * Отвязка брифа от сделки
     */
    async function detachBrief(dealId) {
        if (isLoading) return;

        isLoading = true;
        const detachBtn = document.getElementById('detachBriefBtn');

        if (detachBtn) {
            detachBtn.disabled = true;
            detachBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Отвязываем...';
        }

        try {
            const response = await fetch(`/api/deals/${dealId}/detach-brief`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': getCsrfToken(),
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            const data = await response.json();

            if (response.ok && data.success) {
                showMessage('Бриф успешно отвязан от сделки!', 'success');

                // Перезагружаем страницу для обновления статуса
                setTimeout(() => {
                    window.location.reload();
                }, 1500);

            } else {
                throw new Error(data.message || 'Ошибка при отвязке брифа');
            }

        } catch (error) {
            console.error('Ошибка отвязки брифа:', error);
            showMessage('Ошибка при отвязке брифа: ' + error.message, 'error');

            // Восстанавливаем кнопку
            if (detachBtn) {
                detachBtn.disabled = false;
                detachBtn.innerHTML = 'Отвязать бриф';
            }
        } finally {
            isLoading = false;
        }
    }

    /**
     * Получение CSRF токена
     */
    function getCsrfToken() {
        const token = document.querySelector('meta[name="csrf-token"]');
        return token ? token.getAttribute('content') : '';
    }

    /**
     * Форматирование даты
     */
    function formatDate(dateString) {
        if (!dateString) return 'Не указано';

        try {
            const date = new Date(dateString);
            return date.toLocaleDateString('ru-RU', {
                year: 'numeric',
                month: 'short',
                day: 'numeric'
            });
        } catch (error) {
            return dateString;
        }
    }

    /**
     * Отображение уведомлений
     */
    function showMessage(message, type = 'info') {
        // Создаем контейнер для уведомлений если его нет
        let container = document.getElementById('notifications-container');
        if (!container) {
            container = document.createElement('div');
            container.id = 'notifications-container';
            container.style.cssText = `
                position: fixed;
                top: 20px;
                right: 20px;
                z-index: 10000;
                max-width: 400px;
            `;
            document.body.appendChild(container);
        }

        // Цвета и иконки для разных типов
        const colors = {
            success: '#28a745',
            error: '#dc3545',
            warning: '#ffc107',
            info: '#17a2b8'
        };

        const icons = {
            success: 'fas fa-check-circle',
            error: 'fas fa-exclamation-triangle',
            warning: 'fas fa-exclamation-circle',
            info: 'fas fa-info-circle'
        };

        // Создаем уведомление
        const notification = document.createElement('div');
        notification.style.cssText = `
            background: ${colors[type] || colors.info};
            color: white;
            padding: 16px 20px;
            border-radius: 8px;
            margin-bottom: 10px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            display: flex;
            align-items: center;
            gap: 10px;
            animation: slideInRight 0.3s ease-out;
            font-size: 14px;
            line-height: 1.4;
        `;

        notification.innerHTML = `
            <i class="${icons[type] || icons.info}"></i>
            <span>${message}</span>
            <button onclick="this.parentElement.remove()"
                    style="background: none; border: none; color: white; margin-left: auto; cursor: pointer; font-size: 16px;">
                ×
            </button>
        `;

        container.appendChild(notification);

        // Автоматическое удаление через 5 секунд
        setTimeout(() => {
            if (notification.parentElement) {
                notification.style.animation = 'slideOutRight 0.3s ease-in';
                setTimeout(() => {
                    if (notification.parentElement) {
                        notification.remove();
                    }
                }, 300);
            }
        }, 5000);
    }

    // Добавляем CSS анимации
    if (!document.getElementById('brief-system-styles')) {
        const style = document.createElement('style');
        style.id = 'brief-system-styles';
        style.textContent = `
            @keyframes slideInRight {
                from {
                    transform: translateX(100%);
                    opacity: 0;
                }
                to {
                    transform: translateX(0);
                    opacity: 1;
                }
            }

            @keyframes slideOutRight {
                from {
                    transform: translateX(0);
                    opacity: 1;
                }
                to {
                    transform: translateX(100%);
                    opacity: 0;
                }
            }

            .brief-item:hover {
                box-shadow: 0 2px 8px rgba(0,0,0,0.1);
                transform: translateY(-1px);
                transition: all 0.2s ease;
            }
        `;
        document.head.appendChild(style);
    }

})();
