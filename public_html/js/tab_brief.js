/**
 * Вспомогательный скрипт для инициализации функций работы с брифами
 * Этот файл загружается в глобальный контекст для обеспечения работы функций
 * поиска, привязки и отвязки брифов в AJAX-загруженных модальных окнах
 */

(function(window) {
    console.log('Инициализация глобальных функций для работы с брифами');

    // Эта функция будет вызываться после загрузки модального окна с брифами
    window.initBriefFunctions = function(dealId) {
        try {
            console.log('Проверка и экспорт функций для работы с брифами');
            
            // Функция поиска брифов
            if (typeof searchBriefsDirectly === 'function' && typeof window.searchBriefsDirectly !== 'function') {
                window.searchBriefsDirectly = searchBriefsDirectly;
                console.log('searchBriefsDirectly зарегистрирована глобально');
            }
            
            // Функция отображения результатов
            if (typeof displayBriefSearchResults === 'function' && typeof window.displayBriefSearchResults !== 'function') {
                window.displayBriefSearchResults = displayBriefSearchResults;
                console.log('displayBriefSearchResults зарегистрирована глобально');
            }
            
            // Функция привязки брифа к сделке
            if (typeof attachBriefToDeal === 'function' && typeof window.attachBriefToDeal !== 'function') {
                window.attachBriefToDeal = attachBriefToDeal;
                console.log('attachBriefToDeal зарегистрирована глобально');
            }
            
            // Функция отвязки брифа от сделки
            if (typeof detachBriefFromDeal === 'function' && typeof window.detachBriefFromDeal !== 'function') {
                window.detachBriefFromDeal = detachBriefFromDeal;
                console.log('detachBriefFromDeal зарегистрирована глобально');
            }
            
            console.log('Инициализация функций для работы с брифами завершена');
        } catch (e) {
            console.error('Ошибка при инициализации функций для работы с брифами:', e);
        }
    };
})(window);
