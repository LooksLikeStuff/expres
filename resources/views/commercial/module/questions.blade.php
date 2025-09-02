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
        <div class="form__button flex between" data-page="{{ $page }}" data-brief-id="{{ $brif->id }}">
            <p class="form__button-ponel-p">Страница {{ $page }}/{{ $totalPages }}</p>
            @if ($page > 1)
                <button type="button" class="btn-secondary" id="prevPageButton">Обратно</button>
            @endif
            <button type="button" class="btn-primary" onclick="goToNext()">Далее</button>
        </div>
    </div>



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

        <!-- JavaScript для работы с зонами теперь в questions.js -->
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
        </div>
    @elseif ($page == 7)
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
            </div>        </div>
    @elseif ($page == 8)
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

        <!-- JavaScript для drag and drop загрузки файлов теперь в questions.js -->
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

<!-- Анимация загрузки на весь экран -->
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
<!-- Все скрипты теперь вынесены в questions.js -->
