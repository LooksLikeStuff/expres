# Система загрузки больших файлов на Яндекс.Диск v3.0

## 🚀 Описание

Полностью переписанная с нуля система загрузки файлов на Яндекс.Диск с поддержкой файлов до **2GB** без таймаутов. Обеспечивает надежную загрузку, визуальный прогресс и автоматические повторные попытки.

## 📋 Возможности

### ✅ Основные функции
- **Файлы до 2GB**: Поддержка очень больших файлов без ограничений
- **Без таймаутов**: Неограниченное время загрузки
- **Визуальный прогресс**: Отображение прогресса, скорости и оставшегося времени
- **Автоматические повторы**: До 3 попыток при ошибках
- **Потоковая передача**: Оптимизированная передача данных
- **Отмена загрузки**: Возможность прервать процесс
- **Drag & Drop**: Поддержка перетаскивания файлов
- **Мобильная адаптивность**: Работает на всех устройствах

### ✅ Технические особенности
- **Chunked upload**: Разбиение на чанки по 2MB для прогресса
- **HTTP Keep-Alive**: Оптимизированные соединения
- **AJAX интеграция**: Бесшовная работа с формами
- **Real-time updates**: Обновление статусов в реальном времени
- **Error handling**: Продвинутая обработка ошибок
- **Memory optimization**: Оптимизация использования памяти

## 🛠️ Установка и настройка

### 1. Подключение компонентов

Добавьте в ваш основной шаблон или в `dealModal.blade.php`:

```blade
{{-- Подключение системы загрузки файлов v3.0 --}}
@include('deals.partials.components.yandex_disk_uploader_v3')
```

### 2. Настройка конфигурации

В файле `config/services.php` уже настроена конфигурация:

```php
'yandex_disk' => [
    'token' => env('YANDEX_DISK_TOKEN'),
    'timeout' => 0, // Без ограничений времени
    'chunk_size' => 2097152, // 2MB chunks
    'max_retries' => 3,
    // ... другие настройки
],
```

### 3. Переменные окружения

Добавьте в `.env`:

```env
YANDEX_DISK_TOKEN=your_token_here
```

### 4. Middleware (опционально)

Для дополнительной оптимизации добавьте middleware в `app/Http/Kernel.php`:

```php
protected $routeMiddleware = [
    // ...
    'large.file' => \App\Http\Middleware\LargeFileUploadMiddleware::class,
];
```

## 📱 Использование

### Простое использование

Замените обычные поля файлов на новый компонент:

```blade
{{-- Старый способ --}}
<input type="file" name="final_project_file" class="form-control">

{{-- Новый способ --}}
@include('deals.partials.components.field_types.file_v3', [
    'field' => [
        'name' => 'final_project_file',
        'label' => 'Финал проекта'
    ]
])
```

### Поддерживаемые поля

```php
$supportedFields = [
    'measurements_file',      // Замеры
    'final_project_file',     // Финал проекта
    'work_act',              // Акт выполненных работ
    'chat_screenshot',       // Скриншот чата
    'archicad_file',         // Файл Archicad
    'execution_order_file',   // Приказ к исполнению
    'final_floorplan',       // Итоговый план
    'final_collage',         // Итоговый коллаж
    'contract_attachment',   // Приложение к договору
    'screenshot_work_1',     // Скриншот работы 1
    'screenshot_work_2',     // Скриншот работы 2
    'screenshot_work_3',     // Скриншот работы 3
    'screenshot_work_4',     // Скриншот работы 4
    'screenshot_work_5',     // Скриншот работы 5
];
```

## 🔌 API Endpoints

### Загрузка файла
```
POST /api/yandex-disk/upload
```

**Параметры:**
- `file` (required) - файл для загрузки
- `deal_id` (required) - ID сделки
- `field_name` (required) - имя поля

**Ответ:**
```json
{
    "success": true,
    "data": {
        "yandex_disk_url": "https://...",
        "original_name": "document.pdf",
        "file_size": 1024000,
        "upload_time": 15.32,
        "upload_speed": "2.1 MB/s"
    }
}
```

### Удаление файла
```
POST /api/yandex-disk/delete
```

**Параметры:**
- `deal_id` (required) - ID сделки
- `field_name` (required) - имя поля

### Информация о файле
```
GET /api/yandex-disk/info?deal_id=123&field_name=final_project_file
```

### Проверка состояния
```
GET /api/yandex-disk/health
```

## 🎯 JavaScript API

### Основные методы

```javascript
// Создание экземпляра
const uploader = new YandexDiskUploaderV3();

// Программная загрузка файла
uploader.uploadFile(file, dealId, fieldName);

// Отмена загрузки
uploader.cancelUpload(fieldName);

// Удаление файла
uploader.confirmAndDeleteFile(dealId, fieldName);

// Проверка статуса файла
uploader.updateFileStatus(fieldName);

// Проверка здоровья сервиса
uploader.checkServiceHealth();
```

### События

```javascript
// Обработка завершения загрузки
$(document).on('fileUploaded', function(event, data) {
    console.log('Файл загружен:', data);
});

// Обработка ошибки загрузки
$(document).on('fileUploadError', function(event, error) {
    console.error('Ошибка загрузки:', error);
});
```

## 🔧 Настройки

### JavaScript настройки

```javascript
const uploader = new YandexDiskUploaderV3();

// Изменение настроек
uploader.settings.maxFileSize = 4 * 1024 * 1024 * 1024; // 4GB
uploader.settings.maxRetries = 5;
uploader.settings.retryDelay = 3000;
```

### CSS кастомизация

```css
/* Изменение цветов прогресс-бара */
.progress-bar {
    background: linear-gradient(90deg, #28a745 0%, #20c997 100%);
}

/* Кастомизация уведомлений */
.notification-container {
    top: 100px;
    left: 20px;
}
```

## 🐛 Отладка

### Включение режима отладки

В `.env`:
```env
APP_DEBUG=true
```

### Проверка логов

```bash
tail -f storage/logs/laravel.log | grep "YandexDisk"
```

### JavaScript отладка

```javascript
// Включение отладки
window.YandexDiskDebug.enabled = true;

// Проверка состояния
console.log(window.yandexDiskUploaderV3);
```

## 🚨 Устранение неполадок

### Частые проблемы

#### 1. Файл не загружается
- Проверьте токен Яндекс.Диска в `.env`
- Убедитесь, что размер файла не превышает 2GB
- Проверьте логи в `storage/logs/laravel.log`

#### 2. Таймаут загрузки
- Убедитесь, что `timeout` установлен в `0`
- Проверьте настройки веб-сервера (`client_max_body_size`)
- Увеличьте `max_execution_time` в PHP

#### 3. Ошибки JavaScript
- Убедитесь, что jQuery загружен
- Проверьте CSRF токен в мета-тегах
- Откройте консоль браузера для подробностей

#### 4. Проблемы с памятью
- Увеличьте `memory_limit` в PHP
- Используйте middleware `LargeFileUploadMiddleware`

### Диагностические команды

```bash
# Проверка конфигурации PHP
php -ini | grep -E "(upload_max_filesize|post_max_size|memory_limit|max_execution_time)"

# Проверка доступности API
curl -X GET http://your-domain/api/yandex-disk/health

# Тест загрузки
curl -X POST -F "file=@test.txt" -F "deal_id=1" -F "field_name=final_project_file" \
     http://your-domain/api/yandex-disk/upload
```

## 📊 Мониторинг

### Логирование

Система записывает подробные логи всех операций:

```
[2024-08-04 10:30:15] 🚀 Начало загрузки большого файла на Яндекс.Диск
[2024-08-04 10:30:16] 📤 Попытка 1 загрузки файла document.pdf размером 500 MB
[2024-08-04 10:32:45] ✅ Файл успешно загружен на Яндекс.Диск
```

### Метрики производительности

```javascript
// Получение статистики загрузок
const stats = window.yandexDiskUploaderV3.getUploadStats();
console.log('Статистика:', stats);
```

## 🔒 Безопасность

### Проверки безопасности
- CSRF защита для всех запросов
- Валидация типов и размеров файлов
- Проверка прав доступа к сделкам
- Безопасное хранение токенов

### Рекомендации
- Регулярно обновляйте токены Яндекс.Диска
- Используйте HTTPS для всех запросов
- Ограничьте права доступа к API

## 📈 Производительность

### Оптимизации
- Потоковая передача данных
- HTTP Keep-Alive соединения
- Chunked transfer encoding
- Асинхронная обработка

### Рекомендуемые настройки сервера

**Nginx:**
```nginx
client_max_body_size 2G;
client_body_timeout 300;
proxy_read_timeout 300;
```

**PHP:**
```ini
upload_max_filesize = 2G
post_max_size = 2G
memory_limit = 1G
max_execution_time = 0
max_input_time = 0
```

## 🔄 Миграция со старой системы

### Автоматическое обновление

1. Подключите новую систему
2. Старые поля автоматически будут обновлены
3. Существующие ссылки на файлы сохранятся

### Ручное обновление

```blade
{{-- Найдите старые поля --}}
<input type="file" name="final_project_file" class="form-control">

{{-- Замените на новые --}}
@include('deals.partials.components.field_types.file_v3', [
    'field' => ['name' => 'final_project_file']
])
```

## 📝 Changelog

### v3.0 (Август 2024)
- ✅ Полная переработка с нуля
- ✅ Поддержка файлов до 2GB
- ✅ Потоковая передача данных
- ✅ Визуальный прогресс загрузки
- ✅ Автоматические повторные попытки
- ✅ Мобильная адаптивность
- ✅ Улучшенная обработка ошибок

## 🤝 Поддержка

При возникновении проблем:

1. Проверьте логи в `storage/logs/laravel.log`
2. Откройте консоль браузера для JavaScript ошибок
3. Убедитесь в правильности конфигурации
4. Проверьте состояние сервиса через `/api/yandex-disk/health`

---

**Система готова к использованию!** 🎉

Загружайте файлы любого размера на Яндекс.Диск быстро и надежно!
