<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ isset($title_site) ? $title_site : 'Панель администратора | Личный кабинет Экспресс-дизайн' }}</title>
    
    <!-- CSS стили (загружаем сначала) -->
    <link rel="stylesheet" href="{{ asset('/css/p/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('/css/p/5.15.4/all.min.css') }}">
    <link rel="stylesheet" href="{{ asset('/css/p/all.min.css') }}">
    <link rel="stylesheet" href="{{ asset('/css/p/bootstrap-select.min.css') }}">
    <link rel="stylesheet" href="{{ asset('/css/p/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('/css/p/jquery.dataTables.min.css') }}">
    <link rel="stylesheet" href="{{ asset('css/admin-briefs.css') }}">
    
    <!-- JavaScript (основная библиотека jQuery - только одна версия) -->
    <script src="{{ asset('/js/p/jquery-3.6.0.min.js') }}"></script>
    <script src="{{ asset('/js/p/popper.min.js') }}"></script>
    <script src="{{ asset('/js/p/bootstrap.min.js') }}"></script>
    
    <!-- JavaScript (дополнительные плагины) -->
    <script src="{{ asset('/js/p/bootstrap-select.min.js') }}"></script>
    <script src="{{ asset('/js/p/defaults-ru_RU.min.js') }}"></script>
    <script src="{{ asset('/js/p/axios.min.js') }}"></script>
    <script src="{{ asset('/js/p/jquery.dataTables.min.js') }}"></script>
    
    <!-- Исправленный путь к Select2 -->
    <script src="{{ asset('/js/p/select2.min.js') }}"></script>
    
    <script src="{{ asset('/js/p/jquery.simplePagination.min.js') }}"></script>
    
    <!-- Графики и диаграммы -->
    <script src="{{ asset('/js/p/chart.min.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-adapter-date-fns@3"></script>
    
    <!-- Vite ресурсы (загружаем последними) -->
    @vite([
        'resources/css/font.css',
        'resources/css/element.css', 
        'resources/css/animation.css',
        'resources/css/admin.css',
        'resources/js/bootstrap.js', 
        'resources/js/ratings.js',
        'resources/js/modal.js', 
        'resources/js/success.js', 
        'resources/js/mask.js', 
        'resources/js/login.js'
    ])
    
    <!-- Обязательный (и достаточный) тег для браузеров -->
    <link type="image/x-icon" rel="shortcut icon" href="{{ asset('/favicon.ico') }}">

    <!-- Дополнительные иконки для десктопных браузеров -->
    <link type="image/png" sizes="16x16" rel="icon" href="{{ asset('/icons/favicon-16x16.png') }}">
    <link type="image/png" sizes="32x32" rel="icon" href="{{ asset('/icons/favicon-32x32.png') }}">
    <link type="image/png" sizes="96x96" rel="icon" href="{{ asset('/icons/favicon-96x96.png') }}">
    <link type="image/png" sizes="120x120" rel="icon" href="{{ asset('/icons/favicon-120x120.png') }}">

    <!-- Иконки для Android -->
    <link type="image/png" sizes="72x72" rel="icon" href="{{ asset('/icons/android-icon-72x72.png') }}">
    <link type="image/png" sizes="96x96" rel="icon" href="{{ asset('/icons/android-icon-96x96.png') }}">
    <link type="image/png" sizes="144x144" rel="icon" href="{{ asset('/icons/android-icon-144x144.png') }}">
    <link type="image/png" sizes="192x192" rel="icon" href="{{ asset('/icons/android-icon-192x192.png') }}">
    <link type="image/png" sizes="512x512" rel="icon" href="{{ asset('/icons/android-icon-512x512.png') }}">
    <link rel="manifest" href="./manifest.json">

    <!-- Иконки для iOS (Apple) -->
    <link sizes="57x57" rel="apple-touch-icon" href="{{ asset('/icons/apple-touch-icon-57x57.png') }} ">
    <link sizes="60x60" rel="apple-touch-icon" href="{{ asset('/icons/apple-touch-icon-60x60.png') }} ">
    <link sizes="72x72" rel="apple-touch-icon" href="{{ asset('/icons/apple-touch-icon-72x72.png') }} ">
    <link sizes="76x76" rel="apple-touch-icon" href="{{ asset('/icons/apple-touch-icon-76x76.png') }} ">
    <link sizes="114x114" rel="apple-touch-icon" href="{{ asset('/icons/apple-touch-icon-114x114.png') }} ">
    <link sizes="120x120" rel="apple-touch-icon" href="{{ asset('/icons/apple-touch-icon-120x120.png') }} ">
    <link sizes="144x144" rel="apple-touch-icon" href="{{ asset('/icons/apple-touch-icon-144x144.png') }} ">
    <link sizes="152x152" rel="apple-touch-icon" href="{{ asset('/icons/apple-touch-icon-152x152.png') }} ">
    <link sizes="180x180" rel="apple-touch-icon" href="{{ asset('/icons/apple-touch-icon-180x180.png') }} ">
    <!-- FontAwesome для звезд рейтинга -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">



    <!-- Иконки для MacOS (Apple) -->
    <link color="#e52037" rel="mask-icon" href="./safari-pinned-tab.svg">
    <!-- FontAwesome для звезд рейтинга -->


    <!-- Иконки и цвета для плиток Windows -->
    <meta name="msapplication-TileColor" content="#2b5797">
    <meta name="msapplication-TileImage" content="./mstile-144x144.png">
    <meta name="msapplication-square70x70logo" content="./mstile-70x70.png">
    <meta name="msapplication-square150x150logo" content="./mstile-150x150.png">
    <meta name="msapplication-wide310x150logo" content="./mstile-310x310.png">
    <meta name="msapplication-square310x310logo" content="./mstile-310x150.png">
    <meta name="application-name" content="My Application">
    <meta name="msapplication-config" content="./browserconfig.xml">
    <livewire:styles />

    <script>
        wow = new WOW({
            boxClass: 'wow', // default
            animateClass: 'animated', // default
            offset: 0, // default
            mobile: true, // default
            live: true // default
        })
        wow.init();
    </script>

    <!-- Глобальный скрипт для DataTables с проверкой дублирования инициализации -->
    <script>
        // Функция для безопасной инициализации DataTable
        function initializeDataTable(tableId, options) {
            // Проверяем, инициализирована ли таблица
            if ($.fn.dataTable.isDataTable('#' + tableId)) {
                $('#' + tableId).DataTable().destroy();
            }
            
            // Устанавливаем стандартные параметры
            const defaultOptions = {
                "language": {
                    "url": "//cdn.datatables.net/plug-ins/1.10.21/i18n/Russian.json"
                },
                "responsive": true,
                "pageLength": 10
            };
            
            // Объединяем стандартные параметры с переданными
            const mergedOptions = $.extend({}, defaultOptions, options || {});
            
            // Инициализируем таблицу
            return $('#' + tableId).DataTable(mergedOptions);
        }
        
        // Делаем функцию доступной глобально
        window.initializeDataTable = initializeDataTable;
    </script>

    <!-- Стили для пагинации -->
    <style>
        /* Стили для пагинации Laravel */
        .pagination {
            display: flex;
            justify-content: center;
            align-items: center;
            margin: 20px 0;
            flex-wrap: wrap;
            gap: 4px;
        }

        .pagination .page-item {
            list-style: none;
        }

        .pagination .page-link {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 40px;
            height: 40px;
            padding: 8px 12px;
            margin: 0 2px;
            color: #495057;
            text-decoration: none;
            background-color: #ffffff;
            border: 1px solid #dee2e6;
            border-radius: 6px;
            transition: all 0.3s ease;
            font-size: 14px;
            font-weight: 500;
            position: relative;
            overflow: hidden;
        }

        .pagination .page-link:hover {
            color: #007bff;
            background-color: #f8f9fa;
            border-color: #007bff;
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(0, 123, 255, 0.15);
        }

        .pagination .page-item.active .page-link {
            color: #ffffff;
            background-color: #007bff;
            border-color: #007bff;
            box-shadow: 0 2px 4px rgba(0, 123, 255, 0.25);
            font-weight: 600;
        }

        .pagination .page-item.active .page-link:hover {
            background-color: #0056b3;
            border-color: #0056b3;
            transform: none;
        }

        .pagination .page-item.disabled .page-link {
            color: #6c757d;
            background-color: #ffffff;
            border-color: #dee2e6;
            opacity: 0.6;
            cursor: not-allowed;
            pointer-events: none;
        }

        /* Стили для стрелок навигации */
        .pagination .page-item:first-child .page-link,
        .pagination .page-item:last-child .page-link {
            font-weight: 600;
            min-width: 44px;
        }

        .pagination .page-item:first-child .page-link:before {
            content: "‹";
            font-size: 18px;
            line-height: 1;
        }

        .pagination .page-item:last-child .page-link:after {
            content: "›";
            font-size: 18px;
            line-height: 1;
        }

        /* Адаптивные стили для мобильных устройств */
        @media (max-width: 576px) {
            .pagination {
                gap: 2px;
            }
            
            .pagination .page-link {
                min-width: 36px;
                height: 36px;
                padding: 6px 8px;
                font-size: 13px;
                margin: 0 1px;
            }
        }

        /* Дополнительные стили для информации о пагинации */
        .pagination-info {
            text-align: center;
            margin-bottom: 15px;
            color: #6c757d;
            font-size: 14px;
        }

        .pagination-info strong {
            color: #495057;
            font-weight: 600;
        }

        /* Стили для контейнера пагинации */
        .pagination-wrapper {
            background-color: #ffffff;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
            margin-top: 20px;
        }

        /* Улучшенные стили для больших наборов страниц */
        .pagination .page-item.dots .page-link {
            background-color: transparent;
            border-color: transparent;
            color: #6c757d;
            cursor: default;
        }

        .pagination .page-item.dots .page-link:hover {
            background-color: transparent;
            border-color: transparent;
            transform: none;
            box-shadow: none;
        }

        /* Стили для показа общего количества записей */
        .pagination-summary {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            flex-wrap: wrap;
            gap: 10px;
        }

        .pagination-summary .results-info {
            color: #495057;
            font-size: 14px;
            font-weight: 500;
        }

        .pagination-summary .per-page-selector {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 14px;
            color: #495057;
        }

        .pagination-summary .per-page-selector select {
            padding: 4px 8px;
            border: 1px solid #dee2e6;
            border-radius: 4px;
            font-size: 13px;
        }

        /* Стили для Tailwind CSS пагинации */
        nav[role="navigation"] {
            margin: 20px 0;
        }

        /* Основные стили для элементов пагинации */
        nav[role="navigation"] .relative.inline-flex {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 40px;
            height: 40px;
            padding: 8px 12px;
            margin: 0 1px;
            color: #495057;
            text-decoration: none;
            background-color: #ffffff;
            border: 1px solid #dee2e6;
            border-radius: 6px;
            transition: all 0.3s ease;
            font-size: 14px;
            font-weight: 500;
            position: relative;
            overflow: hidden;
        }

        /* Стили для активной страницы */
        nav[role="navigation"] span[aria-current="page"] span {
            color: #ffffff !important;
            background-color: #007bff !important;
            border-color: #007bff !important;
            box-shadow: 0 2px 4px rgba(0, 123, 255, 0.25);
            font-weight: 600;
        }

        /* Стили при наведении */
        nav[role="navigation"] a.relative.inline-flex:hover {
            color: #007bff;
            background-color: #f8f9fa;
            border-color: #007bff;
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(0, 123, 255, 0.15);
        }

        /* Стили для неактивных элементов (Previous/Next) */
        nav[role="navigation"] span[aria-disabled="true"] span {
            color: #6c757d !important;
            background-color: #ffffff !important;
            border-color: #dee2e6 !important;
            opacity: 0.6;
            cursor: not-allowed;
            pointer-events: none;
        }

        /* Стили для многоточия */
        nav[role="navigation"] span[aria-disabled="true"] span:not([aria-hidden="true"]) {
            background-color: transparent !important;
            border-color: transparent !important;
            color: #6c757d !important;
            cursor: default;
        }

        /* Стили для стрелок навигации */
        nav[role="navigation"] a[rel="next"],
        nav[role="navigation"] a[aria-label*="Previous"] {
            font-weight: 600;
            min-width: 44px;
        }

        /* Стили для SVG иконок */
        nav[role="navigation"] svg {
            width: 20px;
            height: 20px;
        }

        /* Стили для информации о результатах */
        nav[role="navigation"] p {
            color: #495057;
            font-size: 14px;
            font-weight: 500;
            margin: 0;
        }

        nav[role="navigation"] p span.font-medium {
            color: #007bff;
            font-weight: 600;
        }

        /* Адаптивные стили для мобильных устройств */
        @media (max-width: 640px) {
            nav[role="navigation"] .relative.inline-flex {
                min-width: 36px;
                height: 36px;
                padding: 6px 8px;
                font-size: 13px;
                margin: 0 1px;
            }

            nav[role="navigation"] .hidden.sm\:flex-1 {
                flex-direction: column;
                gap: 15px;
            }

            nav[role="navigation"] p {
                text-align: center;
                font-size: 13px;
            }
        }

        /* Стили для темной темы (если используется) */
        nav[role="navigation"] .dark\:bg-gray-800 {
            background-color: #343a40;
            border-color: #495057;
            color: #ffffff;
        }

        nav[role="navigation"] .dark\:text-gray-400 {
            color: #adb5bd;
        }

        nav[role="navigation"] .dark\:border-gray-600 {
            border-color: #495057;
        }

        /* Улучшенные стили для больших списков страниц */
        nav[role="navigation"] .rtl\:flex-row-reverse {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 2px;
        }

        /* Стили для rounded элементов */
        nav[role="navigation"] .rounded-l-md {
            border-top-left-radius: 6px;
            border-bottom-left-radius: 6px;
            border-right: 0;
        }

        nav[role="navigation"] .rounded-r-md {
            border-top-right-radius: 6px;
            border-bottom-right-radius: 6px;
            border-left: 0;
        }

        nav[role="navigation"] .-ml-px {
            margin-left: -1px;
        }

        /* Стили для фокуса */
        nav[role="navigation"] a:focus,
        nav[role="navigation"] span:focus {
            outline: none;
            z-index: 10;
            box-shadow: 0 0 0 3px rgba(0, 123, 255, 0.25);
        }

        /* Анимация для активных элементов */
        nav[role="navigation"] a:active {
            background-color: #e9ecef;
            transform: translateY(0);
        }

        /* Стили для улучшения доступности */
        nav[role="navigation"] [aria-label] {
            position: relative;
        }

        nav[role="navigation"] [aria-label]:hover::after {
            content: attr(aria-label);
            position: absolute;
            bottom: 100%;
            left: 50%;
            transform: translateX(-50%);
            background-color: #333;
            color: white;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            white-space: nowrap;
            z-index: 1000;
            margin-bottom: 5px;
        }
    </style>

<body>

    <div id="loading-screen" class="wow fadeInleft" data-wow-duration="1s" data-wow-delay="1s"">
        <img src="/storage/icon/fool_logo.svg" alt="Loading">
    </div>
    <script>
        window.addEventListener('load', () => {
            const loadingScreen = document.getElementById('loading-screen');
            const content = document.getElementById('content');
            setTimeout(() => {
                loadingScreen.classList.add('hidden'); // Применяем класс для анимации исчезновения
                document.body.style.overflow = 'auto'; // Включаем прокрутку
                setTimeout(() => {
                    loadingScreen.style.display =
                    'none'; // Полностью убираем загрузку после анимации
                    content.style.opacity =
                    '1'; // Плавно показываем содержимое (контент уже анимируется в CSS)
                }, 1000); // Длительность анимации исчезновения (совпадает с fadeOut)
            }, 1000); // Задержка до начала исчезновения
        });
    </script>
    @if (session('success'))
        <div id="success-message" class="success-message">
            {{ session('success') }}
        </div>
    @endif
    @if (session('error'))
        <div id="error-message" class="error-message">
            {{ session('error') }}
        </div>
    @endif
    <main class="">

        @yield('content')
        @include('layouts/mobponel')

    </main>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const toggleButton = document.getElementById('toggle-panel');
            const panel = document.querySelector('.main__ponel');
            // Проверяем сохраненное состояние панели в localStorage
            const isCollapsed = localStorage.getItem('panelCollapsed') === 'true';
            if (isCollapsed) {
                panel.classList.add('collapsed');
            }
            // Обработчик клика по кнопке переключения
            toggleButton.addEventListener('click', () => {
                panel.classList.toggle('collapsed');
                // Сохраняем текущее состояние панели в localStorage
                const collapsed = panel.classList.contains('collapsed');
                localStorage.setItem('panelCollapsed', collapsed);
            });
        });
    </script>

    <!-- Добавляем инициализацию DataTables для всех таблиц -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            initDataTables();
            
            // Функция для инициализации DataTables
            function initDataTables() {
                // Проверяем, загружен ли jQuery
                if (typeof jQuery === 'undefined') {
                    console.error('jQuery не загружен! DataTables не будет инициализирован.');
                    return;
                }
                
                // Проверяем наличие DataTables
                if (typeof jQuery.fn.DataTable === 'undefined') {
                    console.error('jQuery DataTables не загружен! Загружаем DataTables...');
                    
                    // Загружаем DataTables динамически
                    var cssLink = document.createElement('link');
                    cssLink.rel = 'stylesheet';
                    cssLink.href = 'https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap4.min.css';
                    document.head.appendChild(cssLink);
                    
                    var scriptResponsive = document.createElement('script');
                    scriptResponsive.src = 'https://cdn.datatables.net/responsive/2.2.9/js/dataTables.responsive.min.js';
                    document.head.appendChild(scriptResponsive);
                    
                    var script = document.createElement('script');
                    script.src = 'https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js';
                    script.onload = function() {
                        initAllTables();
                    };
                    document.head.appendChild(script);
                } else {
                    initAllTables();
                }
            }
            
            // Функция для инициализации всех таблиц
            function initAllTables() {
                // Установка языка для DataTables
                $.extend(true, $.fn.dataTable.defaults, {
                    "language": {
                        "url": "//cdn.datatables.net/plug-ins/1.10.21/i18n/Russian.json"
                    }
                });
                
                // Инициализация только тех таблиц, которые еще не инициализированы
                $('table.table').not('.dataTable, .no-datatable').each(function() {
                    var tableId = $(this).attr('id');
                    
                    // Добавляем id если его нет
                    if (!tableId) {
                        tableId = 'datatable-' + Math.floor(Math.random() * 10000);
                        $(this).attr('id', tableId);
                    }
                    
                    // Проверяем, инициализирована ли таблица уже (двойная защита)
                    if ($.fn.dataTable.isDataTable('#' + tableId)) {
                        console.log('Таблица #' + tableId + ' уже инициализирована. Пропускаем.');
                        return;
                    }
                    
                    // Добавляем атрибуты data-title для мобильного вида
                    addDataAttributes(this);
                    
                    try {
                        // Инициализируем DataTable
                        $(this).DataTable({
                            responsive: true,
                            autoWidth: false,
                            pageLength: 10,
                            lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "Все"]],
                            dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>t<"row"<"col-sm-12 col-md-6"i><"col-sm-12 col-md-6"p>>',
                            columnDefs: [
                                { responsivePriority: 1, targets: 0 }, // Первая колонка
                                { responsivePriority: 2, targets: -1 } // Последняя колонка
                            ]
                        });
                        console.log('Таблица #' + tableId + ' успешно инициализирована');
                    } catch (error) {
                        console.error('Ошибка при инициализации таблицы #' + tableId + ':', error);
                    }
                });
            }
            
            // Функция для добавления атрибутов data-title к ячейкам таблицы
            function addDataAttributes(table) {
                var headers = $(table).find('thead th');
                
                $(table).find('tbody tr').each(function() {
                    $(this).find('td').each(function(index) {
                        if (index < headers.length) {
                            var title = $(headers[index]).text().trim();
                            $(this).attr('data-title', title);
                        }
                    });
                });
            }
        });
    </script>

    <!-- Контейнер для уведомлений (toast) -->
    <div aria-live="polite" aria-atomic="true" class="toast-container" style="position: fixed; top: 15px; right: 15px; z-index: 1060;"></div>
    
    <!-- Скрипт для показа уведомлений при загрузке страницы -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Функция для отображения уведомления
            function showToast(message, type = 'success') {
                const toastId = 'toast-' + Date.now();
                const backgroundColor = type === 'success' ? '#d4edda' : 
                                       type === 'error' ? '#f8d7da' :
                                       type === 'info' ? '#d1ecf1' : '#fff3cd';
                const textColor = type === 'success' ? '#155724' : 
                                 type === 'error' ? '#721c24' :
                                 type === 'info' ? '#0c5460' : '#856404';
                const iconClass = type === 'success' ? 'fa-check-circle' :
                                  type === 'error' ? 'fa-exclamation-circle' :
                                  type === 'info' ? 'fa-info-circle' : 'fa-exclamation-triangle';
                
                const toastHTML = `
                    <div id="${toastId}" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
                        <div class="toast-header" style="background-color: ${backgroundColor}; color: ${textColor};">
                            <i class="fas ${iconClass} mr-2"></i>
                            <strong class="mr-auto">${type === 'success' ? 'Успешно' : type === 'error' ? 'Ошибка' : type === 'info' ? 'Информация' : 'Предупреждение'}</strong>
                            <button type="button" class="ml-2 mb-1 close" data-dismiss="toast" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="toast-body" style="background-color: #fff;">
                            ${message}
                        </div>
                    </div>
                `;
                
                // Добавляем тост в контейнер
                document.querySelector('.toast-container').insertAdjacentHTML('beforeend', toastHTML);
                
                // Показываем тост
                const toast = document.getElementById(toastId);
                
                if (typeof $ !== 'undefined' && typeof $.fn.toast === 'function') {
                    // Если доступен Bootstrap Toast API
                    $(toast).toast({delay: 5000}).toast('show');
                    
                    // Удаляем элемент после скрытия
                    $(toast).on('hidden.bs.toast', function() {
                        this.remove();
                    });
                } else {
                    // Если Bootstrap Toast API недоступен, используем собственную реализацию
                    toast.style.opacity = '1';
                    toast.style.display = 'block';
                    
                    setTimeout(() => {
                        toast.style.opacity = '0';
                        setTimeout(() => {
                            toast.remove();
                        }, 300);
                    }, 5000);
                }
            }

            // Проверка наличия флеш-сообщений от сервера
            @if(session('success'))
                showToast("{{ session('success') }}", 'success');
            @endif

            @if(session('error'))
                showToast("{{ session('error') }}", 'error');
            @endif

            @if(session('info'))
                showToast("{{ session('info') }}", 'info');
            @endif

            @if(session('warning'))
                showToast("{{ session('warning') }}", 'warning');
            @endif

            // Добавляем функцию в глобальную область видимости для доступа из других скриптов
            window.showToast = showToast;
        });
    </script>
    @section('scripts')
   
@endsection
  <script>
        // Функция для предотвращения зума на iPhone при фокусе на input
        function preventInputZoom() {
            // Проверяем, является ли устройство iOS
            const isIOS = /iPad|iPhone|iPod/.test(navigator.userAgent) && !window.MSStream;
            
            if (isIOS) {
                // Запоминаем исходное значение метатега viewport
                const originalViewport = document.querySelector('meta[name="viewport"]').getAttribute('content');
                
                // Находим все поля ввода
                const inputElements = document.querySelectorAll('input, select, textarea');
                
                // Добавляем обработчики событий для каждого поля ввода
                inputElements.forEach(input => {
                    // При фокусе запрещаем масштабирование
                    input.addEventListener('focus', function() {
                        document.querySelector('meta[name="viewport"]').setAttribute('content', 
                            'width=device-width, initial-scale=1, maximum-scale=1');
                    });
                    
                    // При потере фокуса восстанавливаем исходные настройки
                    input.addEventListener('blur', function() {
                        setTimeout(function() {
                            document.querySelector('meta[name="viewport"]').setAttribute('content', originalViewport);
                        }, 300);
                    });
                });
            }
        }
        
        // Вызываем функцию при загрузке страницы
        document.addEventListener('DOMContentLoaded', preventInputZoom);
    </script>
    <!-- Скрипт для проверки и загрузки Select2 при необходимости -->
    <script src="{{ asset('/js/select2-checker.js') }}"></script>
</body>
</html>
