/**
 * Тестовый скрипт для проверки функциональности вкладок документов
 */

console.log('=== ТЕСТ СИСТЕМЫ ВКЛАДОК ===');

// Тестируем функцию получения ID сделки
setTimeout(function() {
    console.log('Тестирование получения ID сделки...');
    
    // Тестируем через поле в модальном окне
    const dealIdField = document.getElementById('dealIdField');
    if (dealIdField) {
        console.log('✓ Поле dealIdField найдено, значение:', dealIdField.value);
    } else {
        console.log('✗ Поле dealIdField не найдено');
    }
    
    // Тестируем data-атрибуты
    const dealContainer = document.querySelector('[data-deal-id]');
    if (dealContainer) {
        console.log('✓ Контейнер с data-deal-id найден, значение:', dealContainer.getAttribute('data-deal-id'));
    } else {
        console.log('✗ Контейнер с data-deal-id не найден');
    }
    
    // Тестируем URL
    const urlMatch = window.location.pathname.match(/\/deal\/(\d+)/);
    if (urlMatch) {
        console.log('✓ ID сделки найден в URL:', urlMatch[1]);
    } else {
        console.log('✗ ID сделки не найден в URL');
    }
    
    // Тестируем доступность функций
    if (typeof window.reinitializeTabsForModal === 'function') {
        console.log('✓ Функция reinitializeTabsForModal доступна');
    } else {
        console.log('✗ Функция reinitializeTabsForModal недоступна');
    }
    
    console.log('=== КОНЕЦ ТЕСТА ===');
}, 1000);
