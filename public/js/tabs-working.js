/**
 * Рабочая функциональность для вкладок документов и брифов
 * Исправленная версия с полной поддержкой всех функций
 */

(function() {
    'use strict';

    // Глобальная переменная для ID сделки
    let dealId = null;

    // Функция получения ID сделки
    function getDealId() {
        if (dealId) return dealId;
        
        // Пытаемся получить из скрытого поля
        const dealIdField = document.getElementById('dealIdField');
        if (dealIdField && dealIdField.value) {
            dealId = dealIdField.value;
            console.log('ID сделки получен из поля dealIdField:', dealId);
            return dealId;
        }
        
        // Пытаемся получить из data-атрибутов
        const dealContainer = document.querySelector('[data-deal-id]');
        if (dealContainer) {
            dealId = dealContainer.getAttribute('data-deal-id');
            console.log('ID сделки получен из data-атрибута:', dealId);
            return dealId;
        }
        
        // Пытаемся получить из URL
        const urlMatch = window.location.pathname.match(/\/deal\/(\d+)/);
        if (urlMatch) {
            dealId = urlMatch[1];
            console.log('ID сделки получен из URL:', dealId);
            return dealId;
        }
        
        console.warn('ID сделки не найден');
        return null;
    }

    // CSRF токен для Laravel
    function getCsrfToken() {
        const metaTag = document.querySelector('meta[name="csrf-token"]');
        if (metaTag) {
            return metaTag.getAttribute('content');
        }
        
        // Попробуем найти в других местах
        const input = document.querySelector('input[name="_token"]');
        if (input) {
            return input.value;
        }
        
        console.warn('CSRF токен не найден');
        return null;
    }

    // === ИНИЦИАЛИЗАЦИЯ ===
    document.addEventListener('DOMContentLoaded', function() {
        console.log('Инициализация tabs-working.js');
        initializeDocumentsModule();
        initializeBriefModule();
    });

    // Функция для повторной инициализации при загрузке модального окна
    function reinitializeForModal() {
        console.log('Повторная инициализация для модального окна');
        // Сбрасываем кэш dealId, чтобы перечитать из поля
        dealId = null;
        
        // Инициализируем модули заново
        initializeDocumentsModule();
        initializeBriefModule();
    }

    // Делаем функцию доступной глобально для вызова из других скриптов
    window.reinitializeTabsForModal = reinitializeForModal;

    // === МОДУЛЬ ДОКУМЕНТОВ ===
    function initializeDocumentsModule() {
        console.log('Инициализация модуля документов');
        
        // Попытаемся получить ID сделки при инициализации
        const currentDealId = getDealId();
        if (currentDealId) {
            console.log('Найден ID сделки:', currentDealId);
        } else {
            console.warn('ID сделки не найден при инициализации');
        }
        
        const uploadBtn = document.getElementById('uploadDocumentsBtn');
        const uploadInput = document.getElementById('documentUploadInput');
        const uploadArea = document.getElementById('documentsUploadArea');
        const filesCountInfo = document.getElementById('filesCountInfo');
        const filesCountText = document.getElementById('filesCountText');

        if (!uploadBtn || !uploadInput) {
            console.log('Элементы загрузки документов не найдены');
            return;
        }

        // Обработчик клика по кнопке загрузки
        uploadBtn.addEventListener('click', function() {
            console.log('Клик по кнопке выбора файлов');
            uploadInput.click();
        });

        // Обработчик выбора файлов
        uploadInput.addEventListener('change', function(e) {
            const files = e.target.files;
            console.log('Выбрано файлов:', files.length);
            
            if (files.length > 0) {
                updateFilesCountDisplay(files.length);
                enableUploadProcess(files);
            }
        });

        // Drag & Drop функциональность
        if (uploadArea) {
            setupDragAndDrop(uploadArea, uploadInput);
        }

        function updateFilesCountDisplay(count) {
            if (filesCountInfo && filesCountText) {
                if (count > 0) {
                    filesCountText.textContent = `${count} ${getFileWord(count)} выбрано`;
                    filesCountInfo.style.display = 'flex';
                } else {
                    filesCountInfo.style.display = 'none';
                }
            }
        }

        function getFileWord(count) {
            if (count === 1) return 'файл';
            if (count >= 2 && count <= 4) return 'файла';
            return 'файлов';
        }

        function enableUploadProcess(files) {
            const uploadBtn = document.getElementById('uploadDocumentsBtn');
            const btnText = uploadBtn.querySelector('.upload-btn-text');
            
            if (btnText) {
                btnText.textContent = 'Загрузить файлы';
                uploadBtn.onclick = function() {
                    uploadFiles(files);
                };
            }
        }
    }

    function setupDragAndDrop(uploadArea, uploadInput) {
        // Предотвращаем стандартное поведение браузера
        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
            uploadArea.addEventListener(eventName, preventDefaults, false);
            document.body.addEventListener(eventName, preventDefaults, false);
        });

        function preventDefaults(e) {
            e.preventDefault();
            e.stopPropagation();
        }

        // Подсветка при перетаскивании
        ['dragenter', 'dragover'].forEach(eventName => {
            uploadArea.addEventListener(eventName, highlight, false);
        });

        ['dragleave', 'drop'].forEach(eventName => {
            uploadArea.addEventListener(eventName, unhighlight, false);
        });

        function highlight() {
            uploadArea.classList.add('drag-over');
        }

        function unhighlight() {
            uploadArea.classList.remove('drag-over');
        }

        // Обработка сброса файлов
        uploadArea.addEventListener('drop', handleDrop, false);

        function handleDrop(e) {
            const dt = e.dataTransfer;
            const files = dt.files;
            
            console.log('Файлы перетащены:', files.length);
            
            if (files.length > 0) {
                uploadInput.files = files;
                updateFilesCountDisplay(files.length);
                enableUploadProcess(files);
            }
        }
    }

    async function uploadFiles(files) {
        console.log('Начинаем загрузку файлов:', files.length);
        
        const uploadBtn = document.getElementById('uploadDocumentsBtn');
        const btnText = uploadBtn.querySelector('.upload-btn-text');
        const btnIcon = uploadBtn.querySelector('i');
        
        // Показываем процесс загрузки
        if (btnText) btnText.textContent = 'Загружаем...';
        if (btnIcon) {
            btnIcon.className = 'fas fa-spinner fa-spin';
        }
        uploadBtn.disabled = true;

        try {
            const formData = new FormData();
            
            // Добавляем файлы
            for (let i = 0; i < files.length; i++) {
                formData.append('documents[]', files[i]);
            }

            // Добавляем CSRF токен
            const csrfToken = getCsrfToken();
            if (csrfToken) {
                formData.append('_token', csrfToken);
            }

            // Добавляем ID сделки если есть
            const currentDealId = getDealId();
            if (currentDealId) {
                formData.append('deal_id', currentDealId);
            }

            const response = await fetch(`/deal/${currentDealId}/upload-documents`, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                }
            });

            const result = await response.json();

            if (response.ok && result.success) {
                showNotification('Файлы успешно загружены!', 'success');
                
                // Обновляем список документов
                if (result.documents) {
                    updateDocumentsList(result.documents);
                }
                
                // Сбрасываем форму
                resetUploadForm();
            } else {
                throw new Error(result.message || 'Ошибка при загрузке файлов');
            }

        } catch (error) {
            console.error('Ошибка загрузки:', error);
            showNotification('Ошибка при загрузке файлов: ' + error.message, 'error');
        } finally {
            // Возвращаем кнопку в исходное состояние
            if (btnText) btnText.textContent = 'Выбрать файлы';
            if (btnIcon) {
                btnIcon.className = 'fas fa-plus';
            }
            uploadBtn.disabled = false;
            uploadBtn.onclick = function() {
                document.getElementById('documentUploadInput').click();
            };
        }
    }

    function resetUploadForm() {
        const uploadInput = document.getElementById('documentUploadInput');
        const filesCountInfo = document.getElementById('filesCountInfo');
        
        if (uploadInput) {
            uploadInput.value = '';
        }
        
        if (filesCountInfo) {
            filesCountInfo.style.display = 'none';
        }
    }

    function updateDocumentsList(documents) {
        // Найдем контейнер для документов
        const emptyState = document.querySelector('.documents-empty-state');
        const placeholder = document.querySelector('.documents-placeholder');
        
        if (emptyState && placeholder && documents.length > 0) {
            emptyState.style.display = 'none';
            placeholder.style.display = 'block';
            
            const grid = document.getElementById('dynamic-documents-grid');
            if (grid) {
                grid.innerHTML = '';
                
                documents.forEach(doc => {
                    grid.appendChild(createDocumentElement(doc));
                });
            }
        }
    }

    function createDocumentElement(doc) {
        const div = document.createElement('div');
        div.className = 'document-item';
        
        // Определяем правильный URL для скачивания
        let downloadUrl = doc.download_url || doc.url;
        
        // Если URL не определен, попробуем сформировать его
        if (!downloadUrl && doc.name) {
            const currentDealId = getDealId();
            if (currentDealId) {
                // Формируем URL для скачивания через маршрут Laravel
                downloadUrl = `/deals/${currentDealId}/documents/${encodeURIComponent(doc.name)}/download`;
                console.log('Сформирован URL для скачивания:', downloadUrl);
            }
        }
        
        div.innerHTML = `
            <div class="document-info">
                <div class="document-icon">
                    <i class="fas ${getFileIcon(doc.name)}"></i>
                </div>
                <div class="document-details">
                    <div class="document-name">${doc.name}</div>
                    <div class="document-size">${formatFileSize(doc.size)}</div>
                </div>
            </div>
            <div class="document-actions">
                ${downloadUrl ? `
                <a href="${downloadUrl}" 
                   target="_blank" 
                   class="document-action-btn download" 
                   title="Скачать"
                   download="${doc.name}">
                    <i class="fas fa-download"></i>
                </a>
                ` : ''}
                <button type="button" 
                        class="document-action-btn delete" 
                        onclick="deleteDocument(${doc.id})"
                        title="Удалить">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        `;
        
        return div;
    }

    function getFileIcon(filename) {
        const extension = filename.split('.').pop().toLowerCase();
        
        switch (extension) {
            case 'pdf': return 'fa-file-pdf';
            case 'doc':
            case 'docx': return 'fa-file-word';
            case 'xls':
            case 'xlsx': return 'fa-file-excel';
            case 'jpg':
            case 'jpeg':
            case 'png':
            case 'gif': return 'fa-file-image';
            case 'zip':
            case 'rar': return 'fa-file-archive';
            default: return 'fa-file';
        }
    }

    function formatFileSize(bytes) {
        if (bytes === 0) return '0 Bytes';
        
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    }

    // === МОДУЛЬ БРИФОВ ===
    function initializeBriefModule() {
        console.log('Инициализация модуля брифов');
        
        const searchBtn = document.getElementById('searchBriefBtn');
        const closeBtn = document.querySelector('.brief-close-btn');
        const detachBtn = document.querySelector('.btn-detach-brief');

        if (searchBtn) {
            searchBtn.addEventListener('click', function() {
                const dealId = this.getAttribute('data-deal-id');
                const clientPhone = this.getAttribute('data-client-phone');
                
                console.log('Поиск брифа для сделки:', dealId, 'телефон:', clientPhone);
                
                if (!clientPhone) {
                    showNotification('Телефон клиента не указан', 'error');
                    return;
                }

                searchBriefs(dealId, clientPhone);
            });
        }

        if (closeBtn) {
            closeBtn.addEventListener('click', function() {
                const resultsDiv = document.getElementById('brief-search-results');
                if (resultsDiv) {
                    resultsDiv.style.display = 'none';
                }
            });
        }

        if (detachBtn) {
            detachBtn.addEventListener('click', function() {
                const dealId = this.getAttribute('data-deal-id');
                console.log('Отвязка брифа от сделки:', dealId);
                detachBrief(dealId);
            });
        }
    }

    async function searchBriefs(dealId, clientPhone) {
        const statusDiv = document.getElementById('brief-search-status');
        const resultsDiv = document.getElementById('brief-search-results');
        const searchBtn = document.getElementById('searchBriefBtn');
        
        // Показываем индикатор загрузки
        if (statusDiv) statusDiv.style.display = 'flex';
        if (resultsDiv) resultsDiv.style.display = 'none';
        if (searchBtn) searchBtn.disabled = true;

        try {
            const csrfToken = getCsrfToken();
            
            const response = await fetch(`/api/deals/${dealId}/search-briefs`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({
                    deal_id: dealId,
                    client_phone: clientPhone
                })
            });

            const result = await response.json();

            if (response.ok && result.success) {
                displayBriefResults(result.briefs || []);
            } else {
                throw new Error(result.message || 'Ошибка при поиске брифов');
            }

        } catch (error) {
            console.error('Ошибка поиска брифов:', error);
            showNotification('Ошибка при поиске брифов: ' + error.message, 'error');
        } finally {
            if (statusDiv) statusDiv.style.display = 'none';
            if (searchBtn) searchBtn.disabled = false;
        }
    }

    function displayBriefResults(briefs) {
        const resultsDiv = document.getElementById('brief-search-results');
        const resultsList = document.getElementById('brief-results-list');
        
        if (!resultsDiv || !resultsList) return;

        if (briefs.length === 0) {
            resultsList.innerHTML = `
                <div style="padding: 20px; text-align: center; color: #6b7280;">
                    <i class="fas fa-search" style="font-size: 24px; margin-bottom: 8px;"></i>
                    <p>Брифы не найдены</p>
                </div>
            `;
        } else {
            resultsList.innerHTML = briefs.map(brief => `
                <div class="brief-result-item" style="padding: 16px; border-bottom: 1px solid #e5e7eb;">
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <div>
                            <h4 style="margin: 0 0 4px 0; font-size: 14px; font-weight: 600;">
                                ${brief.type === 'common' ? 'Обычный бриф' : 'Коммерческий бриф'}
                            </h4>
                            <p style="margin: 0; font-size: 12px; color: #6b7280;">
                                ID: ${brief.id} | Создан: ${formatDate(brief.created_at)}
                            </p>
                        </div>
                        <button type="button" 
                                class="btn btn-sm btn-primary" 
                                onclick="attachBrief(${brief.id}, '${brief.type}')"
                                style="padding: 6px 12px; font-size: 12px;">
                            Привязать
                        </button>
                    </div>
                </div>
            `).join('');
        }
        
        resultsDiv.style.display = 'block';
    }

    async function attachBrief(briefId, briefType) {
        console.log('Привязка брифа:', briefId, 'тип:', briefType);
        
        const dealId = getDealId();
        if (!dealId) {
            showNotification('ID сделки не найден', 'error');
            return;
        }

        try {
            const csrfToken = getCsrfToken();
            
            const response = await fetch(`/api/deals/${dealId}/attach-brief`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({
                    deal_id: dealId,
                    brief_id: briefId,
                    brief_type: briefType
                })
            });

            const result = await response.json();

            if (response.ok && result.success) {
                showNotification('Бриф успешно привязан!', 'success');
                
                // Перезагружаем страницу через 1 секунду
                setTimeout(() => {
                    window.location.reload();
                }, 1000);
            } else {
                throw new Error(result.message || 'Ошибка при привязке брифа');
            }

        } catch (error) {
            console.error('Ошибка привязки брифа:', error);
            showNotification('Ошибка при привязке брифа: ' + error.message, 'error');
        }
    }

    async function detachBrief(dealId) {
        if (!confirm('Вы уверены, что хотите отвязать бриф от сделки?')) {
            return;
        }

        try {
            const csrfToken = getCsrfToken();
            
            const response = await fetch(`/api/deals/${dealId}/detach-brief`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({
                    deal_id: dealId
                })
            });

            const result = await response.json();

            if (response.ok && result.success) {
                showNotification('Бриф успешно отвязан', 'success');
                
                // Перезагружаем страницу через 1 секунду
                setTimeout(() => {
                    window.location.reload();
                }, 1000);
            } else {
                throw new Error(result.message || 'Ошибка при отвязке брифа');
            }

        } catch (error) {
            console.error('Ошибка отвязки брифа:', error);
            showNotification('Ошибка при отвязке брифа: ' + error.message, 'error');
        }
    }

    // === ВСПОМОГАТЕЛЬНЫЕ ФУНКЦИИ ===
    function getDealId() {
        // Попробуем найти ID сделки в разных местах
        const searchBtn = document.getElementById('searchBriefBtn');
        if (searchBtn) {
            return searchBtn.getAttribute('data-deal-id');
        }
        
        const detachBtn = document.querySelector('.btn-detach-brief');
        if (detachBtn) {
            return detachBtn.getAttribute('data-deal-id');
        }
        
        // Попробуем найти в URL
        const urlMatch = window.location.href.match(/deal[s]?\/(\d+)/);
        if (urlMatch) {
            return urlMatch[1];
        }
        
        return null;
    }

    function formatDate(dateString) {
        const date = new Date(dateString);
        return date.toLocaleDateString('ru-RU');
    }

    function showNotification(message, type = 'info') {
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

        const notification = document.createElement('div');
        notification.style.cssText = `
            background: ${getNotificationColor(type)};
            color: white;
            padding: 16px 20px;
            border-radius: 8px;
            margin-bottom: 10px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            display: flex;
            align-items: center;
            gap: 10px;
            animation: slideInRight 0.3s ease-out;
        `;
        
        notification.innerHTML = `
            <i class="fas ${getNotificationIcon(type)}"></i>
            <span>${message}</span>
            <button onclick="this.parentElement.remove()" style="
                background: none; 
                border: none; 
                color: white; 
                cursor: pointer; 
                margin-left: auto;
                font-size: 16px;
            ">×</button>
        `;

        container.appendChild(notification);

        // Автоудаление через 5 секунд
        setTimeout(() => {
            if (notification.parentElement) {
                notification.remove();
            }
        }, 5000);
    }

    function getNotificationColor(type) {
        switch (type) {
            case 'success': return '#10b981';
            case 'error': return '#ef4444';
            case 'warning': return '#f59e0b';
            default: return '#3b82f6';
        }
    }

    function getNotificationIcon(type) {
        switch (type) {
            case 'success': return 'fa-check-circle';
            case 'error': return 'fa-exclamation-circle';
            case 'warning': return 'fa-exclamation-triangle';
            default: return 'fa-info-circle';
        }
    }

    // === ГЛОБАЛЬНЫЕ ФУНКЦИИ ===
    // Функция для удаления документа (вызывается из HTML)
    window.deleteDocument = async function(documentId) {
        if (!confirm('Вы уверены, что хотите удалить этот документ?')) {
            return;
        }

        try {
            const csrfToken = getCsrfToken();
            
            const response = await fetch(`/deals/delete-document/${documentId}`, {
                method: 'DELETE',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': csrfToken
                }
            });

            const result = await response.json();

            if (response.ok && result.success) {
                showNotification('Документ успешно удален', 'success');
                
                // Удаляем элемент из DOM
                const documentElement = document.querySelector(`[onclick="deleteDocument(${documentId})"]`).closest('.document-item');
                if (documentElement) {
                    documentElement.remove();
                }
            } else {
                throw new Error(result.message || 'Ошибка при удалении документа');
            }

        } catch (error) {
            console.error('Ошибка удаления документа:', error);
            showNotification('Ошибка при удалении документа: ' + error.message, 'error');
        }
    };

    window.attachBrief = attachBrief;

    // Добавляем CSS для анимации
    const style = document.createElement('style');
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
    `;
    document.head.appendChild(style);

})();
