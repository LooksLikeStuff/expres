/**
 * CSRF Protection System
 * Автоматическая система защиты от CSRF атак с обновлением токенов
 * 
 * Особенности:
 * - Автоматическое обновление CSRF токенов каждые 5 минут
 * - Перехват и повтор запросов при 419 ошибках
 * - Обновление токенов перед отправкой форм
 * - Поддержка axios и jQuery AJAX
 * - Обработка ошибок с пользовательскими уведомлениями
 */

(function() {
    'use strict';

    // Проверяем, что скрипт еще не был загружен
    if (window.CSRFProtection) {
        console.warn('CSRF Protection уже инициализирован');
        return;
    }

    // Глобальные переменные для управления CSRF токеном
    let csrfRefreshInProgress = false;
    let lastCsrfRefresh = Date.now();
    let refreshInterval = null;
    
    // Конфигурация
    const config = {
        refreshIntervalMs: 300000, // 5 минут
        forceRefreshAfterMs: 600000, // 10 минут
        retryAttempts: 3,
        retryDelay: 1000
    };

    /**
     * Получить текущий CSRF токен из meta тега
     */
    function getCurrentToken() {
        const metaToken = document.querySelector('meta[name="csrf-token"]');
        return metaToken ? metaToken.getAttribute('content') : null;
    }

    /**
     * Получить URL для обновления CSRF токена
     */
    function getRefreshUrl() {
        // Пытаемся найти URL в Laravel route helper или используем базовый
        if (window.Laravel && window.Laravel.routes && window.Laravel.routes['refresh-csrf']) {
            return window.Laravel.routes['refresh-csrf'];
        }
        return '/refresh-csrf';
    }

    /**
     * Обновление CSRF токена с retry логикой
     */
    function refreshCsrfToken(attempts = 0) {
        // Избегаем одновременных запросов на обновление токена
        if (csrfRefreshInProgress && attempts === 0) {
            return Promise.resolve(getCurrentToken());
        }
        
        csrfRefreshInProgress = true;
        
        return fetch(getRefreshUrl(), {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            credentials: 'same-origin'
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }
            return response.json();
        })
        .then(data => {
            if (!data.token) {
                throw new Error('Сервер вернул пустой токен');
            }

            // Обновляем мета-тег
            const metaToken = document.querySelector('meta[name="csrf-token"]');
            if (metaToken) {
                metaToken.setAttribute('content', data.token);
            }
            
            // Обновляем все скрытые поля с токеном
            document.querySelectorAll('input[name="_token"]').forEach(input => {
                input.value = data.token;
            });
            
            // Обновляем токен в axios заголовках, если axios доступен
            if (window.axios && window.axios.defaults) {
                window.axios.defaults.headers.common['X-CSRF-TOKEN'] = data.token;
            }
            
            // Обновляем jQuery AJAX setup, если jQuery доступен
            if (window.$ && window.$.ajaxSetup) {
                window.$.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': data.token
                    }
                });
            }
            
            lastCsrfRefresh = Date.now();
            console.log('CSRF токен обновлен: ' + new Date().toLocaleTimeString());
            
            // Вызываем пользовательские callback'и, если они есть
            if (window.CSRFProtection && window.CSRFProtection.callbacks.onTokenRefresh) {
                window.CSRFProtection.callbacks.onTokenRefresh(data.token);
            }
            
            return data.token;
        })
        .catch(error => {
            console.error('Ошибка при обновлении CSRF токена (попытка ' + (attempts + 1) + '):', error);
            
            // Retry логика
            if (attempts < config.retryAttempts - 1) {
                return new Promise(resolve => {
                    setTimeout(() => {
                        resolve(refreshCsrfToken(attempts + 1));
                    }, config.retryDelay * (attempts + 1));
                });
            }
            
            throw error;
        })
        .finally(() => {
            csrfRefreshInProgress = false;
        });
    }

    /**
     * Обеспечение свежего токена перед важными операциями
     */
    async function ensureFreshCsrfToken() {
        const timeSinceLastRefresh = Date.now() - lastCsrfRefresh;
        
        // Если прошло больше установленного времени, принудительно обновляем
        if (timeSinceLastRefresh > config.forceRefreshAfterMs) {
            try {
                await refreshCsrfToken();
                return true;
            } catch (error) {
                console.error('Не удалось обновить CSRF токен:', error);
                return false;
            }
        }
        
        return true;
    }

    /**
     * Настройка axios перехватчиков
     */
    function setupAxiosInterceptors() {
        if (!window.axios) return;

        const token = getCurrentToken();
        if (token) {
            window.axios.defaults.headers.common['X-CSRF-TOKEN'] = token;
        }
        
        // Перехватчик для обработки 419 ошибок
        window.axios.interceptors.response.use(
            response => response,
            async error => {
                if (error.response && error.response.status === 419) {
                    console.log('Axios: получена 419 ошибка, обновляем CSRF токен...');
                    try {
                        await refreshCsrfToken();
                        // Повторяем запрос с новым токеном
                        const originalRequest = error.config;
                        originalRequest.headers['X-CSRF-TOKEN'] = getCurrentToken();
                        return window.axios(originalRequest);
                    } catch (refreshError) {
                        console.error('Не удалось обновить CSRF токен:', refreshError);
                        handleCriticalError('Сессия истекла. Страница будет перезагружена.');
                        return Promise.reject(refreshError);
                    }
                }
                return Promise.reject(error);
            }
        );
    }

    /**
     * Настройка jQuery AJAX обработчиков
     */
    function setupJQueryHandlers() {
        if (!window.$) return;

        const token = getCurrentToken();
        if (token) {
            window.$.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': token
                }
            });
        }
        
        // Глобальный обработчик ошибок для jQuery AJAX
        $(document).ajaxError(async function(event, xhr, settings, thrownError) {
            if (xhr.status === 419) {
                console.log('jQuery AJAX: получена 419 ошибка, обновляем CSRF токен...');
                try {
                    await refreshCsrfToken();
                    // Автоматически повторяем запрос
                    const newToken = getCurrentToken();
                    settings.headers = settings.headers || {};
                    settings.headers['X-CSRF-TOKEN'] = newToken;
                    $.ajax(settings);
                } catch (error) {
                    console.error('Не удалось обновить CSRF токен:', error);
                    handleCriticalError('Сессия истекла. Страница будет перезагружена.');
                }
            }
        });
    }

    /**
     * Обработка форм
     */
    function setupFormHandlers() {
        const forms = document.querySelectorAll('form');
        
        forms.forEach(form => {
            // Сохраняем информацию о нажатой кнопке submit
            let activeSubmitButton = null;
            
            // Отслеживаем нажатие кнопок submit
            form.addEventListener('click', function(event) {
                if (event.target.type === 'submit') {
                    activeSubmitButton = event.target;
                }
            });
            
            form.addEventListener('submit', async function(event) {
                // Пропускаем формы, которые уже обрабатываются
                if (form.dataset.csrfProcessing === 'true') {
                    event.preventDefault();
                    return;
                }
                
                // Обрабатываем только POST формы
                const method = (form.method || 'GET').toLowerCase();
                if (method === 'post') {
                    event.preventDefault();
                    form.dataset.csrfProcessing = 'true';
                    
                    try {
                        // Обновляем токен перед отправкой
                        await ensureFreshCsrfToken();
                        
                        // Обновляем токен в форме, если он есть
                        const tokenInput = form.querySelector('input[name="_token"]');
                        if (tokenInput) {
                            tokenInput.value = getCurrentToken();
                        }
                        
                        // Если была нажата кнопка submit с name и value, добавляем её значение
                        if (activeSubmitButton && activeSubmitButton.name && activeSubmitButton.value) {
                            // Проверяем, есть ли уже поле с таким именем
                            let existingInput = form.querySelector(`input[name="${activeSubmitButton.name}"]`);
                            if (!existingInput) {
                                // Создаём скрытое поле для сохранения значения кнопки
                                const hiddenInput = document.createElement('input');
                                hiddenInput.type = 'hidden';
                                hiddenInput.name = activeSubmitButton.name;
                                hiddenInput.value = activeSubmitButton.value;
                                form.appendChild(hiddenInput);
                            } else {
                                // Обновляем существующее поле
                                existingInput.value = activeSubmitButton.value;
                            }
                        }
                        
                        // Отправляем форму
                        form.dataset.csrfProcessing = 'false';
                        form.submit();
                    } catch (error) {
                        form.dataset.csrfProcessing = 'false';
                        console.error('Ошибка при обновлении CSRF токена:', error);
                        handleCriticalError('Произошла ошибка. Страница будет перезагружена для обновления данных.');
                    }
                }
            });
        });
    }

    /**
     * Обработка критических ошибок
     */
    function handleCriticalError(message) {
        if (window.CSRFProtection && window.CSRFProtection.callbacks.onCriticalError) {
            window.CSRFProtection.callbacks.onCriticalError(message);
        } else {
            alert(message);
            location.reload();
        }
    }

    /**
     * Запуск периодического обновления токена
     */
    function startPeriodicRefresh() {
        if (refreshInterval) {
            clearInterval(refreshInterval);
        }
        
        refreshInterval = setInterval(() => {
            refreshCsrfToken().catch(() => {
                console.warn('Не удалось автоматически обновить CSRF токен');
            });
        }, config.refreshIntervalMs);
    }

    /**
     * Остановка периодического обновления токена
     */
    function stopPeriodicRefresh() {
        if (refreshInterval) {
            clearInterval(refreshInterval);
            refreshInterval = null;
        }
    }

    /**
     * Инициализация системы защиты
     */
    function init() {
        // Настраиваем обработчики
        setupAxiosInterceptors();
        setupJQueryHandlers();
        setupFormHandlers();
        
        // Запускаем периодическое обновление
        startPeriodicRefresh();
        
        // Делаем первое обновление через секунду после загрузки
        setTimeout(() => {
            refreshCsrfToken().catch(() => {
                console.warn('Не удалось обновить CSRF токен при инициализации');
            });
        }, 1000);
        
        console.log('CSRF Protection инициализирован');
    }

    // Публичный API
    window.CSRFProtection = {
        init: init,
        refreshToken: refreshCsrfToken,
        ensureFreshToken: ensureFreshCsrfToken,
        getCurrentToken: getCurrentToken,
        startPeriodicRefresh: startPeriodicRefresh,
        stopPeriodicRefresh: stopPeriodicRefresh,
        config: config,
        callbacks: {
            onTokenRefresh: null,
            onCriticalError: null
        }
    };

    // Автоматическая инициализация при загрузке DOM
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        // DOM уже загружен
        init();
    }

})();
