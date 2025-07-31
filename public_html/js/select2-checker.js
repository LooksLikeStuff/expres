/**
 * Скрипт для проверки наличия Select2 и его автоматической загрузки
 */
document.addEventListener('DOMContentLoaded', function() {
    // Проверяем, загружен ли jQuery
    if (typeof jQuery === 'undefined') {
        console.error('jQuery не загружен. Select2 не может быть инициализирован.');
        return;
    }

    // Проверяем, доступен ли Select2
    if (typeof jQuery.fn.select2 === 'undefined') {
        console.log('Select2 не обнаружен, загружаем с CDN...');
        
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
            console.log('Select2 успешно загружен с CDN');
            initializeAllSelect2();
        };
        
        document.head.appendChild(script);
    } else {
        console.log('Select2 уже загружен');
        // Select2 уже загружен, просто инициализируем элементы
        initializeAllSelect2();
    }

    // Функция для инициализации всех Select2 элементов на странице
    function initializeAllSelect2() {
        try {
            // Инициализация стандартных селектов с Select2
            $('select.select2').each(function() {
                var options = {};
                
                if ($(this).data('placeholder')) {
                    options.placeholder = $(this).data('placeholder');
                }
                
                if ($(this).data('allow-clear') === true) {
                    options.allowClear = true;
                }
                
                $(this).select2(options);
            });
            
            console.log('Все Select2 элементы инициализированы');
        } catch (error) {
            console.error('Ошибка при инициализации Select2:', error);
        }
    }
});
