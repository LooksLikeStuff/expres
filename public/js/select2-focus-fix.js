/**
 * Утилита для исправления проблем с фокусом в Select2
 * Решает проблемы с aria-hidden и автофокусом
 */

(function($) {
    'use strict';
    
    // Функция для исправления aria-hidden проблем
    function fixAriaHiddenIssues() {
        // Находим все Select2 элементы
        $('.select2-hidden-accessible').each(function() {
            var $hiddenSelect = $(this);
            
            // Убираем возможный фокус с скрытых элементов
            if (document.activeElement === this) {
                this.blur();
                console.log('Убран фокус со скрытого select элемента');
            }
            
            // Устанавливаем правильные aria атрибуты
            $hiddenSelect.attr({
                'tabindex': '-1',
                'aria-hidden': 'true'
            });
        });
    }
    
    // Улучшенная функция для установки фокуса на поле поиска
    function setFocusOnSearchField() {
        var $searchField = $('.select2-container--open .select2-search__field');
        
        if ($searchField.length > 0) {
            // Проверяем видимость элемента
            if ($searchField.is(':visible') && $searchField.attr('aria-hidden') !== 'true') {
                try {
                    // Убираем фокус с любых скрытых элементов
                    if (document.activeElement && 
                        document.activeElement.getAttribute('aria-hidden') === 'true') {
                        document.activeElement.blur();
                    }
                    
                    // Устанавливаем фокус на поле поиска
                    $searchField[0].focus();
                    
                    // Проверяем успешность
                    if (document.activeElement === $searchField[0]) {
                        console.log('✅ Фокус успешно установлен на поле поиска');
                        return true;
                    }
                } catch (e) {
                    console.warn('Ошибка при установке фокуса:', e);
                }
            }
        }
        return false;
    }
    
    // Глобальный обработчик для исправления проблем с aria-hidden
    function addGlobalFocusFix() {
        $(document).on('select2:opening', function(e) {
            console.log('Select2 открывается, исправляем aria-hidden проблемы');
            fixAriaHiddenIssues();
        });
        
        $(document).on('select2:open', function(e) {
            console.log('Select2 открыт, устанавливаем правильный фокус');
            
            // Множественные попытки установки фокуса
            var attempts = 0;
            var maxAttempts = 5;
            
            function trySetFocus() {
                if (attempts >= maxAttempts) {
                    console.warn('Не удалось установить фокус после ' + maxAttempts + ' попыток');
                    return;
                }
                
                attempts++;
                
                if (setFocusOnSearchField()) {
                    console.log('Фокус установлен с попытки #' + attempts);
                } else {
                    setTimeout(trySetFocus, 10 * attempts);
                }
            }
            
            setTimeout(trySetFocus, 5);
        });
    }
    
    // Функция для принудительного исправления всех проблем
    window.fixSelect2Focus = function() {
        console.log('Принудительное исправление проблем с фокусом Select2...');
        
        fixAriaHiddenIssues();
        
        // Если есть открытый Select2, устанавливаем фокус
        setTimeout(function() {
            setFocusOnSearchField();
        }, 10);
        
        console.log('Исправление завершено');
    };
    
    // Автоматическая инициализация
    $(document).ready(function() {
        console.log('Инициализация исправлений для фокуса Select2');
        addGlobalFocusFix();
        
        // Исправляем существующие проблемы
        setTimeout(function() {
            fixAriaHiddenIssues();
        }, 100);
    });
    
})(jQuery);
