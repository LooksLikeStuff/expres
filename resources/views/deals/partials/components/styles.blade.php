<!-- Стили для страницы редактирования сделки -->
<style>
/* === ОСНОВНЫЕ СТИЛИ СТРАНИЦЫ === */
.deal-edit-container {
    background: #f8fafc;
    min-height: calc(100vh - 120px);
    padding: 20px;
}

.deal-edit-header {
    background: linear-gradient(135deg, #007bff 0%, #7c3aed 100%);
    padding: 24px 32px;
    border-radius: 16px;
    margin-bottom: 24px;
    color: white;
    display: flex;
    align-items: center;
    justify-content: space-between;
    box-shadow: 0 8px 32px rgba(79, 70, 229, 0.2);
}

.deal-edit-header h1 {
    margin: 0;
    font-size: 28px;
    font-weight: 600;
}

.deal-edit-breadcrumb .btn {
    background: rgba(255, 255, 255, 0.2);
    border: 1px solid rgba(255, 255, 255, 0.3);
    color: white;
    border-radius: 10px;
    padding: 10px 20px;
    text-decoration: none;
    transition: all 0.3s ease;
}

.deal-edit-breadcrumb .btn:hover {
    background: rgba(255, 255, 255, 0.3);
    transform: translateY(-2px);
}

/* === СИСТЕМА ВКЛАДОК === */
.deal-tabs-container {
    background: transparent;
    border-radius: 16px 16px 0 0;
    overflow: hidden;
    position: relative;
}

.deal-tabs-nav {
    display: flex;
    background: transparent;
    padding: 16px 16px 0 16px;
    gap: 4px;
    overflow-x: auto;
    scrollbar-width: none;
    -ms-overflow-style: none;
}

.deal-tabs-nav::-webkit-scrollbar {
    display: none;
}

.deal-tab-button {
background: #007bff42 !important;
    border: 2px solid rgba(255, 255, 255, 0.2);
    padding: 16px 24px;
    cursor: pointer;
    font-size: 14px;
    font-weight: 600;
    color: rgba(255, 255, 255, 0.8);
    border-radius: 12px 12px 0 0;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    white-space: nowrap;
    display: flex;
    align-items: center;
    gap: 10px;
    position: relative;
    min-width: auto;
    backdrop-filter: blur(10px);
    -webkit-backdrop-filter: blur(10px);
}

.deal-tab-button i {
    font-size: 16px;
    transition: transform 0.3s ease;
}

.deal-tab-button span {
    transition: all 0.3s ease;
    font-weight: 500;
}

.deal-tab-button:hover {
    background: rgba(255, 255, 255, 0.2);
    border-color: rgba(255, 255, 255, 0.4);
    color: white;
    transform: translateY(-2px);
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);
}

.deal-tab-button:hover i {
    transform: scale(1.1);
}

.deal-tab-button.active {
  background: #007bff42 !important;
    color: #007bff !important;
    border-color: white;
    position: relative;
    z-index: 2;
}

.deal-tab-button.active::after {
    content: '';
    position: absolute;
    bottom: -2px;
    left: 0;
    right: 0;
    height: 2px;
    background: white;
    z-index: 1;
}

.deal-tab-button.active i {
    color: #007bff !important;
    transform: scale(1.1);
}

.deal-tab-button.active span {
    color: #007bff !important;
    font-weight: 600;
}

.deal-tabs-content {
    background: white;
    padding: 32px;
    min-height: 600px;
    border-radius: 0 0 16px 16px;
}

.deal-tab-pane {
    display: none;
}

.deal-tab-pane.active {
    display: block;
    animation: slideInUp 0.4s cubic-bezier(0.4, 0, 0.2, 1);
}

/* Также для bootstrap tab-pane */
.tab-pane {
    display: none;
}

.tab-pane.show.active {
    display: block;
    animation: slideInUp 0.4s cubic-bezier(0.4, 0, 0.2, 1);
}

@keyframes slideInUp {
    from { 
        opacity: 0; 
        transform: translateY(20px); 
    }
    to { 
        opacity: 1; 
        transform: translateY(0); 
    }
}

/* === СТИЛИ ПОЛЕЙ ФОРМЫ === */
.module__deal {
    background: linear-gradient(135deg, #fefefe 0%, #f0f4f8 100%);
    border: 1px solid #e2e8f0;
    border-radius: 16px;
    padding: 24px;
    margin-bottom: 24px;
    position: relative;
    overflow: hidden;
}

.module__deal legend {
    background: linear-gradient(135deg, #007bff 0%, #7c3aed 100%);
    color: white;
    padding: 12px 24px;
    border-radius: 10px;
    font-size: 16px;
    font-weight: 600;
    border: none;
    margin-bottom: 20px;
    box-shadow: 0 4px 16px rgba(79, 70, 229, 0.2);
}

/* === УНИФИЦИРОВАННЫЕ СТИЛИ ДЛЯ ВСЕХ ПОЛЕЙ ФОРМЫ === */
.form-label {
    font-weight: 600;
    color: #374151;
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 14px;
    margin-bottom: 8px;
}

.form-label i {
    color: #007bff;
    font-size: 16px;
}

.form-label .text-danger {
    color: #dc2626 !important;
}

/* Базовые стили для всех контролов */
.form-control,
.form-select {
    width: 100%;
    padding: 12px 16px;
    border: 2px solid #e2e8f0 !important;
 
    font-size: 14px;
    font-family: inherit;
    line-height: 1.5;
    background: white;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
}

.form-control:focus,
.form-select:focus {
    border-color: #007bff;
    box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.15), 0 4px 6px rgba(0, 0, 0, 0.1);
    outline: none;
    transform: translateY(-1px);
}

.form-control:hover,
.form-select:hover {
    border-color: #c7d2fe;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.08);
}

.form-control[disabled],
.form-select[disabled] {
    background: #f1f5f9;
    color: #64748b;
    cursor: not-allowed;
    opacity: 0.7;
}

/* Специальные стили для textarea */
.form-control[type="textarea"],
textarea.form-control {
    min-height: 100px;
    resize: vertical;
    line-height: 1.6;
}

/* === КАСТОМНАЯ СТИЛИЗАЦИЯ INPUT FILE === */
.file-upload-wrapper {
    position: relative;
    display: inline-block;
    width: 100%;
}

.form-control[type="file"] {
    position: absolute;
    opacity: 0;
    width: 100%;
    height: 100%;
    cursor: pointer;
    z-index: 2;
}

.file-upload-display {
    position: relative;
    z-index: 1;
    display: flex;
    align-items: center;
    padding: 12px 16px;
    border: 2px dashed #e2e8f0;
    border-radius: 10px;
    background: linear-gradient(135deg, #fafbff 0%, #f0f4f8 100%);
    cursor: pointer;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    min-height: 52px;
}

.file-upload-display:hover {
    border-color: #007bff;
    background: linear-gradient(135deg, #f0f4f8 0%, #e6f2ff 100%);
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(79, 70, 229, 0.15);
}

.file-upload-display.has-file {
    border-style: solid;
    border-color: #059669;
    background: linear-gradient(135deg, #f0fdf4 0%, #ecfdf5 100%);
}

.file-upload-icon {
    font-size: 20px;
    color: #007bff;
    margin-right: 12px;
    transition: all 0.3s ease;
}

.file-upload-display:hover .file-upload-icon {
    transform: scale(1.1);
    color: #3730a3;
}

.file-upload-display.has-file .file-upload-icon {
    color: #059669;
}

.file-upload-text {
    flex: 1;
    display: flex;
    flex-direction: column;
    gap: 2px;
}

.file-upload-primary {
    font-weight: 500;
    color: #374151;
    font-size: 14px;
}

.file-upload-secondary {
    font-size: 12px;
    color: #6b7280;
}

.file-upload-display.has-file .file-upload-primary {
    color: #059669;
    font-weight: 600;
}

.file-upload-actions {
    display: flex;
    gap: 8px;
    align-items: center;
}

.file-upload-btn {
    background: #007bff;
    color: white;
    border: none;
    padding: 6px 12px;
    border-radius: 6px;
    font-size: 12px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    gap: 4px;
}

.file-upload-btn:hover {
    background: #3730a3;
    transform: translateY(-1px);
    box-shadow: 0 2px 8px rgba(79, 70, 229, 0.3);
}

.file-upload-btn.btn-remove {
    background: #dc2626;
}

.file-upload-btn.btn-remove:hover {
    background: #b91c1c;
}

/* Стили для групп полей */
.form-group,
.col-md-6,
.col-12 {
    margin-bottom: 20px;
}

.form-group-deal {
    margin-bottom: 20px;
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.form-group-deal label {
    font-weight: 600;
    color: #374151;
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 14px;
}

.read-only-hint {
    font-size: 12px;
    color: #64748b;
    font-style: italic;
    margin-top: 4px;
}

/* === КНОПКИ === */
.deal-save-buttons {
    background: white;
    padding: 20px 32px;
    border-top: 2px solid #e2e8f0;
    display: flex;
    gap: 12px;
    justify-content: flex-end;
    border-radius: 0 0 16px 16px;
}

.btn-save {
    background: linear-gradient(135deg, #059669 0%, #0d9488 100%);
    color: white;
    border: none;
    padding: 12px 24px;
    border-radius: 10px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    gap: 8px;
}

.btn-save:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 24px rgba(5, 150, 105, 0.3);
}

.btn-cancel {
    background: #f1f5f9;
    color: #64748b;
    border: 2px solid #e2e8f0;
    padding: 12px 24px;
    border-radius: 10px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    text-decoration: none;
    display: flex;
    align-items: center;
    gap: 8px;
}

.btn-cancel:hover {
    background: #e2e8f0;
    color: #475569;
}

/* === АДАПТИВНОСТЬ === */
@media (max-width: 768px) {
    .deal-edit-header {
        flex-direction: column;
        gap: 16px;
        text-align: center;
    }
    
    .deal-tabs-nav {
        padding: 12px 8px 0 8px;
        gap: 2px;
    }
    
    .deal-tab-button {
        padding: 12px 16px;
        font-size: 12px;
        min-width: auto;
        flex-direction: column;
        gap: 4px;
        text-align: center;
    }
    
    .deal-tab-button i {
        font-size: 14px;
    }
    
    .deal-tab-button span {
        font-size: 11px;
        line-height: 1.2;
    }
    
    .deal-tabs-content {
        padding: 20px 16px;
    }
    
    .deal-save-buttons {
        padding: 16px;
        flex-direction: column;
        gap: 8px;
    }
    
    .btn-save,
    .btn-cancel {
        width: 100%;
        justify-content: center;
    }
}

@media (max-width: 480px) {
    .deal-tabs-nav {
        padding: 8px 4px 0 4px;
        gap: 1px;
    }
    
    .deal-tab-button {
        padding: 10px 8px;
        font-size: 10px;
    }
    
    .deal-tab-button span {
        display: none;
    }
    
    .deal-tab-button i {
        font-size: 16px;
    }
    
    .deal-tabs-content {
        padding: 16px 12px;
    }
}

/* === LOADING СОСТОЯНИЕ === */
.loading-overlay {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.5);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 9999;
}

.loading-spinner {
    background: white;
    padding: 32px;
    border-radius: 16px;
    text-align: center;
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.2);
}

.spinner {
    width: 40px;
    height: 40px;
    border: 4px solid #e2e8f0;
    border-left: 4px solid #007bff;
    border-radius: 50%;
    animation: spin 1s linear infinite;
    margin: 0 auto 16px;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

/* === ОТЛАДОЧНЫЕ СТИЛИ === */
.debug-tab-info {
    position: fixed;
    top: 10px;
    left: 10px;
    background: rgba(0, 0, 0, 0.8);
    color: white;
    padding: 10px;
    border-radius: 5px;
    font-size: 12px;
    z-index: 9999;
    max-width: 300px;
}

/* Подсветка активных элементов для отладки */
.deal-tab-button.active {
    background: #007bff42 !important;
    color: #007bff !important;
}

.tab-pane.active {
    display: block !important;
}

/* === ДОПОЛНИТЕЛЬНЫЕ СТИЛИ ДЛЯ РАЗЛИЧНЫХ СОСТОЯНИЙ === */
.form-control.is-valid,
.form-select.is-valid {
    border-color: #059669;
    box-shadow: 0 0 0 3px rgba(5, 150, 105, 0.15);
}

.form-control.is-invalid,
.form-select.is-invalid {
    border-color: #dc2626;
    box-shadow: 0 0 0 3px rgba(220, 38, 38, 0.15);
}

.invalid-feedback {
    display: block;
    width: 100%;
    margin-top: 4px;
    font-size: 12px;
    color: #dc2626;
}

.valid-feedback {
    display: block;
    width: 100%;
    margin-top: 4px;
    font-size: 12px;
    color: #059669;
}

/* Стили для загрузки */
.file-upload-display.loading {
    opacity: 0.7;
    pointer-events: none;
}

.file-upload-display.loading::after {
    content: '';
    position: absolute;
    top: 50%;
    left: 50%;
    width: 20px;
    height: 20px;
    margin: -10px 0 0 -10px;
    border: 2px solid #e2e8f0;
    border-top: 2px solid #007bff;
    border-radius: 50%;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Инициализация file input компонентов
    initializeFileInputs();
    
    // Инициализация вкладок
    initializeTabs();
    
    function initializeTabs() {
        const tabButtons = document.querySelectorAll('.deal-tab-button');
        const tabPanes = document.querySelectorAll('.tab-pane');
        
        console.log('🔍 Найдено вкладок:', tabButtons.length);
        console.log('🔍 Найдено панелей:', tabPanes.length);
        
        tabButtons.forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                console.log('🚀 Клик по вкладке:', this.id);
                
                const targetId = this.getAttribute('data-bs-target') || this.getAttribute('aria-controls');
                if (!targetId) {
                    console.error('❌ Не найден target для вкладки:', this.id);
                    return;
                }
                
                const targetPane = document.querySelector(targetId.startsWith('#') ? targetId : '#' + targetId);
                if (!targetPane) {
                    console.error('❌ Не найдена панель:', targetId);
                    return;
                }
                
                // Убираем активность со всех вкладок
                tabButtons.forEach(btn => {
                    btn.classList.remove('active');
                    btn.setAttribute('aria-selected', 'false');
                });
                
                // Скрываем все панели
                tabPanes.forEach(pane => {
                    pane.classList.remove('show', 'active');
                });
                
                // Активируем текущую вкладку
                this.classList.add('active');
                this.setAttribute('aria-selected', 'true');
                
                // Показываем целевую панель
                targetPane.classList.add('show', 'active');
                
                console.log('✅ Переключение на:', targetId);
            });
        });
        
        // Проверяем, есть ли активная вкладка при загрузке
        const activeTab = document.querySelector('.deal-tab-button.active');
        if (activeTab) {
            const targetId = activeTab.getAttribute('data-bs-target') || activeTab.getAttribute('aria-controls');
            if (targetId) {
                const targetPane = document.querySelector(targetId.startsWith('#') ? targetId : '#' + targetId);
                if (targetPane) {
                    targetPane.classList.add('show', 'active');
                    console.log('✅ Активирована начальная вкладка:', targetId);
                }
            }
        }
    }
    
    function initializeFileInputs() {
        const fileInputs = document.querySelectorAll('input[type="file"]');
        
        fileInputs.forEach(input => {
            setupCustomFileInput(input);
        });
    }
    
    function setupCustomFileInput(input) {
        // Создаем wrapper если его нет
        if (!input.parentElement.classList.contains('file-upload-wrapper')) {
            const wrapper = document.createElement('div');
            wrapper.className = 'file-upload-wrapper';
            input.parentElement.insertBefore(wrapper, input);
            wrapper.appendChild(input);
            
            // Создаем custom display
            const display = createFileDisplay(input);
            wrapper.appendChild(display);
        }
        
        // Обработчик изменения файла
        input.addEventListener('change', function(e) {
            handleFileChange(e.target);
        });
    }
    
    function createFileDisplay(input) {
        const display = document.createElement('div');
        display.className = 'file-upload-display';
        
        const accept = input.getAttribute('accept') || '';
        const isImage = accept.includes('image');
        const fieldName = getFieldDisplayName(input.name);
        
        display.innerHTML = `
            <div class="file-upload-icon">
                <i class="fas ${isImage ? 'fa-image' : 'fa-file-upload'}"></i>
            </div>
            <div class="file-upload-text">
                <div class="file-upload-primary">
                    ${fieldName}
                </div>
                <div class="file-upload-secondary">
                    Нажмите для выбора${isImage ? ' изображения' : ' файла'}
                </div>
            </div>
            <div class="file-upload-actions">
                <button type="button" class="file-upload-btn">
                    <i class="fas fa-plus"></i> Выбрать
                </button>
            </div>
        `;
        
        // Обработчик клика
        display.addEventListener('click', function(e) {
            if (!e.target.classList.contains('btn-remove')) {
                input.click();
            }
        });
        
        return display;
    }
    
    function handleFileChange(input) {
        const display = input.parentElement.querySelector('.file-upload-display');
        const textDiv = display.querySelector('.file-upload-text');
        const actionsDiv = display.querySelector('.file-upload-actions');
        
        if (input.files && input.files.length > 0) {
            const file = input.files[0];
            const fileName = file.name;
            const fileSize = formatFileSize(file.size);
            
            // Обновляем отображение
            display.classList.add('has-file');
            
            textDiv.innerHTML = `
                <div class="file-upload-primary">
                    <i class="fas fa-check-circle me-1"></i>
                    ${fileName}
                </div>
                <div class="file-upload-secondary">
                    Размер: ${fileSize}
                </div>
            `;
            
            actionsDiv.innerHTML = `
                <button type="button" class="file-upload-btn btn-remove" onclick="clearFileInput('${input.id}')">
                    <i class="fas fa-times"></i> Удалить
                </button>
            `;
            
            // Показываем превью для изображений
            if (file.type.startsWith('image/')) {
                showImagePreview(file, display);
            }
            
        } else {
            // Возвращаем к исходному состоянию
            resetFileDisplay(input, display);
        }
    }
    
    function showImagePreview(file, display) {
        const reader = new FileReader();
        reader.onload = function(e) {
            // Проверяем, есть ли уже превью
            let preview = display.querySelector('.file-preview-image');
            if (!preview) {
                preview = document.createElement('div');
                preview.className = 'file-preview-image';
                preview.style.cssText = `
                    margin-top: 8px;
                    border-radius: 8px;
                    overflow: hidden;
                    border: 1px solid #e2e8f0;
                `;
                display.appendChild(preview);
            }
            
            preview.innerHTML = `
                <img src="${e.target.result}" 
                     style="width: 100%; max-width: 200px; height: auto; display: block;" 
                     alt="Предпросмотр">
            `;
        };
        reader.readAsDataURL(file);
    }
    
    function resetFileDisplay(input, display) {
        display.classList.remove('has-file');
        
        const accept = input.getAttribute('accept') || '';
        const isImage = accept.includes('image');
        const fieldName = getFieldDisplayName(input.name);
        
        const textDiv = display.querySelector('.file-upload-text');
        const actionsDiv = display.querySelector('.file-upload-actions');
        
        textDiv.innerHTML = `
            <div class="file-upload-primary">
                ${fieldName}
            </div>
            <div class="file-upload-secondary">
                Нажмите для выбора${isImage ? ' изображения' : ' файла'}
            </div>
        `;
        
        actionsDiv.innerHTML = `
            <button type="button" class="file-upload-btn">
                <i class="fas fa-plus"></i> Выбрать
            </button>
        `;
        
        // Удаляем превью
        const preview = display.querySelector('.file-preview-image');
        if (preview) {
            preview.remove();
        }
    }
    
    function getFieldDisplayName(fieldName) {
        const names = {
            'avatar_path': 'Аватар сделки',
            'measurements_file': 'Замеры',
            'final_project_file': 'Финал проекта',
            'work_act': 'Акт выполненных работ',
            'chat_screenshot': 'Скрин чата',
            'archicad_file': 'Исходный файл архикад',
            'plan_final': 'Планировка финал',
            'final_collage': 'Коллаж финал',
            'screenshot_work_1': 'Скриншот работы #1',
            'screenshot_work_2': 'Скриншот работы #2',
            'screenshot_work_3': 'Скриншот работы #3',
            'screenshot_work_4': 'Скриншот работы #4',
            'screenshot_work_5': 'Скриншот работы #5',
            'screenshot_final': 'Скриншот финального этапа',
            'execution_order_file': 'Заказ на исполнение',
            'contract_attachment': 'Приложение к договору',
            'final_floorplan': 'Финальная планировка'
        };
        return names[fieldName] || 'Файл';
    }
    
    function formatFileSize(bytes) {
        if (bytes === 0) return '0 Байт';
        const k = 1024;
        const sizes = ['Байт', 'КБ', 'МБ', 'ГБ'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    }
    
    // Глобальная функция для очистки файла
    window.clearFileInput = function(inputId) {
        const input = document.getElementById(inputId);
        if (input) {
            input.value = '';
            const display = input.parentElement.querySelector('.file-upload-display');
            resetFileDisplay(input, display);
        }
    };
});
});
</script>
