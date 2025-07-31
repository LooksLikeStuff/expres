<!-- Скрипты для функционирования сделок -->

<script>
    // Глобальная функция для подтверждения удаления сделки
    window.confirmDeleteDeal = function(dealId) {
        if (confirm('ВНИМАНИЕ! Вы собираетесь удалить сделку. Это действие нельзя отменить.\n\nСвязи с брифами и другими элементами будут сохранены.\n\nВы уверены, что хотите удалить эту сделку?')) {
            console.log('Отправка запроса на удаление сделки #' + dealId);
            
            // Создаем форму для отправки запроса методом DELETE
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = `/deal/${dealId}/delete`;
            form.style.display = 'none';
            
            // Добавляем CSRF токен
            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            const csrfInput = document.createElement('input');
            csrfInput.type = 'hidden';
            csrfInput.name = '_token';
            csrfInput.value = csrfToken;
            form.appendChild(csrfInput);
            
            // Добавляем метод DELETE
            const methodInput = document.createElement('input');
            methodInput.type = 'hidden';
            methodInput.name = '_method';
            methodInput.value = 'DELETE';
            form.appendChild(methodInput);
            
            // Добавляем форму в документ и отправляем
            document.body.appendChild(form);
            form.submit();
            
            // Показываем индикатор загрузки
            const loadingScreen = document.createElement('div');
            loadingScreen.style.position = 'fixed';
            loadingScreen.style.top = '0';
            loadingScreen.style.left = '0';
            loadingScreen.style.width = '100%';
            loadingScreen.style.height = '100%';
            loadingScreen.style.backgroundColor = 'rgba(255, 255, 255, 0.8)';
            loadingScreen.style.display = 'flex';
            loadingScreen.style.justifyContent = 'center';
            loadingScreen.style.alignItems = 'center';
            loadingScreen.style.zIndex = '9999';
            loadingScreen.innerHTML = '<div>Удаление сделки...</div>';
            document.body.appendChild(loadingScreen);
        }
    };
</script>

<script>
    $(function() {
        // Инициализация DataTable для табличного вида
        if ($('#dealTable').length) {
            $('#dealTable').DataTable({
                language: {
                    url: 'https://cdn.datatables.net/plug-ins/1.13.4/i18n/ru.json'
                },
                paging: true,
                ordering: true,
                info: true,
                autoWidth: false,
                responsive: true,
                dom: '<"table-header"<"table-title"l><"table-search"f>><"table-content"rt><"table-footer"<"table-info"i><"table-pagination"p>>',
                lengthMenu: [
                    [10, 25, 50, -1],
                    [10, 25, 50, "Все"]
                ]
            });

            // Добавляем обработчик для окрашивания ячеек статуса
            $('#dealTable tbody tr').each(function() {
                var statusCell = $(this).find('td:nth-child(4)');
                var status = statusCell.text().trim();

                // Добавляем нужный класс в зависимости от статуса
                if (status === 'Новая заявка') {
                    statusCell.addClass('status-new');
                } else if (status === 'В процессе') {
                    statusCell.addClass('status-processing');
                } else if (status === 'Проект завершен') {
                    statusCell.addClass('status-completed');
                }
            });
        }

        // Пагинация для блочного вида
        function paginateContainer(container, paginationContainer, perPage = 6) {
            var $container = $(container);
            var $blocks = $container.find('.faq_block__deal');
            var total = $blocks.length;

            if (total <= perPage) {
                $blocks.show();
                return;
            }

            $blocks.hide();
            $blocks.slice(0, perPage).show();

            $(paginationContainer).pagination({
                items: total,
                itemsOnPage: perPage,
                cssStyle: 'light-theme',
                prevText: 'Предыдущая',
                nextText: 'Следующая',
                onPageClick: function(pageNumber, event) {
                    var start = (pageNumber - 1) * perPage;
                    var end = start + perPage;
                    $blocks.hide().slice(start, end).show();
                }
            });
        }

        // Вызов функции пагинации для блочного представления
        paginateContainer('#all-deals-container', '#all-deals-pagination', 6);

        var $editModal = $('#editModal'),
            $editForm = $('#editForm');

        // Функция инициализации Select2, вызывается после загрузки модального окна
        function initSelect2() {
            $('.select2-field:not(.select2-hidden-accessible)').each(function() {
                // Находим родительский контейнер для каждого Select2
                var $parent = $(this).closest('.form-group-deal');
                if (!$parent.length) {
                    $parent = $(this).parent();
                }

                // Устанавливаем position: relative для правильного позиционирования
                $parent.css({
                    'position': 'relative',
                    'width': '100%',
                    'overflow': 'visible'
                });

                // Сохраняем ширину родителя для использования в dropdownCssClass
                var parentWidth = $parent.width();

                // Добавляем уникальный идентификатор для родительского контейнера
                var uniqueId = 'parent-' + Math.random().toString(36).substr(2, 9);
                $parent.attr('data-select2-id', uniqueId);

                // Инициализируем Select2 с указанием родителя для dropdown
                $(this).select2({
                    width: '100%',
                    placeholder: $(this).attr('placeholder') || "Выберите значение",
                    allowClear: true,
                    dropdownParent: $parent, // Важно: dropdownParent указывает на родителя
                    language: 'ru',
                    // Добавляем CSS класс для дальнейшей стилизации
                    dropdownCssClass: 'select2-dropdown-in-parent'
                });

                // Применяем фиксированную ширину к выпадающему списку после открытия
                $(this).on('select2:open', function() {
                    setTimeout(function() {
                        $('.select2-container--open .select2-dropdown').css({
                            'width': parentWidth + 'px',
                            'min-width': '100%',
                            'max-width': parentWidth + 'px'
                        });
                        
                        // Автоматически ставим фокус на поле поиска при открытии Select2
                        var searchField = $('.select2-container--open .select2-search__field');
                        if (searchField.length) {
                            searchField.focus();
                        }
                    }, 0);
                });
            });
        }

        var modalCache = {}; // Объект для кэширования модальных окон

        // Обработчик клика для открытия модального окна с данными сделки
        $('.edit-deal-btn').on('click', function() {
            var dealId = $(this).data('id');
            var $modalContainer = $("#dealModalContainer");

            // Проверяем, есть ли модальное окно в кэше
            if (modalCache[dealId]) {
                // Если есть, показываем его из кэша
                $modalContainer.html(modalCache[dealId]);
                setTimeout(function() {
                    initSelect2();
                }, 300);
                $("#editModal").show().addClass('show');
                initModalFunctions();
            } else {
                // Если нет, загружаем с сервера
                // Показываем индикатор загрузки
                $modalContainer.html('<div class="loading">Загрузка...</div>');

                $.ajax({
                    url: "/deal/" + dealId + "/modal",
                    type: "GET",
                    success: function(response) {
                        // Сохраняем модальное окно в кэш
                        modalCache[dealId] = response.html;

                        // Вставляем HTML модального окна
                        $modalContainer.html(response.html);

                        // Устанавливаем задержку для корректной инициализации Select2
                        setTimeout(function() {
                            initSelect2();
                        }, 300);

                        // Показываем модальное окно
                        $("#editModal").show().addClass('show');

                        // Обработчики закрытия модального окна
                        $('#closeModalBtn').on('click', function() {
                            $("#editModal").removeClass('show').hide();
                        });

                        $("#editModal").on('click', function(e) {
                            if (e.target === this) $(this).removeClass('show')
                                .hide();
                        });

                        // Инициализация других JS-функций для модального окна
                        initModalFunctions();
                    },
                    error: function(xhr, status, error) {
                        console.error("Ошибка загрузки данных сделки:", status, error);
                        alert(
                            "Ошибка загрузки данных сделки. Попробуйте обновить страницу."
                        );
                    },
                    complete: function() {
                        // Скрываем индикатор загрузки
                        $('.loading').remove();
                    }
                });
            }

            // Динамическое изменение URL
            history.pushState(null, null, "#editDealModal");
        });

        // Обработчик закрытия модального окна
        $('#dealModalContainer').on('click', '#closeModalBtn', function() {
            $("#editModal").removeClass('show').hide();
            history.pushState("", document.title, window.location.pathname + window.location.search);
        });

        $('#dealModalContainer').on('click', '#editModal', function(e) {
            if (e.target === this) {
                $(this).removeClass('show').hide();
                history.pushState("", document.title, window.location.pathname + window.location
                    .search);
            }
        });

        // Функция инициализации дополнительных JS-функций для модального окна
        function initModalFunctions() {
            console.log('initModalFunctions: делегирование к единой системе вкладок');
            
            // Используем единую систему вкладок
            if (typeof window.TabsSystem !== 'undefined') {
                window.TabsSystem.reinit();
            }
            
            // Инициализируем маски телефона для полей в модальном окне
            if (typeof window.initPhoneMasks === 'function') {
                console.log('initModalFunctions: инициализация масок телефона');
                window.initPhoneMasks();
            }

            // Обработчик отправки формы ленты
            $("#feed-form").on("submit", function(e) {
                e.preventDefault();
                var content = $("#feed-content").val().trim();
                if (!content) {
                    alert("Введите текст сообщения!");
                    return;
                }
                var dealId = $("#dealIdField").val();
                if (dealId) {
                    $.ajax({
                        url: "/deal/" + dealId + "/feed",
                        type: "POST",
                        data: {
                            _token: $('meta[name="csrf-token"]').attr('content'),
                            content: content
                        },
                        success: function(response) {
                            // ...existing code...
                        },
                        error: function(xhr) {
                            alert("Ошибка при добавлении записи: " + xhr.responseText);
                        }
                    });
                } else {
                    alert("Не удалось определить сделку. Пожалуйста, обновите страницу.");
                }
            });

            // Обработчик для файловых полей
            $('input[type="file"]').on('change', function() {
                var file = this.files[0];
                var fileName = file ? file.name : "";
                var fieldName = $(this).attr('id');
                var linkDiv = $('#' + fieldName + 'Link');

                if (fileName) {
                    linkDiv.html('<a href="' + URL.createObjectURL(file) + '" target="_blank">' +
                        fileName + '</a>');
                }
            });
        }

        $('#closeModalBtn').on('click', function() {
            $("#editModal").removeClass('show').hide();
        });
        $("#editModal").on('click', function(e) {
            if (e.target === this) $(this).removeClass('show').hide();
        });

        $.getJSON('/cities.json', function(data) {
            var grouped = {};
            $.each(data, function(i, item) {
                grouped[item.region] = grouped[item.region] || [];
                grouped[item.region].push({
                    id: item.city,
                    text: item.city
                });
            });
            var selectData = $.map(grouped, function(cities, region) {
                return {
                    text: region,
                    children: cities
                };
            });
            
            // Получаем текущее значение поля client_timezone
            var currentTimezone = $('#client_timezone').val() || $('#client_timezone').data('current-value') || $('#client_timezone').attr('data-current-value');
            console.log('Текущий часовой пояс для установки:', currentTimezone);
            
            $('#client_timezone, #cityField').select2({
                data: selectData,
                placeholder: "-- Выберите город/часовой пояс --",
                allowClear: true,
                minimumInputLength: 1,
                dropdownParent: $('#editModal').find('.modal-content')
            });
            
            // Устанавливаем текущее значение, если оно есть
            if (currentTimezone && currentTimezone.trim() !== '') {
                setTimeout(function() {
                    // Проверяем, есть ли уже опция с таким значением
                    var existingOption = $('#client_timezone option[value="' + currentTimezone + '"]');
                    if (existingOption.length === 0) {
                        // Создаем новую опцию, если её нет
                        var newOption = new Option(currentTimezone, currentTimezone, true, true);
                        $('#client_timezone').append(newOption);
                    } else {
                        // Устанавливаем существующую опцию как выбранную
                        $('#client_timezone').val(currentTimezone);
                    }
                    $('#client_timezone').trigger('change');
                    console.log('Город установлен в deals-scripts:', currentTimezone);
                }, 150);
            }
        }).fail(function(err) {
            console.error("Ошибка загрузки городов", err);
        });

        $('#responsiblesField').select2({
            placeholder: "Выберите ответственных",
            allowClear: true,
            dropdownParent: $('#editModal').find('.modal-content')
        });
        
        $('.select2-field').select2({
            width: '100%',
            placeholder: "Выберите значение",
            allowClear: true,
            dropdownParent: $('#editModal').find('.modal-content')
        });

        $("#feed-form").on("submit", function(e) {
            e.preventDefault();
            var content = $("#feed-content").val().trim();
            if (!content) {
                alert("Введите текст сообщения!");
                return;
            }
            var dealId = $("#dealIdField").val();
            if (dealId) {
                $.ajax({
                    url: "{{ url('/deal') }}/" + dealId + "/feed",
                    type: "POST",
                    data: {
                        _token: "{{ csrf_token() }}",
                        content: content
                    },
                    success: function(response) {
                        $("#feed-content").val("");
                        var avatarUrl = response.avatar_url ? response.avatar_url :
                            "/storage/icon/profile.svg";
                        $("#feed-posts-container").prepend(`
                        <div class="feed-post">
                            <div class="feed-post-avatar">
                                <img src="${avatarUrl}" alt="${response.user_name}">
                            </div>
                            <div class="feed-post-text">
                                <div class="feed-author">${response.user_name}</div>
                                <div class="feed-content">${response.content}</div>
                                <div class="feed-date">${response.date}</div>
                            </div>
                        </div>
                    `);
                    },
                    error: function(xhr) {
                        alert("Ошибка при добавлении записи: " + xhr.responseText);
                    }
                });
            } else {
                alert("Не удалось определить сделку. Пожалуйста, обновите страницу.");
            }
        });

        // Обработчик отправки формы редактирования сделки с поддержкой AJAX
        $('#dealModalContainer').on('submit', '#editForm', function(e) {
            e.preventDefault();
            var form = $(this);
            var url = form.attr('action');
            var formData = new FormData(this);

            // СТАРАЯ система загрузки файлов ОТКЛЮЧЕНА - новая система обрабатывает все автоматически
            var hasFiles = false;
            var fileInputs = form.find('input[type="file"]');

            fileInputs.each(function() {
                if (this.files && this.files.length > 0) {
                    hasFiles = true;
                    return false; // прерываем цикл, если нашли хотя бы один файл
                }
            });

            // СТАРАЯ анимация загрузки УДАЛЕНА - новая система управляет всем процессом

            $.ajax({
                url: url,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    // СТАРАЯ логика прогресса УДАЛЕНА - новая система управляет всем процессом

                    $("#editModal").removeClass('show').hide();

                    if (response.success) {
                        // Показываем сообщение об успехе
                        $('<div class="success-message">Сделка успешно обновлена</div>')
                            .appendTo('body')
                            .fadeIn('fast')
                            .delay(3000)
                            .fadeOut('slow', function() {
                                $(this).remove();
                            });

                        // Если статус изменен на "Проект завершен", проверяем необходимость оценок
                        if (response.status_changed_to_completed ||
                            (response.deal && response.deal.status === 'Проект завершен')) {
                            
                            console.log('[Сделка] Статус изменен на "Проект завершен", сохраняем ID сделки:', response.deal.id);
                            
                            // Сохраняем ID завершенной сделки в localStorage для проверки рейтингов
                            localStorage.setItem('completed_deal_id', response.deal.id);
                            
                            // Вызываем событие обновления сделки
                            window.dispatchEvent(new CustomEvent('dealUpdated', {
                                detail: {
                                    dealId: response.deal.id,
                                    statusChanged: true
                                }
                            }));

                            // Непосредственно вызываем функцию проверки оценок
                            if (typeof window.runRatingCheck === 'function') {
                                console.log('[Сделка] Вызов runRatingCheck для сделки:', response.deal.id);
                                window.runRatingCheck(response.deal.id);
                            } else if (typeof window.checkPendingRatings === 'function') {
                                setTimeout(() => {
                                    console.log('[Сделка] Проверка необходимости оценок для сделки:', response.deal.id);
                                    window.checkPendingRatings(response.deal.id);
                                }, 500);
                            } else {
                                console.warn('[Сделка] Функции рейтингов не найдены, перезагрузка страницы');
                                // Если функции рейтингов не найдены, перезагружаем страницу через 2 секунды
                                setTimeout(function() {
                                    location.reload();
                                }, 2000);
                            }
                        } else {
                            // Обновляем страницу только если статус НЕ изменился на "Проект завершен"
                            setTimeout(function() {
                                location.reload();
                            }, 1000);
                        }
                    }
                },
                error: function(xhr) {
                    // Скрываем анимацию загрузки в случае ошибки
                    if (hasFiles) {
                        const loader = document.getElementById('fullscreen-loader');
                        loader.classList.remove('show');
                    }
                    alert('Произошла ошибка при обновлении сделки.');
                    console.error(xhr.responseText);
                }
            });
        });
    });
</script>
