/**
 * Скрипт для устранения проблем с загрузкой библиотек на странице редактирования сделки
 * Автоматически загружает Select2 и DataTables если они не найдены
 */

// Глобальная переменная для отслеживания состояния загрузки
window.dealEditLibrariesStatus = {
    select2Loaded: false,
    dataTablesLoaded: false,
    jQueryLoaded: false
};

(function() {
    'use strict';

    // Проверяем jQuery
    function checkJQuery() {
        return typeof window.jQuery !== 'undefined' && typeof $ !== 'undefined';
    }

    // Проверяем Select2
    function checkSelect2() {
        return checkJQuery() && typeof $.fn.select2 !== 'undefined';
    }

    // Проверяем DataTables
    function checkDataTables() {
        return checkJQuery() && typeof $.fn.DataTable !== 'undefined';
    }

    // Загрузка CSS файла
    function loadCSS(href, onload) {
        const link = document.createElement('link');
        link.rel = 'stylesheet';
        link.type = 'text/css';
        link.href = href;
        
        if (onload) {
            link.onload = onload;
        }
        
        document.head.appendChild(link);
        return link;
    }

    // Загрузка JS файла
    function loadJS(src, onload, onerror) {
        const script = document.createElement('script');
        script.src = src;
        script.type = 'text/javascript';
        
        if (onload) {
            script.onload = onload;
        }
        
        if (onerror) {
            script.onerror = onerror;
        }
        
        document.head.appendChild(script);
        return script;
    }

    // Загрузка Select2
    function loadSelect2() {
        return new Promise((resolve, reject) => {
            if (checkSelect2()) {
                console.log('✅ Select2 уже загружен');
                window.dealEditLibrariesStatus.select2Loaded = true;
                resolve();
                return;
            }

            console.log('🔄 Загружаем Select2...');
            
            // Загружаем CSS
            loadCSS('https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css');
            
            // Загружаем JS
            loadJS(
                'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js',
                function() {
                    console.log('✅ Select2 успешно загружен');
                    window.dealEditLibrariesStatus.select2Loaded = true;
                    resolve();
                },
                function() {
                    console.error('❌ Ошибка загрузки Select2');
                    reject(new Error('Не удалось загрузить Select2'));
                }
            );
        });
    }

    // Загрузка DataTables
    function loadDataTables() {
        return new Promise((resolve, reject) => {
            if (checkDataTables()) {
                console.log('✅ DataTables уже загружен');
                window.dealEditLibrariesStatus.dataTablesLoaded = true;
                resolve();
                return;
            }

            console.log('🔄 Загружаем DataTables...');
            
            // Загружаем CSS
            loadCSS('https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css');
            
            // Загружаем JS
            loadJS(
                'https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js',
                function() {
                    console.log('✅ DataTables успешно загружен');
                    window.dealEditLibrariesStatus.dataTablesLoaded = true;
                    resolve();
                },
                function() {
                    console.error('❌ Ошибка загрузки DataTables');
                    reject(new Error('Не удалось загрузить DataTables'));
                }
            );
        });
    }

    // Инициализация Select2 элементов
    function initializeSelect2Elements() {
        if (!checkSelect2()) {
            console.warn('⚠️ Select2 не доступен для инициализации');
            return;
        }

        console.log('🔧 Инициализация Select2 элементов...');
        
        try {
            // Находим все select элементы, которые нужно преобразовать в Select2
            const selectorsToInitialize = [
                'select.select2',
                'select[data-select2]',
                'select[data-toggle="select2"]',
                '#deal-edit-form select:not(.no-select2)'
            ];

            selectorsToInitialize.forEach(selector => {
                $(selector).each(function() {
                    if (!$(this).hasClass('select2-hidden-accessible')) {
                        const $select = $(this);
                        const options = {
                            placeholder: $select.data('placeholder') || $select.attr('placeholder') || 'Выберите...',
                            allowClear: $select.data('allow-clear') === true || $select.hasClass('allow-clear'),
                            width: '100%',
                            dropdownParent: $select.closest('.modal').length ? $select.closest('.modal') : $('body')
                        };

                        // Проверяем, есть ли специальные настройки
                        if ($select.data('minimum-results-for-search') !== undefined) {
                            options.minimumResultsForSearch = $select.data('minimum-results-for-search');
                        }

                        try {
                            $select.select2(options);
                            console.log('✅ Select2 инициализирован для:', this.name || this.id || selector);
                        } catch (error) {
                            console.error('❌ Ошибка инициализации Select2 для элемента:', this, error);
                        }
                    }
                });
            });

            console.log('✅ Инициализация Select2 элементов завершена');
        } catch (error) {
            console.error('❌ Общая ошибка при инициализации Select2:', error);
        }
    }

    // Инициализация DataTables элементов
    function initializeDataTablesElements() {
        if (!checkDataTables()) {
            console.warn('⚠️ DataTables не доступен для инициализации');
            return;
        }

        console.log('🔧 Инициализация DataTables элементов...');
        
        try {
            $('table.datatable, table[data-datatable]').each(function() {
                if (!$.fn.DataTable.isDataTable(this)) {
                    const $table = $(this);
                    const options = {
                        language: {
                            url: '//cdn.datatables.net/plug-ins/1.11.5/i18n/ru.json'
                        },
                        responsive: true,
                        pageLength: 25,
                        ordering: true,
                        searching: true
                    };

                    try {
                        $table.DataTable(options);
                        console.log('✅ DataTables инициализирован для таблицы:', this.id || 'без ID');
                    } catch (error) {
                        console.error('❌ Ошибка инициализации DataTables для таблицы:', this, error);
                    }
                }
            });

            console.log('✅ Инициализация DataTables элементов завершена');
        } catch (error) {
            console.error('❌ Общая ошибка при инициализации DataTables:', error);
        }
    }

    // Основная функция инициализации
    function initializeLibraries() {
        console.log('🚀 Запуск системы автозагрузки библиотек для страницы редактирования сделки');

        // Проверяем jQuery
        if (!checkJQuery()) {
            console.error('❌ jQuery не найден. Невозможно продолжить.');
            return;
        }

        window.dealEditLibrariesStatus.jQueryLoaded = true;
        console.log('✅ jQuery найден');

        // Загружаем библиотеки параллельно
        Promise.allSettled([
            loadSelect2(),
            loadDataTables()
        ]).then(results => {
            console.log('📊 Результаты загрузки библиотек:');
            results.forEach((result, index) => {
                const libName = index === 0 ? 'Select2' : 'DataTables';
                if (result.status === 'fulfilled') {
                    console.log(`✅ ${libName}: успешно загружен`);
                } else {
                    console.error(`❌ ${libName}: ошибка загрузки -`, result.reason);
                }
            });

            // Инициализируем элементы
            setTimeout(() => {
                initializeSelect2Elements();
                initializeDataTablesElements();
                
                // Делаем функции доступными глобально для отладки
                window.initializeSelect2Elements = initializeSelect2Elements;
                window.initializeDataTablesElements = initializeDataTablesElements;
                window.checkLibrariesStatus = function() {
                    console.log('📋 Статус библиотек:', window.dealEditLibrariesStatus);
                    console.log('🔍 jQuery доступен:', checkJQuery());
                    console.log('🔍 Select2 доступен:', checkSelect2());
                    console.log('🔍 DataTables доступен:', checkDataTables());
                };
                
                console.log('🎉 Система автозагрузки библиотек завершена');
                console.log('💡 Для отладки используйте: checkLibrariesStatus()');
            }, 500);
        });
    }

    // Запускаем инициализацию когда DOM готов
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initializeLibraries);
    } else {
        // DOM уже готов
        setTimeout(initializeLibraries, 100);
    }

})();
