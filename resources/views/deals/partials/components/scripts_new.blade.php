<!-- Скрипты для модального окна и его компонентов -->
<script>
$(function() {
    // Инициализация всех Select2 элементов в модальном окне
    initModalSelects();
    
    // Инициализация загрузки документов
    initDocumentsUpload();
    
    // Система вкладок инициализируется автоматически через unified_tabs_system.blade.php
    console.log('scripts.blade.php: основные компоненты инициализированы');
});

/**
 * ФУНКЦИИ ОБРАТНОЙ СОВМЕСТИМОСТИ
 * Все функции переадресуют вызовы к единой системе TabsSystem
 */

/**
 * @deprecated Используется unified_tabs_system.blade.php
 * Функция оставлена для обратной совместимости
 */
function initTabHandlers() {
    console.log('initTabHandlers: переадресация к TabsSystem');
    if (window.TabsSystem && typeof window.TabsSystem.init === 'function') {
        window.TabsSystem.init();
    }
}

/**
 * @deprecated Используется unified_tabs_system.blade.php
 * Функция оставлена для обратной совместимости
 */
function showModule(selector) {
    console.log('showModule: переадресация к TabsSystem для селектора', selector);
    if (window.showModule && typeof window.showModule === 'function') {
        window.showModule(selector);
    }
}

/**
 * @deprecated Используется unified_tabs_system.blade.php
 * Функция оставлена для обратной совместимости
 */
function showDocumentsTab() {
    console.log('showDocumentsTab: переадресация к TabsSystem');
    if (window.showDocumentsTab && typeof window.showDocumentsTab === 'function') {
        window.showDocumentsTab();
    }
}

/**
 * Инициализация загрузки документов
 * Современная система больших файлов обрабатывает все автоматически
 */
function initDocumentsUpload() {
    console.log('initDocumentsUpload: используется система больших файлов large-file-upload.js');
    // Новая система large-file-upload.js обрабатывает все события автоматически
    // Дополнительная инициализация не требуется
}

/**
 * Инициализация Select2 элементов в модальном окне
 */
function initModalSelects() {
    console.log('initModalSelects: инициализация Select2 полей');
    
    // Инициализация выполняется через отдельные компоненты
    // См. select2-coordinator-partner.css и соответствующие скрипты
    
    // Если есть Select2 элементы, инициализируем их
    if (typeof $.fn.select2 !== 'undefined') {
        $('.select2-field').each(function() {
            if (!$(this).hasClass('select2-hidden-accessible')) {
                try {
                    const $parent = $(this).closest('.form-group-deal');
                    if ($parent.length) {
                        $parent.css('position', 'relative');
                    }
                    
                    $(this).select2({
                        theme: 'default',
                        width: '100%',
                        dropdownParent: $parent.length ? $parent : $(this).parent()
                    });
                } catch(e) {
                    console.warn('initModalSelects: ошибка инициализации Select2 для элемента', this, e);
                }
            }
        });
    }
}

/**
 * Функция для копирования регистрационной ссылки
 */
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

/**
 * Функция для подтверждения удаления сделки
 */
function confirmDeleteDeal(dealId) {
    if (confirm('ВНИМАНИЕ! Вы собираетесь удалить сделку. Это действие нельзя отменить.\\n\\nВы уверены?')) {
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

// Обработчики событий для модального окна
$(document).on('shown.bs.modal', '#editModal', function() {
    setTimeout(function() {
        initModalSelects();
        // Система вкладок инициализируется автоматически
    }, 100);
});

// Также проверяем, чтобы инициализация срабатывала при открытии модального окна через AJAX
$(document).ajaxComplete(function(event, xhr, settings) {
    if (settings.url && settings.url.includes('/deal/') && settings.url.includes('/modal')) {
        setTimeout(function() {
            if ($('#editModal').is(':visible')) {
                initModalSelects();
                // Система вкладок переинициализируется автоматически
            }
        }, 200);
    }
});

</script>
