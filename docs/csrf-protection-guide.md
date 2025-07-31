# CSRF Protection System - Система защиты от CSRF атак

## Описание

Система автоматической защиты от CSRF атак с интеллектуальным обновлением токенов. Решает проблему ошибки 419 "Page Expired" в Laravel приложениях.

## Основные возможности

- ✅ Автоматическое обновление CSRF токенов каждые 5 минут
- ✅ Перехват и автоматический повтор запросов при ошибке 419
- ✅ Обновление токенов перед отправкой форм
- ✅ Поддержка axios и jQuery AJAX
- ✅ Retry логика при ошибках обновления
- ✅ Настраиваемые callback'и для обработки событий

## Установка и настройка

### 1. Подключение файла

Система уже подключена в основных layout файлах:
- `resources/views/layouts/app.blade.php`
- `resources/views/layouts/brifapp.blade.php`

```html
<script src="{{ asset('/js/csrf-protection.js') }}"></script>
```

### 2. Роут для обновления токена

В `routes/web.php` уже настроен роут:

```php
Route::get('/refresh-csrf', function () {
    return response()->json(['token' => csrf_token()]);
})->name('refresh-csrf');
```

### 3. Meta тег в HTML

Убедитесь, что в `<head>` есть meta тег:

```html
<meta name="csrf-token" content="{{ csrf_token() }}">
```

## Использование

### Автоматическая работа

Система работает автоматически после загрузки страницы:

1. **Формы**: Все POST формы автоматически получают обновленные токены перед отправкой
2. **AJAX запросы**: Автоматически перехватываются и повторяются при ошибке 419
3. **Периодическое обновление**: Токен обновляется каждые 5 минут

### Программный доступ

```javascript
// Получить текущий токен
const token = window.CSRFProtection.getCurrentToken();

// Принудительно обновить токен
await window.CSRFProtection.refreshToken();

// Убедиться что токен свежий (обновит если нужно)
await window.CSRFProtection.ensureFreshToken();

// Остановить автоматическое обновление
window.CSRFProtection.stopPeriodicRefresh();

// Запустить автоматическое обновление
window.CSRFProtection.startPeriodicRefresh();
```

### Настройка callback'ов

```javascript
// Callback при обновлении токена
window.CSRFProtection.callbacks.onTokenRefresh = function(newToken) {
    console.log('Токен обновлен:', newToken);
    // Ваша логика
};

// Callback при критических ошибках
window.CSRFProtection.callbacks.onCriticalError = function(message) {
    // Показать красивое уведомление вместо alert
    showNotification(message, 'error');
    setTimeout(() => location.reload(), 3000);
};
```

### Настройка конфигурации

```javascript
// Изменить интервал обновления (в миллисекундах)
window.CSRFProtection.config.refreshIntervalMs = 600000; // 10 минут

// Изменить время принудительного обновления
window.CSRFProtection.config.forceRefreshAfterMs = 1200000; // 20 минут

// Изменить количество попыток при ошибке
window.CSRFProtection.config.retryAttempts = 5;
```

## Интеграция с существующим кодом

### Axios

Система автоматически настраивает axios:

```javascript
// Токен автоматически добавляется в заголовки
axios.post('/api/data', { /* данные */ })
    .then(response => {
        // При ошибке 419 запрос автоматически повторится с новым токеном
    });
```

### jQuery AJAX

Система автоматически настраивает jQuery:

```javascript
// Токен автоматически добавляется в заголовки
$.post('/api/data', { /* данные */ })
    .done(function(response) {
        // При ошибке 419 запрос автоматически повторится с новым токеном
    });
```

### Fetch API

Для fetch нужно добавить токен вручную:

```javascript
fetch('/api/data', {
    method: 'POST',
    headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': window.CSRFProtection.getCurrentToken()
    },
    body: JSON.stringify(data)
})
.then(response => {
    if (response.status === 419) {
        // Обновляем токен и повторяем запрос
        return window.CSRFProtection.refreshToken().then(() => {
            return fetch('/api/data', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': window.CSRFProtection.getCurrentToken()
                },
                body: JSON.stringify(data)
            });
        });
    }
    return response;
});
```

## Отладка и мониторинг

### Логирование

Система выводит информацию в консоль:

```
CSRF токен обновлен: 14:30:25
Axios: получена 419 ошибка, обновляем CSRF токен...
jQuery AJAX: получена 419 ошибка, обновляем CSRF токен...
```

### Проверка состояния

```javascript
// Когда последний раз обновлялся токен
console.log('Последнее обновление:', new Date(window.CSRFProtection.lastRefresh));

// Текущая конфигурация
console.log('Конфигурация:', window.CSRFProtection.config);
```

## Решение проблем

### Ошибка "Сессия истекла"

Если появляется это сообщение, проверьте:

1. Доступность роута `/refresh-csrf`
2. Правильность настройки сессий Laravel
3. Корректность конфигурации домена и cookies

### Токен не обновляется

1. Откройте консоль разработчика
2. Проверьте сетевые запросы к `/refresh-csrf`
3. Убедитесь что JavaScript не блокируется

### Формы не работают

1. Убедитесь что в форме есть `@csrf` directive
2. Проверьте метод формы (должен быть POST)
3. Убедитесь что нет конфликтов с другими обработчиками submit

## Производительность

- Минимальное влияние на производительность
- Intelligent caching - токен обновляется только при необходимости
- Retry логика предотвращает избыточные запросы
- Graceful degradation при ошибках

## Совместимость

- ✅ Все современные браузеры
- ✅ IE11+ (с полифиллами для fetch)
- ✅ Мобильные браузеры
- ✅ Laravel 8+
- ✅ Axios любой версии
- ✅ jQuery 1.7+

## Безопасность

- Токены передаются только по HTTPS в production
- Автоматическая очистка старых токенов
- Защита от race conditions
- Валидация токенов на серверной стороне

## Поддержка

При возникновении проблем:

1. Проверьте консоль браузера на наличие ошибок
2. Убедитесь что роут `/refresh-csrf` доступен
3. Проверьте настройки сессий в Laravel
4. Отключите другие CSRF middleware если они конфликтуют
