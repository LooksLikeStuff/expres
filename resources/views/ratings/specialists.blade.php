@extends('layouts.app')

@section('content')
<div class="container">
    <div class="main__flex">
        <div class="main__ponel">
            @include('layouts/ponel')
        </div>
        <div class="main__module">
            @include('layouts/header')
            
            <div class="specialists-ratings">
                <div class="page-header">
                    <h1>Рейтинги специалистов</h1>
                    <p class="ratings-subtitle">Оценки специалистов от клиентов и партнеров</p>
                </div>
                
                <!-- Кнопка-переключатель для фильтров -->
                <div class="filter-toggle" id="specialists-filter-toggle" data-target="#specialists-filter-panel">
                    <div class="filter-toggle-text">
                        <i class="fas fa-filter"></i> Фильтры и сортировка
                        <span class="filter-counter" id="specialists-filter-counter">0</span>
                    </div>
                    <div class="filter-toggle-icon">
                        <i class="fas fa-chevron-down"></i>
                    </div>
                </div>
                
                <!-- Улучшенная панель фильтров - добавляем дополнительные классы для CSS -->
                <div class="filter filter-panel" id="specialists-filter-panel">
                    <form method="GET" action="{{ route('ratings.specialists') }}" class="ratings-filter-form">
                        <!-- Панель фильтров -->
                        <div class="filter-panels">
                            <div class="search__input search__input-styled">
                                <i class="fas fa-search search-icon"></i>
                                <input type="text" name="search" value="{{ $search ?? '' }}"
                                    placeholder="Поиск по имени специалиста...">
                            </div>
                            
                            <!-- Первая строка фильтров -->
                            <div class="filter-container">
                                <!-- Фильтр по роли -->
                                <div class="filter-group">
                                    <label class="filter-label"><i class="fas fa-user-tag"></i> Роль</label>
                                    <div class="select-container">
                                        <select name="role" class="filter-select">
                                            @foreach ($roles as $key => $roleName)
                                                <option value="{{ $key }}" {{ $role === $key ? 'selected' : '' }}>
                                                    {{ $roleName }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <i class="fas fa-chevron-down select-icon"></i>
                                    </div>
                                </div>
                                
                                <!-- Фильтр по минимальному рейтингу -->
                                <div class="filter-group">
                                    <label class="filter-label"><i class="fas fa-star"></i> Минимальный рейтинг</label>
                                    <div class="select-container">
                                        <select name="min_rating" class="filter-select">
                                            <option value="">Любой</option>
                                            <option value="1" {{ $minRating == '1' ? 'selected' : '' }}>От 1 звезды</option>
                                            <option value="2" {{ $minRating == '2' ? 'selected' : '' }}>От 2 звезд</option>
                                            <option value="3" {{ $minRating == '3' ? 'selected' : '' }}>От 3 звезд</option>
                                            <option value="4" {{ $minRating == '4' ? 'selected' : '' }}>От 4 звезд</option>
                                            <option value="4.5" {{ $minRating == '4.5' ? 'selected' : '' }}>От 4.5 звезд</option>
                                        </select>
                                        <i class="fas fa-chevron-down select-icon"></i>
                                    </div>
                                </div>
                                
                                <!-- Фильтр по максимальному рейтингу -->
                                <div class="filter-group">
                                    <label class="filter-label"><i class="fas fa-star-half-alt"></i> Максимальный рейтинг</label>
                                    <div class="select-container">
                                        <select name="max_rating" class="filter-select">
                                            <option value="">Любой</option>
                                            <option value="1" {{ $maxRating == '1' ? 'selected' : '' }}>До 1 звезды</option>
                                            <option value="2" {{ $maxRating == '2' ? 'selected' : '' }}>До 2 звезд</option>
                                            <option value="3" {{ $maxRating == '3' ? 'selected' : '' }}>До 3 звезд</option>
                                            <option value="4" {{ $maxRating == '4' ? 'selected' : '' }}>До 4 звезд</option>
                                            <option value="5" {{ $maxRating == '5' ? 'selected' : '' }}>До 5 звезд</option>
                                        </select>
                                        <i class="fas fa-chevron-down select-icon"></i>
                                    </div>
                                </div>
                                
                                <!-- Сортировка -->
                                <div class="filter-group">
                                    <label class="filter-label"><i class="fas fa-sort"></i> Сортировка</label>
                                    <div class="select-container">
                                        <select name="sort_by" class="filter-select">
                                            <option value="rating_desc" {{ $sortBy == 'rating_desc' ? 'selected' : '' }}>По рейтингу (высокий → низкий)</option>
                                            <option value="rating_asc" {{ $sortBy == 'rating_asc' ? 'selected' : '' }}>По рейтингу (низкий → высокий)</option>
                                            <option value="reviews_count_desc" {{ $sortBy == 'reviews_count_desc' ? 'selected' : '' }}>По количеству отзывов (много → мало)</option>
                                            <option value="reviews_count_asc" {{ $sortBy == 'reviews_count_asc' ? 'selected' : '' }}>По количеству отзывов (мало → много)</option>
                                            <option value="name_asc" {{ $sortBy == 'name_asc' ? 'selected' : '' }}>По имени (А → Я)</option>
                                            <option value="name_desc" {{ $sortBy == 'name_desc' ? 'selected' : '' }}>По имени (Я → А)</option>
                                        </select>
                                        <i class="fas fa-chevron-down select-icon"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Панель действий фильтра -->
                        <div class="filter-actions">
                            <button type="submit" class="filter-button">
                                <i class="fas fa-filter"></i> Применить фильтры
                            </button>
                            <a href="{{ route('ratings.specialists') }}" class="filter-reset">
                                <i class="fas fa-undo"></i> Сбросить
                            </a>
                            
                            <div class="active-filters-badge" id="active-filters-badge">
                                <span id="active-filters-count">0</span>
                            </div>
                            
                            <!-- Переключение вида отображения (добавляем кнопки) -->
                            <div class="variate__view">
                                <button type="submit" name="view_type" value="blocks" title="Отображение блоками"
                                    class="view-button {{ $viewType === 'blocks' ? 'active-button' : '' }}">
                                    <i class="fas fa-th-large"></i>
                                </button>
                                <button type="submit" name="view_type" value="table" title="Отображение таблицей"
                                    class="view-button {{ $viewType === 'table' ? 'active-button' : '' }}">
                                    <i class="fas fa-table"></i>
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
                
                <!-- Выбор представления данных (карточки или таблица) -->
                @if($viewType === 'table')
                    <!-- Табличное представление -->
                    <div class="specialists-table-container">
                        <table id="specialistsTable" class="table table-hover specialists-table" width="100%">
                            <thead>
                                <tr>
                                    <th>Фото</th>
                                    <th>Имя</th>
                                    <th>Роль</th>
                                    <th>Рейтинг</th>
                                    <th>Кол-во отзывов</th>
                                    <th>Статус</th>
                                    <th>Действия</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($specialists as $specialist)
                                    <tr>
                                        <td>
                                            <div class="specialist-table-avatar">
                                                <img src="{{ $specialist->avatar_url }}" alt="{{ $specialist->name }}">
                                                @if($specialist->isOnline())
                                                    <span class="online-indicator"></span>
                                                @endif
                                            </div>
                                        </td>
                                        <td class="specialist-name">{{ $specialist->name }}</td>
                                        <td>
                                            <span class="specialist-role-badge role-{{ $specialist->status }}">
                                                @switch($specialist->status)
                                                    @case('architect')
                                                        <i class="fas fa-drafting-compass"></i> Архитектор
                                                        @break
                                                    @case('designer')
                                                        <i class="fas fa-paint-brush"></i> Дизайнер
                                                        @break
                                                    @case('visualizer')
                                                        <i class="fas fa-vr-cardboard"></i> Визуализатор
                                                        @break
                                                    @default
                                                        <i class="fas fa-user"></i> {{ $specialist->status }}
                                                @endswitch
                                            </span>
                                        </td>
                                        <td>
                                            <div class="rating-stars-table">
                                                @php
                                                    $avgRating = $specialist->received_ratings_avg_score ?? 0;
                                                    $fullStars = floor($avgRating);
                                                    $halfStar = $avgRating - $fullStars > 0.25 && $avgRating - $fullStars < 0.75;
                                                    $emptyStars = 5 - $fullStars - ($halfStar ? 1 : 0);
                                                @endphp
                                                
                                                @for ($i = 0; $i < $fullStars; $i++)
                                                    <i class="fas fa-star"></i>
                                                @endfor
                                                
                                                @if ($halfStar)
                                                    <i class="fas fa-star-half-alt"></i>
                                                @endif
                                                
                                                @for ($i = 0; $i < $emptyStars; $i++)
                                                    <i class="far fa-star"></i>
                                                @endfor
                                                
                                                <span class="rating-value-table">{{ number_format($avgRating, 1) }}</span>
                                            </div>
                                        </td>
                                        <td class="reviews-count">{{ $specialist->received_ratings_count }}</td>
                                        <td>
                                            @if($specialist->isOnline())
                                                <span class="online-status-table">Онлайн</span>
                                            @else
                                                <span class="offline-status-table">Офлайн</span>
                                            @endif
                                        </td>
                                        <td>
                                            <a href="{{ route('profile.view', $specialist->id) }}" class="action-button">
                                                <i class="fas fa-user"></i> Профиль
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <!-- Контейнер для карточек специалистов (блочное отображение) -->
                    <div class="specialists-grid">
                        @if($specialists->isEmpty())
                            <div class="empty-results">
                                <div class="empty-results-icon">
                                    <i class="fas fa-search"></i>
                                </div>
                                <h3>Нет результатов</h3>
                                <p>Не найдено специалистов, соответствующих заданным критериям</p>
                            </div>
                        @else
                            @foreach($specialists as $specialist)
                                <div class="specialist-card">
                                    <div class="specialist-header">
                                        <div class="specialist-avatar">
                                            <img src="{{ $specialist->avatar_url }}" alt="{{ $specialist->name }}">
                                            @if($specialist->isOnline())
                                                <span class="online-status" title="Онлайн"></span>
                                            @endif
                                        </div>
                                        <div class="specialist-info">
                                            <h3>{{ $specialist->name }}</h3>
                                            <div class="specialist-role">
                                                @switch($specialist->status)
                                                    @case('architect')
                                                        <i class="fas fa-drafting-compass"></i> Архитектор
                                                        @break
                                                    @case('designer')
                                                        <i class="fas fa-paint-brush"></i> Дизайнер
                                                        @break
                                                    @case('visualizer')
                                                        <i class="fas fa-vr-cardboard"></i> Визуализатор
                                                        @break
                                                    @default
                                                        <i class="fas fa-user"></i> {{ $specialist->status }}
                                                @endswitch
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="specialist-rating">
                                        @php
                                            $avgRating = $specialist->received_ratings_avg_score ?? 0;
                                            $ratingsCount = $specialist->received_ratings_count ?? 0;
                                        @endphp
                                        
                                        <x-specialist-rating 
                                            :rating="$avgRating" 
                                            :count="$ratingsCount" 
                                            size="md"
                                            :show-count="true" 
                                        />
                                        
                                        <div class="rating-details">
                                            <span class="rating-text">
                                                @if($avgRating >= 4.5)
                                                    Отличный специалист
                                                @elseif($avgRating >= 4.0)
                                                    Хороший специалист
                                                @elseif($avgRating >= 3.5)
                                                    Надежный специалист
                                                @elseif($avgRating >= 3.0)
                                                    Нормальный специалист
                                                @elseif($avgRating > 0)
                                                    Начинающий специалист
                                                @else
                                                    Новый специалист
                                                @endif
                                            </span>
                                        </div>
                                    </div>
                                    
                                    <div class="divider"></div>
                                    
                                    @if($specialist->latestRatings && $specialist->latestRatings->count() > 0)
                                        <div class="recent-reviews">
                                            <h4>Последние отзывы:</h4>
                                            <div class="reviews-list">
                                                @foreach($specialist->latestRatings as $rating)
                                                    <div class="review-item">
                                                        <div class="reviewer-info">
                                                            <img src="{{ $rating->raterUser->avatar_url }}" 
                                                                alt="{{ $rating->raterUser->name }}" class="reviewer-avatar">
                                                            <div class="reviewer-name">{{ $rating->raterUser->name }}</div>
                                                        </div>
                                                        <div class="review-rating">
                                                            @for ($i = 1; $i <= 5; $i++)
                                                                <i class="fas fa-star {{ $i <= $rating->score ? 'filled' : '' }}"></i>
                                                            @endfor
                                                            <span class="review-date">{{ $rating->created_at->format('d.m.Y') }}</span>
                                                        </div>
                                                        @if($rating->comment)
                                                            <div class="review-comment">
                                                                {{ \Illuminate\Support\Str::limit($rating->comment, 100) }}
                                                            </div>
                                                        @endif
                                                        @if($rating->deal)
                                                            <div class="review-project">
                                                                <span class="project-label">Проект:</span> 
                                                                <span class="project-number">{{ $rating->deal->project_number ?? 'Без номера' }}</span>
                                                            </div>
                                                        @endif
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    @else
                                        <div class="no-reviews">
                                            <i class="fas fa-comment-slash"></i>
                                            <p>Пока нет отзывов</p>
                                        </div>
                                    @endif
                                    
                                    <div class="specialist-actions">
                                        <a href="{{ route('profile.view', $specialist->id) }}" class="btn">
                                            <i class="fas fa-user"></i> Посмотреть профиль
                                        </a>
                                    </div>
                                </div>
                            @endforeach
                        @endif
                    </div>
                @endif
                
                <!-- Пагинация -->
                <div class="ratings-pagination">
                    {{ $specialists->links() }}
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* Улучшенные стили для карточек специалистов */
.specialist-card {
    background: white;
    border-radius: 12px;
    box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
    transition: all 0.3s ease;
    overflow: hidden;
    border: 1px solid #f0f0f0;
}

.specialist-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
    border-color: #e0e0e0;
}

.specialist-header {
    padding: 20px;
    display: flex;
    align-items: center;
    gap: 15px;
    background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
}

.specialist-avatar {
    position: relative;
    flex-shrink: 0;
}

.specialist-avatar img {
    width: 64px;
    height: 64px;
    border-radius: 50%;
    object-fit: cover;
    border: 3px solid #fff;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.online-status {
    position: absolute;
    bottom: 2px;
    right: 2px;
    width: 18px;
    height: 18px;
    background: #28a745;
    border: 3px solid white;
    border-radius: 50%;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.2);
}

.specialist-info h3 {
    margin: 0 0 4px 0;
    font-size: 1.2rem;
    font-weight: 600;
    color: #2c3e50;
}

.specialist-role {
    color: #6c757d;
    font-size: 0.9rem;
    display: flex;
    align-items: center;
    gap: 6px;
}

.specialist-role i {
    color: #007bff;
}

.specialist-rating {
    padding: 16px 20px;
    background: #f8f9fa;
    border-top: 1px solid #e9ecef;
}

.rating-details {
    margin-top: 8px;
}

.rating-text {
    font-size: 0.85rem;
    color: #6c757d;
    font-style: italic;
}

.divider {
    height: 1px;
    background: linear-gradient(to right, transparent, #e9ecef, transparent);
    margin: 0 20px;
}

.recent-reviews {
    padding: 20px;
}

.recent-reviews h4 {
    margin: 0 0 12px 0;
    font-size: 1rem;
    font-weight: 600;
    color: #495057;
}

.review-item {
    display: flex;
    align-items: flex-start;
    gap: 10px;
    margin-bottom: 12px;
    padding: 10px;
    background: #f8f9fa;
    border-radius: 8px;
    border-left: 3px solid #007bff;
}

.reviewer-avatar {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    object-fit: cover;
    flex-shrink: 0;
}

.reviewer-name {
    font-weight: 500;
    font-size: 0.9rem;
    color: #495057;
    margin-bottom: 2px;
}

.review-rating {
    display: flex;
    align-items: center;
    gap: 4px;
    margin-bottom: 4px;
}

.review-rating i {
    font-size: 0.8rem;
    color: #ffc107;
}

.review-comment {
    font-size: 0.85rem;
    color: #6c757d;
    line-height: 1.4;
    margin-top: 4px;
}

.specialist-actions {
    padding: 16px 20px;
    background: white;
    border-top: 1px solid #e9ecef;
    text-align: center;
}

.specialist-actions .btn {
    background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
    color: white;
    border: none;
    padding: 10px 20px;
    border-radius: 8px;
    font-weight: 500;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    transition: all 0.2s ease;
}

.specialist-actions .btn:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(0, 123, 255, 0.3);
    color: white;
    text-decoration: none;
}

/* Адаптивная сетка */
.specialists-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
    gap: 24px;
    padding: 20px 0;
}

@media (max-width: 768px) {
    .specialists-grid {
        grid-template-columns: 1fr;
        gap: 16px;
    }
    
    .specialist-header {
        flex-direction: column;
        text-align: center;
        gap: 12px;
    }
    
    .specialist-actions .btn {
        width: 100%;
        justify-content: center;
    }
}

/* Пустые результаты */
.empty-results {
    text-align: center;
    padding: 60px 20px;
    grid-column: 1 / -1;
}

.empty-results-icon {
    font-size: 4rem;
    color: #dee2e6;
    margin-bottom: 20px;
}

.empty-results h3 {
    color: #6c757d;
    margin-bottom: 8px;
}

.empty-results p {
    color: #adb5bd;
    font-size: 0.9rem;
}

/* Стили для пагинации */
.ratings-pagination {
    margin-top: 30px;
    margin-bottom: 30px;
    display: flex;
    justify-content: center;
}

/* Общие стили для навигации */
.ratings-pagination nav {
    width: 100%;
    display: flex;
    /* max-width: 1000px; */
    align-items: center;
    justify-content: center;
    align-content: center;
}

/* Стили для текста с информацией о страницах */
.ratings-pagination .text-sm.text-gray-700 {
    font-size: 14px;
    color: #666;
    margin-bottom: 15px;
}

/* Контейнер с кнопками */
.ratings-pagination .shadow-sm {
    box-shadow: none;
    display: flex;
    flex-wrap: wrap;
    justify-content: center;
}

/* Стили для всех кнопок пагинации */
.ratings-pagination a,
.ratings-pagination span.relative.inline-flex {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 40px;
    height: 40px;
    padding: 0 10px;
    margin: 0 3px;
    font-size: 14px;
    font-weight: 500;
    border-radius: 6px;
    transition: all 0.2s ease;
    text-decoration: none;
    background-color: #fff;
    border: 1px solid #e2e8f0;
    color: #555;
}

/* Активная страница */
.ratings-pagination span[aria-current="page"] span {
    background-color: #3490dc;
    color: white;
    border-color: #3490dc;
}

/* Стили при наведении на кнопки */
.ratings-pagination a:hover {
    background-color: #f7fafc;
    border-color: #3490dc;
    color: #3490dc;
    transform: translateY(-2px);
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
}

/* Стили для кнопок Назад/Вперед */
.ratings-pagination a[rel="prev"],
.ratings-pagination a[rel="next"] {
    background-color: #f8fafc;
    padding: 0 12px;
}

/* Многоточие */
.ratings-pagination span[aria-disabled="true"] span {
    background-color: #f8fafc;
    color: #888;
    border: 1px solid #e2e8f0;
}

/* Адаптация для мобильных устройств */
@media (max-width: 768px) {
    .ratings-pagination .flex.justify-between {
        flex-direction: column;
        align-items: center;
    }
    
    .ratings-pagination .sm\:hidden {
        display: flex !important;
        width: 100%;
        justify-content: space-between;
        margin-bottom: 15px;
    }
    
    .ratings-pagination a,
    .ratings-pagination span.relative.inline-flex {
        margin-bottom: 8px;
    }
    
    /* Скрываем десктопное отображение на мобильных */
    .ratings-pagination .sm\:flex-1.sm\:flex.sm\:items-center.sm\:justify-between {
        display: none !important;
    }
}

/* Дополнительные улучшения для десктопа */
@media (min-width: 769px) {
    .ratings-pagination .sm\:hidden {
        display: none !important;
    }
    
    .ratings-pagination .sm\:flex-1.sm\:flex.sm\:items-center.sm\:justify-between {
        display: flex !important;
        align-items: center;
        justify-content: space-between;
        flex-direction: column;
        align-content: center;
    }
}
</style>

<!-- Подключаем DataTables для табличного вида -->
@if($viewType === 'table')
<link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css">
<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>

<script>
    $(document).ready(function() {
        $('#specialistsTable').DataTable({
            language: {
                url: 'https://cdn.datatables.net/plug-ins/1.13.4/i18n/ru.json'
            },
            paging: false,
            searching: false,
            info: false,
            ordering: true,
            responsive: true,
            autoWidth: false,
            columnDefs: [
                { orderable: false, targets: [0, 6] }
            ]
        });
    });
    
  
</script>
@endif

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Локальная функция для инициализации фильтров - дублирует глобальную, но гарантирует работу
        function localInitFilter() {
            const toggle = document.getElementById('specialists-filter-toggle');
            const panel = document.getElementById('specialists-filter-panel');
            const icon = toggle.querySelector('.filter-toggle-icon i');
            
            // Проверка localStorage
            const isExpanded = localStorage.getItem('filter_specialists-filter-panel') === 'expanded';
            
            // Установка исходного состояния
            if (isExpanded) {
                panel.classList.add('expanded');
                icon.classList.add('rotated');
            }
            
            // Обработчик клика
            toggle.addEventListener('click', function(event) {
                event.preventDefault();
                panel.classList.toggle('expanded');
                icon.classList.toggle('rotated');
                
                // Сохранение состояния
                if (panel.classList.contains('expanded')) {
                    try {
                        localStorage.setItem('filter_specialists-filter-panel', 'expanded');
                    } catch (e) {
                        console.warn('Не удалось сохранить состояние фильтра');
                    }
                } else {
                    try {
                        localStorage.setItem('filter_specialists-filter-panel', 'collapsed');
                    } catch (e) {
                        console.warn('Не удалось сохранить состояние фильтра');
                    }
                }
            });
            
            // Подсчет активных фильтров
            countActiveFilters();
        }
        
        // Сначала попробуем использовать глобальную функцию
        if (typeof initCollapsibleFilters === 'function') {
            try {
                initCollapsibleFilters();
                console.log('Использованы глобальные функции для инициализации фильтров');
            } catch (e) {
                console.warn('Ошибка использования глобальной функции:', e);
                // Если глобальная функция не сработала, используем локальную
                localInitFilter();
            }
        } else {
            // Если глобальной функции нет, используем локальную
            console.log('Глобальная функция не найдена, используем локальную');
            localInitFilter();
        }
        
        // Обновление счетчика через глобальную функцию
        if (typeof updateFilterCounters === 'function') {
            try {
                updateFilterCounters();
            } catch (e) {
                console.warn('Ошибка при обновлении счетчиков фильтров:', e);
            }
        }
        
        // Функция подсчета активных фильтров
        function countActiveFilters() {
            const form = document.querySelector('.ratings-filter-form');
            if (!form) return;
            
            let count = 0;
            
            // Проверяем текстовые поля и селекты
            const inputs = form.querySelectorAll('input[type="text"], select');
            inputs.forEach(input => {
                if (input.value && input.name !== 'sort_by' && input.name !== 'view_type' && input.value !== 'all') {
                    count++;
                    // Добавляем класс для выделения активных фильтров
                    input.classList.add('filter-active');
                } else {
                    input.classList.remove('filter-active');
                }
            });
            
            // Обновляем счетчик активных фильтров
            const countBadge = document.getElementById('active-filters-badge');
            const countElement = document.getElementById('active-filters-count');
            const specialistsCounter = document.getElementById('specialists-filter-counter');
            
            if (count > 0) {
                if (countElement) countElement.textContent = count;
                if (countBadge) countBadge.classList.add('show');
                if (specialistsCounter) {
                    specialistsCounter.textContent = count;
                    specialistsCounter.classList.add('active');
                }
                
                // Если есть активные фильтры, раскрываем панель
                const panel = document.getElementById('specialists-filter-panel');
                const toggle = document.getElementById('specialists-filter-toggle');
                const icon = toggle?.querySelector('.filter-toggle-icon i');
                
                if (panel && !panel.classList.contains('expanded')) {
                    panel.classList.add('expanded');
                    if (icon) icon.classList.add('rotated');
                    try {
                        localStorage.setItem('filter_specialists-filter-panel', 'expanded');
                    } catch (e) {
                        console.warn('Не удалось сохранить состояние фильтра');
                    }
                }
            } else {
                if (countElement) countElement.textContent = '0';
                if (countBadge) countBadge.classList.remove('show');
                if (specialistsCounter) {
                    specialistsCounter.textContent = '0';
                    specialistsCounter.classList.remove('active');
                }
            }
        }
        
        // Подсветка полей при изменении
        const filterInputs = document.querySelectorAll('.ratings-filter-form input, .ratings-filter-form select');
        filterInputs.forEach(field => {
            field.addEventListener('change', function() {
                countActiveFilters();
            });
        });
        
        // Анимация появления карточек
        const cards = document.querySelectorAll('.specialist-card');
        if (cards.length) {
            cards.forEach((card, index) => {
                card.style.opacity = '0';
                card.style.transform = 'translateY(20px)';
                setTimeout(() => {
                    card.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
                    card.style.opacity = '1';
                    card.style.transform = 'translateY(0)';
                }, 100 + (index * 50)); // Задержка для каждой карточки
            });
        }
    });
</script><script>
      // Добавляем обработчик для корректной работы фильтров
      document.addEventListener('DOMContentLoaded', function() {
        // Инициализация раскрывающихся фильтров
        if (typeof initCollapsibleFilters === 'function') {
            initCollapsibleFilters();
        } else {
            console.warn('Функция initCollapsibleFilters не определена');
        }
        
        // Обновляем счетчики фильтров после загрузки страницы
        if (typeof updateFilterCounters === 'function') {
            updateFilterCounters();
        }
        
        // Добавляем обработчики для подсветки полей с фильтрами
        const filterInputs = document.querySelectorAll('.filter input, .filter select');
        filterInputs.forEach(input => {
            input.addEventListener('change', function() {
                if (this.value && this.name !== 'view_type') {
                    this.classList.add('filter-active');
                } else {
                    this.classList.remove('filter-active');
                }
                if (typeof updateFilterCounters === 'function') {
                    updateFilterCounters();
                }
            });
            
            // Инициализация подсветки при загрузке
            if (input.value && input.name !== 'view_type') {
                input.classList.add('filter-active');
            }
        });
    });
</script>
@endsection
