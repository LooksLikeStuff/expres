@section('title', $title_site ?? 'Ваши брифы | Личный кабинет Экспресс-дизайн')
@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="main__flex">
            <div class="main__ponel">
                @include('layouts/ponel')
            </div>
            <div class="main__module">
                @include('layouts/header')

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
                                        <li class="brif"
                                            {{--                    onclick="window.location.href='{{ route(--}}
                                            {{--                        $brif instanceof \App\Models\Common--}}
                                            {{--                            ? 'common.questions'--}}
                                            {{--                            : 'commercial.questions',--}}
                                            {{--                        [--}}
                                            {{--                            'id'   => $brif->id,--}}
                                            {{--                            'page' => $brif->current_page--}}
                                            {{--                        ]--}}
                                            {{--                    ) }}'"--}}
                                        >

                                            <h4>{{ $brief->title }} #{{ $brief->id }}</h4>
                                            <div class="brif__body flex">
                                                <ul>
                                                    @foreach ($brief->getPageTitles() as $index => $info)
                                                        <li class="{{ $index + 1 <= $brief->current_page ? 'completed' : '' }}">
                                                            {{ $info['title'] }}
                                                        </li>
                                                    @endforeach
                                                </ul>
                                            </div>
                                            <div class="button__brifs-variab">
                                                <div class="button__brifs flex">
                                                    <!-- Кнопка заполнения (без изменений) -->
                                                    <button class="button__variate2">
                                                        <img src="/storage/icon/create__info.svg" alt="">
                                                        <span>Заполнить</span>
                                                    </button>
                                                    <!-- Кнопка удаления с event.stopPropagation() и вызовом confirmDelete -->
                                                    <button class="button__brief-delete button__variate2 icon"
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
                                            <form id="delete-form-{{ $brief->id }}" action="{{ route('brifs.destroy', $brief->id) }}" method="POST" style="display: none;">
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
                                                        onclick="event.stopPropagation(); window.location.href='{{ route('common.download.pdf') }}'">
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
@endsection
