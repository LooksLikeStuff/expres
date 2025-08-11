/**
 * Модуль для загрузки документов в локальное хранилище (storage)
 * Заменяет функциональность Яндекс.Диска
 * Версия: 1.0
 */

class LocalDocumentsUploader {
    constructor() {
        this.uploadInProgress = false;
        this.maxFileSize = 100 * 1024 * 1024; // 100MB
        this.allowedTypes = [
            'pdf', 'doc', 'docx', 'xls', 'xlsx', 
            'jpg', 'jpeg', 'png', 'zip', 'rar',
            'txt', 'rtf', 'odt', 'ods'
        ];
        
        this.init();
    }
    
    init() {
        console.log('🔧 Инициализация локального загрузчика документов');
        this.bindEvents();
    }
    
    bindEvents() {
        const uploadBtn = document.getElementById('uploadDocumentsBtn');
        const uploadInput = document.getElementById('documentUploadInput');
        const uploadArea = document.getElementById('documentsUploadArea');
        
        if (!uploadBtn || !uploadInput || !uploadArea) {
            console.warn('⚠️ Не найдены необходимые элементы для загрузки документов');
            return;
        }
        
        // Клик по кнопке выбора файлов
        uploadBtn.addEventListener('click', () => {
            uploadInput.click();
        });
        
        // Клик по области загрузки
        uploadArea.addEventListener('click', () => {
            uploadInput.click();
        });
        
        // Выбор файлов
        uploadInput.addEventListener('change', (e) => {
            const files = e.target.files;
            if (files.length > 0) {
                this.handleFileSelection(files);
            }
        });
        
        // Drag & Drop
        this.setupDragAndDrop(uploadArea, uploadInput);
    }
    
    setupDragAndDrop(uploadArea, uploadInput) {
        // Предотвращаем стандартное поведение
        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
            uploadArea.addEventListener(eventName, this.preventDefaults, false);
            document.body.addEventListener(eventName, this.preventDefaults, false);
        });
        
        // Подсветка при перетаскивании
        ['dragenter', 'dragover'].forEach(eventName => {
            uploadArea.addEventListener(eventName, () => {
                uploadArea.classList.add('drag-over');
            }, false);
        });
        
        ['dragleave', 'drop'].forEach(eventName => {
            uploadArea.addEventListener(eventName, () => {
                uploadArea.classList.remove('drag-over');
            }, false);
        });
        
        // Обработка сброса файлов
        uploadArea.addEventListener('drop', (e) => {
            const files = e.dataTransfer.files;
            if (files.length > 0) {
                this.handleFileSelection(files);
            }
        }, false);
    }
    
    preventDefaults(e) {
        e.preventDefault();
        e.stopPropagation();
    }
    
    handleFileSelection(files) {
        console.log(`📁 Выбрано файлов: ${files.length}`);
        
        // Валидация файлов
        const validationResult = this.validateFiles(files);
        if (!validationResult.valid) {
            this.showNotification(validationResult.message, 'error');
            return;
        }
        
        // Отображаем информацию о файлах
        this.displaySelectedFiles(files);
        
        // Автоматически начинаем загрузку
        this.uploadFiles(files);
    }
    
    validateFiles(files) {
        const errors = [];
        
        Array.from(files).forEach((file, index) => {
            // Проверка размера
            if (file.size > this.maxFileSize) {
                errors.push(`Файл "${file.name}" превышает максимальный размер (100MB)`);
            }
            
            // Проверка типа
            const extension = file.name.split('.').pop().toLowerCase();
            if (!this.allowedTypes.includes(extension)) {
                errors.push(`Файл "${file.name}" имеет неподдерживаемый формат`);
            }
        });
        
        return {
            valid: errors.length === 0,
            message: errors.join('\n')
        };
    }
    
    displaySelectedFiles(files) {
        const filesCountInfo = document.getElementById('filesCountInfo');
        const filesCountText = document.getElementById('filesCountText');
        const selectedFilesList = document.getElementById('selectedFilesList');
        
        if (!filesCountInfo || !filesCountText) return;
        
        // Показываем информацию о количестве файлов
        const count = files.length;
        const word = this.getFileWord(count);
        const totalSize = Array.from(files).reduce((sum, file) => sum + file.size, 0);
        
        filesCountText.textContent = `Выбрано ${count} ${word} (${this.formatFileSize(totalSize)})`;
        filesCountInfo.style.display = 'block';
        
        // Отображаем список файлов
        if (selectedFilesList) {
            selectedFilesList.innerHTML = '';
            Array.from(files).forEach((file, index) => {
                const fileElement = this.createFileElement(file, index);
                selectedFilesList.appendChild(fileElement);
            });
        }
    }
    
    createFileElement(file, index) {
        const fileElement = document.createElement('div');
        fileElement.className = 'selected-file-item d-flex justify-content-between align-items-center py-2 px-3 mb-2 border rounded';
        
        fileElement.innerHTML = `
            <div class="file-info">
                <i class="fas ${this.getFileIcon(file.name)} me-2 text-primary"></i>
                <span class="file-name">${file.name}</span>
                <small class="text-muted ms-2">(${this.formatFileSize(file.size)})</small>
            </div>
            <div class="file-status">
                <span class="badge bg-secondary">Готов к загрузке</span>
            </div>
        `;
        
        return fileElement;
    }
    
    async uploadFiles(files) {
        if (this.uploadInProgress) {
            this.showNotification('Загрузка уже выполняется', 'warning');
            return;
        }
        
        this.uploadInProgress = true;
        this.updateUploadButton(true);
        
        try {
            console.log('🚀 Начинаем загрузку файлов в локальное хранилище');
            
            // Создаем FormData
            const formData = new FormData();
            formData.append('_token', this.getCsrfToken());
            formData.append('deal_id', this.getDealId());
            
            // Добавляем файлы
            Array.from(files).forEach((file, index) => {
                formData.append('documents[]', file);
            });
            
            // Обновляем статусы файлов
            this.updateFileStatuses('Загружается...');
            
            // Отправляем запрос
            const response = await fetch(`/deal/${this.getDealId()}/upload-documents`, {
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
                console.log('✅ Файлы успешно загружены в локальное хранилище:', result);
                this.updateFileStatuses('Загружено', 'success');
                this.showNotification(result.message || 'Файлы успешно загружены', 'success');
                
                // Обновляем список документов
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
    
    updateFileStatuses(status, type = 'info') {
        const statusElements = document.querySelectorAll('.file-status .badge');
        const statusClass = type === 'success' ? 'bg-success' : 
                           type === 'error' ? 'bg-danger' : 'bg-info';
        
        statusElements.forEach(element => {
            element.className = `badge ${statusClass}`;
            element.textContent = status;
        });
    }
    
    updateUploadButton(loading) {
        const uploadBtn = document.getElementById('uploadDocumentsBtn');
        const btnText = uploadBtn?.querySelector('.upload-btn-text');
        const btnIcon = uploadBtn?.querySelector('i');
        
        if (!uploadBtn) return;
        
        if (loading) {
            uploadBtn.disabled = true;
            if (btnText) btnText.textContent = 'Загружаем...';
            if (btnIcon) btnIcon.className = 'fas fa-spinner fa-spin me-2';
        } else {
            uploadBtn.disabled = false;
            if (btnText) btnText.textContent = 'Выбрать файлы';
            if (btnIcon) btnIcon.className = 'fas fa-plus me-2';
        }
    }
    
    clearSelectedFiles() {
        const uploadInput = document.getElementById('documentUploadInput');
        const filesCountInfo = document.getElementById('filesCountInfo');
        const selectedFilesList = document.getElementById('selectedFilesList');
        
        if (uploadInput) uploadInput.value = '';
        if (filesCountInfo) filesCountInfo.style.display = 'none';
        if (selectedFilesList) selectedFilesList.innerHTML = '';
    }
    
    refreshDocumentsList(documents) {
        // Простое обновление - перезагружаем страницу
        // В будущем можно улучшить для динамического обновления
        setTimeout(() => {
            location.reload();
        }, 1000);
    }
    
    // Утилиты
    getCsrfToken() {
        return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    }
    
    getDealId() {
        const url = window.location.pathname;
        const matches = url.match(/\/deal\/(\d+)/);
        return matches ? matches[1] : null;
    }
    
    getFileWord(count) {
        if (count === 1) return 'файл';
        if (count >= 2 && count <= 4) return 'файла';
        return 'файлов';
    }
    
    formatFileSize(bytes) {
        if (bytes === 0) return '0 Байт';
        const k = 1024;
        const sizes = ['Байт', 'КБ', 'МБ', 'ГБ'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    }
    
    getFileIcon(filename) {
        const extension = filename.split('.').pop().toLowerCase();
        
        switch (extension) {
            case 'pdf': return 'fa-file-pdf';
            case 'doc':
            case 'docx': return 'fa-file-word';
            case 'xls':
            case 'xlsx': return 'fa-file-excel';
            case 'jpg':
            case 'jpeg':
            case 'png': return 'fa-file-image';
            case 'zip':
            case 'rar': return 'fa-file-archive';
            case 'txt': return 'fa-file-alt';
            default: return 'fa-file';
        }
    }
    
    showNotification(message, type = 'info') {
        // Простая система уведомлений
        console.log(`${type.toUpperCase()}: ${message}`);
        
        // Создаем уведомление
        const notification = document.createElement('div');
        notification.className = `alert alert-${type === 'error' ? 'danger' : type === 'success' ? 'success' : 'info'} alert-dismissible fade show position-fixed`;
        notification.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
        
        notification.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        
        document.body.appendChild(notification);
        
        // Автоматически удаляем через 5 секунд
        setTimeout(() => {
            if (notification.parentNode) {
                notification.remove();
            }
        }, 5000);
    }
}

// Инициализация при загрузке DOM
document.addEventListener('DOMContentLoaded', function() {
    if (document.getElementById('documentsUploadArea')) {
        console.log('🔧 Инициализация локального загрузчика документов');
        new LocalDocumentsUploader();
    }
});

// Экспортируем для глобального использования
window.LocalDocumentsUploader = LocalDocumentsUploader;
