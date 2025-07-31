<div class="modal modal__deal" id="editModal" style="display: none;">
    <!-- Подключаем стили для компонентов брифа -->
    <link rel="stylesheet" href="{{ asset('css/brief-search.css') }}">
    <!-- Подключаем стили для загрузки больших файлов -->
    <link rel="stylesheet" href="{{ asset('css/large-file-upload.css') }}">
    <!-- Подключаем стили для Select2 полей координатора и партнера -->
    <link rel="stylesheet" href="{{ asset('css/select2-coordinator-partner.css') }}">
    
    <div class="modal-content">
        @if(isset($deal) && isset($dealFields))
            <!-- Подключаем компонент для AJAX-обновления сделки без перезагрузки -->
            @include('deals.partials.components.ajax_deal_update')
            
            <div class="button__points">
                <p>{{ $deal->project_number  ?? 'Не указан'}}</p>
                <span class="close-modal" id="closeModalBtn" title="Закрыть окно без сохранения изменений">&times;</span>
                <button data-target="Заказ" class="buttonSealaActive" title="Показать информацию о заказе">Заказ</button>
                <button data-target="Работа над проектом" title="Показать информацию о работе над проектом">Работа над проектом</button>
                @if (in_array(Auth::user()->status, ['coordinator', 'admin', 'partner']))
                    <button data-target="Финал проекта" title="Показать информацию о финальной стадии проекта">Финал проекта</button>
                @endif                <!-- Вкладка Документы -->
                <button data-target="Документы" title="Показать загруженные документы сделки">Документы</button>
                
                <!-- Вкладка Поиск брифа -->
                <button data-target="Бриф" title="Поиск и привязка брифа к сделке">Бриф</button>
                
                @include('deals.partials.components.header_actions')
            </div>
             <!-- Форма редактирования сделки с AJAX-обработкой -->
            <form id="editForm" method="POST" enctype="multipart/form-data" action="{{ route('deal.update', $deal->id) }}" onsubmit="return false;">
                @csrf
                @method('PUT')
                <input type="hidden" name="deal_id" id="dealIdField" value="{{ $deal->id }}">
                @php
                    $userRole = Auth::user()->status;
                    // Получаем документы используя метод модели
                    $dealDocuments = isset($documents) ? $documents : (method_exists($deal, 'getDocuments') ? $deal->getDocuments() : []);
                @endphp
                
                <!-- Подключаем вкладки -->
                @include('deals.partials.components.tab_zakaz')
                @include('deals.partials.components.tab_rabota')
                
                @if (in_array(Auth::user()->status, ['coordinator', 'admin', 'partner']))
                    @include('deals.partials.components.tab_final')
                @endif
                  <!-- Передаем документы во вкладку -->
                @include('deals.partials.components.tab_documents', ['documents' => $dealDocuments])
                  <!-- Вкладка с поиском брифа -->
                @include('deals.partials.components.tab_brief')
                <!-- Подключаем скрипты для работы с брифами -->
                @include('deals.partials.components.scripts_brief_search')
                  <div class="form-buttons">
                    <!-- Скрытая стандартная кнопка сохранения (будет заменена на AJAX-версию) -->
                    <button type="submit" id="saveButton" title="Сохранить все изменения сделки" style="display: none;">Сохранить</button>
                    
                    <!-- Добавляем кнопку удаления для администратора -->
                    @if (Auth::user()->status == 'admin')
                        <button type="button" class="delete-deal-button" onclick="confirmDeleteDeal({{ $deal->id }})" title="Удалить сделку">
                            Удалить сделку
                        </button>
                    @endif
                </div>
            </form>
        @endif
    </div>
</div>

<!-- Проверка наличия Select2 и подключение, если не подключен -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Проверяем, подключен ли Select2
        if (typeof $.fn.select2 === 'undefined') {
            console.warn('Select2 не загружен, загружаем его динамически...');
            
            // Подключаем CSS
            var selectCss = document.createElement('link');
            selectCss.rel = 'stylesheet';
            selectCss.href = 'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css';
            document.head.appendChild(selectCss);
            
            // Подключаем JavaScript
            var selectScript = document.createElement('script');
            selectScript.src = 'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js';
            selectScript.onload = function() {
                // После загрузки инициализируем Select2
                if ($('#editModal').is(':visible')) {
                    setTimeout(function() {
                        if (typeof initModalSelects === 'function') {
                            initModalSelects();
                        }
                    }, 300);
                }
            };
            document.body.appendChild(selectScript);
        }
    });
</script>

<!-- Инициализация загрузки документов -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // ПРИМЕЧАНИЕ: Новая система больших файлов (large-file-upload.js) также обрабатывает события change
        // Этот код оставлен для совместимости с предпросмотром файлов
        
        // Инициализация загрузки файлов в разделе документов
        const documentUploadInputs = document.querySelectorAll('.document-upload-input');
        
        documentUploadInputs.forEach(input => {
            input.addEventListener('change', function(e) {
                const files = this.files;
                if (files.length > 0) {
                    const fileList = this.closest('.documents-container').querySelector('.document-items');
                    
                    // Если списка документов нет, создаем его
                    if (!fileList) {
                        const documentsList = document.createElement('div');
                        documentsList.className = 'documents-list';
                        documentsList.innerHTML = '<h4>Загруженные документы</h4><ul class="document-items"></ul>';
                        this.closest('.documents-container').insertBefore(documentsList, this.closest('.document-upload-section'));
                    }
                    
                    // Добавляем файлы в список для предпросмотра
                    Array.from(files).forEach(file => {
                        const fileExtension = file.name.split('.').pop().toLowerCase();
                        let iconClass = 'fa-file';
                        
                        // Определяем иконку на основе расширения
                        if (['jpg', 'jpeg', 'png', 'gif'].includes(fileExtension)) iconClass = 'fa-file-image';
                        else if (fileExtension === 'pdf') iconClass = 'fa-file-pdf';
                        else if (['doc', 'docx'].includes(fileExtension)) iconClass = 'fa-file-word';
                        else if (['xls', 'xlsx'].includes(fileExtension)) iconClass = 'fa-file-excel';
                        
                        const fileItem = document.createElement('li');
                        fileItem.className = 'document-item document-preview';
                        fileItem.innerHTML = `
                            <div class="document-link">
                                <i class="fas ${iconClass}"></i>
                                <span class="document-name">${file.name.split('.')[0]}</span>
                                <span class="document-extension">.${fileExtension}</span>
                            </div>
                        `;
                        
                        const documentItems = document.querySelector('.document-items');
                        if (documentItems) {
                            documentItems.appendChild(fileItem);
                        }
                    });
                }
            });
        });
    });
</script>

<!-- Подключаем стили -->
@include('deals.partials.components.styles')

<!-- Подключаем стили для новой структуры -->
@include('deals.partials.components.tabs-style')

<!-- Подключаем улучшенные стили и скрипты для вкладок -->
@include('deals.partials.components.tabs-improved-includes')

<!-- Подключаем единую систему вкладок -->
@include('deals.partials.components.unified_tabs_system')

<!-- Подключаем основные скрипты -->
@include('deals.partials.components.scripts')

<!-- Подключаем скрипты для новой структуры -->
@include('deals.partials.components.tabs-scripts')

<!-- Подключаем исправление автоматического обновления документов -->
<script src="{{ asset('js/document-update-fix.js') }}"></script>

<!-- Дополнительный скрипт для обеспечения корректной работы вкладок -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Единая система вкладок инициализируется автоматически
        console.log('dealModal: DOM загружен, система вкладок готова');
    });
</script>

<!-- Добавляем скрипт для настройки Select2 в модальном окне -->
<script>
    // Обработчик события открытия модального окна
    $(document).ready(function() {
        $('#editModal').on('shown.bs.modal', function() {
            // Инициализируем маски телефона при открытии модального окна
            console.log('shown.bs.modal: инициализация масок телефона');
            window.initPhoneMasks();
            
            // Вызываем событие загрузки модального окна для инициализации AJAX-обработки
            $(document).trigger('dealModalLoaded');
        });
        
        // Добавляем обработчик события загрузки модального окна
        $(document).on('dealModalLoaded', function() {
            setTimeout(function() {
                // Инициализируем маски телефона
                console.log('dealModalLoaded: инициализация масок телефона');
                window.initPhoneMasks();
                
                if (typeof window.forceUpdateFileLinks === 'function') {
                    console.log('Обновление файловых ссылок после события dealModalLoaded');
                    window.forceUpdateFileLinks();
                }
                
                // Повторная инициализация вкладок для модального окна
                if (typeof window.reinitializeTabsForModal === 'function') {
                    console.log('Повторная инициализация вкладок для модального окна');
                    window.reinitializeTabsForModal();
                }
            }, 300);
        });
    });

    // Функция для инициализации масок телефона
    window.initPhoneMasks = function() {
        console.log('Инициализация масок телефона...');
        
        // Находим все поля с классом maskphone, которые еще не обработаны
        const phoneInputs = document.querySelectorAll("input.maskphone:not([data-mask-initialized])");
        
        phoneInputs.forEach(function(input) {
            // Помечаем как обработанное
            input.setAttribute('data-mask-initialized', 'true');
            
            // Добавляем обработчики событий
            input.addEventListener("input", phoneMask);
            input.addEventListener("focus", phoneMask);
            input.addEventListener("blur", phoneMask);
            
            console.log('Маска телефона добавлена к полю:', input.id || input.name);
        });
    };
    
    // Функция маски телефона
    function phoneMask(event) {
        const blank = "+_ (___) ___-__-__";
        let i = 0;
        const val = this.value.replace(/\D/g, "").replace(/^8/, "7").replace(/^9/, "79");
        
        this.value = blank.replace(/./g, function(char) {
            if (/[_\d]/.test(char) && i < val.length) return val.charAt(i++);
            return i >= val.length ? "" : char;
        });
        
        if (event.type === "blur") {
            if (this.value.length == 2) this.value = "";
        } else {
            // Добавляем проверку наличия метода setSelectionRange
            if (this.setSelectionRange) {
                this.setSelectionRange(this.value.length, this.value.length);
            }
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        // Инициализируем маски при загрузке страницы
        window.initPhoneMasks();
        
        // Инициализация масок телефона при открытии модального окна
        $('.edit-deal-btn').on('click', function() {
            setTimeout(function() {
                console.log('Инициализация масок телефона после открытия модального окна');
                window.initPhoneMasks();
            }, 500);
        });
        
        // Модифицируем функцию initModalFunctions для инициализации масок
        function initModalFunctions() {
            // Добавляем инициализацию масок телефона
            console.log('initModalFunctions: инициализация масок телефона');
            window.initPhoneMasks();
        }
        
        // Делаем функцию доступной глобально
        window.initModalFunctions = initModalFunctions;
        
        // Инициализация файловых ссылок при загрузке DOM модального окна
        @if(isset($deal))
        setTimeout(function() {
            const dealData = @json($deal);
            if (typeof updateFileLinksInDealModal === 'function') {
                console.log('Инициализация файловых ссылок при загрузке DOM');
                updateFileLinksInDealModal(dealData);
            }
            
            // Дублирующий вызов с использованием принудительной функции
            if (typeof window.forceUpdateFileLinks === 'function') {
                console.log('Принудительная инициализация файловых ссылок при загрузке DOM');
                window.forceUpdateFileLinks();
            }
        }, 500);
        @endif
    });
    
    // Добавляем явный вызов инициализации масок после загрузки модального окна AJAX 
    $(document).ajaxComplete(function(event, xhr, settings) {
        if (settings.url.includes('/deal/') && settings.url.includes('/modal')) {
            setTimeout(function() {
                console.log('Инициализация масок телефона после AJAX загрузки модального окна');
                window.initPhoneMasks();
                
                // Инициализируем файловые ссылки при загрузке модального окна
                const modalData = xhr.responseJSON;
                if (modalData && modalData.deal) {
                    console.log('Инициализация файловых ссылок после AJAX загрузки модального окна');
                    if (typeof updateFileLinksInDealModal === 'function') {
                        updateFileLinksInDealModal(modalData.deal);
                    }
                }
            }, 300);
        }
    });
    
    // Функция для принудительного обновления файловых ссылок
    window.forceUpdateFileLinks = function() {
        console.log('Принудительное обновление файловых ссылок');
        
        @if(isset($deal))
        const dealData = @json($deal);
        console.log('Данные сделки из PHP:', dealData);
        @else
        // Если данные сделки не переданы из PHP, попробуем получить их из скрытого поля
        const dealId = $('#dealIdField').val();
        if (!dealId) {
            console.warn('ID сделки не найден, не можем обновить ссылки');
            return;
        }
        
        // Получаем данные сделки через AJAX
        $.get(`/deal/${dealId}/data`, function(response) {
            if (response.deal) {
                forceUpdateFileLinksFromDealData(response.deal);
            }
        }).fail(function() {
            console.error('Не удалось загрузить данные сделки для обновления ссылок');
        });
        return;
        @endif
        
        // Обновляем ссылки для всех файловых полей
        const fileFields = [
            'work_act', 'chat_screenshot', 'plan_final', 'final_collage', 'measurements_file',
            'final_floorplan', 'final_project_file', 'archicad_file', 'contract_attachment', 'execution_order_file'
        ];
        
        fileFields.forEach(function(fieldName) {
            const yandexUrlField = 'yandex_url_' + fieldName;
            const originalNameField = 'original_name_' + fieldName;
            const yandexUrl = dealData[yandexUrlField];
            const originalName = dealData[originalNameField] || 'Просмотр файла';
            
            if (yandexUrl && yandexUrl.trim() !== '') {
                // Удаляем существующие ссылки
                $(`input[name="${fieldName}"]`).siblings('.file-link.yandex-file-link').remove();
                
                // Создаем новую ссылку
                const newFileLink = $(`
                    <div class="file-link yandex-file-link">
                        <a href="${yandexUrl}" target="_blank" title="Открыть файл, загруженный на Яндекс.Диск">
                            <i class="fas fa-cloud-download-alt"></i> ${originalName}
                        </a>
                    </div>
                `);
                
                // Добавляем ссылку после поля ввода файла
                $(`input[name="${fieldName}"]`).after(newFileLink);
                console.log(`Создана файловая ссылка для поля ${fieldName}:`, yandexUrl);
            }
        });
    };
    
    // Вызываем принудительное обновление ссылок при открытии модального окна
    $(document).on('click', '.edit-deal-btn, [data-target="#editModal"]', function() {
        setTimeout(function() {
            if (typeof window.forceUpdateFileLinks === 'function') {
                window.forceUpdateFileLinks();
            }
        }, 800);
    });
    
    // Также вызываем при событии показа модального окна
    $('#editModal').on('shown.bs.modal', function() {
        setTimeout(function() {
            if (typeof window.forceUpdateFileLinks === 'function') {
                window.forceUpdateFileLinks();
            }
        }, 300);
    });
    
    // Дополнительная инициализация при изменении вкладок
    $(document).on('click', '.button__points button[data-target="Финал проекта"]', function() {
        setTimeout(function() {
            if (typeof window.forceUpdateFileLinks === 'function') {
                console.log('Обновление файловых ссылок при переключении на вкладку "Финал проекта"');
                window.forceUpdateFileLinks();
            }
        }, 500);
    });
    
    // Добавляем обработчик для события успешного сохранения сделки
    $(document).on('dealUpdated', function(event) {
        console.log('Событие dealUpdated получено, обновляем файловые ссылки');
        setTimeout(function() {
            if (typeof window.forceUpdateFileLinks === 'function') {
                window.forceUpdateFileLinks();
            }
        }, 100);
    });
    
    // Добавляем обработчик завершения большого файла
    $(document).on('largeFileUploadComplete', function(event) {
        console.log('Большой файл загружен, обновляем ссылки');
        setTimeout(function() {
            // Перезагружаем данные сделки из сервера
            const dealId = $('#dealIdField').val();
            if (dealId) {
                $.get(`/deal/${dealId}/data`, function(response) {
                    if (response.success && response.deal) {
                        forceUpdateFileLinksFromDealData(response.deal);
                    }
                });
            }
        }, 500);
    });
    
    // Добавляем обработчик завершения загрузки документов
    $(document).on('documentUploadComplete', function(event) {
        console.log('Документы загружены, обновляем интерфейс модального окна');
        setTimeout(function() {
            // Перезагружаем данные сделки из сервера
            const dealId = $('#dealIdField').val();
            if (dealId) {
                $.get(`/deal/${dealId}/data`, function(response) {
                    if (response.success && response.deal) {
                        console.log('Обновляем данные модального окна после загрузки документов');
                        
                        // Обновляем данные в модальном окне если доступна функция
                        if (typeof updateDealModalData === 'function') {
                            updateDealModalData(response.deal);
                        }
                        
                        // Принудительно обновляем файловые ссылки
                        forceUpdateFileLinksFromDealData(response.deal);
                        
                        // Также обновляем список документов в интерфейсе если есть функция
                        if (typeof updateDocumentsList === 'function' && event.detail && event.detail.documents) {
                            updateDocumentsList(event.detail.documents);
                        }
                    }
                }).fail(function() {
                    console.warn('Не удалось обновить данные сделки после загрузки документов');
                });
            }
        }, 500);
    });
    
    // Функция для обновления ссылок из конкретных данных сделки
    function forceUpdateFileLinksFromDealData(dealData) {
        console.log('Обновление файловых ссылок из данных сделки', dealData);
        
        const fileFields = [
            'work_act', 'chat_screenshot', 'plan_final', 'final_collage', 'measurements_file',
            'final_floorplan', 'final_project_file', 'archicad_file', 'contract_attachment', 'execution_order_file'
        ];
        
        fileFields.forEach(function(fieldName) {
            const yandexUrlField = 'yandex_url_' + fieldName;
            const originalNameField = 'original_name_' + fieldName;
            const yandexUrl = dealData[yandexUrlField];
            const originalName = dealData[originalNameField] || 'Просмотр файла';
            
            if (yandexUrl && yandexUrl.trim() !== '') {
                // Удаляем существующие ссылки
                $(`input[name="${fieldName}"]`).siblings('.file-link.yandex-file-link').remove();
                
                // Создаем новую ссылку
                const newFileLink = $(`
                    <div class="file-link yandex-file-link">
                        <a href="${yandexUrl}" target="_blank" title="Открыть файл, загруженный на Яндекс.Диск">
                            <i class="fas fa-cloud-download-alt"></i> ${originalName}
                        </a>
                    </div>
                `);
                
                // Добавляем ссылку после поля ввода файла
                $(`input[name="${fieldName}"]`).after(newFileLink);
                console.log(`Создана файловая ссылка для поля ${fieldName}:`, yandexUrl);
            }
        });
    }
    
    // Глобальная функция для обновления файловых ссылок из данных сделки
    window.updateFileLinksInDealModal = function(dealData) {
        console.log('Обновляем файловые ссылки в модальном окне сделки (глобальная функция)', dealData);
        
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
    };
</script>

<!-- Инициализация Large File Uploader только если еще не инициализирован -->
<script>
// Дополнительная инициализация для модального окна
$(document).ready(function() {
    // Проверяем, что Large File Uploader уже загружен основным скриптом
    setTimeout(function() {
        if (typeof window.largeFileUploader !== 'undefined') {
            console.log('Large File Uploader уже инициализирован');
        } else if (typeof window.LargeFileUploader !== 'undefined') {
            console.log('Инициализируем Large File Uploader для модального окна');
            if (!window.largeFileUploader) {
                window.largeFileUploader = new window.LargeFileUploader();
                window.largeFileUploader.init();
            }
        }
    }, 500);
});

// Инициализация при открытии модального окна
$(document).on('modalOpened', '#editModal', function() {
    setTimeout(function() {
        // Инициализируем маски телефона
        console.log('modalOpened: инициализация масок телефона');
        window.initPhoneMasks();
        
        if (typeof window.largeFileUploader !== 'undefined') {
            window.largeFileUploader.init();
            console.log('Large File Uploader переинициализирован для модального окна');
        } else if (typeof window.LargeFileUploader !== 'undefined' && !window.largeFileUploader) {
            window.largeFileUploader = new window.LargeFileUploader();
            window.largeFileUploader.init();
            console.log('Large File Uploader создан для модального окна');
        }
        
        // Инициализируем Select2 поля после открытия модального окна
        if (typeof initModalSelects === 'function') {
            initModalSelects();
            console.log('Select2 поля переинициализированы для модального окна');
        }
    }, 200);
});
</script>

<!-- Подключение диагностического скрипта для Select2 (только в режиме разработки) -->
@if(config('app.debug'))
<script src="{{ asset('js/select2-diagnostic.js') }}"></script>
<script src="{{ asset('js/city-field-test.js') }}"></script>
@endif





