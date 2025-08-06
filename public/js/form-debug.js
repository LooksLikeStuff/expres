/**
 * Отладочные функции для диагностики проблем с формой редактирования сделки
 * Включает диагностику системы загрузки файлов на Яндекс.Диск
 */

console.log('🔧 Загрузка системы отладки формы...');

// Функция диагностики системы Яндекс.Диска
window.debugYandexSystem = function() {
    console.log('🔍 === ДИАГНОСТИКА СИСТЕМЫ ЯНДЕКС.ДИСКА ===');
    
    // Проверяем загрузку основных компонентов
    console.log('📋 Проверка загруженных компонентов:');
    
    if (window.yandexDiskUploader) {
        console.log('   ✅ YandexDiskUploaderV3: загружена');
        console.log(`   Инициализирована: ${window.yandexDiskUploader.isInitialized}`);
        console.log(`   Поддерживаемые поля: ${window.yandexDiskUploader.settings.supportedFields.length}`);
    } else {
        console.log('   ❌ YandexDiskUploaderV3: НЕ ЗАГРУЖЕНА!');
    }
    
    if (window.YandexDiskUniversal) {
        console.log('   ✅ YandexDiskUniversal: доступна');
    } else {
        console.log('   ❌ YandexDiskUniversal: недоступна');
    }
    
    if (window.forceUpdateYandexLinks) {
        console.log('   ✅ forceUpdateYandexLinks: доступна');
    } else {
        console.log('   ❌ forceUpdateYandexLinks: недоступна');
    }
    
    if (window.handleMultipleYandexFileUpload) {
        console.log('   ✅ handleMultipleYandexFileUpload: доступна');
    } else {
        console.log('   ❌ handleMultipleYandexFileUpload: недоступна');
    }
    
    // Проверяем поля формы
    console.log('📋 Проверка полей файлов:');
    const yandexFields = [
        'measurements_file', 'final_project_file', 'work_act', 'chat_screenshot',
        'archicad_file', 'execution_order_file', 'final_floorplan', 'final_collage',
        'contract_attachment', 'plan_final', 'screenshot_work_1', 'screenshot_work_2',
        'screenshot_work_3', 'screenshot_final'
    ];
    
    yandexFields.forEach(fieldName => {
        const input = document.querySelector(`input[name="${fieldName}"]`);
        if (input) {
            console.log(`   ✅ ${fieldName}: найдено`);
            const container = document.querySelector(`.yandex-file-links-container[data-field="${fieldName}"]`);
            if (container) {
                console.log(`      📁 Контейнер ссылок: найден`);
                const links = container.querySelectorAll('.yandex-file-link, .file-success');
                console.log(`      🔗 Активных ссылок: ${links.length}`);
            } else {
                console.log(`      📁 Контейнер ссылок: отсутствует`);
            }
        } else {
            console.log(`   ❌ ${fieldName}: НЕ НАЙДЕНО`);
        }
    });
    
    // Проверяем API
    console.log('📋 Проверка API:');
    fetch('/api/yandex-disk/health', {
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    }).then(response => {
        if (response.ok) {
            console.log('   ✅ Yandex Disk API: доступно');
            return response.json();
        } else {
            console.log(`   ❌ Yandex Disk API: ошибка ${response.status}`);
        }
    }).then(data => {
        if (data) {
            console.log(`   📊 Статус API:`, data);
        }
    }).catch(error => {
        console.log(`   ❌ Yandex Disk API: недоступно (${error.message})`);
    });
    
    // Проверяем CSRF токен
    const csrfToken = document.querySelector('meta[name="csrf-token"]');
    if (csrfToken && csrfToken.getAttribute('content')) {
        console.log('   ✅ CSRF токен: найден');
    } else {
        console.log('   ❌ CSRF токен: отсутствует');
    }
};

// Функция тестирования загрузки файла
window.testYandexUpload = function() {
    console.log('🚀 === ТЕСТ ЗАГРУЗКИ ЯНДЕКС.ДИСКА ===');
    
    // Создаем тестовый файл
    const testContent = 'Тестовый файл для проверки загрузки на Яндекс.Диск';
    const testFile = new File([testContent], 'test-file.txt', { type: 'text/plain' });
    
    const dealId = window.yandexDiskUploader?.getDealId();
    if (!dealId) {
        console.log('❌ Не удалось определить ID сделки');
        return;
    }
    
    console.log(`📁 Тестируем загрузку файла для сделки ${dealId}`);
    
    if (window.yandexDiskUploader) {
        window.yandexDiskUploader.uploadFile(testFile, dealId, 'screenshot_work_1')
            .then(result => {
                console.log('✅ Тест загрузки прошел успешно:', result);
            })
            .catch(error => {
                console.log('❌ Тест загрузки неудачен:', error);
            });
    } else {
        console.log('❌ YandexDiskUploader недоступен');
    }
};

// Функция принудительного обновления ссылок
window.forceFixLinks = function() {
    console.log('🔧 Принудительное исправление ссылок...');
    
    if (window.forceUpdateYandexLinks) {
        window.forceUpdateYandexLinks();
        console.log('✅ Запущено обновление через forceUpdateYandexLinks');
    }
    
    // Также попробуем через событие
    const event = new CustomEvent('dealUpdated', {
        detail: { deal: window.dealData || {} }
    });
    document.dispatchEvent(event);
    console.log('✅ Отправлено событие dealUpdated');
};

// Автоматическая диагностика при загрузке
document.addEventListener('DOMContentLoaded', function() {
    setTimeout(() => {
        console.log('🔍 Автоматическая диагностика системы...');
        if (window.debugYandexSystem) {
            window.debugYandexSystem();
        }
    }, 2000);
});

console.log('✅ Система отладки формы загружена. Доступные команды:');
console.log('   - debugYandexSystem() - полная диагностика');
console.log('   - testYandexUpload() - тест загрузки файла');
console.log('   - forceFixLinks() - принудительное обновление ссылок');