@if(session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
@endif

@if($errors->any())
    <div class="alert alert-danger">
        <ul>
            @foreach($errors->all() as $error)
              <li>{{ $error }}</li>
            @endforeach
        </ul> 
    </div>
@endif

<form id="create-deal-form" action="{{ route('deals.store') }}" method="POST" enctype="multipart/form-data">
    @csrf
    <!-- Добавляем скрытые поля для datestamps, которые заполняются скриптом -->
    <input type="hidden" name="start_date" id="start_date">
    <input type="hidden" name="project_duration" id="project_duration">
    <input type="hidden" name="project_end_date" id="project_end_date">

    <!-- БЛОК: Основная информация -->
    <fieldset class="module">
        <legend><h1>{{ $title_site }}</h1></legend>
        
        <!-- Добавляем поле для названия сделки -->
        <div class="form-group-deal">
            <label for="price_service_option" title="Выберите услугу из доступного прайса"><i class="fas fa-list-check"></i> Услуга по прайсу: <span class="required">*</span></label>
            <select id="price_service_option" name="price_service_option" class="form-control" required title="Обязательное поле: выберите тип услуги по прайсу">
                <option value="">-- Выберите услугу --</option>
                <option value="Визуализация на одну комнату" title="Трехмерная визуализация одной комнаты">Визуализация на одну комнату</option>
                <option value="экспресс планировка" title="Базовая планировка помещения без детализации">Экспресс планировка</option>
                <option value="экспресс планировка с коллажами" title="Планировка с добавлением коллажей для визуализации">Экспресс планировка с коллажами</option>
                <option value="экспресс проект с электрикой" title="Проект с планом электрических точек и размещения розеток">Экспресс проект с электрикой</option>
                <option value="экспресс планировка с электрикой и коллажами" title="Полный проект планировки с электросхемами и визуальными коллажами">Экспресс планировка с электрикой и коллажами</option>
            
                <option value="экспресс рабочий проект" title="Проект с рабочей документацией">Экспресс рабочий проект</option>
                <option value="экспресс эскизный проект с рабочей документацией" title="Концептуальный эскизный проект с необходимой рабочей документацией">Экспресс эскизный проект с рабочей документацией</option>
                <option value="экспресс 3Dвизуализация с коллажами" title="Только 3D визуализация пространства без рабочей документации">экспресс 3Dвизуализация с коллажами</option>
                <option value="экспресс полный дизайн-проект" title="Комплексный дизайн-проект включающий все этапы проектирования">Экспресс полный дизайн-проект</option>
                <option value="360 градусов" title="Панорамная 360-градусная визуализация пространства">360 градусов</option>
                                <option value="Визуализация на одну комнату" title="Визуализация на одну комнату">Визуализация на одну комнату</option>
            </select>
        </div>  
        <div class="form-group-deal">
            <label for="rooms_count_pricing" title="Укажите количество комнат для расчёта цены"><i class="fas fa-door-open"></i> Количество комнат по прайсу:</label>
            <input type="text" id="rooms_count_pricing" name="rooms_count_pricing" class="form-control" title="Введите количество комнат (можно указать цифры и буквы)" placeholder="Например: 3, студия, 2+1">
        </div>
        <div class="form-group-deal">
     
            <label for="package" title="Выберите услугу из доступного прайса"><i class="fas fa-list-check"></i> Пакет: <span class="required">*</span></label>
            <select id="package" name="package" class="form-control" required title="Обязательное поле: выберите тип услуги по прайсу">
                <option value="">-- Выберите Пакет --</option>
                <option value="Первый пакет 1400 м2" title="Первый пакет 1400 м2">Первый пакет 1400 м2</option>
                <option value="Второй пакет 85% комиссия" title="Второй пакет 85% комиссия">Второй пакет 85% комиссия</option>
                 <option value="Третий пакет 55% комиссия" title="Третий пакет 55% комиссия">Третий пакет 55% комиссия</option>
                 <option value="Партнер 75% комиссия" title="Партнер 75% комиссия">Партнер 75% комиссия</option>
            </select>
        </div>
        <div class="form-group-deal">
            <label for="client_phone" title="Контактный номер телефона клиента"><i class="fas fa-phone"></i> Телефон: <span class="required">*</span></label>
            <input type="text" id="client_phone" name="client_phone"  class="form-control maskphone" required title="Обязательное поле: введите номер телефона клиента в формате +7 (XXX) XXX-XX-XX">
        </div>
        <div class="form-group-deal">
            <label for="client_timezone" title="Город проживания клиента и его часовой пояс"><i class="fas fa-city"></i> Город/часовой пояс:</label>
            <select id="client_timezone" name="client_timezone" class="form-control" title="Выберите город клиента для определения часового пояса">
                 <option value="">-- Выберите город --</option>
            </select>
        </div>
        
        <!-- Добавляем поле для имени клиента -->
        <div class="form-group-deal">
            <label for="client_name" title="Имя клиента"><i class="fas fa-user"></i> Имя клиента: <span class="required">*</span></label>
            <input type="text" id="client_name" name="client_name" class="form-control" required title="Обязательное поле: введите имя клиента" maxlength="255">
        </div>
       
       

        <script>
            document.addEventListener("DOMContentLoaded", function(){
                // Автоматически устанавливаем сегодняшнюю дату в поле "Дата начала проекта"
                var today = new Date().toISOString().split("T")[0];
                document.getElementById("start_date").value = today;
                
                // При изменении срока проекта обновляем "Дата завершения проекта"
                document.getElementById("project_duration").addEventListener("input", function(){
                     var duration = parseInt(this.value, 10);
                     if (!isNaN(duration)) {
                         var startDate = new Date(document.getElementById("start_date").value);
                         startDate.setDate(startDate.getDate() + duration);
                         var endDate = startDate.toISOString().split("T")[0];
                         document.getElementById("project_end_date").value = endDate;
                     } else {
                         document.getElementById("project_end_date").value = "";
                     }
                });

                // Добавляем обработку для загрузки файлов
                initFileUpload();
            });

            // Функция инициализации загрузки файлов
            function initFileUpload() {
                const dropzone = document.getElementById('dropzone-documents');
                const fileInput = document.getElementById('documents');
                const filePreview = document.getElementById('file-preview');
                const fileList = filePreview.querySelector('.file-list');
                const maxSize = 1572864000; // 1500 МБ в байтах
                
                // Предотвращаем стандартное поведение при перетаскивании
                ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
                    dropzone.addEventListener(eventName, preventDefaults, false);
                });
                
                function preventDefaults(e) {
                    e.preventDefault();
                    e.stopPropagation();
                }
                
                // Подсветка при перетаскивании
                ['dragenter', 'dragover'].forEach(eventName => {
                    dropzone.addEventListener(eventName, highlight, false);
                });
                
                ['dragleave', 'drop'].forEach(eventName => {
                    dropzone.addEventListener(eventName, unhighlight, false);
                });
                
                function highlight() {
                    dropzone.classList.add('highlight');
                }
                
                function unhighlight() {
                    dropzone.classList.remove('highlight');
                }
                
                // Обработка при отпускании файлов
                dropzone.addEventListener('drop', handleDrop, false);
                
                function handleDrop(e) {
                    const dt = e.dataTransfer;
                    const files = dt.files;
                    handleFiles(files);
                }
                
                // Обработка изменения через input
                fileInput.addEventListener('change', function() {
                    handleFiles(this.files);
                });
                
                // Обработка файлов
                function handleFiles(files) {
                    // Проверяем общий размер всех загружаемых файлов
                    let totalSize = 0;
                    for (let i = 0; i < files.length; i++) {
                        totalSize += files[i].size;
                    }
                    
                    if (totalSize > maxSize) {
                        alert(`Общий размер файлов превышает 100 МБ. Пожалуйста, выберите файлы меньшего размера.`);
                        return;
                    }
                    
                    // Добавляем файлы в список предпросмотра
                    Array.from(files).forEach(file => {
                        const fileSize = formatFileSize(file.size);
                        const fileItem = document.createElement('li');
                        fileItem.className = 'file-item';
                        
                        // Определяем тип файла для иконки
                        let fileIcon = 'fa-file';
                        if (file.type.match('image.*')) fileIcon = 'fa-file-image';
                        else if (file.type.match('video.*')) fileIcon = 'fa-file-video';
                        else if (file.type.match('audio.*')) fileIcon = 'fa-file-audio';
                        else if (file.type.match('text.*')) fileIcon = 'fa-file-alt';
                        else if (file.type.match('application/pdf')) fileIcon = 'fa-file-pdf';
                        else if (file.type.match('application/zip')) fileIcon = 'fa-file-archive';
                        else if (file.type.match('application/msword')) fileIcon = 'fa-file-word';
                        else if (file.type.match('application/vnd.ms-excel')) fileIcon = 'fa-file-excel';
                        
                        fileItem.innerHTML = `
                            <i class="fas ${fileIcon}"></i>
                            <div class="file-info">
                                <div class="file-name">${file.name}</div>
                                <div class="file-size">${fileSize}</div>
                            </div>
                            <button type="button" class="remove-file" title="Удалить файл">
                                <i class="fas fa-times"></i>
                            </button>
                        `;
                        
                        fileList.appendChild(fileItem);
                        
                        // Добавляем обработчик на кнопку удаления
                        const removeButton = fileItem.querySelector('.remove-file');
                        removeButton.addEventListener('click', function() {
                            // Удаляем из FileList нельзя напрямую, поэтому создаем новый DataTransfer
                            const dt = new DataTransfer();
                            const input = document.getElementById('documents');
                            const { files } = input;
                            
                            for (let i = 0; i < files.length; i++) {
                                // Добавляем все файлы кроме удаляемого
                                if (files[i].name !== file.name) {
                                    dt.items.add(files[i]);
                                }
                            }
                            
                            input.files = dt.files;
                            fileItem.remove();
                        });
                    });
                    
                    // Показываем контейнер предпросмотра
                    filePreview.style.display = 'flex';
                }
                
                // Форматирование размера файла
                function formatFileSize(bytes) {
                    if (bytes === 0) return '0 Байт';
                    const k = 1024;
                    const sizes = ['Байт', 'КБ', 'МБ', 'ГБ'];
                    const i = Math.floor(Math.log(bytes) / Math.log(k));
                    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
                }
            }
        </script>
        
        <!-- Убираем блок выбора партнёра, если пользователь partner -->
        @if(auth()->user()->status == 'partner')
            <div class="form-group-deal">
                <label title="Информация о партнере сделки"><i class="fas fa-handshake"></i> Партнер</label>
                <p title="Вы являетесь партнером в этой сделке">{{ auth()->user()->name }}</p>
                <input type="hidden" name="office_partner_id" value="{{ auth()->id() }}">
            </div>
        @else
            <!-- Если не partner, отображаем выбор партнеров -->
            <div class="form-group-deal">
                <label for="office_partner_id" title="Выберите партнера для сделки"><i class="fas fa-handshake"></i> Партнер:</label>
                <select id="office_partner_id" name="office_partner_id" class="form-control select2-field" title="Выберите партнера, который будет участвовать в сделке">
                    <option value="">-- Не выбрано --</option>
                    @foreach($partners as $partner)
                        <option value="{{ $partner->id }}" title="{{ $partner->email ?? 'Email не указан' }}">{{ $partner->name }}</option>
                    @endforeach
                </select>
            </div>
        @endif

        <!-- Добавляем поле "Кто делает комплектацию" -->
        @if(in_array(auth()->user()->status, ['partner', 'coordinator', 'admin']))
            <div class="form-group-deal">
                <label for="completion_responsible" title="Укажите, кто отвечает за комплектацию проекта"><i class="fas fa-clipboard-check"></i> Кто делает комплектацию:<span class="required">*</span></label>
                <select id="completion_responsible" name="completion_responsible" class="form-control" required title="Обязательное поле: выберите ответственного за комплектацию">
                    <option value="">-- Выберите --</option>
                    <option value="клиент" title="Клиент самостоятельно выполняет комплектацию">Клиент</option>
                    <option value="партнер" title="Партнер отвечает за комплектацию">Партнер</option>
                    <option value="шопинг-лист" title="Предоставляется только список необходимых предметов">Шопинг-лист</option>
                    <option value="закупки и снабжение от УК" title="Управляющая компания берет на себя все закупки">Нужны закупки и снабжение от УК</option>
                </select>
            </div>
        @endif

        <!-- Убираем блок выбора координатора, если пользователь coordinator -->
        @if(auth()->user()->status == 'coordinator')
            <div class="form-group-deal">
                <label><i class="fas fa-user-tie"></i> Отв. координатор</label>
                <p>{{ auth()->user()->name }}</p>
                <input type="hidden" name="coordinator_id" value="{{ auth()->id() }}">
            </div>
        @else
           
        @endif
        <div class="form-group-deal">
            <label for="total_sum" title="Общая стоимость сделки"><i class="fas fa-ruble-sign"></i> Общая сумма:</label>
            <input type="number" step="0.01" id="total_sum" name="total_sum" class="form-control" title="Введите общую сумму сделки в рублях">
        </div>
        <div class="form-group-deal">
            <label for="payment_date" title="Дата поступления оплаты от клиента"><i class="fas fa-calendar-alt"></i> Дата оплаты:</label>
            <input type="date" id="payment_date" name="payment_date" class="form-control" title="Укажите дату, когда поступила или ожидается оплата">
        </div>
        <div class="form-group-deal">
            <label for="comment" title="Общий комментарий по сделке"><i class="fas fa-sticky-note"></i> Общий комментарий:</label>
            <textarea id="comment" name="comment" class="form-control" rows="3" maxlength="1000" title="Добавьте любую важную информацию о сделке"></textarea>
        </div>
       <!-- ДОБАВЛЯЕМ НОВОЕ ПОЛЕ ДЛЯ ЗАГРУЗКИ ДОКУМЕНТОВ -->
        <div class="form-group-deal" style="max-width: 100%;width: 100%;">
            <div class="custom-file-upload-container">
                <div class="file-upload-dropzone" id="dropzone-documents">
                    <input type="file" id="documents" name="documents[]" class="file-upload-input" multiple accept="*/*" 
                           data-max-size="1572864000" title="Выберите файлы или перетащите их сюда (до 1500 МБ)">
                    <div class="dropzone-placeholder">
                        <i class="fas fa-cloud-upload-alt"></i>
                        <span>Выберите файлы или перетащите их сюда</span>
                        <small>(максимум 1500 МБ)</small>
                    </div>
                </div>
            </div>
        </div>
    </fieldset>
     
    <button type="submit" class="btn btn-primary" title="Создать новую сделку на основе введенных данных">Создать сделку</button>
</form>

<!-- Добавляем скрипт инициализации Select2 -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Функция для инициализации Select2
        function initSelect2() {
            if (typeof $ === 'undefined' || typeof $.fn.select2 === 'undefined') {
                console.error('jQuery или Select2 не загружены!');
                
                // Пробуем загрузить jQuery, если его нет
                if (typeof $ === 'undefined') {
                    let script = document.createElement('script');
                    script.src = 'https://code.jquery.com/jquery-3.6.0.min.js';
                    script.onload = function() {
                        // После загрузки jQuery, загружаем Select2
                        let select2Script = document.createElement('script');
                        select2Script.src = 'https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js';
                        select2Script.onload = initSelect2;
                        document.head.appendChild(select2Script);
                    };
                    document.head.appendChild(script);
                    return;
                }
                
                // Пробуем загрузить Select2, если его нет
                if (typeof $.fn.select2 === 'undefined') {
                    let link = document.createElement('link');
                    link.rel = 'stylesheet';
                    link.href = 'https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css';
                    document.head.appendChild(link);
                    
                    let script = document.createElement('script');
                    script.src = 'https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js';
                    script.onload = initSelect2;
                    document.head.appendChild(script);
                    return;
                }
                
                return;
            }
            
            // Инициализируем все селекторы с классом select2-field
            $('.select2-field').each(function() {
                // Находим родительский контейнер для Select2
                var $parent = $(this).closest('.form-group-deal');
                if (!$parent.length) {
                    $parent = $(this).parent();
                }
                
                // Устанавливаем position: relative для родителя
                $parent.css({
                    'position': 'relative',
                    'overflow': 'visible'
                });
                
                // Сохраняем ширину родителя для использования в dropdown
                var parentWidth = $parent.width();
                
                // Добавляем уникальный идентификатор для родителя
                var uniqueId = 'select2-container-' + Math.random().toString(36).substring(2, 9);
                $parent.attr('data-select2-id', uniqueId);
                
                // Проверяем, был ли Select2 уже инициализирован для этого элемента
                if (!$(this).hasClass('select2-hidden-accessible')) {
                    $(this).select2({
                        width: '100%',
                        placeholder: $(this).attr('placeholder') || "Выберите значение",
                        allowClear: true,
                        language: 'ru',
                        dropdownParent: $parent // Указываем родительский контейнер
                    });
                    
                    // Добавляем обработчик для корректной установки ширины при открытии
                    $(this).on('select2:open', function() {
                        setTimeout(function() {
                            var containerWidth = $parent.width();
                            $('.select2-container--open .select2-dropdown').css({
                                'width': containerWidth + 'px', 
                                'min-width': containerWidth + 'px',
                                'max-width': containerWidth + 'px'
                            });
                        }, 0);
                    });
                }
            });
            
            // Инициализация для поля client_timezone
            $('#client_timezone').each(function() {
                // Проверяем, был ли Select2 уже инициализирован
                if (!$(this).hasClass('select2-hidden-accessible')) {
                    // Находим родительский контейнер
                    var $parent = $(this).closest('.form-group-deal');
                    if (!$parent.length) {
                        $parent = $(this).parent();
                    }
                    
                    // Устанавливаем position: relative для родителя
                    $parent.css({
                        'position': 'relative',
                      
                        'overflow': 'visible'
                    });
                    
                    // Добавляем уникальный идентификатор для родителя
                    var uniqueId = 'select2-timezone-' + Math.random().toString(36).substring(2, 9);
                    $parent.attr('data-select2-id', uniqueId);
                    
                    $.getJSON('/cities.json', function(data) {
                        // Группируем города по региону
                        var grouped = {};
                        $.each(data, function(i, item) {
                            var region = item.region || "Другие города";
                            grouped[region] = grouped[region] || [];
                            grouped[region].push({
                                id: item.city,
                                text: item.city
                            });
                        });
                        
                        // Преобразуем для Select2
                        var selectData = $.map(grouped, function(cities, region) {
                            return {
                                text: region,
                                children: cities
                            };
                        });
                        
                        // Инициализируем Select2
                        $('#client_timezone').select2({
                            data: selectData,
                            placeholder: "-- Выберите город --",
                            allowClear: true,
                            width: '100%',
                            minimumInputLength: 1,
                            language: 'ru',
                            dropdownParent: $parent // Указываем родительский контейнер
                        }).on('select2:open', function() {
                            setTimeout(function() {
                                var containerWidth = $parent.width();
                                $('.select2-container--open .select2-dropdown').css({
                                    'width': containerWidth + 'px', 
                                    'min-width': containerWidth + 'px',
                                    'max-width': containerWidth + 'px'
                                });
                            }, 0);
                        });
                    }).fail(function(err) {
                        console.error("Ошибка загрузки городов:", err);
                        
                        // Инициализация в случае ошибки загрузки городов
                        $('#client_timezone').select2({
                            placeholder: "-- Выберите город --",
                            allowClear: true,
                            width: '100%',
                            language: 'ru',
                            dropdownParent: $parent // Указываем родительский контейнер
                        });
                    });
                }
            });
        }
        
        // Запуск инициализации с небольшой задержкой
        setTimeout(initSelect2, 300);
        
        // Добавляем обработчик события для повторной инициализации при изменении DOM
        const observer = new MutationObserver(function() {
            initSelect2();
        });
        
        // Начинаем наблюдение за изменениями в форме
        const form = document.getElementById('create-deal-form');
        if (form) {
            observer.observe(form, {
                childList: true,
                subtree: true
            });
        }
        
        // Также инициализируем при изменении размера окна
        window.addEventListener('resize', function() {
            if ($('.select2-container--open').length) {
                $('.select2-hidden-accessible').select2('close');
                setTimeout(initSelect2, 200);
            }
        });
    });
</script>

<!-- Добавляем дополнительные стили для корректного отображения Select2 -->
<style>

    .specialist-name {
        font-weight: 500;
        margin-bottom: 2px;
    }
    
    .specialist-rating {
        font-size: 14px;
        color: #666;
    }
    
    .specialist-rating .fa-star, 
    .specialist-rating .fa-star-half-alt {
        color: #FFD700;
    }
    
    .rating-value {
        color: #666;
        font-size: 12px;
        margin-left: 4px;
    }
    
   
</style>

<!-- Исправленный скрипт для обработки файлов и отображения прогресса загрузки -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Находим форму создания сделки - правильный ID формы
        const dealForm = document.getElementById('create-deal-form');
        
        // Находим все поля для загрузки файлов
        const fileInputs = document.querySelectorAll('input[type="file"]');
        console.log('Найдено полей для загрузки файлов:', fileInputs.length);
        
        // Добавляем обработчики для каждого поля загрузки файлов
        fileInputs.forEach(input => {
            // Создаем контейнер для предпросмотра файлов если его еще нет
            let previewContainer = document.querySelector('.selected-files-preview') || document.createElement('div');
            if (!document.querySelector('.selected-files-preview')) {
                previewContainer.className = 'selected-files-preview';
                input.parentNode.insertBefore(previewContainer, input.nextSibling);
            }
            
            // Обработчик выбора файлов
            input.addEventListener('change', function(e) {
                // Очищаем контейнер предпросмотра
                previewContainer.innerHTML = '';
                
                if (this.files && this.files.length > 0) {
                    console.log(`Выбрано ${this.files.length} файлов для загрузки`);
                    
                    // Создаем заголовок для выбранных файлов
                    const title = document.createElement('div');
                    title.className = 'preview-title';
                    title.textContent = 'Выбранные файлы для загрузки:';
                    previewContainer.appendChild(title);
                    
                    // Создаем сетку для файлов
                    const fileGrid = document.createElement('div');
                    fileGrid.className = 'selected-files-grid';
                    
                    // Добавляем каждый файл в сетку
                    Array.from(this.files).forEach(file => {
                        const fileItem = document.createElement('div');
                        fileItem.className = 'selected-file-item';
                        
                        // Определяем класс иконки в зависимости от типа файла
                        let iconClass = 'fa-file';
                        let colorClass = 'file-default';
                        const fileExtension = file.name.split('.').pop().toLowerCase();
                        
                        if (['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp'].includes(fileExtension)) {
                            iconClass = 'fa-file-image';
                            colorClass = 'file-image';
                        } else if (fileExtension === 'pdf') {
                            iconClass = 'fa-file-pdf';
                            colorClass = 'file-pdf';
                        } else if (['doc', 'docx'].includes(fileExtension)) {
                            iconClass = 'fa-file-word';
                            colorClass = 'file-word';
                        } else if (['xls', 'xlsx', 'csv'].includes(fileExtension)) {
                            iconClass = 'fa-file-excel';
                            colorClass = 'file-excel';
                        } else if (['zip', 'rar', '7z', 'tar', 'gz'].includes(fileExtension)) {
                            iconClass = 'fa-file-archive';
                            colorClass = 'file-archive';
                        } else if (['mp4', 'avi', 'mov', 'wmv'].includes(fileExtension)) {
                            iconClass = 'fa-file-video';
                            colorClass = 'file-video';
                        } else if (['mp3', 'wav', 'ogg'].includes(fileExtension)) {
                            iconClass = 'fa-file-audio';
                            colorClass = 'file-audio';
                        } else if (['html', 'css', 'js', 'php', 'py'].includes(fileExtension)) {
                            iconClass = 'fa-file-code';
                            colorClass = 'file-code';
                        }
                        
                        // Создаем содержимое блока файла
                        fileItem.innerHTML = `
                            <div class="file-icon-container ${colorClass}">
                                <i class="fas ${iconClass}"></i>
                                <span class="file-ext">${fileExtension}</span>
                            </div>
                            <div class="file-details">
                                <div class="file-name" title="${file.name}">${file.name}</div>
                                <div class="file-size">${formatFileSize(file.size)}</div>
                            </div>
                        `;
                        
                        fileGrid.appendChild(fileItem);
                    });
                    
                    previewContainer.appendChild(fileGrid);
                    previewContainer.style.display = 'block';
                }
            });
        });
        
        // Функция для форматирования размера файла
        function formatFileSize(bytes) {
            if (bytes === 0) return '0 Байт';
            const k = 1024;
            const sizes = ['Байт', 'КБ', 'МБ', 'ГБ'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
        }
        
        // Обработчик отправки формы
        if (dealForm) {
            console.log('Добавляем обработчик на форму:', dealForm);
            
            dealForm.addEventListener('submit', function(e) {
                console.log('Форма отправляется!');
                
                // Проверяем, есть ли загружаемые файлы
                let hasFiles = false;
                fileInputs.forEach(input => {
                    if (input.files && input.files.length > 0) {
                        hasFiles = true;
                        console.log(`Файлы для загрузки обнаружены в поле ${input.name}`);
                    }
                });
                
                // Если есть файлы для загрузки, показываем индикатор загрузки
                if (hasFiles) {
                    e.preventDefault(); // Предотвращаем стандартную отправку формы
                    console.log('Показываем индикатор загрузки');
                    
                    // Показываем индикатор загрузки
                    const loader = document.getElementById('fullscreen-loader');
                    if (loader) {
                        loader.style.opacity = '1';
                        loader.style.visibility = 'visible';
                    } else {
                        console.error('Элемент fullscreen-loader не найден!');
                    }
                    
                    // Анимация прогресс-бара
                    let width = 0;
                    const progressBar = document.querySelector('.loader-progress-bar');
                    if (progressBar) {
                        const progressInterval = setInterval(function() {
                            if (width >= 95) {
                                clearInterval(progressInterval);
                            } else {
                                width += Math.random() * 5;
                                progressBar.style.width = width + '%';
                            }
                        }, 300);
                    }
                    
                    // Отправляем форму через FormData для поддержки загрузки файлов
                    const formData = new FormData(dealForm);
                    
                    console.log('Отправка формы через Fetch API');
                    fetch(dealForm.action, {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        credentials: 'same-origin'
                    })
                    .then(response => {
                        console.log('Ответ получен, статус:', response.status);
                        // Быстро заполняем прогресс-бар до 100%
                        if (progressBar) progressBar.style.width = '100%';
                        
                        // Проверяем статус ответа
                        if (response.ok) {
                            if (response.headers.get('content-type').includes('application/json')) {
                                return response.json();
                            } else {
                                // Если ответ не JSON, возможно это HTML страница или редирект
                                window.location.href = '/deal-cardinator';
                                return { success: true };
                            }
                        } else {
                            throw new Error('Ошибка при отправке формы. Статус: ' + response.status);
                        }
                    })
                    .then(data => {
                        console.log('Данные успешно получены:', data);
                        // Успешная отправка формы
                        setTimeout(() => {
                            if (loader) {
                                loader.style.opacity = '0';
                                loader.style.visibility = 'hidden';
                            }
                            
                            // Редирект на страницу сделки или другую указанную в ответе
                            if (data && data.redirect) {
                                window.location.href = data.redirect;
                            } else {
                                window.location.href = '/deal-cardinator'; // Дефолтный редирект
                            }
                        }, 500);
                    })
                    .catch(error => {
                        console.error('Ошибка:', error);
                        if (loader) {
                            loader.style.opacity = '0';
                            loader.style.visibility = 'hidden';
                        }
                        
                        // Отображаем ошибку пользователю
                        alert('Произошла ошибка при создании сделки: ' + error.message);
                    });
                } else {
                    console.log('Файлов для загрузки нет, форма отправляется обычным способом');
                    // Если нет файлов, форма отправляется стандартным способом
                }
            });
        } else {
            console.error('Форма создания сделки не найдена!');
        }
    });
</script>

<style>
    /* Стиль для индикатора загрузки - улучшенная версия */
    .fullscreen-loader {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(255, 255, 255, 0.8);
        display: flex;
        justify-content: center;
        align-items: center;
        z-index: 9999;
        opacity: 0;
        visibility: hidden;
        transition: opacity 0.3s ease, visibility 0.3s;
    }
    
    /* Добавляем класс для показа загрузчика с помощью стилей */
    .fullscreen-loader.show {
        opacity: 1 !important;
        visibility: visible !important;
    }
    
    /* Остальные стили fullscreen-loader оставляем как есть */
    .loader-wrapper {
        width: 100%;
        max-width: 500px;
        padding: 20px;
    }
    
    .loader-container {
        background-color: white;
        border-radius: 8px;
        box-shadow: 0 3px 15px rgba(0, 0, 0, 0.15);
        padding: 25px;
        text-align: center;
    }
    
    .loader-animation {
        display: flex;
        justify-content: center;
        margin-bottom: 20px;
        height: 40px;
        position: relative;
    }
    
    .loader-circle {
        width: 12px;
        height: 12px;
        background-color: #3498db;
        border-radius: 50%;
        margin: 0 5px;
        animation: bounce 1.5s infinite ease-in-out;
    }
    
    .loader-circle:nth-child(2) {
        animation-delay: 0.15s;
    }
    
    .loader-circle:nth-child(3) {
        animation-delay: 0.3s;
    }
    
    @keyframes bounce {
        0%, 80%, 100% {
            transform: scale(0);
        }
        40% {
            transform: scale(1);
        }
    }
    
    .loader-text h4 {
        margin: 0 0 10px;
        color: #333;
        font-weight: 600;
    }
    
    .loader-text p {
        margin: 0 0 15px;
        color: #666;
    }
    
    .loader-progress {
        height: 6px;
        background-color: #f1f1f1;
        border-radius: 3px;
        overflow: hidden;
    }
    
    .loader-progress-bar {
        height: 100%;
        background-color: #4caf50;
        width: 0;
        transition: width 0.3s ease;
    }
</style>

<!-- Улучшенные стили для отображения выбранных файлов -->
<style>
    /* Стили для контейнера предпросмотра выбранных файлов */
    .selected-files-preview {
        margin-top: 15px;
        padding: 15px;
        background-color: #f9f9f9;
        border-radius: 8px;
        border: 1px solid #e0e0e0;
        box-shadow: 0 2px 5px rgba(0,0,0,0.05);
        max-height: 400px;
        overflow-y: auto;
        display: none; /* По умолчанию скрыт */
    }
    
    /* Показываем контейнер, когда в нём есть содержимое */
    .selected-files-preview:not(:empty) {
        display: block;
    }
    
    .preview-title {
        font-weight: 600;
        font-size: 16px;
        color: #333;
        margin-bottom: 15px;
        padding-bottom: 8px;
        border-bottom: 1px solid #eee;
    }
    
    /* Сетка для файлов */
    .selected-files-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
        grid-gap: 15px;
    }
    
    /* Блок файла */
    .selected-file-item {
        background-color: white;
        border-radius: 6px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        padding: 12px;
        display: flex;
        align-items: center;
        transition: transform 0.2s, box-shadow 0.2s;
    }
    
    .selected-file-item:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.12);
    }
    
    /* Контейнер для иконки */
    .file-icon-container {
        width: 50px;
        height: 50px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: 12px;
        position: relative;
        flex-shrink: 0;
    }
    
    .file-icon-container i {
        font-size: 24px;
        color: white;
    }
    
    .file-ext {
        position: absolute;
        bottom: 0;
        left: 0;
        right: 0;
        background-color: rgba(0,0,0,0.3);
        color: white;
        font-size: 9px;
        text-align: center;
        border-bottom-left-radius: 8px;
        border-bottom-right-radius: 8px;
        padding: 1px 0;
        text-transform: uppercase;
    }
    
    /* Стили для разных типов файлов */
    .file-default { background-color: #607D8B; }
    .file-image { background-color: #4CAF50; }
    .file-pdf { background-color: #F44336; }
    .file-word { background-color: #2196F3; }
    .file-excel { background-color: #4CAF50; }
    .file-archive { background-color: #FF9800; }
    .file-video { background-color: #9C27B0; }
    .file-audio { background-color: #E91E63; }
    .file-code { background-color: #3F51B5; }
    
    /* Детали файла */
    .file-details {
        overflow: hidden;
    }
    
    .file-name {
        font-weight: 500;
        color: #333;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        margin-bottom: 3px;
    }
    
    .file-size {
        font-size: 12px;
        color: #666;
    }
    
    /* Адаптивность */
    @media (max-width: 768px) {
        .selected-files-grid {
            grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
        }
    }
    
    @media (max-width: 480px) {
        .selected-files-grid {
            grid-template-columns: 1fr;
        }
    }
</style>

<!-- Удаляем старые стили для .file-item, .file-upload-preview, .file-list и другие, так как они заменены новыми -->
<style>
    /* Остальные стили для загрузки файлов */
    .custom-file-upload-container {
        margin-bottom: 15px;
    }
    
    .file-upload-dropzone {
        width: 100%;
        border: 2px dashed #ccc;
        border-radius: 8px;
        padding: 25px;
        text-align: center;
        background-color: #f9f9f9;
        cursor: pointer;
        transition: all 0.3s;
        position: relative;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .file-upload-dropzone.highlight {
        background-color: #e3f2fd;
        border-color: #2196F3;
    }
    
    .file-upload-input {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        opacity: 0;
        cursor: pointer;
    }
    
    .dropzone-placeholder {
        display: flex;
        flex-direction: column;
        align-items: center;
        color: #555;
    }
    
    .dropzone-placeholder i {
        font-size: 2.5rem;
        color: #2196F3;
        margin-bottom: 15px;
    }
    
    .dropzone-placeholder span {
        font-size: 16px;
        margin-bottom: 5px;
    }
    
    .dropzone-placeholder small {
        font-size: 12px;
        color: #777;
    }
</style>

<!-- Добавляем fullscreen-loader для отображения загрузки файлов -->
<div id="fullscreen-loader" class="fullscreen-loader">
    <div class="loader-wrapper">
        <div class="loader-container">
            <div class="loader-animation">
                <div class="loader-circle"></div>
                <div class="loader-circle"></div>
                <div class="loader-circle"></div>
            </div>
            <div class="loader-text">
                <h4>Загрузка файлов</h4>
                <p>Пожалуйста, подождите. Ваши файлы загружаются на сервер.</p>
                <div class="loader-progress">
                    <div class="loader-progress-bar"></div>
                </div>
            </div>
        </div>
    </div>
</div>
