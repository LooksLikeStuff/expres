<!-- Скрипты для обработки модальных окон и таблиц -->
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
            $editForm = $('#editForm');        // Используем функцию инициализации Select2 из deals-compatibility-fixes.js
        function initSelect2() {
            // Проверяем наличие глобальной функции initializeAllSelect2Elements
            if (typeof window.initializeAllSelect2Elements === 'function') {
                window.initializeAllSelect2Elements();
            } else {
                console.warn('Функция initializeAllSelect2Elements не найдена. Возможно, скрипт не загружен.');
            }
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
            console.log('initModalFunctions: использование единой системы вкладок');
            
            // Инициализируем единую систему вкладок
            if (typeof window.TabsSystem !== 'undefined') {
                window.TabsSystem.reinit();
            } else if (typeof initTabHandlers === 'function') {
                initTabHandlers();
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
        
        // Обработка отправки формы редактирования сделки с поддержкой AJAX
        $('#dealModalContainer').on('submit', '#editForm', function(e) {
            e.preventDefault();
            var form = $(this);
            var url = form.attr('action');
            var formData = new FormData(this);

            // СТАРАЯ система загрузки файлов ОТКЛЮЧЕНА
            // Новая система больших файлов (large-file-upload.js) обрабатывает все автоматически
            var hasFiles = false;
            var fileInputs = form.find('input[type="file"]');

            fileInputs.each(function() {
                if (this.files && this.files.length > 0) {
                    hasFiles = true;
                    return false; // прерываем цикл, если нашли хотя бы один файл
                }
            });

            // ПРИМЕЧАНИЕ: Старая логика загрузки отключена, новая система обрабатывает файлы автоматически
            // if (hasFiles) { ... } - УДАЛЕНО

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

        // Добавляем код для проверки завершенных сделок при загрузке страницы
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
                    console.log('[Сделки] Проверка оценок для первой найденной сделки:', completedDealIds[
                        0]);
                    window.checkPendingRatings(completedDealIds[0]);
                }
            }, 800);
        });
    });
</script>
