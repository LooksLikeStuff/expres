/**
 * Исправление автоматического обновления документов после загрузки
 * Этот файл решает проблему с отображением файлов после загрузки
 */

(function() {
    'use strict';

    // Убеждаемся, что функции определены глобально
    if (typeof window.updateDocumentsList !== 'function') {
        window.updateDocumentsList = function(documents) {
            console.log('updateDocumentsList вызвана с', documents?.length || 0, 'документами');
            
            if (!documents || documents.length === 0) return;
            
            // Находим контейнер для документов
            let documentsList = $('.documents-list');
            let documentsPlaceholder = $('.documents-placeholder');
            
            // Если нет списка документов, создаем его
            if (documentsList.length === 0 && documentsPlaceholder.length > 0) {
                documentsPlaceholder.show();
                documentsList = documentsPlaceholder.find('.documents-list');
            }
            
            if (documentsList.length === 0) {
                // Создаем новый контейнер
                const documentsContainer = $('.documents-container');
                if (documentsContainer.length > 0) {
                    documentsContainer.append(`
                        <div class="documents-list">
                            <h4>Загруженные документы</h4>
                            <ul class="document-items"></ul>
                        </div>
                    `);
                    documentsList = $('.documents-list');
                }
            }
            
            if (documentsList.length > 0) {
                const documentItems = documentsList.find('.document-items');
                
                // Добавляем новые документы
                documents.forEach(function(doc) {
                    const extension = doc.extension || 'unknown';
                    const fileName = doc.name || 'document';
                    const fileNameWithoutExt = fileName.split('.').slice(0, -1).join('.') || fileName;
                    
                    // Проверяем, что такого документа еще нет в списке
                    const existingDoc = documentItems.find(`[data-doc-name="${fileName}"]`);
                    if (existingDoc.length === 0) {
                        documentItems.append(`
                            <li class="document-item" data-doc-name="${fileName}">
                                <a href="${doc.url}" target="_blank" class="document-link" download="${fileName}">
                                    <i class="fas ${doc.icon || 'fa-file'}"></i>
                                    <span class="document-name">${fileNameWithoutExt}</span>
                                    <span class="document-extension">.${extension}</span>
                                </a>
                            </li>
                        `);
                    }
                });
                
                // Удаляем сообщение "нет документов"
                $('.no-documents').hide();
                
                console.log('Документы успешно добавлены в интерфейс');
            }
        };
    }

    if (typeof window.updateDealModalData !== 'function') {
        window.updateDealModalData = function(dealData) {
            console.log('updateDealModalData вызвана с данными сделки:', dealData);
            
            if (!dealData) return;
            
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
            
            // Обновляем файловые ссылки
            if (typeof window.updateFileLinksInDealModal === 'function') {
                window.updateFileLinksInDealModal(dealData);
            }
            
            // Обновляем заголовок модального окна
            if (dealData.project_number && $('.modal-title').length) {
                $('.modal-title').text(`Сделка #${dealData.project_number}`);
            }
            
            console.log('Данные модального окна обновлены');
        };
    }

    if (typeof window.forceUpdateFileLinks !== 'function') {
        window.forceUpdateFileLinks = function() {
            console.log('forceUpdateFileLinks: принудительное обновление файловых ссылок');
            
            // Получаем ID сделки
            const dealId = $('#dealIdField').val();
            if (!dealId) {
                console.warn('ID сделки не найден, не можем обновить ссылки');
                return;
            }
            
            // Загружаем актуальные данные сделки с сервера
            $.get(`/deal/${dealId}/data`)
                .done(function(response) {
                    if (response.success && response.deal) {
                        console.log('Получены актуальные данные сделки для обновления ссылок');
                        window.forceUpdateFileLinksFromDealData(response.deal);
                    }
                })
                .fail(function() {
                    console.warn('Не удалось загрузить данные сделки для обновления ссылок');
                });
        };
    }

    if (typeof window.forceUpdateFileLinksFromDealData !== 'function') {
        window.forceUpdateFileLinksFromDealData = function(dealData) {
            console.log('forceUpdateFileLinksFromDealData: обновление ссылок из данных', dealData);
            
            if (!dealData) return;
            
            // Обрабатываем все поля с файлами Яндекс.Диск
            const fileFields = [
                'work_act', 'chat_screenshot', 'plan_final', 'final_collage', 'measurements_file',
                'final_floorplan', 'final_project_file', 'archicad_file', 'contract_attachment', 'execution_order_file',
                'screenshot_work_1', 'screenshot_work_2', 'screenshot_work_3', 'screenshot_final'
            ];
            
            fileFields.forEach(function(fieldName) {
                const yandexUrlField = 'yandex_url_' + fieldName;
                const originalNameField = 'original_name_' + fieldName;
                const yandexUrl = dealData[yandexUrlField];
                const originalName = dealData[originalNameField] || 'Просмотр файла';
                
                if (yandexUrl && yandexUrl.trim() !== '') {
                    // Находим существующую ссылку или создаем новую
                    let fileLink = $(`input[name="${fieldName}"]`).siblings('.file-link.yandex-file-link');
                    
                    if (fileLink.length === 0) {
                        // Создаем новую ссылку
                        const newFileLink = $(`
                            <div class="file-link yandex-file-link">
                                <a href="${yandexUrl}" target="_blank" title="Открыть файл, загруженный на Яндекс.Диск">
                                    <i class="fas fa-cloud-download-alt"></i> ${originalName}
                                </a>
                            </div>
                        `);
                        $(`input[name="${fieldName}"]`).after(newFileLink);
                        console.log(`Создана новая файловая ссылка для поля ${fieldName}`);
                    } else {
                        // Обновляем существующую ссылку
                        fileLink.find('a').attr('href', yandexUrl).find('i').after(' ' + originalName);
                        console.log(`Обновлена файловая ссылка для поля ${fieldName}`);
                    }
                }
            });
        };
    }

    if (typeof window.updateFileLinksInDealModal !== 'function') {
        window.updateFileLinksInDealModal = function(dealData) {
            console.log('updateFileLinksInDealModal вызвана');
            if (typeof window.forceUpdateFileLinksFromDealData === 'function') {
                window.forceUpdateFileLinksFromDealData(dealData);
            }
        };
    }

    // Улучшенный обработчик события завершения загрузки документов
    $(document).off('documentUploadComplete.autoUpdate').on('documentUploadComplete.autoUpdate', function(event) {
        console.log('Событие documentUploadComplete получено, начинаем обновление интерфейса');
        
        setTimeout(function() {
            const dealId = $('#dealIdField').val();
            if (dealId) {
                console.log('Загружаем актуальные данные сделки после загрузки документов');
                
                $.get(`/deal/${dealId}/data`)
                    .done(function(response) {
                        if (response.success && response.deal) {
                            console.log('Получены актуальные данные сделки, обновляем интерфейс');
                            
                            // Обновляем данные модального окна
                            if (typeof window.updateDealModalData === 'function') {
                                window.updateDealModalData(response.deal);
                            }
                            
                            // Обновляем файловые ссылки
                            if (typeof window.forceUpdateFileLinksFromDealData === 'function') {
                                window.forceUpdateFileLinksFromDealData(response.deal);
                            }
                            
                            // Обновляем список документов если есть данные
                            if (event.detail && event.detail.documents && typeof window.updateDocumentsList === 'function') {
                                window.updateDocumentsList(event.detail.documents);
                            }
                            
                            // Также попробуем обновить документы из ответа сервера
                            if (response.deal.documents && typeof window.updateDocumentsList === 'function') {
                                window.updateDocumentsList(response.deal.documents);
                            }
                            
                            console.log('Интерфейс модального окна обновлен после загрузки документов');
                        }
                    })
                    .fail(function() {
                        console.warn('Не удалось загрузить актуальные данные сделки после загрузки документов');
                    });
            }
        }, 500);
    });

    // Дополнительный обработчик для события обновления сделки
    $(document).off('dealUpdated.autoUpdate').on('dealUpdated.autoUpdate', function(event) {
        console.log('Событие dealUpdated получено, обновляем файловые ссылки');
        
        setTimeout(function() {
            if (typeof window.forceUpdateFileLinks === 'function') {
                window.forceUpdateFileLinks();
            }
        }, 200);
    });

    // Обработчик для принудительного обновления при открытии модального окна
    $(document).off('modalShown.autoUpdate').on('modalShown.autoUpdate', function() {
        console.log('Модальное окно показано, обновляем файловые ссылки');
        
        setTimeout(function() {
            if (typeof window.forceUpdateFileLinks === 'function') {
                window.forceUpdateFileLinks();
            }
        }, 300);
    });

    // Инициализация при загрузке DOM
    $(document).ready(function() {
        console.log('document-update-fix.js: система автоматического обновления документов инициализирована');
        
        // Привязываем событие к модальному окну если оно существует
        $('#editModal').off('shown.bs.modal.autoUpdate').on('shown.bs.modal.autoUpdate', function() {
            $(document).trigger('modalShown.autoUpdate');
        });
    });

    console.log('document-update-fix.js: все функции определены глобально');
})();
