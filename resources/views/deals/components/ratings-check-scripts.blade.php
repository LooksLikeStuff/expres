<!-- Скрипты для проверки рейтингов сделок -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Небольшая задержка для уверенности, что RatingSystem загружен
        setTimeout(function() {
            // Проверяем, есть ли ID завершенной сделки в localStorage
            const completedDealId = localStorage.getItem('completed_deal_id');
            if (completedDealId) {
                console.log('[Кардинатор] Найден ID завершенной сделки в localStorage:', completedDealId);

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
                console.log('[Кардинатор] Событие обновления сделки, проверка рейтингов:', event.detail.dealId);
                if (typeof window.RatingSystem !== 'undefined' && typeof window.RatingSystem
                    .checkPendingRatings === 'function') {
                    window.RatingSystem.checkPendingRatings(event.detail.dealId);
                }
            }
        });
    });
</script>

<!-- Скрипт для проверки завершенных сделок -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Небольшая задержка для уверенности, что ratings.js загружен
        setTimeout(function() {
            if (typeof window.checkPendingRatings !== 'function') {
                console.error('[Сделки] Функция checkPendingRatings не определена!');
                return;
            }

            console.log('[Сделки] Поиск завершенных сделок для проверки оценок...');

            // Собираем ID завершенных сделок
            const completedDealIds = [];

            // Проверяем блочное представление
            document.querySelectorAll('.faq_block__deal[data-status="Проект завершен"]').forEach(
                block => {
                    const dealId = block.dataset.id;
                    if (dealId) completedDealIds.push(dealId);
                });

            // Проверяем табличное представление
            document.querySelectorAll('#dealTable td').forEach(cell => {
                if (cell.textContent.trim() === 'Проект завершен') {
                    const row = cell.closest('tr');
                    const editBtn = row.querySelector('.edit-deal-btn');
                    if (editBtn && editBtn.dataset.id) {
                        completedDealIds.push(editBtn.dataset.id);
                    }
                }
            });

            console.log('[Сделки] Найдено завершенных сделок:', completedDealIds.length);

            // Проверяем localStorage
            const completedDealId = localStorage.getItem('completed_deal_id');
            if (completedDealId) {
                console.log('[Сделки] Найден ID завершенной сделки в localStorage:', completedDealId);
                window.checkPendingRatings(completedDealId);
                localStorage.removeItem('completed_deal_id');
            }
            // Если есть завершенные сделки на странице, проверяем первую из них
            else if (completedDealIds.length > 0) {
                console.log('[Сделки] Проверка оценок для первой найденной сделки:', completedDealIds[0]);
                window.checkPendingRatings(completedDealIds[0]);
            }
        }, 800);
    });
</script>
