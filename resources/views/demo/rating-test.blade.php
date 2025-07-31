<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Тест системы рейтингов</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    
    <style>
        body {
            padding: 40px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }
        
        .demo-container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            border-radius: 16px;
            padding: 40px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }
        
        .demo-header {
            text-align: center;
            margin-bottom: 40px;
        }
        
        .demo-button {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            padding: 15px 30px;
            border-radius: 10px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            margin: 10px;
        }
        
        .demo-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }
        
        .demo-section {
            margin: 30px 0;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 10px;
            border-left: 4px solid #667eea;
        }
        
        .rating-demo {
            display: flex;
            align-items: center;
            gap: 20px;
            margin: 15px 0;
        }
    </style>
</head>
<body>
    <div class="demo-container">
        <div class="demo-header">
            <h1><i class="fas fa-star text-warning"></i> Демо системы рейтингов</h1>
            <p class="text-muted">Тестирование улучшенной системы оценки специалистов</p>
        </div>
        
        <div class="demo-section">
            <h3><i class="fas fa-modal"></i> Тест модального окна</h3>
            <p>Нажмите кнопку ниже, чтобы протестировать модальное окно рейтинга:</p>
            <button class="demo-button" onclick="testRatingModal()">
                <i class="fas fa-star"></i> Открыть модальное окно рейтинга
            </button>
        </div>
        
        <div class="demo-section">
            <h3><i class="fas fa-components"></i> Компонент рейтинга</h3>
            <p>Различные варианты отображения рейтинга:</p>
            
            <div class="rating-demo">
                <strong>Размер SM:</strong>
                <x-specialist-rating :rating="4.8" :count="156" size="sm" />
            </div>
            
            <div class="rating-demo">
                <strong>Размер MD:</strong>
                <x-specialist-rating :rating="4.2" :count="89" size="md" />
            </div>
            
            <div class="rating-demo">
                <strong>Размер LG:</strong>
                <x-specialist-rating :rating="3.7" :count="45" size="lg" />
            </div>
            
            <div class="rating-demo">
                <strong>Размер XL:</strong>
                <x-specialist-rating :rating="4.9" :count="234" size="xl" />
            </div>
            
            <div class="rating-demo">
                <strong>Без счетчика:</strong>
                <x-specialist-rating :rating="4.5" :count="67" :show-count="false" />
            </div>
            
            <div class="rating-demo">
                <strong>Новый специалист:</strong>
                <x-specialist-rating :rating="0" :count="0" />
            </div>
        </div>
        
        <div class="demo-section">
            <h3><i class="fas fa-notifications"></i> Тест уведомлений</h3>
            <p>Различные типы уведомлений:</p>
            <button class="demo-button" onclick="showTestNotification('success')">
                <i class="fas fa-check-circle"></i> Успех
            </button>
            <button class="demo-button" onclick="showTestNotification('warning')">
                <i class="fas fa-exclamation-triangle"></i> Предупреждение
            </button>
            <button class="demo-button" onclick="showTestNotification('error')">
                <i class="fas fa-exclamation-circle"></i> Ошибка
            </button>
            <button class="demo-button" onclick="showTestNotification('info')">
                <i class="fas fa-info-circle"></i> Информация
            </button>
        </div>
        
        <div class="demo-section">
            <h3><i class="fas fa-features"></i> Ключевые особенности</h3>
            <ul class="list-unstyled">
                <li><i class="fas fa-check text-success"></i> Возможность закрытия модального окна</li>
                <li><i class="fas fa-check text-success"></i> Кнопка "Пропустить" оценку</li>
                <li><i class="fas fa-check text-success"></i> Кнопка "Закрыть все" оценки</li>
                <li><i class="fas fa-check text-success"></i> Прогресс бар для множественных оценок</li>
                <li><i class="fas fa-check text-success"></i> Улучшенная анимация звезд</li>
                <li><i class="fas fa-check text-success"></i> Система уведомлений</li>
                <li><i class="fas fa-check text-success"></i> Адаптивный дизайн</li>
                <li><i class="fas fa-check text-success"></i> Переиспользуемые компоненты</li>
            </ul>
        </div>
    </div>
    
    <!-- Подключаем модальное окно рейтинга -->
    @include('layouts.app', ['ratingModalOnly' => true])
    
    <script>
        // Тестовые данные для демонстрации
        let testPendingRatings = [
            {
                user_id: 1,
                name: 'Алексей Иванов',
                role: 'architect',
                avatar_url: '/storage/icon/profile.svg',
                isOnline: true
            },
            {
                user_id: 2,
                name: 'Мария Петрова', 
                role: 'designer',
                avatar_url: '/storage/icon/profile.svg',
                isOnline: false
            },
            {
                user_id: 3,
                name: 'Дмитрий Сидоров',
                role: 'visualizer', 
                avatar_url: '/storage/icon/profile.svg',
                isOnline: true
            }
        ];
        
        function testRatingModal() {
            // Имитируем наличие пользователей для оценки
            if (typeof window.checkPendingRatings === 'function') {
                // Устанавливаем тестовые данные
                window.pendingRatings = testPendingRatings;
                window.currentRatingIndex = 0;
                window.currentDealId = 'test-deal-123';
                
                // Показываем модальное окно
                const ratingModal = document.getElementById('rating-modal');
                if (ratingModal) {
                    ratingModal.style.display = 'flex';
                    setTimeout(() => {
                        ratingModal.classList.add('show');
                    }, 10);
                    
                    // Инициализируем первого пользователя
                    if (typeof showNextRating === 'function') {
                        showNextRating();
                    }
                } else {
                    alert('Модальное окно рейтинга не найдено. Убедитесь, что подключен файл ratings.js');
                }
            } else {
                alert('Система рейтингов не инициализирована. Проверьте подключение ratings.js');
            }
        }
        
        function showTestNotification(type) {
            const messages = {
                'success': 'Операция выполнена успешно!',
                'warning': 'Внимание! Проверьте введенные данные.',
                'error': 'Произошла ошибка при выполнении операции.',
                'info': 'Это информационное сообщение.'
            };
            
            if (typeof showNotification === 'function') {
                showNotification(messages[type], type);
            } else {
                alert('Функция showNotification не найдена');
            }
        }
        
        // Инициализация при загрузке страницы
        document.addEventListener('DOMContentLoaded', function() {
            console.log('Демо страница загружена');
            
            // Проверяем доступность основных функций
            if (typeof window.checkPendingRatings === 'function') {
                console.log('✅ Система рейтингов загружена');
            } else {
                console.log('❌ Система рейтингов не загружена');
            }
        });
    </script>
</body>
</html>
