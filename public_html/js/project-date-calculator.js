/**
 * 🗓️ АВТОМАТИЧЕСКИЙ РАСЧЕТ ПЛАНОВОЙ ДАТЫ ЗАВЕРШЕНИЯ ПРОЕКТА
 * 
 * Этот скрипт автоматически рассчитывает плановую дату завершения проекта
 * на основе даты старта работы и длительности в рабочих днях.
 * 
 * Учитывает только рабочие дни (понедельник-пятница), исключая выходные.
 * 
 * @version 1.0
 * @author ExpressDizain Team
 */

class ProjectDateCalculator {
    constructor() {
        this.startDateField = '#start_date';
        this.durationField = '#project_duration';
        this.endDateField = '#project_end_date';
        this.debugMode = true;
        
        this.init();
    }

    init() {
        this.log('🗓️ Инициализация калькулятора дат проекта...');
        
        // Используем более надежный способ ожидания DOM
        const initWhenReady = () => {
            if (typeof $ === 'undefined') {
                this.log('⏳ jQuery недоступен, повторяем попытку...');
                setTimeout(initWhenReady, 100);
                return;
            }
            
            if (document.readyState === 'loading') {
                $(document).ready(() => {
                    this.setupCalculator();
                });
            } else {
                this.setupCalculator();
            }
        };
        
        initWhenReady();
    }

    setupCalculator() {
        this.log('🚀 Настройка калькулятора...');
        this.bindEvents();
        this.calculateInitialDate();
        this.log('✅ Калькулятор дат активирован');
    }

    bindEvents() {
        this.log('🔗 Привязка событий...');
        
        // Проверяем наличие полей на странице
        const $startField = $(this.startDateField);
        const $durationField = $(this.durationField);
        const $endField = $(this.endDateField);
        
        this.log(`📋 Найденные поля:`, {
            startDate: $startField.length,
            duration: $durationField.length,
            endDate: $endField.length
        });
        
        if ($startField.length === 0) {
            this.log('❌ Поле даты старта не найдено!');
            return;
        }
        
        if ($durationField.length === 0) {
            this.log('❌ Поле длительности не найдено!');
            return;
        }
        
        if ($endField.length === 0) {
            this.log('❌ Поле даты завершения не найдено!');
            return;
        }
        
        // Событие изменения даты старта
        $startField.on('change', () => {
            this.log('📅 Изменена дата старта');
            this.calculateEndDate();
        });

        // Событие изменения длительности
        $durationField.on('input change keyup', () => {
            this.log('⏱️ Изменена длительность проекта');
            this.calculateEndDate();
        });

        // Дополнительное событие для обработки вставки текста
        $durationField.on('paste', () => {
            setTimeout(() => {
                this.calculateEndDate();
            }, 50);
        });
        
        this.log('✅ События успешно привязаны');
    }

    calculateInitialDate() {
        this.log('🚀 Расчет начальной даты при загрузке страницы...');
        this.calculateEndDate();
    }

    calculateEndDate() {
        const startDateValue = $(this.startDateField).val();
        const durationValue = parseInt($(this.durationField).val());

        this.log(`📊 Данные для расчета: Дата старта: ${startDateValue}, Длительность: ${durationValue} дней`);

        // Проверяем наличие обязательных данных
        if (!startDateValue || !durationValue || durationValue <= 0) {
            this.log('⚠️ Недостаточно данных для расчета');
            $(this.endDateField).val('');
            this.updateEndDateDisplay('');
            return;
        }

        try {
            const startDate = new Date(startDateValue);
            const endDate = this.addWorkingDays(startDate, durationValue);
            const endDateString = this.formatDate(endDate);

            this.log(`✅ Рассчитанная дата завершения: ${endDateString}`);
            
            $(this.endDateField).val(endDateString);
            this.updateEndDateDisplay(endDateString);
            this.showCalculationAnimation();
            
        } catch (error) {
            this.log('❌ Ошибка при расчете даты:', error);
            $(this.endDateField).val('');
            this.updateEndDateDisplay('');
        }
    }

    /**
     * Добавляет рабочие дни к дате (исключая выходные)
     * @param {Date} startDate - Начальная дата
     * @param {number} workingDays - Количество рабочих дней
     * @returns {Date} - Конечная дата
     */
    addWorkingDays(startDate, workingDays) {
        let currentDate = new Date(startDate);
        let addedDays = 0;

        while (addedDays < workingDays) {
            currentDate.setDate(currentDate.getDate() + 1);
            
            // Проверяем, что это рабочий день (понедельник = 1, пятница = 5)
            const dayOfWeek = currentDate.getDay();
            if (dayOfWeek >= 1 && dayOfWeek <= 5) {
                addedDays++;
            }
        }

        return currentDate;
    }

    /**
     * Форматирует дату в формат YYYY-MM-DD
     * @param {Date} date - Дата для форматирования
     * @returns {string} - Отформатированная дата
     */
    formatDate(date) {
        const year = date.getFullYear();
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const day = String(date.getDate()).padStart(2, '0');
        return `${year}-${month}-${day}`;
    }

    /**
     * Обновляет отображение поля даты завершения
     * @param {string} dateValue - Значение даты
     */
    updateEndDateDisplay(dateValue) {
        const $endDateField = $(this.endDateField);
        
        if (dateValue) {
            // Добавляем класс для стилизации автоматически рассчитанного поля
            $endDateField.addClass('auto-calculated-active');
            
            // Показываем красивое уведомление
            this.showSuccessIndicator();
        } else {
            $endDateField.removeClass('auto-calculated-active');
        }
    }

    /**
     * Показывает индикатор успешного расчета
     */
    showSuccessIndicator() {
        const $container = $(this.endDateField).closest('.col-md-4');
        const $existingIndicator = $container.find('.calculation-success');
        
        if ($existingIndicator.length === 0) {
            const $indicator = $(`
                <div class="calculation-success mt-1">
                    <small class="text-success">
                        <i class="fas fa-check-circle me-1"></i>
                        Дата рассчитана автоматически
                    </small>
                </div>
            `);
            
            $container.append($indicator);
            
            // Убираем индикатор через 3 секунды
            setTimeout(() => {
                $indicator.fadeOut(300, function() {
                    $(this).remove();
                });
            }, 3000);
        }
    }

    /**
     * Показывает анимацию расчета
     */
    showCalculationAnimation() {
        const $endDateField = $(this.endDateField);
        
        // Добавляем временный класс для анимации
        $endDateField.addClass('calculating');
        
        setTimeout(() => {
            $endDateField.removeClass('calculating').addClass('calculated');
            
            setTimeout(() => {
                $endDateField.removeClass('calculated');
            }, 1000);
        }, 300);
    }

    /**
     * Логирование для отладки
     * @param {...any} args - Аргументы для логирования
     */
    log(...args) {
        if (this.debugMode) {
            console.log('🗓️ [ProjectDateCalculator]', ...args);
        }
    }
}

// Безопасная инициализация с ожиданием jQuery
function initProjectDateCalculator() {
    console.log('🗓️ Попытка инициализации калькулятора дат...');
    
    // Проверяем доступность jQuery
    if (typeof $ === 'undefined') {
        console.log('⏳ jQuery еще не загружен, ждем...');
        setTimeout(initProjectDateCalculator, 100);
        return;
    }
    
    console.log('✅ jQuery найден, инициализируем калькулятор...');
    
    // Создаем экземпляр калькулятора
    window.projectDateCalculator = new ProjectDateCalculator();
    
    console.log('🗓️ Калькулятор дат проекта успешно загружен!');
}

// Запускаем инициализацию
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initProjectDateCalculator);
} else {
    initProjectDateCalculator();
}
