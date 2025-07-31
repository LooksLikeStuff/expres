
    <div class="history-header">
        <h1>История изменений брифа #{{ $brif->id }}</h1>
        <div class="history-actions">
            <a href="{{ route('common.show', $brif->id) }}" class="btn btn-primary">
                <i class="fas fa-arrow-left"></i> Вернуться к брифу
            </a>
        </div>
    </div>

    @if($histories->count() > 0)
        <div class="history-controls">
            <div class="history-search">
                <div class="search-icon">
                    <i class="fas fa-search"></i>
                </div>
                <input type="text" id="history-search" class="form-control" placeholder="Поиск по изменениям...">
                @if($histories->count() > 1)
                    <div class="expand-controls">
                        <button id="expand-all" class="btn btn-sm btn-outline-secondary">Развернуть все</button>
                        <button id="collapse-all" class="btn btn-sm btn-outline-secondary">Свернуть все</button>
                    </div>
                @endif
            </div>
            <div class="history-counter">
                <span class="badge bg-info">{{ $histories->count() }} {{ trans_choice('записей|запись|записи|записей', $histories->count()) }}</span>
            </div>
        </div>

        <div class="history-timeline">
            @foreach($histories as $history)
                <div class="history-card" data-changes="{{ $history->changes_description }}">
                    <div class="history-card-header" onclick="toggleHistoryDetails(this)">
                        <div class="history-meta">
                            <div class="history-date">
                                <i class="fas fa-calendar-alt"></i> 
                                {{ $history->created_at->format('d.m.Y') }}
                                <span class="history-time">{{ $history->created_at->format('H:i') }}</span>
                            </div>
                            <div class="history-editor">
                                <i class="fas fa-user-edit"></i> 
                                @if($history->editor)
                                    <strong>{{ $history->editor->name }}</strong>
                                    <span class="editor-role">{{ $history->editor->status }}</span>
                                @else
                                    <em>Неизвестный пользователь</em>
                                @endif
                            </div>
                        </div>
                        <div class="history-toggle-btn">
                            <i class="fas fa-chevron-down"></i>
                        </div>
                    </div>
                    
                    <div class="history-card-body">
                        <div class="history-details">
                            @if($history->changes_description === "Нет изменений")
                                <div class="no-changes">
                                    <i class="fas fa-info-circle"></i> Нет изменений в данной версии
                                </div>
                            @else
                                <div class="changes-list">
                                    @php
                                        // Создаем массив соответствия идентификаторов полей к названиям вопросов
                                        $questionsMap = [
                                            // Страница 1
                                            'question_1_1' => 'Сколько человек будет проживать в квартире?',
                                            'question_1_2' => 'Есть ли у вас домашние животные и комнатные растения?',
                                            'question_1_3' => 'Есть ли у членов семьи особые увлечения или хобби?',
                                            'question_1_4' => 'Требуется ли перепланировка? Каков состав помещений?',
                                            'question_1_5' => 'Как часто вы встречаете гостей?',
                                            'question_1_6' => 'Адрес',
                                            // Страница 2
                                            'question_2_1' => 'Какой стиль Вы хотите видеть в своем интерьере?',
                                            'question_2_2' => 'Какие имеющиеся предметы обстановки нужно включить в новый интерьер?',
                                            'question_2_3' => 'В каком ценовом сегменте предполагается ремонт?',
                                            'question_2_4' => 'Что не должно быть в вашем интерьере?',
                                            'question_2_5' => 'Бюджет проекта',
                                            'price' => 'Бюджет проекта',
                                            // Страница 3 - помещения
                                            'question_3_1' => 'Прихожая',
                                            'question_3_2' => 'Детская',
                                            'question_3_3' => 'Кладовая',
                                            'question_3_4' => 'Кухня и гостиная',
                                            'question_3_5' => 'Гостевой санузел',
                                            'question_3_6' => 'Гостиная',
                                            'question_3_7' => 'Рабочее место',
                                            'question_3_8' => 'Столовая',
                                            'question_3_9' => 'Ванная комната',
                                            'question_3_10' => 'Кухня',
                                            'question_3_11' => 'Кабинет',
                                            'question_3_12' => 'Спальня',
                                            'question_3_13' => 'Гардеробная',
                                            'question_3_14' => 'Другое',
                                            // Страница 4
                                            'question_4_1' => 'Напольные покрытия',
                                            'question_4_2' => 'Двери',
                                            'question_4_3' => 'Отделка стен',
                                            'question_4_4' => 'Освещение и электрика',
                                            'question_4_5' => 'Потолки',
                                            'question_4_6' => 'Дополнительные пожелания',
                                            // Страница 5
                                            'question_5_1' => 'Пожелания по звукоизоляции',
                                            'question_5_2' => 'Теплые полы',
                                            'question_5_3' => 'Предпочтения по размещению и типу радиаторов',
                                            'question_5_4' => 'Водоснабжение',
                                            'question_5_5' => 'Кондиционирование и вентиляция',
                                            'question_5_6' => 'Сети',
                                            // Общие поля
                                            'rooms' => 'Выбранные помещения',
                                            'custom_rooms' => 'Пользовательские комнаты',
                                            'custom_room_answers' => 'Ответы по пользовательским комнатам',
                                            'references' => 'Референсы',
                                            'documents' => 'Документы',
                                            'status' => 'Статус брифа',
                                            'edit_status' => 'Статус редактирования',
                                        ];
                                        $changes = explode("; ", $history->changes_description);
                                    @endphp
                                    
                                    <ul>
                                        @foreach($changes as $change)
                                            <li class="change-item">
                                                @php
                                                    // Форматирование изменения для лучшей читабельности
                                                    if (strpos($change, 'Изменено') === 0) {
                                                        $parts = explode(": было '", $change);
                                                        if (count($parts) > 1) {
                                                            $field = trim(explode("'", $parts[0])[1]);
                                                            $valueParts = explode("', стало '", $parts[1]);
                                                            if (count($valueParts) > 1) {
                                                                $oldValue = $valueParts[0];
                                                                $newValue = trim(explode("'", $valueParts[1])[0]);
                                                                
                                                                // Получаем название вопроса из карты или используем просто идентификатор
                                                                $fieldDisplay = isset($questionsMap[$field]) 
                                                                    ? "<div class='field-name'>{$questionsMap[$field]} <span class='field-id'>($field)</span></div>" 
                                                                    : "<div class='field-name'>$field</div>";
                                                                
                                                                echo "
                                                                <div class='change-content'>
                                                                    $fieldDisplay
                                                                    <div class='change-values'>
                                                                        <div class='old-value'>
                                                                            <i class='fas fa-minus-circle'></i> 
                                                                            <span>$oldValue</span>
                                                                        </div>
                                                                        <div class='change-arrow'>
                                                                            <i class='fas fa-long-arrow-alt-right'></i>
                                                                        </div>
                                                                        <div class='new-value'>
                                                                            <i class='fas fa-plus-circle'></i>
                                                                            <span>$newValue</span>
                                                                        </div>
                                                                    </div>
                                                                </div>";
                                                            } else {
                                                                echo $change;
                                                            }
                                                        } else {
                                                            echo $change;
                                                        }
                                                    } elseif (strpos($change, 'Обновлено поле') === 0) {
                                                        $field = trim(str_replace("Обновлено поле '", "", explode("'", $change)[1]));
                                                        
                                                        // Получаем название вопроса из карты или используем просто идентификатор
                                                        $fieldDisplay = isset($questionsMap[$field]) 
                                                            ? "<div class='field-name'>{$questionsMap[$field]} <span class='field-id'>($field)</span></div>" 
                                                            : "<div class='field-name'>$field</div>";
                                                        
                                                        echo "
                                                        <div class='change-content'>
                                                            $fieldDisplay
                                                            <div class='field-updated'>
                                                                <i class='fas fa-sync-alt fa-spin'></i>
                                                                <span>Значение обновлено</span>
                                                            </div>
                                                        </div>";
                                                    } else {
                                                        echo "<div class='change-content'>$change</div>";
                                                    }
                                                @endphp
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="empty-history">
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i> История изменений пуста
            </div>
        </div>
    @endif


<style>
     .history-container {
        max-width: 1000px;
        margin: 0 auto;
        padding: 20px;
        font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, 'Open Sans', 'Helvetica Neue', sans-serif;
    }
    
    .history-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 30px;
        flex-wrap: wrap;
        gap: 15px;
    }
    
    .history-header h1 {
        margin: 0;
        color: #333;
        font-size: 24px;
        font-weight: 600;
    }
    
    .history-controls {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
        background: #f8f9fa;
        border-radius: 10px;
        /* padding: 15px; */
        /* box-shadow: 0 1px 3px rgba(0,0,0,0.05); */
    }
    
    .history-search {
        position: relative;
        flex-grow: 1;
        display: flex;
        align-items: center;
    }
    
    .search-icon {
        position: absolute;
        left: 12px;
        color: #adb5bd;
    }
    
    #history-search {
        padding-left: 35px;
        border-radius: 10px;
        border: 1px solid #dee2e6;
        transition: all 0.3s;
    }
    
    #history-search:focus {
        box-shadow: 0 0 0 0.2rem rgba(52, 144, 220, 0.25);
        border-color: #99c7f1;
    }
    
    .expand-controls {
        display: flex;
        gap: 10px;
        margin-left: 15px;
    }
    
    .history-counter {
        margin-left: 15px;
    }
    
    .history-counter .badge {
        padding: 8px 12px;
        font-size: 14px;
        border-radius: 20px;
    }
    
    .history-timeline {
        position: relative;
    }
    
    .history-card {
        background: #fff;
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        margin-bottom: 20px;
        overflow: hidden;
        border-left: 4px solid #3490dc;
        transition: all 0.3s ease;
    }
    
    .history-card:hover {
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.12);
        transform: translateY(-2px);
    }
    
    .history-card-header {
        background: linear-gradient(to right, #f8f9fa, #ffffff);
        padding: 18px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        cursor: pointer;
        transition: background-color 0.3s;
        position: relative;
    }
    
    .history-card-header:hover {
        background: linear-gradient(to right, #f1f3f5, #f8f9fa);
    }
    
    .history-card-header::after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 0;
        right: 0;
        height: 1px;
        background: linear-gradient(to right, rgba(0,0,0,0.05), rgba(0,0,0,0.01));
    }
    
    .history-meta {
        display: flex;
        flex-wrap: wrap;
        gap: 20px;
    }
    
    .history-date, .history-editor {
        display: flex;
        align-items: center;
        color: #495057;
    }
    
    .history-date i, .history-editor i {
        margin-right: 8px;
        color: #3490dc;
    }
    
    .history-time {
        margin-left: 5px;
        font-size: 0.9em;
        color: #6c757d;
    }
    
    .editor-role {
        margin-left: 5px;
        font-size: 0.9em;
        color: #6c757d;
        font-style: italic;
    }
    
    .history-toggle-btn {
        background: #e9ecef;
        width: 32px;
        height: 32px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.3s;
        flex-shrink: 0;
    }
    
    .history-toggle-btn i {
        transition: transform 0.3s ease;
        color: #495057;
    }
    
    .history-card-header.active .history-toggle-btn {
        background: #3490dc;
    }
    
    .history-card-header.active .history-toggle-btn i {
        transform: rotate(180deg);
        color: #fff;
    }
    
    .history-card-body {
        max-height: 0;
        overflow: hidden;
        transition: max-height 0.5s cubic-bezier(0, 1, 0, 1);
        background: #fff;
    }
    
    .history-card-body.active {
        max-height: 2000px;
        transition: max-height 1s ease-in-out;
        padding: 20px;
    }
    
    .history-details h5 {
        margin-top: 0;
        margin-bottom: 15px;
        color: #444;
        font-size: 16px;
    }
    
    .changes-list ul {
        list-style-type: none;
        padding-left: 0;
        margin: 0;
    }
    
    .change-item {
        padding: 15px;
        border-bottom: 1px solid #f1f3f5;
        transition: background-color 0.2s;
    }
    
    .change-item:last-child {
        border-bottom: none;
    }
    
    .change-item:hover {
        background-color: #f8f9fa;
    }
    
    .change-content {
        display: flex;
        flex-direction: column;
        gap: 10px;
    }
    
    .field-name {
        font-size: 16px;
        font-weight: 600;
        color: #212529;
        margin-bottom: 5px;
    }
    
    .field-id {
        font-size: 12px;
        color: #6c757d;
        font-weight: normal;
        opacity: 0.7;
    }
    
    .change-values {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 10px;
    }
    
    .old-value, .new-value {
        display: flex;
        align-items: center;
        padding: 8px 12px;
        border-radius: 6px;
        font-size: 14px;
        gap: 6px;
    }
    
    .old-value {
        color: #842029;
        background-color: #f8d7da;
        border: 1px solid #f5c2c7;
    }
    
    .new-value {
        color: #0f5132;
        background-color: #d1e7dd;
        border: 1px solid #badbcc;
    }
    
    .change-arrow {
        color: #6c757d;
        margin: 0 5px;
    }
    
    .field-updated {
        display: flex;
        align-items: center;
        padding: 8px 12px;
        border-radius: 6px;
        font-size: 14px;
        gap: 6px;
        color: #664d03;
        background-color: #fff3cd;
        border: 1px solid #ffecb5;
    }
    
    .field-updated i {
        animation-duration: 2s;
    }
    
    .no-changes {
        color: #6c757d;
        font-style: italic;
        padding: 15px;
        text-align: center;
        background: #f8f9fa;
        border-radius: 6px;
    }
    
    .empty-history {
        text-align: center;
        padding: 50px 0;
    }
    
    /* Адаптивность для планшетов */
    @media (max-width: 992px) {
        .change-values {
            flex-direction: column;
            align-items: flex-start;
        }
        
        .change-arrow {
            transform: rotate(90deg);
            margin: 5px 0;
        }
    }
    
    /* Адаптивность для мобильных устройств */
    @media (max-width: 768px) {
        .history-header {
            flex-direction: column;
            align-items: flex-start;
        }
        
        .history-meta {
            flex-direction: column;
            gap: 10px;
        }
        
        .history-controls {
            flex-direction: column;
            gap: 15px;
        }
        
        .history-search {
            width: 100%;
        }
        
        .history-counter {
            margin-left: 0;
            align-self: flex-end;
        }
        
        .expand-controls {
            margin-left: auto;
        }
    }
</style>

<script>
    // Функция для переключения отображения деталей
    function toggleHistoryDetails(element) {
        // Если кликнули на кнопку, получаем родительский заголовок
        const header = element.classList.contains('history-card-header') ? element : element.closest('.history-card-header');
        const card = header.closest('.history-card');
        const body = card.querySelector('.history-card-body');
        
        header.classList.toggle('active');
        body.classList.toggle('active');
    }
    
    document.addEventListener('DOMContentLoaded', function() {
        // Поиск по истории изменений
        const searchInput = document.getElementById('history-search');
        if (searchInput) {
            searchInput.addEventListener('input', function() {
                const searchTerm = this.value.toLowerCase();
                const cards = document.querySelectorAll('.history-card');
                let visibleCount = 0;
                
                cards.forEach(card => {
                    const changes = card.getAttribute('data-changes').toLowerCase();
                    const isVisible = changes.includes(searchTerm) || searchTerm === '';
                    
                    card.style.display = isVisible ? 'block' : 'none';
                    if (isVisible) visibleCount++;
                });
                
                // Обновляем счетчик видимых карточек
                const counterElement = document.querySelector('.history-counter .badge');
                if (counterElement) {
                    const noun = getPlural(visibleCount, ['запись', 'записи', 'записей']);
                    counterElement.textContent = `${visibleCount} ${noun}`;
                }
            });
        }
        
        // Обработчики для кнопок "Развернуть все" и "Свернуть все"
        const expandAllBtn = document.getElementById('expand-all');
        const collapseAllBtn = document.getElementById('collapse-all');
        
        if (expandAllBtn) {
            expandAllBtn.addEventListener('click', function() {
                document.querySelectorAll('.history-card-header:not(.active)').forEach(header => {
                    header.classList.add('active');
                    const card = header.closest('.history-card');
                    card.querySelector('.history-card-body').classList.add('active');
                });
            });
        }
        
        if (collapseAllBtn) {
            collapseAllBtn.addEventListener('click', function() {
                document.querySelectorAll('.history-card-header.active').forEach(header => {
                    header.classList.remove('active');
                    const card = header.closest('.history-card');
                    card.querySelector('.history-card-body').classList.remove('active');
                });
            });
        }
    });
    
    // Функция для получения правильного склонения слова
    function getPlural(count, forms) {
        let n = Math.abs(count) % 100;
        let n1 = n % 10;
        
        if (n > 10 && n < 20) return forms[2];
        if (n1 > 1 && n1 < 5) return forms[1];
        if (n1 === 1) return forms[0];
        
        return forms[2];
    }
</script>

