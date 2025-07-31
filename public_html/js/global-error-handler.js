/**
 * Глобальный обработчик ошибок JavaScript для предотвращения критических сбоев
 * Подключать этот файл в начале загрузки страницы
 */

// Глобальный обработчик ошибок JavaScript
window.addEventListener('error', function(e) {
    // Перехватываем ошибки с null элементами
    if (e.message && e.message.includes("Cannot read properties of null")) {
        console.warn('🔧 Перехвачена ошибка с null элементом:', e.message, 'в файле:', e.filename, 'строка:', e.lineno);
        
        // Предотвращаем показ ошибки в консоли
        e.preventDefault();
        
        // Попытка восстановления для наиболее частых случаев
        setTimeout(() => {
            try {
                // Если ошибка связана с Select2, переинициализируем его
                if (e.message.includes('select2') || e.filename.includes('select2')) {
                    if (window.initializeAllSelect2Elements) {
                        console.log('🔄 Попытка переинициализации Select2...');
                        window.initializeAllSelect2Elements();
                    }
                }
                
                // Если ошибка связана с модальными окнами
                if (e.message.includes('modal') || e.filename.includes('modal')) {
                    console.log('🔄 Попытка исправления модальных окон...');
                    $('.modal').each(function() {
                        if ($(this).hasClass('show') && !$(this).is(':visible')) {
                            $(this).modal('hide');
                        }
                    });
                }
            } catch (recoveryError) {
                console.error('❌ Ошибка при попытке восстановления:', recoveryError);
            }
        }, 100);
        
        return false;
    }
    
    // Перехватываем ошибки с undefined свойствами
    if (e.message && e.message.includes("Cannot read properties of undefined")) {
        console.warn('🔧 Перехвачена ошибка с undefined свойством:', e.message);
        e.preventDefault();
        return false;
    }
    
    // Логируем другие ошибки без остановки выполнения
    console.error('⚠️ JavaScript Error:', e.message, 'в файле:', e.filename, 'строка:', e.lineno);
});

// Обработчик необработанных промисов
window.addEventListener('unhandledrejection', function(e) {
    console.error('⚠️ Unhandled Promise Rejection:', e.reason);
    
    // Если ошибка связана с AJAX запросами, не прерываем выполнение
    if (e.reason && (e.reason.includes('404') || e.reason.includes('500'))) {
        console.warn('🔧 Перехвачена ошибка AJAX запроса, продолжаем выполнение');
        e.preventDefault();
        return false;
    }
});

// Безопасная функция для работы с DOM элементами
window.safeDOMOperation = function(selector, operation, fallback = null) {
    try {
        const element = typeof selector === 'string' ? document.querySelector(selector) : selector;
        if (element) {
            if (typeof operation === 'function') {
                return operation(element);
            } else {
                return element;
            }
        } else {
            console.warn(`🔍 Элемент "${selector}" не найден`);
            return fallback;
        }
    } catch (error) {
        console.error(`❌ Ошибка при работе с элементом "${selector}":`, error);
        return fallback;
    }
};

// Безопасная функция для установки стилей
window.safeSetElementStyle = function(selector, styles) {
    return window.safeDOMOperation(selector, function(element) {
        if (element.style) {
            if (typeof styles === 'object') {
                Object.assign(element.style, styles);
            } else if (typeof styles === 'string') {
                element.style.cssText = styles;
            }
            return true;
        }
        return false;
    }, false);
};

// Безопасная функция для добавления классов
window.safeAddClass = function(selector, className) {
    return window.safeDOMOperation(selector, function(element) {
        if (element.classList) {
            element.classList.add(className);
            return true;
        }
        return false;
    }, false);
};

// Безопасная функция для удаления классов
window.safeRemoveClass = function(selector, className) {
    return window.safeDOMOperation(selector, function(element) {
        if (element.classList) {
            element.classList.remove(className);
            return true;
        }
        return false;
    }, false);
};

// Логируем успешную загрузку обработчика
console.log('✅ Глобальный обработчик ошибок JavaScript загружен');
