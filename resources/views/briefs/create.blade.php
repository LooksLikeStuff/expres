@section('title', $title_site ?? 'Создайте Бриф | Личный кабинет Экспресс-дизайн')
@extends('layouts.app')

@vite(['resources/js/briefs/create.js'])

@section('content')
    <div class="container">
        <div class="main__flex">
            <div class="main__ponel">
                @include('layouts/ponel')
            </div>
            <div class="main__module">
                @include('layouts/header')

{{--            Если передан тип брифа - выводим форму создания для переданного типа иначе стандартную форму--}}
                @isset($type)
                    @include('briefs.partials.' . $type->value, ['type' => $type])
                @else
                    @include('briefs.partials.default')
                @endisset

            </div>
        </div>
    </div>
@endsection
