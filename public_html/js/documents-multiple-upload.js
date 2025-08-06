/**
 * Модуль множественной загрузки документов для вкладки "Документы"
 * Поддерживает drag & drop, валидацию файлов, прогресс загрузки и интеграцию с Яндекс.Диском
 */

class DocumentsMultipleUploader {
    constructor() {
        this.uploadArea = null;
        this.uploadInput = null;
        this.uploadBtn = null;
        this.filesCountInfo = null;
        this.filesCountText = null;
        this.selectedFilesList = null;
        this.dealId = null;
        this.uploadInProgress = false;
        this.selectedFiles = [];
        
        // Настройки
        this.maxFileSize = 100 * 1024 * 1024; // 100 МБ
        this.allowedTypes = [
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'image/jpeg',
            'image/png',
            'application/zip',
            'application/x-rar-compressed'
        ];
        
        this.allowedExtensions = ['.pdf', '.doc', '.docx', '.xls', '.xlsx', '.jpg', '.jpeg', '.png', '.zip', '.rar'];
        
        this.init();
    }
    
    /**
     * Инициализация модуля
     */
    init() {
        console.log('🚀 Инициализация DocumentsMultipleUploader');
        
        // Ждем загрузки DOM
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', () => this.initElements());
        } else {
            this.initElements();
        }
    }
    
    /**
     * Инициализация элементов DOM
     */
    initElements() {
        // Находим элементы
        this.uploadArea = document.getElementById('documentsUploadArea');
        this.uploadInput = document.getElementById('documentUploadInput');
        this.uploadBtn = document.getElementById('uploadDocumentsBtn');
        this.filesCountInfo = document.getElementById('filesCountInfo');
        this.filesCountText = document.getElementById('filesCountText');
        this.selectedFilesList = document.getElementById('selectedFilesList');
        
        // Получаем ID сделки
        this.dealId = this.getDealId();
        
        if (!this.uploadArea || !this.uploadInput || !this.uploadBtn) {
            console.error('❌ Не найдены необходимые элементы для загрузки документов');
            return;
        }
        
        console.log('✅ Элементы найдены, настраиваем обработчики');
        
        this.setupEventHandlers();
        this.setupDragAndDrop();
        
        console.log('✅ DocumentsMultipleUploader инициализирован для сделки:', this.dealId);
    }
    
    /**
     * Получение ID сделки
     */
    getDealId() {
        // Пробуем получить из скрытого поля
        const dealIdField = document.getElementById('dealIdField');
        if (dealIdField && dealIdField.value) {
            return dealIdField.value;
        }
        
        // Пробуем получить из URL
        const url = window.location.pathname;
        const matches = url.match(/\/deal\/(\d+)/);
        if (matches && matches[1]) {
            return matches[1];
        }
        
        // Пробуем получить из формы
        const form = document.getElementById('deal-edit-form');
        if (form) {
            const action = form.getAttribute('action');
            const actionMatches = action ? action.match(/\/deal\/(\d+)/) : null;
            if (actionMatches && actionMatches[1]) {
                return actionMatches[1];
            }
        }
        
        console.warn('⚠️ Не удалось определить ID сделки');
        return null;
    }
    
    /**
     * Настройка обработчиков событий
     */
    setupEventHandlers() {
        // Кнопка выбора файлов
        this.uploadBtn.addEventListener('click', (e) => {
            e.preventDefault();
            e.stopPropagation();
            this.uploadInput.click();
        });
        
        // Изменение input файла
        this.uploadInput.addEventListener('change', (e) => {
            const files = Array.from(e.target.files);
            if (files.length > 0) {
                this.handleFileSelection(files);
            }
        });
        
        // Клик по области загрузки
        this.uploadArea.addEventListener('click', (e) => {
            if (e.target === this.uploadArea || e.target.closest('.upload-container')) {
                e.preventDefault();
                this.uploadInput.click();
            }
        });
    }
    
    /**
     * Настройка Drag & Drop
     */
    setupDragAndDrop() {
        // Предотвращаем стандартные действия браузера
        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
            this.uploadArea.addEventListener(eventName, this.preventDefaults, false);
            document.body.addEventListener(eventName, this.preventDefaults, false);
        });
        
        // Подсветка при наведении
        ['dragenter', 'dragover'].forEach(eventName => {
            this.uploadArea.addEventListener(eventName, () => this.highlight(), false);
        });
        
        ['dragleave', 'drop'].forEach(eventName => {
            this.uploadArea.addEventListener(eventName, () => this.unhighlight(), false);
        });
        
        // Обработка drop
        this.uploadArea.addEventListener('drop', (e) => this.handleDrop(e), false);
    }
    
    /**
     * Предотвращение стандартных действий
     */
    preventDefaults(e) {
        e.preventDefault();
        e.stopPropagation();
    }
    
    /**
     * Подсветка области при drag over
     */
    highlight() {
        this.uploadArea.style.borderColor = '#28a745';
        this.uploadArea.style.backgroundColor = '#d4edda';
    }
    
    /**
     * Убрать подсветку области
     */
    unhighlight() {
        this.uploadArea.style.borderColor = '#007bff';
        this.uploadArea.style.backgroundColor = '#f8f9fa';
    }
    
    /**
     * Обработка drop файлов
     */
    handleDrop(e) {
        const dt = e.dataTransfer;
        const files = Array.from(dt.files);
        
        if (files.length > 0) {
            console.log('📁 Перетащено файлов:', files.length);
            this.handleFileSelection(files);
        }
    }
    
    /**
     * Обработка выбора файлов
     */
    handleFileSelection(files) {
        console.log('📂 Выбрано файлов:', files.length);
        
        if (this.uploadInProgress) {
            this.showNotification('Подождите завершения текущей загрузки', 'warning');
            return;
        }
        
        // Валидация файлов
        const validationResult = this.validateFiles(files);
        if (!validationResult.valid) {
            this.showNotification(validationResult.message, 'error');
            return;
        }
        
        this.selectedFiles = files;
        this.displaySelectedFiles(files);
        this.startUpload(files);
    }
    
    /**
     * Валидация файлов
     */
    validateFiles(files) {
        const errors = [];
        
        for (let i = 0; i < files.length; i++) {
            const file = files[i];
            
            // Проверка размера
            if (file.size > this.maxFileSize) {
                errors.push(`Файл "${file.name}" слишком большой (${this.formatFileSize(file.size)}). Максимум: 100 МБ`);
            }
            
            // Проверка типа файла
            const isValidType = this.allowedTypes.includes(file.type) || 
                               this.allowedExtensions.some(ext => file.name.toLowerCase().endsWith(ext));
            
            if (!isValidType) {
                errors.push(`Файл "${file.name}" имеет неподдерживаемый формат`);
            }
        }
        
        if (errors.length > 0) {
            return {
                valid: false,
                message: errors.join('\n')
            };
        }
        
        return { valid: true };
    }
    
    /**
     * Отображение выбранных файлов
     */
    displaySelectedFiles(files) {
        if (!this.filesCountInfo || !this.filesCountText || !this.selectedFilesList) {
            return;
        }
        
        const count = files.length;
        const totalSize = files.reduce((sum, file) => sum + file.size, 0);
        
        // Обновляем текст счетчика
        const fileWord = this.getFileWord(count);
        this.filesCountText.textContent = `Выбрано ${count} ${fileWord} (${this.formatFileSize(totalSize)})`;
        
        // Создаем список файлов
        this.selectedFilesList.innerHTML = '';
        files.forEach((file, index) => {
            const fileItem = document.createElement('div');
            fileItem.className = 'selected-file-item d-flex justify-content-between align-items-center mb-1 p-2 bg-light rounded';
            fileItem.innerHTML = `
                <div class="file-info d-flex align-items-center">
                    <i class="${this.getFileIcon(file)} me-2"></i>
                    <span class="file-name fw-semibold">${file.name}</span>
                    <small class="text-muted ms-2">(${this.formatFileSize(file.size)})</small>
                </div>
                <div class="file-status">
                    <span class="badge bg-primary">Готов к загрузке</span>
                </div>
            `;
            this.selectedFilesList.appendChild(fileItem);
        });
        
        // Показываем блок с информацией
        this.filesCountInfo.style.display = 'block';
    }
    
    /**
     * Начало загрузки файлов
     */
    async startUpload(files) {
        if (!this.dealId) {
            this.showNotification('Не удалось определить ID сделки', 'error');
            return;
        }
        
        this.uploadInProgress = true;
        this.updateUploadButton(true);
        
        try {
            console.log('🚀 Начинаем загрузку файлов на сервер');
            
            // Создаем FormData
            const formData = new FormData();
            formData.append('_token', this.getCsrfToken());
            formData.append('deal_id', this.dealId);
            
            // Добавляем файлы
            files.forEach((file, index) => {
                formData.append('documents[]', file);
            });
            
            // Обновляем статусы файлов
            this.updateFileStatuses('Загружаем...');
            
            // Отправляем запрос
            const response = await fetch(`/deal/${this.dealId}/upload-documents`, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }
            
            const result = await response.json();
            
            if (result.success) {
                console.log('✅ Файлы успешно загружены:', result);
                this.updateFileStatuses('Загружено', 'success');
                this.showNotification(result.message || 'Файлы успешно загружены', 'success');
                
                // Обновляем список загруженных документов
                setTimeout(() => {
                    this.refreshDocumentsList(result.documents);
                    this.clearSelectedFiles();
                }, 1500);
                
            } else {
                throw new Error(result.message || 'Ошибка при загрузке файлов');
            }
            
        } catch (error) {
            console.error('❌ Ошибка загрузки:', error);
            this.updateFileStatuses('Ошибка', 'error');
            this.showNotification(`Ошибка загрузки: ${error.message}`, 'error');
        } finally {
            this.uploadInProgress = false;
            this.updateUploadButton(false);
        }
    }
    
    /**
     * Обновление кнопки загрузки
     */
    updateUploadButton(uploading) {
        const btnText = this.uploadBtn.querySelector('.upload-btn-text');
        const btnIcon = this.uploadBtn.querySelector('i');
        
        if (uploading) {
            if (btnText) btnText.textContent = 'Загружаем...';
            if (btnIcon) {
                btnIcon.className = 'fas fa-spinner fa-spin me-1';
            }
            this.uploadBtn.disabled = true;
        } else {
            if (btnText) btnText.textContent = 'Выбрать файлы';
            if (btnIcon) {
                btnIcon.className = 'fas fa-plus me-1';
            }
            this.uploadBtn.disabled = false;
        }
    }
    
    /**
     * Обновление статусов файлов
     */
    updateFileStatuses(status, type = 'primary') {
        const statusElements = this.selectedFilesList.querySelectorAll('.file-status .badge');
        statusElements.forEach(element => {
            element.textContent = status;
            element.className = `badge bg-${type}`;
        });
    }
    
    /**
     * Очистка выбранных файлов
     */
    clearSelectedFiles() {
        this.selectedFiles = [];
        this.uploadInput.value = '';
        if (this.filesCountInfo) {
            this.filesCountInfo.style.display = 'none';
        }
        if (this.selectedFilesList) {
            this.selectedFilesList.innerHTML = '';
        }
    }
    
    /**
     * Обновление списка загруженных документов
     */
    refreshDocumentsList(newDocuments) {
        console.log('🔄 Обновляем список документов');
        
        // Попробуем обновить страницу мягко или перезагрузить секцию документов
        const documentsSection = document.querySelector('.uploaded-documents-section');
        if (documentsSection && newDocuments && newDocuments.length > 0) {
            // Добавляем новые документы в список
            this.addDocumentsToList(newDocuments);
        } else {
            // Если не получается обновить мягко, перезагружаем страницу
            console.log('🔄 Перезагружаем страницу для отображения новых документов');
            setTimeout(() => window.location.reload(), 1000);
        }
    }
    
    /**
     * Добавление документов в список
     */
    addDocumentsToList(documents) {
        const uploadedSection = document.querySelector('.uploaded-documents-section');
        if (!uploadedSection) return;
        
        let filesContainer = uploadedSection.querySelector('.uploaded-files');
        if (!filesContainer) {
            // Создаем контейнер если его нет
            filesContainer = document.createElement('div');
            filesContainer.className = 'uploaded-files';
            uploadedSection.appendChild(filesContainer);
        }
        
        // Добавляем каждый новый документ
        documents.forEach(doc => {
            const fileItem = document.createElement('div');
            fileItem.className = 'file-item card mb-2 new-upload';
            fileItem.innerHTML = `
                <div class="card-body py-2">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <i class="${doc.icon || 'fas fa-file'} me-2"></i>
                            <strong>${doc.name}</strong>
                            <small class="text-muted">(Загружен только что)</small>
                        </div>
                        <div>
                            <a href="${doc.url}" target="_blank" class="btn btn-sm btn-outline-primary me-1">
                                <i class="fas fa-eye"></i>
                            </a>
                            <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeDocument('${doc.path}')">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                </div>
            `;
            
            // Добавляем анимацию появления
            fileItem.style.opacity = '0';
            fileItem.style.transform = 'translateY(20px)';
            filesContainer.appendChild(fileItem);
            
            // Анимация
            setTimeout(() => {
                fileItem.style.transition = 'all 0.3s ease';
                fileItem.style.opacity = '1';
                fileItem.style.transform = 'translateY(0)';
            }, 100);
        });
    }
    
    /**
     * Получение CSRF токена
     */
    getCsrfToken() {
        const meta = document.querySelector('meta[name="csrf-token"]');
        return meta ? meta.getAttribute('content') : '';
    }
    
    /**
     * Форматирование размера файла
     */
    formatFileSize(bytes) {
        if (bytes === 0) return '0 Байт';
        
        const k = 1024;
        const sizes = ['Байт', 'КБ', 'МБ', 'ГБ'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    }
    
    /**
     * Получение правильного склонения слова "файл"
     */
    getFileWord(count) {
        if (count % 10 === 1 && count % 100 !== 11) {
            return 'файл';
        } else if ([2, 3, 4].includes(count % 10) && ![12, 13, 14].includes(count % 100)) {
            return 'файла';
        } else {
            return 'файлов';
        }
    }
    
    /**
     * Получение иконки файла по типу
     */
    getFileIcon(file) {
        const extension = file.name.split('.').pop().toLowerCase();
        
        switch (extension) {
            case 'pdf':
                return 'fas fa-file-pdf text-danger';
            case 'doc':
            case 'docx':
                return 'fas fa-file-word text-primary';
            case 'xls':
            case 'xlsx':
                return 'fas fa-file-excel text-success';
            case 'jpg':
            case 'jpeg':
            case 'png':
                return 'fas fa-file-image text-info';
            case 'zip':
            case 'rar':
                return 'fas fa-file-archive text-warning';
            default:
                return 'fas fa-file text-secondary';
        }
    }
    
    /**
     * Показ уведомления
     */
    showNotification(message, type = 'info') {
        // Создаем уведомление Bootstrap toast
        const toastContainer = this.getOrCreateToastContainer();
        
        const toastId = 'toast-' + Date.now();
        const toastHtml = `
            <div id="${toastId}" class="toast" role="alert" aria-live="assertive" aria-atomic="true" data-bs-delay="5000">
                <div class="toast-header bg-${type === 'error' ? 'danger' : type === 'success' ? 'success' : type === 'warning' ? 'warning' : 'primary'} text-white">
                    <i class="fas fa-${type === 'error' ? 'exclamation-circle' : type === 'success' ? 'check-circle' : type === 'warning' ? 'exclamation-triangle' : 'info-circle'} me-2"></i>
                    <strong class="me-auto">Документы</strong>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast"></button>
                </div>
                <div class="toast-body">
                    ${message}
                </div>
            </div>
        `;
        
        toastContainer.insertAdjacentHTML('beforeend', toastHtml);
        
        // Показываем toast
        const toastElement = document.getElementById(toastId);
        if (typeof bootstrap !== 'undefined' && bootstrap.Toast) {
            const toast = new bootstrap.Toast(toastElement);
            toast.show();
            
            // Удаляем элемент после закрытия
            toastElement.addEventListener('hidden.bs.toast', () => {
                toastElement.remove();
            });
        } else {
            // Fallback для случая если Bootstrap не загружен
            console.log(`${type.toUpperCase()}: ${message}`);
            alert(message);
            toastElement.remove();
        }
    }
    
    /**
     * Получение или создание контейнера для toast уведомлений
     */
    getOrCreateToastContainer() {
        let container = document.getElementById('toast-container');
        if (!container) {
            container = document.createElement('div');
            container.id = 'toast-container';
            container.className = 'toast-container position-fixed top-0 end-0 p-3';
            container.style.zIndex = '9999';
            document.body.appendChild(container);
        }
        return container;
    }
}

// Автоматическая инициализация при загрузке страницы
document.addEventListener('DOMContentLoaded', () => {
    // Проверяем, что мы на странице редактирования сделки с вкладкой документов
    if (document.getElementById('documentsUploadArea')) {
        console.log('📄 Инициализируем систему множественной загрузки документов');
        window.documentsUploader = new DocumentsMultipleUploader();
    }
});

// Экспорт для глобального использования
window.DocumentsMultipleUploader = DocumentsMultipleUploader;
