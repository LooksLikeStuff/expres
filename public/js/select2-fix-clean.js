/**
 * Исправленный скрипт для инициализации Select2 без проблемных символов
 */
(function() {
    'use strict';
    
    console.log('🔧 Select2 Fix Clean загружен');
    
    // Ждем полной загрузки DOM
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initSelect2);
    } else {
        initSelect2();
    }
    
    function initSelect2() {
        console.log('🔄 Инициализация Select2...');
        
        // Проверяем jQuery с несколькими попытками
        let attempts = 0;
        const maxAttempts = 10;
        
        function checkJQuery() {
            attempts++;
            
            if (typeof window.jQuery !== 'undefined' && typeof window.jQuery.fn.select2 !== 'undefined') {
                console.log('✅ jQuery и Select2 найдены');
                setupSelect2();
            } else if (attempts < maxAttempts) {
                console.log('⏳ Ожидание jQuery... попытка ' + attempts);
                setTimeout(checkJQuery, 200);
            } else {
                console.error('❌ jQuery или Select2 не найдены после ' + maxAttempts + ' попыток');
            }
        }
        
        checkJQuery();
    }
    
    function setupSelect2() {
        try {
            const $ = window.jQuery;
            
            // Настройка языка по умолчанию
            if (typeof $.fn.select2.defaults !== 'undefined') {
                $.fn.select2.defaults.set('language', {
                    errorLoading: function() {
                        return 'Невозможно загрузить результаты';
                    },
                    inputTooLong: function(args) {
                        var overChars = args.input.length - args.maximum;
                        return 'Пожалуйста, удалите ' + overChars + ' символ(ов)';
                    },
                    inputTooShort: function(args) {
                        var remainingChars = args.minimum - args.input.length;
                        return 'Пожалуйста, введите ' + remainingChars + ' или более символов';
                    },
                    loadingMore: function() {
                        return 'Загрузка данных...';
                    },
                    maximumSelected: function(args) {
                        return 'Вы можете выбрать не более ' + args.maximum + ' элементов';
                    },
                    noResults: function() {
                        return 'Совпадений не найдено';
                    },
                    searching: function() {
                        return 'Поиск...';
                    }
                });
                console.log('✅ Язык Select2 настроен');
            }
            
            // Инициализация всех select элементов
            $('select').each(function() {
                if (!$(this).hasClass('select2-hidden-accessible')) {
                    $(this).select2({
                        width: '100%',
                        language: 'ru'
                    });
                }
            });
            
            console.log('✅ Select2 инициализация завершена');
            
        } catch (error) {
            console.error('❌ Ошибка настройки Select2:', error);
        }
    }
})();
