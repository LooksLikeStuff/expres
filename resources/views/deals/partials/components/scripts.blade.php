<!-- Скрипты для модального окна и его компонентов -->
<script>
$(function() {
    // Инициализация всех Select2 элементов в модальном окне
    initModalSelects();
    
    // Инициализация загрузки документов
    initDocumentsUpload();
    
    // Инициализация масок телефона
    if (typeof window.initPhoneMasks === 'function') {
        console.log('scripts.blade.php: инициализация масок телефона');
        window.initPhoneMasks();
    }
    
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
    console.log('initModalSelects: инициализация Select2 полей и масок телефона');
    
    // Инициализируем маски телефона
    if (typeof window.initPhoneMasks === 'function') {
        window.initPhoneMasks();
    }
    
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
    var documentsPlaceholder = $('.documents-placeholder');
    
    if (documentsList.length === 0) {
        // Если нет, создаем его
        if (documentsPlaceholder.length > 0) {
            // Показываем скрытый плейсхолдер
            documentsPlaceholder.show();
            documentsList = documentsPlaceholder.find('.documents-list');
        } else {
            // Создаем новый контейнер
            $('.documents-container').append(
                '<div class="faq_item__deal">' +
                '<div class="faq_block__deal">' +
                '<div class="create__group">' +
                '<i class="fas fa-file-alt"></i>' +
                '<span>Загруженные файлы</span>' +
                '</div>' +
                '<div class="faq_text__deal">' +
                '<div class="documents-list">' +
                '<h4>Загруженные документы</h4>' +
                '<ul class="document-items"></ul>' +
                '</div>' +
                '</div>' +
                '</div>' +
                '</div>'
            );
            documentsList = $('.documents-list');
        }
        
        // Удаляем сообщение о том, что нет документов
        $('.no-documents').remove();
    }
    
    var documentItems = $('.document-items');
    
    // Очищаем старый список перед добавлением новых элементов
    documentItems.empty();
    
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
    
    // Обновляем счетчик файлов в заголовке
    var fileCountSpan = $('.documents-container .create__group span');
    if (fileCountSpan.length > 0) {
        fileCountSpan.text('Загруженные файлы (' + documents.length + ')');
    }
}
        
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

/**
 * Функция для удаления документа
 */
function deleteDocument(documentId) {
    // Парсим documentId для получения dealId и field
    const parts = documentId.split('_');
    if (parts.length < 2) {
        console.error('Неверный формат ID документа:', documentId);
        return;
    }
    
    const dealId = parts[0];
    const field = parts.slice(1).join('_');
    
    if (confirm('Вы уверены, что хотите удалить этот документ? Это действие нельзя отменить.')) {
        // Находим элемент документа
        const documentElement = $(`[data-document-id="${documentId}"]`).closest('.document-item');
        
        // Показываем индикатор загрузки
        documentElement.addClass('deleting');
        
        // Сначала попробуем найти оригинальное имя файла из элемента
        const documentName = documentElement.find('.document-name').text();
        const documentExtension = documentElement.find('.document-extension').text();
        const fileName = documentName + documentExtension;
        
        $.ajax({
            url: `/deals/${dealId}/documents/${encodeURIComponent(fileName)}`,
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                'Accept': 'application/json'
            },
            data: {
                field: field // Передаем поле для идентификации в базе данных
            },
            success: function(response) {
                if (response.success) {
                    // Удаляем элемент с анимацией
                    documentElement.fadeOut(300, function() {
                        $(this).remove();
                        
                        // Проверяем, остались ли документы
                        const remainingDocs = $('.document-item').length;
                        if (remainingDocs === 0) {
                            // Показываем пустое состояние
                            $('.documents-list-section').hide();
                            $('.documents-empty-state').show();
                        } else {
                            // Обновляем счетчик
                            $('.documents-list-title').text(`Загруженные документы (${remainingDocs})`);
                        }
                    });
                    
                    // Показываем уведомление об успехе
                    if (typeof toastr !== 'undefined') {
                        toastr.success('Документ успешно удален');
                    } else {
                        alert('Документ успешно удален');
                    }
                } else {
                    documentElement.removeClass('deleting');
                    alert(response.message || 'Ошибка при удалении документа');
                }
            },
            error: function(xhr) {
                documentElement.removeClass('deleting');
                
                let errorMessage = 'Ошибка при удалении документа';
                if (xhr.responseJSON && xhr.responseJSON.error) {
                    errorMessage = xhr.responseJSON.error;
                }
                
                if (typeof toastr !== 'undefined') {
                    toastr.error(errorMessage);
                } else {
                    alert(errorMessage);
                }
                
                console.error('Ошибка удаления документа:', xhr);
            }
        });
    }
}

// Обработчики событий для модального окна
$(document).on('shown.bs.modal', '#editModal', function() {
    setTimeout(function() {
        initModalSelects();
        // Инициализируем маски телефона
        if (typeof window.initPhoneMasks === 'function') {
            console.log('scripts.blade.php: shown.bs.modal - инициализация масок телефона');
            window.initPhoneMasks();
        }
        // Система вкладок инициализируется автоматически
    }, 100);
});

// Также проверяем, чтобы инициализация срабатывала при открытии модального окна через AJAX
$(document).ajaxComplete(function(event, xhr, settings) {
    if (settings.url && settings.url.includes('/deal/') && settings.url.includes('/modal')) {
        setTimeout(function() {
            if ($('#editModal').is(':visible')) {
                initModalSelects();
                // Инициализируем маски телефона после AJAX загрузки
                if (typeof window.initPhoneMasks === 'function') {
                    console.log('scripts.blade.php: ajaxComplete - инициализация масок телефона');
                    window.initPhoneMasks();
                }
                // Система вкладок переинициализируется автоматически
            }
        }, 200);
    }
});

</script>
