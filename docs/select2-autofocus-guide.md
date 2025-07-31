# Руководство по автофокусу для Select2

## Что было сделано

Добавлен автоматический фокус на поле поиска при открытии Select2 выпадающих списков во всех компонентах системы.

## Обновленные файлы

### JavaScript файлы:
1. `public/js/deals-compatibility-fixes.js` - основной файл совместимости
2. `resources/views/deals/components/deals-scripts.blade.php` - скрипты для модальных окон
3. `resources/views/deals/components/filter-scripts.blade.php` - скрипты фильтров 
4. `resources/views/deals/components/filters_scripts.blade.php` - дублирующие скрипты фильтров
5. `resources/views/deals/cardinators.blade.php` - страница координаторов
6. `resources/views/deals/create.blade.php` - страница создания сделки

### Новые файлы:
1. `public/css/select2-autofocus.css` - стили для улучшения UX автофокуса
2. `public/js/select2-autofocus.js` - универсальная библиотека для автофокуса

## Как это работает

При открытии любого Select2 выпадающего списка:

1. Выполняется `setTimeout` с задержкой 0-50мс для ожидания полной отрисовки dropdown
2. Находится поле поиска в открытом контейнере: `.select2-container--open .select2-search__field`
3. На найденное поле устанавливается фокус с помощью `.focus()`
4. Для мобильных устройств дополнительно вызывается `.click()` для принудительного открытия клавиатуры

## Код, который был добавлен в каждый файл:

```javascript
// Автоматически ставим фокус на поле поиска при открытии Select2
var searchField = $('.select2-container--open .select2-search__field');
if (searchField.length) {
    searchField.focus();
}
```

## Использование универсальной библиотеки

Для подключения в новых местах добавьте в HTML:

```html
<link rel="stylesheet" href="/css/select2-autofocus.css">
<script src="/js/select2-autofocus.js"></script>
```

Затем можно использовать:

```javascript
// Добавить автофокус ко всем Select2 элементам
Select2AutoFocus.init();

// Или к конкретным селекторам
Select2AutoFocus.init('.my-select2-class');

// Инициализация Select2 с автофокусом
Select2AutoFocus.initWithSelect2('.new-select-elements');
```

## Совместимость

- Работает со всеми версиями Select2 4.x
- Совместимо с Bootstrap модальными окнами
- Поддерживает мобильные устройства (iOS, Android)
- Не конфликтует с существующими обработчиками событий

## Troubleshooting

Если автофокус не работает:

1. Убедитесь, что Select2 полностью инициализирован
2. Проверьте, что нет конфликтующих CSS стилей
3. Увеличьте задержку в setTimeout до 100-200мс
4. Проверьте консоль браузера на наличие ошибок JavaScript
