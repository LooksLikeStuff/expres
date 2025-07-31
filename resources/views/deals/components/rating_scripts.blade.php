<!-- Скрипты для обработки рейтингов -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Небольшая задержка для уверенности, что RatingSystem загружен
        setTimeout(function() {
            // Проверяем, есть ли ID завершенной сделки в localStorage
            const completedDealId = localStorage.getItem('completed_deal_id');
            if (completedDealId) {
                console.log('[Кардинатор] Найден ID завершенной сделки в localStorage:',
                    completedDealId);

                if (typeof window.RatingSystem !== 'undefined' && typeof window.RatingSystem
                    .checkPendingRatings === 'function') {
                    window.RatingSystem.checkPendingRatings(completedDealId);
                } else if (typeof window.checkPendingRatings === 'function') {
                    window.checkPendingRatings(completedDealId);
                } else {
                    console.error('[Кардинатор] Система рейтингов не инициализирована');
                }
            }
        }, 1000);

        // Обработчик события обновления сделки
        window.addEventListener('dealUpdated', function(event) {
            if (event.detail && event.detail.dealId) {
                console.log('[Кардинатор] Событие обновления сделки, проверка рейтингов:', event.detail
                    .dealId);
                if (typeof window.RatingSystem !== 'undefined' && typeof window.RatingSystem
                    .checkPendingRatings === 'function') {
                    window.RatingSystem.checkPendingRatings(event.detail.dealId);
                }
            }
        });
    });
</script>
