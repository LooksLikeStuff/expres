@section('title', $title_site ?? 'Процесс создания Общего брифа | Личный кабинет Экспресс-дизайн');
@extends('layouts.brifapp')

@vite(['resources/sass/briefs/questions.scss', 'resources/js/briefs/questions.js'])
@section('content')
    <input type="hidden" id="page" value="{{$page}}">

    <div class="container">
        <div class="main__flex">
            <div class="main__ponel">
                @include('layouts/ponel')
            </div>
            <div class="main__module">
                @include('layouts/header')
                    @php
                        $pageInfo = $brief->getPageTitles()[$page];
                    @endphp

                    <div class="form__title" id="top-title">
                        <div class="form__title__info">
                                <h1>{{ $pageInfo['title'] }}</h1>
                                <p>{{ $pageInfo['subtitle'] }}</p>

                            {{-- Показываем уведомление о режиме редактирования --}}
                            @if (isset($brief) && $brief->isEditing())
                                <div class="edit-mode-notice">
                                    <i class="fas fa-edit"></i> Вы редактируете завершенный бриф. Все изменения будут записаны в историю.
                                </div>
                            @endif
                        </div>

                        {{-- Кнопки навигации --}}
                        <div class="form__button flex between">
                            <p class="form__button-ponel-p">Страница {{ $page }}/{{ $totalPages }}</p>
                            @if ($page > 1)
                                <button id="prevPageBtn" type="button" class=" btn-secondary btn-propustit" onclick="goToPrev()">Обратно</button>
                            @endif
                            <button id="nextPageBtn" type="button" class=" btn-primary btn-dalee">Далее</button>

                            @if ($page > 0 && $page < 5)
                                <button id="skipPageBtn" type="button" class=" btn-warning  btn-propustit skip-page-btn" onclick="skipPage()">Пропустить</button>
                            @endif

                            @if ($page >= 5 && !empty(json_decode($brif->skipped_pages ?? '[]')))
                                <span class="skipped-notice">Вы заполняете пропущенные страницы</span>
                            @endif
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

                <form id="briefForm" action="{{ route('briefs.answers', ['brief' => $brief, 'page' => $page]) }}" method="POST"
                      enctype="multipart/form-data" class="back__fon__common csrf-check">
                    @csrf
                    <!-- Скрытое поле для определения направления перехода -->
                    <input type="hidden" name="action" id="actionInput" value="next">
                    <!-- Скрытое поле для определения, была ли страница пропущена -->
                    <input type="hidden" name="skip_page" id="skipPageInput" value="0">

                    {{-- Блок с вопросами форматов "default" и "faq" --}}
                    <div class="form__body flex between wrap">

                        @if($brief->relationLoaded('rooms'))

                        @endif

                        @foreach ($questions as $question)

                            {{-- PRICE — отдельный кейс --}}
                            @if ($question->key === 'price')
                                <div class="form-group flex wrap">
                                    <h2>{{ $question->title }}</h2>
                                    @if (!empty($question->subtitle))
                                        <p>{{ $question->subtitle }}</p>
                                    @endif
                                    <input type="text" name="price" class="form-control required-field price-input"
                                           value="{{ $brif->price ?? '' }}" placeholder="{{ $question->placeholder }}"
                                           data-original-placeholder="{{ $question->placeholder }}" maxlength="500">
                                    <span class="error-message">Это поле обязательно для заполнения</span>
                                </div>

                                {{-- CHECKPOINT --}}
                            @elseif ($question->format === 'checkpoint')
                                <div class="checkpoint flex wrap">
                                    <div class="radio">
                                        <input type="checkbox" id="{{ $question->key }}" class="custom-checkbox"
                                               name="answers[{{ $question->key }}]" value="1"
                                               @if (!empty($brif->{$question->key})) checked @endif>
                                        <label for="{{ $question->key }}">{{ $question->title }}</label>
                                    </div>
                                </div>

                                {{-- DEFAULT и FAQ --}}
                            @elseif (in_array($question->format, ['default', 'faq']))
                                @php
                                    $field = view('briefs.partials.question-field', compact('question', 'brief'))->render();
                                @endphp

                                @if ($question->format === 'default')
                                    <div class="form-group flex wrap">
                                        <h2>{{ $question->title }}</h2>
                                        @if (!empty($question->subtitle))
                                            <p>{{ $question->subtitle }}</p>
                                        @endif
                                        {!! $field !!}
                                    </div>
                                @else
                                    <div class="faq__body">
                                        <div class="faq_block flex center">
                                            <div class="faq_item">
                                                <div class="faq_question" onclick="toggleFaq(this)">
                                                    <h2>{{ $question->title }}</h2>
                                                    <svg class="arrow" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"
                                                         width="24" height="24">
                                                        <path d="M7 10l5 5 5-5z"></path>
                                                    </svg>
                                                </div>
                                                <div class="faq_answer">
                                                    {!! $field !!}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            @endif

                        @endforeach

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

            </div>
        </div>
    </div>
@endsection
