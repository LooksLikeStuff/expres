<!-- Улучшенная система автоматического расчета даты завершения проекта с Flatpickr -->
<script>
    /**
     * Современный калькулятор дат проекта с интеграцией Flatpickr
     */
    class EnhancedProjectDateCalculator {
        constructor() {
            this.startDateField = null;
            this.durationField = null;
            this.endDateField = null;
            this.initialized = false;
            this.holidays = [
                // Российские праздники 2025
                '2025-01-01', '2025-01-02', '2025-01-03', '2025-01-04', '2025-01-05',
                '2025-01-06', '2025-01-07', '2025-01-08', '2025-02-23', '2025-03-08',
                '2025-05-01', '2025-05-09', '2025-06-12', '2025-11-04'
            ];
        }

        /**
         * Инициализация калькулятора
         */
        init() {
            this.findFields();
            if (this.areFieldsValid()) {
                this.loadFlatpickr().then(() => {
                    this.setupDatePickers();
                    this.setupEventListeners();
                    this.calculateInitialDates();
                    this.initialized = true;
                    console.log('📅 [EnhancedDateCalculator] Инициализирован успешно');
                });
            } else {
                console.warn('⚠️ [EnhancedDateCalculator] Не удалось найти все необходимые поля');
            }
        }

        /**
         * Динамическая загрузка Flatpickr
         */
        async loadFlatpickr() {
            if (typeof flatpickr !== 'undefined') {
                return Promise.resolve();
            }

            return new Promise((resolve, reject) => {
                // Загружаем CSS
                const css = document.createElement('link');
                css.rel = 'stylesheet';
                css.href = 'https://cdn.jsdelivr.net/npm/flatpickr@4.6.13/dist/flatpickr.min.css';
                document.head.appendChild(css);

                // Загружаем тему
                const theme = document.createElement('link');
                theme.rel = 'stylesheet';
                theme.href = 'https://cdn.jsdelivr.net/npm/flatpickr@4.6.13/dist/themes/material_blue.css';
                document.head.appendChild(theme);

                // Загружаем JS
                const script = document.createElement('script');
                script.src = 'https://cdn.jsdelivr.net/npm/flatpickr@4.6.13/dist/flatpickr.min.js';
                script.onload = () => {
                    // Загружаем русскую локализацию
                    const scriptRu = document.createElement('script');
                    scriptRu.src = 'https://cdn.jsdelivr.net/npm/flatpickr@4.6.13/dist/l10n/ru.js';
                    scriptRu.onload = resolve;
                    scriptRu.onerror = reject;
                    document.head.appendChild(scriptRu);
                };
                script.onerror = reject;
                document.head.appendChild(script);
            });
        }

        /**
         * Поиск полей на странице
         */
        findFields() {
            this.startDateField = document.getElementById('start_date') ||
                document.querySelector('input[name="start_date"]') ||
                document.querySelector('input[id*="start_date"]');

            this.durationField = document.getElementById('project_duration') ||
                document.querySelector('input[name="project_duration"]') ||
                document.querySelector('input[id*="duration"]');

            this.endDateField = document.getElementById('project_end_date') ||
                document.querySelector('input[name="project_end_date"]') ||
                document.querySelector('input[id*="end_date"]');
        }

        /**
         * Проверка валидности найденных полей
         */
        areFieldsValid() {
            return this.startDateField && this.durationField && this.endDateField;
        }

        /**
         * Настройка современных date picker'ов с Flatpickr
         */
        setupDatePickers() {
            const commonConfig = {
                locale: 'ru',
                dateFormat: 'Y-m-d',
                altFormat: 'd.m.Y',
                altInput: true,
                allowInput: true,
                clickOpens: true,
                theme: 'material_blue',
                time_24hr: true
            };

            // Date picker для даты начала с выбором сегодня по умолчанию
            if (this.startDateField && !this.startDateField._flatpickr) {
                flatpickr(this.startDateField, {
                    ...commonConfig,
                    placeholder: 'Выберите дату начала проекта',
                    defaultDate: this.startDateField.value || new Date(),
                    minDate: new Date().fp_incr(-365), // Можно выбрать дату до года назад
                    maxDate: new Date().fp_incr(365), // И до года вперед
                    onChange: () => {
                        setTimeout(() => this.calculateEndDate(), 100);
                        this.addFieldAnimation(this.startDateField);
                    }
                });
            }

            // Date picker для даты завершения (только для просмотра)
            if (this.endDateField && !this.endDateField._flatpickr) {
                flatpickr(this.endDateField, {
                    ...commonConfig,
                    placeholder: 'Рассчитается автоматически',
                    clickOpens: false,
                    allowInput: false,
                    altInputClass: 'flatpickr-alt-input readonly-date-field'
                });
            }
        }

        /**
         * Настройка обработчиков событий
         */
        setupEventListeners() {
            // Обработчики для срока проекта
            this.durationField.addEventListener('input', () => {
                this.calculateEndDate();
                this.addFieldAnimation(this.durationField);
            });
            
            this.durationField.addEventListener('change', () => this.calculateEndDate());
            this.durationField.addEventListener('keyup', () => this.calculateEndDate());

            // Валидация срока проекта
            this.durationField.addEventListener('input', (e) => {
                let value = parseInt(e.target.value);
                if (value && (value < 1 || value > 365)) {
                    this.showFieldError(this.durationField, 'Срок проекта должен быть от 1 до 365 дней');
                } else {
                    this.clearFieldError(this.durationField);
                }
            });
        }

        /**
         * Расчет даты завершения проекта
         */
        calculateEndDate() {
            if (!this.areFieldsValid()) return;

            const startValue = this.startDateField.value.trim();
            const durationValue = this.durationField.value.trim();

            console.log('🔄 [EnhancedDateCalculator] Расчет даты завершения:', {
                'Дата начала': startValue,
                'Срок проекта (дней)': durationValue
            });

            // Проверяем наличие обязательных значений
            if (!startValue || !durationValue) {
                this.clearEndDate();
                return;
            }

            const workDays = parseInt(durationValue);
            if (isNaN(workDays) || workDays <= 0) {
                this.clearEndDate();
                this.showFieldError(this.durationField, 'Введите корректное количество дней');
                return;
            }

            // Парсим дату начала
            const startDate = this.parseDate(startValue);
            if (!startDate || isNaN(startDate.getTime())) {
                this.clearEndDate();
                this.showFieldError(this.startDateField, 'Некорректная дата начала');
                return;
            }

            // Рассчитываем дату завершения
            const endDate = this.addWorkDays(startDate, workDays);
            const formattedEndDate = this.formatDateForInput(endDate);

            // Устанавливаем результат
            this.endDateField.value = formattedEndDate;
            
            // Обновляем Flatpickr
            if (this.endDateField._flatpickr) {
                this.endDateField._flatpickr.setDate(endDate, false);
            }

            console.log('✅ [EnhancedDateCalculator] Дата завершения рассчитана:', formattedEndDate);
            
            // Добавляем визуальную обратную связь
            this.addSuccessAnimation();
            this.clearFieldError(this.durationField);
            this.clearFieldError(this.startDateField);
        }

        /**
         * Парсинг даты из строки
         */
        parseDate(dateString) {
            if (dateString.includes('-')) {
                return new Date(dateString);
            } else if (dateString.includes('.')) {
                const [day, month, year] = dateString.split('.');
                return new Date(year, month - 1, day);
            } else {
                return new Date(dateString);
            }
        }

        /**
         * Добавление рабочих дней к дате (исключая выходные и праздники)
         */
        addWorkDays(startDate, workDays) {
            let currentDate = new Date(startDate);
            let remainingDays = workDays;

            while (remainingDays > 0) {
                currentDate.setDate(currentDate.getDate() + 1);
                
                if (this.isWorkDay(currentDate)) {
                    remainingDays--;
                }
            }

            return currentDate;
        }

        /**
         * Проверка, является ли день рабочим
         */
        isWorkDay(date) {
            const dayOfWeek = date.getDay();
            const dateString = this.formatDateForComparison(date);
            
            // Проверяем выходные (суббота = 6, воскресенье = 0)
            if (dayOfWeek === 0 || dayOfWeek === 6) {
                return false;
            }
            
            // Проверяем праздники
            if (this.holidays.includes(dateString)) {
                return false;
            }
            
            return true;
        }

        /**
         * Форматирование даты для сравнения с праздниками
         */
        formatDateForComparison(date) {
            return date.getFullYear() + '-' + 
                   String(date.getMonth() + 1).padStart(2, '0') + '-' + 
                   String(date.getDate()).padStart(2, '0');
        }

        /**
         * Форматирование даты для input поля
         */
        formatDateForInput(date) {
            return this.formatDateForComparison(date);
        }

        /**
         * Расчет начальных дат при загрузке
         */
        calculateInitialDates() {
            // Если дата начала не указана, устанавливаем сегодня
            if (!this.startDateField.value) {
                const today = new Date();
                this.startDateField.value = this.formatDateForInput(today);
                
                if (this.startDateField._flatpickr) {
                    this.startDateField._flatpickr.setDate(today, false);
                }
            }

            // Выполняем расчет если есть все данные
            if (this.startDateField.value && this.durationField.value) {
                this.calculateEndDate();
            }
        }

        /**
         * Очистка поля даты завершения
         */
        clearEndDate() {
            if (this.endDateField) {
                this.endDateField.value = '';
                if (this.endDateField._flatpickr) {
                    this.endDateField._flatpickr.clear();
                }
            }
        }

        /**
         * Анимация успешного расчета
         */
        addSuccessAnimation() {
            if (this.endDateField) {
                this.endDateField.classList.add('date-calculated-success');
                setTimeout(() => {
                    this.endDateField.classList.remove('date-calculated-success');
                }, 1500);
            }
        }

        /**
         * Анимация изменения поля
         */
        addFieldAnimation(field) {
            if (field) {
                field.classList.add('field-updated');
                setTimeout(() => {
                    field.classList.remove('field-updated');
                }, 300);
            }
        }

        /**
         * Показать ошибку для поля
         */
        showFieldError(field, message) {
            this.clearFieldError(field);
            
            const errorDiv = document.createElement('div');
            errorDiv.className = 'field-error-message';
            errorDiv.textContent = message;
            
            field.classList.add('field-error');
            field.parentNode.appendChild(errorDiv);
        }

        /**
         * Очистить ошибку поля
         */
        clearFieldError(field) {
            field.classList.remove('field-error');
            const existingError = field.parentNode.querySelector('.field-error-message');
            if (existingError) {
                existingError.remove();
            }
        }

        /**
         * Уничтожение калькулятора
         */
        destroy() {
            this.initialized = false;
            
            // Уничтожаем Flatpickr экземпляры
            if (this.startDateField && this.startDateField._flatpickr) {
                this.startDateField._flatpickr.destroy();
            }
            if (this.endDateField && this.endDateField._flatpickr) {
                this.endDateField._flatpickr.destroy();
            }
        }
    }

    // Глобальная переменная для доступа к калькулятору
    window.enhancedProjectDateCalculator = null;

    /**
     * Инициализация улучшенного калькулятора дат
     */
    function initEnhancedProjectDateCalculator() {
        // Уничтожаем предыдущий экземпляр если есть
        if (window.enhancedProjectDateCalculator) {
            window.enhancedProjectDateCalculator.destroy();
        }

        // Создаем новый экземпляр
        window.enhancedProjectDateCalculator = new EnhancedProjectDateCalculator();
        window.enhancedProjectDateCalculator.init();
    }

    // Автоматическая инициализация при загрузке DOM
    document.addEventListener('DOMContentLoaded', function() {
        console.log('📅 [EnhancedDateCalculator] DOM загружен, инициализация...');
        setTimeout(initEnhancedProjectDateCalculator, 500);
    });

    // Инициализация при показе модального окна
    if (typeof $ !== 'undefined') {
        $(document).on('shown.bs.modal', '#editModal', function() {
            console.log('📋 [EnhancedDateCalculator] Модальное окно показано, инициализация...');
            setTimeout(initEnhancedProjectDateCalculator, 300);
        });
    }

    // Наблюдение за изменениями DOM для переинициализации
    const observer = new MutationObserver(function(mutations) {
        let needsReinit = false;
        
        mutations.forEach(function(mutation) {
            if (mutation.addedNodes.length) {
                for (let node of mutation.addedNodes) {
                    if (node.nodeType === 1 && (
                        node.id === 'editModal' || 
                        node.querySelector && node.querySelector('#editModal')
                    )) {
                        needsReinit = true;
                        break;
                    }
                }
            }
        });
        
        if (needsReinit) {
            console.log('🔄 [EnhancedDateCalculator] Обнаружено новое модальное окно, переинициализация...');
            setTimeout(initEnhancedProjectDateCalculator, 300);
        }
    });

    observer.observe(document.body, {
        childList: true,
        subtree: true
    });

    // Экспорт функции для ручной инициализации
    window.initEnhancedProjectDateCalculator = initEnhancedProjectDateCalculator;
</script>
