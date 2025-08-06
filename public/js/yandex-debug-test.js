/**
 * Тестовый скрипт для диагностики проблем с загрузкой Яндекс.Диска
 */

console.log('🔧 Загружен тестовый скрипт диагностики Яндекс.Диска');

// Глобальная функция диагностики
window.diagnosеYandexUpload = function() {
    console.log('🔍 === ДИАГНОСТИКА СИСТЕМЫ ЗАГРУЗКИ ЯНДЕКС.ДИСКА ===');
    
    // 1. Проверяем загрузку скриптов
    console.log('📋 1. Проверка загруженных скриптов:');
    console.log('   YandexDiskUploaderV3:', typeof YandexDiskUploaderV3);
    console.log('   window.yandexDiskUploader:', typeof window.yandexDiskUploader);
    console.log('   window.YandexDiskUniversal:', typeof window.YandexDiskUniversal);
    
    // 2. Проверяем поля файлов
    console.log('📋 2. Проверка полей файлов:');
    const yandexFields = document.querySelectorAll('input[type="file"].yandex-upload');
    console.log(`   Найдено полей с классом yandex-upload: ${yandexFields.length}`);
    
    yandexFields.forEach((field, index) => {
        console.log(`   Поле ${index + 1}:`, {
            name: field.name,
            id: field.id,
            classes: field.className,
            uploadType: field.getAttribute('data-upload-type'),
            hasEventListener: field._hasYandexListener || false
        });
    });
    
    // 3. Проверяем API
    console.log('📋 3. Проверка API:');
    fetch('/api/yandex-disk/health', {
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
        }
    })
    .then(response => {
        console.log(`   API Health Status: ${response.status}`);
        return response.json();
    })
    .then(data => {
        console.log('   API Health Response:', data);
    })
    .catch(error => {
        console.error('   API Health Error:', error);
    });
    
    // 4. Проверяем CSRF токен
    const csrfToken = document.querySelector('meta[name="csrf-token"]');
    console.log('📋 4. CSRF токен:', csrfToken ? 'найден' : 'отсутствует');
    
    // 5. Проверяем Deal ID
    console.log('📋 5. Deal ID:');
    const dealIdField = document.querySelector('input[name="deal_id"]');
    const urlMatch = window.location.href.match(/\/deal\/(\d+)/);
    console.log('   Deal ID из поля:', dealIdField?.value || 'не найден');
    console.log('   Deal ID из URL:', urlMatch ? urlMatch[1] : 'не найден');
    
    // 6. Тестируем обработчик событий
    console.log('📋 6. Тестирование обработчика событий:');
    const testField = document.querySelector('input[name="measurements_file"]');
    if (testField) {
        console.log('   Тестовое поле найдено:', testField.name);
        
        // Имитируем событие change
        const event = new Event('change', { bubbles: true });
        console.log('   Имитируем событие change...');
        testField.dispatchEvent(event);
    } else {
        console.log('   Тестовое поле не найдено!');
    }
};

// Диагностика событий
document.addEventListener('change', function(event) {
    if (event.target.type === 'file' && event.target.classList.contains('yandex-upload')) {
        console.log('🚀 СОБЫТИЕ CHANGE обнаружено для Яндекс поля:', {
            name: event.target.name,
            files: event.target.files.length,
            fileName: event.target.files[0]?.name || 'нет файла'
        });
    }
});

// Автоматическая диагностика через 2 секунды после загрузки
document.addEventListener('DOMContentLoaded', function() {
    setTimeout(() => {
        console.log('🔧 Запуск автоматической диагностики через 2 секунды...');
        if (window.diagnosеYandexUpload) {
            window.diagnosеYandexUpload();
        }
    }, 2000);
});

console.log('✅ Тестовый скрипт готов. Используйте diagnosеYandexUpload() для запуска диагностики');
