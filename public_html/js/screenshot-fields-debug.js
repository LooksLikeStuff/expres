/**
 * Отладочный скрипт для тестирования загрузки файлов во вкладке "Работа над проектом"
 * Создан: 5 августа 2025 г.
 */

(function() {
    'use strict';
    
    console.log('🔧 Загружен отладочный скрипт для проблемных полей');
    
    // Список проблемных полей
    const problemFields = [
        'screenshot_work_1',
        'screenshot_work_2', 
        'screenshot_work_3',
        'screenshot_final'
    ];
    
    // Функция отладки
    function debugFileUploadSystem() {
        console.log('🚀 === ОТЛАДКА СИСТЕМЫ ЗАГРУЗКИ ФАЙЛОВ ===');
        
        // 1. Проверяем наличие полей
        console.log('📋 1. Проверка наличия проблемных полей:');
        problemFields.forEach((fieldName, index) => {
            const input = document.querySelector(`input[name="${fieldName}"]`);
            if (input) {
                console.log(`   ✅ ${index + 1}. ${fieldName}: найден (ID: ${input.id}, Classes: ${input.className})`);
                
                // Проверяем события
                const events = ['change', 'input'];
                events.forEach(eventType => {
                    const hasEvent = input.onclick || input.onchange || input.oninput;
                    console.log(`      События ${eventType}: ${hasEvent ? 'найдены' : 'не найдены'}`);
                });
                
                // Проверяем атрибуты
                console.log(`      data-upload-type: ${input.getAttribute('data-upload-type')}`);
                console.log(`      data-yandex-initialized: ${input.getAttribute('data-yandex-initialized')}`);
                
                // Проверяем контейнеры для ссылок
                const linkContainer = document.querySelector(`.yandex-file-links-container[data-field="${fieldName}"]`);
                console.log(`      Контейнер ссылок: ${linkContainer ? 'найден' : 'не найден'}`);
                
                // Проверяем существующие ссылки
                const existingLinks = input.closest('.col-md-6, .mb-3')?.querySelectorAll('.existing-file-link');
                console.log(`      Существующие ссылки: ${existingLinks ? existingLinks.length : 0}`);
                
            } else {
                console.log(`   ❌ ${index + 1}. ${fieldName}: НЕ НАЙДЕН!`);
            }
        });
        
        // 2. Проверяем универсальную систему
        console.log('📋 2. Проверка универсальной системы:');
        if (window.YandexDiskUniversal) {
            console.log('   ✅ YandexDiskUniversal: загружена');
            console.log(`   Версия: ${window.YandexDiskUniversal.version}`);
            console.log(`   Инициализирована: ${window.YandexDiskUniversal.initialized}`);
            console.log(`   Поддерживаемые поля: ${window.YandexDiskUniversal.supportedFields.join(', ')}`);
        } else {
            console.log('   ❌ YandexDiskUniversal: НЕ ЗАГРУЖЕНА!');
        }
        
        // 3. Проверяем AJAX систему
        console.log('📋 3. Проверка AJAX системы:');
        if (window.handleMultipleYandexFileUpload) {
            console.log('   ✅ handleMultipleYandexFileUpload: найдена');
        } else {
            console.log('   ❌ handleMultipleYandexFileUpload: НЕ НАЙДЕНА!');
        }
        
        // 4. Проверяем drag-drop систему
        console.log('📋 4. Проверка drag-drop системы:');
        const dragDropFields = document.querySelectorAll('input[type="file"][data-drag-drop-initialized]');
        console.log(`   Инициализированных полей: ${dragDropFields.length}`);
        
        // 5. Проверяем контейнеры для ссылок
        console.log('📋 5. Проверка контейнеров для ссылок:');
        const linkContainers = document.querySelectorAll('.yandex-file-links-container');
        console.log(`   Найдено контейнеров: ${linkContainers.length}`);
        linkContainers.forEach((container, index) => {
            const field = container.getAttribute('data-field');
            console.log(`      ${index + 1}. Поле: ${field}, Содержимое: ${container.innerHTML.length > 0 ? 'есть' : 'пусто'}`);
        });
        
        // 6. Симуляция загрузки файла
        console.log('📋 6. Тестирование загрузки:');
        console.log('   Для тестирования используйте: testFileUpload("screenshot_work_1")');
        console.log('   Для проверки ссылок используйте: checkFileLinks()');
        
        console.log('🚀 === КОНЕЦ ОТЛАДКИ ===');
    }
    
    // Функция тестирования загрузки файла
    function testFileUpload(fieldName) {
        console.log(`🧪 Тестирование загрузки файла для поля: ${fieldName}`);
        
        const input = document.querySelector(`input[name="${fieldName}"]`);
        if (!input) {
            console.log(`❌ Поле ${fieldName} не найдено`);
            return;
        }
        
        // Создаем тестовый файл
        const testFile = new File(['test content'], 'test-screenshot.jpg', { type: 'image/jpeg' });
        
        // Создаем DataTransfer для установки файла
        const dataTransfer = new DataTransfer();
        dataTransfer.items.add(testFile);
        input.files = dataTransfer.files;
        
        console.log(`📁 Тестовый файл установлен: ${testFile.name}`);
        
        // Запускаем событие change
        const changeEvent = new Event('change', { bubbles: true });
        input.dispatchEvent(changeEvent);
        
        console.log(`🚀 Событие change запущено для ${fieldName}`);
    }
    
    // Функция проверки API
    async function testYandexAPI() {
        console.log('🌐 Тестирование Яндекс.Диск API...');
        
        try {
            const response = await fetch('/api/yandex-disk/health', {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            });
            
            const data = await response.json();
            console.log('✅ API ответ:', data);
        } catch (error) {
            console.log('❌ Ошибка API:', error);
        }
    }
    
    // Функция проверки отображения ссылок
    function checkFileLinks() {
        console.log('🔗 === ПРОВЕРКА ОТОБРАЖЕНИЯ ССЫЛОК ===');
        
        problemFields.forEach((fieldName, index) => {
            console.log(`📋 ${index + 1}. Проверка поля: ${fieldName}`);
            
            // Проверяем существующие PHP ссылки
            const input = document.querySelector(`input[name="${fieldName}"]`);
            if (input) {
                const container = input.closest('.col-md-6, .mb-3');
                if (container) {
                    const existingLinks = container.querySelectorAll('.existing-file-link');
                    const dynamicContainer = container.querySelector('.yandex-file-links-container');
                    const dynamicLinks = dynamicContainer ? dynamicContainer.querySelectorAll('.yandex-file-link, .file-success') : [];
                    
                    console.log(`   Существующие PHP ссылки: ${existingLinks.length}`);
                    console.log(`   Динамические JS ссылки: ${dynamicLinks.length}`);
                    console.log(`   Контейнер для динамических ссылок: ${dynamicContainer ? 'найден' : 'не найден'}`);
                    
                    if (existingLinks.length > 0) {
                        existingLinks.forEach((link, i) => {
                            const href = link.querySelector('a')?.href;
                            const visible = link.style.display !== 'none';
                            console.log(`      PHP ссылка ${i + 1}: ${href} (${visible ? 'видима' : 'скрыта'})`);
                        });
                    }
                    
                    if (dynamicLinks.length > 0) {
                        dynamicLinks.forEach((link, i) => {
                            const href = link.href || link.querySelector('a')?.href;
                            console.log(`      JS ссылка ${i + 1}: ${href}`);
                        });
                    }
                }
            } else {
                console.log(`   ❌ Поле не найдено`);
            }
            console.log('');
        });
        
        console.log('🔗 === КОНЕЦ ПРОВЕРКИ ССЫЛОК ===');
    }
    
    // Функция принудительного создания тестовой ссылки
    function createTestLink(fieldName) {
        console.log(`🧪 Создание тестовой ссылки для поля: ${fieldName}`);
        
        const input = document.querySelector(`input[name="${fieldName}"]`);
        if (!input) {
            console.log(`❌ Поле ${fieldName} не найдено`);
            return;
        }
        
        // Создаем тестовую ссылку
        const testUrl = 'https://yadi.sk/d/test-link-' + Date.now();
        const testName = 'Тестовый файл ' + fieldName + '.jpg';
        
        if (window.YandexDiskUniversal) {
            window.YandexDiskUniversal.updateFileLink(fieldName, testUrl, testName);
            console.log(`✅ Тестовая ссылка создана через YandexDiskUniversal`);
        } else {
            console.log(`❌ YandexDiskUniversal недоступна`);
        }
    }
    
    // Добавляем функции в глобальную область для консоли
    window.debugFileUpload = debugFileUploadSystem;
    window.testFileUpload = testFileUpload;
    window.testYandexAPI = testYandexAPI;
    window.checkFileLinks = checkFileLinks;
    window.createTestLink = createTestLink;
    
    // Автоматический запуск отладки через 2 секунды после загрузки
    setTimeout(() => {
        console.log('🔧 Автоматический запуск отладки...');
        debugFileUploadSystem();
    }, 2000);
    
    console.log('🔧 Отладочные функции загружены. Доступны:');
    console.log('   debugFileUpload() - полная отладка системы');
    console.log('   testFileUpload("field_name") - тест загрузки');
    console.log('   testYandexAPI() - тест API');
    console.log('   checkFileLinks() - проверка отображения ссылок');
    console.log('   createTestLink("field_name") - создать тестовую ссылку');
    
})();
