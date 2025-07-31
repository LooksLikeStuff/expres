/**
 * Тестовый скрипт для проверки работы поля "Город/часовой пояс"
 * Проверяет, правильно ли устанавливается значение из базы данных
 */

(function() {
    'use strict';
    
    function testCityFieldValue() {
        console.log('=== Тест поля "Город/часовой пояс" ===');
        
        var $cityField = $('.select2-cities-search, #client_timezone');
        
        if ($cityField.length === 0) {
            console.log('❌ Поле города не найдено');
            return;
        }
        
        console.log('✅ Найдено полей города:', $cityField.length);
        
        $cityField.each(function(index) {
            var $field = $(this);
            var fieldId = $field.attr('id') || 'без ID';
            var currentValue = $field.data('current-value') || $field.attr('data-current-value');
            var selectedValue = $field.val();
            var isSelect2 = $field.hasClass('select2-hidden-accessible');
            
            console.log('--- Поле #' + (index + 1) + ' (ID: ' + fieldId + ') ---');
            console.log('  data-current-value:', currentValue);
            console.log('  Текущее значение (val()):', selectedValue);
            console.log('  Select2 инициализирован:', isSelect2 ? 'Да' : 'Нет');
            
            if (isSelect2) {
                try {
                    var select2Data = $field.select2('data');
                    var selectedText = select2Data.length > 0 ? select2Data[0].text : 'не выбрано';
                    console.log('  Select2 выбранный текст:', selectedText);
                    
                    // Проверяем соответствие
                    if (currentValue && currentValue.trim() !== '') {
                        if (selectedValue === currentValue) {
                            console.log('  ✅ Значение установлено корректно');
                        } else {
                            console.log('  ❌ Значение НЕ соответствует ожидаемому');
                            console.log('  ⚠️  Ожидалось:', currentValue);
                            console.log('  ⚠️  Получено:', selectedValue);
                        }
                    } else {
                        console.log('  ℹ️  Текущее значение не задано (корректно)');
                    }
                } catch (e) {
                    console.log('  ❌ Ошибка получения данных Select2:', e.message);
                }
            }
            
            // Проверяем HTML-опции
            var options = $field.find('option');
            var selectedOptions = $field.find('option:selected');
            console.log('  Общее количество опций:', options.length);
            console.log('  Выбранных опций:', selectedOptions.length);
            
            if (selectedOptions.length > 0) {
                selectedOptions.each(function() {
                    console.log('  Выбранная опция:', $(this).val(), '|', $(this).text());
                });
            }
        });
        
        console.log('=== Конец теста ===');
    }
    
    // Функция для принудительной установки значения города
    function forceSetCityValue(cityName) {
        console.log('=== Принудительная установка города: ' + cityName + ' ===');
        
        var $cityField = $('.select2-cities-search, #client_timezone').first();
        
        if ($cityField.length === 0) {
            console.log('❌ Поле города не найдено');
            return;
        }
        
        // Проверяем, есть ли уже такая опция
        var existingOption = $cityField.find('option[value="' + cityName + '"]');
        
        if (existingOption.length === 0) {
            // Создаем новую опцию
            var newOption = new Option(cityName, cityName, true, true);
            $cityField.append(newOption);
            console.log('✅ Создана новая опция для города:', cityName);
        } else {
            // Выбираем существующую опцию
            $cityField.val(cityName);
            console.log('✅ Выбрана существующая опция для города:', cityName);
        }
        
        // Обновляем Select2
        if ($cityField.hasClass('select2-hidden-accessible')) {
            $cityField.trigger('change');
            console.log('✅ Select2 обновлен');
        }
        
        // Проверяем результат
        setTimeout(function() {
            testCityFieldValue();
        }, 100);
    }
    
    // Экспортируем функции
    window.cityFieldTest = {
        test: testCityFieldValue,
        setCity: forceSetCityValue
    };
    
    // Автоматически запускаем тест при загрузке
    $(document).ready(function() {
        setTimeout(testCityFieldValue, 2000);
    });
    
    console.log('Тестовый модуль загружен. Используйте cityFieldTest.test() или cityFieldTest.setCity("Название города")');
    
})();
