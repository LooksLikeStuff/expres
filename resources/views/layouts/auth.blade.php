<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title_site }}</title>
    @vite([ 'resources/css/style.css', 'resources/css/font.css', 'resources/css/element.css', 'resources/css/animation.css', 'resources/css/mobile.css', 'resources/js/bootstrap.js',  'resources/js/success.js', 'resources/js/mask.js', 'resources/js/login.js'])</head>
    <link rel="stylesheet" href="resources/css/animate.css">
    <script src="resources/js/wow.js"></script>
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

     
    <script>
        // Регистрация сервис-воркера для PWA
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('/sw.js')
                    .then(registration => {
                        console.log('ServiceWorker зарегистрирован:', registration.scope);
                        
                        // Передаем CSRF токен в сервис-воркер
                        if (registration.active) {
                            registration.active.postMessage({
                                type: 'SET_CSRF_TOKEN',
                                token: document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                            });
                        }

                        // Запрос разрешения на push-уведомления
                        requestNotificationPermission(registration);
                    })
                    .catch(error => {
                        console.error('Ошибка регистрации ServiceWorker:', error);
                    });
            });
        }

        // Запрос разрешения на уведомления и подписка на push
        function requestNotificationPermission(registration) {
            if ('Notification' in window) {
                Notification.requestPermission().then(permission => {
                    if (permission === 'granted') {
                        console.log('Разрешение на уведомления получено');
                        
                        // Подписываемся на push-уведомления
                        if (registration && registration.pushManager) {
                            registration.pushManager.subscribe({
                                userVisibleOnly: true,
                                applicationServerKey: urlBase64ToUint8Array(
                                    // Замените на ваш VAPID public key
                                    'BEl62iUYgUivxIkv69yViEuiBIa-Ib9-SkvMeAtA3LFgDzkrxZJjSgSnfckjBJuBkr3qBUYIHBQFLXYp5Nksh8U'
                                )
                            }).then(subscription => {
                                // Отправляем подписку на сервер
                                sendSubscriptionToServer(subscription);
                            }).catch(err => console.error('Ошибка подписки:', err));
                        }
                    }
                });
            }
        }

        // Отправка подписки на сервер
        function sendSubscriptionToServer(subscription) {
            fetch('/api/push-subscriptions', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify(subscription)
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Не удалось отправить подписку на сервер');
                }
                console.log('Подписка успешно отправлена на сервер');
            })
            .catch(error => {
                console.error('Ошибка отправки подписки:', error);
            });
        }

        // Преобразование base64-строки в массив Uint8Array для applicationServerKey
        function urlBase64ToUint8Array(base64String) {
            const padding = '='.repeat((4 - base64String.length % 4) % 4);
            const base64 = (base64String + padding)
                .replace(/\-/g, '+')
                .replace(/_/g, '/');

            const rawData = window.atob(base64);
            const outputArray = new Uint8Array(rawData.length);

            for (let i = 0; i < rawData.length; ++i) {
                outputArray[i] = rawData.charCodeAt(i);
            }
            
            return outputArray;
        }
    </script>

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
<body class="auth__fon">
    <div id="loading-screen" class="wow fadeInleft" data-wow-duration="1s" data-wow-delay="1s"">
        <img src="/storage/icon/fool_logo.svg" alt="Loading">
    </div>
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
   
 
        @yield('content')
        
   
</body>
<script>
    document.addEventListener('DOMContentLoaded', function () {
    const successMessage = document.getElementById('success-message');
    const errorMessage = document.getElementById('error-message');
    // Анимация появления сообщения об успехе
    if (successMessage) {
        successMessage.classList.add('show');
        setTimeout(() => {
            successMessage.classList.remove('show');
        }, 5000);
        successMessage.addEventListener('click', () => {
            successMessage.classList.remove('show');
        });
    }
    // Анимация появления сообщения об ошибке
    if (errorMessage) {
        errorMessage.classList.add('show');
        setTimeout(() => {
            errorMessage.classList.remove('show');
        }, 5000);
        errorMessage.addEventListener('click', () => {
            errorMessage.classList.remove('show');
        });
    }
});

document.addEventListener("DOMContentLoaded", function () {
    var inputs = document.querySelectorAll("input.maskphone");
    for (var i = 0; i < inputs.length; i++) {
        var input = inputs[i];
        input.addEventListener("input", mask);
        input.addEventListener("focus", mask);
        input.addEventListener("blur", mask);
    }
    function mask(event) {
        var blank = "+_ (___) ___-__-__";
        var i = 0;
        var val = this.value.replace(/\D/g, "").replace(/^8/, "7").replace(/^9/, "79");
        this.value = blank.replace(/./g, function (char) {
            if (/[_\d]/.test(char) && i < val.length) return val.charAt(i++);
            return i >= val.length ? "" : char;
        });
        if (event.type == "blur") {
            if (this.value.length == 2) this.value = "";
        } else {
            setCursorPosition(this, this.value.length);
        }
    }
    function setCursorPosition(elem, pos) {
        elem.focus();
        if (elem.setSelectionRange) {
            elem.setSelectionRange(pos, pos);
            return;
        }
        if (elem.createTextRange) {
            var range = elem.createTextRange();
            range.collapse(true);
            range.moveEnd("character", pos);
            range.moveStart("character", pos);
            range.select();
            return;
        }
    }
});
document.addEventListener('DOMContentLoaded', function () {
    const nameInputs = document.querySelectorAll('input[name="name"]');
    nameInputs.forEach(input => {
        input.addEventListener('input', function () {
            // Удаляем все символы, которые не являются русскими буквами
            this.value = this.value.replace(/[^А-Яа-яЁё\s\-]/g, '');
        });
    });
});
window.addEventListener('load', () => {
    const loadingScreen = document.getElementById('loading-screen');
    const content = document.getElementById('content');
    setTimeout(() => {
        loadingScreen.classList.add('hidden'); // Применяем класс для анимации исчезновения
        document.body.style.overflow = 'auto'; // Включаем прокрутку
        setTimeout(() => {
            loadingScreen.style.display = 'none'; // Полностью убираем загрузку после анимации
            content.style.opacity = '1'; // Плавно показываем содержимое (контент уже анимируется в CSS)
        }, 1000); // Длительность анимации исчезновения (совпадает с fadeOut)
    }, 1000); // Задержка до начала исчезновения
});
document.addEventListener("DOMContentLoaded", () => {
    // Находим все textarea на странице
    const textareas = document.querySelectorAll("textarea");
    // Применяем обработчик ко всем textarea
    textareas.forEach((textarea) => {
        textarea.addEventListener("input", (event) => {
            // Разрешенные символы: английские, русские буквы, цифры, пробелы, запятые, точки, тире и символ рубля (₽)
            const allowedChars = /^[a-zA-Zа-яА-ЯёЁ0-9\s,.\-₽]*$/;
            const value = event.target.value;
            // Если введены запрещенные символы, удаляем их
            if (!allowedChars.test(value)) {
                event.target.value = value.replace(/[^a-zA-Zа-яА-ЯёЁ0-9\s,.\-₽]/g, "");
            }
        });
    });
});

</script>
</html>
