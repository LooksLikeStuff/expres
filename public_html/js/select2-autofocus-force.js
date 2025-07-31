/**
 * Утилита для принудительной активации автофокуса Select2
 * Для использования в консоли браузера или как отдельный скрипт
 */

(function() {
    'use strict';
    
    // Принудительная активация автофокуса для всех Select2 элементов
    function forceSelect2AutoFocus() {
        console.log('Принудительная активация автофокуса Select2...');
        
        $('.select2-hidden-accessible').each(function() {
            var $element = $(this);
            
            // Удаляем все существующие обработчики автофокуса
            $element.off('select2:open.autofocus');
            $element.off('select2:open.force-autofocus');
            
            // Добавляем новый обработчик с принудительным фокусом
            $element.on('select2:open.force-autofocus', function(e) {
                console.log('Select2 открыт, применяем автофокус...', this);
                
                // Несколько попыток установки фокуса с разными интервалами
                var attempts = [5, 15, 50, 100, 200];
                
                attempts.forEach(function(delay, index) {
                    setTimeout(function() {
                        var $searchField = $('.select2-container--open .select2-search__field');
                        
                        if ($searchField.length > 0) {
                            console.log('Попытка #' + (index + 1) + ' установки фокуса через ' + delay + 'мс');
                            
                            // Проверяем, что фокус еще не установлен
                            if (document.activeElement !== $searchField[0]) {
                                $searchField.focus();
                                
                                // Дополнительные методы активации
                                $searchField.trigger('focus');
                                $searchField.trigger('click');
                                
                                // Проверяем результат
                                setTimeout(function() {
                                    if (document.activeElement === $searchField[0]) {
                                        console.log('✅ Фокус успешно установлен на попытке #' + (index + 1));
                                    } else {
                                        console.log('❌ Фокус не установлен на попытке #' + (index + 1));
                                    }
                                }, 10);
                            } else {
                                console.log('✅ Фокус уже установлен');
                            }
                        } else {
                            console.log('⚠️ Поле поиска не найдено на попытке #' + (index + 1));
                        }
                    }, delay);
                });
            });
        });
        
        console.log('Автофокус активирован для ' + $('.select2-hidden-accessible').length + ' элементов');
    }
    
    // Глобальный обработчик для всех Select2
    function addGlobalSelect2AutoFocus() {
        $(document).off('select2:open.global-autofocus');
        $(document).on('select2:open.global-autofocus', function(e) {
            console.log('Глобальный обработчик: Select2 открыт');
            
            setTimeout(function() {
                var $searchField = $('.select2-container--open .select2-search__field');
                
                if ($searchField.length) {
                    console.log('Устанавливаем фокус через глобальный обработчик');
                    $searchField.focus();
                    
                    setTimeout(function() {
                        if (document.activeElement !== $searchField[0]) {
                            $searchField.trigger('focus');
                            $searchField.trigger('click');
                        }
                    }, 25);
                }
            }, 10);
        });
    }
    
    // Функция для тестирования текущего состояния
    function testSelect2AutoFocus() {
        console.log('=== Тестирование автофокуса Select2 ===');
        
        var $select2Elements = $('.select2-hidden-accessible');
        console.log('Найдено Select2 элементов:', $select2Elements.length);
        
        $select2Elements.each(function(index) {
            var $element = $(this);
            var events = $._data(this, 'events');
            var hasAutofocus = false;
            
            if (events && events['select2:open']) {
                events['select2:open'].forEach(function(handler) {
                    if (handler.namespace.includes('autofocus') || handler.namespace.includes('force-autofocus')) {
                        hasAutofocus = true;
                    }
                });
            }
            
            console.log('Элемент #' + (index + 1) + ':', {
                element: this,
                hasAutofocusHandler: hasAutofocus,
                classes: this.className,
                id: this.id || 'без ID'
            });
        });
        
        console.log('=== Конец тестирования ===');
    }
    
    // Экспортируем функции в глобальную область
    window.forceSelect2AutoFocus = forceSelect2AutoFocus;
    window.addGlobalSelect2AutoFocus = addGlobalSelect2AutoFocus;
    window.testSelect2AutoFocus = testSelect2AutoFocus;
    
    // Автоматически применяем при загрузке, если jQuery доступен
    if (typeof $ !== 'undefined') {
        $(document).ready(function() {
            setTimeout(function() {
                forceSelect2AutoFocus();
                addGlobalSelect2AutoFocus();
                
                console.log('🚀 Принудительный автофокус Select2 активирован!');
                console.log('Используйте testSelect2AutoFocus() для проверки состояния');
            }, 1000);
        });
    }
    
})();
