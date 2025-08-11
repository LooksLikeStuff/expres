/**
 * Исправление системы отображения ссылок Яндекс.Диска
 * Обеспечивает автоматическое обновление ссылок после загрузки файлов
 */

(function() {
    'use strict';
    
    console.log('🔧 Инициализация системы исправления ссылок Яндекс.Диска...');
    
    /**
     * Исправление отображения ссылок после успешного сохранения
     */
    function fixYandexLinksDisplay() {
        console.log('🚀 Запуск исправления отображения ссылок...');
        
        // Список полей Яндекс.Диска
        const yandexFields = [
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
        ];
        
        yandexFields.forEach(fieldName => {
            const urlField = document.querySelector(`input[name="yandex_url_${fieldName}"]`);
            const nameField = document.querySelector(`input[name="original_name_${fieldName}"]`);
            
            if (urlField && urlField.value && nameField && nameField.value) {
                console.log(`🔗 Обновляем ссылку для поля: ${fieldName}`);
                updateYandexLinkDisplay(fieldName, urlField.value, nameField.value);
            }
        });
    }
    
    /**
     * Обновление отображения ссылки для конкретного поля
     */
    function updateYandexLinkDisplay(fieldName, url, fileName) {
        // Получаем или создаем контейнер для ссылок
        let container = document.querySelector(`.yandex-file-links-container[data-field="${fieldName}"]`);
        
        if (!container) {
            container = createLinkContainer(fieldName);
        }
        
        if (!container) {
            console.warn(`⚠️ Не удалось создать контейнер для поля: ${fieldName}`);
            return;
        }
        
        // Очищаем контейнер от старых ссылок
        container.innerHTML = '';
        
        // Создаем новую ссылку
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
        if (linkWrapper) {
            linkWrapper.style.animation = 'slideInUp 0.5s ease-out';
        }
        
        console.log(`✅ Ссылка обновлена для поля: ${fieldName}`);
    }
    
    /**
     * Создание контейнера для ссылок
     */
    function createLinkContainer(fieldName) {
        // Ищем поле ввода файла
        const fileInput = document.querySelector(`input[name="${fieldName}"]`) ||
                         document.getElementById(fieldName);
        
        if (!fileInput) {
            console.warn(`⚠️ Поле ${fieldName} не найдено в DOM`);
            return null;
        }
        
        // Проверяем, есть ли уже контейнер
        let container = fileInput.parentElement.querySelector(`.yandex-file-links-container[data-field="${fieldName}"]`);
        
        if (!container) {
            // Создаем новый контейнер
            container = document.createElement('div');
            container.className = 'yandex-file-links-container';
            container.setAttribute('data-field', fieldName);
            
            // Вставляем контейнер после поля ввода
            fileInput.parentNode.insertBefore(container, fileInput.nextSibling);
        }
        
        return container;
    }
    
    /**
     * Интеграция с системой AJAX обновления сделки
     */
    function integrateWithAjaxSystem() {
        // Слушаем событие успешного обновления сделки
        document.addEventListener('dealUpdated', function(event) {
            console.log('🔄 Получено событие dealUpdated, обновляем ссылки...');
            
            if (event.detail && event.detail.deal) {
                updateLinksFromDealData(event.detail.deal);
            } else {
                // Fallback: пытаемся обновить ссылки из DOM
                setTimeout(fixYandexLinksDisplay, 500);
            }
        });
        
        // Слушаем событие успешной загрузки файла
        document.addEventListener('yandexFileUploaded', function(event) {
            console.log('🔄 Получено событие yandexFileUploaded...');
            
            if (event.detail) {
                const { fieldName, url, fileName } = event.detail;
                updateYandexLinkDisplay(fieldName, url, fileName);
            }
        });
    }
    
    /**
     * Обновление ссылок из данных сделки
     */
    function updateLinksFromDealData(dealData) {
        const yandexFields = [
            'measurements_file', 'final_project_file', 'work_act', 'chat_screenshot',
            'archicad_file', 'execution_order_file', 'final_floorplan', 'final_collage',
            'contract_attachment', 'plan_final', 'screenshot_work_1', 'screenshot_work_2',
            'screenshot_work_3', 'screenshot_final'
        ];
        
        yandexFields.forEach(fieldName => {
            const urlField = `yandex_url_${fieldName}`;
            const nameField = `original_name_${fieldName}`;
            
            if (dealData[urlField] && dealData[nameField]) {
                updateYandexLinkDisplay(fieldName, dealData[urlField], dealData[nameField]);
            }
        });
    }
    
    /**
     * Мониторинг изменений DOM для автоматического обновления
     */
    function setupDOMObserver() {
        // Наблюдаем за изменениями в скрытых полях с данными Яндекс.Диска
        const observer = new MutationObserver(function(mutations) {
            let shouldUpdate = false;
            
            mutations.forEach(function(mutation) {
                if (mutation.type === 'attributes' && mutation.attributeName === 'value') {
                    const target = mutation.target;
                    if (target.name && (target.name.startsWith('yandex_url_') || target.name.startsWith('original_name_'))) {
                        shouldUpdate = true;
                    }
                }
            });
            
            if (shouldUpdate) {
                console.log('🔄 Обнаружены изменения в полях Яндекс.Диска, обновляем ссылки...');
                setTimeout(fixYandexLinksDisplay, 100);
            }
        });
        
        // Наблюдаем за скрытыми полями
        document.querySelectorAll('input[type="hidden"]').forEach(input => {
            if (input.name && (input.name.startsWith('yandex_url_') || input.name.startsWith('original_name_'))) {
                observer.observe(input, { attributes: true, attributeFilter: ['value'] });
            }
        });
    }
    
    /**
     * Исправление после загрузки страницы
     */
    function initializeOnPageLoad() {
        // Проверяем, загружена ли страница
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', function() {
                setTimeout(fixYandexLinksDisplay, 1000);
                integrateWithAjaxSystem();
                setupDOMObserver();
            });
        } else {
            setTimeout(fixYandexLinksDisplay, 1000);
            integrateWithAjaxSystem();
            setupDOMObserver();
        }
    }
    
    /**
     * Глобальная функция для принудительного обновления ссылок
     */
    window.forceUpdateYandexLinks = function() {
        console.log('🔧 Принудительное обновление ссылок Яндекс.Диска...');
        fixYandexLinksDisplay();
    };
    
    /**
     * Интеграция с формой сохранения
     */
    function integrateSaveForm() {
        // Ищем форму редактирования сделки
        const dealForm = document.querySelector('form[action*="deal"]');
        
        if (dealForm) {
            dealForm.addEventListener('submit', function() {
                console.log('🔄 Форма отправлена, будем обновлять ссылки после ответа...');
            });
            
            // Перехватываем AJAX запросы успешного сохранения
            const originalFetch = window.fetch;
            window.fetch = function(...args) {
                return originalFetch.apply(this, args).then(response => {
                    if (response.ok && args[0] && args[0].includes('/deal/')) {
                        console.log('🔄 Успешный AJAX запрос сделки, обновляем ссылки...');
                        setTimeout(fixYandexLinksDisplay, 1000);
                    }
                    return response;
                });
            };
        }
    }
    
    // Запуск инициализации
    initializeOnPageLoad();
    integrateSaveForm();
    
    console.log('✅ Система исправления ссылок Яндекс.Диска инициализирована');
    
})();
