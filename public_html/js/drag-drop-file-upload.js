/**
 * Система drag & drop для загрузки файлов
 * Современный функциональный подход без классов
 */

// Глобальные переменные
let dragCounter = 0;

/**
 * Инициализация системы drag & drop
 */
function initDragDropFileUpload() {
    console.log('🚀 Инициализация Drag & Drop системы');
    
    // Находим все file input поля
    const fileInputs = document.querySelectorAll('input[type="file"]:not([data-drag-drop-initialized])');
    
    console.log(`🔍 Найдено ${fileInputs.length} неинициализированных файловых полей:`);
    fileInputs.forEach((input, index) => {
        console.log(`   ${index + 1}. ID: ${input.id}, Name: ${input.name}, Parent: ${input.parentNode.tagName}`);
    });
    
    fileInputs.forEach(input => {
        initFileInput(input);
    });
    
    // Предотвращаем стандартное поведение браузера для всей страницы
    preventDefaults();
    
    console.log(`✅ Инициализировано ${fileInputs.length} файловых полей`);
}

/**
 * Инициализация отдельного file input
 */
function initFileInput(input) {
    // Помечаем как инициализированное
    input.setAttribute('data-drag-drop-initialized', 'true');
    
    // Создаем drop zone
    const dropZone = createDropZone(input);
    
    // Привязываем события к input
    bindFileInputEvents(input);
    
    console.log(`🔧 Инициализирован input: ${input.name || input.id} (${input.type})`);
}

/**
 * Создание drop zone для file input
 */
function createDropZone(input) {
    // Проверяем, не был ли уже создан drop zone
    const existingDropZone = input.nextElementSibling;
    if (existingDropZone && existingDropZone.classList.contains('file-drop-zone')) {
        console.log('🔄 Drop zone уже существует для', input.name || input.id);
        return existingDropZone;
    }
    
    // Скрываем оригинальный input
   
    
    // Получаем информацию о поле
    const fieldInfo = getFieldInfo(input);
    
    // Создаем drop zone
    const dropZone = document.createElement('div');
    dropZone.className = 'file-drop-zone';
    dropZone.innerHTML = `
        <div class="drop-area">
            <div class="drop-icon">
                <i class="${fieldInfo.icon}"></i>
            </div>
            <div class="drop-text">
                <div class="drop-title">Перетащите файл сюда</div>
                <div class="drop-subtitle">или <span class="drop-link">нажмите для выбора</span></div>
                <div class="drop-info">
                    Поддерживаемые форматы: ${fieldInfo.types}<br>
                    Максимальный размер: ${fieldInfo.maxSize}
                </div>
            </div>
        </div>
        <div class="file-info">
            <div class="file-info-content">
                <div class="file-info-icon">
                    <i class="fas fa-file"></i>
                </div>
                <div class="file-info-details">
                    <div class="file-info-name"></div>
                    <div class="file-info-size"></div>
                </div>
                <button type="button" class="file-remove" title="Удалить файл">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>
        <div class="upload-progress">
            <div class="progress-bar">
                <div class="progress-fill"></div>
            </div>
            <div class="progress-text">0%</div>
        </div>
        <div class="error-message"></div>
    `;
    
    // Вставляем drop zone после input
    input.parentNode.insertBefore(dropZone, input.nextSibling);
    
    console.log(`📦 Создан drop zone для ${input.name || input.id}`);
    
    // Привязываем события к drop zone
    bindDropZoneEvents(dropZone, input);
    
    return dropZone;
}

/**
 * Получение информации о поле
 */
function getFieldInfo(input) {
    const accept = input.getAttribute('accept') || '';
    
    if (accept.includes('image/')) {
        return {
            icon: 'fas fa-image',
            types: 'JPG, PNG, GIF',
            maxSize: '1500 МБ'
        };
    } else if (accept.includes('application/pdf')) {
        return {
            icon: 'fas fa-file-pdf',
            types: 'PDF',
            maxSize: '1500 МБ'
        };
    } else if (accept.includes('.dwg') || accept.includes('.pln')) {
        return {
            icon: 'fas fa-drafting-compass',
            types: 'DWG, PLN',
            maxSize: '1500 МБ'
        };
    } else {
        return {
            icon: 'fas fa-file-upload',
            types: 'Любые файлы',
            maxSize: '1500 МБ'
        };
    }
}

/**
 * Привязка событий к drop zone
 */
function bindDropZoneEvents(dropZone, input) {
    const dropArea = dropZone.querySelector('.drop-area');
    const dropLink = dropZone.querySelector('.drop-link');
    
    // Drag & Drop события
    dropArea.addEventListener('dragenter', (e) => handleDragEnter(e, dropZone));
    dropArea.addEventListener('dragover', (e) => handleDragOver(e, dropZone));
    dropArea.addEventListener('dragleave', (e) => handleDragLeave(e, dropZone));
    dropArea.addEventListener('drop', (e) => handleDrop(e, dropZone, input));
    
    // Клик по всей области drop area
    dropArea.addEventListener('click', (e) => {
        e.preventDefault();
        e.stopPropagation();
        console.log('🖱️ Клик по drop area, открываем диалог выбора файла');
        input.click();
    });
    
    // Клик по ссылке "нажмите для выбора"
    if (dropLink) {
        dropLink.addEventListener('click', (e) => {
            e.preventDefault();
            e.stopPropagation();
            console.log('🖱️ Клик по ссылке выбора файла');
            input.click();
        });
    }
    
    // Кнопка удаления файла
    const removeBtn = dropZone.querySelector('.file-remove');
    if (removeBtn) {
        removeBtn.addEventListener('click', (e) => {
            e.preventDefault();
            e.stopPropagation();
            removeFile(dropZone, input);
        });
    }
}

/**
 * Привязка событий к file input
 */
function bindFileInputEvents(input) {
    // Событие изменения файла
    input.addEventListener('change', (e) => {
        const dropZone = input.nextElementSibling;
        if (e.target.files.length > 0) {
            const file = e.target.files[0];
            handleFileSelection(dropZone, input, file);
        } else {
            // Файл был удален
            showDropArea(dropZone);
        }
    });
}

/**
 * Обработка drag enter
 */
function handleDragEnter(e, dropZone) {
    e.preventDefault();
    e.stopPropagation();
    dragCounter++;
    dropZone.classList.add('dragover');
}

/**
 * Обработка drag over
 */
function handleDragOver(e, dropZone) {
    e.preventDefault();
    e.stopPropagation();
    dropZone.classList.add('dragover');
}

/**
 * Обработка drag leave
 */
function handleDragLeave(e, dropZone) {
    e.preventDefault();
    e.stopPropagation();
    dragCounter--;
    if (dragCounter === 0) {
        dropZone.classList.remove('dragover');
    }
}

/**
 * Обработка drop
 */
function handleDrop(e, dropZone, input) {
    e.preventDefault();
    e.stopPropagation();
    
    dragCounter = 0;
    dropZone.classList.remove('dragover');
    
    const files = e.dataTransfer.files;
    if (files.length > 0) {
        const file = files[0];
        
        // Создаем новый DataTransfer и добавляем файл
        const dataTransfer = new DataTransfer();
        dataTransfer.items.add(file);
        input.files = dataTransfer.files;
        
        // Обрабатываем выбор файла
        handleFileSelection(dropZone, input, file);
        
        // Запускаем событие change для совместимости
        const changeEvent = new Event('change', { bubbles: true });
        input.dispatchEvent(changeEvent);
    }
}

/**
 * Обработка выбора файла
 */
function handleFileSelection(dropZone, input, file) {
    console.log('📁 Файл выбран:', file.name);
    
    // Валидация файла
    const validation = validateFile(file, input);
    if (!validation.valid) {
        showError(dropZone, validation.message);
        input.value = '';
        return;
    }
    
    // Скрываем ошибки
    hideError(dropZone);
    
    // Показываем информацию о файле
    showFileInfo(dropZone, file);
    
    // Имитируем загрузку
    simulateUpload(dropZone);
}

/**
 * Валидация файла
 */
function validateFile(file, input) {
    const accept = input.getAttribute('accept');
    const maxSize = 1500 * 1024 * 1024; // 1500 МБ
    
    // Проверка размера
    if (file.size > maxSize) {
        return {
            valid: false,
            message: 'Файл слишком большой. Максимальный размер: 1500 МБ'
        };
    }
    
    // Проверка типа файла
    if (accept && !isFileTypeAllowed(file, accept)) {
        return {
            valid: false,
            message: `Неподдерживаемый тип файла. Разрешенные типы: ${accept}`
        };
    }
    
    return { valid: true };
}

/**
 * Проверка разрешенного типа файла
 */
function isFileTypeAllowed(file, accept) {
    const acceptTypes = accept.split(',').map(type => type.trim());
    
    return acceptTypes.some(type => {
        if (type.startsWith('.')) {
            return file.name.toLowerCase().endsWith(type.toLowerCase());
        } else {
            return file.type.startsWith(type.replace('*', ''));
        }
    });
}

/**
 * Показать информацию о файле
 */
function showFileInfo(dropZone, file) {
    const dropArea = dropZone.querySelector('.drop-area');
    const fileInfo = dropZone.querySelector('.file-info');
    const icon = getFileIcon(file);
    
    // Обновляем информацию о файле
    const iconElement = fileInfo.querySelector('.file-info-icon i');
    const nameElement = fileInfo.querySelector('.file-info-name');
    const sizeElement = fileInfo.querySelector('.file-info-size');
    
    iconElement.className = icon;
    nameElement.textContent = file.name;
    nameElement.title = file.name;
    sizeElement.textContent = formatFileSize(file.size);
    
    // Скрываем drop area и показываем file info
    dropArea.style.display = 'none';
    fileInfo.classList.add('show');
}

/**
 * Показать drop area
 */
function showDropArea(dropZone) {
    const dropArea = dropZone.querySelector('.drop-area');
    const fileInfo = dropZone.querySelector('.file-info');
    const progress = dropZone.querySelector('.upload-progress');
    
    // Показываем drop area и скрываем остальное
    dropArea.style.display = 'flex';
    fileInfo.classList.remove('show');
    progress.classList.remove('show');
}

/**
 * Удаление файла
 */
function removeFile(dropZone, input) {
    console.log('🗑️ Удаление файла');
    
    // Очищаем input
    input.value = '';
    
    // Показываем drop area
    showDropArea(dropZone);
    
    // Скрываем ошибки
    hideError(dropZone);
    
    // Запускаем событие change
    const changeEvent = new Event('change', { bubbles: true });
    input.dispatchEvent(changeEvent);
}

/**
 * Имитация загрузки файла
 */
function simulateUpload(dropZone) {
    const progress = dropZone.querySelector('.upload-progress');
    const progressFill = dropZone.querySelector('.progress-fill');
    const progressText = dropZone.querySelector('.progress-text');
    
    progress.classList.add('show');
    
    let currentProgress = 0;
    const interval = setInterval(() => {
        currentProgress += Math.random() * 15;
        if (currentProgress >= 100) {
            currentProgress = 100;
            clearInterval(interval);
            
            // Скрываем прогресс через секунду
            setTimeout(() => {
                progress.classList.remove('show');
            }, 1000);
        }
        
        progressFill.style.width = currentProgress + '%';
        progressText.textContent = Math.round(currentProgress) + '%';
    }, 200);
}

/**
 * Показать ошибку
 */
function showError(dropZone, message) {
    const errorElement = dropZone.querySelector('.error-message');
    errorElement.textContent = message;
    errorElement.style.display = 'block';
    dropZone.classList.add('error');
    
    // Автоматически скрываем ошибку через 5 секунд
    setTimeout(() => {
        hideError(dropZone);
    }, 5000);
}

/**
 * Скрыть ошибку
 */
function hideError(dropZone) {
    const errorElement = dropZone.querySelector('.error-message');
    errorElement.style.display = 'none';
    errorElement.textContent = '';
    dropZone.classList.remove('error');
}

/**
 * Получение иконки файла
 */
function getFileIcon(file) {
    const ext = file.name.split('.').pop().toLowerCase();
    const type = file.type;
    
    if (type.startsWith('image/')) {
        return 'fas fa-file-image';
    } else if (type === 'application/pdf') {
        return 'fas fa-file-pdf';
    } else if (['doc', 'docx'].includes(ext)) {
        return 'fas fa-file-word';
    } else if (['xls', 'xlsx'].includes(ext)) {
        return 'fas fa-file-excel';
    } else if (['dwg', 'pln'].includes(ext)) {
        return 'fas fa-drafting-compass';
    } else if (['zip', 'rar', '7z'].includes(ext)) {
        return 'fas fa-file-archive';
    } else {
        return 'fas fa-file';
    }
}

/**
 * Форматирование размера файла
 */
function formatFileSize(bytes) {
    if (bytes === 0) return '0 Bytes';
    
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
}

/**
 * Предотвращение стандартного поведения браузера
 */
function preventDefaults() {
    // Предотвращаем стандартное поведение drag & drop для всей страницы
    ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
        document.addEventListener(eventName, (e) => {
            // Разрешаем только для наших drop zone
            if (!e.target.closest('.file-drop-zone')) {
                e.preventDefault();
                e.stopPropagation();
            }
        }, false);
    });
    
    // Сбрасываем счетчик при уходе курсора с документа
    document.addEventListener('dragleave', (e) => {
        if (e.clientX === 0 && e.clientY === 0) {
            dragCounter = 0;
        }
    });
}

/**
 * Переинициализация для динамически добавленных элементов
 */
function reinitializeDragDrop() {
    initDragDropFileUpload();
}

/**
 * Инициализация при загрузке DOM
 */
document.addEventListener('DOMContentLoaded', () => {
    initDragDropFileUpload();
    
    // Наблюдатель за изменениями DOM для автоинициализации новых полей
    const observer = new MutationObserver((mutations) => {
        let needsReinit = false;
        
        mutations.forEach((mutation) => {
            mutation.addedNodes.forEach((node) => {
                if (node.nodeType === 1) { // Element node
                    const fileInputs = node.querySelectorAll ? 
                        node.querySelectorAll('input[type="file"]:not([data-drag-drop-initialized])') : [];
                    
                    if (fileInputs.length > 0) {
                        needsReinit = true;
                    }
                }
            });
        });
        
        if (needsReinit) {
            console.log('🔄 Обнаружены новые файловые поля, переинициализация...');
            initDragDropFileUpload();
        }
    });
    
    observer.observe(document.body, {
        childList: true,
        subtree: true
    });
    
    console.log('✅ Drag & Drop система полностью инициализирована');
});

// Экспорт функций для использования в других скриптах
window.DragDropFileUpload = {
    init: initDragDropFileUpload,
    reinit: reinitializeDragDrop,
    test: function() {
        console.log('🧪 ТЕСТИРОВАНИЕ DRAG & DROP СИСТЕМЫ');
        console.log('=================================');
        
        // Проверяем все file inputs
        const allFileInputs = document.querySelectorAll('input[type="file"]');
        const initializedInputs = document.querySelectorAll('input[type="file"][data-drag-drop-initialized="true"]');
        const dropZones = document.querySelectorAll('.file-drop-zone');
        
        console.log(`📊 Статистика:`);
        console.log(`   Всего файловых полей: ${allFileInputs.length}`);
        console.log(`   Инициализированных: ${initializedInputs.length}`);
        console.log(`   Drop zone элементов: ${dropZones.length}`);
        
        // Проверяем каждое поле
        allFileInputs.forEach((input, index) => {
            const isInitialized = input.hasAttribute('data-drag-drop-initialized');
            const hasDropZone = input.nextElementSibling && input.nextElementSibling.classList.contains('file-drop-zone');
            
            console.log(`${index + 1}. ${input.id || input.name}:`);
            console.log(`   Инициализировано: ${isInitialized ? '✅' : '❌'}`);
            console.log(`   Есть drop zone: ${hasDropZone ? '✅' : '❌'}`);
            console.log(`   Display: ${input.style.display}`);
        });
        
        return {
            total: allFileInputs.length,
            initialized: initializedInputs.length,
            dropZones: dropZones.length
        };
    }
};
