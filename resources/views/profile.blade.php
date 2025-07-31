@extends('layouts.app')
@section('content')
<div class="container">
    <div class="main__flex">
        <div class="main__ponel">
            @include('layouts/ponel')
        </div>
        <div class="main__module">
            @include('layouts/header')
            
            @if(session('error'))
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
            </div>
            @endif
            
            @if(session('success'))
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> {{ session('success') }}
            </div>
            @endif
            
            @include('module/profile')
            
            @if($user->deleted_at !== null && $user->deleted_at->diffInMinutes(now()) < 60)
            <div class="alert alert-success mt-4">
                <i class="fas fa-check-circle"></i> Ваш аккаунт был успешно восстановлен. Если вы обнаружите какие-либо проблемы с доступом к вашим брифам или сделкам, пожалуйста, свяжитесь с поддержкой.
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
