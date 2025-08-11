/**
 * Помощник для тестирования Select2
 * Версия: 2.0 - Исправленная
 */

(function() {
    'use strict';
    
    console.log('🧪 Select2 Test Helper v2.0 загружен');
    
    // Проверяем наличие jQuery
    if (typeof window.jQuery === 'undefined') {
        console.error('❌ jQuery не найден для тестирования Select2');
        return;
    }
    
    const $ = window.jQuery;
    
    // Функция проверки загрузки Select2
    function checkSelect2() {
        if (typeof $.fn.select2 !== 'undefined') {
            console.log('✅ Select2 загружен и готов к использованию');
            return true;
        } else {
            console.warn('⚠️ Select2 не загружен');
            return false;
        }
    }
    
    // Функция тестирования базовой инициализации Select2
    function testSelect2Init() {
        if (!checkSelect2()) {
            return false;
        }
        
        try {
            // Найдем select элементы для тестирования
            const selectElements = $('select:not(.select2-hidden-accessible)');
            
            if (selectElements.length > 0) {
                console.log(`🔍 Найдено ${selectElements.length} select элементов для тестирования`);
                
                // Попробуем инициализировать первый элемент
                const firstSelect = selectElements.first();
                firstSelect.select2({
                    width: '100%',
                    placeholder: 'Выберите опцию...',
                    allowClear: true
                });
                
                console.log('✅ Тестовая инициализация Select2 прошла успешно');
                return true;
            } else {
                console.log('ℹ️ Select элементы не найдены или уже инициализированы');
                return true;
            }
        } catch (error) {
            console.error('❌ Ошибка при тестировании Select2:', error);
            return false;
        }
    }
    
    // Функция полной диагностики
    function fullDiagnosis() {
        console.log('🔬 Запуск полной диагностики Select2...');
        
        const results = {
            jquery: typeof $ !== 'undefined',
            select2Plugin: typeof $.fn.select2 !== 'undefined',
            selectElements: $('select').length,
            initializedElements: $('select.select2-hidden-accessible').length
        };
        
        console.log('📊 Результаты диагностики:', results);
        return results;
    }
    
    // Экспорт функций в глобальную область
    window.Select2TestHelper = {
        check: checkSelect2,
        test: testSelect2Init,
        diagnose: fullDiagnosis
    };
    
    // Автоматический запуск диагностики при загрузке
    $(document).ready(function() {
        setTimeout(function() {
            console.log('🚀 Автоматический запуск диагностики Select2...');
            fullDiagnosis();
        }, 1000);
    });
    
})();
