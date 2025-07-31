<!-- Здесь предполагается, что у вас есть представление для просмотра профиля пользователя -->

<!-- Добавляем секцию с рейтингом пользователя -->
<div class="rating-section">
    <h3>Рейтинг специалиста</h3>
    
    <!-- Кнопка-переключатель для фильтров рейтинга -->
    <div class="filter-toggle" id="ratings-filter-toggle" data-target="#ratings-filter-panel">
        <div class="filter-toggle-text">
            <i class="fas fa-filter"></i> Фильтры рейтинга
            <span class="filter-counter" id="ratings-filter-counter">0</span>
        </div>
        <div class="filter-toggle-icon">
            <i class="fas fa-chevron-down"></i>
        </div>
    </div>
    
    <!-- Панель фильтров для рейтингов -->
    <div class="filter filter-panel" id="ratings-filter-panel">
        <form method="GET" action="{{ url()->current() }}">
            <div class="filter-container">
                <!-- Фильтр по оценке -->
                <div class="filter-group">
                    <label class="filter-label"><i class="fas fa-star"></i> Минимальная оценка</label>
                    <div class="select-container">
                        <select name="min_rating" class="filter-select">
                            <option value="">Все оценки</option>
                            <option value="5" {{ request('min_rating') == '5' ? 'selected' : '' }}>5 звезд</option>
                            <option value="4" {{ request('min_rating') == '4' ? 'selected' : '' }}>4 звезды и выше</option>
                            <option value="3" {{ request('min_rating') == '3' ? 'selected' : '' }}>3 звезды и выше</option>
                            <option value="2" {{ request('min_rating') == '2' ? 'selected' : '' }}>2 звезды и выше</option>
                            <option value="1" {{ request('min_rating') == '1' ? 'selected' : '' }}>1 звезда и выше</option>
                        </select>
                        <i class="fas fa-chevron-down select-icon"></i>
                    </div>
                </div>
                
                <!-- Фильтр по периоду -->
                <div class="filter-group">
                    <label class="filter-label"><i class="fas fa-calendar-alt"></i> Период</label>
                    <div class="date-filter-container">
                        <div class="date-input-wrapper">
                            <i class="fas fa-calendar-day date-icon"></i>
                            <input type="date" name="date_from" value="{{ request('date_from') }}" placeholder="Дата с" class="filter-date">
                        </div>
                        <span class="date-separator"><i class="fas fa-arrow-right"></i></span>
                        <div class="date-input-wrapper">
                            <i class="fas fa-calendar-day date-icon"></i>
                            <input type="date" name="date_to" value="{{ request('date_to') }}" placeholder="Дата по" class="filter-date">
                        </div>
                    </div>
                </div>
                
                <!-- Фильтр по наличию комментария -->
                <div class="filter-group">
                    <label class="filter-label"><i class="fas fa-comment"></i> С комментариями</label>
                    <div class="select-container">
                        <select name="has_comment" class="filter-select">
                            <option value="">Все отзывы</option>
                            <option value="1" {{ request('has_comment') == '1' ? 'selected' : '' }}>Только с комментариями</option>
                            <option value="0" {{ request('has_comment') == '0' ? 'selected' : '' }}>Без комментариев</option>
                        </select>
                        <i class="fas fa-chevron-down select-icon"></i>
                    </div>
                </div>
            </div>
            
            <!-- Панель действий фильтра -->
            <div class="filter-actions">
                <button type="submit" class="filter-button">
                    <i class="fas fa-filter"></i> Применить
                </button>
                <a href="{{ url()->current() }}" class="filter-reset">
                    <i class="fas fa-undo"></i> Сбросить
                </a>
                <div class="filter-counter" data-tooltip="Активных фильтров">
                    <span id="active-ratings-filters-count">0</span>
                </div>
            </div>
        </form>
    </div>
    
    <div class="average-rating">
        <div class="stars">
            @php
                $averageRating = $user->average_rating;
                $fullStars = floor($averageRating);
                $halfStar = $averageRating - $fullStars >= 0.5;
                $emptyStars = 5 - $fullStars - ($halfStar ? 1 : 0);
            @endphp
            
            @for ($i = 0; $i < $fullStars; $i++)
                <i class="fas fa-star filled"></i>
            @endfor
            
            @if ($halfStar)
                <i class="fas fa-star-half-alt filled"></i>
            @endif
            
            @for ($i = 0; $i < $emptyStars; $i++)
                <i class="far fa-star"></i>
            @endfor
        </div>
        <div class="rating-number">
            {{ number_format($averageRating, 1) }} из 5 ({{ $user->receivedRatings()->count() }} оценок)
        </div>
    </div>
    
    <div class="rating-reviews">
        <h4>Последние отзывы</h4>
        @php
            // Применяем фильтры, если они заданы
            $ratingsQuery = $user->receivedRatings()->with('raterUser')->latest();
            
            if (request('min_rating')) {
                $ratingsQuery->where('score', '>=', request('min_rating'));
            }
            
            if (request('date_from')) {
                $ratingsQuery->whereDate('created_at', '>=', request('date_from'));
            }
            
            if (request('date_to')) {
                $ratingsQuery->whereDate('created_at', '<=', request('date_to'));
            }
            
            if (request('has_comment') === '1') {
                $ratingsQuery->whereNotNull('comment')->where('comment', '!=', '');
            } elseif (request('has_comment') === '0') {
                $ratingsQuery->where(function($q) {
                    $q->whereNull('comment')->orWhere('comment', '');
                });
            }
            
            $latestRatings = $ratingsQuery->take(5)->get();
        @endphp
        
        @if ($latestRatings->count() > 0)
            @foreach ($latestRatings as $rating)
                <div class="rating-review">
                    <div class="reviewer-info">
                        <img src="{{ $rating->raterUser->avatar_url ?? asset('storage/icon/profile.svg') }}" alt="{{ $rating->raterUser->name }}" class="reviewer-avatar">
                        <div class="reviewer-details">
                            <h5>{{ $rating->raterUser->name }}</h5>
                            <div class="review-date">{{ $rating->created_at->format('d.m.Y') }}</div>
                        </div>
                    </div>
                    <div class="review-rating">
                        @for ($i = 1; $i <= 5; $i++)
                            <i class="fas fa-star {{ $i <= $rating->score ? 'filled' : '' }}"></i>
                        @endfor
                    </div>
                    @if ($rating->comment)
                        <div class="review-comment">
                            {{ $rating->comment }}
                        </div>
                    @endif
                </div>
            @endforeach
        @else
            <p>Пока нет отзывов.</p>
        @endif
    </div>
</div>

<!-- Добавляем скрипт для инициализации фильтров -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        if (typeof initCollapsibleFilters === 'function') {
            initCollapsibleFilters();
        }
        
        if (typeof updateFilterCounters === 'function') {
            updateFilterCounters();
        }
    });
</script>

<style>
    .rating-section {
        margin-top: 30px;
        padding: 20px;
        background-color: #f9f9f9;
        border-radius: 8px;
    }
    
    .average-rating {
        display: flex;
        align-items: center;
        margin-bottom: 20px;
    }
    
    .stars {
        font-size: 24px;
        margin-right: 15px;
    }
    
    .stars .filled {
        color: #ffbf00;
    }
    
    .rating-number {
        font-size: 18px;
        color: #555;
    }
    
    .rating-reviews h4 {
        margin-bottom: 15px;
    }
    
    .rating-review {
        padding: 15px;
        margin-bottom: 15px;
        background-color: #fff;
        border-radius: 6px;
        box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    }
    
    .reviewer-info {
        display: flex;
        align-items: center;
        margin-bottom: 10px;
    }
    
    .reviewer-avatar {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        object-fit: cover;
        margin-right: 10px;
    }
    
    .reviewer-details h5 {
        margin: 0;
        font-size: 16px;
    }
    
    .review-date {
        font-size: 12px;
        color: #999;
    }
    
    .review-rating {
        font-size: 16px;
        margin-bottom: 10px;
    }
    
    .review-rating .filled {
        color: #ffbf00;
    }
    
    .review-comment {
        color: #333;
        line-height: 1.5;
    }
</style>
