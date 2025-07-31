@extends('layouts.app')

@section('content')
<div class="error-page" style="text-align: center; padding: 50px;">
    <div style="font-size: 100px; color: #e74c3c;">
        <i class="fas fa-server"></i>
    </div>
    <h1 style="font-size: 48px; color: #333;">500</h1>
    <p style="font-size: 18px; color: #666;">Внутренняя ошибка сервера. Пожалуйста, попробуйте позже.</p>
    <a href="{{ route('home') }}" style="display: inline-block; margin-top: 20px; padding: 10px 20px; background-color: #3498db; color: #fff; text-decoration: none; border-radius: 5px;">
        <i class="fas fa-home"></i> Вернуться на главную
    </a>
</div>
@endsection
