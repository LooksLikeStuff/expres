/**
 * Диагностический скрипт для проверки работы Select2 полей
 * Координатор, Партнер и Город/часовой пояс
 */

(function() {
    'use strict';
    
    // Функция для вывода диагностической информации
    function log(message, type = 'info') {
        var prefix = '[Select2 Diagnostic] ';
        var styles = {
            info: 'color: #007bff;',
            success: 'color: #28a745;',
            warning: 'color: #ffc107;',
            error: 'color: #dc3545;'
        };
        
        console.log('%c' + prefix + message, styles[type] || styles.info);
    }
    
    // Функция проверки состояния полей
    function checkSelect2Fields() {
        log('=== Начало диагностики Select2 полей ===');
        
        // Проверяем наличие jQuery и Select2
        if (typeof $ === 'undefined') {
            log('jQuery не загружен!', 'error');
            return;
        }
        
        if (typeof $.fn.select2 === 'undefined') {
            log('Select2 не загружен!', 'error');
            return;
        }
        
        log('jQuery и Select2 загружены успешно', 'success');
        
        // Проверяем каждое поле
        var fields = [
            { 
                selector: '.select2-coordinator-search', 
                name: 'Координатор',
                endpoint: '/search-users?status=coordinator'
            },
            { 
                selector: '.select2-partner-search', 
                name: 'Партнер',
                endpoint: '/search-users?status=partner'
            },
            { 
                selector: '.select2-cities-search', 
                name: 'Город/часовой пояс',
                endpoint: '/cities.json'
            }
        ];
        
        fields.forEach(function(field) {
            var $elements = $(field.selector);
            
            if ($elements.length === 0) {
                log('Поле "' + field.name + '" (' + field.selector + ') не найдено', 'warning');
                return;
            }
            
            log('Найдено полей "' + field.name + '": ' + $elements.length, 'info');
            
            $elements.each(function(index) {
                var $element = $(this);
                var isInitialized = $element.hasClass('select2-hidden-accessible');
                
                if (isInitialized) {
                    log('Поле "' + field.name + '" #' + (index + 1) + ' инициализировано ✓', 'success');
                    
                    // Проверяем настройки Select2
                    try {
                        var data = $element.select2('data');
                        var options = $element.data('select2').options.options;
                        var currentValue = $element.data('current-value') || $element.val();
                        var selectedText = data.length > 0 ? data[0].text : 'не выбрано';
                        
                        log('  - Placeholder: ' + (options.placeholder || 'не задан'), 'info');
                        log('  - DropdownParent: ' + (options.dropdownParent ? 'задан' : 'не задан'), 'info');
                        log('  - AJAX URL: ' + (options.ajax ? options.ajax.url : 'нет AJAX'), 'info');
                        log('  - Атрибут data-current-value: ' + (currentValue || 'не задан'), 'info');
                        log('  - Выбранное значение: ' + selectedText, 'info');
                        
                        // Для поля городов дополнительно проверяем соответствие
                        if (field.name === 'Город/часовой пояс') {
                            if (currentValue && selectedText !== 'не выбрано' && selectedText !== currentValue) {
                                log('  - ВНИМАНИЕ: Выбранное значение не соответствует data-current-value!', 'warning');
                            }
                        }
                    } catch (e) {
                        log('  - Ошибка получения данных Select2: ' + e.message, 'error');
                    }
                } else {
                    log('Поле "' + field.name + '" #' + (index + 1) + ' НЕ инициализировано ✗', 'error');
                }
            });
        });
        
        // Проверяем доступность эндпоинтов
        log('=== Проверка доступности эндпоинтов ===');
        
        // Проверяем эндпоинт для поиска пользователей
        $.ajax({
            url: '/search-users',
            method: 'GET',
            data: { q: 'test', status: 'coordinator' },
            timeout: 5000
        }).done(function(data) {
            log('Эндпоинт /search-users доступен ✓', 'success');
            log('  - Возвращено записей: ' + (Array.isArray(data) ? data.length : 'некорректный формат'), 'info');
        }).fail(function(xhr, status, error) {
            log('Эндпоинт /search-users недоступен ✗ (' + xhr.status + ')', 'error');
        });
        
        // Проверяем файл с городами
        $.ajax({
            url: '/cities.json',
            method: 'GET',
            timeout: 5000
        }).done(function(data) {
            log('Файл /cities.json доступен ✓', 'success');
            log('  - Загружено городов: ' + (Array.isArray(data) ? data.length : 'некорректный формат'), 'info');
        }).fail(function(xhr, status, error) {
            log('Файл /cities.json недоступен ✗ (' + xhr.status + ')', 'error');
        });
        
        log('=== Диагностика завершена ===');
    }
    
    // Функция для принудительной переинициализации
    function reinitializeSelect2() {
        log('=== Принудительная переинициализация Select2 ===');
        
        // Уничтожаем существующие инстансы
        $('.select2-coordinator-search, .select2-partner-search, .select2-cities-search').each(function() {
            if ($(this).hasClass('select2-hidden-accessible')) {
                $(this).select2('destroy');
                log('Уничтожен Select2 для ' + this.className, 'info');
            }
        });
        
        // Переинициализируем
        if (typeof initModalSelects === 'function') {
            initModalSelects();
            log('initModalSelects() вызвана', 'success');
        } else {
            log('Функция initModalSelects не найдена!', 'error');
        }
        
        // Проверяем результат
        setTimeout(function() {
            checkSelect2Fields();
        }, 1000);
    }
    
    // Экспортируем функции в глобальную область
    window.select2Diagnostic = {
        check: checkSelect2Fields,
        reinit: reinitializeSelect2
    };
    
    // Автоматически запускаем диагностику при загрузке
    $(document).ready(function() {
        setTimeout(checkSelect2Fields, 1000);
    });
    
    log('Диагностический модуль загружен. Используйте select2Diagnostic.check() или select2Diagnostic.reinit()', 'success');
    
})();
