/**
 * Улучшенная функциональность для вкладок "Бриф" и "Документы"
 */

document.addEventListener('DOMContentLoaded', function() {
    initializeBriefModule();
    initializeDocumentsModule();
});

/**
 * Инициализация модуля брифа
 */
function initializeBriefModule() {
    const searchBtn = document.getElementById('searchBriefBtn');
    const phoneInput = document.getElementById('client_phone_search');
    const statusDiv = document.getElementById('brief-search-status');
    const resultsDiv = document.getElementById('brief-search-results');
    const closeBtn = document.querySelector('.brief-close-btn');

    // Обработчик поиска брифа
    if (searchBtn) {
        searchBtn.addEventListener('click', function() {
            const dealId = this.getAttribute('data-deal-id');
            const clientPhone = this.getAttribute('data-client-phone');
            
            if (!clientPhone) {
                showBriefNotification('Телефон клиента не указан', 'error');
                return;
            }

            searchBrief(dealId, clientPhone);
        });
    }

    // Закрытие результатов поиска
    if (closeBtn) {
        closeBtn.addEventListener('click', function() {
            if (resultsDiv) {
                resultsDiv.style.display = 'none';
            }
        });
    }

    // Обработчик отвязки брифа
    const detachBtn = document.querySelector('.btn-detach-brief');
    if (detachBtn) {
        detachBtn.addEventListener('click', function() {
            const dealId = this.getAttribute('data-deal-id');
            detachBrief(dealId);
        });
    }
}

/**
 * Инициализация модуля документов
 */
function initializeDocumentsModule() {
    const fileInput = document.getElementById('document-upload');
    const uploadArea = document.querySelector('.documents-upload-area');
    const uploadBtn = document.getElementById('upload-documents-btn');
    const filesCountElement = document.getElementById('files-count');
    
    if (!fileInput || !uploadArea) return;

    // Drag and drop функциональность
    uploadArea.addEventListener('dragover', function(e) {
        e.preventDefault();
        this.classList.add('drag-over');
    });

    uploadArea.addEventListener('dragleave', function(e) {
        e.preventDefault();
        this.classList.remove('drag-over');
    });

    uploadArea.addEventListener('drop', function(e) {
        e.preventDefault();
        this.classList.remove('drag-over');
        
        const files = e.dataTransfer.files;
        if (files.length > 0) {
            fileInput.files = files;
            updateFilesCount(files);
            enableUploadButton();
        }
    });

    // Обработчик выбора файлов
    if (fileInput) {
        fileInput.addEventListener('change', function() {
            updateFilesCount(this.files);
            enableUploadButton();
        });
    }

    // Обработчик загрузки
    if (uploadBtn) {
        uploadBtn.addEventListener('click', function() {
            if (fileInput.files.length > 0) {
                uploadDocuments(fileInput.files);
            }
        });
    }

    function updateFilesCount(files) {
        if (filesCountElement) {
            if (files.length === 0) {
                filesCountElement.textContent = 'Файлы не выбраны';
            } else if (files.length === 1) {
                filesCountElement.textContent = `Выбран 1 файл: ${files[0].name}`;
            } else {
                filesCountElement.textContent = `Выбрано файлов: ${files.length}`;
            }
        }
    }

    function enableUploadButton() {
        if (uploadBtn && fileInput.files.length > 0) {
            uploadBtn.disabled = false;
        } else if (uploadBtn) {
            uploadBtn.disabled = true;
        }
    }
}

/**
 * Поиск брифа
 */
function searchBrief(dealId, clientPhone) {
    const statusDiv = document.getElementById('brief-search-status');
    const resultsDiv = document.getElementById('brief-search-results');
    
    // Показываем индикатор загрузки
    if (statusDiv) {
        statusDiv.style.display = 'block';
    }
    if (resultsDiv) {
        resultsDiv.style.display = 'none';
    }

    // Здесь должен быть AJAX запрос к серверу
    // Пример:
    fetch('/search-brief', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
        },
        body: JSON.stringify({
            deal_id: dealId,
            client_phone: clientPhone
        })
    })
    .then(response => response.json())
    .then(data => {
        if (statusDiv) {
            statusDiv.style.display = 'none';
        }
        
        if (data.success) {
            displayBriefResults(data.briefs);
        } else {
            showBriefNotification(data.message || 'Ошибка поиска', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        if (statusDiv) {
            statusDiv.style.display = 'none';
        }
        showBriefNotification('Произошла ошибка при поиске', 'error');
    });
}

/**
 * Отображение результатов поиска брифа
 */
function displayBriefResults(briefs) {
    const resultsDiv = document.getElementById('brief-search-results');
    const resultsList = document.getElementById('brief-results-list');
    
    if (!resultsDiv || !resultsList) return;

    if (briefs.length === 0) {
        showBriefNotification('Брифы не найдены', 'warning');
        return;
    }

    let html = '';
    briefs.forEach(brief => {
        html += `
            <div class="brief-result-item" style="padding: 16px; border-bottom: 1px solid #e5e7eb; display: flex; justify-content: space-between; align-items: center;">
                <div>
                    <h6 style="margin: 0 0 4px 0; font-weight: 600; color: #1f2937;">${brief.title || 'Бриф #' + brief.id}</h6>
                    <p style="margin: 0; font-size: 13px; color: #6b7280;">Создан: ${brief.created_at}</p>
                </div>
                <button type="button" class="brief-attach-btn" data-brief-id="${brief.id}" 
                        style="background: #10b981; color: white; border: none; border-radius: 6px; padding: 8px 16px; font-size: 13px; cursor: pointer;">
                    <i class="fas fa-link"></i> Привязать
                </button>
            </div>
        `;
    });

    resultsList.innerHTML = html;
    resultsDiv.style.display = 'block';

    // Добавляем обработчики для кнопок привязки
    document.querySelectorAll('.brief-attach-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const briefId = this.getAttribute('data-brief-id');
            attachBrief(briefId);
        });
    });
}

/**
 * Привязка брифа
 */
function attachBrief(briefId) {
    // Здесь должен быть AJAX запрос для привязки брифа
    showBriefNotification('Функция привязки брифа будет реализована', 'info');
}

/**
 * Отвязка брифа
 */
function detachBrief(dealId) {
    if (confirm('Вы уверены, что хотите отвязать бриф от сделки?')) {
        // Здесь должен быть AJAX запрос для отвязки брифа
        showBriefNotification('Функция отвязки брифа будет реализована', 'info');
    }
}

/**
 * Показать уведомление для брифа
 */
function showBriefNotification(message, type = 'info') {
    const notificationsDiv = document.getElementById('brief-notifications');
    if (!notificationsDiv) return;

    const colors = {
        success: '#10b981',
        error: '#ef4444',
        warning: '#f59e0b',
        info: '#3b82f6'
    };

    const notification = document.createElement('div');
    notification.style.cssText = `
        background: ${colors[type]};
        color: white;
        padding: 12px 16px;
        border-radius: 8px;
        margin-bottom: 8px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        animation: slideInRight 0.3s ease;
    `;
    notification.textContent = message;

    notificationsDiv.appendChild(notification);
    notificationsDiv.style.display = 'block';

    // Автоматически удаляем уведомление через 5 секунд
    setTimeout(() => {
        notification.style.animation = 'slideOutRight 0.3s ease';
        setTimeout(() => {
            if (notification.parentNode) {
                notification.parentNode.removeChild(notification);
                if (notificationsDiv.children.length === 0) {
                    notificationsDiv.style.display = 'none';
                }
            }
        }, 300);
    }, 5000);
}

/**
 * Загрузка документов
 */
function uploadDocuments(files) {
    const progressContainer = document.getElementById('upload-progress');
    const progressBar = document.getElementById('upload-progress-bar');
    const uploadBtn = document.getElementById('upload-documents-btn');
    
    if (progressContainer && progressBar) {
        progressContainer.style.display = 'block';
        progressBar.style.width = '0%';
    }
    
    if (uploadBtn) {
        uploadBtn.disabled = true;
        uploadBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Загрузка...';
    }

    // Здесь должна быть реализация загрузки файлов
    // Симуляция прогресса загрузки
    let progress = 0;
    const interval = setInterval(() => {
        progress += Math.random() * 15;
        if (progress >= 100) {
            progress = 100;
            clearInterval(interval);
            
            // Завершение загрузки
            setTimeout(() => {
                if (progressContainer) {
                    progressContainer.style.display = 'none';
                }
                if (uploadBtn) {
                    uploadBtn.disabled = false;
                    uploadBtn.innerHTML = '<i class="fas fa-upload"></i> Загрузить файлы';
                }
                showDocumentNotification('Файлы успешно загружены!', 'success');
            }, 500);
        }
        
        if (progressBar) {
            progressBar.style.width = progress + '%';
        }
    }, 200);
}

/**
 * Показать уведомление для документов
 */
function showDocumentNotification(message, type = 'info') {
    // Можно реализовать отдельные уведомления для модуля документов
    console.log(`Document notification (${type}): ${message}`);
}

/**
 * Удаление документа
 */
function deleteDocument(documentId) {
    if (confirm('Вы уверены, что хотите удалить этот документ?')) {
        // Здесь должен быть AJAX запрос для удаления документа
        showDocumentNotification('Функция удаления документа будет реализована', 'info');
    }
}

// CSS анимации
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
`;
document.head.appendChild(style);
