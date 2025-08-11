/**
 * Тестовый хелпер для отладки Select2 на странице редактирования сделки
 */

(function() {
    'use strict';
    
    // Запуск отладки через 3 секунды после загрузки
    setTimeout(function() {
        console.log('🔍 ЗАПУСК ТЕСТИРОВАНИЯ SELECT2');
        testSelect2Implementation();
    }, 3000);
    
    function testSelect2Implementation() {
        console.log('=== ТЕСТИРОВАНИЕ SELECT2 ===');
        
        // 1. Проверяем загрузку библиотек
        console.log('1. Проверка библиотек:');
        console.log('   jQuery загружен:', typeof $ !== 'undefined');
        console.log('   Select2 загружен:', typeof $.fn.select2 !== 'undefined');
        
        if (typeof $ === 'undefined') {
            console.error('❌ jQuery не загружен!');
            return;
        }
        
        if (typeof $.fn.select2 === 'undefined') {
            console.error('❌ Select2 не загружен!');
            return;
        }
        
        // 2. Подсчет элементов
        const allSelects = $('select');
        const initializedSelects = $('select.select2-hidden-accessible');
        const uninitializedSelects = $('select:not(.select2-hidden-accessible)');
        
        console.log('2. Статистика элементов:');
        console.log(`   Всего select: ${allSelects.length}`);
        console.log(`   Инициализированных: ${initializedSelects.length}`);
        console.log(`   НЕ инициализированных: ${uninitializedSelects.length}`);
        
        // 3. Детальная информация по неинициализированным
        if (uninitializedSelects.length > 0) {
            console.log('3. НЕ инициализированные поля:');
            uninitializedSelects.each(function(index) {
                const $select = $(this);
                const isVisible = $select.is(':visible');
                const hasFormSelectClass = $select.hasClass('form-select');
                
                console.log(`   ${index + 1}. ID: ${this.id || 'нет'}, Name: ${this.name || 'нет'}`);
                console.log(`      Видимый: ${isVisible}, Класс form-select: ${hasFormSelectClass}`);
                console.log(`      Родитель: ${$select.parent().get(0).tagName}.${$select.parent().get(0).className}`);
            });
            
            // 4. Попытка принудительной инициализации
            console.log('4. Попытка принудительной инициализации...');
            initializeRemainingSelects(uninitializedSelects);
        } else {
            console.log('✅ Все select поля инициализированы!');
        }
        
        // 5. Проверяем работу с глобальными функциями
        console.log('5. Проверка глобальных функций:');
        console.log('   forceReinitializeAllSelect2:', typeof window.forceReinitializeAllSelect2);
        console.log('   initializeSelect2ForField:', typeof window.initializeSelect2ForField);
        console.log('   initializeSelect2ForDealEditPage:', typeof window.initializeSelect2ForDealEditPage);
        
        console.log('=== КОНЕЦ ТЕСТИРОВАНИЯ ===');
    }
    
    function initializeRemainingSelects(uninitializedSelects) {
        uninitializedSelects.each(function() {
            const $select = $(this);
            
            if (!$select.is(':visible')) {
                console.log(`⚠️ Пропускаем невидимый элемент: ${this.id || this.name}`);
                return;
            }
            
            try {
                let $parent = $select.closest('.col-md-6, .col-md-12, .col-12, .form-group, .mb-3');
                if (!$parent.length) {
                    $parent = $select.parent();
                }
                
                $select.select2({
                    theme: 'bootstrap-5',
                    width: '100%',
                    placeholder: $select.attr('placeholder') || $select.data('placeholder') || 'Выберите значение',
                    allowClear: true,
                    language: 'ru',
                    dropdownParent: $parent,
                    escapeMarkup: function(markup) {
                        return markup;
                    }
                });
                
                console.log(`✅ Успешно инициализирован: ${this.id || this.name}`);
                
            } catch (error) {
                console.error(`❌ Ошибка инициализации ${this.id || this.name}:`, error);
            }
        });
    }
    
    // Добавляем глобальную функцию для ручного тестирования
    window.testSelect2 = testSelect2Implementation;
    
    console.log('💡 Для ручного тестирования Select2 введите: testSelect2()');
    
})();
