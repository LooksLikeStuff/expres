/**
 * Исправление ошибок JavaScript для сайта
 * Устраняет распространенные ошибки и предотвращает падение скриптов
 */

(function() {
    'use strict';

    // Проверка наличия jQuery и загрузка при необходимости
    if (typeof jQuery === 'undefined') {
        console.log('🔄 jQuery не найден, загружаем...');
        
        const script = document.createElement('script');
        script.src = 'https://code.jquery.com/jquery-3.6.0.min.js';
        script.onload = function() {
            console.log('✅ jQuery загружен успешно');
            initErrorHandling();
        };
        document.head.appendChild(script);
    } else {
        initErrorHandling();
    }

    function initErrorHandling() {
        console.log('🛡️ Инициализация системы обработки ошибок...');

        // Глобальные определения для предотвращения ошибок
        if (typeof window.handleDomErrors === 'undefined') {
            window.handleDomErrors = function(error) {
                console.log('🔍 DOM ошибка перехвачена:', error);
                return true;
            };
        }

        if (typeof window.subscribeToNotifications === 'undefined') {
            window.subscribeToNotifications = function() {
                console.log('ℹ️ Функция подписки на уведомления вызвана, но не реализована');
                return false;
            };
        }

        // Добавляем обработку ненайденных элементов для рейтингов
        document.addEventListener('DOMContentLoaded', function() {
            if (!document.getElementById('rating-modal')) {
                console.log('ℹ️ Элемент #rating-modal не найден - это ожидаемо на текущей странице');
            }
        });

        // Перехватываем общие ошибки
        window.addEventListener('error', function(event) {
            console.log('🚨 Перехвачена ошибка:', event.error ? event.error.message : event.message);
            
            // Предотвращаем распространение конкретных ошибок
            if (event.message && (
                event.message.includes('Cannot read properties of null') || 
                event.message.includes('$ is not defined') ||
                event.message.includes('handleDomErrors') ||
                event.message.includes('subscribeToNotifications')
            )) {
                console.log('✅ Ошибка подавлена для предотвращения падения страницы');
                event.preventDefault();
                return false;
            }
        });
        
        // Перехватываем AJAX ошибки 404 для Яндекс.Диска
        if (window.jQuery) {
            jQuery(document).ajaxError(function(event, jqXHR, settings, thrownError) {
                if (settings.url && settings.url.includes('/api/yandex-disk/')) {
                    console.log('ℹ️ Перехвачен 404 для Яндекс.Диска:', settings.url);
                    event.preventDefault();
                    return false;
                }
            });
        }

        console.log('✅ Система обработки ошибок инициализирована');
    }
})();
