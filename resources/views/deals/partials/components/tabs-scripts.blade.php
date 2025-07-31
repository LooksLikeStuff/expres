<!-- Скрипты для новой структуры документов и брифов -->
<script>
$(document).ready(function() {
    // Инициализация загрузки документов
    initDocumentUpload();
    
    // Инициализация поиска брифов
    initBriefSearch();
});
/**
 * Универсальная функция для настройки автофокуса в Select2
 * Добавляет автоматический фокус на поле поиска при открытии Select2
 */

(function($) {
    'use strict';
    
    // Функция для принудительной установки фокуса на поле поиска
    function forceFocusOnSearchField() {
        var $searchField = $('.select2-container--open .select2-search__field');
        
        if ($searchField.length > 0) {
            // Проверяем, что фокус еще не установлен
            if (document.activeElement !== $searchField[0]) {
                console.log('Устанавливаем фокус на поле поиска Select2');
                
                // Убираем возможный предыдущий фокус
                if (document.activeElement && document.activeElement.blur) {
                    document.activeElement.blur();
                }
                
                // Устанавливаем фокус несколькими способами для максимальной совместимости
                $searchField[0].focus();
                $searchField.trigger('focus');
                
                // Дополнительно для некоторых браузеров
                $searchField[0].click();
                
                return true;
            }
        }
        return false;
    }
    
    // Функция для установки автофокуса с множественными попытками
    function setupAutoFocusWithRetries() {
        var maxAttempts = 10;
        var currentAttempt = 0;
        var delays = [5, 10, 20, 30, 50, 75, 100, 150, 200, 300];
        
        function attemptFocus() {
            if (currentAttempt >= maxAttempts) {
                console.warn('Не удалось установить автофокус на Select2 после ' + maxAttempts + ' попыток');
                return;
            }
            
            var success = forceFocusOnSearchField();
            
            if (!success) {
                currentAttempt++;
                var delay = delays[currentAttempt - 1] || 300;
                setTimeout(attemptFocus, delay);
            } else {
                console.log('Автофокус установлен успешно с попытки #' + (currentAttempt + 1));
            }
        }
        
        attemptFocus();
    }
    
    // Глобальная функция для добавления автофокуса к Select2
    window.addSelect2AutoFocus = function(selector) {
        selector = selector || '.select2-enabled, .select2-field, .select2-search, .select2-specialist, .select2-coordinator-search, .select2-partner-search, .select2-cities-search';
        
        $(selector).each(function() {
            var $element = $(this);
            
            // Проверяем, что элемент инициализирован как Select2
            if ($element.hasClass('select2-hidden-accessible')) {
                // Удаляем все старые обработчики автофокуса
                $element.off('select2:open.autofocus select2:open.autofocus-force select2:open.force-autofocus');
                
                // Добавляем новый надежный обработчик
                $element.on('select2:open.autofocus-reliable', function(e) {
                    console.log('Select2 открыт, запускаем надежный автофокус для элемента:', this);
                    setupAutoFocusWithRetries();
                });
                
                console.log('Автофокус добавлен для Select2 элемента:', $element[0]);
            }
        });
    };
    
    // Функция для автоматического применения к новым элементам
    window.initSelect2WithAutoFocus = function(selector) {
        selector = selector || '.select2-field, .select2-search, .select2-specialist';
        
        $(selector).each(function() {
            if (!$(this).hasClass('select2-hidden-accessible')) {
                var $element = $(this);
                var $parent = $element.closest('.filter-group, .form-group-deal, .modal');
                
                if (!$parent.length) {
                    $parent = $element.parent();
                }
                
                // Базовые опции Select2
                var options = {
                    width: '100%',
                    placeholder: $element.attr('placeholder') || $element.data('placeholder') || "Выберите значение",
                    allowClear: true,
                    dropdownParent: $parent,
                    language: 'ru'
                };
                
                // Инициализируем Select2
                $element.select2(options);
                
                // Добавляем автофокус
                addSelect2AutoFocus($element);
            }
        });
    };
    
    // Автоматическая инициализация при загрузке DOM
    $(document).ready(function() {
        console.log('Инициализация надежного автофокуса для Select2');
        
        // Применяем автофокус ко всем существующим Select2 элементам
        setTimeout(function() {
            addSelect2AutoFocus();
        }, 500);
        
        // Дополнительный обработчик для уже инициализированных элементов
        setTimeout(function() {
            $('.select2-hidden-accessible').each(function() {
                addSelect2AutoFocus($(this));
            });
        }, 1000);
        
        // Глобальный обработчик для всех событий открытия Select2
        // Этот обработчик будет перехватывать ВСЕ события открытия Select2 на странице
        $(document).on('select2:open', function(e) {
            console.log('Глобальный обработчик: Select2 открыт');
            setupAutoFocusWithRetries();
        });
        
        // Обработчик для модальных окон
        $(document).on('shown.bs.modal', function() {
            console.log('Модальное окно открыто, применяем автофокус');
            setTimeout(function() {
                addSelect2AutoFocus();
            }, 200);
        });
        
        // Обработчик для AJAX запросов
        $(document).ajaxComplete(function(event, xhr, settings) {
            // Проверяем, что AJAX запрос связан с модальными окнами или сделками
            if (settings.url && (settings.url.includes('/deal') || settings.url.includes('/modal'))) {
                console.log('AJAX запрос завершен, применяем автофокус');
                setTimeout(function() {
                    addSelect2AutoFocus();
                }, 100);
            }
        });
        
        // Обработчик для динамически добавляемых элементов (современная альтернатива DOMNodeInserted)
        if (window.MutationObserver) {
            var observer = new MutationObserver(function(mutations) {
                var shouldReinitialize = false;
                
                mutations.forEach(function(mutation) {
                    if (mutation.type === 'childList') {
                        mutation.addedNodes.forEach(function(node) {
                            if (node.nodeType === 1) { // Element node
                                var $node = $(node);
                                if ($node.hasClass('select2-hidden-accessible') || $node.find('.select2-hidden-accessible').length > 0) {
                                    shouldReinitialize = true;
                                }
                            }
                        });
                    }
                });
                
                if (shouldReinitialize) {
                    console.log('Обнаружены новые Select2 элементы, применяем автофокус');
                    setTimeout(function() {
                        addSelect2AutoFocus();
                    }, 100);
                }
            });
            
            observer.observe(document.body, {
                childList: true,
                subtree: true
            });
        }
    });
    
    // Экспортируем функции глобально
    window.setupAutoFocusWithRetries = setupAutoFocusWithRetries;
    window.forceFocusOnSearchField = forceFocusOnSearchField;
    window.Select2AutoFocus = {
        init: addSelect2AutoFocus,
        initWithSelect2: initSelect2WithAutoFocus,
        setupRetries: setupAutoFocusWithRetries,
        forceFocus: forceFocusOnSearchField
    };
    
})(jQuery);

/**
 * Инициализация загрузки документов
 */
function initDocumentUpload() {
    const fileInput = $('#document-upload');
    const uploadLabel = $('.upload-label');
    const filesCount = $('.files-count');
    const uploadBtn = $('#upload-documents-btn');
    const uploadText = $('.upload-text');
    
    // Обработка выбора файлов
    fileInput.on('change', function() {
        const files = this.files;
        const count = files.length;
        
        if (count > 0) {
            filesCount.text(`Выбрано файлов: ${count}`);
            uploadText.text('Изменить файлы');
            uploadBtn.prop('disabled', false);
            uploadLabel.addClass('files-selected');
        } else {
            filesCount.text('Файлы не выбраны');
            uploadText.text('Выбрать файлы');
            uploadBtn.prop('disabled', true);
            uploadLabel.removeClass('files-selected');
        }
    });
    
    // Обработка загрузки
    uploadBtn.on('click', function() {
        const files = fileInput[0].files;
        if (files.length === 0) {
            showNotification('Выберите файлы для загрузки', 'error');
            return;
        }
        
        // Используем существующую систему загрузки больших файлов
        if (window.LargeFileUploader) {
            // Интеграция с существующей системой
            uploadDocumentsToYandexDisk(files);
        } else {
            console.error('Система загрузки больших файлов не найдена');
            showNotification('Ошибка системы загрузки', 'error');
        }
    });
    
    // Drag & Drop функциональность
    uploadLabel.on('dragover', function(e) {
        e.preventDefault();
        $(this).addClass('drag-over');
    });
    
    uploadLabel.on('dragleave', function(e) {
        e.preventDefault();
        $(this).removeClass('drag-over');
    });
    
    uploadLabel.on('drop', function(e) {
        e.preventDefault();
        $(this).removeClass('drag-over');
        
        const files = e.originalEvent.dataTransfer.files;
        if (files.length > 0) {
            fileInput[0].files = files;
            fileInput.trigger('change');
        }
    });
}

/**
 * Инициализация поиска брифов
 */
function initBriefSearch() {
    const searchBtn = $('#searchBriefBtn');
    const spinner = $('#brief-spinner-container');
    const resultsContainer = $('#brief-search-results-container');
    const resultsList = $('#brief-results-list');
    const notification = $('#brief-notification');
    
    // Обработка поиска
    searchBtn.on('click', function() {
        const dealId = $(this).data('deal-id');
        const clientPhone = $(this).data('client-phone');
        
        if (!clientPhone) {
            showBriefNotification('Телефон клиента не указан', 'error');
            return;
        }
        
        searchBriefs(dealId, clientPhone);
    });
    
    // Закрытие результатов
    $(document).on('click', '.btn-close', function() {
        resultsContainer.hide();
    });
    
    // Отвязка брифа
    $(document).on('click', '.btn-detach-brief', function() {
        const dealId = $(this).data('deal-id');
        
        if (confirm('Вы уверены, что хотите отвязать бриф от сделки?')) {
            detachBrief(dealId);
        }
    });
}

/**
 * Поиск брифов по телефону
 */
function searchBriefs(dealId, clientPhone) {
    const spinner = $('#brief-spinner-container');
    const resultsContainer = $('#brief-search-results-container');
    const resultsList = $('#brief-results-list');
    
    // Показываем загрузку
    spinner.show();
    resultsContainer.hide();
    hideBriefNotification();
    
    // AJAX запрос (интеграция с существующим API)
    $.ajax({
        url: '/search-briefs', // Используем существующий роут
        method: 'POST',
        data: {
            deal_id: dealId,
            client_phone: clientPhone,
            _token: $('meta[name="csrf-token"]').attr('content')
        },
        success: function(response) {
            spinner.hide();
            
            if (response.success && response.briefs && response.briefs.length > 0) {
                displayBriefResults(response.briefs, dealId);
                resultsContainer.show();
            } else {
                showBriefNotification('Брифы для данного номера телефона не найдены', 'info');
            }
        },
        error: function(xhr) {
            spinner.hide();
            console.error('Ошибка поиска брифов:', xhr);
            showBriefNotification('Ошибка при поиске брифов', 'error');
        }
    });
}

/**
 * Отображение результатов поиска брифов
 */
function displayBriefResults(briefs, dealId) {
    const resultsList = $('#brief-results-list');
    let html = '';
    
    briefs.forEach(function(brief) {
        const isLinked = brief.is_linked;
        const statusClass = isLinked ? 'linked' : 'available';
        const statusText = isLinked ? 'Уже привязан' : 'Доступен';
        
        html += `
            <div class="brief-result-item ${statusClass}" data-brief-id="${brief.id}" data-brief-type="${brief.type}">
                <div class="brief-info">
                    <div class="brief-title">
                        <i class="fas fa-file-alt"></i>
                        ${brief.type === 'common' ? 'Общий бриф' : 'Коммерческий бриф'}
                    </div>
                    <div class="brief-details">
                        <small>ID: ${brief.id} | Создан: ${brief.created_date}</small>
                    </div>
                    <div class="brief-status ${statusClass}">
                        <i class="fas ${isLinked ? 'fa-link' : 'fa-check-circle'}"></i>
                        ${statusText}
                    </div>
                </div>
                ${!isLinked ? `
                    <button type="button" class="create__group btn-attach-brief" 
                            data-deal-id="${dealId}" 
                            data-brief-id="${brief.id}" 
                            data-brief-type="${brief.type}"
                            style="margin: 0; min-width: auto;">
                        <i class="fas fa-link"></i>
                        Привязать
                    </button>
                ` : ''}
            </div>
        `;
    });
    
    resultsList.html(html);
    
    // Обработка привязки
    $('.btn-attach-brief').on('click', function() {
        const dealId = $(this).data('deal-id');
        const briefId = $(this).data('brief-id');
        const briefType = $(this).data('brief-type');
        
        attachBrief(dealId, briefId, briefType);
    });
}

/**
 * Привязка брифа к сделке
 */
function attachBrief(dealId, briefId, briefType) {
    $.ajax({
        url: '/attach-brief', // Используем существующий роут
        method: 'POST',
        data: {
            deal_id: dealId,
            brief_id: briefId,
            brief_type: briefType,
            _token: $('meta[name="csrf-token"]').attr('content')
        },
        success: function(response) {
            if (response.success) {
                showBriefNotification('Бриф успешно привязан к сделке', 'success');
                // Обновляем страницу или перезагружаем модальное окно
                setTimeout(function() {
                    location.reload();
                }, 1500);
            } else {
                showBriefNotification(response.message || 'Ошибка при привязке брифа', 'error');
            }
        },
        error: function(xhr) {
            console.error('Ошибка привязки брифа:', xhr);
            showBriefNotification('Ошибка при привязке брифа', 'error');
        }
    });
}

/**
 * Отвязка брифа от сделки
 */
function detachBrief(dealId) {
    $.ajax({
        url: '/detach-brief', // Используем существующий роут
        method: 'POST',
        data: {
            deal_id: dealId,
            _token: $('meta[name="csrf-token"]').attr('content')
        },
        success: function(response) {
            if (response.success) {
                showBriefNotification('Бриф отвязан от сделки', 'success');
                setTimeout(function() {
                    location.reload();
                }, 1500);
            } else {
                showBriefNotification(response.message || 'Ошибка при отвязке брифа', 'error');
            }
        },
        error: function(xhr) {
            console.error('Ошибка отвязки брифа:', xhr);
            showBriefNotification('Ошибка при отвязке брифа', 'error');
        }
    });
}

/**
 * Показ уведомлений для брифов
 */
function showBriefNotification(message, type) {
    const notification = $('#brief-notification');
    
    notification
        .removeClass('success error info')
        .addClass(type)
        .html(`<i class="fas fa-${type === 'success' ? 'check-circle' : type === 'error' ? 'exclamation-circle' : 'info-circle'}"></i> ${message}`)
        .show();
        
    if (type === 'success') {
        setTimeout(hideBriefNotification, 3000);
    }
}

/**
 * Скрытие уведомлений
 */
function hideBriefNotification() {
    $('#brief-notification').hide();
}

/**
 * Общая функция для уведомлений
 */
function showNotification(message, type) {
    // Используем существующую систему уведомлений или создаем простую
    if (window.showDealUpdateSuccess && type === 'success') {
        window.showDealUpdateSuccess(message);
    } else if (window.showDealUpdateError && type === 'error') {
        window.showDealUpdateError(message);
    } else {
        // Простое уведомление
        alert(message);
    }
}

/**
 * Интеграция с системой загрузки больших файлов
 */
function uploadDocumentsToYandexDisk(files) {
    // Эта функция должна интегрироваться с существующей системой large-file-upload.js
    console.log('Загрузка файлов:', files);
    
    // Здесь должна быть логика интеграции с существующей системой
    // В зависимости от реализации LargeFileUploader
}
</script>

<style>
/* Дополнительные стили для скриптовых элементов */
.files-selected {
    border-color: var(--blue) !important;
    background: #f0f7ff !important;
}

.drag-over {
    border-color: var(--blue) !important;
    background: #e3f2fd !important;
}

.brief-result-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 12px;
    border: 1px solid #e9ecef;
    border-radius: 6px;
    margin-bottom: 8px;
    transition: all 0.3s ease;
}

.brief-result-item:hover {
    border-color: var(--blue);
    background: #f8f9fa;
}

.brief-result-item.linked {
    background: #f8f9fa;
    opacity: 0.8;
}

.brief-info {
    flex-grow: 1;
}

.brief-title {
    font-weight: 500;
    color: #495057;
    display: flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 4px;
}

.brief-details {
    color: #6c757d;
    font-size: 0.85em;
    margin-bottom: 4px;
}

.brief-status {
    display: flex;
    align-items: center;
    gap: 4px;
    font-size: 0.85em;
}

.brief-status.available {
    color: var(--green);
}

.brief-status.linked {
    color: #6c757d;
}
</style>
