<!-- Скрипты для работы с фильтрами -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Используем глобальные функции из app.blade.php
        if (typeof updateFilterCounters === 'function') {
            updateFilterCounters();
        }

        // Обработчики для подсветки полей с фильтрами
        const dateFields = document.querySelectorAll('.filter-date');
        dateFields.forEach(field => {
            field.addEventListener('change', function() {
                if (this.value) {
                    this.classList.add('filter-active');
                } else {
                    this.classList.remove('filter-active');
                }
                if (typeof updateFilterCounters === 'function') {
                    updateFilterCounters();
                }
            });

            // Инициализация
            if (field.value) {
                field.classList.add('filter-active');
            }
        });

        // Подсветка селектов при изменении
        const selectFields = document.querySelectorAll('.filter-select');
        selectFields.forEach(field => {
            field.addEventListener('change', function() {
                if (this.value) {
                    this.classList.add('filter-active');
                } else {
                    this.classList.remove('filter-active');
                }
                if (typeof updateFilterCounters === 'function') {
                    updateFilterCounters();
                }
            });

            // Инициализация
            if (field.value) {
                field.classList.add('filter-active');
            }
        });

        // Инициализация всплывающих подсказок Bootstrap
        if (typeof $().tooltip === 'function') {
            $('[title]').tooltip({
                placement: 'auto',
                trigger: 'hover',
                delay: {
                    show: 1500,
                    hide: 0
                },
                animation: false,
                container: 'body',
                template: '<div class="tooltip custom-tooltip" role="tooltip"><div class="tooltip-arrow"></div><div class="tooltip-inner"></div></div>'
            });
        }

        // Инициализация Select2 для фильтров с поиском
        if (typeof $.fn.select2 !== 'undefined') {
            initializeSearchableFilters();
        } else {
            console.error('Select2 plugin not loaded');
        }

        // Обработка мультиселекта для статусов
        const multiselect = document.querySelector('.custom-multiselect');
        const dropdown = multiselect.querySelector('.multiselect-dropdown');
        const selected = multiselect.querySelector('#status-selected');
        const checkboxes = multiselect.querySelectorAll('.status-checkbox:not([data-status-all])');
        const checkboxAll = multiselect.querySelector('[data-status-all]');
        const hiddenInput = document.getElementById('status-hidden');

        // Инициализация текста выбранных элементов
        updateSelectedText();

        // При клике на селект открываем/закрываем дропдаун
        multiselect.addEventListener('click', function(e) {
            if (!e.target.closest('.multiselect-dropdown') || e.target.tagName === 'LABEL') {
                dropdown.classList.toggle('open');
                e.stopPropagation();
            }
        });

        // При клике вне мультиселекта - закрываем его
        document.addEventListener('click', function(e) {
            if (!e.target.closest('.custom-multiselect')) {
                dropdown.classList.remove('open');
            }
        });

        // Обработка выбора "Все статусы"
        checkboxAll.addEventListener('change', function() {
            const isChecked = this.checked;
            checkboxes.forEach(checkbox => {
                checkbox.checked = isChecked;
            });
            updateSelectedText();
        });

        // Обработка выбора отдельных статусов
        checkboxes.forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                // Если все чекбоксы выбраны - отмечаем "Все статусы"
                const allChecked = Array.from(checkboxes).every(cb => cb.checked);
                checkboxAll.checked = allChecked;

                // Если ни один не выбран - помечаем "Все статусы"
                if (Array.from(checkboxes).filter(cb => cb.checked).length === 0) {
                    checkboxAll.checked = true;
                }

                updateSelectedText();
            });
        });

        // Обновление текста выбранных элементов
        function updateSelectedText() {
            const checkedItems = Array.from(checkboxes).filter(cb => cb.checked);

            if (checkedItems.length === 0 || checkedItems.length === checkboxes.length) {
                selected.textContent = 'Все статусы';
                hiddenInput.value = '';
            } else {
                const texts = checkedItems.map(cb => {
                    return cb.nextElementSibling.textContent;
                });

                selected.textContent = texts.join(', ');

                // Обновляем скрытый input для совместимости
                hiddenInput.value = texts[0]; // записываем первый выбранный статус для совместимости
            }

            // Помечаем класс, если есть выбранные элементы
            if (checkedItems.length > 0 && checkedItems.length < checkboxes.length) {
                selected.classList.add('filter-active');
            } else {
                selected.classList.remove('filter-active');
            }

            // Обновляем счётчики фильтров
            if (typeof updateFilterCounters === 'function') {
                updateFilterCounters();
            }
        }
    });

    // Функция инициализации поисковых фильтров
    function initializeSearchableFilters() {
        $('.select2-search').each(function() {
            var $select = $(this);
            var status = $select.data('status');

            // Находим родительский контейнер
            var $parent = $select.closest('.filter-group');
            if (!$parent.length) {
                $parent = $select.parent();
            }

            // Установка position: relative для корректного позиционирования dropdown
            $parent.css({
                'position': 'relative',
                'z-index': '100'
            });

            // Сохраняем ширину родительского элемента
            var parentWidth = $parent.width();

            $select.select2({
                width: '100%',
                placeholder: "Поиск...",
                allowClear: true,
                dropdownParent: $parent,
                language: 'ru',
                minimumInputLength: 1,
                ajax: {
                    url: '/search-users',
                    dataType: 'json',
                    delay: 300,
                    data: function(params) {
                        return {
                            q: params.term || '',
                            status: status,
                            page: params.page || 1
                        };
                    },
                    processResults: function(data, params) {
                        params.page = params.page || 1;

                        return {
                            results: $.map(data, function(user) {
                                return {
                                    id: user.id,
                                    text: user.name,
                                    email: user.email
                                };
                            }),
                            pagination: {
                                more: false
                            }
                        };
                    },
                    cache: true
                },
                templateResult: formatUserResult,
                templateSelection: formatUserSelection
            }).on('select2:open', function() {
                // Устанавливаем корректную ширину выпадающего списка
                setTimeout(function() {
                    $('.select2-container--open .select2-dropdown').css({
                        'width': parentWidth + 'px',
                        'min-width': parentWidth + 'px',
                        'max-width': parentWidth + 'px'
                    });
                }, 0);
            });

            // Применение классов при изменении значения
            $select.on('change', function() {
                if ($(this).val()) {
                    $(this).addClass('filter-active');
                } else {
                    $(this).removeClass('filter-active');
                }
                if (typeof updateFilterCounters === 'function') {
                    updateFilterCounters();
                }
            });

            // Инициализация - если значение уже выбрано
            if ($select.val()) {
                $select.addClass('filter-active');
            }
        });
    }

    // Форматирование результата поиска пользователя
    function formatUserResult(user) {
        if (!user.id) return user.text;
        var $result = $(
            '<div class="select2-user-result">' +
            '<div class="select2-user-name">' + user.text + '</div>' +
            (user.email ? '<div class="select2-user-email">' + user.email + '</div>' : '') +
            '</div>'
        );
        return $result;
    }

    // Форматирование выбранного пользователя
    function formatUserSelection(user) {
        return user.text;
    }
</script>
