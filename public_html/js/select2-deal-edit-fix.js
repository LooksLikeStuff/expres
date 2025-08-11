/**
 * Решение проблемы с Select2 для страницы редактирования сделки
 */
document.addEventListener('DOMContentLoaded', function() {
    // Проверяем, загружен ли jQuery
    if (typeof jQuery === 'undefined') {
        console.error('❌ jQuery не загружен. Select2 не может быть инициализирован.');
        return;
    }

    // Проверяем, доступен ли Select2
    if (typeof jQuery.fn.select2 === 'undefined') {
        console.log('🔄 Select2 не обнаружен, загружаем с CDN...');
        
        // Создаем и добавляем CSS для Select2
        var cssLink = document.createElement('link');
        cssLink.rel = 'stylesheet';
        cssLink.href = 'https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css';
        document.head.appendChild(cssLink);
        
        // Создаем и добавляем JS для Select2
        var script = document.createElement('script');
        script.src = 'https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js';
        
        // После загрузки скрипта инициализируем все Select2 элементы
        script.onload = function() {
            console.log('✅ Select2 успешно загружен с CDN');
            configureSelect2();
            initializeAllSelect2();
            
            // Запускаем отладку через 1 секунду после загрузки
            setTimeout(function() {
                console.log('🔍 Проверка состояния Select2 элементов...');
                checkSelect2Status();
            }, 1000);
        };
        
        document.head.appendChild(script);
    } else {
        console.log('✅ Select2 уже загружен');
        // Select2 уже загружен, настраиваем и инициализируем элементы
        configureSelect2();
        initializeAllSelect2();
        
        // Запускаем отладку через 1 секунду
        setTimeout(function() {
            console.log('🔍 Проверка состояния Select2 элементов...');
            checkSelect2Status();
        }, 1000);
    }
    
    // Настройка Select2
    function configureSelect2() {
        if (typeof $.fn.select2.defaults !== 'undefined') {
            $.fn.select2.defaults.set('language', {
                errorLoading: function () { return 'Невозможно загрузить результаты'; },
                inputTooLong: function (args) {
                    var overChars = args.input.length - args.maximum;
                    var message = 'Пожалуйста, удалите ' + overChars + ' символ';
                    if (overChars >= 2 && overChars <= 4) { message += 'а'; } 
                    else if (overChars >= 5) { message += 'ов'; }
                    return message;
                },
                inputTooShort: function (args) {
                    var remainingChars = args.minimum - args.input.length;
                    var message = 'Пожалуйста, введите еще ' + remainingChars + ' символ';
                    if (remainingChars >= 2 && remainingChars <= 4) { message += 'а'; } 
                    else if (remainingChars >= 5) { message += 'ов'; }
                    return message;
                },
                loadingMore: function () { return 'Загрузка данных...'; },
                maximumSelected: function (args) {
                    var message = 'Вы можете выбрать не более ' + args.maximum + ' элемент';
                    if (args.maximum >= 2 && args.maximum <= 4) { message += 'а'; } 
                    else if (args.maximum >= 5) { message += 'ов'; }
                    return message;
                },
                noResults: function () { return 'Совпадений не найдено'; },
                searching: function () { return 'Поиск...'; }
            });
        }
    }

    // Функция для инициализации всех Select2 элементов на странице
    function initializeAllSelect2() {
        try {
            console.log('🔄 Инициализация Select2 элементов...');
            
            // Инициализация стандартных селектов с Select2
            $('select.select2, .select2-enabled select, select[data-select2="true"]').each(function() {
                var $select = $(this);
                
                // Пропускаем уже инициализированные
                if ($select.hasClass('select2-hidden-accessible')) {
                    return;
                }
                
                var options = {};
                
                if ($select.data('placeholder')) {
                    options.placeholder = $select.data('placeholder');
                }
                
                if ($select.data('allow-clear') === true) {
                    options.allowClear = true;
                }
                
                if ($select.data('tags') === true) {
                    options.tags = true;
                }
                
                if ($select.data('minimum-results-for-search') !== undefined) {
                    options.minimumResultsForSearch = $select.data('minimum-results-for-search');
                }
                
                // Если есть data-ajax-url, настраиваем AJAX
                if ($select.data('ajax-url')) {
                    options.ajax = {
                        url: $select.data('ajax-url'),
                        dataType: 'json',
                        delay: 250,
                        cache: true,
                        processResults: function (data) {
                            return { results: data };
                        }
                    };
                }
                
                // Применяем Select2 с собранными опциями
                try {
                    $select.select2(options);
                    console.log('✅ Инициализирован Select2:', $select.attr('id') || $select.attr('name') || 'безымянный');
                } catch (e) {
                    console.error('❌ Ошибка инициализации Select2 для:', $select.attr('id') || $select.attr('name') || 'безымянный', e);
                }
            });
            
            console.log('✅ Инициализация Select2 элементов завершена');
        } catch (e) {
            console.error('❌ Ошибка при инициализации Select2:', e);
        }
    }
    
    // Функция проверки статуса Select2
    function checkSelect2Status() {
        console.log('=== ОТЛАДКА SELECT2 ===');
        
        const allSelects = document.querySelectorAll('select');
        console.log(`Всего select элементов: ${allSelects.length}`);
        
        const initializedSelects = document.querySelectorAll('select.select2-hidden-accessible');
        console.log(`Инициализированных Select2: ${initializedSelects.length}`);
        
        const uninitializedSelects = document.querySelectorAll('select:not(.select2-hidden-accessible)');
        console.log(`НЕ инициализированных Select2: ${uninitializedSelects.length}`);
        
        if (uninitializedSelects.length > 0) {
            console.log('НЕ инициализированные поля:');
            uninitializedSelects.forEach((select, index) => {
                console.log(`${index + 1}. ID: ${select.id || 'без ID'}, Name: ${select.name || 'без name'}, Class: ${select.className}`);
            });
            
            // Пытаемся принудительно инициализировать
            console.log('🔧 Пытаемся принудительно инициализировать...');
            initializeAllSelect2();
        } else {
            console.log('✅ Все Select2 поля инициализированы!');
        }
        
        console.log('=== КОНЕЦ ОТЛАДКИ ===');
    }
    
    // Делаем функции глобальными для отладки в консоли
    window.checkSelect2Status = checkSelect2Status;
    window.initializeAllSelect2 = initializeAllSelect2;
    
    // Добавляем функцию для принудительной переинициализации
    window.forceReinitializeAllSelect2 = function() {
        console.log('🔄 Принудительная переинициализация Select2...');
        
        // Уничтожаем существующие экземпляры
        $('select.select2-hidden-accessible').select2('destroy');
        
        // Заново инициализируем все
        initializeAllSelect2();
        
        setTimeout(function() {
            checkSelect2Status();
        }, 500);
        
        return 'Переинициализация выполнена';
    };
    
    console.log('💡 Для отладки Select2 введите в консоли: checkSelect2Status()');
    console.log('💡 Для принудительной переинициализации: forceReinitializeAllSelect2()');
});
