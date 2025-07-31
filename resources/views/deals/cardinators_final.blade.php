<!-- Главный файл с подключением компонентов для страницы сделок -->
@extends('layouts.app')

@section('content')
<div class="container">
    <div class="main__flex">
        <div class="main__ponel">
            @include('layouts/ponel')
        </div>
        <div class="main__module">
            @include('layouts/header')
            
            <!-- Подключаем скомпонованную страницу сделок -->
            @include('deals.components._cardinators_main')
        </div>
    </div>
</div>

<!-- Подключаем файл с исправлениями совместимости JS -->
<script src="{{ asset('/js/deals-compatibility-fixes.js') }}"></script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Проверка доступности функции рейтингов и установка задержки для уверенности
        setTimeout(function() {
            // Проверяем разные варианты доступности функций рейтингов
            if (typeof window.checkPendingRatings === 'function') {
                @if(session('completed_deal_id'))
                console.log('[Рейтинги] Проверка оценок для сделки из сессии:', {{ session('completed_deal_id') }});
                window.checkPendingRatings({{ session('completed_deal_id') }});
                @endif

                // Проверяем локальное хранилище
                const storedDealId = localStorage.getItem('completed_deal_id');
                if (storedDealId) {
                    console.log('[Рейтинги] Найден ID сделки в localStorage:', storedDealId);
                    window.checkPendingRatings(storedDealId);
                    localStorage.removeItem('completed_deal_id');
                }
            } else if (typeof window.RatingSystem !== 'undefined' && typeof window.RatingSystem.checkPendingRatings === 'function') {
                @if(session('completed_deal_id'))
                console.log('[Рейтинги] Проверка оценок для сделки из сессии (RatingSystem):', {{ session('completed_deal_id') }});
                window.RatingSystem.checkPendingRatings({{ session('completed_deal_id') }});
                @endif

                // Проверяем локальное хранилище
                const storedDealId = localStorage.getItem('completed_deal_id');
                if (storedDealId) {
                    console.log('[Рейтинги] Найден ID сделки в localStorage (RatingSystem):', storedDealId);
                    window.RatingSystem.checkPendingRatings(storedDealId);
                    localStorage.removeItem('completed_deal_id');
                }
            }
        }, 1000);
    });

    // Функциональность для админского блока логов
    @if(Auth::check() && Auth::user()->id == 1)
    // Обновление счетчика логов каждые 30 секунд
    function updateLogCounter() {
        fetch('/api/deals/logs-count', {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            credentials: 'same-origin'
        })
        .then(response => response.json())
        .then(data => {
            const badge = document.querySelector('.admin-logs-counter');
            if (badge && data.count !== undefined) {
                badge.textContent = data.count;
                
                // Добавляем анимацию при изменении
                badge.style.transform = 'scale(1.2)';
                setTimeout(() => {
                    badge.style.transform = 'scale(1)';
                }, 200);
            }
        })
        .catch(error => {
            console.log('Ошибка обновления счетчика логов:', error);
        });
    }

    // Инициализация админского функционала
    setTimeout(function() {
        // Автообновление счетчика
        updateLogCounter();
        setInterval(updateLogCounter, 30000); // Каждые 30 секунд

        // Обработка кликов по пунктам меню
        const dropdownItems = document.querySelectorAll('.admin-logs-dropdown .dropdown-item');
        dropdownItems.forEach(item => {
            item.addEventListener('click', function(e) {
                e.preventDefault();
                const href = this.getAttribute('href');
                if (href && href !== '#') {
                    window.location.href = href;
                }
            });
        });

        // Показать тултипы для элементов меню
        const tooltipElements = document.querySelectorAll('[data-bs-toggle="tooltip"]');
        tooltipElements.forEach(element => {
            if (typeof bootstrap !== 'undefined' && bootstrap.Tooltip) {
                new bootstrap.Tooltip(element);
            }
        });
    }, 1500);
    @endif
</script>
@endsection
