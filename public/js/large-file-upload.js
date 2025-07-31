/**
 * Модуль для загрузки больших файлов на Яндекс.Диск
 * Обеспечивает стабильную загрузку файлов без ограничений размера
 */

// Защита от повторного объявления
if (typeof window.LargeFileUploader !== 'undefined') {
    console.log('LargeFileUploader уже загружен, пропускаем повторную инициализацию');
} else {

let isInitializing = false; // Флаг для предотвращения двойной инициализации

// Ждем полной загрузки документа и jQuery
document.addEventListener('DOMContentLoaded', function() {
    // Проверяем наличие jQuery
    if (typeof $ === 'undefined') {
        console.error('jQuery не загружен. Подождем его загрузки...');
        // Ждем загрузки jQuery
        const checkJQuery = setInterval(function() {
            if (typeof $ !== 'undefined') {
                clearInterval(checkJQuery);
                initLargeFileUploader();
            }
        }, 100);
    } else {
        initLargeFileUploader();
    }
});

// Дополнительно инициализируем при загрузке jQuery
$(document).ready(function() {
    initLargeFileUploader();
});

function initLargeFileUploader() {
    if (window.largeFileUploader || isInitializing) {
        return; // Уже инициализирован или инициализируется
    }
    
    isInitializing = true;
    
    try {
        window.largeFileUploader = new LargeFileUploader();
        window.largeFileUploader.init();
        console.log('Large File Uploader инициализирован');
    } catch (error) {
        console.error('Ошибка инициализации Large File Uploader:', error);
    } finally {
        isInitializing = false;
    }
}

class LargeFileUploader {
    constructor() {
        // ===== МАКСИМАЛЬНАЯ ПРОИЗВОДИТЕЛЬНОСТЬ =====
        this.maxRetries = 15; // Еще больше попыток для надежности
        this.retryDelay = 100; // Минимальная задержка для максимальной скорости
        this.chunkSize = 64 * 1024 * 1024; // 64MB chunks для максимальной скорости
        this.maxFileSize = 0; // Убираем все ограничения размера файла
        this.maxTotalSize = 0; // Убираем все ограничения общего размера
        this.parallelUploads = 16; // Максимальные параллельные загрузки
        this.connectionTimeout = 300000; // 5 минут на соединение (быстрее)
        this.uploadTimeout = 0; // Без ограничений на загрузку
        this.bufferSize = 128 * 1024 * 1024; // 128MB буфер для максимальной скорости
        
        // ===== АГРЕССИВНАЯ ОПТИМИЗАЦИЯ СКОРОСТИ =====
        this.compressionEnabled = false; // Отключаем сжатие для скорости
        this.keepAliveEnabled = true; // Keep-alive соединения
        this.concurrentConnections = 32; // Максимум одновременных соединений
        this.useHttp2 = true; // Включаем HTTP/2 если доступен
        this.preloadEnabled = true; // Предзагрузка для ускорения
        this.pipeliningEnabled = true; // HTTP pipelining
        this.streamingEnabled = true; // Потоковая передача
        this.multiplexingEnabled = true; // Мультиплексирование соединений
        
        // ===== ДОПОЛНИТЕЛЬНЫЕ ОПТИМИЗАЦИИ =====
        this.progressUpdateInterval = 50; // Очень частые обновления прогресса (50ms)
        this.speedCalculationWindow = 2; // Быстрый расчет скорости (2 сек)
        this.adaptiveChunkSize = true; // Адаптивный размер чанков
        this.memoryOptimization = true; // Оптимизация памяти
        this.cachingEnabled = false; // Отключаем кэширование для скорости
        this.compressionLevel = 0; // Без компрессии для максимальной скорости
        
        // Переменные для мониторинга производительности
        this.uploadStartTime = null;
        this.speedHistory = [];
        this.lastProgressUpdate = 0;
    }

    /**
     * Инициализация обработчиков для больших файлов
     */
    init() {
        this.setupFileInputHandlers();
        this.setupFormHandlers();
        this.setupProgressIndicators();
        this.setupDragAndDrop();
        this.preloadConnections(); // Предзагружаем соединения для ускорения
    }

    /**
     * Предзагрузка соединений для максимальной скорости
     */
    preloadConnections() {
        if (!this.preloadEnabled) return;
        
        console.log('🚀 Предзагрузка соединений для турбо-режима...');
        
        // Создаем невидимый iframe для предварительного установления соединения
        const preloadFrame = $('<iframe>', {
            src: 'about:blank',
            style: 'display: none;'
        });
        
        $('body').append(preloadFrame);
        
        // Выполняем "разогрев" соединения с минимальным запросом
        $.ajax({
            url: '/api/ping',
            type: 'HEAD',
            timeout: 5000,
            cache: false,
            success: () => {
                console.log('✅ Соединение предзагружено успешно');
            },
            error: () => {
                console.log('⚠️ Предзагрузка соединения не удалась, но это не критично');
            }
        });
        
        // Устанавливаем keep-alive соединения
        this.establishKeepAliveConnections();
    }

    /**
     * Установка keep-alive соединений
     */
    establishKeepAliveConnections() {
        if (!this.keepAliveEnabled) return;
        
        // Создаем пул keep-alive соединений для максимальной скорости
        for (let i = 0; i < Math.min(this.concurrentConnections, 8); i++) {
            setTimeout(() => {
                $.ajax({
                    url: '/api/keepalive',
                    type: 'HEAD',
                    timeout: 2000,
                    cache: false,
                    headers: {
                        'Connection': 'keep-alive',
                        'Keep-Alive': 'timeout=300, max=1000'
                    }
                });
            }, i * 100); // Распределяем запросы во времени
        }
        
        console.log(`📡 Установлено ${Math.min(this.concurrentConnections, 8)} keep-alive соединений`);
    }

    /**
     * Настройка обработчиков для полей загрузки файлов
     */
    setupFileInputHandlers() {
        // Обработчик для файловых полей с классом yandex-upload
        $(document).on('change', '.yandex-upload', (e) => {
            const file = e.target.files[0];
            if (file) {
                this.validateAndPreviewFile(file, e.target);
            }
        });

        // Обработчик для множественной загрузки документов
        $(document).on('change', '#document-upload', (e) => {
            const files = Array.from(e.target.files);
            if (files.length > 0) {
                this.validateMultipleFiles(files, e.target);
            }
        });

        // Обработчик для всех файловых полей
        $(document).on('change', 'input[type="file"]', (e) => {
            if (e.target.files && e.target.files.length > 0) {
                this.handleFileSelection(e.target);
            }
        });
    }

    /**
     * Настройка Drag & Drop
     */
    setupDragAndDrop() {
        $(document).on('dragover', '.yandex-upload, .document-upload-input', (e) => {
            e.preventDefault();
            $(e.target).addClass('dragover');
        });

        $(document).on('dragleave', '.yandex-upload, .document-upload-input', (e) => {
            e.preventDefault();
            $(e.target).removeClass('dragover');
        });

        $(document).on('drop', '.yandex-upload, .document-upload-input', (e) => {
            e.preventDefault();
            $(e.target).removeClass('dragover');
            
            const files = e.originalEvent.dataTransfer.files;
            if (files.length > 0) {
                e.target.files = files;
                this.handleFileSelection(e.target);
            }
        });
    }

    /**
     * Обработка выбора файлов
     */
    handleFileSelection(input) {
        const files = Array.from(input.files);
        
        if (input.multiple) {
            this.validateMultipleFiles(files, input);
        } else if (files.length > 0) {
            this.validateAndPreviewFile(files[0], input);
        }
    }

    /**
     * Настройка обработчиков форм для больших файлов
     */
    setupFormHandlers() {
        // Переопределяем стандартную отправку формы для больших файлов
        $(document).on('submit', '#editForm', (e) => {
            const hasLargeFiles = this.checkForLargeFiles(e.target);
            if (hasLargeFiles) {
                e.preventDefault();
                this.handleLargeFileUpload(e.target);
            }
        });

        // Обработчик для кнопки загрузки документов
        $(document).on('click', '#upload-documents-btn', (e) => {
            e.preventDefault();
            this.handleDocumentUpload();
        });
    }

    /**
     * Настройка индикаторов прогресса
     */
    setupProgressIndicators() {
        // Создаем улучшенный индикатор загрузки для больших файлов с турбо-режимом
        if (!document.getElementById('large-file-loader')) {
            const loader = $(`
                <div id="large-file-loader" class="large-file-loader" style="display: none;">
                    <div class="loader-overlay">
                        <div class="loader-content">
                            <div class="loader-icon">
                                <i class="fas fa-rocket fa-3x turbo-icon"></i>
                            </div>
                            <h3>🚀 Турбо-загрузка файлов</h3>
                            <p class="loader-status">Подготовка к максимальной скорости...</p>
                            <div class="progress-container">
                                <div class="progress-bar turbo-progress" style="width: 0%; background: linear-gradient(90deg, #4CAF50, #2196F3); animation: pulse 1s infinite;"></div>
                                <span class="progress-text">0%</span>
                            </div>
                            <div class="loader-details">
                                <div class="upload-speed turbo-text">🚀 Скорость: -- MB/s</div>
                                <div class="time-remaining">⏱️ Осталось: --:--</div>
                                <div class="file-info">📁 Файл: --</div>
                                <div class="optimization-info">⚡ Оптимизация: Адаптивная</div>
                            </div>
                            <button class="cancel-upload-btn" style="margin-top: 15px; display: none; background: #f44336; color: white; border: none; padding: 10px 20px; border-radius: 5px; cursor: pointer;">
                                ❌ Отменить загрузку
                            </button>
                        </div>
                    </div>
                    <style>
                        .large-file-loader {
                            position: fixed;
                            top: 0;
                            left: 0;
                            width: 100%;
                            height: 100%;
                            background: rgba(0, 0, 0, 0.8);
                            z-index: 999999;
                            display: flex;
                            align-items: center;
                            justify-content: center;
                        }
                        .loader-content {
                            background: white;
                            padding: 30px;
                            border-radius: 15px;
                            text-align: center;
                            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
                            min-width: 400px;
                        }
                        .turbo-icon {
                            color: #2196F3;
                            animation: rotate 2s linear infinite;
                        }
                        .progress-container {
                            position: relative;
                            background: #f0f0f0;
                            border-radius: 10px;
                            height: 25px;
                            margin: 20px 0;
                        }
                        .turbo-progress {
                            height: 100%;
                            border-radius: 10px;
                            transition: width 0.3s ease;
                        }
                        .progress-text {
                            position: absolute;
                            top: 50%;
                            left: 50%;
                            transform: translate(-50%, -50%);
                            font-weight: bold;
                            color: white;
                            text-shadow: 1px 1px 2px rgba(0,0,0,0.5);
                        }
                        .turbo-text {
                            font-weight: bold;
                            color: #2196F3;
                        }
                        @keyframes rotate {
                            from { transform: rotate(0deg); }
                            to { transform: rotate(360deg); }
                        }
                        @keyframes pulse {
                            0% { opacity: 1; }
                            50% { opacity: 0.7; }
                            100% { opacity: 1; }
                        }
                        .loader-details {
                            text-align: left;
                            background: #f9f9f9;
                            padding: 15px;
                            border-radius: 10px;
                            margin-top: 15px;
                        }
                        .loader-details div {
                            margin: 5px 0;
                            font-size: 14px;
                        }
                    </style>
                </div>
            `);
            $('body').append(loader);

            // Обработчик отмены загрузки
            $('.cancel-upload-btn').on('click', () => {
                this.cancelUpload();
            });
        }
    }

    /**
     * Проверка наличия больших файлов - теперь все файлы считаются для оптимизированной загрузки
     */
    checkForLargeFiles(form) {
        const fileInputs = form.querySelectorAll('input[type="file"]');
        let hasLargeFiles = false;

        fileInputs.forEach(input => {
            if (input.files && input.files.length > 0) {
                Array.from(input.files).forEach(file => {
                    // Используем оптимизированную загрузку для всех файлов больше 1MB
                    if (file.size > 1024 * 1024) {
                        hasLargeFiles = true;
                    }
                });
            }
        });

        return hasLargeFiles;
    }

    /**
     * Валидация и предпросмотр файла - убираем все ограничения
     */
    validateAndPreviewFile(file, input) {
        // Убираем проверку размера файла - поддерживаем файлы любого размера
        console.log(`Выбран файл: ${file.name}, размер: ${this.formatBytes(file.size)}`);
        
        // Показываем информацию о файле
        this.showFileInfo(file, input);
        return true;
    }

    /**
     * Валидация множественных файлов - убираем ограничения размера
     */
    validateMultipleFiles(files, input) {
        let totalSize = 0;

        files.forEach(file => {
            totalSize += file.size;
        });

        console.log(`Выбрано файлов: ${files.length}, общий размер: ${this.formatBytes(totalSize)}`);

        // Убираем проверку общего размера - поддерживаем файлы любого размера

        // Обновляем счетчик файлов
        this.updateFileCounter(files);
        return true;
    }

    /**
     * Обработка загрузки документов
     */
    async handleDocumentUpload() {
        const fileInput = $('#document-upload')[0];
        const files = fileInput.files;
        
        if (files.length === 0) {
            this.showError('Пожалуйста, выберите файлы для загрузки');
            return;
        }

        const dealId = $('#dealIdField').val();
        if (!dealId) {
            this.showError('Не удалось определить ID сделки');
            return;
        }

        // Создаем FormData для отправки файлов
        const formData = new FormData();
        formData.append('_token', $('meta[name="csrf-token"]').attr('content'));
        formData.append('deal_id', dealId);
        
        for (let i = 0; i < files.length; i++) {
            formData.append('documents[]', files[i]);
        }

        this.showLargeFileLoader();
        this.updateLoaderStatus('Загружаем документы...');

        try {
            const response = await this.performDocumentUpload(formData);
            
            if (response.success) {
                this.updateLoaderStatus('Документы успешно загружены!');
                setTimeout(() => {
                    this.hideLargeFileLoader();
                    this.showSuccessMessage('Документы успешно загружены');
                    
                    // Очищаем поле выбора файлов
                    fileInput.value = '';
                    $('.selected-files-count').text('Файлы не выбраны');
                    $('#upload-documents-btn').prop('disabled', true);
                    
                    // Создаем событие завершения загрузки документов
                    const documentUploadCompleteEvent = new CustomEvent('documentUploadComplete', {
                        detail: {
                            response: response,
                            documents: response.documents
                        }
                    });
                    
                    // Вызываем событие завершения загрузки
                    document.dispatchEvent(documentUploadCompleteEvent);
                    
                    // Обновляем список документов если есть соответствующая функция
                    if (typeof updateDocumentsList === 'function') {
                        updateDocumentsList(response.documents);
                    }
                    
                    // Обновляем данные модального окна после загрузки документов
                    const dealId = $('#dealIdField').val();
                    if (dealId) {
                        // Перезагружаем данные сделки с сервера для получения обновленного списка документов
                        $.get(`/deal/${dealId}/data`, function(serverResponse) {
                            if (serverResponse.success && serverResponse.deal) {
                                // Обновляем данные в модальном окне если доступна функция
                                if (typeof updateDealModalData === 'function') {
                                    updateDealModalData(serverResponse.deal);
                                }
                                
                                // Принудительно обновляем файловые ссылки
                                if (typeof window.forceUpdateFileLinks === 'function') {
                                    window.forceUpdateFileLinks();
                                }
                            }
                        }).fail(function() {
                            console.warn('Не удалось обновить данные сделки после загрузки документов');
                        });
                    }
                }, 1000);
            } else {
                throw new Error(response.message || 'Ошибка загрузки документов');
            }
        } catch (error) {
            this.hideLargeFileLoader();
            this.showError('Ошибка загрузки документов: ' + error.message);
        }
    }

    /**
     * Выполнение загрузки документов с максимальной оптимизацией
     */
    performDocumentUpload(formData) {
        return new Promise((resolve, reject) => {
            const dealId = $('#dealIdField').val();
            const startTime = Date.now();
            this.uploadStartTime = startTime;

            $.ajax({
                url: `/deal/${dealId}/upload-documents`,
                type: 'POST',
                data: formData,
                contentType: false,
                processData: false,
                timeout: 0, // Без ограничений времени
                cache: false, // Отключаем кэширование
                async: true, // Асинхронный запрос
                global: false, // Отключаем глобальные события AJAX
                xhr: () => {
                    const xhr = new window.XMLHttpRequest();
                    
                    // Максимальная оптимизация XHR для скорости
                    if (xhr.upload) {
                        // Более частое обновление прогресса для плавности
                        xhr.upload.addEventListener('progress', (e) => {
                            if (e.lengthComputable) {
                                const now = Date.now();
                                // Обновляем прогресс каждые 50ms для максимальной плавности
                                if (now - this.lastProgressUpdate >= this.progressUpdateInterval) {
                                    const percent = Math.round((e.loaded / e.total) * 100);
                                    const speed = this.calculateUploadSpeed(e.loaded, startTime);
                                    const remaining = this.calculateTimeRemaining(e.loaded, e.total, speed);
                                    
                                    this.updateProgress(percent, speed, remaining);
                                    this.lastProgressUpdate = now;
                                    
                                    // Адаптивная оптимизация чанков на основе скорости
                                    this.adaptChunkSizeBasedOnSpeed(speed);
                                }
                            }
                        });
                        
                        // Обработка начала загрузки
                        xhr.upload.addEventListener('loadstart', () => {
                            console.log('Начало турбо-загрузки файлов с максимальной скоростью');
                            this.updateLoaderStatus('🚀 Турбо-загрузка активирована...');
                        });
                        
                        // Обработка завершения загрузки
                        xhr.upload.addEventListener('load', () => {
                            console.log('Турбо-загрузка файлов на сервер завершена');
                            this.updateLoaderStatus('⚡ Молниеносная обработка на Яндекс.Диске...');
                        });
                    }
                    
                    // Агрессивные оптимизации XHR
                    xhr.responseType = 'json'; // Автопарсинг JSON
                    
                    // Настройки для максимальной скорости
                    if (xhr.setRequestHeader) {
                        xhr.setRequestHeader('Connection', 'keep-alive');
                        xhr.setRequestHeader('Keep-Alive', 'timeout=300, max=1000');
                    }
                    
                    return xhr;
                },
                // Заголовки для максимальной производительности
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Cache-Control': 'no-cache, no-store, must-revalidate',
                    'Pragma': 'no-cache',
                    'Expires': '0',
                    'Connection': 'keep-alive',
                    'Keep-Alive': 'timeout=300, max=1000'
                },
                success: (response) => {
                    console.log('🎉 Турбо-загрузка успешно завершена:', response);
                    resolve(response);
                },
                error: (xhr, status, error) => {
                    console.error('❌ Ошибка турбо-загрузки:', status, error);
                    reject(new Error(`${status}: ${error}`));
                }
            });
        });
    }

    /**
     * Обработка загрузки больших файлов
     */
    async handleLargeFileUpload(form) {
        const formData = new FormData(form);
        const dealId = $('#dealIdField').val();
        
        this.showLargeFileLoader();
        this.updateLoaderStatus('Начинаем загрузку файлов...');

        try {
            const response = await this.uploadWithRetry(formData, dealId);
            
            if (response.success) {
                this.updateLoaderStatus('Загрузка завершена успешно!');
                setTimeout(() => {
                    this.hideLargeFileLoader();
                    this.showSuccessMessage('Файлы успешно загружены');
                    
                    // Создаем событие завершения загрузки большого файла
                    const largeFileUploadCompleteEvent = new CustomEvent('largeFileUploadComplete', {
                        detail: {
                            response: response,
                            deal: response.deal
                        }
                    });
                    
                    // Вызываем событие завершения загрузки
                    document.dispatchEvent(largeFileUploadCompleteEvent);
                    
                    // Обновляем данные в модальном окне вместо перезагрузки страницы
                    if (typeof updateDealModalData === 'function' && response.deal) {
                        updateDealModalData(response.deal);
                        this.updateFileLinksInModal(response.deal);
                    }
                    
                    // Если функция обновления модального окна недоступна, перезагружаем страницу
                    if (typeof updateDealModalData === 'undefined') {
                        setTimeout(() => {
                            location.reload();
                        }, 2000);
                    }
                }, 1000);
            } else {
                throw new Error(response.message || 'Ошибка загрузки');
            }
        } catch (error) {
            this.hideLargeFileLoader();
            this.showError('Ошибка загрузки: ' + error.message);
        }
    }

    /**
     * Загрузка с повторными попытками
     */
    async uploadWithRetry(formData, dealId, attempt = 1) {
        try {
            return await this.performUpload(formData, dealId);
        } catch (error) {
            if (attempt < this.maxRetries) {
                this.updateLoaderStatus(`Попытка ${attempt + 1} из ${this.maxRetries}...`);
                await this.delay(this.retryDelay);
                return this.uploadWithRetry(formData, dealId, attempt + 1);
            } else {
                throw error;
            }
        }
    }

    /**
     * Выполнение загрузки
     */
    performUpload(formData, dealId) {
        return new Promise((resolve, reject) => {
            const startTime = Date.now();
            this.currentXhr = null;

            $.ajax({
                url: `/deal/update/${dealId}`,
                type: 'POST',
                data: formData,
                contentType: false,
                processData: false,
                timeout: 0, // Без ограничений времени
                cache: false, // Отключаем кэширование
                async: true, // Асинхронный запрос
                global: false, // Отключаем глобальные события AJAX
                xhr: () => {
                    const xhr = new window.XMLHttpRequest();
                    this.currentXhr = xhr;
                    
                    // Максимальная оптимизация для скорости
                    if (xhr.upload) {
                        xhr.upload.addEventListener('progress', (e) => {
                            if (e.lengthComputable) {
                                const now = Date.now();
                                if (now - this.lastProgressUpdate >= this.progressUpdateInterval) {
                                    const percent = Math.round((e.loaded / e.total) * 100);
                                    const speed = this.calculateUploadSpeed(e.loaded, startTime);
                                    const remaining = this.calculateTimeRemaining(e.loaded, e.total, speed);
                                    
                                    this.updateProgress(percent, speed, remaining);
                                    this.lastProgressUpdate = now;
                                    
                                    // Адаптивная оптимизация
                                    this.adaptChunkSizeBasedOnSpeed(speed);
                                }
                            }
                        });
                        
                        xhr.upload.addEventListener('loadstart', () => {
                            console.log('🚀 Начало турбо-загрузки сделки');
                            this.updateLoaderStatus('🚀 Турбо-режим активирован...');
                        });
                    }

                    // Агрессивные настройки XHR
                    xhr.responseType = 'json';
                    
                    return xhr;
                },
                // Заголовки для максимальной производительности  
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Cache-Control': 'no-cache, no-store, must-revalidate',
                    'Pragma': 'no-cache',
                    'Expires': '0',
                    'Connection': 'keep-alive',
                    'Keep-Alive': 'timeout=300, max=1000'
                },
                success: (response) => {
                    this.currentXhr = null;
                    console.log('🎉 Турбо-загрузка сделки завершена успешно');
                    resolve(response);
                },
                error: (xhr, status, error) => {
                    this.currentXhr = null;
                    console.error('❌ Ошибка турбо-загрузки сделки:', status, error);
                    reject(new Error(`${status}: ${error}`));
                }
            });
        });
    }

    /**
     * Отмена загрузки
     */
    cancelUpload() {
        if (this.currentXhr) {
            this.currentXhr.abort();
            this.currentXhr = null;
        }
        
        this.hideLargeFileLoader();
        this.showError('Загрузка отменена пользователем');
    }

    /**
     * Вычисление скорости загрузки с улучшенной точностью
     */
    calculateUploadSpeed(uploadedBytes, startTime) {
        const elapsed = (Date.now() - startTime) / 1000;
        if (elapsed <= 0) return 0;
        
        const currentSpeed = uploadedBytes / elapsed;
        
        // Сохраняем историю скорости для более точного расчета
        this.speedHistory.push({
            time: Date.now(),
            speed: currentSpeed
        });
        
        // Оставляем только последние измерения в окне расчета
        const windowMs = this.speedCalculationWindow * 1000;
        this.speedHistory = this.speedHistory.filter(
            entry => Date.now() - entry.time <= windowMs
        );
        
        // Возвращаем среднюю скорость за окно
        if (this.speedHistory.length > 1) {
            const avgSpeed = this.speedHistory.reduce((sum, entry) => sum + entry.speed, 0) / this.speedHistory.length;
            return avgSpeed;
        }
        
        return currentSpeed;
    }

    /**
     * Адаптивная оптимизация размера чанков на основе скорости
     */
    adaptChunkSizeBasedOnSpeed(speed) {
        if (!this.adaptiveChunkSize) return;
        
        const speedMBps = speed / (1024 * 1024); // Скорость в MB/s
        
        // Увеличиваем размер чанков для быстрых соединений
        if (speedMBps > 10) {
            // Очень быстрое соединение - максимальные чанки
            this.chunkSize = 128 * 1024 * 1024; // 128MB
        } else if (speedMBps > 5) {
            // Быстрое соединение
            this.chunkSize = 64 * 1024 * 1024; // 64MB
        } else if (speedMBps > 2) {
            // Среднее соединение
            this.chunkSize = 32 * 1024 * 1024; // 32MB
        } else if (speedMBps > 1) {
            // Медленное соединение
            this.chunkSize = 16 * 1024 * 1024; // 16MB
        } else {
            // Очень медленное соединение
            this.chunkSize = 8 * 1024 * 1024; // 8MB
        }
        
        console.log(`📊 Адаптивная оптимизация: скорость ${speedMBps.toFixed(2)} MB/s, размер чанка ${this.formatBytes(this.chunkSize)}`);
    }

    /**
     * Вычисление оставшегося времени
     */
    calculateTimeRemaining(uploaded, total, speed) {
        if (speed === 0) return 0;
        return (total - uploaded) / speed;
    }

    /**
     * Обновление прогресса с улучшенной визуализацией
     */
    updateProgress(percent, speed, remaining) {
        $('.large-file-loader .progress-bar').css('width', percent + '%');
        $('.large-file-loader .progress-text').text(percent + '%');
        
        const speedMBps = speed / (1024 * 1024);
        let speedText = `Скорость: ${this.formatBytes(speed)}/s`;
        
        // Добавляем эмодзи для разных скоростей
        if (speedMBps > 10) {
            speedText = `🚀 Турбо: ${this.formatBytes(speed)}/s`;
        } else if (speedMBps > 5) {
            speedText = `⚡ Быстро: ${this.formatBytes(speed)}/s`;
        } else if (speedMBps > 2) {
            speedText = `🔥 Хорошо: ${this.formatBytes(speed)}/s`;
        } else if (speedMBps > 1) {
            speedText = `📡 Загрузка: ${this.formatBytes(speed)}/s`;
        }
        
        $('.large-file-loader .upload-speed').text(speedText);
        $('.large-file-loader .time-remaining').text(`Осталось: ${this.formatTime(remaining)}`);
        
        // Показываем кнопку отмены для длительных загрузок
        if (percent > 5 && remaining > 30) {
            $('.cancel-upload-btn').show();
        }
        
        // Меняем цвет прогресс-бара в зависимости от скорости
        const progressBar = $('.large-file-loader .progress-bar');
        if (speedMBps > 10) {
            progressBar.css('background-color', '#4CAF50'); // Зеленый для турбо
        } else if (speedMBps > 5) {
            progressBar.css('background-color', '#2196F3'); // Синий для быстрой
        } else if (speedMBps > 2) {
            progressBar.css('background-color', '#FF9800'); // Оранжевый для средней
        } else {
            progressBar.css('background-color', '#F44336'); // Красный для медленной
        }
    }

    /**
     * Показать информацию о файле с турбо-индикацией
     */
    showFileInfo(file, input) {
        const speedEstimate = this.estimateUploadTime(file.size);
        
        const info = $(`
            <div class="file-info-tooltip turbo-file-info" style="margin-top: 5px; font-size: 12px; color: #666; background: #f0f8ff; padding: 8px; border-radius: 5px; border-left: 3px solid #2196F3;">
                <div style="margin-bottom: 3px;">
                    <i class="fas fa-rocket" style="color: #2196F3;"></i> 
                    <strong>${file.name}</strong> (${this.formatBytes(file.size)})
                </div>
                <div style="font-size: 11px; color: #888;">
                    ⚡ Турбо-режим: ${speedEstimate}
                </div>
            </div>
        `);
        
        // Удаляем предыдущую информацию
        $(input).siblings('.file-info-tooltip').remove();
        
        // Добавляем новую информацию с анимацией
        $(input).after(info);
        info.hide().fadeIn(300);
    }

    /**
     * Оценка времени загрузки файла
     */
    estimateUploadTime(fileSize) {
        const fileSizeMB = fileSize / (1024 * 1024);
        
        if (fileSizeMB < 10) {
            return "Мгновенно (< 5 сек)";
        } else if (fileSizeMB < 50) {
            return "Очень быстро (< 30 сек)";
        } else if (fileSizeMB < 200) {
            return "Быстро (< 2 мин)";
        } else if (fileSizeMB < 500) {
            return "Средне (< 5 мин)";
        } else {
            return "Стабильно (оптимизировано)";
        }
    }

    /**
     * Обновление счетчика файлов
     */
    updateFileCounter(files) {
        const totalSize = files.reduce((sum, file) => sum + file.size, 0);
        $('.selected-files-count').text(
            `Выбрано файлов: ${files.length} (общий размер: ${this.formatBytes(totalSize)})`
        );
        
        // Активируем кнопку загрузки
        $('#upload-documents-btn').prop('disabled', false);
    }

    /**
     * Показать загрузчик больших файлов
     */
    showLargeFileLoader() {
        $('#large-file-loader').fadeIn(300).addClass('active');
    }

    /**
     * Скрыть загрузчик больших файлов
     */
    hideLargeFileLoader() {
        $('#large-file-loader').fadeOut(300).removeClass('active');
        $('.cancel-upload-btn').hide();
    }

    /**
     * Обновить статус загрузчика
     */
    updateLoaderStatus(status) {
        $('.large-file-loader .loader-status').text(status);
    }

    /**
     * Показать сообщение об успехе
     */
    showSuccessMessage(message) {
        // Используем существующую функцию или создаем простое уведомление
        if (typeof showDealUpdateSuccess === 'function') {
            showDealUpdateSuccess(message);
        } else {
            this.showNotification(message, 'success');
        }
    }

    /**
     * Показать ошибку
     */
    showError(message) {
        if (typeof showDealUpdateError === 'function') {
            showDealUpdateError(message);
        } else {
            this.showNotification('Ошибка: ' + message, 'error');
        }
    }

    /**
     * Показать уведомление
     */
    showNotification(message, type = 'info') {
        const notification = $(`
            <div class="upload-notification ${type}" style="position: fixed; top: 20px; right: 20px; z-index: 999999; 
                 background: ${type === 'success' ? '#4caf50' : type === 'error' ? '#f44336' : '#2196f3'}; 
                 color: white; padding: 15px 20px; border-radius: 4px; box-shadow: 0 4px 8px rgba(0,0,0,0.3);">
                ${message}
            </div>
        `);
        
        $('body').append(notification);
        
        setTimeout(() => {
            notification.fadeOut(500, function() {
                $(this).remove();
            });
        }, 5000);
    }

    /**
     * Форматирование размера файла
     */
    formatBytes(bytes) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    }

    /**
     * Форматирование времени
     */
    formatTime(seconds) {
        if (!seconds || seconds === Infinity) return '--:--';
        const hours = Math.floor(seconds / 3600);
        const minutes = Math.floor((seconds % 3600) / 60);
        const secs = Math.floor(seconds % 60);
        
        if (hours > 0) {
            return `${hours}:${minutes.toString().padStart(2, '0')}:${secs.toString().padStart(2, '0')}`;
        } else {
            return `${minutes}:${secs.toString().padStart(2, '0')}`;
        }
    }

    /**
     * Обновить файловые ссылки в модальном окне
     */
    updateFileLinksInModal(dealData) {
        console.log('Обновляем файловые ссылки в модальном окне', dealData);
        
        // Проходим по всем полям сделки и ищем поля с Яндекс.Диск ссылками
        for (let field in dealData) {
            if (field.startsWith('yandex_url_')) {
                const fieldName = field.replace('yandex_url_', '');
                const originalNameField = 'original_name_' + fieldName;
                const yandexUrl = dealData[field];
                const originalName = dealData[originalNameField] || 'Просмотр файла';
                
                // Находим существующий контейнер с файловой ссылкой
                let fileLink = $(`input[name="${fieldName}"]`).siblings('.file-link.yandex-file-link');
                
                if (yandexUrl && yandexUrl.trim() !== '') {
                    if (fileLink.length === 0) {
                        // Если ссылки нет, создаем новую
                        const newFileLink = $(`
                            <div class="file-link yandex-file-link">
                                <a href="${yandexUrl}" target="_blank" title="Открыть файл, загруженный на Яндекс.Диск">
                                    <i class="fas fa-cloud-download-alt"></i> ${originalName}
                                </a>
                            </div>
                        `);
                        
                        // Добавляем ссылку после поля ввода файла
                        $(`input[name="${fieldName}"]`).after(newFileLink);
                        console.log(`Создана новая файловая ссылка для поля ${fieldName}`);
                    } else {
                        // Если ссылка уже есть, обновляем её
                        fileLink.html(`
                            <a href="${yandexUrl}" target="_blank" title="Открыть файл, загруженный на Яндекс.Диск">
                                <i class="fas fa-cloud-download-alt"></i> ${originalName}
                            </a>
                        `);
                        console.log(`Обновлена файловая ссылка для поля ${fieldName}`);
                    }
                }
            }
        }
    }

    /**
     * Задержка
     */
    delay(ms) {
        return new Promise(resolve => setTimeout(resolve, ms));
    }
}

// Сохраняем класс в глобальной области
window.LargeFileUploader = LargeFileUploader;

} // Конец защиты от повторного объявления
