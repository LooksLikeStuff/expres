@if (!empty($title) || !empty($subtitle))
    <div class="form__title" id="top-title">
        <div class="form__title__info">
            @if (!empty($title))
                <h1>{{ $title }}</h1>
            @endif
            @if (!empty($subtitle))
                <p>{{ $subtitle }}</p>
            @endif

            {{-- Показываем уведомление о режиме редактирования --}}
            @if (isset($brif) && $brif->edit_status === 'Редактируется')
                <div class="edit-mode-notice">
                    <i class="fas fa-edit"></i> Вы редактируете завершенный бриф. Все изменения будут записаны в историю.
                </div>
            @endif
        </div>

        {{-- Кнопки навигации --}}
        <div class="form__button flex between">
            <p class="form__button-ponel-p">Страница {{ $page }}/{{ $totalPages }}</p>
            @if ($page > 1)
                <button type="button" class=" btn-secondary btn-propustit" onclick="goToPrev()">Обратно</button>
            @endif
            <button type="button" class=" btn-primary btn-dalee" onclick="validateAndSubmit()">Далее</button>

            @if ($page > 0 && $page < 5)
                <button type="button" class=" btn-warning  btn-propustit" onclick="skipPage()">Пропустить</button>
            @endif

            @if ($page >= 5 && !empty(json_decode($brif->skipped_pages ?? '[]')))
                <span class="skipped-notice">Вы заполняете пропущенные страницы</span>
            @endif
        </div>
    </div>
@endif
@include('layouts/mobponel')
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

<form id="briefForm" action="{{ route('common.saveAnswers', ['id' => $brif->id, 'page' => $page]) }}" method="POST"
    enctype="multipart/form-data" class="back__fon__common csrf-check">
    @csrf
    <!-- Скрытое поле для определения направления перехода -->
    <input type="hidden" name="action" id="actionInput" value="next">
    <!-- Скрытое поле для определения, была ли страница пропущена -->
    <input type="hidden" name="skip_page" id="skipPageInput" value="0">

    <!-- Добавляем стили для ошибок валидации -->
    <style>
        .field-error {
            border: 2px solid #ff0000 !important;
            background-color: #fff0f0 !important;
        }

        .error-message {
            color: #ff0000;
            font-size: 12px;
            margin-top: 5px;
            display: none;
        }

        .error-placeholder::placeholder {
            color: #ff0000 !important;
            opacity: 1;
        }

        .edit-mode-notice {
            background-color: #fff3cd;
            color: #856404;
            padding: 10px;
            border-radius: 5px;
            margin-top: 10px;
            border-left: 4px solid #ffc107;
        }

        .edit-mode-notice i {
            margin-right: 5px;
        }
    </style>

    @if ($page == 0)
        <div class="form__body flex between wrap pointblock">
            <!-- Добавим контейнер для пользовательских комнат -->
            <div id="custom-rooms-container" class="display-rooms-container"></div>

            <!-- Форма для добавления пользовательской комнаты -->
            <div class="custom-room-form">
                <h3>Добавить свою комнату:</h3>
                <div class="custom-room-inputs">
                    <input type="text" id="custom-room-name" class="form-control" placeholder="Название комнаты">
                    <button type="button" id="add-custom-room" class="btn btn-primary">Добавить</button>
                </div>
            </div>
            @foreach ($questions as $room)
                <div class="checkpoint flex wrap">
                    <div class="radio">
                        <input type="checkbox" id="room_{{ $room['key'] }}" class="custom-checkbox"
                            name="answers[{{ $room['key'] }}]" value="{{ $room['title'] }}"
                            @if (isset($brif->{$room['key']})) checked @endif>
                        <label for="room_{{ $room['key'] }}">{{ $room['title'] }}</label>
                    </div>
                </div>
            @endforeach


        </div>

        <!-- JavaScript для обработки добавления пользовательских комнат -->
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const addButton = document.getElementById('add-custom-room');
                const customRoomInput = document.getElementById('custom-room-name');
                const customRoomsContainer = document.getElementById('custom-rooms-container');
                let customRoomCounter = 0;

                // Проверяем, есть ли сохраненные пользовательские комнаты
                const existingCustomRooms = @json(isset($brif->custom_rooms) ? json_decode($brif->custom_rooms, true) : []);

                // Восстанавливаем сохраненные пользовательские комнаты
                if (existingCustomRooms && existingCustomRooms.length > 0) {
                    existingCustomRooms.forEach(function(room, index) {
                        addCustomRoomCheckbox(room, index, true);
                    });
                    customRoomCounter = existingCustomRooms.length;
                }

                addButton.addEventListener('click', function() {
                    const roomName = customRoomInput.value.trim();

                    if (roomName) {
                        addCustomRoomCheckbox(roomName, customRoomCounter);
                        customRoomCounter++;
                        customRoomInput.value = '';
                    }
                });

                function addCustomRoomCheckbox(roomName, index, isChecked = false) {
                    const roomKey = `custom_room_${index}`;
                    const roomId = `room_${roomKey}`;

                    const checkpointDiv = document.createElement('div');
                    checkpointDiv.className = 'checkpoint flex wrap';

                    const radioDiv = document.createElement('div');
                    radioDiv.className = 'radio';

                    const checkbox = document.createElement('input');
                    checkbox.type = 'checkbox';
                    checkbox.id = roomId;
                    checkbox.className = 'custom-checkbox';
                    checkbox.name = `answers[custom_rooms][${index}]`;
                    checkbox.value = roomName;
                    if (isChecked) checkbox.checked = true;

                    const label = document.createElement('label');
                    label.setAttribute('for', roomId);
                    label.textContent = roomName;

                    // Добавляем кнопку удаления
                    const deleteButton = document.createElement('button');
                    deleteButton.type = 'button';
                    deleteButton.className = 'btn btn-sm btn-danger delete-room';
                    deleteButton.innerHTML = '&times;'; // × символ
                    deleteButton.dataset.index = index;
                    deleteButton.addEventListener('click', function() {
                        checkpointDiv.remove();
                    });

                    radioDiv.appendChild(checkbox);
                    radioDiv.appendChild(label);
                    radioDiv.appendChild(deleteButton);
                    checkpointDiv.appendChild(radioDiv);
                    customRoomsContainer.appendChild(checkpointDiv);
                }
            });
        </script>

        <!-- Стили для формы добавления комнат -->
        <style>
            .custom-room-form {
                width: 100%;
                margin-top: 20px;
                padding: 15px;
                background: var(--fff);
                border-radius: var(--radius);
            }

            .custom-room-form h3 {
                margin-bottom: 10px;
                font-size: 18px;
            }

            .custom-room-inputs {
                display: flex;
                gap: 10px;
            }

            .custom-room-inputs input {
                flex-grow: 1;
            }

            .delete-room {
                margin-left: 10px;
                font-size: 16px;
                line-height: 1;
                padding: 0 5px;
                vertical-align: middle;
            }

            #custom-rooms-container {
                width: 100%;
            }

            #custom-rooms-container .checkpoint {
                margin-top: 10px;
            }
        </style>
    @endif

    {{-- Блок с вопросами форматов "default" и "faq" --}}
    <div class="form__body flex between wrap">
        @foreach ($questions as $question)
            @if ($question['format'] === 'default')
                <div class="form-group flex wrap">
                    <h2>{{ $question['title'] }}</h2>
                    @if (!empty($question['subtitle']))
                        <p>{{ $question['subtitle'] }}</p>
                    @endif
                    @if ($question['type'] === 'textarea')
                        <textarea name="answers[{{ $question['key'] }}]" placeholder="{{ $question['placeholder'] }}"
                            class="form-control required-field {{ $question['key'] == 'question_2_6' ? 'budget-input' : '' }}"
                            data-original-placeholder="{{ $question['placeholder'] }}" maxlength="500">{{ $brif->{$question['key']} ?? '' }}</textarea>
                    @else
                        <input type="text" name="answers[{{ $question['key'] }}]"
                            class="form-control required-field {{ isset($question['format']) && $question['format'] == 'price' ? 'price-input' : '' }} {{ isset($question['class']) ? $question['class'] : '' }}"
                            value="{{ $brif->{$question['key']} ?? '' }}" placeholder="{{ $question['placeholder'] }}"
                            data-original-placeholder="{{ $question['placeholder'] }}" maxlength="500">
                    @endif
                    <span class="error-message">Это поле обязательно для заполнения</span>
                </div>
            @endif

            {{-- Специальная обработка для поля price --}}
            @if ($question['key'] === 'price')
                <div class="form-group flex wrap">
                    <h2>{{ $question['title'] }}</h2>
                    @if (!empty($question['subtitle']))
                        <p>{{ $question['subtitle'] }}</p>
                    @endif
                    <input type="text" name="price" class="form-control required-field price-input"
                        value="{{ $brif->price ?? '' }}" placeholder="{{ $question['placeholder'] }}"
                        data-original-placeholder="{{ $question['placeholder'] }}" maxlength="500">
                    <span class="error-message">Это поле обязательно для заполнения</span>
                </div>
            @endif

            {{-- Если формат faq — аккордеон --}}
            @if ($question['format'] === 'faq')
                <div class="faq__body">
                    <div class="faq_block flex center">
                        <div class="faq_item">
                            <div class="faq_question" onclick="toggleFaq(this)">
                                <h2>{{ $question['title'] }}</h2>
                                <svg class="arrow" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"
                                    width="24" height="24">
                                    <path d="M7 10l5 5 5-5z"></path>
                                </svg>
                            </div>
                            <div class="faq_answer">
                                @if ($question['type'] === 'textarea')
                                    <textarea name="answers[{{ $question['key'] }}]" placeholder="{{ $question['placeholder'] }}"
                                        class="form-control required-field" data-original-placeholder="{{ $question['placeholder'] }}" maxlength="500">{{ $brif->{$question['key']} ?? '' }}</textarea>
                                @else
                                    <input type="text" name="answers[{{ $question['key'] }}]"
                                        class="form-control required-field"
                                        value="{{ $brif->{$question['key']} ?? '' }}"
                                        placeholder="{{ $question['placeholder'] }}"
                                        data-original-placeholder="{{ $question['placeholder'] }}" maxlength="500">
                                @endif
                                <span class="error-message">Это поле обязательно для заполнения</span>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            {{-- Если формат checkpoint — чекбоксы --}}
            @if ($question['format'] === 'checkpoint')
                <div class="checkpoint flex wrap">
                    <div class="radio">
                        <input type="checkbox" id="{{ $question['key'] }}" class="custom-checkbox"
                            name="answers[{{ $question['key'] }}]" value="1"
                            @if (isset($brif->{$question['key']}) && $brif->{$question['key']} == 1) checked @endif>
                        <label for="{{ $question['key'] }}">{{ $question['title'] }}</label>
                    </div>
                </div>
            @endif        @endforeach
        @if ($page == 5)
            <div class="upload__files">
                <h6>Пожалуйста, предоставьте референсы (фото, видео, документы), которые отражают ваши пожелания по
                    стилю интерьера</h6>
                <div id="drop-zone-references">
                    <p id="drop-zone-references-text">Перетащите файлы сюда или нажмите, чтобы выбрать</p>
                    <input id="referenceInput" type="file" name="references[]" multiple
                        accept=".pdf,.xlsx,.xls,.doc,.docx,.jpg,.jpeg,.png,.heic,.heif,.mp4,.mov,.avi,.wmv,.flv,.mkv,.webm,.3gp">
                </div>
                <p class="error-message" style="color: red;"></p>
                <small>Допустимые форматы: изображения (.jpg, .jpeg, .png, .heic, .heif), документы (.pdf, .xlsx, .xls,
                    .doc, .docx), видео (.mp4, .mov, .avi, .wmv, .flv, .mkv, .webm, .3gp)</small><br>
                <small>Максимальный суммарный размер: 50 МБ</small>
                @if ($brif->references)
                    <div class="uploaded-references">
                        <h6>Загруженные референсы:</h6>
                        <ul>
                            @foreach (json_decode($brif->references, true) ?? [] as $reference)
                                <li>
                                    <a href="{{ asset($reference) }}" target="_blank">{{ basename($reference) }}</a>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            </div>
            <script>
                const dropZoneReferences = document.getElementById('drop-zone-references');
                const referenceInput = document.getElementById('referenceInput');
                const dropZoneReferencesText = document.getElementById('drop-zone-references-text');

                function updateDropZoneReferencesText() {
                    const files = referenceInput.files;
                    if (files && files.length > 0) {
                        const names = [];
                        for (let i = 0; i < files.length; i++) {
                            names.push(files[i].name);
                        }
                        dropZoneReferencesText.textContent = names.join(', ');
                    } else {
                        dropZoneReferencesText.textContent = "Перетащите файлы сюда или нажмите, чтобы выбрать";
                    }
                }
                ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
                    dropZoneReferences.addEventListener(eventName, function(e) {
                        e.preventDefault();
                        e.stopPropagation();
                    }, false);
                });
                ['dragenter', 'dragover'].forEach(eventName => {
                    dropZoneReferences.addEventListener(eventName, () => {
                        dropZoneReferences.classList.add('dragover');
                    }, false);
                });
                ['dragleave', 'drop'].forEach(eventName => {
                    dropZoneReferences.addEventListener(eventName, () => {
                        dropZoneReferences.classList.remove('dragover');
                    }, false);
                });
                dropZoneReferences.addEventListener('drop', function(e) {
                    let files = e.dataTransfer.files;
                    referenceInput.files = files;
                    updateDropZoneReferencesText();
                });
                referenceInput.addEventListener('change', function() {
                    updateDropZoneReferencesText();
                });
                referenceInput.addEventListener('change', function() {
                    const allowedFormats = ['pdf', 'xlsx', 'xls', 'doc', 'docx', 'jpg', 'jpeg', 'png', 'heic', 'heif',
                        'mp4', 'mov', 'avi', 'wmv', 'flv', 'mkv', 'webm', '3gp'
                    ];
                    const errorMessageElement = this.parentElement.nextElementSibling;
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
            </script>
        @endif
    </div>
</form>
<!-- Обновленная анимация загрузки на весь экран -->
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
<!-- JavaScript для проверки заполнения обязательных полей и возможности пропуска страниц -->
<script>
    // Функция для проверки заполнения всех обязательных полей
    function validateForm() {
        let isValid = true;
        const requiredFields = document.querySelectorAll('.required-field');
        let firstInvalidField = null;

        // Сбрасываем стили ошибок для всех полей
        requiredFields.forEach(function(field) {
            field.classList.remove('field-error', 'error-placeholder');
            field.placeholder = field.getAttribute('data-original-placeholder');

            const errorMsg = field.nextElementSibling;
            if (errorMsg && errorMsg.classList.contains('error-message')) {
                errorMsg.style.display = 'none';
            }
        });

        // Проверяем каждое обязательное поле
        requiredFields.forEach(function(field) {
            if (!field.value.trim()) {
                isValid = false;

                // Добавляем стили ошибок
                field.classList.add('field-error', 'error-placeholder');
                field.placeholder = 'Заполните это поле!';

                // Показываем сообщение об ошибке
                const errorMsg = field.nextElementSibling;
                if (errorMsg && errorMsg.classList.contains('error-message')) {
                    errorMsg.style.display = 'block';
                }

                // Сохраняем первое невалидное поле
                if (!firstInvalidField) {
                    firstInvalidField = field;
                }

                // Если поле в аккордеоне, открываем аккордеон
                const faqItem = field.closest('.faq_item');
                if (faqItem && !faqItem.classList.contains('active')) {
                    toggleFaq(faqItem.querySelector('.faq_question'));
                }
            }
        });

        // Если есть невалидное поле, прокручиваем к нему
        if (firstInvalidField) {
            scrollToElement(firstInvalidField);
        }

        return isValid;
    }

    // Функция для прокрутки к элементу
    function scrollToElement(element) {
        // Получаем позицию элемента относительно документа
        const rect = element.getBoundingClientRect();
        const scrollTop = window.pageYOffset || document.documentElement.scrollTop;

        // Вычисляем абсолютную позицию элемента
        const absoluteTop = rect.top + scrollTop;

        // Прокручиваем с учетом отступа (для отображения заголовка формы)
        window.scrollTo({
            top: absoluteTop - 120, // 120px - примерная высота шапки
            behavior: 'smooth'
        });

        // Добавляем фокус на элемент после прокрутки
        setTimeout(() => {
            element.focus();
            // Добавляем подсвечивание
            element.classList.add('highlight-field');
            // Убираем подсвечивание через 2 секунды
            setTimeout(() => {
                element.classList.remove('highlight-field');
            }, 2000);
        }, 500);
    }

    // Обновленная функция validateAndSubmit с поддержкой обновления CSRF токена
    async function validateAndSubmit() {
        // Перед валидацией проверяем, есть ли поле price и обрабатываем его
        const priceInput = document.querySelector('input[name="price"]');
        if (priceInput) {
            // Очищаем значение от нецифровых символов
            const numericValue = priceInput.value.replace(/[^\d]/g, '');
            console.log('Значение price перед отправкой:', numericValue);

            // Создаем скрытое поле с числовым значением
            const hiddenInput = document.createElement('input');
            hiddenInput.type = 'hidden';
            hiddenInput.name = 'price';
            hiddenInput.value = numericValue;

            // Добавляем скрытое поле в форму
            priceInput.name = 'price_display';
            priceInput.form.appendChild(hiddenInput);
        }

        if (validateForm()) {
            document.getElementById('actionInput').value = 'next';
            document.getElementById('skipPageInput').value = '0';

            // Обновляем CSRF токен перед отправкой формы
            try {
                await refreshCsrfToken();
            } catch (error) {
                alert('Произошла ошибка при обновлении данных сессии. Страница будет перезагружена для сохранности данных.');
                location.reload();
                return;
            }            // Показываем анимацию загрузки только на странице 5 (загрузка референсов)
            if ({{ $page }} == 5 && document.getElementById('referenceInput') &&
                document.getElementById('referenceInput').files &&
                document.getElementById('referenceInput').files.length > 0) {

                // Плавно показываем анимацию загрузки
                const loader = document.getElementById('fullscreen-loader');
                loader.classList.add('show');

                // Анимация прогресс-бара
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

                // Добавляем таймаут для отображения анимации до начала отправки
                setTimeout(function() {
                    document.getElementById('briefForm').submit();
                }, 500);
            } else {
                // Добавляем отладочный вывод для пятой страницы
                if ({{ $page }} == 5) {
                    console.log('Отправляем форму со страницы 5');
                    // Добавляем отображение индикатора загрузки
                    const loader = document.getElementById('fullscreen-loader');
                    if (loader) loader.classList.add('show');
                }
                document.getElementById('briefForm').submit();
            }
        }
    }

    // Функция для пропуска текущей страницы
    function skipPage() {
        // Проверяем, что страница < 5, так как страницы 5+ нельзя пропускать
        @if ($page < 5)
            // Создаем форму CSRF-токена для отправки
            const csrfToken = '{{ csrf_token() }}';

            // Отправляем запрос на пропуск текущей страницы
            fetch('{{ route('common.skipPage', ['id' => $brif->id, 'page' => $page]) }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    credentials: 'same-origin' // Важно для работы с сессиями и куками
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Ошибка сервера: ' + response.status);
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        window.location.href = data.redirect;
                    } else {
                        alert(data.message || 'Произошла ошибка при пропуске страницы');
                    }
                })
                .catch(error => {
                    console.error('Ошибка:', error);
                    alert('Произошла ошибка при пропуске страницы. Пожалуйста, попробуйте еще раз.');
                });
        @else
            alert('Эту страницу нельзя пропустить.');
        @endif
    }

    // Функция для перехода на предыдущую страницу
    function goToPrev() {
        document.getElementById('actionInput').value = 'prev';
        document.getElementById('briefForm').submit();
    }

    // Функция для переключения аккордеонов FAQ
    function toggleFaq(questionElement) {
        const faqItem = questionElement.parentElement;
        const faqAnswer = faqItem.querySelector('.faq_answer');
        const inputElement = faqAnswer.querySelector('textarea, input');
        const isActive = faqItem.classList.contains('active');

        if (!isActive) {
            faqItem.classList.add('active');
            faqAnswer.style.height = '0px';
            faqAnswer.offsetHeight; // принудительный reflow
            faqAnswer.style.height = faqAnswer.scrollHeight + 'px';
            if (inputElement) {
                setTimeout(() => {
                    inputElement.focus();
                }, 50);
            }
        } else {
            faqItem.classList.remove('active');
            const currentHeight = faqAnswer.scrollHeight;
            faqAnswer.style.height = currentHeight + 'px';
            faqAnswer.offsetHeight;
            faqAnswer.style.height = '0px';
        }
    }

    // Добавляем обработчики событий для полей, чтобы убирать ошибки при вводе
    document.addEventListener('DOMContentLoaded', function() {
        const requiredFields = document.querySelectorAll('.required-field');

        requiredFields.forEach(function(field) {
            field.addEventListener('input', function() {
                if (field.value.trim()) {
                    field.classList.remove('field-error', 'error-placeholder');
                    field.placeholder = field.getAttribute('data-original-placeholder');

                    const errorMsg = field.nextElementSibling;
                    if (errorMsg && errorMsg.classList.contains('error-message')) {
                        errorMsg.style.display = 'none';
                    }
                }
            });
        });

        // Удаляем дублирующиеся обработчики price-input и budget-input
        // Оставляем только один унифицированный обработчик для всех полей цены
        const priceInputs = document.querySelectorAll('.price-input');

        priceInputs.forEach(function(input) {
            // Очищаем все существующие обработчики событий
            const newInput = input.cloneNode(true);
            input.parentNode.replaceChild(newInput, input);

            // Инициализация поля при загрузке страницы
            if (newInput.value) {
                let value = newInput.value.replace(/[^\d]/g, '');
                if (value) {
                    newInput.value = formatPriceValue(value);
                }
            }

            // Обработка ввода с единой функцией форматирования
            newInput.addEventListener('input', function(e) {
                let value = this.value.replace(/[^\d]/g, '');
                this.value = value ? formatPriceValue(value) : '';
            });

            // При фокусе убираем форматирование для удобства редактирования
            newInput.addEventListener('focus', function() {
                let value = this.value.replace(/[^\d]/g, '');
                this.value = value;
            });

            // При потере фокуса добавляем форматирование
            newInput.addEventListener('blur', function() {
                if (this.value.trim()) {
                    let value = this.value.replace(/[^\d]/g, '');
                    this.value = formatPriceValue(value);
                }
            });
        });

        // Добавляем обработчик отправки формы для страницы 2
        if ({{ $page }} == 2) {
            // Добавляем обработчик события ошибок при отправке формы
            document.getElementById('briefForm').addEventListener('error', function(event) {
                console.error('Ошибка при отправке формы:', event);
                alert('Произошла ошибка при отправке формы. Проверьте правильность заполнения полей.');
            });

            document.getElementById('briefForm').addEventListener('submit', function(event) {
                // Проверяем, есть ли файлы для загрузки
                if (document.getElementById('referenceInput') &&
                    document.getElementById('referenceInput').files &&
                    document.getElementById('referenceInput').files.length > 0) {

                    // Плавно показываем анимацию загрузки
                    const loader = document.getElementById('fullscreen-loader');
                    loader.classList.add('show');

                    // Анимация прогресс-бара при отправке формы
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
            });
        }
    });

    // Единая функция форматирования ценовых значений
    function formatPriceValue(value) {
        // Добавляем пробелы между тысячами и добавляем суффикс руб
        return value.replace(/\B(?=(\d{3})+(?!\d))/g, " ") + ' руб';
    }

    // Заменяем функцию formatBudgetValue на formatPriceValue для унификации
    function formatBudgetValue(value) {
        return formatPriceValue(value);
    }
</script>

<!-- Скрипт для проверки файлов на размер и формат -->
<script>
    document.getElementById('fileInput')?.addEventListener('change', function() {
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
</script>
<style>
    .loader-text {
        display: flex;
        flex-direction: column;
        align-content: center;
        align-items: center;
    }

    .loader-text {
        text-align: center !important;
    }
</style>
<style>
    .pointblock .radio label {
        background: var(--fff) !important;
    }

    .custom-checkbox:checked+label::before {
        content: '✔';
    }

    .radio label {
        margin: 0px;
        width: 100%;
        font-size: 16px;
        padding: var(--p20) 20px 20px 50px;
        -webkit-transition: color 0.3s;
        border-radius: var(--radius);
        -o-transition: color 0.3s;
        font-family: Onest;
        font-weight: 400;
        font-size: 20px;
        line-height: 25.5px;
        letter-spacing: 0%;
        transition: color 0.3s;
        background: var(--fff);
    }

    .form__body.flex.between.wrap.pointblock {
        background: var(--blockbody) !important;
        display: flex;
        -webkit-box-align: center;
        align-items: center;
        margin-bottom: var(--m20);
        padding: var(--p20);
        border-radius: var(--radius);
        place-content: center space-between;
    }

    .pointblock {
        padding-bottom: var(--p30);
    }

    .display-rooms-container {
        display: flex;
    }

    .display-rooms-container .checkpoint button {
        width: 110px;
    }
</style>
