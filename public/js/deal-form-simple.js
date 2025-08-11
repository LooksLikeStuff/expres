/**
 * Простая система работы с формой сделки
 * Только базовая функциональность без AJAX
 * Обычная отправка формы с редиректом
 */

(function() {
    'use strict';
    
    console.log('🔧 Простая система формы сделки загружена');

    // Ждем полной загрузки DOM
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initSimpleForm);
    } else {
        initSimpleForm();
    }

    function initSimpleForm() {
        console.log('🔄 Инициализация простой формы сделки...');
        
        const form = document.getElementById('deal-edit-form');
        const saveButton = document.getElementById('saveButton');
        
        if (!form) {
            console.error('❌ Форма сделки не найдена');
            return;
        }

        if (!saveButton) {
            console.error('❌ Кнопка сохранения не найдена');
            return;
        }

        // Простая обработка отправки формы
        form.addEventListener('submit', function(e) {
            // НЕ preventDefault() - позволяем форме отправиться обычным способом
            console.log('🚀 Отправка формы...');
            
            // Показываем индикатор загрузки
            saveButton.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Сохранение...';
            saveButton.disabled = true;
            
            // Базовая валидация
            if (!validateBasicFields(form)) {
                e.preventDefault();
                resetSaveButton(saveButton);
                showNotification('Пожалуйста, заполните обязательные поля', 'error');
                return false;
            }

            // Форма отправится автоматически
            console.log('✅ Форма отправлена обычным способом');
        });

        console.log('✅ Простая форма инициализирована');
    }

    /**
     * Базовая валидация полей формы
     */
    function validateBasicFields(form) {
        const clientName = form.querySelector('[name="client_name"]');
        
        if (clientName && !clientName.value.trim()) {
            clientName.focus();
            return false;
        }

        return true;
    }

    /**
     * Сброс состояния кнопки сохранения
     */
    function resetSaveButton(saveButton) {
        saveButton.innerHTML = '<i class="fas fa-save me-2"></i>Сохранить';
        saveButton.disabled = false;
    }

    /**
     * Простая функция показа уведомлений
     */
    function showNotification(message, type = 'success') {
        // Используем простой alert для уведомлений
        if (type === 'error') {
            alert('Ошибка: ' + message);
        } else {
            console.log('✅ ' + message);
        }
    }

    // Экспортируем функции в глобальную область для совместимости
    window.showNotification = showNotification;

    console.log('✅ Простая система формы сделки готова');

})();
