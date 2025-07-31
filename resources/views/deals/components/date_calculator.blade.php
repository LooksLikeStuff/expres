<!-- Скрипт для расчета даты завершения проекта -->
<script>
    // Функция для расчета даты завершения проекта
    function initProjectDateCalculator() {

        // Находим поля разными способами для повышения надежности
        const startDateField = document.getElementById('start_date') ||
            document.querySelector('input[name="start_date"]') ||
            document.querySelector('input[id*="start_date"]');

        const durationField = document.getElementById('project_duration') ||
            document.querySelector('input[name="project_duration"]') ||
            document.querySelector('input[id*="duration"]');

        const endDateField = document.getElementById('project_end_date') ||
            document.querySelector('input[name="project_end_date"]') ||
            document.querySelector('input[id*="end_date"]');

        // Проверяем, найдены ли все необходимые поля
        if (startDateField && durationField && endDateField) {

            // Функция для расчета даты завершения с учетом только рабочих дней
            function calculateEndDate() {
                console.log('[DateCalculator] Запуск расчета даты завершения:', {
                    'Начальная дата': startDateField.value,
                    'Срок проекта (дней)': durationField.value
                });

                // Проверяем, есть ли значения в обоих полях
                if (!startDateField.value || !durationField.value) {
                    return;
                }

                const workDays = parseInt(durationField.value);
                // Если введено некорректное значение, очищаем поле даты завершения
                if (isNaN(workDays) || workDays <= 0) {
                    endDateField.value = '';
                    return;
                }

                // Преобразуем дату начала в объект Date
                let startDate;

                // Поддержка разных форматов даты
                if (startDateField.value.includes('-')) { // формат YYYY-MM-DD
                    const [year, month, day] = startDateField.value.split('-');
                    startDate = new Date(year, month - 1, day);
                } else if (startDateField.value.includes('.')) { // формат DD.MM.YYYY
                    const [day, month, year] = startDateField.value.split('.');
                    startDate = new Date(year, month - 1, day);
                } else {
                    startDate = new Date(startDateField.value);
                }

                // Если дата некорректная, выходим
                if (isNaN(startDate.getTime())) {
                    endDateField.value = '';
                    return;
                }

                // Отладочная информация о начальной дате
                let remainingWorkDays = workDays;
                let currentDate = new Date(startDate);

                // Цикл для добавления рабочих дней
                while (remainingWorkDays > 0) {
                    // Добавляем 1 день к текущей дате
                    currentDate.setDate(currentDate.getDate() + 1);

                    // Проверяем, является ли день рабочим (не суббота и не воскресенье)
                    const dayOfWeek = currentDate.getDay(); // 0 - воскресенье, 6 - суббота
                    if (dayOfWeek !== 0 && dayOfWeek !== 6) {
                        remainingWorkDays--; // Уменьшаем счетчик рабочих дней
                    }
                }

                // Определяем формат выходной даты на основе формата входной
                let formattedDate;

                if (endDateField.type === 'date' || startDateField.value.includes('-')) {
                    // Для полей типа date или если входной формат YYYY-MM-DD
                    formattedDate = currentDate.toISOString().split('T')[0]; // YYYY-MM-DD
                } else if (startDateField.value.includes('.')) {
                    // Если входной формат DD.MM.YYYY
                    const day = String(currentDate.getDate()).padStart(2, '0');
                    const month = String(currentDate.getMonth() + 1).padStart(2, '0');
                    const year = currentDate.getFullYear();
                    formattedDate = `${day}.${month}.${year}`;
                } else {
                    // Используем локальный формат даты как запасной вариант
                    formattedDate = currentDate.toLocaleDateString();
                }

                // Устанавливаем дату завершения проекта
                endDateField.value = formattedDate;
            }

            // Удаляем все существующие обработчики событий (для избежания дубликатов)
            startDateField.removeEventListener('change', calculateEndDate);
            startDateField.removeEventListener('input', calculateEndDate);
            durationField.removeEventListener('change', calculateEndDate);
            durationField.removeEventListener('input', calculateEndDate);
            durationField.removeEventListener('keyup', calculateEndDate);

            // Добавляем слушатели событий для полей ввода
            startDateField.addEventListener('change', calculateEndDate);
            startDateField.addEventListener('input', calculateEndDate);

            // Добавляем несколько типов событий для надежности
            durationField.addEventListener('change', calculateEndDate);
            durationField.addEventListener('input', calculateEndDate);
            durationField.addEventListener('keyup', calculateEndDate); // Дополнительный обработчик

            // Запускаем расчет при загрузке страницы, если поля уже заполнены
            calculateEndDate();

            // Устанавливаем прямое отслеживание изменений значения поля duration
            // для случаев, когда стандартные события могут не срабатывать
            const originalValue = durationField.value;
            setInterval(() => {
                if (durationField.value !== originalValue && durationField.value) {
                    calculateEndDate();
                }
            }, 500);
        }
    }

    // Инициализация при загрузке страницы
    document.addEventListener('DOMContentLoaded', function() {
        initProjectDateCalculator();

        // Также инициализируем при появлении модального окна
        const observer = new MutationObserver(function(mutations) {
            mutations.forEach(function(mutation) {
                if (mutation.addedNodes.length) {
                    for (let node of mutation.addedNodes) {
                        if (node.id === 'editModal' || (node.querySelector && node
                                .querySelector('#editModal'))) {
                            setTimeout(initProjectDateCalculator, 300);
                        }
                    }
                }
            });
        });

        // Начинаем наблюдение за документом
        observer.observe(document.body, {
            childList: true,
            subtree: true
        });
    });

    // Повторная инициализация при появлении модального окна
    $(document).on('shown.bs.modal', '#editModal', function() {
        setTimeout(initProjectDateCalculator, 300);
    });

    // Добавляем глобальную функцию для ручной инициализации
    window.initProjectDateCalculator = initProjectDateCalculator;
</script>
