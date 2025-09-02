import '../../sass/briefs/questions.scss';
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
    //
    //     const formData = new FormData(this); // "this" — это форма
    //
    //     // Чтобы увидеть все поля и их значения
    //     for (let [name, value] of formData.entries()) {
    //         console.log(`${name}: ${value}`);
    //     }
    //
    //     // Или получить конкретное поле по имени
    //     const nameValue = formData.get('name'); // если есть input с name="name"
    //     console.log('Имя:', nameValue);
    //
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
        checkbox.setAttribute('data-is-custom', 'true');
        if (isChecked) checkbox.checked = true;

        const label = document.createElement('label');
        label.setAttribute('for', roomId);
        
        // Создаем текст для лейбла с бейджем
        const labelText = document.createTextNode(roomName);
        const customBadge = document.createElement('span');
        customBadge.className = 'custom-room-badge';
        customBadge.textContent = '(Кастомная)';
        
        label.appendChild(labelText);
        label.appendChild(customBadge);

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
    if (isNaN(page)) return document.getElementById('briefForm').submit();

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

        // // Обновляем CSRF токен перед отправкой формы
        // try {
        //     await refreshCsrfToken();
        // } catch (error) {
        //     alert('Произошла ошибка при обновлении данных сессии. Страница будет перезагружена для сохранности данных.');
        //     location.reload();
        //     return;
        // }            // Показываем анимацию загрузки только на странице 5 (загрузка референсов)
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



// Общие константы
const ALLOWED_FILE_FORMATS = ['pdf', 'xlsx', 'xls', 'doc', 'docx', 'jpg', 'jpeg', 'png', 'heic', 'heif',
    'mp4', 'mov', 'avi', 'wmv', 'flv', 'mkv', 'webm', '3gp'];
const MAX_FILE_SIZE = 50 * 1024 * 1024; // 50 МБ

// Унифицированная функция для валидации файлов
function validateFiles(files, errorElement) {
    let totalSize = 0;
    errorElement.textContent = '';

    for (const file of files) {
        const fileExt = file.name.split('.').pop().toLowerCase();
        if (!ALLOWED_FILE_FORMATS.includes(fileExt)) {
            errorElement.textContent = `Недопустимый формат файла: ${file.name}.`;
            return false;
        }
        totalSize += file.size;
    }

    if (totalSize > MAX_FILE_SIZE) {
        errorElement.textContent = 'Суммарный размер файлов не должен превышать 50 МБ.';
        return false;
    }

    return true;
}

// Унифицированная функция для настройки drag and drop
function setupDragAndDrop(dropZone, fileInput, textElement, updateTextCallback) {
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
        if (updateTextCallback) updateTextCallback();
    });

    dropZone.addEventListener('click', function() {
        fileInput.click();
    });
}

// Логика для работы с зонами (вынесено из blade файла)
function initializeZoneManagement() {
    const addZoneButton = document.getElementById('add-zone');
    const zonesContainer = document.getElementById('zones-container');

    if (!addZoneButton || !zonesContainer) return;

    // Обработчик клика на кнопку добавления новой зоны
    addZoneButton.addEventListener('click', function() {
        // Подсчитываем только новые зоны (addRooms), а не все
        const addRoomsItems = document.querySelectorAll('input[name^="addRooms"]');
        const newIndex = addRoomsItems.length;
        const questionKey = document.getElementById('question_key').value;

        const newZoneItem = document.createElement('div');
        newZoneItem.className = 'zone-item';
        newZoneItem.innerHTML = `
            <div class="zone-item-inputs-title">
                <input type="text" name="addRooms[${newIndex}][title]" maxlength="250"
                    placeholder="Название зоны" class="form-control" />
                <span class="remove-zone"><img src="/storage/icon/close__info.svg" alt=""></span>
            </div>
            <textarea maxlength="500" name="addRooms[${newIndex}][${questionKey}]" placeholder="Описание зоны"
                class="form-control required-field"></textarea>
        `;

        zonesContainer.insertBefore(newZoneItem, addZoneButton);
        activateRemoveButtons();
        newZoneItem.querySelector('input').focus();
    });

    // Функция для активации обработчиков удаления зон
    function activateRemoveButtons() {
        const removeButtons = document.querySelectorAll('.remove-zone');
        removeButtons.forEach(button => {
            button.removeEventListener('click', removeZoneHandler);
            button.addEventListener('click', removeZoneHandler);
        });
    }

    // Функция-обработчик удаления зоны
    function removeZoneHandler(event) {
        const zoneItems = document.querySelectorAll('.zone-item:not(#add-zone)');

        if (zoneItems.length <= 1) {
            alert('Должна остаться хотя бы одна зона!');
            return;
        }

        const zoneItem = event.target.closest('.zone-item');
        zoneItem.remove();
        renumberZones();
    }

    // Функция для перенумерации индексов полей после удаления зоны
    function renumberZones() {
        const existingZones = document.querySelectorAll('.zone-item:not(#add-zone)');
        let addRoomsIndex = 0;

        existingZones.forEach((zone) => {
            // Проверяем, это существующая комната или новая
            const nameInput = zone.querySelector('input[name^="rooms"], input[name^="addRooms"]');
            if (nameInput && nameInput.name.startsWith('addRooms')) {
                // Это новая комната, перенумеровываем
                nameInput.setAttribute('name', `addRooms[${addRoomsIndex}][title]`);

                const descTextarea = zone.querySelector('textarea[name^="addRooms"]');
                if (descTextarea) {
                    descTextarea.setAttribute('name', `addRooms[${addRoomsIndex}][description]`);
                }
                addRoomsIndex++;
            }
            // Существующие комнаты (rooms[id][...]) оставляем без изменений
        });
    }

    // Активируем обработчики при загрузке страницы
    activateRemoveButtons();
}

// Функция для работы с навигацией между страницами
function initializeNavigation() {
    const currentPage = getPageNumber();

    // Навигация "Назад"
    const prevPageButton = document.getElementById('prevPageButton');
    if (prevPageButton) {
        prevPageButton.addEventListener('click', function() {
            const prevPage = currentPage - 1;
            if (prevPage >= 1) {
                const briefId = getBriefId();
                window.location.href = `/commercial/questions/${briefId}/${prevPage}`;
            }
        });
    }
}

// Функция для настройки валидации форм по страницам
function initializePageValidation() {
    const currentPage = getPageNumber();

    window.goToNext = function() {
        // Проверяем валидацию для страниц с обязательными полями
        if ([1, 2, 8].includes(currentPage)) {
            if (!validateZoneForm()) {
                return false;
            }
        }

        // Проверка на наличие файлов для загрузки (страница 8)
        if (currentPage === 8) {
            const fileInput = document.getElementById('fileInput');
            if (fileInput && fileInput.files && fileInput.files.length > 0) {
                showLoader();
                setTimeout(() => {
                    document.getElementById('zone-form').submit();
                }, 300);
                    return;
            }
        }

        document.getElementById('zone-form').submit();
    };
}

// Валидация форм для зон
function validateZoneForm() {
    let isValid = true;
    let firstInvalidField = null;
    const currentPage = getPageNumber();

    if (currentPage === 1) {
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
    } else if (currentPage === 2) {
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
        });
    }

    // Если есть невалидное поле, прокручиваем к нему
    if (firstInvalidField) {
        scrollToElement(firstInvalidField);
    }

    return isValid;
}

// Функция для показа анимации загрузки
function showLoader() {
    const loader = document.getElementById('fullscreen-loader');
    if (!loader) return;

    loader.classList.add('show');

    // Анимируем прогресс-бар
    let width = 0;
    const progressBar = document.querySelector('.loader-progress-bar');
    if (progressBar) {
        const progressInterval = setInterval(function() {
            if (width >= 90) {
                clearInterval(progressInterval);
            } else {
                width += Math.random() * 3;
                progressBar.style.width = width + '%';
            }
        }, 300);
    }
}

// CSRF токен функции (вынесены из blade)
// function refreshCsrfToken() {
//     return fetch('/refresh-csrf-token', {
//         method: 'GET',
//         headers: {
//             'Accept': 'application/json',
//             'Content-Type': 'application/json'
//         },
//         credentials: 'same-origin'
//     })
//     .then(response => response.json())
//     .then(data => {
//         if (data.token) {
//             // Обновляем все CSRF токены на странице
//             const csrfInputs = document.querySelectorAll('input[name="_token"]');
//             csrfInputs.forEach(input => {
//                 input.value = data.token;
//             });
//
//             // Обновляем meta тег
//             const metaToken = document.querySelector('meta[name="csrf-token"]');
//             if (metaToken) {
//                 metaToken.setAttribute('content', data.token);
//             }
//
//             return data.token;
//         }
//         throw new Error('Не удалось получить новый CSRF токен');
//     });
// }
//

// Функция для отслеживания времени бездействия и обновления токена
let inactivityTimeout;

// function resetInactivityTimer() {
//     clearTimeout(inactivityTimeout);
//     inactivityTimeout = setTimeout(async function() {
//         console.log('Обнаружено длительное бездействие, обновляем CSRF токен...');
//         await refreshCsrfToken();
//     }, 60000); // Проверяем после 1 минуты бездействия
// }
//
// // Отслеживаем активность пользователя
// function initializeInactivityTracking() {
//     ['mousedown', 'mousemove', 'keypress', 'scroll', 'touchstart'].forEach(function(name) {
//         document.addEventListener(name, resetInactivityTimer, true);
//     });
//     resetInactivityTimer();
// }

// Утилиты для получения данных страницы
function getPageNumber() {
    const pageElement = document.getElementById('page') || document.querySelector('[data-page]');
    if (pageElement) {
        return parseInt(pageElement.value || pageElement.dataset.page) || 1;
    }
    // Fallback - извлекаем из URL
    const urlParts = window.location.pathname.split('/');
    return parseInt(urlParts[urlParts.length - 1]) || 1;
}

function getBriefId() {
    const briefElement = document.querySelector('[data-brief-id]');
    if (briefElement) {
        return briefElement.dataset.briefId;
    }
    // Fallback - извлекаем из URL
    const urlParts = window.location.pathname.split('/');
    const questionsIndex = urlParts.indexOf('questions');
    if (questionsIndex !== -1 && urlParts[questionsIndex + 1]) {
        return urlParts[questionsIndex + 1];
    }
    return null;
}

// Форматирование ввода для денежных полей (глобальная функция)
window.formatInput = function(event) {
    let value = event.target.value.replace(/[^\d]/g, '');
    if (value) {
        event.target.value = formatPriceValue(value);
    }
}

// Инициализация файловых загрузчиков (универсальная функция)
function initializeFileUploader(dropZoneId, fileInputId, textElementId) {
    const dropZone = document.getElementById(dropZoneId);
    const fileInput = document.getElementById(fileInputId);
    const textElement = document.getElementById(textElementId);

    if (!dropZone || !fileInput || !textElement) return;

    function updateDropZoneText() {
        const files = fileInput.files;
        if (files && files.length > 0) {
            const names = Array.from(files).map(file => file.name);
            textElement.textContent = names.join(', ');
        } else {
            textElement.textContent = "Перетащите файлы сюда или нажмите, чтобы выбрать";
        }
    }

    // Настройка drag and drop
    setupDragAndDrop(dropZone, fileInput, textElement, updateDropZoneText);

    // Обработчик изменения файлов
    fileInput.addEventListener('change', function() {
        updateDropZoneText();

        // Валидация файлов
        const errorElement = document.querySelector('.error-message') ||
                           this.parentElement.nextElementSibling;
        if (errorElement && this.files.length > 0) {
            if (!validateFiles(this.files, errorElement)) {
                this.value = '';
                updateDropZoneText();
            }
        }
    });
}

// Главная функция инициализации страницы
document.addEventListener('DOMContentLoaded', function() {
    // Обновляем CSRF токен сразу после загрузки страницы
    refreshCsrfToken();

    // Инициализируем различные компоненты
    initializeZoneManagement();
    initializeNavigation();
    initializePageValidation();
    // initializeInactivityTracking();

    // Инициализируем файловые загрузчики
    initializeFileUploader('drop-zone', 'fileInput', 'drop-zone-text');
    initializeFileUploader('drop-zone-references', 'referenceInput', 'drop-zone-references-text');

    // Обработчик submit для формы
    const zoneForm = document.getElementById('zone-form');
    if (zoneForm) {
        zoneForm.addEventListener('submit', async function(event) {
            const fileInput = document.getElementById('fileInput');
            const hasFiles = (fileInput && fileInput.files && fileInput.files.length > 0);

            if (hasFiles) {
                const loader = document.getElementById('fullscreen-loader');
                if (loader) loader.classList.add('show');
            }

            // // Перед отправкой формы принудительно обновляем CSRF токен
            // try {
            //     await refreshCsrfToken();
            // } catch (error) {
            //     event.preventDefault();
            //     alert('Произошла ошибка при обновлении данных сессии. Страница будет перезагружена.');
            //     location.reload();
            // }
        });
    }
});

// Дублирующийся код удален, так как теперь используется универсальная функция initializeFileUploader
