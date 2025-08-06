/**
 * Улучшенная система загрузки документов для модуля сделок
 * Исправляет проблемы с кликом по полям выбора файлов
 * Добавляет интеграцию с Яндекс.Диском
 */

class ImprovedDocumentsModule {
    constructor() {
        this.uploadInProgress = false;
        this.supportedFormats = {
            'contract_file': ['.pdf', '.doc', '.docx'],
            'technical_task': ['.pdf', '.doc', '.docx'],
            'project_estimate': ['.pdf', '.doc', '.docx', '.xls', '.xlsx'],
            'blueprints': ['.pdf', '.dwg', '.jpg', '.jpeg', '.png', '.zip'],
            'models_3d': ['.3ds', '.max', '.obj', '.fbx', '.blend', '.zip', '.rar'],
            'presentation': ['.pdf', '.ppt', '.pptx', '.jpg', '.jpeg', '.png', '.zip'],
            'reference_materials': ['.pdf', '.doc', '.docx', '.jpg', '.jpeg', '.png', '.zip', '.rar'],
            'client_correspondence': ['.pdf', '.doc', '.docx', '.txt', '.jpg', '.jpeg', '.png', '.zip'],
            'other_documents': ['*']
        };
        
        this.maxFileSizes = {
            'contract_file': 1500 * 1024 * 1024, // 1500MB
            'technical_task': 1500 * 1024 * 1024, // 1500MB
            'project_estimate': 1500 * 1024 * 1024, // 1500MB
            'blueprints': 1500 * 1024 * 1024, // 1500MB
            'models_3d': 1500 * 1024 * 1024, // 1500MB
            'presentation': 1500 * 1024 * 1024, // 1500MB
            'reference_materials': 1500 * 1024 * 1024, // 1500MB
            'client_correspondence': 1500 * 1024 * 1024, // 1500MB
            'other_documents': 1500 * 1024 * 1024 // 1500MB
        };
        
        this.init();
    }
    
    init() {
        console.log('📄 Инициализация улучшенного модуля документов');
        
        // Исправляем обработчики кликов
        this.fixClickHandlers();
        
        // Инициализируем drag & drop
        this.initDragAndDrop();
        
        // Подключаем к системе Яндекс.Диска
        this.connectToYandexDisk();
        
        console.log('✅ Модуль документов успешно инициализирован');
    }
    
    fixClickHandlers() {
        console.log('🔧 Исправление обработчиков кликов');
        
        // Находим все кнопки загрузки
        const uploadButtons = document.querySelectorAll('[onclick*="uploadFile"]');
        uploadButtons.forEach(button => {
            // Удаляем старый onclick
            button.removeAttribute('onclick');
            
            // Добавляем новый обработчик
            const fieldName = this.extractFieldName(button);
            if (fieldName) {
                button.addEventListener('click', (e) => {
                    e.preventDefault();
                    e.stopPropagation();
                    this.triggerFileUpload(fieldName);
                });
            }
        });
        
        // Исправляем кнопки замены файла
        const replaceButtons = document.querySelectorAll('[onclick*="replaceFile"]');
        replaceButtons.forEach(button => {
            button.removeAttribute('onclick');
            
            const fieldName = this.extractFieldName(button);
            if (fieldName) {
                button.addEventListener('click', (e) => {
                    e.preventDefault();
                    e.stopPropagation();
                    this.triggerFileUpload(fieldName);
                });
            }
        });
        
        // Исправляем кнопки удаления
        const deleteButtons = document.querySelectorAll('[onclick*="deleteFile"]');
        deleteButtons.forEach(button => {
            button.removeAttribute('onclick');
            
            const fieldName = this.extractFieldName(button);
            if (fieldName) {
                button.addEventListener('click', (e) => {
                    e.preventDefault();
                    e.stopPropagation();
                    this.deleteFile(fieldName);
                });
            }
        });
        
        // Инициализируем file input обработчики
        const fileInputs = document.querySelectorAll('.file-input');
        fileInputs.forEach(input => {
            input.addEventListener('change', (e) => {
                this.handleFileSelection(e.target);
            });
        });
    }
    
    extractFieldName(button) {
        // Пытаемся извлечь имя поля из различных атрибутов
        const row = button.closest('.document-row');
        if (row) {
            return row.dataset.field;
        }
        
        // Альтернативные способы
        const onclick = button.getAttribute('onclick');
        if (onclick) {
            const match = onclick.match(/['"]([\w_]+)['"]/);
            return match ? match[1] : null;
        }
        
        return null;
    }
    
    triggerFileUpload(fieldName) {
        console.log('📎 Запуск загрузки файла для поля:', fieldName);
        
        if (this.uploadInProgress) {
            alert('Дождитесь завершения текущей загрузки');
            return;
        }
        
        const fileInput = document.getElementById(fieldName);
        if (fileInput) {
            // Очищаем предыдущий выбор
            fileInput.value = '';
            
            // Программно кликаем на input
            fileInput.click();
        } else {
            console.error('❌ Не найден файловый input для поля:', fieldName);
            this.createFileInput(fieldName);
        }
    }
    
    createFileInput(fieldName) {
        console.log('🔧 Создание файлового input для поля:', fieldName);
        
        const input = document.createElement('input');
        input.type = 'file';
        input.id = fieldName;
        input.name = fieldName;
        input.className = 'file-input d-none';
        input.dataset.field = fieldName;
        
        // Устанавливаем accept
        if (this.supportedFormats[fieldName]) {
            const formats = this.supportedFormats[fieldName];
            if (formats[0] !== '*') {
                input.accept = formats.join(',');
            }
        }
        
        // Добавляем обработчик
        input.addEventListener('change', (e) => {
            this.handleFileSelection(e.target);
        });
        
        // Добавляем в соответствующую строку таблицы
        const row = document.querySelector(`[data-field="${fieldName}"]`);
        if (row) {
            const actionsCell = row.querySelector('.document-actions');
            if (actionsCell) {
                actionsCell.appendChild(input);
            }
        }
        
        // Сразу кликаем
        input.click();
    }
    
    handleFileSelection(input) {
        const file = input.files[0];
        if (!file) return;
        
        const fieldName = input.dataset.field;
        console.log('📄 Выбран файл:', file.name, 'для поля:', fieldName);
        
        // Валидация файла
        if (!this.validateFile(file, fieldName)) {
            input.value = '';
            return;
        }
        
        // Показываем прогресс
        this.showUploadProgress(fieldName);
        
        // Запускаем загрузку
        this.uploadToYandexDisk(fieldName, file);
    }
    
    validateFile(file, fieldName) {
        // Проверка размера
        const maxSize = this.maxFileSizes[fieldName] || 1500 * 1024 * 1024;
        if (file.size > maxSize) {
            const maxSizeMB = Math.round(maxSize / (1024 * 1024));
            alert(`Файл слишком большой. Максимальный размер: ${maxSizeMB}MB`);
            return false;
        }
        
        // Проверка формата
        const supportedFormats = this.supportedFormats[fieldName];
        if (supportedFormats && supportedFormats[0] !== '*') {
            const fileName = file.name.toLowerCase();
            const isSupported = supportedFormats.some(format => 
                fileName.endsWith(format.replace('.', ''))
            );
            
            if (!isSupported) {
                alert(`Неподдерживаемый формат файла. Разрешены: ${supportedFormats.join(', ')}`);
                return false;
            }
        }
        
        return true;
    }
    
    showUploadProgress(fieldName) {
        console.log('⏳ Показ прогресса загрузки для:', fieldName);
        
        this.uploadInProgress = true;
        
        // Создаем или показываем блок прогресса
        let progressContainer = document.querySelector('.upload-progress-container');
        if (!progressContainer) {
            progressContainer = this.createProgressContainer();
        }
        
        progressContainer.classList.remove('d-none');
        
        // Обновляем текст
        const statusText = progressContainer.querySelector('.upload-status');
        if (statusText) {
            statusText.textContent = `Загрузка документа: ${this.getFieldDisplayName(fieldName)}`;
        }
        
        // Сброс прогресс-бара
        const progressBar = progressContainer.querySelector('.progress-bar');
        if (progressBar) {
            progressBar.style.width = '0%';
        }
    }
    
    createProgressContainer() {
        const container = document.createElement('div');
        container.className = 'upload-progress-container';
        container.innerHTML = `
            <div class="card mt-3">
                <div class="card-body">
                    <h6><i class="fas fa-upload me-2"></i>Загрузка документа</h6>
                    <div class="progress">
                        <div class="progress-bar bg-primary" role="progressbar" style="width: 0%"></div>
                    </div>
                    <div class="mt-2">
                        <small class="text-muted upload-status">Подготовка к загрузке...</small>
                    </div>
                </div>
            </div>
        `;
        
        // Добавляем после таблицы документов
        const tableContainer = document.querySelector('.documents-table-container');
        if (tableContainer) {
            tableContainer.parentNode.insertBefore(container, tableContainer.nextSibling);
        }
        
        return container;
    }
    
    hideUploadProgress() {
        this.uploadInProgress = false;
        
        const progressContainer = document.querySelector('.upload-progress-container');
        if (progressContainer) {
            progressContainer.classList.add('d-none');
        }
    }
    
    updateUploadProgress(percentage, status) {
        const progressBar = document.querySelector('.progress-bar');
        const statusText = document.querySelector('.upload-status');
        
        if (progressBar) {
            progressBar.style.width = percentage + '%';
            progressBar.setAttribute('aria-valuenow', percentage);
        }
        
        if (statusText && status) {
            statusText.textContent = status;
        }
    }
    
    uploadToYandexDisk(fieldName, file) {
        console.log('☁️ Загрузка на Яндекс.Диск:', fieldName, file.name);
        
        // Проверяем наличие системы Яндекс.Диска
        if (!window.YandexDiskUploaderV3 || !window.yandexUploaderV3) {
            console.warn('⚠️ Система Яндекс.Диска недоступна, используем стандартную загрузку');
            this.uploadStandard(fieldName, file);
            return;
        }
        
        const uploader = window.yandexUploaderV3;
        
        uploader.uploadFile(file, fieldName, {
            onProgress: (percentage) => {
                this.updateUploadProgress(percentage, `Загрузка: ${percentage}%`);
            },
            onSuccess: (response) => {
                console.log('✅ Файл успешно загружен:', response);
                this.handleUploadSuccess(fieldName, response.url || response.downloadUrl, file.name);
            },
            onError: (error) => {
                console.error('❌ Ошибка загрузки на Яндекс.Диск:', error);
                this.handleUploadError(fieldName, error);
            }
        });
    }
    
    uploadStandard(fieldName, file) {
        console.log('📤 Стандартная загрузка файла:', fieldName);
        
        const formData = new FormData();
        formData.append(fieldName, file);
        formData.append('_token', document.querySelector('meta[name="csrf-token"]').content);
        
        const dealId = this.getDealId();
        if (dealId) {
            formData.append('deal_id', dealId);
        }
        
        const xhr = new XMLHttpRequest();
        
        xhr.upload.addEventListener('progress', (e) => {
            if (e.lengthComputable) {
                const percentage = Math.round((e.loaded / e.total) * 100);
                this.updateUploadProgress(percentage, `Загрузка: ${percentage}%`);
            }
        });
        
        xhr.addEventListener('load', () => {
            if (xhr.status === 200) {
                try {
                    const response = JSON.parse(xhr.responseText);
                    if (response.success) {
                        this.handleUploadSuccess(fieldName, response.url, file.name);
                    } else {
                        this.handleUploadError(fieldName, new Error(response.message || 'Ошибка загрузки'));
                    }
                } catch (e) {
                    this.handleUploadError(fieldName, new Error('Ошибка обработки ответа сервера'));
                }
            } else {
                this.handleUploadError(fieldName, new Error(`Ошибка сервера: ${xhr.status}`));
            }
        });
        
        xhr.addEventListener('error', () => {
            this.handleUploadError(fieldName, new Error('Ошибка сети'));
        });
        
        xhr.open('POST', '/deals/upload-document');
        xhr.send(formData);
    }
    
    handleUploadSuccess(fieldName, fileUrl, fileName) {
        console.log('✅ Успешная загрузка:', fieldName, fileUrl);
        
        this.hideUploadProgress();
        this.updateDocumentRow(fieldName, fileUrl, fileName);
        this.saveDocumentToForm(fieldName, fileUrl);
        this.updateDocumentsCounter();
        
        // Показываем уведомление
        this.showNotification('Файл успешно загружен!', 'success');
    }
    
    handleUploadError(fieldName, error) {
        console.error('❌ Ошибка загрузки:', fieldName, error);
        
        this.hideUploadProgress();
        
        // Показываем ошибку
        this.showNotification('Ошибка загрузки файла: ' + error.message, 'danger');
    }
    
    updateDocumentRow(fieldName, fileUrl, fileName) {
        const row = document.querySelector(`[data-field="${fieldName}"]`);
        if (!row) return;
        
        const statusCell = row.querySelector('.file-status');
        const actionsCell = row.querySelector('.document-actions');
        
        if (statusCell) {
            statusCell.innerHTML = `
                <div class="file-status uploaded">
                    <i class="fas fa-check-circle text-success me-1"></i>
                    <span class="text-success">Загружен</span>
                    <br><small class="text-muted">${fileName}</small>
                </div>
            `;
        }
        
        if (actionsCell) {
            const newHTML = `
                <div class="document-actions">
                    <a href="${fileUrl}" target="_blank" 
                       class="btn btn-sm btn-outline-primary me-1">
                        <i class="fas fa-eye"></i>
                    </a>
                    <button type="button" class="btn btn-sm btn-outline-secondary me-1">
                        <i class="fas fa-upload"></i>
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-danger">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
                <input type="file" 
                       class="file-input d-none" 
                       id="${fieldName}" 
                       name="${fieldName}" 
                       data-field="${fieldName}">
            `;
            
            actionsCell.innerHTML = newHTML;
            
            // Переинициализируем обработчики для новых кнопок
            this.initRowHandlers(actionsCell, fieldName);
        }
    }
    
    initRowHandlers(container, fieldName) {
        const replaceBtn = container.querySelector('.btn-outline-secondary');
        const deleteBtn = container.querySelector('.btn-outline-danger');
        const fileInput = container.querySelector('.file-input');
        
        if (replaceBtn) {
            replaceBtn.addEventListener('click', (e) => {
                e.preventDefault();
                this.triggerFileUpload(fieldName);
            });
        }
        
        if (deleteBtn) {
            deleteBtn.addEventListener('click', (e) => {
                e.preventDefault();
                this.deleteFile(fieldName);
            });
        }
        
        if (fileInput) {
            fileInput.addEventListener('change', (e) => {
                this.handleFileSelection(e.target);
            });
        }
    }
    
    deleteFile(fieldName) {
        if (!confirm('Вы уверены, что хотите удалить этот файл?')) {
            return;
        }
        
        console.log('🗑️ Удаление файла:', fieldName);
        
        // Здесь должна быть логика удаления с Яндекс.Диска
        // Пока просто обновляем интерфейс
        
        const row = document.querySelector(`[data-field="${fieldName}"]`);
        if (!row) return;
        
        const statusCell = row.querySelector('.file-status');
        const actionsCell = row.querySelector('.document-actions');
        
        if (statusCell) {
            statusCell.innerHTML = `
                <div class="file-status not-uploaded">
                    <i class="fas fa-times-circle text-danger me-1"></i>
                    <span class="text-danger">Не загружен</span>
                </div>
            `;
        }
        
        if (actionsCell) {
            const newHTML = `
                <div class="document-actions">
                    <button type="button" class="btn btn-sm btn-primary">
                        <i class="fas fa-upload me-1"></i>Загрузить
                    </button>
                </div>
                <input type="file" 
                       class="file-input d-none" 
                       id="${fieldName}" 
                       name="${fieldName}" 
                       data-field="${fieldName}">
            `;
            
            actionsCell.innerHTML = newHTML;
            
            // Переинициализируем обработчики
            this.initRowHandlers(actionsCell, fieldName);
        }
        
        // Удаляем из формы
        this.removeDocumentFromForm(fieldName);
        this.updateDocumentsCounter();
        
        this.showNotification('Файл удален', 'info');
    }
    
    saveDocumentToForm(fieldName, fileUrl) {
        const form = document.getElementById('deal-edit-form');
        if (!form) return;
        
        let hiddenInput = form.querySelector(`input[name="${fieldName}_url"]`);
        if (!hiddenInput) {
            hiddenInput = document.createElement('input');
            hiddenInput.type = 'hidden';
            hiddenInput.name = fieldName + '_url';
            form.appendChild(hiddenInput);
        }
        
        hiddenInput.value = fileUrl;
        console.log('💾 Сохранено в форму:', fieldName + '_url', '=', fileUrl);
    }
    
    removeDocumentFromForm(fieldName) {
        const form = document.getElementById('deal-edit-form');
        if (!form) return;
        
        const hiddenInput = form.querySelector(`input[name="${fieldName}_url"]`);
        if (hiddenInput) {
            hiddenInput.remove();
        }
    }
    
    updateDocumentsCounter() {
        const uploadedRows = document.querySelectorAll('.file-status.uploaded').length;
        const totalRows = document.querySelectorAll('.document-row').length;
        const badge = document.querySelector('.documents-stats .badge');
        
        if (badge) {
            badge.textContent = `${uploadedRows}/${totalRows} загружено`;
        }
    }
    
    getDealId() {
        // Пытаемся извлечь ID сделки из URL или формы
        const url = window.location.pathname;
        const match = url.match(/deal\/(\d+)/);
        if (match) {
            return match[1];
        }
        
        const form = document.getElementById('deal-edit-form');
        if (form) {
            const dealIdInput = form.querySelector('input[name="deal_id"]');
            if (dealIdInput) {
                return dealIdInput.value;
            }
        }
        
        return null;
    }
    
    getFieldDisplayName(fieldName) {
        const names = {
            'contract_file': 'Договор',
            'technical_task': 'Техническое задание',
            'project_estimate': 'Смета проекта',
            'blueprints': 'Чертежи',
            'models_3d': '3D модели',
            'presentation': 'Презентация',
            'reference_materials': 'Справочные материалы',
            'client_correspondence': 'Переписка',
            'other_documents': 'Прочие документы'
        };
        
        return names[fieldName] || fieldName;
    }
    
    showNotification(message, type = 'info') {
        // Простая система уведомлений
        const notification = document.createElement('div');
        notification.className = `alert alert-${type} alert-dismissible fade show position-fixed`;
        notification.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
        notification.innerHTML = `
            ${message}
            <button type="button" class="btn-close" aria-label="Close"></button>
        `;
        
        document.body.appendChild(notification);
        
        // Автоматическое скрытие через 5 секунд
        setTimeout(() => {
            if (notification.parentNode) {
                notification.remove();
            }
        }, 5000);
        
        // Обработчик кнопки закрытия
        const closeBtn = notification.querySelector('.btn-close');
        if (closeBtn) {
            closeBtn.addEventListener('click', () => {
                notification.remove();
            });
        }
    }
    
    initDragAndDrop() {
        console.log('🎯 Инициализация Drag & Drop');
        
        const table = document.querySelector('.documents-table tbody');
        if (!table) return;
        
        table.addEventListener('dragover', (e) => {
            e.preventDefault();
            e.stopPropagation();
            table.classList.add('drag-over');
        });
        
        table.addEventListener('dragleave', (e) => {
            e.preventDefault();
            e.stopPropagation();
            table.classList.remove('drag-over');
        });
        
        table.addEventListener('drop', (e) => {
            e.preventDefault();
            e.stopPropagation();
            table.classList.remove('drag-over');
            
            const files = Array.from(e.dataTransfer.files);
            if (files.length > 0) {
                this.handleDragAndDropFiles(files);
            }
        });
    }
    
    handleDragAndDropFiles(files) {
        console.log('📁 Обработка D&D файлов:', files.length);
        
        if (files.length === 1) {
            // Один файл - показываем модальное окно выбора типа
            this.showFileTypeModal(files[0]);
        } else {
            // Несколько файлов - автоматическое определение типа
            files.forEach(file => {
                const fieldName = this.detectFileType(file);
                if (fieldName) {
                    // Проверяем, что поле еще не заполнено
                    const row = document.querySelector(`[data-field="${fieldName}"]`);
                    const isUploaded = row && row.querySelector('.file-status.uploaded');
                    
                    if (!isUploaded) {
                        this.uploadFileToField(fieldName, file);
                    }
                }
            });
        }
    }
    
    detectFileType(file) {
        const fileName = file.name.toLowerCase();
        
        if (fileName.includes('contract') || fileName.includes('договор')) {
            return 'contract_file';
        }
        if (fileName.includes('technical') || fileName.includes('тз') || fileName.includes('техническое')) {
            return 'technical_task';
        }
        if (fileName.includes('estimate') || fileName.includes('смета')) {
            return 'project_estimate';
        }
        if (fileName.includes('blueprint') || fileName.includes('чертеж') || fileName.includes('план')) {
            return 'blueprints';
        }
        if (fileName.includes('3d') || fileName.includes('model') || fileName.includes('модель')) {
            return 'models_3d';
        }
        if (fileName.includes('presentation') || fileName.includes('презентация')) {
            return 'presentation';
        }
        if (fileName.includes('reference') || fileName.includes('справочн')) {
            return 'reference_materials';
        }
        if (fileName.includes('correspondence') || fileName.includes('переписка') || fileName.includes('chat')) {
            return 'client_correspondence';
        }
        
        // По умолчанию - прочие документы
        return 'other_documents';
    }
    
    showFileTypeModal(file) {
        // Простая модальная форма для выбора типа документа
        const modal = document.createElement('div');
        modal.className = 'modal fade show';
        modal.style.display = 'block';
        modal.innerHTML = `
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Выберите тип документа</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <p>Файл: <strong>${file.name}</strong></p>
                        <p>Выберите тип документа для загрузки:</p>
                        <select class="form-select" id="fileTypeSelect">
                            <option value="contract_file">Договор с клиентом</option>
                            <option value="technical_task">Техническое задание</option>
                            <option value="project_estimate">Смета проекта</option>
                            <option value="blueprints">Чертежи и планы</option>
                            <option value="models_3d">3D модели</option>
                            <option value="presentation">Презентация проекта</option>
                            <option value="reference_materials">Справочные материалы</option>
                            <option value="client_correspondence">Переписка с клиентом</option>
                            <option value="other_documents">Прочие документы</option>
                        </select>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                        <button type="button" class="btn btn-primary" id="uploadFileBtn">Загрузить</button>
                    </div>
                </div>
            </div>
        `;
        
        document.body.appendChild(modal);
        
        // Обработчики
        const closeBtn = modal.querySelector('.btn-close');
        const cancelBtn = modal.querySelector('.btn-secondary');
        const uploadBtn = modal.querySelector('#uploadFileBtn');
        const select = modal.querySelector('#fileTypeSelect');
        
        const closeModal = () => {
            modal.remove();
        };
        
        closeBtn.addEventListener('click', closeModal);
        cancelBtn.addEventListener('click', closeModal);
        
        uploadBtn.addEventListener('click', () => {
            const fieldName = select.value;
            this.uploadFileToField(fieldName, file);
            closeModal();
        });
        
        // Предустановка значения на основе имени файла
        const detectedType = this.detectFileType(file);
        if (detectedType) {
            select.value = detectedType;
        }
    }
    
    uploadFileToField(fieldName, file) {
        // Эмулируем выбор файла в соответствующем input
        const fileInput = document.getElementById(fieldName);
        if (fileInput) {
            // Создаем новый FileList
            const dt = new DataTransfer();
            dt.items.add(file);
            fileInput.files = dt.files;
            
            // Запускаем обработку
            this.handleFileSelection(fileInput);
        }
    }
    
    connectToYandexDisk() {
        // Проверяем доступность системы Яндекс.Диска
        if (typeof window.YandexDiskUploaderV3 === 'undefined') {
            console.warn('⚠️ YandexDiskUploaderV3 не найден');
            return;
        }
        
        // Ждем инициализации
        const checkYandexReady = () => {
            if (window.yandexUploaderV3 && window.yandexUploaderV3.isInitialized) {
                console.log('✅ Подключение к Яндекс.Диску установлено');
                return;
            }
            
            setTimeout(checkYandexReady, 500);
        };
        
        checkYandexReady();
    }
}

// Автоматическая инициализация при загрузке DOM
document.addEventListener('DOMContentLoaded', function() {
    // Задержка для того, чтобы все скрипты загрузились
    setTimeout(() => {
        window.improvedDocumentsModule = new ImprovedDocumentsModule();
    }, 1000);
});

// CSS стили для Drag & Drop
const dragDropStyles = `
<style>
.documents-table.drag-over {
    background-color: #e3f2fd;
    border: 2px dashed #2196f3;
}

.documents-table tbody tr:hover {
    background-color: #f8f9fa;
}

.alert.position-fixed {
    animation: slideInRight 0.3s ease-out;
}

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
</style>
`;

// Добавляем стили в head
document.head.insertAdjacentHTML('beforeend', dragDropStyles);
