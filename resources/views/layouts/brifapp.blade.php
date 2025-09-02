<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title')</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css">
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
      <script src="{{ asset('/js/p/jquery-3.6.0.min.js') }}"></script>
   <script src="{{ asset('/js/p/jquery.dataTables.min.js') }}"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/simplePagination.js/1.6/jquery.simplePagination.min.js"></script>
    @vite(['resources/css/style.css', 'resources/css/font.css', 'resources/css/element.css', 'resources/js/bootstrap.js', 'resources/css/animation.css', 'resources/css/mobile.css', 'resources/js/modal.js', 'resources/js/success.js', 'resources/js/mask.js', 'resources/js/login.js'])

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

    <!-- Глобальный скрипт для валидации и прокрутки к ошибкам -->
    <script>
        // Глобальная функция для прокрутки к элементу
        function scrollToElement(element) {
            if (!element) return;

            // Получаем позицию элемента относительно документа
            const rect = element.getBoundingClientRect();
            const scrollTop = window.pageYOffset || document.documentElement.scrollTop;

            // Вычисляем абсолютную позицию элемента
            const absoluteTop = rect.top + scrollTop;

            // Прокручиваем с учетом отступа (для отображения заголовка формы)
            window.scrollTo({
                top: absoluteTop - 120, // 120px - примерная высота шапки
                behavior: 'smooth'
            });

            // Добавляем фокус на элемент после прокрутки
            setTimeout(() => {
                element.focus();
                // Добавляем подсвечивание
                element.classList.add('highlight-field');
                // Убираем подсвечивание через 2 секунды
                setTimeout(() => {
                    element.classList.remove('highlight-field');
                }, 2000);
            }, 500);
        }
    </script>

    <!-- Глобальные стили для подсветки ошибок -->
    <style>
        .highlight-field {
            animation: highlightPulse 1s ease-in-out;
            box-shadow: 0 0 10px 2px rgba(255, 0, 0, 0.5);
        }

        @keyframes highlightPulse {
            0% {
                box-shadow: 0 0 5px 1px rgba(255, 0, 0, 0.5);
            }

            50% {
                box-shadow: 0 0 15px 4px rgba(255, 0, 0, 0.8);
            }

            100% {
                box-shadow: 0 0 5px 1px rgba(255, 0, 0, 0.5);
            }
        }

        .field-error {
            border: 2px solid #ff0000 !important;
            background-color: #fff0f0 !important;
        }
  @media only screen and (max-width:780px) {
            .flex-h1 {
                    flex-wrap: wrap;    gap: 10px;
            }
             .flex-h1 .button-group   {
                display: flex;  flex-wrap: wrap;
                   width: 100%;
            }
        }
        .error-placeholder::placeholder {
            color: #ff0000 !important;
            opacity: 1;
        }
    </style>


<body>

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
    <div id="loading-screen">
        <img src="/storage/icon/fool_logo.svg" alt="Loading">
    </div>

    <main class="py-4">
        @yield('content')
    </main>
    </div>
    {{-- <div class="question_class-button">
        <a href="#top-title" class="top-title"><img src="/storage/icon/send.svg" alt=""></a>
    </div> --}}
{{--    <script>--}}
{{--        const unreadCounter = document.getElementById('unread-count');--}}

{{--        async function fetchUnreadMessagesCount() {--}}
{{--            try {--}}
{{--                const response = await fetch('/messages/unread-total');--}}
{{--                if (response.ok) {--}}
{{--                    const data = await response.json();--}}
{{--                    unreadCounter.textContent = data.total_unread_count || 0;--}}
{{--                } else {--}}
{{--                    console.error('Ошибка получения данных о непрочитанных сообщениях');--}}
{{--                }--}}
{{--            } catch (error) {--}}
{{--                console.error('Ошибка запроса:', error);--}}
{{--            }--}}
{{--        }--}}

{{--        // Обновляем данные каждые 10 секунд--}}
{{--        setInterval(fetchUnreadMessagesCount, 2000);--}}

{{--        // Первый вызов для немедленного обновления--}}
{{--        fetchUnreadMessagesCount();--}}
{{--    </script>--}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
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

        document.addEventListener("DOMContentLoaded", function() {
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
                this.value = blank.replace(/./g, function(char) {
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
        document.addEventListener('DOMContentLoaded', function() {
            const nameInputs = document.querySelectorAll('input[name="name"]');
            nameInputs.forEach(input => {
                input.addEventListener('input', function() {
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
                    loadingScreen.style.display =
                        'none'; // Полностью убираем загрузку после анимации
                    content.style.opacity =
                        '1'; // Плавно показываем содержимое (контент уже анимируется в CSS)
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

    <script>
        // Функция для сохранения состояния полей и чекбоксов
        function saveFormState() {
            const currentURL = window.location.href; // Получаем текущий URL страницы
            const formState = {};

            // Проходим по всем полям формы и чекбоксам на странице
            document.querySelectorAll('input, select, textarea').forEach(el => {
                // Пропускаем элементы с пустыми значениями или с неактивными полями
                if (!el.name || el.disabled || el.type === 'submit') return;

                // Сохраняем значения полей в объект
                if (el.type === 'checkbox' || el.type === 'radio') {
                    formState[el.name] = el.checked; // Для чекбоксов и радио кнопок сохраняем состояние
                } else {
                    formState[el.name] = el.value; // Для других элементов сохраняем значение
                }
            });

            // Сохраняем состояние в localStorage в зависимости от URL
            localStorage.setItem(currentURL, JSON.stringify(formState));
        }

        // Функция для загрузки состояния формы
        function loadFormState() {
            const currentURL = window.location.href; // Получаем текущий URL страницы
            const formState = localStorage.getItem(currentURL); // Получаем сохранённые данные для текущего URL

            if (formState) {
                const state = JSON.parse(formState);

                // Проходим по всем полям и восстанавливаем их состояние
                document.querySelectorAll('input, select, textarea').forEach(el => {
                    if (!el.name || el.disabled || el.type === 'submit') return;

                    if (el.type === 'checkbox' || el.type === 'radio') {
                        el.checked = state[el.name] || false; // Восстанавливаем для чекбоксов и радио кнопок
                    } else {
                        el.value = state[el.name] || ''; // Восстанавливаем для других элементов
                    }
                });
            }
        }

        // Слушаем события для сохранения состояния при изменении полей
        document.addEventListener('input', saveFormState);
        document.addEventListener('change', saveFormState);

        // // Загружаем сохранённое состояние при загрузке страницы
        // window.addEventListener('load', loadFormState);
    </script>
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
    <script>
        function refreshCsrfToken() {
            {{--return fetch('{{ route('refresh-csrf') }}')--}}
            {{--    .then(response => {--}}
            {{--        if (!response.ok) {--}}
            {{--            throw new Error('Ошибка обновления CSRF токена');--}}
            {{--        }--}}
            {{--        return response.json();--}}
            {{--    })--}}
            {{--    .then(data => {--}}
            {{--        document.querySelector('meta[name="csrf-token"]').setAttribute('content', data.token);--}}
            {{--        document.querySelectorAll('input[name="_token"]').forEach(input => input.value = data.token);--}}
            {{--        console.log('CSRF токен успешно обновлен: ' + new Date().toLocaleTimeString());--}}
            {{--        return data.token;--}}
            {{--    })--}}
            {{--    .catch(error => {--}}
            {{--        console.error('Ошибка при обновлении CSRF токена:', error);--}}
            {{--    });--}}
        }

        // Обновляем токен каждые 30 секунд
        setInterval(refreshCsrfToken, 30000);

        // Функция для проверки и обновления токена перед отправкой формы
        async function ensureFreshCsrfToken() {
            try {
                await refreshCsrfToken();
                return true;
            } catch (error) {
                console.error('Не удалось обновить CSRF токен перед отправкой формы:', error);
                return false;
            }
        }

        // Автоматическое обновление страницы при неудачной отправке формы из-за CSRF
        document.addEventListener('DOMContentLoaded', function() {
            const forms = document.querySelectorAll('form');
            forms.forEach(form => {
                form.addEventListener('submit', async function(event) {
                    // Если форма уже обрабатывается - пропускаем
                    if (form.dataset.processing === 'true') return;

                    // Проверяем, есть ли в форме индикатор необходимости проверки CSRF
                    if (form.classList.contains('csrf-check')) {
                        event.preventDefault();
                        form.dataset.processing = 'true';

                        // Обновляем токен перед отправкой
                        const success = await ensureFreshCsrfToken();
                        form.dataset.processing = 'false';

                        if (success) {
                            // Если токен успешно обновлен, отправляем форму
                            form.submit();
                        } else {
                            // Если не удалось обновить токен, перезагружаем страницу
                            alert('Сессия устарела. Страница будет перезагружена для обновления данных.');
                            location.reload();
                        }
                    }
                });
            });
        });
    </script>
    <!-- PWA скрипты -->
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
                                token: document.querySelector('meta[name="csrf-token"]').getAttribute(
                                    'content')
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
</body>

</html>
