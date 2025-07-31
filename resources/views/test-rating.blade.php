@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">Тест системы рейтингов</div>
                <div class="card-body">
                    <h5>Проверка элементов модального окна</h5>
                    <button id="test-rating-btn" class="btn btn-primary">Тестировать рейтинги</button>
                    
                    <div class="mt-3">
                        <h6>Результаты проверки:</h6>
                        <div id="test-results"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Ждем, пока ratings.js полностью загрузится
    setTimeout(function() {
        document.getElementById('test-rating-btn').addEventListener('click', function() {
            testRatingSystem();
        });
    }, 1000);
});

function testRatingSystem() {
    const results = document.getElementById('test-results');
    let html = '<ul>';
    
    // Проверяем основные элементы
    const elements = [
        'rating-modal',
        'rating-user-name',
        'rating-user-role',
        'rating-user-avatar',
        'current-rating-index',
        'total-ratings',
        'rating-instruction',
        'rating-alert',
        'submit-rating',
        'skip-rating',
        'close-all-ratings'
    ];
    
    elements.forEach(id => {
        const element = document.getElementById(id);
        if (element) {
            html += `<li style="color: green;">✓ Элемент #${id} найден</li>`;
        } else {
            html += `<li style="color: red;">✗ Элемент #${id} НЕ найден</li>`;
        }
    });
    
    // Проверяем звезды
    const stars = document.querySelectorAll('#rating-modal .rating-stars .star');
    html += `<li style="color: ${stars.length === 5 ? 'green' : 'red'};">${stars.length === 5 ? '✓' : '✗'} Звезды: найдено ${stars.length} из 5</li>`;
    
    // Проверяем функции
    const functions = ['checkPendingRatings', 'runRatingCheck'];
    functions.forEach(funcName => {
        if (typeof window[funcName] === 'function') {
            html += `<li style="color: green;">✓ Функция ${funcName} доступна</li>`;
        } else {
            html += `<li style="color: red;">✗ Функция ${funcName} НЕ доступна</li>`;
        }
    });
    
    html += '</ul>';
    
    // Тестируем показ модального окна
    if (typeof window.runRatingCheck === 'function') {
        html += '<h6 class="mt-3">Тест показа модального окна:</h6>';
        html += '<button class="btn btn-secondary" onclick="testModal()">Показать тестовое модальное окно</button>';
    }
    
    results.innerHTML = html;
}

function testModal() {
    // Создаем тестовые данные
    if (typeof window.checkPendingRatings === 'function') {
        // Имитируем ответ сервера
        const testData = {
            pending_ratings: [
                {
                    user_id: 1,
                    name: 'Тестовый Архитектор',
                    role: 'architect',
                    avatar_url: '/storage/icon/profile.svg'
                }
            ],
            force_rating: true,
            user_status: 'partner'
        };
        
        // Прямо устанавливаем данные и показываем модальное окно
        if (window.pendingRatings !== undefined) {
            window.pendingRatings = testData.pending_ratings;
            window.currentRatingIndex = 0;
            window.currentDealId = 'test-deal-123';
            
            const modal = document.getElementById('rating-modal');
            if (modal && typeof window.showNextRating === 'function') {
                modal.style.display = 'flex';
                setTimeout(() => modal.classList.add('show'), 10);
                window.showNextRating();
            }
        }
    }
}
</script>
@endsection
