@if (!empty($title_site) || !empty($description))
    <div class="form__title" id="top-title">
        <div class="form__title__info">
            @if (!empty($title_site))
                <h1>{{ $title_site }}</h1>
            @endif
            @if (!empty($description))
                <p>{{ $description }}</p>
            @endif
        </div>
        {{-- Навигационные кнопки --}}
        <div class="form__button flex between">
            <p class="form__button-ponel-p">Страница {{ $page }}/{{ $totalPages }}</p>
            @if ($page > 1)
                <button type="button" class=" btn-secondary" id="prevPageButton">Обратно</button>
                <script>
                    document.getElementById('prevPageButton').addEventListener('click', function() {
                        const prevPage = {{ $page }} - 1;
                        if (prevPage >= 1) {
                            window.location.href = '{{ url('commercial/questions/' . $brif->id) }}/' + prevPage;
                        }
                    });
                </script>
            @endif
            <button type="button" class=" btn-primary" onclick="goToNext()">Далее</button>
        </div>
    </div>
    <!-- Функции для навигации между шагами (если понадобятся в дальнейшем) -->
    <script>        function goToNext() {
            // Проверяем валидацию для страниц с обязательными полями
            if ([1, 2, 8].includes({{ $page }})) {
                if (!validateForm()) {
                    return false;
                }
            }

            // Проверка на наличие файлов для загрузки (страница 8)
            if ({{ $page }} === 8) {
                const fileInput = document.getElementById('fileInput');
                if (fileInput && fileInput.files && fileInput.files.length > 0) {
                    // Показываем анимацию загрузки
                    showLoader();

                    // Добавляем небольшую задержку перед отправкой формы для показа анимации
                    setTimeout(() => {
                        document.getElementById('zone-form').submit();
                    }, 300);
                    return;
                }
            }

            document.getElementById('zone-form').submit();
        }

        // Функция для показа анимации загрузки
        function showLoader() {
            const loader = document.getElementById('fullscreen-loader');
            loader.classList.add('show');

            // Анимируем прогресс-бар
            let width = 0;
            const progressBar = document.querySelector('.loader-progress-bar');
            const progressInterval = setInterval(function() {
                if (width >= 90) {
                    clearInterval(progressInterval);
                } else {
                    width += Math.random() * 3;
                    progressBar.style.width = width + '%';
                }
            }, 300);
        }

        // Функция для валидации полей
        function validateForm() {
            let isValid = true;
            let firstInvalidField = null;

            if ({{ $page }} === 1) {
                // Валидация для страницы 1 (название зон)
                const zoneNameInputs = document.querySelectorAll('input[name^="zones"][name$="[name]"]');
                zoneNameInputs.forEach(function(input) {
                    input.classList.remove('field-error');
                    if (!input.value.trim()) {
                        input.classList.add('field-error');
                        isValid = false;
                        if (!firstInvalidField) {
                            firstInvalidField = input;
                        }
                    }
                });
            } else if ({{ $page }} === 2) {
                // Валидация для страницы 2 (метраж зон)
                const areaInputs = document.querySelectorAll(
                    'input[name^="zones"][name$="[total_area]"], input[name^="zones"][name$="[projected_area]"]');
                areaInputs.forEach(function(input) {
                    input.classList.remove('field-error');
                    if (!input.value.trim()) {
                        input.classList.add('field-error');
                        isValid = false;
                        if (!firstInvalidField) {
                            firstInvalidField = input;
                        }
                    }
                });            } else if ({{ $page }} === 8) {
                // Валидация для страницы 8 (бюджет) - на самом деле проверка была на странице 7, но теперь проверяем на 8
                // Здесь мы можем добавить проверку, если нужно
                // Например, если нужно проверить, что есть хотя бы одно дополнительное пожелание
            }

            // Если есть невалидное поле, прокручиваем к нему
            if (firstInvalidField) {
                scrollToElement(firstInvalidField);
            }

            return isValid;
        }

        // Функция для прокрутки к элементу
        function scrollToElement(element) {
            const rect = element.getBoundingClientRect();
            const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
            const absoluteTop = rect.top + scrollTop;

            window.scrollTo({
                top: absoluteTop - 120,
                behavior: 'smooth'
            });

            setTimeout(() => {
                element.focus();
                element.classList.add('highlight-field');
                setTimeout(() => {
                    element.classList.remove('highlight-field');
                }, 2000);
            }, 500);
        }
    </script>

    <style>
        .field-error {
            border: 2px solid #ff0000 !important;
            background-color: #fff0f0 !important;
        }

        .highlight-field {
            animation: highlightPulse 1s ease-in-out;
            box-shadow: 0 0 10px 2px rgba(255, 0, 0, 0.5);
        }

        @keyframes highlightPulse {
            0% {
                box-shadow: 0 0 5px 1px rgba(255, 0, 0, 0.5);
            }

            50% {
                box-shadow: 0 0 15px 4px rgba(255, 0, 0, 0.8);
            }

            100% {
                box-shadow: 0 0 5px 1px rgba(255, 0, 0, 0.5);
            }
        }
        
        /* Стили для кнопки добавления зоны */
        #add-zone {
            cursor: pointer;
            transition: all 0.3s ease;
            border: 2px dashed #cccccc;
            background-color: #f9f9f9;
            margin-top: 15px;
        }
        
        #add-zone:hover {
            border-color: #007bff;
            background-color: #f0f7ff;
        }
        
        .blur__form__zone {
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 15px;
            text-align: center;
        }
        
        .blur__form__zone p {
            margin: 0;
            font-weight: 500;
            color: #666;
        }
        
        #add-zone:hover .blur__form__zone p {
            color: #007bff;
        }
        
        /* Улучшенный стиль для зон */
        .zone-item {
            margin-bottom: 15px;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            background: white;
            position: relative;
            padding: 15px;
        }
        div#zones-container h3 {
    width: 100%;
}
        .zone-item input,
        .zone-item textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid #e0e0e0;
            border-radius: 4px;
            margin-bottom: 8px;
        }
        
        .zone-item textarea {
            min-height: 80px;
            resize: vertical;
        }
        
        .zone-item-inputs-title {
            display: flex;
            width: 100%;
            align-items: center;
        }
        
        .zone-item-inputs-title input {
            flex-grow: 1;
            margin-bottom: 0;
            margin-right: 10px;
        }
        
        .remove-zone {
            cursor: pointer;
            padding: 5px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }
        
        .remove-zone:hover {
            opacity: 0.7;
        }
        
        .zone-area-inputs {
            display: flex;
            width: 100%;
            gap: 15px;
            margin-top: 10px;
        }
        
        .area-input-group {
            flex: 1;
        }
        
        .area-input-group label {
            display: block;
            font-weight: 500;
            margin-bottom: 5px;
        }
    </style>
@endif


<form action="{{ route('commercial.saveAnswers', ['id' => $brif->id, 'page' => $page]) }}" method="POST"
    id="zone-form" class="csrf-check" enctype="multipart/form-data">
    @csrf

    @if ($page == 1)
        <!-- Страница 1: Информация о зонах -->
        <div id="zones-container">
            {{-- Отображаем все существующие зоны --}}
            @foreach ($zones as $index => $zone)
                <div class="zone-item">
                    <div class="zone-item-inputs-title">
                        <input type="text" name="zones[{{ $index }}][name]" maxlength="250"
                            value="{{ $zone['name'] ?? '' }}" placeholder="Название зоны" class="form-control" />
                        <span class="remove-zone"><img src="/storage/icon/close__info.svg" alt=""></span>
                    </div>
                    <textarea maxlength="500" name="zones[{{ $index }}][description]" placeholder="Описание зоны"
                        class="form-control">{{ $zone['description'] ?? '' }}</textarea>
                </div>
            @endforeach
            
            {{-- Если зон вообще нет, добавляем пустую форму для первой зоны --}}
            @if(count($zones) == 0)
                <div class="zone-item">
                    <div class="zone-item-inputs-title">
                        <input type="text" name="zones[0][name]" placeholder="Название зоны" maxlength="250"
                            class="form-control" />
                        <span class="remove-zone"><img src="/storage/icon/close__info.svg" alt=""></span>
                    </div>
                    <textarea maxlength="500" name="zones[0][description]" placeholder="Описание зоны" class="form-control"></textarea>
                </div>
            @endif
             <div class="zone-item" id="add-zone">
                <div class="blur__form__zone">
                    <p>Добавить зону</p>
                </div>
            </div>
        </div>
        
        <!-- Добавляем JavaScript для работы с зонами -->
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                // Кнопка добавления новой зоны
                const addZoneButton = document.getElementById('add-zone');
                const zonesContainer = document.getElementById('zones-container');
                
                // Обработчик клика на кнопку добавления новой зоны
                addZoneButton.addEventListener('click', function() {
                    // Находим все существующие элементы зон (без учета кнопки добавления)
                    const zoneItems = document.querySelectorAll('.zone-item:not(#add-zone)');
                    // Индекс новой зоны (количество имеющихся)
                    const newIndex = zoneItems.length;
                    
                    // Создаем элемент новой зоны
                    const newZoneItem = document.createElement('div');
                    newZoneItem.className = 'zone-item';
                    newZoneItem.innerHTML = `
                        <div class="zone-item-inputs-title">
                            <input type="text" name="zones[${newIndex}][name]" maxlength="250"
                                placeholder="Название зоны" class="form-control" />
                            <span class="remove-zone"><img src="/storage/icon/close__info.svg" alt=""></span>
                        </div>
                        <textarea maxlength="500" name="zones[${newIndex}][description]" placeholder="Описание зоны"
                            class="form-control"></textarea>
                    `;
                    
                    // Вставляем новую зону перед кнопкой добавления
                    zonesContainer.insertBefore(newZoneItem, addZoneButton);
                    
                    // Активируем обработчики для кнопки удаления
                    activateRemoveButtons();
                    
                    // Фокусируемся на поле названия новой зоны
                    newZoneItem.querySelector('input').focus();
                });
                
                // Функция для активации обработчиков удаления зон
                function activateRemoveButtons() {
                    const removeButtons = document.querySelectorAll('.remove-zone');
                    
                    removeButtons.forEach(button => {
                        // Удаляем старые обработчики чтобы избежать дублирования
                        button.removeEventListener('click', removeZoneHandler);
                        // Добавляем новый обработчик
                        button.addEventListener('click', removeZoneHandler);
                    });
                }
                
                // Функция-обработчик удаления зоны
                function removeZoneHandler(event) {
                    // Проверяем, есть ли хотя бы две зоны (не считая кнопки добавления)
                    const zoneItems = document.querySelectorAll('.zone-item:not(#add-zone)');
                    
                    if (zoneItems.length <= 1) {
                        alert('Должна остаться хотя бы одна зона!');
                        return;
                    }
                    
                    // Получаем родительский элемент зоны и удаляем его
                    const zoneItem = event.target.closest('.zone-item');
                    zoneItem.remove();
                    
                    // Перенумеруем оставшиеся зоны
                    renumberZones();
                }
                
                // Функция для перенумерации индексов полей после удаления зоны
                function renumberZones() {
                    const zoneItems = document.querySelectorAll('.zone-item:not(#add-zone)');
                    
                    zoneItems.forEach((zone, index) => {
                        // Обновляем name для поля названия зоны
                        const nameInput = zone.querySelector('input[name^="zones"]');
                        if (nameInput) {
                            nameInput.setAttribute('name', `zones[${index}][name]`);
                        }
                        
                        // Обновляем name для поля описания зоны
                        const descTextarea = zone.querySelector('textarea[name^="zones"]');
                        if (descTextarea) {
                            descTextarea.setAttribute('name', `zones[${index}][description]`);
                        }
                    });
                }
                
                // Активируем обработчики при загрузке страницы
                activateRemoveButtons();
            });
        </script>
    @elseif ($page == 2)
        <!-- Страница 2: Метраж зон -->
        <div id="zones-container">
            @foreach ($zones as $index => $zone)
                <div class="zone-item">
                    <h3>{{ $zone['name'] }}</h3>
                    <div class="zone-area-inputs">
                        <div class="area-input-group">
                            <label>Общая площадь</label>
                            <input maxlength="15" type="text" name="zones[{{ $index }}][total_area]"
                                class="form-control" placeholder="Общая площадь (м²)"
                                value="{{ $zone['total_area'] ?? '' }}" />
                        </div>
                        <div class="area-input-group">
                            <label>Проектная площадь</label>
                            <input maxlength="15" type="text" name="zones[{{ $index }}][projected_area]"
                                class="form-control" placeholder="Проектная площадь (м²)"
                                value="{{ $zone['projected_area'] ?? '' }}" />
                        </div>
                    </div>
                </div>
            @endforeach
        </div>    @elseif ($page == 7)
        <!-- Страница 7: Бюджет -->
        <div id="zones-container">
            <h3>Бюджет по зонам</h3>
            @foreach ($zones as $index => $zone)
                <div class="zone-item">
                    <h4>{{ $zone['name'] }}</h4>
                    <input maxlength="500" type="text" name="budget[{{ $index }}]"
                        class="form-control budget-input" placeholder="Укажите бюджет для {{ $zone['name'] }}"
                        value="{{ $zoneBudgets[$index] ?? '' }}" min="0" step="any"
                        data-zone-index="{{ $index }}" oninput="formatInput(event)" />
                </div>
            @endforeach
            <div class="faq__custom-template__prise">
                <h6>Общий бюджет: <span id="budget-total">0</span></h6>
                <input type="hidden" id="budget-input" name="price" value="{{ $budget }}">
            </div>        </div>@elseif ($page == 8)
        <!-- Страница 8: Дополнительные пожелания/комментарии и документы -->
        <div id="zones-container">
            <h3>Дополнительные пожелания или комментарии</h3>
            @foreach ($zones as $index => $zone)
                <div class="zone-item">
                    <h4>{{ $zone['name'] }}</h4>
                    <textarea maxlength="1000" name="preferences[zone_{{ $index }}][answer]" class="form-control"
                        placeholder="Дополнительные пожелания для {{ $zone['name'] }}">{{ $preferences['zone_' . $index]['question_8'] ?? '' }}</textarea>
                </div>
            @endforeach
            
            <div class="upload__files">
                <h3>Загрузите документы (до 50 МБ суммарно):</h3>
                <div id="drop-zone">
                    <p id="drop-zone-text">Перетащите файлы сюда или нажмите, чтобы выбрать</p>
                    <input id="fileInput" type="file" name="documents[]" multiple
                        accept=".pdf,.xlsx,.xls,.doc,.docx,.jpg,.jpeg,.png,.heic,.heif,.mp4,.mov,.avi,.wmv,.flv,.mkv,.webm,.3gp">
                </div>
                <p class="error-message" style="color: red;"></p>
                <small>Допустимые форматы: изображения (.jpg, .jpeg, .png, .heic, .heif), документы (.pdf, .xlsx, .xls,
                    .doc, .docx), видео (.mp4, .mov, .avi, .wmv, .flv, .mkv, .webm, .3gp)</small><br>
                <small>Максимальный суммарный размер: 50 МБ</small>

                @if ($brif->documents)
                    <div class="uploaded-documents">
                        <h6>Загруженные документы:</h6>
                        <ul>
                            @foreach (json_decode($brif->documents, true) ?? [] as $document)
                                <li>
                                    <a href="{{ asset($document) }}" target="_blank">{{ basename($document) }}</a>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            </div>
        </div>

        <script>
            const dropZone = document.getElementById('drop-zone');
            const fileInput = document.getElementById('fileInput');
            const dropZoneText = document.getElementById('drop-zone-text');

            function updateDropZoneText() {
                const files = fileInput.files;
                if (files && files.length > 0) {
                    const names = [];
                    for (let i = 0; i < files.length; i++) {
                        names.push(files[i].name);
                    }
                    dropZoneText.textContent = names.join(', ');
                } else {
                    dropZoneText.textContent = "Перетащите файлы сюда или нажмите, чтобы выбрать";
                }
            }

            ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
                dropZone.addEventListener(eventName, function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                }, false);
            });

            ['dragenter', 'dragover'].forEach(eventName => {
                dropZone.addEventListener(eventName, () => {
                    dropZone.classList.add('dragover');
                }, false);
            });

            ['dragleave', 'drop'].forEach(eventName => {
                dropZone.addEventListener(eventName, () => {
                    dropZone.classList.remove('dragover');
                }, false);
            });

            dropZone.addEventListener('drop', function(e) {
                let files = e.dataTransfer.files;
                fileInput.files = files;
                updateDropZoneText();
            });

            fileInput.addEventListener('change', function() {
                updateDropZoneText();

                // Проверка форматов и размера файлов
                const allowedFormats = ['pdf', 'xlsx', 'xls', 'doc', 'docx', 'jpg', 'jpeg', 'png', 'heic', 'heif',
                    'mp4', 'mov', 'avi', 'wmv', 'flv', 'mkv', 'webm', '3gp'
                ];
                const errorMessageElement = document.querySelector('.error-message');
                const files = this.files;
                let totalSize = 0;
                errorMessageElement.textContent = '';

                for (const file of files) {
                    const fileExt = file.name.split('.').pop().toLowerCase();
                    if (!allowedFormats.includes(fileExt)) {
                        errorMessageElement.textContent = `Недопустимый формат файла: ${file.name}.`;
                        this.value = '';
                        return;
                    }
                    totalSize += file.size;
                }

                if (totalSize > 50 * 1024 * 1024) {
                    errorMessageElement.textContent = 'Суммарный размер файлов не должен превышать 50 МБ.';
                    this.value = '';
                }
            });

            // Показываем анимацию загрузки при отправке формы с файлами
            document.getElementById('zone-form').addEventListener('submit', function(event) {
                if (fileInput.files && fileInput.files.length > 0) {
                    // Используем общую функцию для показа анимации загрузки
                    showLoader();
                }
            });
        </script>
    @else
        <!-- Страницы 3-6: предпочтения для каждой зоны -->
        <div id="zones-container">
            @foreach ($zones as $index => $zone)
                <div class="zone-item">
                    <h3>{{ $zone['name'] }}</h3>
                    
                    @if($page == 3)
                        <label>Стиль оформления и меблировка:</label>
                        <p class="hint">Опишите желаемый стиль интерьера и предпочтения по меблировке для данной зоны.</p>
                    @elseif($page == 4)
                        <label>Отделочные материалы и поверхности:</label>
                        <p class="hint">Укажите предпочтения по отделке пола, стен и потолка для этой зоны. Какие материалы Вы хотели бы использовать.</p>
                    @elseif($page == 5)
                        <label>Инженерные системы и коммуникации:</label>
                        <p class="hint">Опишите требования к освещению, электрике, вентиляции и кондиционированию данной зоны.</p>
                    @elseif($page == 6)
                        <label>Предпочтения и ограничения:</label>
                        <p class="hint">Укажите, что категорически неприемлемо для этой зоны, и особые пожелания.</p>
                    @endif 
                    
                    <textarea maxlength="1000" name="preferences[zone_{{ $index }}][answer]" class="form-control"
                        placeholder="Введите предпочтения для {{ $zone['name'] }}">{{ $preferences['zone_' . $index]['question_' . $page] ?? '' }}</textarea>
                </div>
            @endforeach
        </div>
    @endif

</form>

<!-- Добавляем анимацию загрузки на весь экран -->
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
<style>
    .loader-text {
        display: flex;
        flex-direction: column;
        align-content: center;
        align-items: center;
        text-align: center !important;
    }
    
    .custom-checkbox:checked + label::before {
        content: '✔';
    }
    
    p.hint {
        color: #6c757d;
        font-size: 0.9em;
        margin-bottom: 10px;
    }
</style>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Обновляем CSRF токен сразу после загрузки страницы
        refreshCsrfToken();
        
        // Добавляем обработчик submit для формы
        document.getElementById('zone-form').addEventListener('submit', async function(event) {
            // Если в форме есть файлы, показываем индикатор загрузки
            const hasFiles = (fileInput && fileInput.files && fileInput.files.length > 0);
            
            if (hasFiles) {
                // Показываем индикатор загрузки
                const loader = document.getElementById('fullscreen-loader');
                if (loader) loader.classList.add('show');
            }
            
            // Перед отправкой формы принудительно обновляем CSRF токен
            try {
                await ensureFreshCsrfToken();
            } catch (error) {
                event.preventDefault();
                alert('Произошла ошибка при обновлении данных сессии. Страница будет перезагружена.');
                location.reload();
            }
        });
    });

    // Функция для отслеживания времени бездействия и обновления токена
    let inactivityTimeout;
    
    function resetInactivityTimer() {
        clearTimeout(inactivityTimeout);
        inactivityTimeout = setTimeout(async function() {
            console.log('Обнаружено длительное бездействие, обновляем CSRF токен...');
            await refreshCsrfToken();
        }, 60000); // Проверяем после 1 минуты бездействия
    }
    
    // Отслеживаем активность пользователя
    ['mousedown', 'mousemove', 'keypress', 'scroll', 'touchstart'].forEach(function(name) {
        document.addEventListener(name, resetInactivityTimer, true);
    });
    
    // Запускаем таймер при загрузке страницы
    resetInactivityTimer();
</script>

<script>
    // ...existing code...
    
    // Модифицируем функцию validateAndSubmit для обновления токена
    async function validateAndSubmit() {
        // Перед валидацией проверяем, есть ли поле price и обрабатываем его
        // ...existing code...

        if (validateForm()) {
            document.getElementById('actionInput').value = 'next';
            document.getElementById('skipPageInput').value = '0';
            
            // Обновляем CSRF токен перед отправкой
            try {
                await refreshCsrfToken();
            } catch (error) {
                alert('Произошла ошибка при обновлении данных сессии. Страница будет перезагружена.');
                location.reload();
                return;
            }

            // Показываем анимацию загрузки только на странице с загрузкой файлов
            // ...existing code...
            
            document.getElementById('briefForm').submit();
        }
    }
    
    // ...existing code...
</script>