<!-- 
    Компонент для AJAX-обновления данных сделки без перезагрузки страницы 
    Создан для работы с формой в dealModal.blade.php
-->
<script>
/**
 * Скрипт для асинхронного обновления сделки через AJAX
 * Не конфликтует с функциональностью поиска брифов
 */
$(document).ready(function() {
    // Инициализация AJAX-обработки формы сделки
    initAjaxDealUpdate();

    // Если компонент загружается динамически, добавляем обработчик события
    $(document).on('dealModalLoaded', function() {
        initAjaxDealUpdate();
    });
});

/**
 * Инициализация AJAX-обновления для формы сделки
 */
function initAjaxDealUpdate() {
    // Проверяем наличие формы редактирования сделки
    if ($('#editForm').length === 0) {
        console.warn('Форма редактирования сделки не найдена');
        return;
    }

    // Переопределяем стандартное поведение формы
    $('#editForm').off('submit.ajaxDealUpdate').on('submit.ajaxDealUpdate', function(e) {
        e.preventDefault();

        // Получаем ID сделки
        const dealId = $('#dealIdField').val();
        
        // Показываем индикатор загрузки
        showDealUpdateLoader();
        
        // Получаем данные формы
        const formData = new FormData(this);
        
        // Добавляем метод PUT для Laravel
        formData.append('_method', 'PUT');
        
        // Выполняем AJAX запрос с неограниченным временем ожидания
        $.ajax({
            url: '/deal/update/' + dealId,
            type: 'POST',
            data: formData,
            contentType: false,
            processData: false,
            cache: false,
            timeout: 0, // Убираем ограничения времени ожидания для больших файлов
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                // Скрываем индикатор загрузки
                hideDealUpdateLoader();
                
                if (response.success) {
                    // Показываем уведомление об успешном сохранении
                    showDealUpdateSuccess('Сделка успешно обновлена');
                    
                    // Обновляем данные в модальном окне
                    updateDealModalData(response.deal);
                    
                    // Обновляем карточку сделки, если она есть на странице
                    updateDealCard(dealId, response.deal);
                    
                    // Создаем событие обновления сделки
                    const dealUpdatedEvent = new CustomEvent('dealUpdated', {
                        detail: {
                            dealId: dealId,
                            deal: response.deal
                        }
                    });
                    
                    // Вызываем событие обновления сделки
                    window.dispatchEvent(dealUpdatedEvent);
                    
                    // Закрываем модальное окно через 1.5 секунды (опционально)
                    // setTimeout(function() {
                    //     $('#editModal').modal('hide');
                    // }, 1500);
                } else {
                    // Показываем уведомление об ошибке
                    showDealUpdateError(response.message || 'Произошла ошибка при обновлении сделки');
                }
            },
            error: function(xhr, status, error) {
                // Скрываем индикатор загрузки
                hideDealUpdateLoader();
                
                // Обрабатываем ошибки валидации
                if (xhr.status === 422) {
                    let errorMessages = [];
                    const errors = xhr.responseJSON.errors;
                    
                    // Формируем список ошибок
                    for (let field in errors) {
                        errorMessages.push(errors[field][0]);
                        
                        // Подсвечиваем поля с ошибками
                        const fieldElement = $('[name="' + field + '"]');
                        if (fieldElement.length) {
                            fieldElement.addClass('field-error');
                            
                            // Добавляем сообщение об ошибке под полем
                            if (!fieldElement.next('.error-message').length) {
                                fieldElement.after('<div class="error-message">' + errors[field][0] + '</div>');
                            }
                        }
                    }
                    
                    // Показываем уведомление с ошибками
                    showDealUpdateError('Пожалуйста, исправьте ошибки в форме:<br>' + errorMessages.join('<br>'));
                } else {
                    // Показываем общую ошибку
                    showDealUpdateError('Произошла ошибка при обновлении сделки. Пожалуйста, попробуйте ещё раз.');
                }
                
                console.error('Ошибка при обновлении сделки:', error);
            }
        });
    });
      // Заменяем стандартную кнопку сохранения на AJAX-версию
    const saveButton = $('#saveButton');
    
    if (saveButton.length) {
        // Если кнопки с AJAX-сохранением еще нет, создаем её
        if ($('#ajaxSaveButton').length === 0) {
            // Создаем новую AJAX-кнопку "Быстрое сохранение"
            const ajaxSaveButton = $('<button>', {
                type: 'button',
                id: 'ajaxSaveButton',
                title: 'Сохранить изменения без перезагрузки страницы',
                class: 'ajax-save-btn',
                html: '<i class="fas fa-bolt"></i> Быстрое сохранение'
            });
            
            // Заменяем стандартную кнопку на AJAX-версию
            saveButton.replaceWith(ajaxSaveButton);
            
            // Скрываем стандартную кнопку (дополнительная мера предосторожности)
            saveButton.hide();
            
            // Обработчик клика по кнопке AJAX-сохранения
            ajaxSaveButton.on('click', function() {
                // Проверяем валидность формы перед отправкой
                if ($('#editForm')[0].checkValidity()) {
                    $('#editForm').trigger('submit.ajaxDealUpdate');
                } else {
                    // Если форма не валидна, вызываем стандартную валидацию
                    $('#editForm')[0].reportValidity();
                }
            });
        }
    }
    
    // Сброс класса ошибки при редактировании поля
    $(document).on('input', '#editForm input, #editForm textarea, #editForm select', function() {
        $(this).removeClass('field-error');
        $(this).next('.error-message').remove();
    });
}

/**
 * Показать индикатор загрузки при сохранении сделки
 */
function showDealUpdateLoader() {
    // Проверяем существование и создаем контейнер для уведомлений
    if ($('#dealUpdateNotification').length === 0) {
        $('body').append('<div id="dealUpdateNotification"></div>');
    }
    
    // Показываем индикатор загрузки
    $('#dealUpdateNotification').html(`
        <div class="deal-update-loader">
            <div class="loader-spinner"></div>
            <div class="loader-text">Сохранение данных...</div>
        </div>
    `).fadeIn(200);
    
    // Блокируем только кнопку AJAX-сохранения
    $('#ajaxSaveButton').prop('disabled', true).css('opacity', '0.7').css('cursor', 'not-allowed');
}

/**
 * Скрыть индикатор загрузки
 */
function hideDealUpdateLoader() {
    $('#dealUpdateNotification').fadeOut(200);
    
    // Разблокируем только кнопку AJAX-сохранения
    $('#ajaxSaveButton').prop('disabled', false).css('opacity', '1').css('cursor', 'pointer');
}

/**
 * Показать сообщение об успешном сохранении
 * @param {string} message - Текст сообщения
 */
function showDealUpdateSuccess(message) {
    // Проверяем существование и создаем контейнер для уведомлений
    if ($('#dealUpdateNotification').length === 0) {
        $('body').append('<div id="dealUpdateNotification"></div>');
    }
    
    // Показываем сообщение об успехе
    $('#dealUpdateNotification').html(`
        <div class="deal-update-success">
            <i class="fas fa-check-circle"></i>
            <div class="success-text">${message}</div>
        </div>
    `).fadeIn(200);
    
    // Автоматически скрываем уведомление через 3 секунды
    setTimeout(function() {
        $('#dealUpdateNotification').fadeOut(500);
    }, 3000);
}

/**
 * Показать сообщение об ошибке
 * @param {string} message - Текст сообщения об ошибке
 */
function showDealUpdateError(message) {
    // Проверяем существование и создаем контейнер для уведомлений
    if ($('#dealUpdateNotification').length === 0) {
        $('body').append('<div id="dealUpdateNotification"></div>');
    }
    
    // Показываем сообщение об ошибке
    $('#dealUpdateNotification').html(`
        <div class="deal-update-error">
            <i class="fas fa-exclamation-circle"></i>
            <div class="error-text">${message}</div>
        </div>
    `).fadeIn(200);
    
    // Автоматически скрываем уведомление через 5 секунд
    setTimeout(function() {
        $('#dealUpdateNotification').fadeOut(500);
    }, 5000);
}

/**
 * Обновить данные в модальном окне после успешного сохранения
 * @param {Object} dealData - Данные сделки
 */
function updateDealModalData(dealData) {
    // Обновляем значения в полях формы
    for (let field in dealData) {
        const fieldElement = $(`#editForm [name="${field}"]`);
        if (fieldElement.length) {
            // Для полей select2 обновляем специальным образом
            if (fieldElement.hasClass('select2-hidden-accessible')) {
                fieldElement.val(dealData[field]).trigger('change');
            } else {
                fieldElement.val(dealData[field]);
            }
        }
    }
    
    // Обновляем файловые ссылки для полей с Яндекс.Диск
    updateFileLinksInDealModal(dealData);
    
    // Обновляем заголовок модального окна, если есть
    if (dealData.project_number && $('.modal-title').length) {
        $('.modal-title').text(`Сделка #${dealData.project_number}`);
    }
    
    // Обновляем статус сделки, если есть соответствующее поле
    if (dealData.status && $('#dealStatus').length) {
        $('#dealStatus').text(dealData.status);
    }
}

/**
 * Обновить файловые ссылки в модальном окне
 * @param {Object} dealData - Данные сделки
 */
function updateFileLinksInDealModal(dealData) {
    console.log('Обновляем файловые ссылки в модальном окне сделки', dealData);
    
    // Проходим по всем полям сделки и ищем поля с Яндекс.Диск ссылками
    for (let field in dealData) {
        if (field.startsWith('yandex_url_')) {
            const fieldName = field.replace('yandex_url_', '');
            const originalNameField = 'original_name_' + fieldName;
            const yandexUrl = dealData[field];
            const originalName = dealData[originalNameField] || 'Просмотр файла';
            
            // Находим существующий контейнер с файловой ссылкой
            let fileLink = $(`input[name="${fieldName}"]`).siblings('.file-link.yandex-file-link');
            
            if (yandexUrl && yandexUrl.trim() !== '') {
                if (fileLink.length === 0) {
                    // Если ссылки нет, создаем новую
                    const newFileLink = $(`
                        <div class="file-link yandex-file-link">
                            <a href="${yandexUrl}" target="_blank" title="Открыть файл, загруженный на Яндекс.Диск">
                                <i class="fas fa-cloud-download-alt"></i> ${originalName}
                            </a>
                        </div>
                    `);
                    
                    // Добавляем ссылку после поля ввода файла
                    $(`input[name="${fieldName}"]`).after(newFileLink);
                    console.log(`Создана новая файловая ссылка для поля ${fieldName}`);
                } else {
                    // Если ссылка уже есть, обновляем её
                    fileLink.html(`
                        <a href="${yandexUrl}" target="_blank" title="Открыть файл, загруженный на Яндекс.Диск">
                            <i class="fas fa-cloud-download-alt"></i> ${originalName}
                        </a>
                    `);
                    console.log(`Обновлена файловая ссылка для поля ${fieldName}`);
                }
            }
        }
    }
}

/**
 * Обновить карточку сделки на странице после успешного сохранения
 * @param {number} dealId - ID сделки
 * @param {Object} dealData - Данные сделки
 */
function updateDealCard(dealId, dealData) {
    // Находим карточку сделки на странице
    const dealCard = $(`.faq_block__deal[data-id="${dealId}"]`);
    
    if (dealCard.length) {
        // Обновляем данные в карточке
        if (dealData.status) {
            dealCard.find('.div__status_info').text(dealData.status);
        }
        
        if (dealData.project_number) {
            dealCard.find('h4').text(dealData.project_number || 'Не указан');
        }
        
        if (dealData.client_name) {
            dealCard.find('p:contains("Клиент:")').html(`Клиент: ${dealData.client_name || 'Не указан'}`);
        }
        
        if (dealData.client_phone) {
            dealCard.find('p:contains("Телефон:")').html(`Телефон: <a href="tel:${dealData.client_phone}">${dealData.client_phone}</a>`);
        }
        
        // Обновляем данные в таблице, если она есть
        const dealRow = $(`#dealTable tr[data-id="${dealId}"]`);
        if (dealRow.length) {
            if (dealData.project_number) {
                dealRow.find('td.deal-name').text(dealData.project_number || 'Не указан');
            }
            
            if (dealData.client_name) {
                dealRow.find('td.deal-client').text(dealData.client_name || 'Не указан');
            }
            
            if (dealData.client_phone) {
                dealRow.find('td.deal-phone').html(`<a href="tel:${dealData.client_phone}">${dealData.client_phone}</a>`);
            }
            
            if (dealData.status) {
                dealRow.find('td.deal-status').text(dealData.status);
            }
            
            if (dealData.total_sum) {
                dealRow.find('td.deal-sum').text(`${dealData.total_sum.toLocaleString()} ₽`);
            }
        }
    }
}
</script>

<!-- Стили для компонента AJAX-обновления сделки -->
<style>
/* Контейнер для уведомлений */
#dealUpdateNotification {
    position: fixed;
    top: 20px;
    right: 20px;
    z-index: 9999999;
    min-width: 250px;
    max-width: 350px;
    display: none;
}

/* Стили для индикатора загрузки */
.deal-update-loader {
    background: #fff;
    border-radius: 4px;
    padding: 15px;
    display: flex;
    align-items: center;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
    border-left: 4px solid #2196F3;
}

.loader-spinner {
    width: 24px;
    height: 24px;
    border: 3px solid rgba(33, 150, 243, 0.3);
    border-top-color: #2196F3;
    border-radius: 50%;
    margin-right: 15px;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    to { transform: rotate(360deg); }
}

.loader-text {
    font-size: 16px;
    color: #333;
    font-weight: 500;
}

/* Стили для сообщения об успехе */
.deal-update-success {
    background: #e8f5e9;
    border-radius: 4px;
    padding: 15px;
    display: flex;
    align-items: center;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    border-left: 4px solid #4caf50;
}

.deal-update-success i {
    color: #4caf50;
    font-size: 24px;
    margin-right: 15px;
}

.success-text {
    font-size: 16px;
    color: #2e7d32;
    font-weight: 500;
}

/* Стили для сообщения об ошибке */
.deal-update-error {
    background: #ffebee;
    border-radius: 4px;
    padding: 15px;
    display: flex;
    align-items: flex-start;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    border-left: 4px solid #f44336;
}

.deal-update-error i {
    color: #f44336;
    font-size: 24px;
    margin-right: 15px;
}

.error-text {
    font-size: 15px;
    color: #c62828;
    font-weight: 500;
    flex: 1;
}

/* Подсветка полей с ошибками */
.field-error {
    border: 1px solid #f44336 !important;
    background-color: rgba(244, 67, 54, 0.05) !important;
}

.error-message {
    color: #f44336;
    font-size: 12px;
    margin-top: 5px;
    display: block;
}

/* Стили для кнопки AJAX-сохранения */
.ajax-save-btn {
    background: linear-gradient(45deg, #2196F3, #1976D2);
    color: white;
    border: none;
    border-radius: 4px;
    padding: 10px 20px;
    cursor: pointer;
    font-weight: 600;
    font-size: 16px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    transition: all 0.3s ease;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
    width: auto;
    min-width: 180px;
    position: relative;
    overflow: hidden;
    letter-spacing: 0.5px;
}

.ajax-save-btn:hover {
    background: linear-gradient(45deg, #1e88e5, #1565c0);
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3);
    transform: translateY(-2px);
}

.ajax-save-btn:active {
    transform: translateY(1px);
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
}

.ajax-save-btn i {
    margin-right: 8px;
    font-size: 16px;
}
</style>
