<div class="container">
    <div class="deals-header">
        <h1 class="deals-title">Мои сделки</h1>
        <div class="deals-actions">
            <div class="deals-filter">
                <select id="statusFilter" class="form-control">
                    <option value="">Все статусы</option>
                    <option value="Новая сделка">Новая сделка</option>
                    <option value="В работе">В работе</option>
                    <option value="На проверке">На проверке</option>
                    <option value="Проект на паузе">Проект на паузе</option>
                    <option value="Проект завершен">Проект завершен</option>
                </select>
                <div class="search-box">
                    <input type="text" id="searchDeals" class="form-control" placeholder="Поиск...">
                    <i class="fas fa-search"></i>
                </div>
            </div>
            <div class="view-switcher">
                <button class="view-btn grid-view active" data-view="grid">
                    <i class="fas fa-th"></i>
                </button>
                <button class="view-btn list-view" data-view="list">
                    <i class="fas fa-list"></i>
                </button>
            </div>
        </div>
    </div>

    @if($userDeals->isEmpty())
        <div class="empty-deals">
            <div class="empty-state" style=" justify-content: center;
    align-items: center;
    display: flex
;
    flex-direction: column;">
                <i class="fas fa-handshake"></i>
                <h3 style="text-align: center">У вас пока нет сделок</h3>
                <p>Здесь будут отображаться ваши сделки после их создания</p>
                
              
            </div>
        </div>
    @else
        <div id="dealsContainer" class="deals-container grid-view">
            @foreach($userDeals as $deal)
                <div class="deal-card" data-id="{{ $deal->id }}" data-status="{{ $deal->status }}">
                    <div class="deal-header">
                        <div class="deal-status status-{{ strtolower(str_replace(' ', '-', $deal->status)) }}">
                            <i class="status-icon 
                                @if($deal->status == 'Проект завершен')
                                    fas fa-check-circle
                                @elseif($deal->status == 'Проект на паузе')
                                    fas fa-pause-circle
                                @elseif($deal->status == 'На проверке')
                                    fas fa-eye
                                @elseif($deal->status == 'В работе')
                                    fas fa-cog fa-spin
                                @else
                                    fas fa-handshake
                                @endif
                            "></i>
                            {{ $deal->status }}
                        </div>
                        <div class="deal-number">#{{ $deal->id }}</div>
                    </div>
                    
                    <div class="deal-body">
                        <h3 class="deal-title">{{ $deal->project_number  ?? 'Не указан'}}</h3>
                          <h2 class="deal-title">{{ $deal->client_name  ?? 'Не указан'}}</h2>
                        @if($deal->description)
                            <div class="deal-description">{{ Str::limit($deal->description, 100) }}</div>
                        @endif
                        
                        <div class="deal-meta">
                            <div class="deal-date">
                                <i class="far fa-calendar-alt"></i>
                                Создана: {{ \Carbon\Carbon::parse($deal->created_date)->format('d.m.Y') }}
                            </div>
                            
                          
                        </div>
                        
                        <div class="deal-team">
                            @php
                                $coordinator = App\Models\User::find($deal->coordinator_id);
                                $architect = App\Models\User::where('status', 'architect')
                                    ->whereHas('deals', function($q) use ($deal) {
                                        $q->where('deal_id', $deal->id);
                                    })
                                    ->first();
                                $designer = App\Models\User::where('status', 'designer')
                                    ->whereHas('deals', function($q) use ($deal) {
                                        $q->where('deal_id', $deal->id);
                                    })
                                    ->first();
                            @endphp
                            
                            @if($coordinator)
                                <div class="team-member coordinator" title="Координатор: {{ $coordinator->name }}">
                                    <div class="avatar">
                                        <img src="{{ $coordinator->avatar_url ?? asset('images/default-avatar.png') }}" alt="Координатор">
                                    </div>
                                    <span>Координатор</span>
                                </div>
                            @endif
                            
                            @if($architect)
                                <div class="team-member architect" title="Архитектор: {{ $architect->name }}">
                                    <div class="avatar">
                                        <img src="{{ $architect->avatar_url ?? asset('images/default-avatar.png') }}" alt="Архитектор">
                                    </div>
                                    <span>Архитектор</span>
                                </div>
                            @endif
                            
                            @if($designer)
                                <div class="team-member designer" title="Дизайнер: {{ $designer->name }}">
                                    <div class="avatar">
                                        <img src="{{ $designer->avatar_url ?? asset('images/default-avatar.png') }}" alt="Дизайнер">
                                    </div>
                                    <span>Дизайнер</span>
                                </div>
                            @endif
                        </div>
                    </div>
                    
                    <div class="deal-footer">
                        <a href="{{ route('deal.view', $deal->id) }}" class="btn btn-primary btn-view-deal">
                            <i class="fas fa-eye"></i> Просмотреть сделку
                        </a>
                        
                       
                    </div>
                    
                    @if($deal->status == 'Проект завершен' && isset($deal->client_rating_avg))
                        <div class="deal-rating">
                            <div class="rating-stars" title="Ваша оценка: {{ number_format($deal->client_rating_avg, 1) }}">
                                @php
                                    $rating = round($deal->client_rating_avg * 2) / 2;
                                    $fullStars = floor($rating);
                                    $halfStar = ceil($rating - $fullStars);
                                    $emptyStars = 5 - $fullStars - $halfStar;
                                @endphp
                                
                                @for($i = 0; $i < $fullStars; $i++)
                                    <i class="fas fa-star"></i>
                                @endfor
                                
                                @if($halfStar)
                                    <i class="fas fa-star-half-alt"></i>
                                @endif
                                
                                @for($i = 0; $i < $emptyStars; $i++)
                                    <i class="far fa-star"></i>
                                @endfor
                                
                                <span class="rating-value">{{ number_format($deal->client_rating_avg, 1) }}</span>
                            </div>
                        </div>
                    @endif
                </div>
            @endforeach
        </div>

    
        
        <!-- Пагинация для списка если будет много сделок -->
        @if($userDeals->count() > 12)
            <div class="deals-pagination">
                <button id="loadMoreDeals" class="btn btn-outline-primary">
                    <i class="fas fa-spinner"></i> Загрузить еще
                </button>
            </div>
        @endif
    @endif
</div>

<!-- Улучшенное модальное окно для оценки сделки -->
<div class="modal fade" id="ratingModal" tabindex="-1" aria-labelledby="ratingModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content modern-modal">
            <div class="modal-header modern-header">
                <h5 class="modal-title" id="ratingModalLabel">
                    <i class="fas fa-star text-warning"></i> Оценка сделки
                </h5>
                <button type="button" class="btn-close modern-close" data-dismiss="modal" aria-label="Close">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body modern-body">
                <div class="rating-header-info">
                    <div class="rating-icon">
                        <i class="fas fa-handshake"></i>
                    </div>
                    <p class="rating-description">
                        Ваша оценка поможет нам улучшить качество обслуживания и выбирать лучших специалистов
                    </p>
                </div>
                
                <form id="ratingForm">
                    <input type="hidden" id="dealIdForRating" name="deal_id" value="">
                    
                    <div class="form-group modern-rating-group">
                        <label class="modern-label">
                            <i class="fas fa-star"></i> Ваша общая оценка
                        </label>
                        <div class="rating-input">
                            <div class="stars modern-stars">
                                <i class="far fa-star" data-value="1" title="Очень плохо"></i>
                                <i class="far fa-star" data-value="2" title="Плохо"></i>
                                <i class="far fa-star" data-value="3" title="Нормально"></i>
                                <i class="far fa-star" data-value="4" title="Хорошо"></i>
                                <i class="far fa-star" data-value="5" title="Отлично"></i>
                            </div>
                            <input type="hidden" name="score" id="ratingScore" value="0">
                            <div class="rating-text">Выберите оценку от 1 до 5 звезд</div>
                        </div>
                    </div>
                    
                    <div class="form-group modern-comment-group">
                        <label for="ratingComment" class="modern-label">
                            <i class="fas fa-comment"></i> Комментарий (необязательно)
                        </label>
                        <textarea id="ratingComment" name="comment" class="form-control modern-textarea" 
                                rows="4" maxlength="500"
                                placeholder="Поделитесь своими впечатлениями о проекте и качестве работы..."></textarea>
                        <div class="character-count">
                            <span id="userRatingCharCount">0</span>/500
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer modern-footer">
                <button type="button" class="btn btn-outline-secondary modern-btn" data-dismiss="modal">
                    <i class="fas fa-times"></i> Отмена
                </button>
                <button type="button" class="btn btn-primary modern-btn" id="submitRating" disabled>
                    <i class="fas fa-paper-plane"></i> Отправить оценку
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Стили для улучшенного модального окна оценки -->
<style>
.modern-modal {
    border-radius: 16px;
    border: none;
    overflow: hidden;
    box-shadow: 0 20px 40px rgba(0,0,0,0.1);
}

.modern-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border: none;
    padding: 20px 24px;
}

.modern-header .modal-title {
    font-weight: 600;
    font-size: 1.3rem;
}

.modern-close {
    background: rgba(255,255,255,0.2);
    border: none;
    color: white;
    border-radius: 50%;
    width: 36px;
    height: 36px;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s ease;
}

.modern-close:hover {
    background: rgba(255,255,255,0.3);
    transform: scale(1.1);
}

.modern-body {
    padding: 24px;
}

.rating-header-info {
    text-align: center;
    margin-bottom: 24px;
    padding: 20px;
    background: #f8f9fa;
    border-radius: 12px;
}

.rating-icon {
    font-size: 2.5rem;
    color: #667eea;
    margin-bottom: 12px;
}

.rating-description {
    color: #6c757d;
    margin: 0;
    font-size: 14px;
    line-height: 1.5;
}

.modern-rating-group {
    margin-bottom: 24px;
}

.modern-label {
    font-weight: 600;
    color: #495057;
    margin-bottom: 12px;
    display: block;
}

.modern-stars {
    display: flex;
    justify-content: center;
    gap: 8px;
    margin-bottom: 12px;
}

.modern-stars i {
    font-size: 2rem;
    color: #e9ecef;
    cursor: pointer;
    transition: all 0.2s ease;
    padding: 8px;
    border-radius: 50%;
}

.modern-stars i:hover,
.modern-stars i.selected {
    color: #ffc107;
    transform: scale(1.1);
}

.modern-stars i:hover {
    background: rgba(255, 193, 7, 0.1);
}

.rating-text {
    text-align: center;
    color: #6c757d;
    font-size: 14px;
    margin-top: 8px;
}

.modern-comment-group {
    margin-bottom: 0;
}

.modern-textarea {
    border: 2px solid #e9ecef;
    border-radius: 8px;
    padding: 12px;
    font-size: 14px;
    transition: border-color 0.2s ease;
    resize: vertical;
}

.modern-textarea:focus {
    border-color: #667eea;
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}

.character-count {
    text-align: right;
    font-size: 12px;
    color: #6c757d;
    margin-top: 4px;
}

.modern-footer {
    background: #f8f9fa;
    border: none;
    padding: 20px 24px;
    display: flex;
    gap: 12px;
    justify-content: flex-end;
}

.modern-btn {
    padding: 10px 20px;
    border-radius: 8px;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 8px;
    transition: all 0.2s ease;
}

.modern-btn:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

.modern-btn.btn-primary:not(:disabled):hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
}

@media (max-width: 576px) {
    .modern-footer {
        flex-direction: column;
    }
    
    .modern-btn {
        width: 100%;
        justify-content: center;
    }
    
    .modern-stars i {
        font-size: 1.8rem;
        padding: 6px;
    }
}
</style>

<!-- Модальное окно для создания сделки из брифа -->
<div class="modal fade" id="createDealFromBriefModal" tabindex="-1" role="dialog" aria-labelledby="createDealFromBriefModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="createDealFromBriefModalLabel">Создание сделки по брифу</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label for="briefSelect">Выберите бриф:</label>
                    <select class="form-control" id="briefSelect" required>
                        <option value="">Выберите бриф</option>
                        <optgroup label="Общие брифы">
                            @foreach(Auth::user()->briefs as $brief)
                                @if(!$brief->deal_id)
                                    <option value="common_{{ $brief->id }}">{{ $brief->title ?? 'Общий бриф №'.$brief->id }} ({{ \Carbon\Carbon::parse($brief->created_at)->format('d.m.Y') }})</option>
                                @endif
                            @endforeach
                        </optgroup>
                        <optgroup label="Коммерческие брифы">
                            @foreach(Auth::user()->commercials as $brief)
                                @if(!$brief->deal_id)
                                    <option value="commercial_{{ $brief->id }}">{{ $brief->title ?? 'Коммерческий бриф №'.$brief->id }} ({{ \Carbon\Carbon::parse($brief->created_at)->format('d.m.Y') }})</option>
                                @endif
                            @endforeach
                        </optgroup>
                    </select>
                    <div id="briefSelectError" class="invalid-feedback">
                        Пожалуйста, выберите бриф
                    </div>
                </div>
                <div class="alert alert-info">
                    После создания сделки по брифу вы сможете отслеживать статус вашего проекта и общаться с исполнителями.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Отмена</button>
                <button type="button" class="btn btn-primary" id="createDealFromBriefBtn">Создать сделку</button>
            </div>
        </div>
    </div>
</div>

<style>

</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Переключение между видами отображения
    document.querySelectorAll('.view-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const viewType = this.dataset.view;
            
            // Убираем активный класс со всех кнопок
            document.querySelectorAll('.view-btn').forEach(b => b.classList.remove('active'));
            
            // Добавляем активный класс на выбранную кнопку
            this.classList.add('active');
            
            // Меняем класс у контейнера сделок
            const container = document.getElementById('dealsContainer');
            container.className = 'deals-container ' + viewType + '-view';
            
            // Сохраняем предпочтение в localStorage
            localStorage.setItem('dealsViewType', viewType);
        });
    });
    
    // Восстанавливаем предыдущий вид из localStorage
    const savedViewType = localStorage.getItem('dealsViewType');
    if (savedViewType) {
        document.querySelector(`.view-btn[data-view="${savedViewType}"]`)?.click();
    }
    
    // Фильтр по статусу
    document.getElementById('statusFilter')?.addEventListener('change', function() {
        const status = this.value;
        const searchText = document.getElementById('searchDeals')?.value.toLowerCase();
        
        filterDeals(status, searchText);
    });
    
    // Поиск по сделкам
    document.getElementById('searchDeals')?.addEventListener('input', function() {
        const status = document.getElementById('statusFilter')?.value;
        const searchText = this.value.toLowerCase();
        
        filterDeals(status, searchText);
    });
    
    // Функция фильтрации сделок
    function filterDeals(status, searchText) {
        const deals = document.querySelectorAll('.deal-card');
        
        deals.forEach(deal => {
            const dealStatus = deal.dataset.status;
            const dealTitle = deal.querySelector('.deal-title')?.textContent.toLowerCase();
            const dealDesc = deal.querySelector('.deal-description')?.textContent.toLowerCase();
            const dealId = deal.dataset.id;
            
            let statusMatch = true;
            let searchMatch = true;
            
            if (status && dealStatus !== status) {
                statusMatch = false;
            }
            
            if (searchText) {
                searchMatch = (dealTitle && dealTitle.includes(searchText)) || 
                             (dealDesc && dealDesc.includes(searchText)) ||
                             dealId.includes(searchText);
            }
            
            if (statusMatch && searchMatch) {
                deal.style.display = '';
            } else {
                deal.style.display = 'none';
            }
        });
    }
    
    // Обработка оценки сделки
    document.querySelectorAll('.btn-rate-deal').forEach(btn => {
        btn.addEventListener('click', function() {
            const dealId = this.dataset.dealId;
            
            document.getElementById('dealIdForRating').value = dealId;
            $('#ratingModal').modal('show');
        });
    });
    
    // Обработка выбора звезд для оценки
    document.querySelectorAll('.stars i').forEach(star => {
        star.addEventListener('click', function() {
            const value = parseInt(this.dataset.value);
            document.getElementById('ratingScore').value = value;
            
            document.querySelectorAll('.stars i').forEach(s => {
                if (parseInt(s.dataset.value) <= value) {
                    s.className = 'fas fa-star selected';
                } else {
                    s.className = 'far fa-star';
                }
            });
            
            const ratingTexts = ['', 'Очень плохо', 'Плохо', 'Нормально', 'Хорошо', 'Отлично'];
            document.querySelector('.rating-text').textContent = ratingTexts[value];
            
            document.getElementById('submitRating').disabled = false;
        });
    });
    
    // Отправка оценки
    document.getElementById('submitRating')?.addEventListener('click', function() {
        const dealId = document.getElementById('dealIdForRating').value;
        const score = document.getElementById('ratingScore').value;
        const comment = document.getElementById('ratingComment').value;
        
        if (score < 1) {
            alert('Пожалуйста, выберите оценку');
            return;
        }
        
        this.disabled = true;
        this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Отправка...';
        
        fetch('{{ route("ratings.store") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                deal_id: dealId,
                score: score,
                comment: comment
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                $('#ratingModal').modal('hide');
                
                // Обновляем интерфейс: добавляем рейтинг к карточке сделки
                const dealCard = document.querySelector(`.deal-card[data-id="${dealId}"]`);
                const rateBtn = dealCard.querySelector('.btn-rate-deal');
                
                if (rateBtn) {
                    rateBtn.remove();
                    
                    // Создаем элемент с рейтингом
                    const ratingHtml = `
                        <div class="deal-rating">
                            <div class="rating-stars" title="Ваша оценка: ${score}">
                                ${'<i class="fas fa-star"></i>'.repeat(score)}
                                ${'<i class="far fa-star"></i>'.repeat(5 - score)}
                                <span class="rating-value">${score}</span>
                            </div>
                        </div>
                    `;
                    
                    dealCard.insertAdjacentHTML('beforeend', ratingHtml);
                }
                
                // Показываем уведомление об успехе
                showNotification('success', 'Спасибо за вашу оценку!');
            } else {
                showNotification('error', data.message || 'Произошла ошибка при отправке оценки');
                this.disabled = false;
                this.innerHTML = 'Отправить оценку';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('error', 'Произошла ошибка при отправке оценки');
            this.disabled = false;
            this.innerHTML = 'Отправить оценку';
        });
    });
    
    // Функция для показа уведомлений
    function showNotification(type, message) {
        const notificationContainer = document.createElement('div');
        notificationContainer.className = `notification notification-${type}`;
        
        const icon = type === 'success' 
            ? '<i class="fas fa-check-circle"></i>' 
            : '<i class="fas fa-exclamation-circle"></i>';
            
        notificationContainer.innerHTML = `
            ${icon}
            <span>${message}</span>
        `;
        
        document.body.appendChild(notificationContainer);
        
        // Анимация появления
        setTimeout(() => {
            notificationContainer.classList.add('show');
        }, 10);
        
        // Удаление через 3 секунды
        setTimeout(() => {
            notificationContainer.classList.remove('show');
            setTimeout(() => {
                document.body.removeChild(notificationContainer);
            }, 300);
        }, 3000);
    }

    // Обработчик для кнопки создания сделки из брифа
    document.getElementById('createDealFromBriefBtn').addEventListener('click', function() {
        const briefSelect = document.getElementById('briefSelect');
        const briefSelectError = document.getElementById('briefSelectError');
        
        // Проверка выбран ли бриф
        if (!briefSelect.value) {
            briefSelectError.style.display = 'block';
            briefSelect.classList.add('is-invalid');
            return;
        }
        
        // Скрываем сообщение об ошибке
        briefSelectError.style.display = 'none';
        briefSelect.classList.remove('is-invalid');
        
        // Получаем значение и разделяем его на тип и ID
        const [briefType, briefId] = briefSelect.value.split('_');
        
        // Показываем индикатор загрузки
        this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Создание...';
        this.disabled = true;
        
        // Получаем CSRF-токен для запроса
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        
        // Отправляем запрос на создание сделки
        fetch('/deal/create-from-brief', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            },
            body: JSON.stringify({
                brief_id: briefId,
                brief_type: briefType,
                client_id: {{ Auth::id() }}
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Показываем сообщение об успехе
                showNotification('success', 'Сделка успешно создана!');
                
                // Закрываем модальное окно
                $('#createDealFromBriefModal').modal('hide');
                
                // Перенаправляем на страницу сделок после небольшой задержки
                setTimeout(() => {
                    window.location.reload();
                }, 1500);
            } else {
                // Показываем сообщение об ошибке
                showNotification('error', data.message || 'Произошла ошибка при создании сделки');
                this.innerHTML = 'Создать сделку';
                this.disabled = false;
            }
        })
        .catch(error => {
            console.error('Ошибка:', error);
            showNotification('error', 'Произошла ошибка при создании сделки');
            this.innerHTML = 'Создать сделку';
            this.disabled = false;
        });
    });
    
    // Функция для отображения уведомлений
    function showNotification(type, message) {
        const notification = document.createElement('div');
        notification.className = `notification notification-${type}`;
        
        const icon = document.createElement('i');
        icon.className = type === 'success' ? 'fas fa-check-circle' : 'fas fa-exclamation-circle';
        
        notification.appendChild(icon);
        notification.appendChild(document.createTextNode(' ' + message));
        
        document.body.appendChild(notification);
        
        // Показываем уведомление
        setTimeout(() => {
            notification.classList.add('show');
        }, 100);
        
        // Скрываем через 3 секунды
        setTimeout(() => {
            notification.classList.remove('show');
            setTimeout(() => {
                document.body.removeChild(notification);
            }, 300);
        }, 3000);
    }
    
    // Обработчик события "change" для выбора брифа
    document.getElementById('briefSelect').addEventListener('change', function() {
        if (this.value) {
            this.classList.remove('is-invalid');
            document.getElementById('briefSelectError').style.display = 'none';
        }
    });
});
</script>
