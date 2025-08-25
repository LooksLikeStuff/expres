@section('title', $title_site ?? 'Процесс создания Общего брифа | Личный кабинет Экспресс-дизайн');
@extends('layouts.brifapp')

@vite(['resources/js/briefs/questions.js', 'resources/sass/briefs/questions.scss'])
@section('content')
    <div class="container">
        <div class="main__flex">
            <div class="main__ponel">
                @include('layouts/ponel')
            </div>
            <div class="main__module">
                @include('layouts/header')

                <div class="form__title" id="top-title">
                    <div class="form__title__info">
                        <h1>Выберите помещения</h1>
                        <p>Отметьте те помещения, над которыми будем работать в проекте</p>
                    </div>

                    {{-- Кнопки навигации --}}
                    <div class="form__button flex between">
                        <p class="form__button-ponel-p">Страница {{ 0 }}/{{ 5 }}</p>

                        <button type="button" id="nextPageBtn" class=" btn-primary btn-dalee">Далее
                        </button>
                    </div>
                </div>
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

                <form id="briefForm" action="{{ route('briefs.rooms.store', ['brief' => $brief]) }}"
                      method="POST" class="back__fon__common csrf-check">
                    @csrf

                    <!-- Скрытое поле для определения направления перехода -->
                    <input type="hidden" name="action" id="actionInput" value="next">
                    <!-- Скрытое поле для определения, была ли страница пропущена -->
                    <input type="hidden" name="skip_page" id="skipPageInput" value="0">

                    <div class="form__body flex between wrap pointblock">
                        <!-- Добавим контейнер для пользовательских комнат -->
                        <div id="custom-rooms-container" class="display-rooms-container"></div>

                        <!-- Форма для добавления пользовательской комнаты -->
                        <div class="custom-room-form">
                            <h3>Добавить свою комнату:</h3>
                            <div class="custom-room-inputs">
                                <input type="text" id="custom-room-name" class="form-control"
                                       placeholder="Название комнаты">
                                <button type="button" id="add-custom-room" class="btn btn-primary">Добавить</button>
                            </div>
                        </div>
                        @foreach ($rooms as $room)
                            <div class="checkpoint flex wrap">
                                <div class="radio">
                                    <input type="checkbox" id="room_{{ $room['key'] }}" class="custom-checkbox"
                                           name="rooms[]" value="{{ $room['title'] }}"
                                           @checked(isset($brief->{$room['key']}))>
                                    <label for="room_{{ $room['key'] }}">{{ $room['title'] }}</label>
                                </div>
                            </div>
                        @endforeach


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
                                <h4>Загрузка информации</h4>
                                <p>Пожалуйста, подождите. Ваша информц загружаются на сервер.</p>
                                <div class="loader-progress">
                                    <div class="loader-progress-bar"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
@endsection
