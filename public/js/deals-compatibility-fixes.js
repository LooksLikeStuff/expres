/**
 * Исправления для несовместимостей и багов в JS скриптах сделок и модальных окон
 * Подключать этот файл нужно после всех компонентов страницы сделок
 */

// Единая функция для инициализации Select2 с проверкой на предыдущую инициализацию
function initializeAllSelect2Elements() {
    // Проверяем, загружен ли Select2
    if (typeof $.fn.select2 !== 'undefined') {
        console.log('Универсальная инициализация Select2 компонентов');
        
        // Находим все элементы, которые еще не были инициализированы
        $('.select2-search:not(.select2-hidden-accessible), .select2-field:not(.select2-hidden-accessible), .select2-specialist:not(.select2-hidden-accessible), .select2-coordinator-search:not(.select2-hidden-accessible), .select2-partner-search:not(.select2-hidden-accessible), .select2-cities-search:not(.select2-hidden-accessible)').each(function() {
            // Находим родительский контейнер
            var $parent = $(this).closest('.filter-group, .form-group-deal');
            if (!$parent.length) {
                $parent = $(this).parent();
            }
            
            // Установка position: relative для корректного позиционирования
            $parent.css({
                'position': 'relative',
               
             
                'overflow': 'visible'
            });

            // Сохраняем ширину родителя
            var parentWidth = $parent.width();
            
            // Опции по умолчанию
            var defaultOptions = {
                width: '100%',
                placeholder: $(this).attr('placeholder') || $(this).data('placeholder') || "Выберите значение",
                allowClear: true,
                dropdownParent: $parent,
                language: 'ru'
            };
            
            // Дополнительные опции в зависимости от класса
            var additionalOptions = {};
            
            // Для полей с поиском пользователей
            if ($(this).hasClass('select2-search')) {
                var status = $(this).data('status');
                additionalOptions = {
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
                };
            } 
            // Для полей с поиском специалистов
            else if ($(this).hasClass('select2-specialist')) {
                var role = $(this).data('role');
                additionalOptions = {
                    minimumInputLength: 1, // Добавляем минимальную длину ввода
                    ajax: {
                        url: '/search-specialists',
                        dataType: 'json',
                        delay: 300,
                        data: function(params) {
                            return {
                                q: params.term || '',
                                role: role,
                                page: params.page || 1
                            };
                        },
                        processResults: function(data, params) {
                            // Безопасная обработка результатов
                            if (!data) {
                                console.warn('Получены некорректные данные от сервера:', data);
                                return { results: [] };
                            }
                            
                            // Если сервер вернул ошибку в JSON формате
                            if (data.error) {
                                console.error('Ошибка сервера:', data.error);
                                return { results: [] };
                            }
                            
                            // Если данные не массив, преобразуем в массив
                            if (!Array.isArray(data)) {
                                console.warn('Данные не являются массивом:', data);
                                return { results: [] };
                            }
                            
                            return {
                                results: data.map(function(specialist) {
                                    return {
                                        id: specialist.id,
                                        text: specialist.text || specialist.name,
                                        email: specialist.email,
                                        rating: specialist.rating,
                                        role: specialist.role
                                    };
                                })
                            };
                        },
                        cache: true,
                        error: function(xhr, status, error) {
                            console.error('Ошибка при поиске специалистов:', error);
                            console.error('Статус ответа:', xhr.status);
                            console.error('Текст ответа:', xhr.responseText);
                            
                            // Пытаемся показать пользователю понятное сообщение
                            if (xhr.status === 500) {
                                console.error('Внутренняя ошибка сервера при поиске специалистов');
                            } else if (xhr.status === 404) {
                                console.error('Эндпоинт поиска специалистов не найден');
                            } else if (xhr.status === 403) {
                                console.error('Доступ к поиску специалистов запрещен');
                            }
                            
                            // Возвращаем пустой результат при ошибке
                            return { results: [] };
                        }
                    },
                    templateResult: formatSpecialistResult,
                    templateSelection: formatSpecialistSelection
                };
            }
            // Для полей с поиском координаторов
            else if ($(this).hasClass('select2-coordinator-search')) {
                additionalOptions = {
                    minimumInputLength: 0,
                    ajax: {
                        url: '/search-users',
                        dataType: 'json',
                        delay: 300,
                        data: function(params) {
                            return {
                                q: params.term || '',
                                status: 'coordinator',
                                page: params.page || 1
                            };
                        },
                        processResults: function(data, params) {
                            return {
                                results: $.map(data || [], function(user) {
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
                    templateResult: formatUserResult,
                    templateSelection: formatUserSelection
                };
            }
            // Для полей с поиском партнеров
            else if ($(this).hasClass('select2-partner-search')) {
                additionalOptions = {
                    minimumInputLength: 0,
                    ajax: {
                        url: '/search-users',
                        dataType: 'json',
                        delay: 300,
                        data: function(params) {
                            return {
                                q: params.term || '',
                                status: 'partner',
                                page: params.page || 1
                            };
                        },
                        processResults: function(data, params) {
                            return {
                                results: $.map(data || [], function(user) {
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
                    templateResult: formatUserResult,
                    templateSelection: formatUserSelection
                };
            }
            // Для полей с поиском городов
            else if ($(this).hasClass('select2-cities-search')) {
                additionalOptions = {
                    minimumInputLength: 1,
                    ajax: {
                        url: '/cities.json',
                        dataType: 'json',
                        delay: 300,
                        data: function(params) {
                            return {};
                        },
                        processResults: function(data, params) {
                            var filteredResults = [];
                            var term = params.term ? params.term.toLowerCase() : '';
                            
                            if (data && Array.isArray(data)) {
                                filteredResults = data.filter(function(city) {
                                    var cityName = city.city || city;
                                    return cityName.toLowerCase().indexOf(term) !== -1;
                                }).map(function(city) {
                                    var cityName = city.city || city;
                                    var text = cityName;
                                    if (city.region) {
                                        text += ' (' + city.region + ')';
                                    }
                                    return {
                                        id: cityName,
                                        text: text
                                    };
                                });
                            }
                            
                            return {
                                results: filteredResults.slice(0, 20) // Ограничиваем до 20 результатов
                            };
                        },
                        cache: true
                    },
                    language: {
                        noResults: function() {
                            return "Город не найден";
                        },
                        searching: function() {
                            return "Поиск...";
                        },
                        inputTooShort: function() {
                            return "Введите минимум 1 символ";
                        }
                    }
                };
            }
            
            // Объединяем опции
            var options = $.extend({}, defaultOptions, additionalOptions);
            
            // Инициализируем Select2
            try {
                $(this).select2(options);
                
                // Применяем фиксированную ширину к выпадающему списку после открытия
                $(this).on('select2:open', function() {
                    setTimeout(function() {
                        var dropdown = $('.select2-container--open .select2-dropdown');
                        if (dropdown.length && dropdown[0]) {
                            dropdown.css({
                                'width': parentWidth + 'px',
                                'min-width': '100%',
                                'max-width': 'none',
                                'z-index': '9999999'
                            });
                        }
                    }, 0);
                });
                
                // Добавляем класс при изменении значения
                $(this).on('change', function() {
                    if ($(this).val()) {
                        $(this).addClass('filter-active');
                    } else {
                        $(this).removeClass('filter-active');
                    }
                });
                
                // Если значение уже выбрано, добавляем класс
                if ($(this).val()) {
                    $(this).addClass('filter-active');
                }
            } catch (e) {
                console.error('Ошибка инициализации Select2:', e);
            }
        });
    } else {
        console.warn('Select2 не загружен, попробуем загрузить его динамически');
        
        // Подключаем CSS
        if (!$('link[href*="select2.min.css"]').length) {
            var selectCss = document.createElement('link');
            selectCss.rel = 'stylesheet';
            selectCss.href = 'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css';
            document.head.appendChild(selectCss);
        }
        
        // Подключаем JavaScript
        if (typeof $.fn.select2 === 'undefined') {
            var selectScript = document.createElement('script');
            selectScript.src = 'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js';
            selectScript.onload = function() {
                // После загрузки инициализируем Select2
                initializeAllSelect2Elements();
            };
            document.body.appendChild(selectScript);
        }
    }
}

// Вспомогательная функция для форматирования результата выбора пользователя
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

// Вспомогательная функция для форматирования выбранного пользователя
function formatUserSelection(user) {
    return user.text;
}

// Вспомогательная функция для форматирования результата поиска специалиста
function formatSpecialistResult(specialist) {
    if (!specialist.id) return specialist.text;
    
    var rating = parseFloat(specialist.rating) || 0;
    var fullStars = Math.floor(rating);
    var halfStar = rating % 1 >= 0.5;
    var emptyStars = 5 - fullStars - (halfStar ? 1 : 0);
    var ratingHtml = '';
    
    // Добавляем целые звезды
    for (var i = 0; i < fullStars; i++) {
        ratingHtml += '<i class="fas fa-star text-warning"></i>';
    }
    
    // Добавляем половину звезды, если необходимо
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

// Вспомогательная функция для форматирования выбранного специалиста
function formatSpecialistSelection(specialist) {
    if (!specialist.id) return specialist.text;
    
    // Если есть рейтинг, добавляем его к имени
    if (specialist.rating) {
        return specialist.text + ' ⭐ ' + parseFloat(specialist.rating).toFixed(1);
    }
    
    return specialist.text;
}

// Инициализация при загрузке DOM
document.addEventListener('DOMContentLoaded', function() {
    console.log('Инициализация компонентов для страницы сделок');
    
    // Инициализируем Select2
    setTimeout(initializeAllSelect2Elements, 500);
    
    // Добавляем обработчик для инициализации после завершения AJAX запросов
    $(document).ajaxComplete(function(event, xhr, settings) {
        if (settings.url.includes('/deal/')) {
            console.log('AJAX запрос завершен, инициализируем Select2');
            setTimeout(initializeAllSelect2Elements, 300);
        }
    });
    
    // Добавляем обработчик глобальных ошибок для перехвата ошибок с null элементами
    window.addEventListener('error', function(e) {
        if (e.message && e.message.includes("Cannot read properties of null")) {
            console.warn('Перехвачена ошибка с null элементом:', e.message);
            // Можно добавить логику восстановления
            e.preventDefault(); // Предотвращаем показ ошибки в консоли
        }
    });
    
    // Экспортируем функцию глобально для возможности ручного вызова
    window.initializeAllSelect2Elements = initializeAllSelect2Elements;
    
    // Экспортируем безопасные функции работы с элементами
    window.safeGetElement = function(selector) {
        try {
            return document.querySelector(selector);
        } catch (e) {
            console.warn('Ошибка при поиске элемента:', selector, e);
            return null;
        }
    };
    
    window.safeSetStyle = function(element, property, value) {
        try {
            if (element && element.style && typeof element.style === 'object') {
                element.style[property] = value;
                return true;
            }
        } catch (e) {
            console.warn('Ошибка при установке стиля:', e);
        }
        return false;
    };
});
