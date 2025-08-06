<!-- Система AJAX обновления для страницы редактирования сделки -->
<script>
$(document).ready(function() {
    console.log('🔧 Инициализация AJAX системы обновления сделок...');
    
    // Используем новую систему загрузки библиотек
    if (typeof window.LibrariesManager !== 'undefined') {
        // Проверяем все библиотеки
        if (!window.LibrariesManager.checkAll()) {
            console.log('🔄 Не все библиотеки загружены, принудительно загружаем...');
            window.LibrariesManager.loadAll();
        }
        
        // Добавляем callback для инициализации после загрузки всех библиотек
        window.LibrariesManager.onReady(function() {
            console.log('✅ Все библиотеки готовы, инициализируем компоненты');
            initAjaxDealUpdate();
            initializeSelect2();
        });
    } else {
        // Fallback: используем старую систему
        checkRequiredLibraries(function() {
            initAjaxDealUpdate();
            initializeSelect2();
        });
    }
});

/**
 * Обработка множественной загрузки файлов на Яндекс.Диск
 */
async function handleMultipleYandexFileUpload(form) {
    return new Promise(async (resolve, reject) => {
        try {
            console.log('🚀 Начинаем загрузку всех файлов на Яндекс.Диск...');
            
            // Находим все поля с файлами для Яндекс.Диска
            const yandexFieldNames = [
                'measurements_file', 'final_project_file', 'work_act', 
                'chat_screenshot', 'archicad_file', 'plan_final', 'final_collage',
                'screenshot_work_1', 'screenshot_work_2', 'screenshot_work_3', 
                'screenshot_final', 'execution_order_file', 'final_floorplan', 
                'contract_attachment'
            ];
            
            const yandexFileInputs = Array.from(form.querySelectorAll('input[type="file"]')).filter(input => {
                return input.classList.contains('yandex-upload') || 
                       input.getAttribute('data-upload-type') === 'yandex' ||
                       input.name.includes('_file') || 
                       yandexFieldNames.includes(input.name);
            });
            
            // Собираем все файлы для загрузки
            const filesToUpload = [];
            yandexFileInputs.forEach(input => {
                if (input.files && input.files.length > 0) {
                    const file = input.files[0];
                    filesToUpload.push({
                        file: file,
                        fieldName: input.name,
                        input: input
                    });
                    console.log(`📁 Добавлен файл для загрузки: ${input.name} - ${file.name}`);
                }
            });
            
            if (filesToUpload.length === 0) {
                console.log('⚠️ Нет файлов для загрузки');
                resolve({ success: false, message: 'Нет файлов для загрузки' });
                return;
            }
            
            console.log(`🚀 Найдено ${filesToUpload.length} файлов для загрузки`);
            
            // Загружаем все файлы одновременно через Yandex Disk API
            const dealId = extractDealIdFromForm(form);
            const uploadPromises = filesToUpload.map(fileData => {
                return uploadSingleFileToYandex(fileData.file, dealId, fileData.fieldName)
                    .then(result => ({
                        success: true,
                        fieldName: fileData.fieldName,
                        data: result.data
                    }))
                    .catch(error => ({
                        success: false,
                        fieldName: fileData.fieldName,
                        error: error.message
                    }));
            });
            
            // Ожидаем завершения всех загрузок
            const uploadResults = await Promise.all(uploadPromises);
            
            // Проверяем результаты
            const failedUploads = uploadResults.filter(result => !result.success);
            if (failedUploads.length > 0) {
                console.error('❌ Некоторые файлы не удалось загрузить:', failedUploads);
                reject(new Error(`Не удалось загрузить ${failedUploads.length} файлов`));
                return;
            }
            
            console.log('✅ Все файлы успешно загружены на Яндекс.Диск');
            
            // Теперь обновляем сделку с обычными данными формы
            const formData = new FormData(form);
            
            // Очищаем файловые поля из FormData, т.к. файлы уже загружены на Яндекс.Диск
            filesToUpload.forEach(fileData => {
                formData.delete(fileData.fieldName);
            });
            
            const updateResponse = await updateDealWithFormData(form, formData);
            
            if (updateResponse.success) {
                // Получаем обновленные данные сделки
                const freshDealData = await fetchDealData(dealId);
                resolve({
                    success: true,
                    message: 'Сделка обновлена, все файлы загружены на Яндекс.Диск',
                    deal: freshDealData.deal || updateResponse.deal,
                    uploadedFiles: uploadResults
                });
            } else {
                reject(new Error(updateResponse.message || 'Ошибка обновления сделки'));
            }
            
        } catch (error) {
            console.error('❌ Ошибка при загрузке файлов на Яндекс.Диск:', error);
            reject(error);
        }
    });
}

/**
 * Загрузка одного файла на Яндекс.Диск
 */
async function uploadSingleFileToYandex(file, dealId, fieldName) {
    return new Promise((resolve, reject) => {
        const formData = new FormData();
        formData.append('file', file);
        formData.append('deal_id', dealId);
        formData.append('field_name', fieldName);
        
        console.log(`🚀 Загружаем файл: ${fieldName} - ${file.name}`);
        
        $.ajax({
            url: '/api/yandex-disk/upload',
            type: 'POST',
            data: formData,
            contentType: false,
            processData: false,
            timeout: 0, // Без таймаута для больших файлов
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                'X-Requested-With': 'XMLHttpRequest'
            },
            success: function(response) {
                if (response.success) {
                    console.log(`✅ Файл ${fieldName} успешно загружен:`, response.data);
                    resolve({
                        success: true,
                        fieldName: fieldName,
                        data: response.data
                    });
                } else {
                    console.error(`❌ Ошибка загрузки файла ${fieldName}:`, response.error);
                    reject(new Error(response.error));
                }
            },
            error: function(xhr, status, error) {
                console.error(`❌ AJAX ошибка загрузки файла ${fieldName}:`, status, error);
                reject(new Error(`${status}: ${error}`));
            }
        });
    });
}

/**
 * Обновление сделки с данными формы (без файлов)
 */
async function updateDealWithFormData(form, formData) {
    return new Promise((resolve, reject) => {
        $.ajax({
            url: form.action,
            type: 'POST',
            data: formData,
            contentType: false,
            processData: false,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                'X-Requested-With': 'XMLHttpRequest'
            },
            success: function(response) {
                resolve(response);
            },
            error: function(xhr, status, error) {
                reject(new Error(`${status}: ${error}`));
            }
        });
    });
}

/**
 * Получение свежих данных сделки с сервера
 */
async function fetchDealData(dealId) {
    return new Promise((resolve, reject) => {
        $.ajax({
            url: `/deal/${dealId}/data`,
            type: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            },
            success: function(response) {
                resolve(response);
            },
            error: function(xhr, status, error) {
                reject(new Error(`${status}: ${error}`));
            }
        });
    });
}

/**
 * Извлечение ID сделки из формы
 */
function extractDealIdFromForm(form) {
    // Попробуем найти ID сделки разными способами
    const dealIdField = form.querySelector('input[name="deal_id"]') || 
                        form.querySelector('#dealIdField') ||
                        document.getElementById('dealIdField');
    
    if (dealIdField && dealIdField.value) {
        return dealIdField.value;
    }
    
    // Попробуем извлечь из URL формы
    const actionUrl = form.action;
    const dealIdMatch = actionUrl.match(/\/deal\/(\d+)/);
    if (dealIdMatch) {
        return dealIdMatch[1];
    }
    
    // Попробуем найти в URL страницы
    const pageUrlMatch = window.location.href.match(/\/deal\/(\d+)/);
    if (pageUrlMatch) {
        return pageUrlMatch[1];
    }
    
    console.error('❌ Не удалось найти ID сделки');
    return null;
}

// Делаем функцию доступной глобально
window.handleMultipleYandexFileUpload = handleMultipleYandexFileUpload;

/**
 * Проверка необходимых библиотек
 */
function checkRequiredLibraries(callback) {
    let librariesLoaded = true;
    
    // Проверка DataTables
    if (typeof $.fn.DataTable === 'undefined') {
        console.log('🔍 jQuery DataTables не загружен! Загружаем DataTables...');
        librariesLoaded = false;
        
        // Загружаем CSS
        $('<link>').attr({
            rel: 'stylesheet',
            type: 'text/css',
            href: 'https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css'
        }).appendTo('head');
        
        // Загружаем JS
        $.getScript('https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js', function() {
            console.log('✅ DataTables успешно загружен');
            checkComplete();
        });
    }
    
    // Проверка Select2
    if (typeof $.fn.select2 === 'undefined') {
        console.log('🔍 jQuery Select2 не загружен! Загружаем Select2...');
        librariesLoaded = false;
        
        // Загружаем CSS
        $('<link>').attr({
            rel: 'stylesheet',
            type: 'text/css',
            href: 'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css'
        }).appendTo('head');
        
        // Загружаем JS
        $.getScript('https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js', function() {
            console.log('✅ Select2 успешно загружен');
            checkComplete();
        });
    }
    
    // Если все библиотеки уже загружены, вызываем callback сразу
    if (librariesLoaded) {
        console.log('✅ Все необходимые библиотеки уже загружены');
        callback();
    }
    
    // Функция проверки загрузки всех библиотек
    function checkComplete() {
        if (typeof $.fn.DataTable !== 'undefined' && typeof $.fn.select2 !== 'undefined') {
            console.log('✅ Все необходимые библиотеки загружены');
            callback();
        }
    }
}

/**
 * Инициализация Select2 элементов
 */
function initializeSelect2() {
    console.log('🔧 Инициализация Select2 элементов...');
    
    try {
        // Проверка доступности Select2
        if (typeof $.fn.select2 === 'undefined') {
            console.error('❌ Select2 не доступен для инициализации');
            return;
        }
        
        // Инициализация всех select элементов с классом select2
        $('select.select2').each(function() {
            if (!$(this).hasClass('select2-hidden-accessible')) {
                var options = {
                    placeholder: $(this).data('placeholder') || 'Выберите...',
                    allowClear: $(this).data('allow-clear') === true,
                    width: '100%'
                };
                
                $(this).select2(options);
                console.log('✅ Select2 инициализирован для элемента:', this.name || this.id);
            }
        });
        
        // Инициализация select элементов без класса select2, но с data-select2 атрибутом
        $('select[data-select2]').each(function() {
            if (!$(this).hasClass('select2-hidden-accessible')) {
                var options = {
                    placeholder: $(this).data('placeholder') || 'Выберите...',
                    allowClear: $(this).data('allow-clear') === true,
                    width: '100%'
                };
                
                $(this).select2(options);
                console.log('✅ Select2 инициализирован для элемента с data-select2:', this.name || this.id);
            }
        });
        
        console.log('✅ Инициализация Select2 завершена');
        
    } catch (error) {
        console.error('❌ Ошибка при инициализации Select2:', error);
    }
}

/**
 * Инициализация AJAX-обновления для формы сделки
 */
function initAjaxDealUpdate() {
    // Проверяем наличие формы редактирования сделки
    if ($('#deal-edit-form').length === 0) {
        console.warn('Форма редактирования сделки не найдена');
        return;
    }

    // Обработчик для быстрого сохранения
    $(document).on('click', '#quickSaveButton', function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        const form = document.getElementById('deal-edit-form');
        const formData = new FormData(form);
        
        // Проверяем наличие файлов для загрузки на Яндекс.Диск
        const hasYandexFiles = checkForYandexFiles(form);
        
        // Показываем индикатор загрузки
        showDealUpdateLoader();
        
        if (hasYandexFiles) {
            console.log('🚀 Обнаружены файлы для загрузки на Яндекс.Диск');
            
            // Загружаем все файлы одновременно
            handleMultipleYandexFileUpload(form).then(function(response) {
                hideDealUpdateLoader();
                if (response && response.success) {
                    showDealUpdateSuccess('Сделка успешно обновлена с загрузкой файлов');
                    updateDealData(response.deal);
                    
                    // Принудительно обновляем ссылки через новую систему
                    if (window.forceUpdateYandexLinks) {
                        setTimeout(window.forceUpdateYandexLinks, 500);
                    }
                    
                    // Генерируем событие для других систем
                    const event = new CustomEvent('dealUpdated', {
                        detail: { deal: response.deal }
                    });
                    document.dispatchEvent(event);
                } else {
                    showDealUpdateError(response.message || 'Произошла ошибка при загрузке файлов');
                }
            }).catch(function(error) {
                hideDealUpdateLoader();
                showDealUpdateError('Ошибка загрузки файлов: ' + error.message);
            });
            return;
        }

        // Обычная обработка
        $.ajax({
            url: form.action,
            type: 'POST',
            data: formData,
            contentType: false,
            processData: false,
            cache: false,
            timeout: 0,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                'X-Requested-With': 'XMLHttpRequest'
            },
            success: function(response) {
                hideDealUpdateLoader();
                
                if (response.success) {
                    showDealUpdateSuccess('Сделка успешно обновлена');
                    updateDealData(response.deal);
                    
                    // Обновляем файловые ссылки через новую систему
                    updateFileLinksInDeal(response.deal);
                    
                    // Уведомляем новую систему Яндекс.Диска об обновлении
                    if (window.forceUpdateYandexLinks) {
                        window.forceUpdateYandexLinks();
                    }
                    
                    // Генерируем событие для других систем
                    const event = new CustomEvent('dealUpdated', {
                        detail: { deal: response.deal }
                    });
                    document.dispatchEvent(event);
                } else {
                    showDealUpdateError(response.message || 'Произошла ошибка при обновлении сделки');
                }
            },
            error: function(xhr, status, error) {
                hideDealUpdateLoader();
                
                if (xhr.status === 422) {
                    let errorMessages = [];
                    const errors = xhr.responseJSON.errors;
                    
                    for (let field in errors) {
                        errorMessages.push(errors[field][0]);
                        
                        // Подсвечиваем поля с ошибками
                        const fieldElement = $(`[name="${field}"]`);
                        if (fieldElement.length) {
                            fieldElement.addClass('is-invalid');
                            
                            // Добавляем сообщение об ошибке
                            if (!fieldElement.next('.invalid-feedback').length) {
                                fieldElement.after(`<div class="invalid-feedback">${errors[field][0]}</div>`);
                            }
                        }
                    }
                    
                    showDealUpdateError('Пожалуйста, исправьте ошибки в форме:<br>' + errorMessages.join('<br>'));
                } else {
                    showDealUpdateError('Произошла ошибка при обновлении сделки. Пожалуйста, попробуйте ещё раз.');
                }
                
                console.error('Ошибка при обновлении сделки:', error);
            }
        });
    });
    
    // Сброс класса ошибки при редактировании поля
    $(document).on('input change', '#deal-edit-form input, #deal-edit-form textarea, #deal-edit-form select', function() {
        $(this).removeClass('is-invalid');
        $(this).next('.invalid-feedback').remove();
    });
}

/**
 * Проверяет наличие файлов для загрузки на Яндекс.Диск
 * Улучшенная версия с дополнительными проверками
 */
function checkForYandexFiles(form) {
    try {
        // Улучшенный поиск полей для Яндекс.Диска с расширенным списком
        const yandexFieldNames = [
            'measurements_file', 'final_project_file', 'work_act', 
            'chat_screenshot', 'archicad_file', 'plan_final', 'final_collage',
            'screenshot_work_1', 'screenshot_work_2', 'screenshot_work_3', 
            'screenshot_final', 'execution_order_file', 'final_floorplan', 
            'contract_attachment'
        ];
        
        const yandexFileInputs = Array.from(form.querySelectorAll('input[type="file"]')).filter(input => {
            return input.classList.contains('yandex-upload') || 
                   input.getAttribute('data-upload-type') === 'yandex' ||
                   input.name.includes('_file') || 
                   yandexFieldNames.includes(input.name);
        });
        
        let hasFiles = false;

        if (!yandexFileInputs || yandexFileInputs.length === 0) {
            console.log('🔍 Полей для Яндекс.Диска не найдено');
            return false;
        }
        
        console.log(`🔍 Найдено ${yandexFileInputs.length} полей для Яндекс.Диска`);

        yandexFileInputs.forEach(input => {
            try {
                if (input.files && input.files.length > 0) {
                    hasFiles = true;
                    console.log(`🔍 Найден файл для Яндекс.Диска: ${input.name} - ${input.files[0].name} (${Math.round(input.files[0].size / 1024)} KB)`);
                }
            } catch (inputError) {
                console.log(`⚠️ Ошибка при проверке поля ${input.name || 'неизвестное поле'}:`, inputError);
            }
        });

        return hasFiles;
    } catch (error) {
        console.error('❌ Ошибка при проверке файлов для Яндекс.Диска:', error);
        return false;
    }
}

/**
 * Показать индикатор загрузки при сохранении сделки
 * Улучшенная версия с анимацией
 */
function showDealUpdateLoader() {
    // Создаем контейнер для уведомлений если его нет
    if ($('#dealUpdateNotification').length === 0) {
        $('body').append('<div id="dealUpdateNotification" class="notification-container"></div>');
    }
    
    // Очищаем предыдущие уведомления
    $('#dealUpdateNotification').empty();
    
    // Добавляем индикатор загрузки с анимацией
    $('#dealUpdateNotification').html(`
        <div class="alert alert-info d-flex align-items-center fade show animated fadeInDown" role="alert">
            <div class="spinner-border spinner-border-sm me-2" role="status">
                <span class="visually-hidden">Загрузка...</span>
            </div>
            <div>
                Сохранение данных...
            </div>
            </div>
    `);
    
    // Показываем индикатор загрузки
    $('#dealUpdateNotification').html(`
        <div class="alert alert-info alert-dismissible position-fixed" style="top: 20px; right: 20px; z-index: 9999; min-width: 300px;">
            <div class="d-flex align-items-center">
                <div class="spinner-border spinner-border-sm me-2" role="status">
                    <span class="visually-hidden">Загрузка...</span>
                </div>
                <div>Сохранение данных...</div>
                </div>
        </div>
    `).fadeIn(200);
    
    // Блокируем кнопку сохранения
    $('#quickSaveButton').prop('disabled', true);
}

/**
 * Скрыть индикатор загрузки
 */
function hideDealUpdateLoader() {
    $('#dealUpdateNotification').fadeOut(200);
    
    // Разблокируем кнопку сохранения
    $('#quickSaveButton').prop('disabled', false);
}

/**
 * Показать сообщение об успешном сохранении
 */
function showDealUpdateSuccess(message) {
    if ($('#dealUpdateNotification').length === 0) {
        $('body').append('<div id="dealUpdateNotification"></div>');
    }
    
    $('#dealUpdateNotification').html(`
        <div class="alert alert-success alert-dismissible position-fixed" style="top: 20px; right: 20px; z-index: 9999; min-width: 300px;">
            <i class="fas fa-check-circle me-2"></i>${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `).fadeIn(200);
    
    // Автоматически скрываем через 3 секунды
    setTimeout(function() {
        $('#dealUpdateNotification').fadeOut(500);
    }, 3000);
}

/**
 * Показать сообщение об ошибке
 */
function showDealUpdateError(message) {
    if ($('#dealUpdateNotification').length === 0) {
        $('body').append('<div id="dealUpdateNotification"></div>');
    }
    
    $('#dealUpdateNotification').html(`
        <div class="alert alert-danger alert-dismissible position-fixed" style="top: 20px; right: 20px; z-index: 9999; min-width: 300px;">
            <i class="fas fa-exclamation-circle me-2"></i>${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `).fadeIn(200);
    
    // Автоматически скрываем через 5 секунд
    setTimeout(function() {
        $('#dealUpdateNotification').fadeOut(500);
    }, 5000);
}

/**
 * Обновить данные сделки после успешного сохранения
 */
function updateDealData(dealData) {
    // Обновляем значения в полях формы
    for (let field in dealData) {
        const fieldElement = $(`#deal-edit-form [name="${field}"]`);
        if (fieldElement.length) {
            if (fieldElement.is('select')) {
                fieldElement.val(dealData[field]).trigger('change');
            } else {
                fieldElement.val(dealData[field]);
            }
        }
    }
    
    // Обновляем заголовок страницы если есть новые данные
    if (dealData.project_number) {
        document.title = `Редактирование сделки #${dealData.project_number}`;
    }
}

/**
 * Обновить файловые ссылки Яндекс.Диска
 */
function updateFileLinksInDeal(dealData) {
    console.log('� Запуск updateFileLinksInDeal с данными сделки', dealData);
    
    // Используем унифицированную систему если она доступна
    if (typeof window.updateFileLinksInDealModal === 'function') {
        try {
            window.updateFileLinksInDealModal(dealData);
            console.log('✅ Использована унифицированная система обновления ссылок');
            return;
        } catch (error) {
            console.error('❌ Ошибка в унифицированной системе:', error);
        }
    }
    
    // Запасной вариант если унифицированная система недоступна
    const yandexFields = [
        'measurements_file', 'final_project_file', 'work_act',
        'chat_screenshot', 'archicad_file', 'plan_final', 'final_collage',
        'screenshot_work_1', 'screenshot_work_2', 'screenshot_work_3',
        'screenshot_final', 'execution_order_file', 'contract_attachment',
        'final_floorplan'
    ];
    
    yandexFields.forEach(fieldName => {
        const yandexUrlField = 'yandex_url_' + fieldName;
        const originalNameField = 'original_name_' + fieldName;
        const yandexUrl = dealData[yandexUrlField];
        const originalName = dealData[originalNameField] || 'Просмотр файла';
        
        if (yandexUrl && yandexUrl.trim() !== '') {
            // Находим поле ввода файла
            const fileInput = $(`input[name="${fieldName}"]`);
            if (fileInput.length === 0) return;
            
            // Удаляем существующие ссылки
            fileInput.siblings('.btn-outline-success, .file-success, .yandex-unified-link').remove();
            fileInput.parent().find('.btn-outline-success, .file-success, .yandex-unified-link').remove();
            
            // Проверка валидности URL
            if (!isValidUrl(yandexUrl)) {
                console.warn(`⚠️ Невалидный URL для поля ${fieldName}: ${yandexUrl}`);
                return;
            }
            
            // Создаем новую улучшенную ссылку с анимацией и улучшенным дизайном
            const newFileLink = $(`
                <div class="yandex-file-link">
                    <i class="fas fa-cloud-download-alt"></i>
                    <a href="${yandexUrl}" target="_blank" class="file-link">
                        ${originalName}
                    </a>
                </div>
            `);
            
            // Удаляем предыдущие ссылки и добавляем новую
            fileInput.siblings('.yandex-file-link').remove();
            fileInput.after(newFileLink);
            
            // Проверяем доступность ссылки
            checkLinkAvailability(yandexUrl, fieldName);
            
            console.log(`✅ Обновлена ссылка для поля ${fieldName}`);
        }
    });
}

/**
 * Проверка валидности URL
 */
function isValidUrl(url) {
    try {
        new URL(url);
        return true;
    } catch (e) {
        return false;
    }
}

/**
 * Проверка доступности ссылки на файл
 */
function checkLinkAvailability(url, fieldName) {
    // Выполняем HEAD запрос для проверки доступности
    $.ajax({
        url: url,
        type: 'HEAD',
        cache: false,
        timeout: 5000,
        success: function() {
            console.log(`✅ Ссылка для ${fieldName} проверена и работает`);
        },
        error: function(xhr) {
            console.warn(`⚠️ Проблема с ссылкой для ${fieldName}: HTTP ${xhr.status}`);
            
            // Добавляем предупреждение к ссылке
            const fileLink = $(`input[name="${fieldName}"]`).siblings('.yandex-file-link');
            if (fileLink.length > 0) {
                fileLink.css('border-left-color', '#ffc107');
                fileLink.find('i').css('color', '#ffc107');
                fileLink.append(`<span class="ms-2 badge bg-warning text-dark">Проверьте доступ</span>`);
            }
        }
    });
}
</script>

<style>
/* Дополнительные стили для системы уведомлений */
#dealUpdateNotification {
    position: fixed;
    top: 20px;
    right: 20px;
    z-index: 9999;
    max-width: 350px;
}

#dealUpdateNotification .alert {
    margin-bottom: 10px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    border-left: 4px solid;
}

#dealUpdateNotification .alert-success {
    border-left-color: #28a745;
}

#dealUpdateNotification .alert-danger {
    border-left-color: #dc3545;
}

#dealUpdateNotification .alert-info {
    border-left-color: #17a2b8;
}

#dealUpdateNotification .spinner-border-sm {
    width: 1rem;
    height: 1rem;
}

/* Анимации для уведомлений */
.animated {
    animation-duration: 0.5s;
    animation-fill-mode: both;
}

@keyframes fadeInDown {
    from {
        opacity: 0;
        transform: translate3d(0, -30px, 0);
    }
    to {
        opacity: 1;
        transform: translate3d(0, 0, 0);
    }
}

.fadeInDown {
    animation-name: fadeInDown;
}

@keyframes fadeOut {
    from {
        opacity: 1;
    }
    to {
        opacity: 0;
    }
}

.fadeOut {
    animation-name: fadeOut;
}

/* Стили для ссылок на файлы */
.yandex-file-link {
    display: flex;
    align-items: center;
    padding: 8px 12px;
    margin-top: 8px;
    background-color: #f8f9fa;
    border-radius: 4px;
    border-left: 3px solid #28a745;
    transition: all 0.3s ease;
    animation: slideIn 0.3s ease;
    max-width: 100%;
    overflow: hidden;
}

.yandex-file-link:hover {
    background-color: #e9f7ef;
    transform: translateY(-2px);
}

.yandex-file-link i {
    margin-right: 8px;
    color: #28a745;
}

@keyframes slideIn {
    from {
        opacity: 0;
        transform: translateY(10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Стили для валидации */
.is-invalid {
    border-color: #dc3545;
}

.invalid-feedback {
    display: block;
    width: 100%;
    margin-top: 0.25rem;
    font-size: 0.875em;
    color: #dc3545;
}
</style>
