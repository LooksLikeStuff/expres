<!-- Скрипты для модального окна и его компонентов -->
<script>
$(function() {
    // Инициализация всех Select2 элементов в модальном окне
    initModalSelects();
    
    // Инициализация загрузки документов
    initDocumentsUpload();
    
    // Система вкладок инициализируется автоматически через unified_tabs_system.blade.php
});

/**
 * УСТАРЕВШИЕ ФУНКЦИИ - оставлены для обратной совместимости
 * Фактическая логика переехала в unified_tabs_system.blade.php
 */

/**
 * @deprecated Используется unified_tabs_system.blade.php
 */
function initTabHandlers() {
    console.log('initTabHandlers: переадресация к window.TabsSystem');
    if (window.TabsSystem) {
        window.TabsSystem.init();
    }
}

/**
 * @deprecated Используется unified_tabs_system.blade.php
 */
function showModule(selector) {
    console.log('showModule: переадресация к window.TabsSystem');
    if (window.showModule) {
        window.showModule(selector);
    }
}

/**
 * @deprecated Используется unified_tabs_system.blade.php
 */
function showDocumentsTab() {
    console.log('showDocumentsTab: переадресация к window.TabsSystem');
    if (window.showDocumentsTab) {
        window.showDocumentsTab();
    }
}

/**
 * Инициализация загрузки документов
 * УДАЛЕНО - новая система больших файлов обрабатывает все автоматически
 */
function initDocumentsUpload() {
    // Новая система large-file-upload.js обрабатывает все события автоматически
    console.log('initDocumentsUpload: используется новая система больших файлов');
}
    
    // УДАЛЕН СТАРЫЙ КОД ЗАГРУЗКИ ДОКУМЕНТОВ
    // Новая система большых файлов (large-file-upload.js) обрабатывает все события автоматически
}

/**
 * Обновление списка документов после загрузки
 */
function updateDocumentsList(documents) {
    if (!documents || documents.length === 0) return;
    
    // Проверяем существует ли контейнер для списка документов
    var documentsList = $('.documents-list');
    if (documentsList.length === 0) {
        // Если нет, создаем его
        $('.documents-container').append(
            '<div class="documents-list">' +
            '<h4>Загруженные документы</h4>' +
            '<ul class="document-items"></ul>' +
            '</div>'
        );
        
        // Удаляем сообщение о том, что нет документов
        $('.no-documents').remove();
    }
    
    var documentItems = $('.document-items');
    
    // Добавляем новые документы в список
    documents.forEach(function(doc) {
        var extension = doc.extension || 'unknown';
        var fileName = doc.name || 'document';
        var fileNameWithoutExt = fileName.split('.').slice(0, -1).join('.') || fileName;
        
        documentItems.append(
            '<li class="document-item">' +
            '<a href="' + doc.url + '" target="_blank" class="document-link" download="' + fileName + '">' +
            '<i class="fas ' + (doc.icon || 'fa-file') + '"></i>' +
            '<span class="document-name">' + fileNameWithoutExt + '</span>' +
            '<span class="document-extension">.' + extension + '</span>' +
            '</a>' +
            '</li>'
        );
    });
}

// Функция для копирования регистрационной ссылки
function copyRegistrationLink(regUrl) {
    if (regUrl && regUrl !== '#') {
        navigator.clipboard.writeText(regUrl).then(function() {
            alert('Регистрационная ссылка скопирована в буфер обмена');
        }).catch(function(err) {
            console.error('Ошибка копирования: ', err);
        });
    } else {
        alert('Регистрационная ссылка отсутствует');
    }
}

// Функция для подтверждения удаления сделки
function confirmDeleteDeal(dealId) {
    if (confirm('ВНИМАНИЕ! Вы собираетесь удалить сделку. Это действие нельзя отменить.\n\nВы уверены?')) {
        console.log('Отправка запроса на удаление сделки #' + dealId);
        
        // Создаем форму для отправки запроса на удаление
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `/deal/${dealId}/delete`;
        form.style.display = 'none';
        
        // Добавляем CSRF токен и метод DELETE
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        const csrfInput = document.createElement('input');
        csrfInput.type = 'hidden';
        csrfInput.name = '_token';
        csrfInput.value = csrfToken;
        form.appendChild(csrfInput);
        
        const methodInput = document.createElement('input');
        methodInput.type = 'hidden';
        methodInput.name = '_method';
        methodInput.value = 'DELETE';
        form.appendChild(methodInput);
        
        // Добавляем форму в документ и отправляем
        document.body.appendChild(form);
        form.submit();
    }
}

/**
 * Инициализация всех Select2 элементов в модальном окне
 */
function initModalSelects() {
    // Специальная инициализация для поля client_timezone
    if ($('#client_timezone').length) {
        $.getJSON('/cities.json', function(data) {
            // Группировка городов по регионам
            var grouped = {};
            $.each(data, function(i, item) {
                grouped[item.region] = grouped[item.region] || [];
                grouped[item.region].push({
                    id: item.city,
                    text: item.city
                });
            });
            
            // Преобразование в формат, понятный Select2
            var selectData = $.map(grouped, function(cities, region) {
                return {
                    text: region,
                    children: cities
                };
            });
            
            // Получаем текущее значение поля client_timezone
            var currentTimezone = $('#client_timezone').val() || $('#client_timezone').data('current-value');
            
            // Находим родительский контейнер для поля client_timezone
            var $parent = $('#client_timezone').closest('.form-group-deal');
            if (!$parent.length) {
                $parent = $('#client_timezone').parent();
            }
            
            $parent.css('position', 'relative');
            var parentWidth = $parent.width();
            
            $('#client_timezone').select2({
                data: selectData,
                placeholder: "-- Выберите город/часовой пояс --",
                allowClear: true,
                minimumInputLength: 1,
                dropdownParent: $parent,
                language: {
                    inputTooShort: function() { return "Введите хотя бы 1 символ для поиска"; },
                    noResults: function() { return "Город не найден"; },
                    searching: function() { return "Поиск..."; }
                }
            }).on('select2:open', function() {
                setTimeout(function() {
                    var $dropdown = $('.select2-container--open .select2-dropdown');
                    $dropdown.css({
                        'width': parentWidth + 'px',
                        'min-width': '100%',
                        'max-width': parentWidth + 'px'
                    });
                }, 0);
            });
            
            // Устанавливаем текущее значение, если оно есть
            if (currentTimezone) {
                $('#client_timezone').val(currentTimezone).trigger('change');
            }
        }).fail(function(error) {
            console.error("Ошибка загрузки списка городов:", error);
        });
    }

    // Инициализация для стандартных select полей (исключая client_timezone)
    $('#editModal select').not('.select2-specialist, .select2-field, #client_timezone').each(function() {
        // Находим родительский элемент .form-group-deal
        var $parent = $(this).closest('.form-group-deal');
        if (!$parent.length) {
            $parent = $(this).parent();
        }
        
        // Устанавливаем position: relative для родителя
        $parent.css('position', 'relative');
        
        // Сохраняем ширину родителя
        var parentWidth = $parent.width();
        
        $(this).select2({
            dropdownParent: $parent, // Важно: привязка к родительскому элементу
            width: '100%',
            minimumResultsForSearch: 6,
            language: {
                noResults: function() { return "Нет данных для выбора"; }
            }
        }).on('select2:open', function() {
            // Устанавливаем стили для контейнера выпадающего списка
            setTimeout(function() {
                var $dropdown = $('.select2-container--open .select2-dropdown');
                $dropdown.css({
                    'width': parentWidth + 'px',
                    'min-width': '100%',
                    'max-width': parentWidth + 'px'
                });
            }, 0);
        });
    });

    // Инициализация select2 для координаторов
    $('.select2-coordinator-search').each(function() {
        var $select = $(this);
        var placeholder = 'Выберите координатора';
        
        // Находим родительский элемент .form-group-deal
        var $parent = $select.closest('.form-group-deal');
        if (!$parent.length) {
            $parent = $select.parent();
        }
        
        // Устанавливаем position: relative для родителя
        $parent.css('position', 'relative');
        var parentWidth = $parent.width();
        
        $select.select2({
            dropdownParent: $parent,
            width: '100%',
            placeholder: placeholder,
            allowClear: true,
            minimumInputLength: 0,
            language: {
                inputTooShort: function() { return "Введите минимум 2 символа для поиска"; },
                searching: function() { return "Поиск..."; },
                noResults: function() { return "Координаторы не найдены"; }
            },
            ajax: {
                url: '/search-users',
                dataType: 'json',
                delay: 250,
                data: function(params) {
                    return {
                        q: params.term || '',
                        status: 'coordinator'
                    };
                },
                processResults: function(data) {
                    return {
                        results: $.map(data, function(user) {
                            return {
                                id: user.id,
                                text: user.name,
                                email: user.email || ''
                            };
                        })
                    };
                },
                cache: true
            },
            templateResult: function(user) {
                if (!user.id) return user.text;
                var $result = $('<div class="select2-user-result">' +
                    '<div class="user-name"><strong>' + user.text + '</strong></div>' +
                    (user.email ? '<div class="user-email text-muted">' + user.email + '</div>' : '') +
                    '</div>');
                return $result;
            },
            templateSelection: function(user) {
                return user.text || user.id;
            }
        }).on('select2:open', function() {
            setTimeout(function() {
                var $dropdown = $('.select2-container--open .select2-dropdown');
                $dropdown.css({
                    'width': parentWidth + 'px',
                    'min-width': '100%',
                    'max-width': parentWidth + 'px'
                });
            }, 0);
        });
    });

    // Инициализация select2 для партнеров
    $('.select2-partner-search').each(function() {
        var $select = $(this);
        var placeholder = 'Выберите партнера';
        
        // Находим родительский элемент .form-group-deal
        var $parent = $select.closest('.form-group-deal');
        if (!$parent.length) {
            $parent = $select.parent();
        }
        
        // Устанавливаем position: relative для родителя
        $parent.css('position', 'relative');
        var parentWidth = $parent.width();
        
        $select.select2({
            dropdownParent: $parent,
            width: '100%',
            placeholder: placeholder,
            allowClear: true,
            minimumInputLength: 0,
            language: {
                inputTooShort: function() { return "Введите минимум 2 символа для поиска"; },
                searching: function() { return "Поиск..."; },
                noResults: function() { return "Партнеры не найдены"; }
            },
            ajax: {
                url: '/search-users',
                dataType: 'json',
                delay: 250,
                data: function(params) {
                    return {
                        q: params.term || '',
                        status: 'partner'
                    };
                },
                processResults: function(data) {
                    return {
                        results: $.map(data, function(user) {
                            return {
                                id: user.id,
                                text: user.name,
                                email: user.email || ''
                            };
                        })
                    };
                },
                cache: true
            },
            templateResult: function(user) {
                if (!user.id) return user.text;
                var $result = $('<div class="select2-user-result">' +
                    '<div class="user-name"><strong>' + user.text + '</strong></div>' +
                    (user.email ? '<div class="user-email text-muted">' + user.email + '</div>' : '') +
                    '</div>');
                return $result;
            },
            templateSelection: function(user) {
                return user.text || user.id;
            }
        }).on('select2:open', function() {
            setTimeout(function() {
                var $dropdown = $('.select2-container--open .select2-dropdown');
                $dropdown.css({
                    'width': parentWidth + 'px',
                    'min-width': '100%',
                    'max-width': parentWidth + 'px'
                });
            }, 0);
        });
    });

    // Инициализация select2 для специалистов (архитектор, дизайнер, визуализатор)
    $('.select2-specialist').each(function() {
        var $select = $(this);
        var role = $select.data('role');
        var placeholder = $select.data('placeholder') || 'Выберите специалиста';
        
        // Находим родительский элемент .form-group-deal
        var $parent = $select.closest('.form-group-deal');
        if (!$parent.length) {
            $parent = $select.parent();
        }
        
        // Устанавливаем position: relative для родителя
        $parent.css('position', 'relative');
        var parentWidth = $parent.width();
        
        $select.select2({
            dropdownParent: $parent, // Привязка к родительскому элементу
            width: '100%',
            placeholder: placeholder,
            minimumInputLength: 1,
            language: {
                inputTooShort: function() { return "Введите минимум 2 символа для поиска"; },
                searching: function() { return "Поиск..."; },
                noResults: function() { return "Ничего не найдено"; }
            },
            ajax: {
                url: '/search-users',
                dataType: 'json',
                delay: 250,
                data: function(params) {
                    return {
                        q: params.term,
                        status: role
                    };
                },
                processResults: function(data) {
                    return {
                        results: $.map(data, function(user) {
                            return {
                                id: user.id,
                                text: user.name,
                                rating: user.rating || 0
                            };
                        })
                    };
                },
                cache: true
            },
            templateResult: formatSpecialistResult,
            templateSelection: formatSpecialistSelection
        }).on('select2:open', function() {
            setTimeout(function() {
                var $dropdown = $('.select2-container--open .select2-dropdown');
                $dropdown.css({
                    'width': parentWidth + 'px',
                    'min-width': '100%',
                    'max-width': parentWidth + 'px'
                });
            }, 0);
        });
    });

    // Инициализация select2 для городов/часовых поясов
    $('.select2-cities-search').each(function() {
        var $select = $(this);
        var placeholder = $select.data('placeholder') || 'Выберите город/часовой пояс';
        var currentValue = $select.data('current-value') || $select.val() || $select.attr('data-current-value');
        
        console.log('Инициализация города для поля:', $select.attr('id'), 'Текущее значение:', currentValue);
        
        // Находим родительский элемент .form-group-deal
        var $parent = $select.closest('.form-group-deal');
        if (!$parent.length) {
            $parent = $select.parent();
        }
        
        // Устанавливаем position: relative для родителя
        $parent.css('position', 'relative');
        var parentWidth = $parent.width();
        
        // Загружаем данные городов из JSON файла
        $.getJSON('/cities.json')
            .done(function(citiesData) {
                console.log('Данные городов загружены для Select2:', citiesData.length);
                
                // Группировка городов по регионам для лучшей навигации
                var grouped = {};
                $.each(citiesData, function(i, item) {
                    var region = item.region || 'Другие города';
                    grouped[region] = grouped[region] || [];
                    grouped[region].push({
                        id: item.city,
                        text: item.city
                    });
                });
                
                // Преобразование в формат, понятный Select2
                var selectData = $.map(grouped, function(cities, region) {
                    return {
                        text: region,
                        children: cities
                    };
                });
                
                // Инициализируем Select2
                $select.select2({
                    data: selectData,
                    dropdownParent: $parent,
                    width: '100%',
                    placeholder: placeholder,
                    allowClear: true,
                    minimumInputLength: 1,
                    language: {
                        inputTooShort: function() { return "Введите минимум 1 символ для поиска"; },
                        searching: function() { return "Поиск..."; },
                        noResults: function() { return "Город не найден"; }
                    },
                    templateResult: function(city) {
                        if (city.loading) return city.text;
                        
                        // Создаем шаблон результата
                        var $result = $('<div class="select2-city-result">');
                        $result.text(city.text);
                        return $result;
                    },
                    templateSelection: function(city) {
                        return city.text || city.id;
                    }
                }).on('select2:open', function() {
                    setTimeout(function() {
                        var $dropdown = $('.select2-container--open .select2-dropdown');
                        $dropdown.css({
                            'width': parentWidth + 'px',
                            'min-width': '100%',
                            'max-width': parentWidth + 'px'
                        });
                    }, 0);
                });

                // Устанавливаем текущее значение, если оно есть
                if (currentValue && currentValue.trim() !== '') {
                    console.log('Устанавливаем текущий город:', currentValue);
                    
                    // Ждем немного, чтобы Select2 полностью инициализировался
                    setTimeout(function() {
                        // Сначала пробуем установить значение напрямую
                        $select.val(currentValue);
                        
                        // Проверяем, было ли значение установлено
                        var selectedValue = $select.val();
                        if (selectedValue === currentValue) {
                            $select.trigger('change');
                            console.log('Город установлен успешно через val():', currentValue);
                        } else {
                            // Если не удалось, ищем город в данных и создаем новую опцию
                            var cityFound = false;
                            $.each(citiesData, function(i, item) {
                                if (item.city === currentValue) {
                                    cityFound = true;
                                    
                                    // Создаем новую опцию и добавляем её
                                    var newOption = new Option(currentValue, currentValue, true, true);
                                    $select.append(newOption);
                                    $select.trigger('change');
                                    
                                    console.log('Город найден в данных и установлен:', currentValue);
                                    return false; // break
                                }
                            });
                            
                            if (!cityFound) {
                                console.log('Город не найден в JSON данных, но устанавливаем как есть:', currentValue);
                                // Все равно создаем опцию для значения из базы данных
                                var newOption = new Option(currentValue, currentValue, true, true);
                                $select.append(newOption);
                                $select.trigger('change');
                            }
                        }
                    }, 100);
                } else {
                    console.log('Текущий город не задан или пустой');
                }
            })
            .fail(function(xhr, status, error) {
                console.error('Ошибка загрузки данных городов:', error);
                
                // Инициализируем Select2 без данных в случае ошибки
                $select.select2({
                    dropdownParent: $parent,
                    width: '100%',
                    placeholder: placeholder,
                    allowClear: true,
                    language: {
                        noResults: function() {
                            return "Ошибка загрузки данных городов";
                        }
                    }
                });
            });
    });
}

// Формат отображения результата поиска специалиста
function formatSpecialistResult(specialist) {
    if (!specialist.id) return specialist.text;
    
    // Создаем рейтинг в виде звездочек
    var rating = parseFloat(specialist.rating) || 0;
    var ratingHtml = '';
    
    // Округляем рейтинг до ближайшей половины звезды
    var fullStars = Math.floor(rating);
    var halfStar = (rating - fullStars >= 0.5) ? 1 : 0;
    var emptyStars = 5 - fullStars - halfStar;
    
    // Добавляем полные звезды
    for (var i = 0; i < fullStars; i++) {
        ratingHtml += '<i class="fas fa-star text-warning"></i>';
    }
    
    // Добавляем половину звезды если нужно
    if (halfStar) {
        ratingHtml += '<i class="fas fa-star-half-alt text-warning"></i>';
    }
    
    // Добавляем пустые звезды
    for (var i = 0; i < emptyStars; i++) {
        ratingHtml += '<i class="far fa-star text-warning"></i>';
    }
    
    var $result = $(
        '<div class="select2-specialist-result">' +
            '<div class="specialist-name">' + specialist.text + '</div>' +
            '<div class="specialist-rating">' + ratingHtml + ' <span class="rating-value">(' + rating.toFixed(1) + ')</span></div>' +
        '</div>'
    );
    
    return $result;
}

// Формат отображения выбранного специалиста
function formatSpecialistSelection(specialist) {
    if (!specialist.id) return specialist.text;
    
    // Если есть рейтинг, добавляем его к имени
    if (specialist.rating) {
        return specialist.text + ' ⭐ ' + parseFloat(specialist.rating).toFixed(1);
    }
    
    return specialist.text;
}

// Вызываем инициализацию селектов после полной загрузки модального окна
$(document).on('shown.bs.modal', '#editModal', function() {
    setTimeout(function() {
        initModalSelects();
        // Система вкладок инициализируется автоматически
    }, 100);
});

// Также проверяем, чтобы инициализация срабатывала при открытии модального окна через AJAX
$(document).ajaxComplete(function(event, xhr, settings) {
    if (settings.url.includes('/deal/') && settings.url.includes('/modal')) {
        setTimeout(function() {
            if ($('#editModal').is(':visible')) {
                initModalSelects();
                // Система вкладок переинициализируется автоматически
            }
        }, 200);
    }
});

// Добавляем обработчик изменения размера окна
$(window).resize(function() {
    if ($('.select2-container--open').length) {
        $('.select2-hidden-accessible').select2('close');
        setTimeout(function() {
            $('.select2-hidden-accessible').select2('open');
        }, 100);
    }
});

// Инициализация Select2 для поля выбора города
$(document).ready(function() {
    // Загрузка данных городов из JSON-файла
    $.getJSON('/cities.json', function(data) {
        // Группировка городов по регионам
        var grouped = {};
        $.each(data, function(i, item) {
            grouped[item.region] = grouped[item.region] || [];
            grouped[item.region].push({
                id: item.city,
                text: item.city
            });
        });
        
        // Преобразование в формат, понятный Select2
        var selectData = $.map(grouped, function(cities, region) {
            return {
                text: region,
                children: cities
            };
        });
        
        // Находим родительский контейнер для поля client_city
        var $parent = $('#client_city').closest('.form-group-deal');
        
        // Устанавливаем position: relative для корректного позиционирования dropdown
        $parent.css({
            'position': 'relative',
           
            'overflow': 'visible'
        });
        
        // Сохраняем ширину родителя для использования в настройке dropdown
        var parentWidth = $parent.width();
        
        // Инициализация Select2 для поля client_city
        $('#client_city').select2({
            data: selectData,
            placeholder: "Выберите город",
            allowClear: true,
            minimumInputLength: 1, // Включаем поиск
            dropdownParent: $parent, // Важно: указываем родительский контейнер для dropdown
            language: {
                inputTooShort: function() {
                    return "Введите хотя бы 1 символ для поиска";
                },
                noResults: function() {
                    return "Город не найден";
                },
                searching: function() {
                    return "Поиск...";
                }
            }
        });
        
        // При открытии dropdown устанавливаем корректную ширину
        $('#client_city').on('select2:open', function() {
            setTimeout(function() {
                $parent.find('.select2-dropdown').css({
                    'width': parentWidth + 'px',
                    'max-width': parentWidth + 'px'
                });
            }, 0);
        });
        
        // Если в поле уже есть значение - устанавливаем его
        var currentCity = $('#client_city').data('current-value');
        if (currentCity) {
            // Поиск города в данных
            var cityFound = false;
            $.each(selectData, function(i, region) {
                $.each(region.children, function(j, city) {
                    if (city.text === currentCity) {
                        // Создаем опцию и устанавливаем как выбранную
                        var option = new Option(city.text, city.id, true, true);
                        $('#client_city').append(option).trigger('change');
                        cityFound = true;
                        return false;
                    }
                });
                if (cityFound) return false;
            });
            
            // Если город не найден в списке, добавляем его как кастомное значение
            if (!cityFound) {
                var option = new Option(currentCity, currentCity, true, true);
                $('#client_city').append(option).trigger('change');
            }
        }
    }).fail(function(error) {
        console.error("Ошибка загрузки списка городов:", error);
        // В случае ошибки загрузки городов, преобразуем поле в обычный input
        convertToInput();
    });
    
    // Функция для преобразования в обычное текстовое поле в случае ошибки загрузки
    function convertToInput() {
        var currentValue = $('#client_city').data('current-value') || '';
        var placeholder = $('#client_city').attr('placeholder') || 'Введите город';
        
        // Создаем текстовое поле вместо select
        var $input = $('<input>', {
            type: 'text',
            id: 'client_city',
            name: 'client_city',
            class: 'form-control',
            value: currentValue,
            placeholder: placeholder
        });
        
        // Заменяем select на input
        $('#client_city').replaceWith($input);
    }
});
</script>
