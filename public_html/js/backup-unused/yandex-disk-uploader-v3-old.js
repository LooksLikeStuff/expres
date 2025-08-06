/**
 * Новая система загрузки больших файлов на Яндекс.Диск v3.0
 * Поддержка файлов до 2GB без таймаутов
 * Полностью переписанная с нуля для максимальной надежности
 */
class YandexDiskUploaderV3 {
    constructor() {
        this.apiEndpoints = {
            upload: '/api/yandex-disk/upload',
            delete: '/api/yandex-disk/delete', 
            info: '/api/yandex-disk/info',
            health: '/api/yandex-disk/health'
        };
        
        // Настройки загрузки
        this.settings = {
            maxFileSize: 2 * 1024 * 1024 * 1024, // 2GB
            chunkSize: 2 * 1024 * 1024, // 2MB для отображения прогресса
            maxRetries: 3,
            retryDelay: 2000,
            timeout: 0, // Без таймаута
            supportedFields: [
                'measurements_file',
                'final_project_file', 
                'work_act',
                'chat_screenshot',
                'archicad_file',
                'execution_order_file',
                'final_floorplan',
                'final_collage',
                'contract_attachment',
                'screenshot_work_1',
                'screenshot_work_2',
                'screenshot_work_3',
                'screenshot_work_4',
                'screenshot_work_5'
            ]
        };
        
        // Состояние загрузок
        this.activeUploads = new Map();
        this.uploadProgress = new Map();
        this.isInitialized = false;
        
        // Инициализация
        this.init();
    }
    
    /**
     * Инициализация системы загрузки
     */
    init() {
        if (this.isInitialized) {
            console.log('🔄 YandexDiskUploaderV3 уже инициализирован');
            return;
        }
        
        console.log('🚀 Инициализация YandexDiskUploaderV3...');
        
        // Добавляем обработчики событий
        this.attachEventHandlers();
        
        // Инициализируем существующие поля
        this.initializeExistingFields();
        
        // Проверяем состояние сервиса
        this.checkServiceHealth();
        
        this.isInitialized = true;
        console.log('✅ YandexDiskUploaderV3 успешно инициализирован');
    }
    
    /**
     * Привязка обработчиков событий
     */
    attachEventHandlers() {
        // Обработчик для полей загрузки файлов
        $(document).on('change', 'input[type="file"]', (event) => {
            const input = event.target;
            const fieldName = this.extractFieldName(input);
            
            if (this.settings.supportedFields.includes(fieldName)) {
                console.log('📁 Выбран файл для поля:', fieldName);
                this.handleFileSelection(input);
            }
        });
        
        // Обработчик для кнопок удаления файлов
        $(document).on('click', '.delete-yandex-file', (event) => {
            event.preventDefault();
            const button = $(event.target);
            const fieldName = button.data('field');
            const dealId = this.extractDealId();
            
            if (dealId && fieldName) {
                this.confirmAndDeleteFile(dealId, fieldName);
            }
        });
        
        // Обработчик для отмены загрузки
        $(document).on('click', '.cancel-upload', (event) => {
            event.preventDefault();
            const button = $(event.target);
            const fieldName = button.data('field');
            
            this.cancelUpload(fieldName);
        });
        
        // Обработчик отправки формы
        $(document).on('submit', '#update-deal-form', (event) => {
            // Проверяем, есть ли активные загрузки
            if (this.activeUploads.size > 0) {
                event.preventDefault();
                this.showNotification('Дождитесь завершения загрузки файлов', 'warning');
                return false;
            }
        });
    }
    
    /**
     * Инициализация существующих полей файлов
     */
    initializeExistingFields() {
        this.settings.supportedFields.forEach(fieldName => {
            const input = $(`input[name="${fieldName}"]`);
            if (input.length > 0) {
                this.enhanceFileInput(input[0], fieldName);
                this.updateFileStatus(fieldName);
            }
        });
    }
    
    /**
     * Улучшение поля загрузки файла
     */
    enhanceFileInput(input, fieldName) {
        const $input = $(input);
        const $container = $input.closest('.form-group-deal');
        
        // Добавляем контейнер для статуса загрузки
        if (!$container.find('.upload-status').length) {
            $container.append(`
                <div class="upload-status" id="upload-status-${fieldName}" style="display: none;">
                    <div class="progress-container">
                        <div class="progress">
                            <div class="progress-bar" role="progressbar" style="width: 0%"></div>
                        </div>
                        <div class="upload-info">
                            <span class="upload-text">Подготовка к загрузке...</span>
                            <button type="button" class="btn btn-sm btn-outline-danger cancel-upload" data-field="${fieldName}">
                                ✕ Отменить
                            </button>
                        </div>
                    </div>
                </div>
            `);
        }
        
        // Добавляем контейнер для успешной загрузки
        if (!$container.find('.file-success').length) {
            $container.append(`
                <div class="file-success" id="file-success-${fieldName}" style="display: none;">
                    <div class="success-info">
                        <i class="fas fa-cloud-download-alt text-success"></i>
                        <a href="#" target="_blank" class="file-link">
                            <span class="file-name">Файл загружен</span>
                        </a>
                        <button type="button" class="btn btn-sm btn-outline-danger delete-yandex-file" data-field="${fieldName}">
                            🗑️ Удалить
                        </button>
                    </div>
                </div>
            `);
        }
    }
    
    /**
     * Обработка выбора файла
     */
    async handleFileSelection(input) {
        const file = input.files[0];
        if (!file) return;
        
        const fieldName = this.extractFieldName(input);
        const dealId = this.extractDealId();
        
        console.log('📂 Обрабатываем выбранный файл:', {
            fileName: file.name,
            fileSize: this.formatBytes(file.size),
            fieldName: fieldName,
            dealId: dealId
        });
        
        // Валидация файла
        const validation = this.validateFile(file);
        if (!validation.valid) {
            this.showNotification(validation.message, 'error');
            input.value = '';
            return;
        }
        
        // Подтверждение для больших файлов (>100MB)
        if (file.size > 100 * 1024 * 1024) {
            const confirmed = await this.confirmLargeFileUpload(file);
            if (!confirmed) {
                input.value = '';
                return;
            }
        }
        
        // Начинаем загрузку
        this.startUpload(file, dealId, fieldName);
    }
    
    /**
     * Валидация файла
     */
    validateFile(file) {
        if (!file) {
            return { valid: false, message: 'Файл не выбран' };
        }
        
        if (file.size > this.settings.maxFileSize) {
            return { 
                valid: false, 
                message: `Файл слишком большой. Максимальный размер: ${this.formatBytes(this.settings.maxFileSize)}` 
            };
        }
        
        if (file.size === 0) {
            return { valid: false, message: 'Файл пустой' };
        }
        
        return { valid: true };
    }
    
    /**
     * Подтверждение загрузки большого файла
     */
    async confirmLargeFileUpload(file) {
        return new Promise((resolve) => {
            const modal = $(`
                <div class="modal fade" id="confirmLargeUploadModal" tabindex="-1">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">🚀 Загрузка большого файла</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <div class="alert alert-info">
                                    <h6>📋 Информация о файле:</h6>
                                    <ul class="mb-2">
                                        <li><strong>Имя:</strong> ${file.name}</li>
                                        <li><strong>Размер:</strong> ${this.formatBytes(file.size)}</li>
                                    </ul>
                                    <p class="mb-0">
                                        <i class="fas fa-info-circle"></i> 
                                        Загрузка большого файла может занять время. 
                                        Не закрывайте страницу во время загрузки.
                                    </p>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                    ❌ Отмена
                                </button>
                                <button type="button" class="btn btn-primary confirm-upload">
                                    🚀 Загрузить файл
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            `);
            
            $('body').append(modal);
            modal.modal('show');
            
            modal.find('.confirm-upload').on('click', () => {
                modal.modal('hide');
                resolve(true);
            });
            
            modal.on('hidden.bs.modal', () => {
                modal.remove();
                resolve(false);
            });
        });
    }
    
    /**
     * Начало загрузки файла
     */
    async startUpload(file, dealId, fieldName) {
        const uploadId = `${dealId}_${fieldName}_${Date.now()}`;
        
        console.log('🚀 Начинаем загрузку файла:', {
            uploadId,
            fileName: file.name,
            fileSize: this.formatBytes(file.size),
            dealId,
            fieldName
        });
        
        // Добавляем в активные загрузки
        this.activeUploads.set(uploadId, {
            file,
            dealId,
            fieldName,
            startTime: Date.now(),
            xhr: null
        });
        
        try {
            // Показываем прогресс
            this.showUploadProgress(fieldName);
            this.updateUploadProgress(fieldName, 0, 'Подготовка к загрузке...');
            
            // Создаем FormData
            const formData = new FormData();
            formData.append('file', file);
            formData.append('deal_id', dealId);
            formData.append('field_name', fieldName);
            
            // Выполняем загрузку
            const result = await this.performUpload(formData, fieldName, uploadId);
            
            if (result.success) {
                console.log('✅ Файл успешно загружен:', result.data);
                
                this.showUploadSuccess(fieldName, result.data);
                this.showNotification(
                    `Файл "${result.data.original_name}" успешно загружен (${result.data.upload_time}с)`,
                    'success'
                );
                
                // Дополнительно обновляем ссылки, если есть данные сделки в ответе
                if (result.deal && window.updateAllYandexFileLinks) {
                    console.log('🔄 Обновляем все ссылки из данных сделки');
                    window.updateAllYandexFileLinks(result.deal);
                }
            } else {
                throw new Error(result.error);
            }
            
        } catch (error) {
            console.error('❌ Ошибка загрузки файла:', error);
            this.showUploadError(fieldName, error.message);
            this.showNotification(`Ошибка загрузки: ${error.message}`, 'error');
        } finally {
            // Убираем из активных загрузок
            this.activeUploads.delete(uploadId);
            
            // Скрываем прогресс через 3 секунды
            setTimeout(() => {
                this.hideUploadProgress(fieldName);
            }, 3000);
        }
    }
    
    /**
     * Выполнение загрузки файла
     */
    performUpload(formData, fieldName, uploadId) {
        return new Promise((resolve, reject) => {
            const xhr = new XMLHttpRequest();
            
            // Сохраняем ссылку на XHR для отмены
            const uploadInfo = this.activeUploads.get(uploadId);
            if (uploadInfo) {
                uploadInfo.xhr = xhr;
            }
            
            // Настройки XHR
            xhr.timeout = this.settings.timeout;
            
            // Обработчик прогресса загрузки
            xhr.upload.addEventListener('progress', (e) => {
                if (e.lengthComputable) {
                    const percent = Math.round((e.loaded / e.total) * 100);
                    const elapsed = (Date.now() - uploadInfo?.startTime) / 1000;
                    const speed = e.loaded / elapsed;
                    const remaining = (e.total - e.loaded) / speed;
                    
                    const statusText = `${percent}% • ${this.formatBytes(speed)}/с • ${this.formatTime(remaining)} осталось`;
                    
                    this.updateUploadProgress(fieldName, percent, statusText);
                }
            });
            
            // Обработчик начала загрузки
            xhr.upload.addEventListener('loadstart', () => {
                console.log('📤 Начало загрузки файла на сервер');
                this.updateUploadProgress(fieldName, 0, 'Начало загрузки...');
            });
            
            // Обработчик завершения загрузки
            xhr.addEventListener('load', () => {
                try {
                    const response = JSON.parse(xhr.responseText);
                    if (xhr.status >= 200 && xhr.status < 300) {
                        resolve(response);
                    } else {
                        reject(new Error(response.error || `HTTP ${xhr.status}`));
                    }
                } catch (error) {
                    reject(new Error('Ошибка парсинга ответа сервера'));
                }
            });
            
            // Обработчики ошибок
            xhr.addEventListener('error', () => {
                reject(new Error('Ошибка сети при загрузке файла'));
            });
            
            xhr.addEventListener('timeout', () => {
                reject(new Error('Превышено время ожидания загрузки'));
            });
            
            xhr.addEventListener('abort', () => {
                reject(new Error('Загрузка была отменена'));
            });
            
            // Отправляем запрос
            xhr.open('POST', this.apiEndpoints.upload, true);
            xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
            
            // CSRF токен
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            if (csrfToken) {
                xhr.setRequestHeader('X-CSRF-TOKEN', csrfToken);
            }
            
            xhr.send(formData);
        });
    }
    
    /**
     * Отмена загрузки
     */
    cancelUpload(fieldName) {
        console.log('❌ Отмена загрузки для поля:', fieldName);
        
        // Найдем активную загрузку для этого поля
        for (const [uploadId, uploadInfo] of this.activeUploads.entries()) {
            if (uploadInfo.fieldName === fieldName && uploadInfo.xhr) {
                uploadInfo.xhr.abort();
                this.activeUploads.delete(uploadId);
                break;
            }
        }
        
        this.hideUploadProgress(fieldName);
        this.showNotification('Загрузка отменена', 'info');
    }
    
    /**
     * Удаление файла
     */
    async confirmAndDeleteFile(dealId, fieldName) {
        const confirmed = confirm('Вы уверены, что хотите удалить этот файл с Яндекс.Диска?');
        if (!confirmed) return;
        
        try {
            console.log('🗑️ Удаление файла:', { dealId, fieldName });
            
            const response = await fetch(this.apiEndpoints.delete, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
                },
                body: JSON.stringify({
                    deal_id: dealId,
                    field_name: fieldName
                })
            });
            
            const result = await response.json();
            
            if (result.success) {
                this.showFileDeleted(fieldName);
                this.showNotification('Файл успешно удален', 'success');
                console.log('✅ Файл удален:', { dealId, fieldName });
            } else {
                throw new Error(result.error);
            }
            
        } catch (error) {
            console.error('❌ Ошибка удаления файла:', error);
            this.showNotification(`Ошибка удаления: ${error.message}`, 'error');
        }
    }
    
    /**
     * Отображение прогресса загрузки
     */
    showUploadProgress(fieldName) {
        $(`#upload-status-${fieldName}`).show();
        $(`#file-success-${fieldName}`).hide();
    }
    
    /**
     * Обновление прогресса загрузки
     */
    updateUploadProgress(fieldName, percent, statusText) {
        const $container = $(`#upload-status-${fieldName}`);
        
        $container.find('.progress-bar').css('width', `${percent}%`);
        $container.find('.upload-text').text(statusText);
    }
    
    /**
     * Скрытие прогресса загрузки
     */
    hideUploadProgress(fieldName) {
        $(`#upload-status-${fieldName}`).hide();
    }
    
    /**
     * Отображение успешной загрузки
     */
    showUploadSuccess(fieldName, data) {
        this.hideUploadProgress(fieldName);
        
        console.log('✅ YandexDiskUploaderV3.showUploadSuccess:', { fieldName, data });
        
        // Используем новую универсальную систему обновления ссылок
        if (window.updateYandexFileLink) {
            window.updateYandexFileLink(fieldName, data.yandex_disk_url, data.original_name);
        } else {
            // Fallback к старой системе, если новая не загружена
            console.warn('⚠️ Новая система обновления ссылок не найдена, используем fallback');
            const $success = $(`#file-success-${fieldName}`);
            if ($success.length > 0) {
                $success.find('.file-name').text(data.original_name);
                $success.find('.file-link').attr('href', data.yandex_disk_url);
                $success.show();
                $success.addClass('animate__animated animate__fadeIn');
            }
        }
        
        // Триггерим событие для других систем
        $(document).trigger('yandexFileUploaded', {
            fieldName: fieldName,
            data: data
        });
    }
    
    /**
     * Отображение ошибки загрузки
     */
    showUploadError(fieldName, errorMessage) {
        this.hideUploadProgress(fieldName);
        
        const $container = $(`input[name="${fieldName}"]`).closest('.form-group-deal');
        $container.find('.upload-status').html(`
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-triangle"></i>
                Ошибка загрузки: ${errorMessage}
            </div>
        `).show();
        
        // Скрываем через 5 секунд
        setTimeout(() => {
            $container.find('.upload-status').hide();
        }, 5000);
    }
    
    /**
     * Отображение удаленного файла
     */
    showFileDeleted(fieldName) {
        $(`#file-success-${fieldName}`).hide();
        
        // Очищаем поле файла
        $(`input[name="${fieldName}"]`).val('');
    }
    
    /**
     * Обновление статуса файла
     */
    async updateFileStatus(fieldName) {
        const dealId = this.extractDealId();
        if (!dealId) return;
        
        try {
            const response = await fetch(`${this.apiEndpoints.info}?deal_id=${dealId}&field_name=${fieldName}`);
            const result = await response.json();
            
            if (result.success && result.data) {
                this.showUploadSuccess(fieldName, result.data);
            }
        } catch (error) {
            console.debug('Файл не найден или ошибка получения информации:', error.message);
        }
    }
    
    /**
     * Проверка состояния сервиса
     */
    async checkServiceHealth() {
        try {
            const response = await fetch(this.apiEndpoints.health);
            const result = await response.json();
            
            if (result.success) {
                console.log('💚 Сервис Яндекс.Диска работает корректно:', result.yandex_disk);
            } else {
                console.warn('⚠️ Проблемы с сервисом Яндекс.Диска:', result.error);
            }
        } catch (error) {
            console.error('❌ Ошибка проверки состояния сервиса:', error);
        }
    }
    
    /**
     * Извлечение имени поля
     */
    extractFieldName(input) {
        return $(input).attr('name') || '';
    }
    
    /**
     * Извлечение ID сделки
     */
    extractDealId() {
        // Пытаемся найти ID сделки разными способами
        const dealIdInput = $('input[name="deal_id"]');
        if (dealIdInput.length > 0) {
            return dealIdInput.val();
        }
        
        // Из URL
        const urlMatch = window.location.href.match(/deal[s]?\/(\d+)/);
        if (urlMatch) {
            return urlMatch[1];
        }
        
        // Из data-атрибута формы
        const form = $('#update-deal-form, #edit-deal-form');
        if (form.length > 0) {
            return form.data('deal-id');
        }
        
        console.warn('⚠️ Не удалось определить ID сделки');
        return null;
    }
    
    /**
     * Форматирование размера файла
     */
    formatBytes(bytes, decimals = 2) {
        if (bytes === 0) return '0 Bytes';
        
        const k = 1024;
        const dm = decimals < 0 ? 0 : decimals;
        const sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB'];
        
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        
        return parseFloat((bytes / Math.pow(k, i)).toFixed(dm)) + ' ' + sizes[i];
    }
    
    /**
     * Форматирование времени
     */
    formatTime(seconds) {
        if (!seconds || seconds < 0) return '∞';
        
        if (seconds < 60) return `${Math.round(seconds)}с`;
        if (seconds < 3600) return `${Math.round(seconds / 60)}м`;
        return `${Math.round(seconds / 3600)}ч`;
    }
    
    /**
     * Показ уведомлений
     */
    showNotification(message, type = 'info') {
        // Создаем контейнер для уведомлений если его нет
        if (!$('.notification-container').length) {
            $('body').append('<div class="notification-container" style="position: fixed; top: 20px; right: 20px; z-index: 9999;"></div>');
        }
        
        const typeClasses = {
            success: 'alert-success',
            error: 'alert-danger',
            warning: 'alert-warning',
            info: 'alert-info'
        };
        
        const notification = $(`
            <div class="alert ${typeClasses[type]} alert-dismissible fade show animate__animated animate__fadeInRight" role="alert">
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `);
        
        $('.notification-container').append(notification);
        
        // Автоматическое скрытие через 5 секунд
        setTimeout(() => {
            notification.addClass('animate__fadeOutRight');
            setTimeout(() => notification.remove(), 500);
        }, 5000);
    }
}

// Глобальная инициализация
window.YandexDiskUploaderV3 = YandexDiskUploaderV3;

// Автоматическая инициализация при загрузке документа
$(document).ready(function() {
    if (typeof window.yandexDiskUploaderV3 === 'undefined') {
        window.yandexDiskUploaderV3 = new YandexDiskUploaderV3();
        console.log('🎯 Глобальный экземпляр YandexDiskUploaderV3 создан');
    }
});

// Реинициализация при загрузке модального окна
$(document).on('shown.bs.modal', '#editModal', function() {
    if (window.yandexDiskUploaderV3 && window.yandexDiskUploaderV3.isInitialized) {
        window.yandexDiskUploaderV3.initializeExistingFields();
        console.log('🔄 YandexDiskUploaderV3 переинициализирован для модального окна');
    }
});

console.log('📦 YandexDiskUploaderV3 загружен и готов к использованию');
