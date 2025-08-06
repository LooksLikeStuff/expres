/**
 * Простая система отправки формы сделки (БЕЗ AJAX)
 * Замена сложной AJAX системы на обычную отправку формы с редиректом
 * 
 * ИСПРАВЛЯЕТ ПРОБЛЕМЫ:
 * - Убирает ошибки AJAX
 * - Упрощает обработку загрузки файлов
 * - Использует стандартную отправку формы Laravel
 */

(function() {
    'use strict';
    
    console.log('🔧 Простая система отправки формы загружена');

    // Ждем полной загрузки DOM
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initSimpleForm);
    } else {
        initSimpleForm();
    }

    function initSimpleForm() {
        console.log('🔄 Инициализация простой отправки формы...');
        
        const form = document.getElementById('deal-edit-form');
        const saveButton = document.getElementById('saveButton');
        
        if (!form || !saveButton) {
            console.warn('⚠️ Форма или кнопка сохранения не найдены');
            return;
        }

        // Удаляем все предыдущие обработчики событий
        const newForm = form.cloneNode(true);
        form.parentNode.replaceChild(newForm, form);

        // Добавляем простой обработчик для кнопки сохранения
        const newSaveButton = document.getElementById('saveButton');
        if (newSaveButton) {
            newSaveButton.addEventListener('click', function(e) {
                handleFormSubmit(e);
            });
        }

        // Добавляем обработчик отправки формы
        newForm.addEventListener('submit', function(e) {
            handleFormSubmit(e);
        });

        console.log('✅ Простая отправка формы настроена');
    }

    /**
     * Обработка отправки формы
     */
    function handleFormSubmit(e) {
        console.log('📝 Начинается отправка формы...');

        const form = document.getElementById('deal-edit-form');
        const saveButton = document.getElementById('saveButton');

        if (!form) {
            console.error('❌ Форма не найдена');
            return;
        }

        // Показываем индикатор загрузки
        if (saveButton) {
            saveButton.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Сохранение...';
            saveButton.disabled = true;
        }

        // Валидация основных полей (опционально)
        if (!validateBasicFields(form)) {
            resetSaveButton(saveButton);
            return false;
        }

        // Показываем уведомление пользователю
        showNotification('Сохранение данных...', 'info');

        // Позволяем форме отправиться обычным способом
        // НЕ вызываем e.preventDefault() - форма отправится сама
        console.log('✅ Форма отправляется обычным способом');
        return true;
    }

    /**
     * Базовая валидация полей формы
     */
    function validateBasicFields(form) {
        const requiredFields = form.querySelectorAll('[required]');
        let isValid = true;

        requiredFields.forEach(field => {
            if (!field.value.trim()) {
                field.classList.add('is-invalid');
                isValid = false;
                console.warn('⚠️ Поле не заполнено:', field.name || field.id);
            } else {
                field.classList.remove('is-invalid');
            }
        });

        if (!isValid) {
            showNotification('Пожалуйста, заполните все обязательные поля', 'error');
        }

        return isValid;
    }

    /**
     * Сброс состояния кнопки сохранения
     */
    function resetSaveButton(saveButton) {
        if (saveButton) {
            saveButton.innerHTML = '<i class="fas fa-save me-2"></i>Сохранить изменения';
            saveButton.disabled = false;
        }
    }

    /**
     * Функция показа уведомлений
     */
    function showNotification(message, type = 'success') {
        // Используем существующую функцию, если есть
        if (typeof window.showNotification === 'function') {
            window.showNotification(message, type);
            return;
        }

        // Простая реализация уведомлений
        console.log(`[${type.toUpperCase()}] ${message}`);
        
        // Создаем простое уведомление
        const notification = document.createElement('div');
        notification.className = `alert alert-${getBootstrapClass(type)} alert-dismissible fade show position-fixed`;
        notification.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
        notification.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;

        document.body.appendChild(notification);

        // Автоматически скрываем через 5 секунд
        setTimeout(() => {
            if (notification.parentNode) {
                notification.parentNode.removeChild(notification);
            }
        }, 5000);
    }

    /**
     * Преобразование типов уведомлений в классы Bootstrap
     */
    function getBootstrapClass(type) {
        const classMap = {
            'success': 'success',
            'error': 'danger',
            'warning': 'warning',
            'info': 'info'
        };
        return classMap[type] || 'info';
    }

    // Экспортируем функции в глобальную область
    window.showNotification = showNotification;

    console.log('✅ Простая система отправки формы готова');

})();
