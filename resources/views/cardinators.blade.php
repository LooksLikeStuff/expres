@extends('layouts.app')
@section('content')
<div class="container">
    <div class="main__flex">
        <div class="main__ponel">
            @include('layouts/ponel')
        </div>
        <div class="main__module">
            @include('layouts/header')
            @include('deals/cardinators_final')
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Проверка доступности функции рейтингов и установка задержки для уверенности
        setTimeout(function() {
            if (typeof window.checkPendingRatings === 'function') {
                @if(session('completed_deal_id'))
                console.log('[Рейтинги] Проверка оценок для сделки из сессии:', {{ session('completed_deal_id') }});
                window.checkPendingRatings({{ session('completed_deal_id') }});
                @endif

                // Проверяем локальное хранилище
                const storedDealId = localStorage.getItem('completed_deal_id');
                if (storedDealId) {
                    console.log('[Рейтинги] Проверка оценок для сделки из localStorage:', storedDealId);
                    window.checkPendingRatings(storedDealId);
                    localStorage.removeItem('completed_deal_id');
                }
            } else {
                console.error('[Рейтинги] Функция checkPendingRatings не определена');
            }
        }, 500); // Небольшая задержка для гарантии загрузки скриптов
    });
</script>
@endsection
