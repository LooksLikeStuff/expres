<div class="profile-container">
    <div class="profile-header">
        <h1 class="profile-title">Профиль пользователя</h1>
        <p class="profile-subtitle">Информация о {{ $target->name }}</p>
        
        @if(Auth::user()->status === 'admin')
        <div class="admin-controls">
            <button id="toggle-edit-mode" class="btn btn-primary">
                <i class="fas fa-edit"></i> Редактировать профиль
            </button>
        </div>
        @endif
    </div>

    <div class="profile-grid">
        <!-- Левая панель профиля -->
        <div class="profile-sidebar">
            <div class="profile-user">
                <div class="profile-avatar-wrapper">
                    <img src="{{ $target->avatar_url ? asset($target->avatar_url) : asset('storage/icon/profile.svg') }}" alt="Аватар" class="profile-avatar">
                </div>

                <!-- Улучшаем блок наград, отображая до 10 самых новых наград для всех статусов кроме 'user' -->
                @if($target->status !== 'user' && $target->awards->count() > 0)
                <div class="profile-awards">
                    @foreach($target->awards->sortByDesc('pivot.awarded_at')->take(10) as $award)
                    <div class="award-icon" title="{{ $award->name }}: {{ $award->description }}" data-award-date="{{ is_string($award->pivot->awarded_at) ? \Carbon\Carbon::parse($award->pivot->awarded_at)->format('d.m.Y') : $award->pivot->awarded_at->format('d.m.Y') }}">
                        {!! $award->icon !!}
                    </div>
                    @endforeach
                </div>
                @endif

                <h2 class="profile-name">{{ $target->name }}</h2>
                <div class="profile-status">{{ ucfirst($target->status) ?? 'Пользователь' }}</div>
                
                <p class="profile-join-date">
                    <i class="fas fa-calendar-alt"></i> 
                    На сайте с {{ $target->created_at->format('d.m.Y') }}
                </p>
                
                @if(in_array($target->status, ['architect', 'designer', 'executor', 'coordinator', 'visualizer']))
                <div class="profile-badges">
                    @if($target->experience)
                    <span class="profile-badge">
                        <i class="fas fa-briefcase"></i> Стаж: {{ $target->experience }}
                    </span>
                    @endif
                    
                    @if($target->rating)
                    <span class="profile-badge">
                        <i class="fas fa-star"></i> Рейтинг: {{ $target->rating }}
                    </span>
                    @endif
                    
                    @if($target->active_projects_count)
                    <span class="profile-badge">
                        <i class="fas fa-tasks"></i> Проекты: {{ $target->active_projects_count }}
                    </span>
                    @endif
                </div>
                @endif
            </div>
            
            <ul class="profile-menu">
                <li class="profile-menu-item">
                    <a href="#info" class="profile-menu-link active" data-section="info-section">
                        <span class="profile-menu-icon"><i class="fas fa-user"></i></span>
                        Информация
                    </a>
                </li>
                @if(in_array($target->status, ['architect', 'designer', 'executor', 'coordinator', 'visualizer']))
                <li class="profile-menu-item">
                    <a href="#rating" class="profile-menu-link" data-section="rating-section">
                        <span class="profile-menu-icon"><i class="fas fa-star"></i></span>
                        Рейтинг и отзывы
                    </a>
                </li>
                @endif

                <!-- Добавляем вкладку с наградами, если пользователь не 'user' -->
                @if($target->status !== 'user')
                <li class="profile-menu-item">
                    <a href="#awards" class="profile-menu-link" data-section="awards-section">
                        <span class="profile-menu-icon"><i class="fas fa-award"></i></span>
                        Награды
                    </a>
                </li>
                @endif
                
                @if(Auth::user()->status === 'admin')
                <li class="profile-menu-item admin-menu-item">
                    <a href="#admin" class="profile-menu-link" data-section="admin-section">
                        <span class="profile-menu-icon"><i class="fas fa-cogs"></i></span>
                        Управление
                    </a>
                </li>
                @endif
            </ul>
            
            <div class="profile-actions">
                @if(in_array($target->status, ['architect', 'designer', 'executor', 'visualizer']))
                    @if($target->portfolio_link)
                    <a href="{{ $target->portfolio_link }}" target="_blank" class="btn btn-primary btn-block">
                        <i class="fas fa-briefcase"></i> Портфолио
                    </a>
                    @endif
                @endif
                <!-- Здесь можно добавить другие действия для взаимодействия с пользователем -->
            </div>
        </div>
        
        <!-- Правая панель профиля -->
        <div class="profile-content">
            <!-- Основная информация -->
            <div class="profile-card profile-section active" id="info-section">
                <div class="profile-card-header">
                    <h3 class="profile-card-title">Информация о пользователе</h3>
                </div>
                <div class="profile-card-body">
                    <!-- Режим просмотра -->
                    <div class="view-mode">
                        <div class="profile-info-row">
                            <div class="profile-info-label">ФИО</div>
                            <div class="profile-info-value">{{ $target->name }}</div>
                        </div>
                        
                        <div class="profile-info-row">
                            <div class="profile-info-label">Email</div>
                            <div class="profile-info-value">{{ $target->email ?: 'Не указан' }}</div>
                        </div>
                        
                        <div class="profile-info-row">
                            <div class="profile-info-label">Телефон</div>
                            <div class="profile-info-value">
                                @if($target->phone)
                                    {{ preg_replace('/(\d{1})(\d{3})(\d{3})(\d{2})(\d{2})/', '+$1 ($2) $3-$4-$5', $target->phone) }}
                                @else
                                    Не указан
                                @endif
                            </div>
                        </div>
                        
                        @if($target->city)
                        <div class="profile-info-row">
                            <div class="profile-info-label">Город</div>
                            <div class="profile-info-value">{{ $target->city }}</div>
                        </div>
                        @endif
                        
                        @if($target->status == 'partner' && $target->contract_number)
                        <div class="profile-info-row">
                            <div class="profile-info-label">Номер договора</div>
                            <div class="profile-info-value">{{ $target->contract_number }}</div>
                        </div>
                        @endif
                        
                        @if($target->comment)
                        <div class="profile-info-row">
                            <div class="profile-info-label">Комментарий</div>
                            <div class="profile-info-value">{{ $target->comment }}</div>
                        </div>
                        @endif
                        
                        <!-- Статистика пользователя -->
                        @if(in_array($target->status, ['architect', 'designer', 'executor', 'coordinator', 'visualizer']))
                        <div class="profile-stats-container">
                            @if($target->experience)
                            <div class="profile-stat-item">
                                <span class="profile-stat-value">{{ $target->experience }}</span>
                                <span class="profile-stat-label">Опыт работы</span>
                            </div>
                            @endif
                            
                            @if($target->active_projects_count)
                            <div class="profile-stat-item">
                                <span class="profile-stat-value">{{ $target->active_projects_count }}</span>
                                <span class="profile-stat-label">Активных проектов</span>
                            </div>
                            @endif
                            
                            @php
                                // Получаем количество завершенных проектов из свойства или через отношение с моделью Deal
                                if(isset($target->completed_projects_count)) {
                                    $completedProjects = $target->completed_projects_count;
                                } elseif(isset($target->dealsPivot)) {
                                    $completedProjects = $target->dealsPivot()
                                        ->whereHas('deal', function($q) {
                                            $q->whereIn('status', ['Проект готов', 'Проект завершен']);
                                        })
                                        ->count();
                                } else {
                                    $completedProjects = isset($target->completed_projects) ? $target->completed_projects : 0;
                                }
                                
                                // Получаем средний рейтинг из свойства или через отношение с моделью Rating
                                if(isset($target->average_rating)) {
                                    $avgRating = $target->average_rating;
                                } elseif(isset($target->receivedRatings)) {
                                    $avgRating = $target->receivedRatings()->avg('score') ?: 0;
                                } else {
                                    $avgRating = $target->rating ?: 0;
                                }

                                // Для пользователей со статусом user, показываем фейковый рейтинг не ниже 4.0
                                if(strtolower($viewer->status) === 'user' && in_array(strtolower($target->status), ['architect', 'designer', 'executor', 'visualizer'])) {
                                    $avgRating = max(4.0, (float)$avgRating);
                                }
                            @endphp
                            
                            <div class="profile-stat-item">
                                <span class="profile-stat-value">{{ $completedProjects }}</span>
                                <span class="profile-stat-label">Завершенных проектов</span>
                            </div>
                            
                            <div class="profile-stat-item">
                                <span class="profile-stat-value">{{ number_format((float)$avgRating, 1) }}</span>
                                <span class="profile-stat-label">Средний рейтинг</span>
                            </div>
                        </div>
                        @endif
                    </div>
                    
                    <!-- Режим редактирования для админа -->
                    @if(Auth::user()->status === 'admin')
                    <div class="edit-mode" style="display: none;">
                        <form id="admin-edit-form" data-user-id="{{ $target->id }}">
                            @csrf
                            <div class="form-group-profile">
                                <label for="admin-edit-name" class="form-label">ФИО</label>
                                <input type="text" id="admin-edit-name" name="name" class="form-control" value="{{ $target->name }}" required>
                            </div>
                            
                            <div class="form-group-profile">
                                <label for="admin-edit-email" class="form-label">Email</label>
                                <input type="email" id="admin-edit-email" name="email" class="form-control" value="{{ $target->email }}">
                            </div>
                            
                            <div class="form-group-profile">
                                <label for="admin-edit-phone" class="form-label">Телефон</label>
                                <input type="text" id="admin-edit-phone" name="phone" class="form-control maskphone" value="{{ $target->phone }}">
                            </div>
                            
                            <div class="form-group-profile">
                                <label for="admin-edit-status" class="form-label">Статус</label>
                                <select id="admin-edit-status" name="status" class="form-control">
                                    <option value="user" {{ $target->status === 'user' ? 'selected' : '' }}>Пользователь</option>
                                    <option value="partner" {{ $target->status === 'partner' ? 'selected' : '' }}>Партнер</option>
                                    <option value="coordinator" {{ $target->status === 'coordinator' ? 'selected' : '' }}>Координатор</option>
                                    <option value="architect" {{ $target->status === 'architect' ? 'selected' : '' }}>Архитектор</option>
                                    <option value="designer" {{ $target->status === 'designer' ? 'selected' : '' }}>Дизайнер</option>
                                    <option value="executor" {{ $target->status === 'executor' ? 'selected' : '' }}>Исполнитель</option>
                                    <option value="visualizer" {{ $target->status === 'visualizer' ? 'selected' : '' }}>Визуализатор</option>
                                    <option value="admin" {{ $target->status === 'admin' ? 'selected' : '' }}>Администратор</option>
                                </select>
                            </div>
                            
                            <div class="form-group-profile">
                                <label for="admin-edit-city" class="form-label">Город</label>
                                <input type="text" id="admin-edit-city" name="city" class="form-control" value="{{ $target->city }}">
                            </div>
                            
                            <div id="partner-fields" style="{{ $target->status === 'partner' ? '' : 'display: none;' }}">
                                <div class="form-group-profile">
                                    <label for="admin-edit-contract-number" class="form-label">Номер договора</label>
                                    <input type="text" id="admin-edit-contract-number" name="contract_number" class="form-control" 
                                           value="{{ $target->contract_number }}">
                                </div>
                                <div class="form-group-profile">
                                    <label for="admin-edit-comment" class="form-label">Комментарий</label>
                                    <textarea id="admin-edit-comment" name="comment" class="form-control" rows="3">{{ $target->comment }}</textarea>
                                </div>
                            </div>
                            
                            <div id="specialist-fields" style="{{ in_array($target->status, ['architect', 'designer', 'executor', 'visualizer']) ? '' : 'display: none;' }}">
                                <div class="form-group-profile">
                                    <label for="admin-edit-experience" class="form-label">Опыт работы (лет)</label>
                                    <input type="number" id="admin-edit-experience" name="experience" class="form-control" 
                                           value="{{ $target->experience }}" min="0" max="100">
                                </div>
                                <div class="form-group-profile">
                                    <label for="admin-edit-portfolio-link" class="form-label">Ссылка на портфолио</label>
                                    <input type="url" id="admin-edit-portfolio-link" name="portfolio_link" class="form-control" 
                                           value="{{ $target->portfolio_link }}">
                                </div>
                                <div class="form-group-profile">
                                    <label for="admin-edit-active-projects" class="form-label">Активные проекты</label>
                                    <input type="number" id="admin-edit-active-projects" name="active_projects_count" class="form-control" 
                                           value="{{ $target->active_projects_count }}" min="0">
                                </div>
                            </div>
                            
                            <div class="form-group-profile">
                                <label for="admin-edit-new-password" class="form-label">Новый пароль (оставьте пустым, чтобы не менять)</label>
                                <input type="password" id="admin-edit-new-password" name="new_password" class="form-control" placeholder="Введите новый пароль">
                            </div>
                            
                            <div class="form-footer">
                                <button type="button" id="cancel-edit" class="btn btn-secondary">Отменить</button>
                                <button type="submit" class="btn btn-primary">Сохранить изменения</button>
                            </div>
                            <div id="admin-edit-message" class="mt-3" style="display:none;"></div>
                        </form>
                    </div>
                    @endif
                </div>
            </div>
            
            <!-- Рейтинг (для специалистов, кроме партнеров) -->
            @if(in_array($target->status, ['architect', 'designer', 'executor', 'coordinator', 'visualizer']))
            <div class="profile-card profile-section" id="rating-section" style="display: none;">
                <div class="profile-card-header">
                    <h3 class="profile-card-title">Рейтинг и отзывы</h3>
                </div>
                <div class="profile-card-body">
                    @php
                        $averageRating = isset($target->averageRating) ? $target->averageRating : 0;
                        if (!$averageRating && isset($target->receivedRatings)) {
                            $averageRating = $target->receivedRatings()->avg('score') ?: 0;
                        }

                        // Проверяем, нужно ли показывать фейковый рейтинг
                        if(strtolower($viewer->status) === 'user' && in_array(strtolower($target->status), ['architect', 'designer', 'executor', 'visualizer'])) {
                            $averageRating = max(4.0, (float)$averageRating);
                        }
                        
                        $totalRatings = isset($target->receivedRatings) ? $target->receivedRatings()->count() : 0;
                    @endphp
                    
                    <div class="rating-summary">
                        <h4 class="rating-title">Средний рейтинг</h4>
                        <div class="rating-stats-container">
                            <div class="rating-stats-overall">
                                <div class="rating-big-score">{{ number_format($averageRating, 1) }}</div>
                                <div class="rating-stars">
                                    @for ($i = 1; $i <= 5; $i++)
                                        @if ($i <= floor($averageRating))
                                            <i class="fas fa-star"></i>
                                        @elseif ($i - 0.5 <= $averageRating)
                                            <i class="fas fa-star-half-alt"></i>
                                        @else
                                            <i class="far fa-star"></i>
                                        @endif
                                    @endfor
                                </div>
                                <div class="rating-count">на основе {{ $totalRatings }} {{ trans_choice('оценки|оценок|оценок', $totalRatings) }}</div>
                            </div>
                        </div>
                    </div>
                    
                    @if($totalRatings > 0)
                        <div class="rating-distribution">
                            <h4 class="rating-title">Распределение оценок</h4>
                            <div class="rating-distribution-wrapper">
                                <ul class="rating-bars">
                                    @php
                                        // Собираем статистику по каждой оценке
                                        $ratingStats = [];
                                        $highestCount = 0;
                                        
                                        for($star = 5; $star >= 1; $star--) {
                                            $ratingCount = isset($target->receivedRatings) ? $target->receivedRatings()->where('score', $star)->count() : 0;
                                            
                                            // Для обычного пользователя скрываем низкие оценки исполнителей
                                            if(strtolower($viewer->status) === 'user' && in_array(strtolower($target->status), ['architect', 'designer', 'executor', 'visualizer']) && $star < 4) {
                                                $ratingCount = 0;
                                            }
                                            
                                            $percentage = $totalRatings > 0 ? round(($ratingCount / $totalRatings) * 100) : 0;
                                            $ratingStats[$star] = [
                                                'count' => $ratingCount,
                                                'percentage' => $percentage
                                            ];
                                            
                                            if ($ratingCount > $highestCount) {
                                                $highestCount = $ratingCount;
                                            }
                                        }
                                    @endphp
                                    
                                    @for($star = 5; $star >= 1; $star--)
                                        @php
                                            $ratingCount = $ratingStats[$star]['count'];
                                            $percentage = $ratingStats[$star]['percentage'];
                                            // Определяем класс для подсветки самой частой оценки
                                            $highlightClass = $ratingCount == $highestCount && $ratingCount > 0 ? 'most-common' : '';
                                            
                                            // Проверяем, нужно ли использовать фейковые данные для оценок ниже 4
                                            $needFakeData = strtolower($viewer->status) === 'user' && 
                                                in_array(strtolower($target->status), ['architect', 'designer', 'executor', 'visualizer']) && 
                                                $star < 4 &&
                                                $averageRating < 4;
                                            
                                            // Генерируем фейковые данные, если нужно
                                            if ($needFakeData) {
                                                if ($star === 4) {
                                                    // Для оценки 4 используем большее значение, чтобы визуально она была главной
                                                    $fakePercentage = rand(55, 75);
                                                    $fakeCount = ceil($totalRatings * $fakePercentage / 100);
                                                    $percentage = $fakePercentage;
                                                    $ratingCount = $fakeCount;
                                                    $highlightClass = 'most-common'; // Всегда подсвечиваем оценку 4
                                                } else if ($star === 5) {
                                                    // Для оценки 5 используем среднее значение
                                                    $fakePercentage = rand(20, 35);
                                                    $fakeCount = ceil($totalRatings * $fakePercentage / 100);
                                                    $percentage = $fakePercentage;
                                                    $ratingCount = $fakeCount;
                                                } else {
                                                    // Для оценок ниже 4 используем минимальное значение
                                                    $fakePercentage = rand(2, 8);
                                                    $fakeCount = ceil($totalRatings * $fakePercentage / 100);
                                                    $percentage = $fakePercentage;
                                                    $ratingCount = $fakeCount;
                                                }
                                            }
                                        @endphp
                                        
                                        <li class="rating-bar {{ $highlightClass }}">
                                            <span class="star-label">{{ $star }} <i class="fas fa-star"></i></span>
                                            <div class="progress-container">
                                                <div class="progress-fill" style="width: {{ $percentage }}%"></div>
                                            </div>
                                            <div class="rating-details">
                                                <span class="star-count">{{ $ratingCount }}</span>
                                                <span class="star-percent">({{ $percentage }}%)</span>
                                            </div>
                                        </li>
                                    @endfor
                                </ul>
                            </div>
                            
                            @php
                                // Получаем несколько последних отзывов с комментариями
                                $latestReviews = isset($target->receivedRatings) ? 
                                    $target->receivedRatings()
                                        ->whereNotNull('comment')
                                        ->with('raterUser')
                                        ->orderBy('created_at', 'desc')
                                        ->take(3)
                                        ->get() : 
                                    collect([]);
                                    
                                // Для обычных пользователей фильтруем отзывы, оставляя только с высоким рейтингом
                                if(strtolower($viewer->status) === 'user' && in_array(strtolower($target->status), ['architect', 'designer', 'executor', 'visualizer'])) {
                                    $latestReviews = $latestReviews->filter(function($review) {
                                        return $review->score >= 4;
                                    });
                                }
                            @endphp
                            
                            @if($latestReviews->count() > 0 && in_array(strtolower($viewer->status), ['admin', 'partner', 'coordinator']))
                                <h4 class="rating-title mt-4">Последние отзывы</h4>
                                <div class="rating-comments">
                                    @foreach($latestReviews as $review)
                                        <div class="rating-comment">
                                            <div class="comment-header">
                                                <div class="comment-stars">
                                                    @for ($i = 1; $i <= 5; $i++)
                                                        @if ($i <= $review->score)
                                                            <i class="fas fa-star"></i>
                                                        @else
                                                            <i class="far fa-star"></i>
                                                        @endif
                                                    @endfor
                                                </div>
                                                <div class="comment-author">
                                                    {{ $review->raterUser ? $review->raterUser->name : 'Пользователь' }}
                                                </div>
                                                <div class="comment-date">
                                                    {{ $review->created_at->format('d.m.Y') }}
                                                </div>
                                            </div>
                                            <div class="comment-text">
                                                {{ $review->comment }}
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    @else
                        <p class="no-ratings">У пользователя пока нет оценок</p>
                    @endif
                </div>
            </div>
            @endif

            <!-- Добавляем секцию наград в content area -->
            @if($target->status !== 'user')
            <div class="profile-card profile-section" id="awards-section" style="display: none;">
                <div class="profile-card-header">
                    <h3 class="profile-card-title">Награды пользователя</h3>
                    
                    @if(Auth::user()->status === 'admin')
                    <div class="header-actions">
                        <a href="{{ route('admin.awards.user.form', $target->id) }}" class="btn btn-sm btn-primary">
                            <i class="fas fa-plus"></i> Выдать награду
                        </a>
                    </div>
                    @endif
                </div>
                
                <div class="profile-card-body">
                    @if($target->awards->count() > 0)
                        <div class="awards-grid">
                            @foreach($target->awards as $award)
                                <div class="award-item">
                                    <div class="award-icon">
                                        {!! $award->icon !!}
                                    </div>
                                    <div class="award-details">
                                        <h4 class="award-name">{{ $award->name }}</h4>
                                        <p class="award-description">{{ $award->description }}</p>
                                        <div class="award-meta">
                                            <div class="award-date">
                                                <i class="far fa-calendar-alt"></i> Получена: {{ \Carbon\Carbon::parse($award->pivot->awarded_at)->format('d.m.Y') }}
                                            </div>
                                            @if(Auth::user()->status === 'admin')
                                                <div class="award-actions">
                                                    <form action="{{ route('admin.awards.user.revoke', [$target->id, $award->id]) }}" method="POST" onsubmit="return confirm('Вы уверены, что хотите отозвать эту награду?')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-danger">
                                                            <i class="fas fa-trash"></i> Отозвать
                                                        </button>
                                                    </form>
                                                </div>
                                            @endif
                                        </div>
                                        @if($award->pivot->comment)
                                            <div class="award-comment">
                                                <i class="far fa-comment"></i> {{ $award->pivot->comment }}
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="no-awards">У пользователя пока нет наград</p>
                    @endif
                </div>
            </div>
            @endif
            
            <!-- Секция администрирования для админов -->
            @if(Auth::user()->status === 'admin')
            <div class="profile-card profile-section" id="admin-section" style="display: none;">
                <div class="profile-card-header">
                    <h3 class="profile-card-title">Управление пользователем</h3>
                </div>
                <div class="profile-card-body">
                    <div class="admin-actions">
                        <div class="admin-action-group">
                            <h4>История действий</h4>
                            <p>Последние действия пользователя</p>
                            <div class="admin-action-buttons">
                                <a href="{{ route('admin.deals') }}?user_id={{ $target->id }}" class="btn btn-info">
                                    <i class="fas fa-history"></i> История сделок
                                </a>
                                <a href="{{ route('admin.user.briefs', $target->id) }}" class="btn btn-info">
                                    <i class="fas fa-file-alt"></i> Просмотр брифов
                                </a>
                            </div>
                        </div>
                        
                        <div class="admin-action-group">
                            <h4>Действия с аккаунтом</h4>
                            <p>Управление аккаунтом пользователя</p>
                            <div class="admin-action-buttons">
                                <button class="btn btn-warning" id="admin-reset-password">
                                    <i class="fas fa-key"></i> Сбросить пароль
                                </button>
                                <button class="btn btn-danger" id="admin-lock-account">
                                    <i class="fas fa-lock"></i> Заблокировать аккаунт
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Переключение между разделами профиля
    const menuLinks = document.querySelectorAll('.profile-menu-link');
    const sections = document.querySelectorAll('.profile-section');
    
    menuLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            
            // Удаляем активный класс у всех пунктов меню
            menuLinks.forEach(item => {
                item.classList.remove('active');
            });
            
            // Добавляем активный класс текущему пункту
            this.classList.add('active');
            
            // Скрываем все секции
            sections.forEach(section => {
                section.style.display = 'none';
            });
            
            // Показываем нужную секцию
            const targetSection = document.getElementById(this.getAttribute('data-section'));
            targetSection.style.display = 'block';
        });
    });
    
    // Добавляем функцию для форматирования телефона при отображении
    function formatPhoneNumber(phoneNumberString) {
        const cleaned = ('' + phoneNumberString).replace(/\D/g, '');
        if (cleaned.length < 11) return phoneNumberString;
        
        const match = cleaned.match(/^(\d{1})(\d{3})(\d{3})(\d{2})(\d{2})$/);
        if (match) {
            return '+' + match[1] + ' (' + match[2] + ') ' + match[3] + '-' + match[4] + '-' + match[5];
        }
        return phoneNumberString;
    }
    
    // Форматируем телефонные номера на странице
    document.querySelectorAll('.profile-info-value').forEach(element => {
        const text = element.textContent.trim();
        if (/^\+?\d{11}$/.test(text.replace(/\D/g, ''))) {
            element.textContent = formatPhoneNumber(text);
        }
    });

    // Updated tooltip initialization
    if (typeof $().tooltip === 'function') {
        $('[title]').tooltip({
            placement: 'auto',
            trigger: 'hover',
            delay: {show: 1500, hide: 0}, // Changed to 1.5 seconds with no hide delay
            animation: false, // Disable animations
            container: 'body', // Ensure proper positioning
            template: '<div class="tooltip custom-tooltip" role="tooltip"><div class="tooltip-arrow"></div><div class="tooltip-inner"></div></div>'
        });
    }
    
    // Код для администратора - переключение между режимами просмотра и редактирования
    const toggleEditBtn = document.getElementById('toggle-edit-mode');
    const cancelEditBtn = document.getElementById('cancel-edit');
    
    if (toggleEditBtn && cancelEditBtn) {
        toggleEditBtn.addEventListener('click', function() {
            const viewMode = document.querySelector('.view-mode');
            const editMode = document.querySelector('.edit-mode');
            
            if (viewMode && editMode) {
                if (viewMode.style.display !== 'none') {
                    viewMode.style.display = 'none';
                    editMode.style.display = 'block';
                    toggleEditBtn.innerHTML = '<i class="fas fa-eye"></i> Вернуться к просмотру';
                    toggleEditBtn.classList.replace('btn-primary', 'btn-secondary');
                } else {
                    viewMode.style.display = 'block';
                    editMode.style.display = 'none';
                    toggleEditBtn.innerHTML = '<i class="fas fa-edit"></i> Редактировать профиль';
                    toggleEditBtn.classList.replace('btn-secondary', 'btn-primary');
                }
            }
        });
        
        cancelEditBtn.addEventListener('click', function() {
            const viewMode = document.querySelector('.view-mode');
            const editMode = document.querySelector('.edit-mode');
            
            if (viewMode && editMode) {
                viewMode.style.display = 'block';
                editMode.style.display = 'none';
                toggleEditBtn.innerHTML = '<i class="fas fa-edit"></i> Редактировать профиль';
                toggleEditBtn.classList.replace('btn-secondary', 'btn-primary');
            }
        });
    }
    
    // Обработчик изменения статуса пользователя для показа/скрытия соответствующих полей
    const statusSelect = document.getElementById('admin-edit-status');
    if (statusSelect) {
        statusSelect.addEventListener('change', function() {
            const partnerFields = document.getElementById('partner-fields');
            const specialistFields = document.getElementById('specialist-fields');
            
            if (partnerFields && specialistFields) {
                // Скрываем все поля по умолчанию
                partnerFields.style.display = 'none';
                specialistFields.style.display = 'none';
                
                // Показываем соответствующие поля в зависимости от выбранного статуса
                if (this.value === 'partner') {
                    partnerFields.style.display = 'block';
                } else if (['architect', 'designer', 'executor', 'visualizer'].includes(this.value)) {
                    specialistFields.style.display = 'block';
                }
            }
        });
    }
    
    // Обработчик отправки формы редактирования для администратора
    const adminEditForm = document.getElementById('admin-edit-form');
    if (adminEditForm) {
        adminEditForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const userId = this.getAttribute('data-user-id');
            const formData = new FormData(this);
            const messageElement = document.getElementById('admin-edit-message');
            
            // Получаем CSRF-токен
            const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            
            // Отправляем запрос на сервер
            fetch(`/admin/profile/update/${userId}`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': token,
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (messageElement) {
                    messageElement.style.display = 'block';
                    
                    if (data.success) {
                        messageElement.className = 'alert alert-success';
                        messageElement.textContent = data.message || 'Профиль успешно обновлен';
                        
                        // Перезагружаем страницу через 1 секунду
                        setTimeout(() => {
                            window.location.reload();
                        }, 1000);
                    } else {
                        messageElement.className = 'alert alert-danger';
                        messageElement.textContent = data.message || 'Ошибка при обновлении профиля';
                    }
                }
            })
            .catch(error => {
                if (messageElement) {
                    messageElement.style.display = 'block';
                    messageElement.className = 'alert alert-danger';
                    messageElement.textContent = 'Произошла ошибка при обработке запроса: ' + error.message;
                }
                console.error('Error:', error);
            });
        });
    }
    
    // Обработчики для кнопок в секции администрирования
    const resetPasswordBtn = document.getElementById('admin-reset-password');
    const lockAccountBtn = document.getElementById('admin-lock-account');
    
    if (resetPasswordBtn) {
        resetPasswordBtn.addEventListener('click', function() {
            const userId = adminEditForm.getAttribute('data-user-id');
            
            if (confirm('Вы уверены, что хотите сбросить пароль этого пользователя? Будет сгенерирован новый пароль и отправлен на почту пользователя.')) {
                // Здесь можно добавить код для сброса пароля
                console.log('Сброс пароля для пользователя ID:', userId);
                
                // В реальной реализации здесь должен быть запрос к API для сброса пароля
            }
        });
    }
    
    if (lockAccountBtn) {
        lockAccountBtn.addEventListener('click', function() {
            const userId = adminEditForm.getAttribute('data-user-id');
            
            if (confirm('Вы уверены, что хотите заблокировать этого пользователя? Он не сможет войти в систему до разблокировки.')) {
                // Здесь можно добавить код для блокировки аккаунта
                console.log('Блокировка аккаунта пользователя ID:', userId);
                
                // В реальной реализации здесь должен быть запрос к API для блокировки аккаунта
            }
        });
    }
    
    // Улучшенная маска для телефона с автоформатированием
    function maskPhone(event) {
        const blank = "+_ (___) ___-__-__";
        let i = 0;
        const val = this.value.replace(/\D/g, "").replace(/^8/, "7").replace(/^9/, "79");
        
        this.value = blank.replace(/./g, function (char) {
            if (/[_\d]/.test(char) && i < val.length) return val.charAt(i++);
            return i >= val.length ? "" : char;
        });
        
        if (event.type == "blur" && this.value.length <= 4) {
            this.value = "";
        }
    }
    
    // Применяем маску для всех полей телефона
    document.querySelectorAll('.maskphone').forEach(input => {
        input.addEventListener('input', maskPhone);
        input.addEventListener('focus', maskPhone);
        input.addEventListener('blur', maskPhone);
    });

    // Инициализируем всплывающие подсказки для наград с улучшенным форматированием
    if (typeof $().tooltip === 'function') {
        $('.award-icon').tooltip({
            placement: 'auto',
            delay: {show: 500, hide: 100},
            html: true,
            title: function() {
                const awardName = $(this).attr('title').split(':')[0];
                const awardDesc = $(this).attr('title').split(':')[1];
                const awardDate = $(this).data('award-date');
                
                return `<div class="award-tooltip">
                    <div class="award-tooltip-title">${awardName}</div>
                    <div class="award-tooltip-desc">${awardDesc}</div>
                    <div class="award-tooltip-date">Получена: ${awardDate}</div>
                </div>`;
            }
        });
    }
});
</script>

<style>

</style>

