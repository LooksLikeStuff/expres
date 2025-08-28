document.addEventListener('DOMContentLoaded', function () {
    const addButton = document.getElementById('add-custom-room');
    const nextPageBtn = document.getElementById('nextPageBtn');
    const prevPageBtn = document.getElementById('prevPageBtn');
    const skipPageBtn = document.getElementById('skipPageBtn');
    const customRoomInput = document.getElementById('custom-room-name');
    const customRoomsContainer = document.getElementById('custom-rooms-container');
    const faqQuestions = document.querySelectorAll('.faq_question');
    let customRoomCounter = 0;

    const page = parseInt($('#page').val());
    const briefId = 4;
    // const existingCustomRooms = @json(isset($brif->custom_rooms) ? json_decode($brif->custom_rooms, true) : []);
    const existingCustomRooms = [];


    // Восстанавливаем сохраненные пользовательские комнаты
    if (existingCustomRooms && existingCustomRooms.length > 0) {
        existingCustomRooms.forEach(function (room, index) {
            addCustomRoomCheckbox(room, index, true);
        });
        customRoomCounter = existingCustomRooms.length;
    }

    if (addButton) {
        addButton.addEventListener('click', function () {
            const roomName = customRoomInput.value.trim();

            if (roomName) {
                addCustomRoomCheckbox(roomName, customRoomCounter);
                customRoomCounter++;
                customRoomInput.value = '';
            }
        });
    }

    nextPageBtn.addEventListener('click', () => {
        validateAndSubmit(page)
    });


    faqQuestions.forEach((elem) => elem.addEventListener('click', function () {
        console.log(this);
        toggleFaq(this);
    }));



    // document.getElementById('briefForm').addEventListener('click', function(event) {
    //     console.log(this);
    //     event.preventDefault();

    //     const formData = new FormData(this); // "this" — это форма

    //     // Чтобы увидеть все поля и их значения
    //     for (let [name, value] of formData.entries()) {
    //         console.log(`${name}: ${value}`);
    //     }
    
    //     // Или получить конкретное поле по имени
    //     const nameValue = formData.get('name'); // если есть input с name="name"
    //     console.log('Имя:', nameValue);
        
    // });


// Добавляем обработчики событий для полей, чтобы убирать ошибки при вводе
    const requiredFields = document.querySelectorAll('.required-field');

    requiredFields.forEach(function (field) {
        field.addEventListener('input', function () {
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

    priceInputs.forEach(function (input) {
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
        newInput.addEventListener('input', function (e) {
            let value = this.value.replace(/[^\d]/g, '');
            this.value = value ? formatPriceValue(value) : '';
        });

        // При фокусе убираем форматирование для удобства редактирования
        newInput.addEventListener('focus', function () {
            let value = this.value.replace(/[^\d]/g, '');
            this.value = value;
        });

        // При потере фокуса добавляем форматирование
        newInput.addEventListener('blur', function () {
            if (this.value.trim()) {
                let value = this.value.replace(/[^\d]/g, '');
                this.value = formatPriceValue(value);
            }
        });
    });

// Добавляем обработчик отправки формы для страницы 2
    if (page === 2) {
        // Добавляем обработчик события ошибок при отправке формы
        document.getElementById('briefForm').addEventListener('error', function (event) {
            console.error('Ошибка при отправке формы:', event);
            alert('Произошла ошибка при отправке формы. Проверьте правильность заполнения полей.');
        });

        document.getElementById('briefForm').addEventListener('submit', function (event) {
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
                const progressInterval = setInterval(function () {
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
        checkbox.name = `rooms[]`;
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
        deleteButton.addEventListener('click', function () {
            checkpointDiv.remove();
        });

        radioDiv.appendChild(checkbox);
        radioDiv.appendChild(label);
        radioDiv.appendChild(deleteButton);
        checkpointDiv.appendChild(radioDiv);
        customRoomsContainer.appendChild(checkpointDiv);
    }
});


// Функция для проверки заполнения всех обязательных полей
function validateForm() {
    let isValid = true;
    const requiredFields = document.querySelectorAll('.required-field');
    let firstInvalidField = null;

    // Сбрасываем стили ошибок для всех полей
    requiredFields.forEach(function (field) {
        field.classList.remove('field-error', 'error-placeholder');
        field.placeholder = field.getAttribute('data-original-placeholder');

        const errorMsg = field.nextElementSibling;
        if (errorMsg && errorMsg.classList.contains('error-message')) {
            errorMsg.style.display = 'none';
        }
    });

    // Проверяем каждое обязательное поле
    requiredFields.forEach(function (field) {
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
async function validateAndSubmit(page) {
    console.log(page);
    // Перед валидацией проверяем, есть ли поле price и обрабатываем его
    const priceInput = document.querySelector('input[name="price"]');
    if (priceInput) {
        // Очищаем значение от нецифровых символов
        const numericValue = priceInput.value.replace(/[^\d]/g, '');
        console.log('Значение price перед отправкой:', numericValue);
    
        // Создаем скрытое поле с числовым значением
        const hiddenInput = document.createElement('input');
        hiddenInput.type = 'hidden';
        hiddenInput.name = 'answers[price]';
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
        if (page === 5 && document.getElementById('referenceInput') &&
        document.getElementById('referenceInput').files &&
        document.getElementById('referenceInput').files.length > 0
    )
        {

            // Плавно показываем анимацию загрузки
            const loader = document.getElementById('fullscreen-loader');
            loader.classList.add('show');

            // Анимация прогресс-бара
            let width = 0;
            const progressBar = document.querySelector('.loader-progress-bar');
            const progressInterval = setInterval(function () {
                if (width >= 90) {
                    clearInterval(progressInterval);
                } else {
                    width += Math.random() * 3;
                    progressBar.style.width = width + '%';
                }
            }, 300);

            // Добавляем таймаут для отображения анимации до начала отправки
            setTimeout(function () {
                document.getElementById('briefForm').submit();
            }, 500);
        }
    else
        {
            {
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
function skipPage(briefId, page) {
    if (page > 5) {
        alert('Эту страницу нельзя пропустить.');
        return;
    }

    // Отправляем запрос на пропуск текущей страницы
    fetch(`/common/${briefId}/skip/${page}`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content'),
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

// Единая функция форматирования ценовых значений
function formatPriceValue(value) {
    // Добавляем пробелы между тысячами и добавляем суффикс руб
    return value.replace(/\B(?=(\d{3})+(?!\d))/g, " ") + ' руб';
}

// Заменяем функцию formatBudgetValue на formatPriceValue для унификации
function formatBudgetValue(value) {
    return formatPriceValue(value);
}


document.getElementById('fileInput')?.addEventListener('change', function () {
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



function confirmDelete(briefId) {
    if (confirm("Вы действительно хотите удалить этот бриф? Это действие нельзя будет отменить.")) {
        document.getElementById('delete-form-' + briefId).submit();
    }
}



document.addEventListener('DOMContentLoaded', function (event) {
    const dropZoneReferences = document.getElementById('drop-zone-references');
    const referenceInput = document.getElementById('referenceInput');
    const dropZoneReferencesText = document.getElementById('drop-zone-references-text');

    if (dropZoneReferences) {
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
    }
});
