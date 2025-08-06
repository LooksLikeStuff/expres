/**
 * Система загрузки файлов на Яндекс.Диск v3.0
 * Поддержка больших файлов и интеграция с формой редактирования сделки
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
                'plan_final',
                'screenshot_work_1',
                'screenshot_work_2',
                'screenshot_work_3',
                'screenshot_final'
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
    async init() {
        console.log('🔧 Инициализация YandexDiskUploaderV3...');
        
        try {
            // Проверяем доступность API
            await this.checkHealth();
            
            // Инициализируем обработчики событий
            this.initEventHandlers();
            
            // Инициализируем отображение существующих ссылок
            this.initExistingLinks();
            
            this.isInitialized = true;
            console.log('✅ YandexDiskUploaderV3 успешно инициализирована');
            
            // Интеграция с глобальной системой
            window.YandexDiskUniversal = this;
            
        } catch (error) {
            console.error('❌ Ошибка инициализации YandexDiskUploaderV3:', error);
        }
    }
    
    /**
     * Проверка состояния API
     */
    async checkHealth() {
        try {
            const response = await fetch(this.apiEndpoints.health, {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Content-Type': 'application/json'
                }
            });
            
            if (!response.ok) {
                throw new Error(`API недоступно: ${response.status}`);
            }
            
            const data = await response.json();
            console.log('✅ Yandex Disk API доступно:', data);
            
        } catch (error) {
            console.warn('⚠️ Предупреждение: Yandex Disk API может быть недоступно:', error);
            // Не прерываем инициализацию, API может быть доступно позже
        }
    }
    
    /**
     * Инициализация обработчиков событий
     */
    initEventHandlers() {
        document.addEventListener('change', (event) => {
            const input = event.target;
            
            if (input.type === 'file' && this.isYandexField(input)) {
                this.handleFileSelect(input);
            }
        });
        
        console.log('🔧 Обработчики событий инициализированы');
    }
    
    /**
     * Проверка, относится ли поле к Яндекс.Диску
     */
    isYandexField(input) {
        return input.classList.contains('yandex-upload') ||
               input.getAttribute('data-upload-type') === 'yandex' ||
               this.settings.supportedFields.includes(input.name);
    }
    
    /**
     * Обработка выбора файла
     */
    async handleFileSelect(input) {
        const file = input.files[0];
        if (!file) return;
        
        const fieldName = input.name;
        console.log(`📁 Выбран файл для загрузки: ${fieldName} - ${file.name}`);
        
        // Проверяем размер файла
        if (file.size > this.settings.maxFileSize) {
            this.showError(fieldName, `Файл слишком большой. Максимальный размер: ${this.formatBytes(this.settings.maxFileSize)}`);
            return;
        }
        
        // Получаем ID сделки
        const dealId = this.getDealId();
        if (!dealId) {
            this.showError(fieldName, 'Не удалось определить ID сделки');
            return;
        }
        
        // Показываем индикатор загрузки
        this.showLoadingIndicator(fieldName, file.name);
        
        try {
            // Загружаем файл
            const result = await this.uploadFile(file, dealId, fieldName);
            
            if (result.success) {
                console.log(`✅ Файл ${fieldName} успешно загружен`);
                this.showSuccess(fieldName, result.data);
                this.updateFileLink(fieldName, result.data.yandex_disk_url, result.data.original_name);
            } else {
                throw new Error(result.error || 'Неизвестная ошибка загрузки');
            }
            
        } catch (error) {
            console.error(`❌ Ошибка загрузки файла ${fieldName}:`, error);
            this.showError(fieldName, error.message);
        } finally {
            this.hideLoadingIndicator(fieldName);
        }
    }
    
    /**
     * Загрузка файла на Яндекс.Диск
     */
    async uploadFile(file, dealId, fieldName) {
        return new Promise((resolve, reject) => {
            const formData = new FormData();
            formData.append('file', file);
            formData.append('deal_id', dealId);
            formData.append('field_name', fieldName);
            
            const xhr = new XMLHttpRequest();
            
            // Отслеживание прогресса
            xhr.upload.addEventListener('progress', (event) => {
                if (event.lengthComputable) {
                    const percentComplete = (event.loaded / event.total) * 100;
                    this.updateProgress(fieldName, percentComplete);
                }
            });
            
            xhr.addEventListener('load', () => {
                if (xhr.status === 200) {
                    try {
                        const response = JSON.parse(xhr.responseText);
                        resolve(response);
                    } catch (error) {
                        reject(new Error('Ошибка парсинга ответа сервера'));
                    }
                } else {
                    reject(new Error(`Ошибка сервера: ${xhr.status}`));
                }
            });
            
            xhr.addEventListener('error', () => {
                reject(new Error('Ошибка сети'));
            });
            
            xhr.addEventListener('timeout', () => {
                reject(new Error('Превышено время ожидания'));
            });
            
            xhr.open('POST', this.apiEndpoints.upload);
            xhr.setRequestHeader('X-CSRF-TOKEN', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));
            xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
            
            xhr.send(formData);
        });
    }
    
    /**
     * Получение ID сделки из DOM
     */
    getDealId() {
        // Пробуем различные способы получения ID сделки
        const dealIdField = document.querySelector('input[name="deal_id"]') || 
                           document.getElementById('dealIdField');
        
        if (dealIdField && dealIdField.value) {
            return dealIdField.value;
        }
        
        // Извлекаем из URL
        const urlMatch = window.location.href.match(/\/deal\/(\d+)/);
        if (urlMatch) {
            return urlMatch[1];
        }
        
        return null;
    }
    
    /**
     * Показать индикатор загрузки
     */
    showLoadingIndicator(fieldName, fileName) {
        const container = this.getOrCreateLinkContainer(fieldName);
        const loadingHtml = `
            <div class="yandex-upload-progress" data-field="${fieldName}">
                <div class="upload-info">
                    <i class="fas fa-cloud-upload-alt upload-icon"></i>
                    <span class="upload-text">Загружается: ${fileName}</span>
                </div>
                <div class="progress">
                    <div class="progress-bar progress-bar-striped progress-bar-animated" 
                         role="progressbar" style="width: 0%"></div>
                </div>
            </div>
        `;
        container.innerHTML = loadingHtml;
    }
    
    /**
     * Обновить прогресс загрузки
     */
    updateProgress(fieldName, percent) {
        const progressBar = document.querySelector(`.yandex-upload-progress[data-field="${fieldName}"] .progress-bar`);
        if (progressBar) {
            progressBar.style.width = `${percent}%`;
            progressBar.setAttribute('aria-valuenow', percent);
        }
    }
    
    /**
     * Скрыть индикатор загрузки
     */
    hideLoadingIndicator(fieldName) {
        const progressIndicator = document.querySelector(`.yandex-upload-progress[data-field="${fieldName}"]`);
        if (progressIndicator) {
            progressIndicator.remove();
        }
    }
    
    /**
     * Показать успешный результат
     */
    showSuccess(fieldName, data) {
        const container = this.getOrCreateLinkContainer(fieldName);
        const successHtml = `
            <div class="upload-success-message" data-field="${fieldName}">
                <i class="fas fa-check-circle text-success"></i>
                <span class="text-success">Файл успешно загружен!</span>
            </div>
        `;
        
        // Временно показываем сообщение об успехе
        const tempDiv = document.createElement('div');
        tempDiv.innerHTML = successHtml;
        container.appendChild(tempDiv.firstElementChild);
        
        // Убираем сообщение через 3 секунды
        setTimeout(() => {
            const successMessage = container.querySelector('.upload-success-message');
            if (successMessage) {
                successMessage.remove();
            }
        }, 3000);
    }
    
    /**
     * Показать ошибку
     */
    showError(fieldName, errorMessage) {
        const container = this.getOrCreateLinkContainer(fieldName);
        const errorHtml = `
            <div class="upload-error-message" data-field="${fieldName}">
                <i class="fas fa-exclamation-triangle text-danger"></i>
                <span class="text-danger">Ошибка: ${errorMessage}</span>
            </div>
        `;
        container.innerHTML = errorHtml;
        
        // Убираем сообщение об ошибке через 5 секунд
        setTimeout(() => {
            const errorElement = container.querySelector('.upload-error-message');
            if (errorElement) {
                errorElement.remove();
            }
        }, 5000);
    }
    
    /**
     * Обновить ссылку на файл
     */
    updateFileLink(fieldName, url, fileName) {
        const container = this.getOrCreateLinkContainer(fieldName);
        
        // Очищаем контейнер от предыдущих ссылок
        container.innerHTML = '';
        
        if (url && fileName) {
            const linkHtml = `
                <div class="yandex-file-link-wrapper" data-field="${fieldName}">
                    <a href="${url}" target="_blank" class="yandex-file-link file-success">
                        <i class="fas fa-external-link-alt"></i>
                        ${fileName}
                    </a>
                    <button type="button" class="btn btn-sm btn-outline-danger delete-file-btn" 
                            data-field="${fieldName}" title="Удалить файл">
                        <i class="fas fa-trash-alt"></i>
                    </button>
                </div>
            `;
            container.innerHTML = linkHtml;
            
            // Добавляем анимацию появления
            const linkWrapper = container.querySelector('.yandex-file-link-wrapper');
            linkWrapper.style.animation = 'slideInUp 0.5s ease-out';
            
            // Добавляем обработчик удаления
            const deleteBtn = container.querySelector('.delete-file-btn');
            if (deleteBtn) {
                deleteBtn.addEventListener('click', () => this.deleteFile(fieldName));
            }
        }
    }
    
    /**
     * Получить или создать контейнер для ссылок
     */
    getOrCreateLinkContainer(fieldName) {
        let container = document.querySelector(`.yandex-file-links-container[data-field="${fieldName}"]`);
        
        if (!container) {
            // Ищем поле ввода файла
            const fileInput = document.querySelector(`input[name="${fieldName}"]`);
            if (fileInput) {
                // Создаем контейнер после поля ввода
                container = document.createElement('div');
                container.className = 'yandex-file-links-container';
                container.setAttribute('data-field', fieldName);
                
                // Вставляем контейнер после поля ввода
                fileInput.parentNode.insertBefore(container, fileInput.nextSibling);
            } else {
                console.warn(`Поле ${fieldName} не найдено`);
                return document.createElement('div'); // Возвращаем пустой div
            }
        }
        
        return container;
    }
    
    /**
     * Инициализация существующих ссылок
     */
    initExistingLinks() {
        // Получаем данные сделки из глобальной переменной или DOM
        const dealData = window.dealData || this.extractDealDataFromForm();
        
        if (dealData) {
            this.settings.supportedFields.forEach(fieldName => {
                const urlField = `yandex_url_${fieldName}`;
                const nameField = `original_name_${fieldName}`;
                
                if (dealData[urlField] && dealData[nameField]) {
                    this.updateFileLink(fieldName, dealData[urlField], dealData[nameField]);
                }
            });
        }
    }
    
    /**
     * Извлечение данных сделки из формы
     */
    extractDealDataFromForm() {
        const form = document.querySelector('form');
        if (!form) return null;
        
        const dealData = {};
        const inputs = form.querySelectorAll('input[type="hidden"]');
        
        inputs.forEach(input => {
            if (input.name.startsWith('yandex_url_') || input.name.startsWith('original_name_')) {
                dealData[input.name] = input.value;
            }
        });
        
        return dealData;
    }
    
    /**
     * Удаление файла
     */
    async deleteFile(fieldName) {
        if (!confirm('Вы уверены, что хотите удалить этот файл?')) {
            return;
        }
        
        try {
            const dealId = this.getDealId();
            const response = await fetch(this.apiEndpoints.delete, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({
                    deal_id: dealId,
                    field_name: fieldName
                })
            });
            
            const result = await response.json();
            
            if (result.success) {
                this.updateFileLink(fieldName, null, null);
                console.log(`✅ Файл ${fieldName} успешно удален`);
            } else {
                throw new Error(result.error || 'Ошибка удаления файла');
            }
            
        } catch (error) {
            console.error(`❌ Ошибка удаления файла ${fieldName}:`, error);
            alert(`Ошибка удаления файла: ${error.message}`);
        }
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
}

// Глобальная инициализация
document.addEventListener('DOMContentLoaded', function() {
    // Проверяем, что jQuery загружен
    if (typeof $ === 'undefined') {
        console.warn('⚠️ jQuery не загружен, YandexDiskUploaderV3 может работать некорректно');
    }
    
    // Инициализируем систему загрузки
    window.yandexDiskUploader = new YandexDiskUploaderV3();
});
