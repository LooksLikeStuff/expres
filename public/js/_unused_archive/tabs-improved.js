/**
 * Улучшенная функциональность для вкладок документов и брифов
 * Обеспечивает удобное взаимодействие с пользователем
 */

(function() {
    'use strict';

    // === КОНСТАНТЫ ===
    const SELECTORS = {
        documentUpload: '#document-upload',
        uploadBtn: '#upload-documents-btn',
        uploadLabel: '.upload-label',
        filesCount: '.files-count',
        uploadText: '.upload-text',
        searchBriefBtn: '#searchBriefBtn',
        searchStatus: '#brief-search-status',
        searchResults: '#brief-search-results',
        notifications: '#brief-notifications'
    };

    const MESSAGES = {
        noFiles: 'Файлы не выбраны',
        oneFile: 'файл выбран',
        multipleFiles: 'файлов выбрано',
        uploading: 'Загружаем...',
        searching: 'Поиск брифов...',
        uploaded: 'Файлы успешно загружены',
        uploadError: 'Ошибка при загрузке файлов'
    };

    // === ИНИЦИАЛИЗАЦИЯ ===
    document.addEventListener('DOMContentLoaded', function() {
        initDocumentUpload();
        initBriefSearch();
        initDragAndDrop();
    });

    // === ФУНКЦИИ ДЛЯ ДОКУМЕНТОВ ===
    function initDocumentUpload() {
        const uploadInput = document.querySelector(SELECTORS.documentUpload);
        const uploadBtn = document.querySelector(SELECTORS.uploadBtn);
        const filesCount = document.querySelector(SELECTORS.filesCount);
        const uploadText = document.querySelector(SELECTORS.uploadText);

        if (!uploadInput || !uploadBtn) return;

        // Обработчик выбора файлов
        uploadInput.addEventListener('change', function(e) {
            const files = e.target.files;
            updateFileCounter(files);
            toggleUploadButton(files.length > 0);
        });

        // Обработчик загрузки
        uploadBtn.addEventListener('click', function() {
            const files = uploadInput.files;
            if (files.length === 0) return;

            uploadFiles(files);
        });

        function updateFileCounter(files) {
            if (!filesCount || !uploadText) return;

            if (files.length === 0) {
                filesCount.textContent = MESSAGES.noFiles;
                uploadText.textContent = 'Нажмите для выбора файлов';
            } else if (files.length === 1) {
                filesCount.textContent = `1 ${MESSAGES.oneFile}`;
                uploadText.textContent = files[0].name;
            } else {
                filesCount.textContent = `${files.length} ${MESSAGES.multipleFiles}`;
                uploadText.textContent = `${files.length} файлов`;
            }
        }

        function toggleUploadButton(enabled) {
            if (!uploadBtn) return;
            
            uploadBtn.disabled = !enabled;
            if (enabled) {
                uploadBtn.classList.remove('disabled');
            } else {
                uploadBtn.classList.add('disabled');
            }
        }
    }

    function initDragAndDrop() {
        const uploadLabel = document.querySelector(SELECTORS.uploadLabel);
        const uploadInput = document.querySelector(SELECTORS.documentUpload);

        if (!uploadLabel || !uploadInput) return;

        // Предотвращаем стандартное поведение
        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
            uploadLabel.addEventListener(eventName, preventDefaults, false);
        });

        function preventDefaults(e) {
            e.preventDefault();
            e.stopPropagation();
        }

        // Подсветка при перетаскивании
        ['dragenter', 'dragover'].forEach(eventName => {
            uploadLabel.addEventListener(eventName, highlight, false);
        });

        ['dragleave', 'drop'].forEach(eventName => {
            uploadLabel.addEventListener(eventName, unhighlight, false);
        });

        function highlight() {
            uploadLabel.classList.add('drag-over');
        }

        function unhighlight() {
            uploadLabel.classList.remove('drag-over');
        }

        // Обработка сброса файлов
        uploadLabel.addEventListener('drop', function(e) {
            const dt = e.dataTransfer;
            const files = dt.files;

            uploadInput.files = files;
            
            // Генерируем событие change
            const event = new Event('change', { bubbles: true });
            uploadInput.dispatchEvent(event);
        }, false);
    }

    async function uploadFiles(files) {
        const uploadBtn = document.querySelector(SELECTORS.uploadBtn);
        const originalText = uploadBtn ? uploadBtn.innerHTML : '';

        try {
            // Показываем индикатор загрузки
            if (uploadBtn) {
                uploadBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> ' + MESSAGES.uploading;
                uploadBtn.disabled = true;
            }

            // Готовим данные для отправки
            const formData = new FormData();
            for (let i = 0; i < files.length; i++) {
                formData.append('documents[]', files[i]);
            }

            // Добавляем CSRF токен
            const csrfToken = document.querySelector('meta[name="csrf-token"]');
            if (csrfToken) {
                formData.append('_token', csrfToken.getAttribute('content'));
            }

            // Отправляем файлы
            const response = await fetch('/deals/upload-documents', {
                method: 'POST',
                body: formData
            });

            if (response.ok) {
                showNotification(MESSAGES.uploaded, 'success');
                // Перезагружаем страницу или обновляем список файлов
                setTimeout(() => window.location.reload(), 1500);
            } else {
                throw new Error('Ошибка сервера');
            }

        } catch (error) {
            console.error('Ошибка загрузки:', error);
            showNotification(MESSAGES.uploadError, 'error');
        } finally {
            // Восстанавливаем кнопку
            if (uploadBtn) {
                uploadBtn.innerHTML = originalText;
                uploadBtn.disabled = false;
            }
        }
    }

    // === ФУНКЦИИ ДЛЯ БРИФОВ ===
    function initBriefSearch() {
        const searchBtn = document.querySelector(SELECTORS.searchBriefBtn);
        
        if (!searchBtn) return;

        searchBtn.addEventListener('click', function() {
            const dealId = this.getAttribute('data-deal-id');
            const clientPhone = this.getAttribute('data-client-phone');
            
            if (!clientPhone) {
                showNotification('Телефон клиента не указан', 'error');
                return;
            }

            searchBriefs(dealId, clientPhone);
        });

        // Обработчик закрытия результатов
        document.addEventListener('click', function(e) {
            if (e.target.closest('.btn-close-results')) {
                hideSearchResults();
            }
        });
    }

    async function searchBriefs(dealId, clientPhone) {
        const searchBtn = document.querySelector(SELECTORS.searchBriefBtn);
        const searchStatus = document.querySelector(SELECTORS.searchStatus);
        const originalBtnText = searchBtn ? searchBtn.innerHTML : '';

        try {
            // Показываем индикатор поиска
            showSearchStatus();
            if (searchBtn) {
                searchBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> ' + MESSAGES.searching;
                searchBtn.disabled = true;
            }

            // Выполняем поиск
            const response = await fetch('/deals/search-briefs', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
                },
                body: JSON.stringify({
                    deal_id: dealId,
                    client_phone: clientPhone
                })
            });

            const data = await response.json();

            if (response.ok) {
                if (data.briefs && data.briefs.length > 0) {
                    showSearchResults(data.briefs);
                    showNotification(`Найдено ${data.briefs.length} брифов`, 'success');
                } else {
                    showNotification('Брифы не найдены', 'info');
                }
            } else {
                throw new Error(data.message || 'Ошибка поиска');
            }

        } catch (error) {
            console.error('Ошибка поиска брифов:', error);
            showNotification('Ошибка при поиске брифов', 'error');
        } finally {
            // Восстанавливаем кнопку
            hideSearchStatus();
            if (searchBtn) {
                searchBtn.innerHTML = originalBtnText;
                searchBtn.disabled = false;
            }
        }
    }

    function showSearchStatus() {
        const searchStatus = document.querySelector(SELECTORS.searchStatus);
        if (searchStatus) {
            searchStatus.style.display = 'block';
        }
    }

    function hideSearchStatus() {
        const searchStatus = document.querySelector(SELECTORS.searchStatus);
        if (searchStatus) {
            searchStatus.style.display = 'none';
        }
    }

    function showSearchResults(briefs) {
        const searchResults = document.querySelector(SELECTORS.searchResults);
        const resultsList = searchResults?.querySelector('.results-list');
        
        if (!searchResults || !resultsList) return;

        // Формируем HTML для результатов
        let html = '';
        briefs.forEach(brief => {
            html += `
                <div class="brief-item" data-brief-id="${brief.id}">
                    <div class="brief-info">
                        <h6>${brief.type || 'Бриф'}</h6>
                        <p><strong>Дата создания:</strong> ${brief.created_at || 'Не указано'}</p>
                        <p><strong>Статус:</strong> ${brief.status || 'Активный'}</p>
                    </div>
                    <div class="brief-actions">
                        <button type="button" class="create__group attach-brief-btn" 
                                data-brief-id="${brief.id}" 
                                data-brief-type="${brief.type}">
                            <i class="fas fa-link"></i>
                            Привязать
                        </button>
                    </div>
                </div>
            `;
        });

        resultsList.innerHTML = html;
        searchResults.style.display = 'block';

        // Добавляем обработчики для кнопок привязки
        resultsList.querySelectorAll('.attach-brief-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const briefId = this.getAttribute('data-brief-id');
                const briefType = this.getAttribute('data-brief-type');
                attachBrief(briefId, briefType);
            });
        });
    }

    function hideSearchResults() {
        const searchResults = document.querySelector(SELECTORS.searchResults);
        if (searchResults) {
            searchResults.style.display = 'none';
        }
    }

    async function attachBrief(briefId, briefType) {
        try {
            const response = await fetch('/deals/attach-brief', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
                },
                body: JSON.stringify({
                    brief_id: briefId,
                    brief_type: briefType
                })
            });

            const data = await response.json();

            if (response.ok) {
                showNotification('Бриф успешно привязан', 'success');
                setTimeout(() => window.location.reload(), 1500);
            } else {
                throw new Error(data.message || 'Ошибка привязки');
            }

        } catch (error) {
            console.error('Ошибка привязки брифа:', error);
            showNotification('Ошибка при привязке брифа', 'error');
        }
    }

    // === ВСПОМОГАТЕЛЬНЫЕ ФУНКЦИИ ===
    function showNotification(message, type = 'info') {
        const notifications = document.querySelector(SELECTORS.notifications);
        
        if (!notifications) {
            // Создаем временный toast если контейнера нет
            createToast(message, type);
            return;
        }

        notifications.className = `brief-notifications ${type}`;
        notifications.innerHTML = `
            <div class="notification-content">
                <i class="fas fa-${getNotificationIcon(type)}"></i>
                <span>${message}</span>
            </div>
        `;
        notifications.style.display = 'block';

        // Автоматически скрываем через 5 секунд
        setTimeout(() => {
            notifications.style.display = 'none';
        }, 5000);
    }

    function getNotificationIcon(type) {
        const icons = {
            success: 'check-circle',
            error: 'exclamation-triangle',
            info: 'info-circle'
        };
        return icons[type] || 'info-circle';
    }

    function createToast(message, type) {
        const toast = document.createElement('div');
        toast.className = `toast toast-${type}`;
        toast.innerHTML = `
            <i class="fas fa-${getNotificationIcon(type)}"></i>
            <span>${message}</span>
        `;
        
        // Добавляем стили
        Object.assign(toast.style, {
            position: 'fixed',
            top: '20px',
            right: '20px',
            padding: '15px 20px',
            borderRadius: '8px',
            color: 'white',
            fontWeight: '500',
            zIndex: '9999',
            display: 'flex',
            alignItems: 'center',
            gap: '10px',
            minWidth: '300px',
            backgroundColor: type === 'success' ? '#28a745' : 
                           type === 'error' ? '#dc3545' : '#17a2b8'
        });

        document.body.appendChild(toast);

        // Удаляем через 5 секунд
        setTimeout(() => {
            if (document.body.contains(toast)) {
                document.body.removeChild(toast);
            }
        }, 5000);
    }

    // === ГЛОБАЛЬНЫЕ ФУНКЦИИ ===
    // Функция для удаления документа (вызывается из HTML)
    window.deleteDocument = function(documentId) {
        if (!confirm('Вы уверены, что хотите удалить этот документ?')) {
            return;
        }

        fetch(`/deals/delete-document/${documentId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showNotification('Документ удален', 'success');
                setTimeout(() => window.location.reload(), 1500);
            } else {
                throw new Error(data.message || 'Ошибка удаления');
            }
        })
        .catch(error => {
            console.error('Ошибка удаления документа:', error);
            showNotification('Ошибка при удалении документа', 'error');
        });
    };

})();
