@section('title', $title_site ?? 'Ваши брифы | Личный кабинет Экспресс-дизайн')
@extends('layouts.app')

@section('content')
    @vite(['resources/js/briefs/index.js', 'resources/sass/briefs/index.scss'])
    
    <div class="container">
        <div class="main__flex">
            <div class="main__ponel">
                @include('layouts/ponel')
            </div>
            <div class="main__module" id="step-1">
                @include('layouts/header')

                <div id="step-mobile-1">
                    @if ($activeBriefs->isEmpty() && $inactiveBriefs->isEmpty())
                        {{-- Если пользователь не имеет никаких брифов --}}
                        <form action="{{ route('briefs.store') }}" method="POST" class="div__create_form" id="step-3" >
                            @csrf
                            <div class="div__create_block">
                                <h1>
                                    <span class="Jikharev">Добро пожаловать!</span>
                                </h1>
                                <p><strong>Дорогой клиент,</strong> для продолжения требуется пройти <strong>бриф-опросник</strong> </p>
                                <div class="button__create__brifs flex gap3" id="step-8">
                                    @foreach($briefTypes as $type)
                                        <button type="submit" class="button__icon" name="brif_type" value="{{ $type->value }}">
                                            <span>Создать {{ $type->label() }} </span> 
                                            <img src="/storage/icon/plus.svg" alt="">
                                        </button>
                                    @endforeach
                                </div>
                            </div>
                        </form>
                        <!-- Кнопка сброса -->
                        {{-- <div class="question_class-button">
                            <button onclick="clearTutorialData()">
                                <img src="/storage/icon/qustion.svg" alt="Сбросить обучение">
                            </button>
                        </div> --}}
                    @else
                        {{-- Если у пользователя есть хотя бы один бриф --}}
                        <div class="brifs wow fadeInLeft" data-wow-duration="1.5s" data-wow-delay="1.5s" id="brifs">
                            <h1 class="flex">
                                Ваши брифы
                            </h1>

                            <div class="brifs__button__create flex">
                                @foreach($briefTypes as $type)
                                    <a href="{{ route('briefs.create', ['type' => $type->value]) }}" class="button__icon">
                                        <span>Создать {{$type->label()}} </span>
                                        <img src="/storage/icon/plus.svg" alt="plus">
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    @endif
                    <div class="brifs__body wow fadeInLeft" data-wow-duration="1.5s" data-wow-delay="1.5s">
                        <!-- Активные брифы -->
                        <div class="brifs__section">
                            <h2>Активные брифы</h2>

                            @if ($activeBriefs->isEmpty())
                                <ul class="brifs__list brifs__list__null">
                                    <li class="brif" onclick="window.location.href='{{ route('briefs.create') }}'">
                                        <p>Создать бриф</p>
                                    </li>
                                </ul>
                            @else
                                <ul class="brifs__list">
                                    @foreach ($activeBriefs as $brief)
                                        <li class="brif">
                                            <h4>{{ $brief->title }} #{{ $brief->id }}</h4>
                                            <div class="brif__body flex">
                                                <ul>
                                                    @foreach ($brief->getPageTitles() as $pageNumber => $info)
                                                        @php
                                                            $isCompleted = $brief->pagesStatus[$pageNumber] ?? false
                                                        @endphp
                                                        <li class="{{ $isCompleted ? 'completed' : '' }}">
                                                            {{ $info['title'] }}
                                                        </li>
                                                    @endforeach
                                                </ul>
                                            </div>
                                            <div class="button__brifs-variab">
                                                <div class="button__brifs flex">
                                                    <!-- Кнопка заполнения -->
                                                    <button class="button__variate2" onclick="window.location.href='{{ route('briefs.questions', ['brief' => $brief->id, 'page' => $brief->getFirstSkippedPage()]) }}'">
                                                        <img src="/storage/icon/create__info.svg" alt="">
                                                        <span>Заполнить</span>
                                                    </button>
                                                    <!-- Кнопка удаления с event.stopPropagation() и вызовом confirmDelete -->
                                                    <button class="button__variate2 icon"
                                                            data-brief-id="{{$brief->id}}"
                                                            onclick="event.stopPropagation(); confirmDelete({{ $brief->id }});">
                                                        <img src="/storage/icon/close__info.svg" alt="">
                                                    </button>
                                                </div>
                                                <p class="flex wd100 between">
                                                    <span>{{ $brief->created_at->format('H:i') }}</span>
                                                    <span>{{ $brief->created_at->format('d.m.Y') }}</span>
                                                </p>

                                            </div>

                                            <!-- Скрытая форма для удаления -->
                                            <form id="delete-form-{{ $brief->id }}" action="{{ route('briefs.destroy', $brief->id) }}" method="POST" style="display: none;">
                                                @csrf
                                                @method('DELETE')
                                            </form>
                                        </li>

                                    @endforeach
                                </ul>
                            @endif
                        </div>

                        {{-- Завершенные брифы --}}
                        <div class="brifs__section brifs__section__finished">
                            <h2>Завершенные брифы</h2>

                            @if ($inactiveBriefs->isEmpty())
                                <p>У вас нет завершенных брифов.</p>
                            @else
                                <ul class="brifs__list">
                                    @foreach ($inactiveBriefs as $brief)
                                        <li class="brif"
                                            onclick="window.location.href={{route('briefs.show', $brief)}}">

                                            <h4>{{ $brief->title }} #{{ $brief->id }}</h4>

                                            <div class="button__brifs flex">
                                                <button class="button__variate2"><img src="/storage/icon/create__info.svg" alt=""> <span>Посмотреть</span></button>
                                                <button class="button__variate2"
                                                        onclick="event.stopPropagation(); window.location.href='{{ route('briefs.pdf', $brief) }}'">
                                                    <span>Скачать PDF</span>
                                                </button>
                                            </div>
                                            <p class="flex wd100 between">
                                                <span>{{ $brief->created_at->format('H:i') }}</span>
                                                <span>{{ $brief->created_at->format('d.m.Y') }}</span>
                                            </p>
                                        </li>
                                    @endforeach
                                </ul>
                            @endif
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
@endsection
