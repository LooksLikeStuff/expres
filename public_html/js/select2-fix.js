/**
 * Исправление проблем с загрузкой Select2
 * Версия: 2.0
 */

(function() {
    'use strict';
    
    console.log('🔧 Инициализация Select2 Fix v2.0...');
    
    // Проверка и загрузка Select2
    function ensureSelect2() {
        return new Promise((resolve, reject) => {
            // Проверяем, загружен ли Select2
            if (typeof $.fn.select2 !== 'undefined') {
                console.log('✅ Select2 уже загружен');
                resolve();
                return;
            }
            
            console.log('🔄 Загружаем Select2...');
            
            // Проверяем jQuery
            if (typeof $ === 'undefined' || typeof jQuery === 'undefined') {
                console.error('❌ jQuery не загружен!');
                reject(new Error('jQuery not loaded'));
                return;
            }
            
            // Загружаем CSS если нужно
            if (!document.querySelector('link[href*="select2"]')) {
                const link = document.createElement('link');
                link.rel = 'stylesheet';
                link.href = '/css/p/select2.min.css';
                document.head.appendChild(link);
                console.log('📦 Загружен CSS для Select2');
            }
            
            // Загружаем JS
            const script = document.createElement('script');
            script.src = '/js/p/select2.min.js';
            script.onload = function() {
                console.log('✅ Select2 успешно загружен');
                
                // Настройка языка по умолчанию
                if (typeof $.fn.select2 !== 'undefined') {
                    $.fn.select2.defaults.set('language', {
                        errorLoading: function () {
                            return 'Невозможно загрузить результаты';
                        },
                        inputTooLong: function (args) {
                            var overChars = args.input.length - args.maximum;
                            return 'Пожалуйста, введите на ' + overChars + ' символ(ов) меньше';
                        },
                        inputTooShort: function (args) {
                            var remainingChars = args.minimum - args.input.length;
                            return 'Пожалуйста, введите ' + remainingChars + ' или более символов';
                        },
                        loadingMore: function () {
                            return 'Загружаются дополнительные результаты…';
                        },
                        maximumSelected: function (args) {
                            return 'Вы можете выбрать не более ' + args.maximum + ' элемент(ов)';
                        },
                        noResults: function () {
                            return 'Совпадений не найдено';
                        },
                        searching: function () {
                            return 'Поиск…';
                        },
                        removeAllItems: function () {
                            return 'Удалить все элементы';
                        }
                    });
                }
                
                resolve();
            };
            script.onerror = function() {
                console.error('❌ Ошибка загрузки Select2');
                reject(new Error('Failed to load Select2'));
            };
            document.head.appendChild(script);
        });
    }
    
    // Инициализация Select2 элементов
    function initializeSelect2Elements() {
        console.log('🔧 Инициализация Select2 элементов...');
        
        // Базовая инициализация Select2
        $('.select2-field, .select2-search, .select2-specialist').each(function() {
            const $element = $(this);
            
            if ($element.hasClass('select2-hidden-accessible')) {
                console.log('⚠️ Элемент уже инициализирован:', $element.attr('id'));
                return;
            }
            
            try {
                $element.select2({
                    placeholder: $element.attr('placeholder') || 'Выберите значение',
                    allowClear: true,
                    width: '100%',
                    dropdownAutoWidth: true,
                    language: 'ru'
                });
                console.log('✅ Select2 инициализирован для:', $element.attr('id') || $element.attr('name'));
            } catch (error) {
                console.error('❌ Ошибка инициализации Select2 для элемента:', $element.attr('id'), error);
            }
        });
    }
    
    // Экспорт функций в глобальную область
    window.Select2Fix = {
        ensure: ensureSelect2,
        initialize: initializeSelect2Elements
    };
    
    // Автоматическая инициализация при загрузке DOM
    $(document).ready(function() {
        ensureSelect2().then(() => {
            // Небольшая задержка для обеспечения готовности DOM
            setTimeout(initializeSelect2Elements, 100);
        }).catch(error => {
            console.error('❌ Не удалось инициализировать Select2:', error);
        });
    });
    
})();
