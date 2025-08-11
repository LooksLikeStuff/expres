/**
 * Исправление функциональности вкладок документов и брифов
 * Комплексное решение для проблем с загрузкой документов и поиском брифов
 */

(function() {
    'use strict';

    console.log('[FIX] Инициализация исправлений для вкладок');

    // CSRF токен для Laravel
    function getCsrfToken() {
        const metaTag = document.querySelector('meta[name="csrf-token"]');
        if (metaTag) {
            return metaTag.getAttribute('content');
        }
        
        const input = document.querySelector('input[name="_token"]');
        if (input) {
            return input.value;
        }
        
        console.warn('[FIX] CSRF токен не найден');
        return null;
    }

    // Функция инициализации модуля документов
    function initializeDocumentsModule() {
        console.log('[FIX] Инициализация модуля документов');
        
        const uploadBtn = document.getElementById('uploadDocumentsBtn');
        const uploadInput = document.getElementById('documentUploadInput');
        const uploadArea = document.getElementById('documentsUploadArea');

        if (!uploadBtn) {
            console.log('[FIX] Кнопка uploadDocumentsBtn не найдена');
            return;
        }

        if (!uploadInput) {
            console.log('[FIX] Элемент documentUploadInput не найден');
            return;
        }

        console.log('[FIX] Элементы найдены, привязываем обработчики');

        // Очищаем старые обработчики
        uploadBtn.removeEventListener('click', handleUploadClick);

        // Обработчик клика по кнопке загрузки
        function handleUploadClick() {
            console.log('[FIX] Клик по кнопке выбора файлов');
            uploadInput.click();
        }

        uploadBtn.addEventListener('click', handleUploadClick);

        // Обработчик выбора файлов
        uploadInput.addEventListener('change', function(e) {
            console.log('[FIX] Файлы выбраны:', e.target.files.length);
            
            if (e.target.files.length > 0) {
                const filesCountInfo = document.getElementById('filesCountInfo');
                const filesCountText = document.getElementById('filesCountText');
                
                if (filesCountInfo && filesCountText) {
                    filesCountText.textContent = `${e.target.files.length} файлов выбрано`;
                    filesCountInfo.style.display = 'block';
                }

                // Здесь должна быть логика загрузки файлов
                console.log('[FIX] Готов к загрузке файлов');
                uploadFiles(e.target.files);
            }
        });

        // Drag & Drop
        if (uploadArea) {
            uploadArea.addEventListener('dragover', function(e) {
                e.preventDefault();
                e.stopPropagation();
                uploadArea.classList.add('drag-over');
            });

            uploadArea.addEventListener('dragleave', function(e) {
                e.preventDefault();
                e.stopPropagation();
                uploadArea.classList.remove('drag-over');
            });

            uploadArea.addEventListener('drop', function(e) {
                e.preventDefault();
                e.stopPropagation();
                uploadArea.classList.remove('drag-over');
                
                const files = e.dataTransfer.files;
                if (files.length > 0) {
                    uploadInput.files = files;
                    const event = new Event('change', { bubbles: true });
                    uploadInput.dispatchEvent(event);
                }
            });
        }
    }

    // Функция загрузки файлов
    function uploadFiles(files) {
        console.log('[FIX] Начинаем загрузку файлов:', files.length);
        
        const formData = new FormData();
        const csrfToken = getCsrfToken();
        
        if (!csrfToken) {
            alert('[FIX] Ошибка: CSRF токен не найден');
            return;
        }

        formData.append('_token', csrfToken);
        
        Array.from(files).forEach((file, index) => {
            formData.append(`documents[${index}]`, file);
        });

        // Показываем индикатор загрузки
        showUploadProgress();

        fetch('/api/upload-documents', {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': csrfToken
            }
        })
        .then(response => response.json())
        .then(data => {
            hideUploadProgress();
            if (data.success) {
                console.log('[FIX] Файлы успешно загружены');
                showNotification('Файлы успешно загружены', 'success');
            } else {
                throw new Error(data.message || 'Ошибка загрузки');
            }
        })
        .catch(error => {
            hideUploadProgress();
            console.error('[FIX] Ошибка загрузки файлов:', error);
            showNotification('Ошибка при загрузке файлов: ' + error.message, 'error');
        });
    }

    // Функция инициализации модуля брифов
    function initializeBriefModule() {
        console.log('[FIX] Инициализация модуля брифов');
        
        const searchBtn = document.getElementById('searchBriefBtn');
        
        if (!searchBtn) {
            console.log('[FIX] Кнопка searchBriefBtn не найдена');
            return;
        }

        console.log('[FIX] Кнопка поиска брифа найдена, привязываем обработчик');

        // Очищаем старые обработчики
        searchBtn.removeEventListener('click', handleBriefSearch);

        function handleBriefSearch() {
            const dealId = searchBtn.getAttribute('data-deal-id');
            const clientPhone = searchBtn.getAttribute('data-client-phone');
            
            console.log('[FIX] Поиск брифа для сделки:', dealId, 'телефон:', clientPhone);
            
            if (!clientPhone) {
                showBriefNotification('Телефон клиента не указан', 'error');
                return;
            }

            searchBriefs(dealId, clientPhone);
        }

        searchBtn.addEventListener('click', handleBriefSearch);
    }

    // Функция поиска брифов
    function searchBriefs(dealId, clientPhone) {
        console.log('[FIX] Отправка запроса поиска брифов');
        
        const csrfToken = getCsrfToken();
        if (!csrfToken) {
            showBriefNotification('Ошибка: CSRF токен не найден', 'error');
            return;
        }

        // Показываем индикатор загрузки
        showBriefSearchProgress();

        fetch(`/api/deals/${dealId}/search-briefs`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            },
            body: JSON.stringify({
                client_phone: clientPhone
            })
        })
        .then(response => response.json())
        .then(data => {
            hideBriefSearchProgress();
            if (data.success) {
                console.log('[FIX] Брифы найдены:', data);
                displayBriefResults(data, dealId);
            } else {
                showBriefNotification(data.message || 'Брифы не найдены', 'info');
            }
        })
        .catch(error => {
            hideBriefSearchProgress();
            console.error('[FIX] Ошибка поиска брифов:', error);
            showBriefNotification('Ошибка при поиске брифов', 'error');
        });
    }

    // Функция отображения результатов поиска брифов
    function displayBriefResults(data, dealId) {
        console.log('[FIX] Отображение результатов поиска брифов');
        
        const resultsContainer = document.getElementById('brief-search-results');
        if (!resultsContainer) {
            console.log('[FIX] Контейнер для результатов не найден');
            return;
        }

        let html = '<div class="brief-results-list">';
        
        // Отображаем обычные брифы
        if (data.briefs && data.briefs.length > 0) {
            html += '<h4>Обычные брифы:</h4>';
            data.briefs.forEach(brief => {
                html += `
                    <div class="brief-item" data-brief-id="${brief.id}">
                        <div class="brief-info">
                            <strong>Бриф #${brief.id}</strong>
                            <div>Создан: ${brief.created_at}</div>
                            <div>Пользователь: ${brief.user_name}</div>
                        </div>
                        <button class="btn-attach-brief" data-brief-id="${brief.id}" data-brief-type="common" data-deal-id="${dealId}">
                            Привязать
                        </button>
                    </div>
                `;
            });
        }

        // Отображаем коммерческие брифы
        if (data.commercials && data.commercials.length > 0) {
            html += '<h4>Коммерческие брифы:</h4>';
            data.commercials.forEach(brief => {
                html += `
                    <div class="brief-item" data-brief-id="${brief.id}">
                        <div class="brief-info">
                            <strong>Коммерческий бриф #${brief.id}</strong>
                            <div>Создан: ${brief.created_at}</div>
                            <div>Пользователь: ${brief.user_name}</div>
                        </div>
                        <button class="btn-attach-brief" data-brief-id="${brief.id}" data-brief-type="commercial" data-deal-id="${dealId}">
                            Привязать
                        </button>
                    </div>
                `;
            });
        }

        html += '</div>';
        
        resultsContainer.innerHTML = html;
        resultsContainer.style.display = 'block';

        // Привязываем обработчики кнопок привязки
        const attachButtons = resultsContainer.querySelectorAll('.btn-attach-brief');
        attachButtons.forEach(button => {
            button.addEventListener('click', function() {
                const briefId = this.getAttribute('data-brief-id');
                const briefType = this.getAttribute('data-brief-type');
                const dealId = this.getAttribute('data-deal-id');
                
                attachBrief(dealId, briefId, briefType);
            });
        });
    }

    // Функция привязки брифа
    function attachBrief(dealId, briefId, briefType) {
        console.log('[FIX] Привязка брифа:', briefId, 'к сделке:', dealId);
        
        const csrfToken = getCsrfToken();
        if (!csrfToken) {
            showBriefNotification('Ошибка: CSRF токен не найден', 'error');
            return;
        }

        fetch(`/api/deals/${dealId}/attach-brief`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            },
            body: JSON.stringify({
                brief_id: briefId,
                brief_type: briefType
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showBriefNotification('Бриф успешно привязан к сделке', 'success');
                // Обновляем страницу через 2 секунды
                setTimeout(() => {
                    window.location.reload();
                }, 2000);
            } else {
                showBriefNotification(data.message || 'Ошибка привязки брифа', 'error');
            }
        })
        .catch(error => {
            console.error('[FIX] Ошибка привязки брифа:', error);
            showBriefNotification('Ошибка при привязке брифа', 'error');
        });
    }

    // Вспомогательные функции для уведомлений и индикаторов
    function showNotification(message, type) {
        console.log(`[FIX] ${type.toUpperCase()}: ${message}`);
        alert(message);
    }

    function showBriefNotification(message, type) {
        console.log(`[FIX] Brief ${type.toUpperCase()}: ${message}`);
        alert(message);
    }

    function showUploadProgress() {
        console.log('[FIX] Показываем прогресс загрузки');
        // Здесь может быть показ спиннера
    }

    function hideUploadProgress() {
        console.log('[FIX] Скрываем прогресс загрузки');
        // Здесь может быть скрытие спиннера
    }

    function showBriefSearchProgress() {
        console.log('[FIX] Показываем прогресс поиска брифов');
        const statusDiv = document.getElementById('brief-search-status');
        if (statusDiv) {
            statusDiv.style.display = 'block';
        }
    }

    function hideBriefSearchProgress() {
        console.log('[FIX] Скрываем прогресс поиска брифов');
        const statusDiv = document.getElementById('brief-search-status');
        if (statusDiv) {
            statusDiv.style.display = 'none';
        }
    }

    // Функция полной реинициализации (для вызова из внешних скриптов)
    function reinitializeModules() {
        console.log('[FIX] Полная реинициализация модулей');
        setTimeout(() => {
            initializeDocumentsModule();
            initializeBriefModule();
        }, 100);
    }

    // Основная инициализация
    function initialize() {
        console.log('[FIX] Запуск инициализации');
        
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', reinitializeModules);
        } else {
            reinitializeModules();
        }

        // Также реинициализируем при появлении модального окна
        const observer = new MutationObserver(function(mutations) {
            mutations.forEach(function(mutation) {
                if (mutation.addedNodes.length) {
                    for (let node of mutation.addedNodes) {
                        if (node.nodeType === Node.ELEMENT_NODE) {
                            if (node.querySelector && (
                                node.querySelector('#uploadDocumentsBtn') ||
                                node.querySelector('#searchBriefBtn') ||
                                node.id === 'editModal'
                            )) {
                                console.log('[FIX] Обнаружено модальное окно или вкладка, реинициализация');
                                setTimeout(reinitializeModules, 200);
                            }
                        }
                    }
                }
            });
        });

        observer.observe(document.body, {
            childList: true,
            subtree: true
        });
    }

    // Экспортируем функции в глобальную область
    window.FixTabsSystem = {
        initialize: initialize,
        reinitialize: reinitializeModules,
        initializeDocumentsModule: initializeDocumentsModule,
        initializeBriefModule: initializeBriefModule
    };

    // Автоинициализация
    initialize();

})();
