<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="user-id" content="{{ Auth::id() }}">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ 'Чаты' }}</title>
    <link rel="stylesheet" href="{{ asset('/css/animate.css') }}">
    <link rel="stylesheet" href="{{ asset('/css/introjs.min.css') }}">


    @vite(['resources/js/app.js'])
    <script src="{{ asset('/js/wow.js') }}"></script>
    <!-- Подключаем стили Intro.js -->


    <script src="{{ asset('/js/intro.min.js') }}"></script>


    <!-- CSS стили (загружаем сначала) -->
    <link rel="stylesheet" href="{{ asset('/css/p/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('/css/p/select2.min.css') }}">

    @yield('stylesheets')

    <!-- JavaScript (основные библиотеки) -->
    <script src="{{ asset('/js/p/jquery-3.6.0.min.js') }}"></script>

    <!-- Исправленный путь к Select2 -->
    <script src="{{ asset('/js/p/select2.min.js') }}"></script>

{{--    pusher--}}
    <script src="https://js.pusher.com/8.4.0/pusher.min.js"></script>

    @vite(['resources/css/font.css','resources/css/animation.css', 'resources/css/style.css', 'resources/css/element.css', 'resources/css/mobile.css'])

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
</head>

<body>

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

<main>
    @yield('content')
    @include('layouts/mobponel')
</main>



<!-- Дополнительные скрипты в конце страницы -->
@stack('scripts')

</body>
</html>
