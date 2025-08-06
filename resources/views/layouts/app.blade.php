<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>

    <meta charset="utf-8">
    <meta name="user-id" content="{{ Auth::id() }}">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title_site ?? 'Личный кабинет' }}</title>
    <link rel="stylesheet" href="{{ asset('/css/animate.css') }}">
    <link rel="stylesheet" href="{{ asset('/css/introjs.min.css') }}">


    <script src="{{ asset('/js/wow.js') }}"></script>
    <!-- Подключаем стили Intro.js -->


    <script src="{{ asset('/js/intro.min.js') }}"></script>


    <!-- CSS стили (загружаем сначала) -->
    <link rel="stylesheet" href="{{ asset('/css/p/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('/css/p/5.15.4/all.min.css') }}">
    <link rel="stylesheet" href="{{ asset('/css/p/all.min.css') }}">
    <link rel="stylesheet" href="{{ asset('/css/p/bootstrap-select.min.css') }}">
    <link rel="stylesheet" href="{{ asset('/css/p/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('/css/p/jquery.dataTables.min.css') }}">
    <link rel="stylesheet" href="{{ asset('css/admin-briefs.css') }}">

    @yield('stylesheets')

    <!-- JavaScript (основные библиотеки) -->
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

    <!-- Подключаем систему загрузки больших файлов -->
    <script src="{{ asset('js/large-file-upload.js') }}"></script>

    <!-- Подключаем CSS для загрузки больших файлов -->
    <link rel="stylesheet" href="{{ asset('css/large-file-upload.css') }}"></script>

    @yield('scripts')

    @vite(['resources/css/font.css', 'resources/js/ratings.js','resources/css/animation.css', 'resources/css/style.css', 'resources/css/element.css', 'resources/css/mobile.css', 'resources/js/bootstrap.js', 'resources/js/modal.js', 'resources/js/success.js', 'resources/js/mask.js'])

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
    <link rel="manifest" href="{{ asset('/manifest.json') }}">

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

    <!-- Иконки для MacOS (Apple) -->
    <link color="#e52037" rel="mask-icon" href="./safari-pinned-tab.svg">

    <!-- Иконки и цвета для плиток Windows -->
    <meta name="msapplication-TileColor" content="#2b5797">
    <meta name="msapplication-TileImage" content="./mstile-144x144.png">
    <meta name="msapplication-square70x70logo" content="./mstile-70x70.png">
    <meta name="msapplication-square150x150logo" content="./mstile-150x150.png">
    <meta name="msapplication-wide310x150logo" content="./mstile-310x310.png">
    <meta name="msapplication-square310x310logo" content="./mstile-310x150.png">
    <meta name="application-name" content="My Application">
    <meta name="msapplication-config" content="./browserconfig.xml">

    <!-- FontAwesome для звезд рейтинга -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

    <!-- PWA скрипты -->

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
    <script>
        function refreshCsrfToken() {
            fetch('{{ route('refresh-csrf') }}')
                .then(response => response.json())
                .then data => {
                    document.querySelector('meta[name="csrf-token"]').setAttribute('content', data.token);
                    document.querySelectorAll('input[name="_token"]').forEach(input => input.value = data.token);
                });
        }

        setInterval(refreshCsrfToken, 60000); // Обновление каждые 10 минут
    </script>
    @include('layouts/style')

    <!-- Передача данных для JS -->
    <script>
        window.Laravel = {!! json_encode([
            'csrfToken' => csrf_token(),
            'user' => Auth::check()
                ? [
                    'id' => Auth::id(),
                    'name' => Auth::user()->name,
                    'email' => Auth::user()->email,
                    'status' => Auth::user()->status,
                ]
                : null,
        ]) !!};
    </script>
    <!-- Дополнительные скрипты и стили в зависимости от страницы -->
    @yield('scripts')
    @yield('styles')
 <style>
     .profile-awards {
    width: 50px !important;
}
    </style>
    <!-- Добавляем улучшенный скрипт для раскрывающихся фильтров -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Инициализация раскрывающихся фильтров на всех страницах
            initCollapsibleFilters();
        });

        // Улучшенная функция инициализации раскрывающихся фильтров
        function initCollapsibleFilters() {
            const filterToggles = document.querySelectorAll('.filter-toggle');

            filterToggles.forEach(toggle => {
                // Получаем целевую панель либо из data-target атрибута, либо по умолчанию #filter-panel
                const targetSelector = toggle.getAttribute('data-target') || '#filter-panel';
                const panel = document.querySelector(targetSelector);
                const icon = toggle.querySelector('.filter-toggle-icon i');

                if (!panel) {
                    console.warn('Не найдена панель фильтра:', targetSelector);
                    return;
                }

                // Проверяем сохраненное состояние в localStorage
                const targetId = panel.id;
                const isExpanded = localStorage.getItem('filter_' + targetId) === 'expanded';

                // Устанавливаем начальное состояние
                if (isExpanded) {
                    panel.classList.add('expanded');
                    if (icon) icon.classList.add('rotated');
                }

                // Добавляем обработчик события для переключения состояния
                toggle.addEventListener('click', function(event) {
                    event.preventDefault(); // Предотвращаем действие по умолчанию

                    panel.classList.toggle('expanded');
                    if (icon) icon.classList.toggle('rotated');

                    // Сохраняем новое состояние в localStorage
                    if (panel.classList.contains('expanded')) {
                        try {
                            localStorage.setItem('filter_' + targetId, 'expanded');
                        } catch (e) {
                            console.warn('Не удалось сохранить состояние фильтра в localStorage');
                        }
                    } else {
                        try {
                            localStorage.setItem('filter_' + targetId, 'collapsed');
                        } catch (e) {
                            console.warn('Не удалось сохранить состояние фильтра в localStorage');
                        }
                    }
                });
            });

            // Проверяем, есть ли активные фильтры, и автоматически раскрываем панель
            const activeFilterInputs = document.querySelectorAll(
                '.filter input[value]:not([value=""]), .filter select option:checked:not([value=""])');
            if (activeFilterInputs.length > 0) {
                // Для каждой формы фильтров с активными фильтрами
                const filterForms = new Set();
                activeFilterInputs.forEach(input => {
                    const form = input.closest('form');
                    if (form) filterForms.add(form);
                });

                filterForms.forEach(form => {
                    const panel = form.closest('.filter-panel');
                    if (panel && !panel.classList.contains('expanded')) {
                        // Находим соответствующий toggle
                        const toggleId = panel.id;
                        const toggle = document.querySelector(`.filter-toggle[data-target="#${toggleId}"]`) ||
                            document.querySelector('.filter-toggle[data-target="#filter-panel"]');
                        if (toggle) {
                            // Эмулируем клик для раскрытия фильтра
                            setTimeout(() => {
                                if (!panel.classList.contains('expanded')) {
                                    toggle.click();
                                }
                            }, 100);
                        }
                    }
                });
            }

            // Обновляем счетчики активных фильтров
            updateFilterCounters();
        }

        // Функция для обновления счетчиков активных фильтров
        function updateFilterCounters() {
            const forms = document.querySelectorAll('.filter form');

            forms.forEach(form => {
                let count = 0;

                // Проверяем текстовые поля, даты и селекты
                const inputs = form.querySelectorAll('input[type="text"], input[type="date"], select');
                inputs.forEach(input => {
                    if (input.value && input.name !== 'view_type') {
                        count++;
                    }
                });

                // Обновляем счетчик для текущей формы
                const counterElements = form.closest('.filter-panel')?.parentNode.querySelectorAll(
                    '.filter-counter');
                if (counterElements && counterElements.length > 0) {
                    counterElements.forEach(counter => {
                        counter.textContent = count;
                        if (count > 0) {
                            counter.classList.add('active');
                        } else {
                            counter.classList.remove('active');
                        }
                    });
                }
            });
        }
    </script>



    <!-- Стили для модального окна рейтингов -->
    <style>
        /* Основной контейнер модального окна */
        .rating-modal {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 10000;
            display: flex;
            justify-content: center;
            align-items: center;
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s ease;
        }

        .rating-modal.show {
            opacity: 1;
            visibility: visible;
        }

        .rating-modal-backdrop {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.7);
            backdrop-filter: blur(4px);
        }

        .rating-modal-content {
            position: relative;
            background: #fff;
            border-radius: 16px;
            padding: 0;
            max-width: 520px;
            width: 90%;
            max-height: 90vh;
            overflow-y: auto;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
            transform: translateY(-20px);
            transition: transform 0.3s ease;
        }

        .rating-modal.show .rating-modal-content {
            transform: translateY(0);
        }

        /* Заголовок модального окна */
        .rating-modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 24px 24px 16px;
            border-bottom: 1px solid #e9ecef;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 16px 16px 0 0;
        }

        .rating-modal-header h2 {
            margin: 0;
            font-size: 1.4rem;
            font-weight: 600;
        }

        .rating-modal-close {
            background: rgba(255, 255, 255, 0.2);
            border: none;
            color: white;
            font-size: 18px;
            width: 32px;
            height: 32px;
            border-radius: 50%;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s ease;
        }

        .rating-modal-close:hover {
            background: rgba(255, 255, 255, 0.3);
            transform: scale(1.1);
        }

        /* Прогресс бар */
        .rating-progress {
            padding: 20px 24px 16px;
            background: #f8f9fa;
        }

        .progress-text {
            text-align: center;
            margin-bottom: 12px;
            font-weight: 600;
            color: #495057;
        }

        .progress-bar {
            width: 100%;
            height: 8px;
            background: #e9ecef;
            border-radius: 4px;
            overflow: hidden;
        }

        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, #667eea, #764ba2);
            border-radius: 4px;
            transition: width 0.3s ease;
        }

        /* Алерт */
        .rating-alert {
            margin: 16px 24px;
            padding: 12px 16px;
            background: #e3f2fd;
            border: 1px solid #bbdefb;
            border-radius: 8px;
            color: #1565c0;
            font-size: 14px;
            text-align: center;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .rating-alert.warning {
            background: #fff8e1;
            border-color: #ffcc02;
            color: #f57c00;
        }

        .rating-alert.error {
            background: #ffebee;
            border-color: #ffcdd2;
            color: #c62828;
            animation: shake 0.5s ease-in-out;
        }

        .rating-alert.success {
            background: #e8f5e8;
            border-color: #c3e6c3;
            color: #2e7d32;
        }

        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-5px); }
            75% { transform: translateX(5px); }
        }

        /* Анимация мигания для алерта */
        @keyframes rating-alert-flash {
            0% { transform: scale(1); background-color: inherit; }
            50% { transform: scale(1.02); background-color: #ffeaa7; }
            100% { transform: scale(1); background-color: inherit; }
        }

        /* Информация о пользователе */
        .rating-user-info {
            display: flex;
            align-items: center;
            padding: 20px 24px;
            gap: 16px;
            background: #f8f9fa;
            margin: 0 24px;
            border-radius: 12px;
            margin-bottom: 20px;
        }

        .rating-avatar-container {
            position: relative;
        }

        .rating-avatar {
            width: 64px;
            height: 64px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid #fff;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .rating-user-status {
            position: absolute;
            bottom: 2px;
            right: 2px;
            width: 16px;
            height: 16px;
            border-radius: 50%;
            border: 2px solid #fff;
        }

        .rating-user-status.online {
            background: #28a745;
        }

        .rating-user-status.offline {
            background: #6c757d;
        }

        .rating-user-details {
            flex: 1;
        }

        .rating-name {
            font-size: 1.2rem;
            font-weight: 600;
            color: #333;
            margin-bottom: 4px;
        }

        .rating-role {
            color: #6c757d;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        /* Инструкция */
        .rating-instruction {
            text-align: center;
            margin: 0 24px 24px;
            color: #495057;
            font-size: 16px;
        }

        /* Звезды */
        .rating-stars {
            display: flex;
            justify-content: center;
            gap: 8px;
            margin: 0 24px 24px;
        }

        .rating-stars .star {
            font-size: 32px;
            color: #e9ecef;
            cursor: pointer;
            transition: all 0.3s ease;
            padding: 8px;
            border-radius: 50%;
            position: relative;
            user-select: none;
        }

        .rating-stars .star:hover {
            color: #ffc107;
            transform: scale(1.15);
            background: rgba(255, 193, 7, 0.1);
            box-shadow: 0 0 20px rgba(255, 193, 7, 0.3);
        }

        .rating-stars .star.active {
            color: #ffc107;
            transform: scale(1.1);
            text-shadow: 0 0 10px rgba(255, 193, 7, 0.5);
        }

        .rating-stars .star::after {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) scale(0);
            width: 20px;
            height: 20px;
            background: radial-gradient(circle, rgba(255, 193, 7, 0.4) 0%, transparent 70%);
            border-radius: 50%;
            transition: transform 0.3s ease;
            pointer-events: none;
        }

        .rating-stars .star:hover::after,
        .rating-stars .star.active::after {
            transform: translate(-50%, -50%) scale(2);
        }

        /* Анимация при клике на звезду */
        .rating-stars .star:active {
            transform: scale(0.95);
        }

        /* Комментарий */
        .rating-comment {
            margin: 0 24px 24px;
        }

        .rating-comment label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #495057;
            font-size: 14px;
        }

        .rating-comment textarea {
            width: 100%;
            min-height: 80px;
            padding: 12px;
            border: 2px solid #e9ecef;
            border-radius: 8px;
            font-family: inherit;
            font-size: 14px;
            resize: vertical;
            transition: border-color 0.2s ease;
        }

        .rating-comment textarea:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .character-count {
            text-align: right;
            font-size: 12px;
            color: #6c757d;
            margin-top: 4px;
        }

        /* Действия */
        .rating-modal-actions {
            padding: 20px 24px;
            display: flex;
            gap: 12px;
            justify-content: center;
            border-top: 1px solid #e9ecef;
            background: #f8f9fa;
            border-radius: 0 0 16px 16px;
        }

        .rating-modal-actions .btn {
            padding: 12px 20px;
            border-radius: 8px;
            font-weight: 600;
            font-size: 14px;
            border: 2px solid transparent;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .rating-modal-actions .btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }

        .rating-modal-actions .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-color: transparent;
        }

        .rating-modal-actions .btn-primary:not(:disabled):hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
        }

        .rating-modal-actions .btn-outline-secondary {
            background: transparent;
            color: #6c757d;
            border-color: #6c757d;
        }

        .rating-modal-actions .btn-outline-secondary:hover {
            background: #6c757d;
            color: white;
        }

        .rating-modal-actions .btn-outline-danger {
            background: transparent;
            color: #dc3545;
            border-color: #dc3545;
        }

        .rating-modal-actions .btn-outline-danger:hover {
            background: #dc3545;
            color: white;
        }

        /* Блокировка страницы */
        .rating-in-progress {
            overflow: hidden !important;
        }

        /* Адаптивность */
        @media (max-width: 576px) {
            .rating-modal-content {
                width: 95%;
                margin: 20px;
            }

            .rating-modal-actions {
                flex-direction: column;
            }

            .rating-modal-actions .btn {
                width: 100%;
                justify-content: center;
            }

            .rating-stars .star {
                font-size: 28px;
                padding: 6px;
            }

            .rating-user-info {
                flex-direction: column;
                text-align: center;
            }
        }

        /* Анимации */
        @keyframes rating-alert-flash {
            0% { transform: scale(1); }
            50% { transform: scale(1.03); background-color: #ffeeba; }
            100% { transform: scale(1); }
        }

        .flash-alert {
            animation: rating-alert-flash 0.5s ease-in-out;
        }
    </style>
</head>
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
<body>

    <div id="loading-screen">
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
    <div id="messages"></div>

    <main>

        @yield('content')
        @include('layouts/mobponel')
    </main>



    <!-- Дополнительные скрипты в конце страницы -->
    @stack('scripts')


    <!-- Убедимся, что Bootstrap JS подключен -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const toggleButton = document.getElementById('toggle-panel');
            const panel = document.querySelector('.main__ponel');

            // Проверяем наличие элементов перед работой с ними
            if (toggleButton && panel) {
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
            } else {
                console.error('Toggle panel elements not found: toggleButton =', !!toggleButton, 'panel =', !!
                panel);
            }
        });
    </script>

    <!-- Упрощенное модальное окно для оценки специалистов -->
    <div id="rating-modal" class="rating-modal" style="display:none;">
        <div class="rating-modal-backdrop" id="rating-modal-backdrop"></div>
        <div class="rating-modal-content">
            <div class="rating-modal-header">
                <h3>Оценка работы по проекту</h3>
                <button type="button" class="rating-modal-close" id="rating-modal-close" title="Закрыть">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <!-- Информация о сделке -->
            <div class="deal-info-block">
                <div class="deal-info-row">
                    <span class="info-label">Проект:</span>
                    <span id="deal-project-number" class="info-value">№ не указан</span>
                </div>
                <div class="deal-info-row">
                    <span class="info-label">Клиент:</span>
                    <span id="deal-client-info" class="info-value">не указан</span>
                </div>
                <div class="deal-info-row">
                    <span class="info-label">Телефон:</span>
                    <span id="deal-client-phone" class="info-value">не указан</span>
                </div>
            </div>

            <!-- Прогресс оценки -->
            <div class="rating-progress-simple">
                <span id="current-rating-index">1</span> из <span id="total-ratings">1</span> специалистов
            </div>

            <!-- Информация о специалисте (упрощенная) -->
            <div class="specialist-block">
                <div class="specialist-avatar">
                    <img id="rating-user-avatar" src="/storage/icon/profile.svg" alt="Аватар">
                </div>
                <div class="specialist-info">
                    <div class="specialist-name" id="rating-user-name">Имя специалиста</div>
                    <div class="specialist-role" id="rating-user-role">Должность</div>
                </div>
            </div>

            <!-- Звезды для оценки -->
            <div class="rating-section">
                <div class="rating-label">Ваша оценка:</div>
                <div class="rating-stars">
                    <span class="star" data-value="1" title="1 звезда">
                        <i class="fas fa-star"></i>
                    </span>
                    <span class="star" data-value="2" title="2 звезды">
                        <i class="fas fa-star"></i>
                    </span>
                    <span class="star" data-value="3" title="3 звезды">
                        <i class="fas fa-star"></i>
                    </span>
                    <span class="star" data-value="4" title="4 звезды">
                        <i class="fas fa-star"></i>
                    </span>
                    <span class="star" data-value="5" title="5 звезд">
                        <i class="fas fa-star"></i>
                    </span>
                </div>
            </div>

            <!-- Комментарий (упрощенный) -->
            <div class="comment-section">
                <textarea id="rating-comment" placeholder="Комментарий (необязательно)" maxlength="300"></textarea>
                <div class="char-counter">
                    <span id="comment-char-count">0</span>/300
                </div>
            </div>

            <!-- Действия -->
            <div class="rating-actions">
                <button id="submit-rating" class="btn btn-primary" disabled>
                    <i class="fas fa-check"></i> Оценить
                </button>
                <button id="skip-rating" class="btn btn-secondary">
                    <i class="fas fa-arrow-right"></i> Пропустить
                </button>
                <button id="close-all-ratings" class="btn btn-outline-danger">
                    <i class="fas fa-times"></i> Закрыть все
                </button>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Инициализация всплывающих подсказок Bootstrap с задержкой в 2 секунды
            if (typeof $().tooltip === 'function') {
                $('[title]').tooltip({
                    placement: 'auto',
                    trigger: 'hover',
                    delay: {show: 1000, hide: 100}, // Задержка в 1 секунду
                    template: '<div class="tooltip custom-tooltip" role="tooltip"><div class="tooltip-arrow"></div><div class="tooltip-inner"></div></div>'
                });

                // Повторная инициализация подсказок после загрузки динамического контента
                $(document).ajaxComplete(function() {
                    setTimeout(function() {
                        $('[title]').tooltip({
                            placement: 'auto',
                            trigger: 'hover',
                            delay: {show: 1000, hide: 100}, // Такая же задержка для динамически загружаемого контента
                            template: '<div class="tooltip custom-tooltip" role="tooltip"><div class="tooltip-arrow"></div><div class="tooltip-inner"></div></div>'
                        });
                    }, 500);
                });
            }
        });
    </script>

    <!-- Улучшение скрипта для всплывающих подсказок с поддержкой HTML-контента -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Инициализация всплывающих подсказок Bootstrap с задержкой в 1 секунду
            if (typeof $().tooltip === 'function') {
                $('[title]').tooltip({
                    placement: 'auto',
                    trigger: 'hover',
                    delay: {show: 800, hide: 100},
                    html: true,
                    template: '<div class="tooltip custom-tooltip" role="tooltip"><div class="tooltip-arrow"></div><div class="tooltip-inner"></div></div>'
                });

                // Специальная инициализация для наград
                $('.award-icon').tooltip({
                    placement: 'auto',
                    delay: {show: 500, hide: 100},
                    html: true,
                    template: '<div class="tooltip custom-tooltip" role="tooltip"><div class="tooltip-arrow"></div><div class="tooltip-inner"></div></div>'
                });

                // Повторная инициализация подсказок после загрузки динамического контента
                $(document).ajaxComplete(function() {
                    setTimeout(function() {
                        $('[title]').tooltip({
                            placement: 'auto',
                            trigger: 'hover',
                            delay: {show: 800, hide: 100},
                            html: true,
                            template: '<div class="tooltip custom-tooltip" role="tooltip"><div class="tooltip-arrow"></div><div class="tooltip-inner"></div></div>'
                        });

                        $('.award-icon').tooltip({
                            placement: 'auto',
                            delay: {show: 500, hide: 100},
                            html: true,
                            template: '<div class="tooltip custom-tooltip" role="tooltip"><div class="tooltip-arrow"></div><div class="tooltip-inner"></div></div>'
                        });
                    }, 500);
                });
            }
        });
    </script>

<style>

        @media only screen and (max-width:780px) {
            .flex-h1 {
                    flex-wrap: wrap;
            }
             .flex-h1  button {
                   width: 100%;
            }
        }

</style>
<!-- Скрипт для проверки и загрузки Select2 при необходимости -->
<script src="{{ asset('/js/select2-checker.js') }}"></script>

<!-- CSRF защита -->
<script src="{{ asset('/js/csrf-protection.js') }}"></script>

</body>
</html>
