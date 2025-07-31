<!-- Основная страница сделок с использованием компонентов -->
<div class="brifs" id="brifs">
    <h1 class="flex">Ваши сделки</h1>
    
  

    <!-- Подключаем компонент фильтров -->
    @include('deals.components.filters')
</div>

<!-- Скрипты для фильтров -->
@include('deals.components.filters_scripts')

<div class="deal" id="deal">
    <div class="deal__body">
        <div class="deal__cardinator__lists">
            @if ($viewType === 'table')
                <!-- Таблица сделок -->
                @include('deals.components.table_view')
            @else
                <!-- Блочное представление сделок -->
                @include('deals.components.block_view')
            @endif
        </div>
    </div>
</div>

<!-- Контейнер для модальных окон -->
<div id="dealModalContainer"></div>

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

<!-- Подключаем скрипты для обработки модальных окон и таблиц -->
@include('deals.components.modal_scripts')

<!-- Подключаем скрипты для расчета дат -->
@include('deals.components.date_calculator')

<!-- Подключаем скрипты для рейтингов -->
@include('deals.components.rating_scripts')

<!-- Подключаем стили -->
@include('deals.components.styles')

<!-- Добавляем стили для сообщения об информации -->
<style>
    .info-message {
        background-color: #e7f3fe;
        border-left: 4px solid #2196F3;
        padding: 12px;
        margin-bottom: 15px;
        border-radius: 4px;
    }
    
    .info-message p {
        margin: 0;
        color: #0c5396;
        font-size: 14px;
        display: flex;
        align-items: center;
    }
    
    .info-message i {
        margin-right: 8px;
        font-size: 16px;
        color: #2196F3;
    }
</style>
