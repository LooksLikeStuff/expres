@section('title', $title_site ?? 'Процесс создания Общего брифа | Личный кабинет Экспресс-дизайн');
@extends('layouts.brifapp')

@vite(['resources/sass/briefs/questions.scss', 'resources/js/briefs/questions.js'])

@section('content')
<div class="container">
    <div class="main__flex">
        <div class="main__ponel">
            @include('layouts/ponel')
        </div>
        <div class="main__module">
            @include('layouts/header')
            @include('common/module/questions')
        </div>
    </div>
</div>
@endsection
