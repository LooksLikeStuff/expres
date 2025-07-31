<div class="profile-container">
    <div class="profile-header">
        <h1 class="profile-title">Личный профиль</h1>
        <p class="profile-subtitle">Управление персональными данными и настройками аккаунта</p>
    </div>

    <div class="profile-grid">
        <div class="profile-sidebar">
            <div class="profile-user">
                <div class="profile-avatar-wrapper">
                    <img src="{{ $user->avatar_url ?? asset('storage/avatars/group_default.svg') }}" alt="Аватар" class="profile-avatar">
                    <div class="avatar-overlay">Изменить фото</div>
                    <form id="update-avatar-form" action="{{ route('profile.avatar.update') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <input type="file" class="avatar-input" name="avatar" id="avatar-upload" accept="image/*">
                    </form>
                </div>
                   @if($user->status !== 'user' && $user->awards->count() > 0)
                <div class="profile-awards">
                    @foreach($user->awards->sortByDesc('pivot.awarded_at')->take(10) as $award)
                    <div class="award-icon" title="{{ $award->name }}: {{ $award->description }}" data-award-date="{{ is_string($award->pivot->awarded_at) ? \Carbon\Carbon::parse($award->pivot->awarded_at)->format('d.m.Y') : $award->pivot->awarded_at->format('d.m.Y') }}">
                        {!! $award->icon !!}
                    </div>
                    @endforeach
                </div>
                @endif
                <h2 class="profile-name">{{ $user->name }}</h2>
                <div class="profile-status">{{ ucfirst($user->status) ?? 'Пользователь' }}</div>
                
                <p class="profile-join-date">
                    <i class="fas fa-calendar-alt"></i> 
                    На сайте с {{ $user->created_at->format('d.m.Y') }}
                </p>
            </div>
            
            <ul class="profile-menu">
                <li class="profile-menu-item">
                    <a href="#personal" class="profile-menu-link active" data-section="personal-section">
                        <span class="profile-menu-icon"><i class="fas fa-user"></i></span>
                        Личная информация
                    </a>
                </li>
                <li class="profile-menu-item">
                    <a href="#security" class="profile-menu-link" data-section="security-section">
                        <span class="profile-menu-icon"><i class="fas fa-lock"></i></span>
                        Безопасность
                    </a>
                </li>
                <li class="profile-menu-item">
                    <a href="#phone" class="profile-menu-link" data-section="phone-section">
                        <span class="profile-menu-icon"><i class="fas fa-phone"></i></span>
                        Сменить телефон
                    </a>
                </li>
                @if(in_array($user->status, ['partner', 'architect', 'designer', 'executor', 'coordinator']))
                <li class="profile-menu-item">
                    <a href="#rating" class="profile-menu-link" data-section="rating-section">
                        <span class="profile-menu-icon"><i class="fas fa-star"></i></span>
                        Рейтинг и отзывы
                    </a>
                </li>
                @endif
            </ul>
            
            <div class="profile-actions">
                <a href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();" class="btn btn-secondary btn-block">
                    <i class="fas fa-sign-out-alt"></i> Выйти из аккаунта
                </a>
                <button type="button" class="btn btn-danger btn-block" id="open-delete-modal">
                    <i class="fas fa-trash-alt"></i> Удалить аккаунт
                </button>
            </div>
        </div>
        
        <!-- Правая панель профиля -->
        <div class="profile-content">
            <!-- Персональная информация -->
            <div class="profile-card profile-section active" id="personal-section">
                <div class="profile-card-header">
                    <h3 class="profile-card-title">Личная информация</h3>
                </div>
                <div class="profile-card-body">
                    @if (session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif
                    
                    <form id="update-profile-form">
                        @csrf
                        <div class="form-row">
                            <div class="form-column">
                                <div class="form-group-profile">
                                    <label class="form-label" for="name">Имя и фамилия</label>
                                    <input type="text" class="form-control" id="name" name="name" value="{{ $user->name }}" 
                                           placeholder="Введите имя и фамилию" maxlength="100" required>
                                </div>
                            </div>
                            <div class="form-column">
                                <div class="form-group-profile">
                                    <label class="form-label" for="email">Электронная почта</label>
                                    <input type="email" class="form-control" id="email" name="email" value="{{ $user->email }}" 
                                           placeholder="example@domain.com" required>
                                </div>
                            </div>
                        </div>
                        
                        @if($user->status == 'user')
                            <div class="form-group-profile">
                                <label class="form-label" for="city">Город</label>
                                <input type="text" class="form-control" id="city" name="city" value="{{ $user->city }}" 
                                       placeholder="Введите название города" maxlength="50">
                            </div>
                        @elseif($user->status == 'partner')
                            <div class="form-row">
                                <div class="form-column">
                                    <div class="form-group-profile">
                                        <label class="form-label" for="city">Город</label>
                                        <input type="text" class="form-control" id="city" name="city" value="{{ $user->city }}" 
                                               placeholder="Введите название города" maxlength="50">
                                    </div>
                                </div>
                                <div class="form-column">
                                    <div class="form-group-profile">
                                        <label class="form-label" for="contract_number">Номер договора</label>
                                        <input type="text" class="form-control" id="contract_number" name="contract_number" 
                                               value="{{ $user->contract_number }}" placeholder="Например: A-12345" maxlength="20" 
                                               pattern="[A-Za-z0-9\-\/]+">
                                    </div>
                                </div>
                            </div>
                            <div class="form-group-profile">
                                <label class="form-label" for="comment">Комментарий</label>
                                <textarea class="form-control form-textarea" id="comment" name="comment" 
                                          placeholder="Введите дополнительную информацию" maxlength="500">{{ $user->comment }}</textarea>
                            </div>
                        @elseif(in_array($user->status, ['executor', 'architect', 'designer']))
                            <div class="form-row">
                                <div class="form-column">
                                    <div class="form-group-profile">
                                        <label class="form-label" for="city">Город</label>
                                        <input type="text" class="form-control" id="city" name="city" value="{{ $user->city }}" 
                                               placeholder="Москва (UTC+3)" maxlength="50">
                                    </div>
                                </div>
                                <div class="form-column">
                                    <div class="form-group-profile">
                                        <label class="form-label" for="experience">Стаж работы</label>
                                        <input type="number" class="form-control" id="experience" name="experience" 
                                               value="{{ $user->experience }}" placeholder="Например: 5" min="0" max="100" step="1">
                                        
                                    </div>
                                </div>
                                {{-- <div class="form-group-profile">
                                    <label class="form-label" for="portfolio_link">Ссылка на портфолио</label>
                                    <input type="url" class="form-control" id="portfolio_link" name="portfolio_link" 
                                           value="{{ $user->portfolio_link }}" placeholder="https://example.com/portfolio" 
                                           pattern="https?://.+">
                                </div> --}}
                                {{-- <div class="form-group-profile">
                                    <label class="form-label" for="active_projects_count">Проекты в работе</label>
                                    <input type="number" class="form-control" id="active_projects_count" name="active_projects_count" 
                                           value="{{ $user->active_projects_count }}" min="0" max="100" placeholder="0">
                                </div> --}}
                            </div>
                           
                        @elseif($user->status == 'coordinator')
                            <div class="form-row">
                                <div class="form-column">
                                    <div class="form-group-profile">
                                        <label class="form-label" for="experience">Стаж работы</label>
                                        <input type="number" class="form-control" id="experience" name="experience" 
                                               value="{{ $user->experience }}" placeholder="Например: 5" min="0" max="100" step="1">
                                        <small class="form-text text-muted">Укажите количество лет опыта (число от 0 до 100)</small>
                                    </div>
                                </div>
                            </div>
                        @endif
                        
                        <div class="form-footer">
                            <button type="button" class=" btn-secondary">Отменить</button>
                            <button type="submit" class=" btn-primary">Сохранить изменения</button>
                        </div>
                        <div id="profile-update-message" style="display: none;"></div>
                    </form>
                </div>
            </div>
            
            <div class="profile-card profile-section" id="security-section" style="display: none;">
                <div class="profile-card-header">
                    <h3 class="profile-card-title">Безопасность</h3>
                </div>
                <div class="profile-card-body">
                    <form id="password-change-form">
                        @csrf
                        <div class="form-group-profile">
                            <label class="form-label" for="new_password">Новый пароль</label>
                            <input type="password" class="form-control" id="new_password" name="new_password" 
                                   minlength="8" maxlength="64" 
                                   placeholder="Минимум 8 символов, включая буквы и цифры" 
                                   pattern="(?=.*\d)(?=.*[a-zA-Z]).{8,}" required>
                          
                        </div>
                        <div class="form-group-profile" style="margin-top: 15px">
                            <label class="form-label" for="new_password_confirmation">Подтверждение пароля</label>
                            <input type="password" class="form-control" id="new_password_confirmation" name="new_password_confirmation" 
                                   minlength="8" maxlength="64" 
                                   placeholder="Повторите новый пароль" required>
                        </div>
                        <div class="form-footer">
                            <button type="button" class=" btn-secondary">Отменить</button>
                            <button type="submit" class=" btn-primary">Изменить пароль</button>
                        </div>
                        <div id="password-change-message" style="display: none;"></div>
                    </form>
                </div>
            </div>
            
            <div class="profile-card profile-section" id="phone-section" style="display: none;">
                <div class="profile-card-header">
                    <h3 class="profile-card-title">Смена номера телефона</h3>
                </div>
                <div class="profile-card-body">
                    <p>Текущий номер: <strong>{{ $user->phone ?: 'Не указан' }}</strong></p>
                    
                    <form id="phone-change-form">
                        <div class="form-group-profile">
                            <label class="form-label" for="new-phone">Новый номер телефона</label>
                            <input type="text" class="form-control maskphone" id="new-phone" name="new-phone" 
                                   placeholder="+7 (___) ___-__-__" required>
                        </div>
                        <div class="form-footer">
                            <button type="button" class=" btn-secondary">Отменить</button>
                            <button type="button" id="send-code-btn" class=" btn-primary">Отправить код подтверждения</button>
                        </div>
                    </form>
                    
                    <div class="verification-section" id="verification-section">
                        <form id="verification-form">
                            <div class="form-group-profile">
                             <h3 style=" text-align: center;width: 100%;  align-items: center;
    align-content: center;
    justify-content: center;">Введите код подтверждения</h3>
                                <div class="verification-code-container">
                                    <input type="text" class="form-control verification-code-input" id="verification-code-1" 
                                           maxlength="1" pattern="[0-9]" inputmode="numeric" placeholder="*" required>
                                    <input type="text" class="form-control verification-code-input" id="verification-code-2" 
                                           maxlength="1" pattern="[0-9]" inputmode="numeric" placeholder="*" required>
                                    <input type="text" class="form-control verification-code-input" id="verification-code-3" 
                                           maxlength="1" pattern="[0-9]" inputmode="numeric" placeholder="*" required>
                                    <input type="text" class="form-control verification-code-input" id="verification-code-4" 
                                           maxlength="1" pattern="[0-9]" inputmode="numeric" placeholder="*" required>
                                    <input type="hidden" id="verification-code" name="verification-code" required>
                                </div>
                                <p class="help-text">Код отправлен на указанный номер телефона</p>
                            </div>
                            <div class="form-footer-verification-form">
                                <span class="resend-code" id="resend-code">Отправить код повторно</span>
                                <button type="button" id="verify-code-btn" class=" btn-primary">Подтвердить</button>
                            </div>
                        </form>
                    </div>
                    
                    <div id="phone-change-message" style="display: none;"></div>
                </div>
            </div>
            
            <!-- Рейтинг (для специалистов, кроме партнеров) -->
            @if(in_array($user->status, ['architect', 'designer', 'executor', 'coordinator', 'visualizer']))
            <div class="profile-card profile-section" id="rating-section" style="display: none;">
                <div class="profile-card-header">
                    <h3 class="profile-card-title">Рейтинг и отзывы</h3>
                </div>
                <div class="profile-card-body">
                    @php
                        $averageRating = isset($user->averageRating) ? $user->averageRating : 0;
                        if (!$averageRating && isset($user->receivedRatings)) {
                            $averageRating = $user->receivedRatings()->avg('score') ?: 0;
                        }
                        $totalRatings = isset($user->receivedRatings) ? $user->receivedRatings()->count() : 0;
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
                                            $ratingCount = isset($user->receivedRatings) ? $user->receivedRatings()->where('score', $star)->count() : 0;
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
                                $latestReviews = isset($user->receivedRatings) ? 
                                    $user->receivedRatings()
                                        ->whereNotNull('comment')
                                        ->with('raterUser', 'deal')
                                        ->orderBy('created_at', 'desc')
                                        ->take(5)
                                        ->get() : 
                                    collect([]);
                                    
                                // Проверяем, нужно ли показывать комментарии
                                $showComments = !in_array(strtolower($user->status), ['architect', 'designer', 'executor', 'visualizer']);
                            @endphp
                            
                            @if($latestReviews->count() > 0 && $showComments)
                                <h4 class="rating-title mt-4">Последние отзывы о вас</h4>
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
                                            @if($review->deal)
                                                <div class="comment-project">
                                                    Проект: {{ $review->deal->name ?: 'Проект #'.$review->deal->id }}
                                                </div>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            @elseif(!$showComments && $totalRatings > 0)
                                
                            @endif
                        </div>
                    @else
                        <p class="no-ratings">У вас пока нет оценок от клиентов</p>
                    @endif
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

<!-- Скрытые формы -->
<form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
    @csrf
</form>

<!-- Кастомное модальное окно удаления аккаунта -->
<div class="custom-modal-overlay" id="delete-account-overlay">
    <div class="custom-modal">
        <div class="custom-modal-header">
            <h3><i class="fas fa-exclamation-triangle"></i> Удаление аккаунта</h3>
            <button type="button" class="custom-modal-close" id="close-delete-modal">&times;</button>
        </div>
        
        <div class="custom-modal-body">
            <div class="warning-icon">
                <i class="fas fa-exclamation-circle"></i>
            </div>
            
            <div class="delete-account-warning">
                <p class="warning-title">Вы уверены, что хотите удалить свой аккаунт?</p>
                <p class="warning-message">Это действие нельзя отменить. При удалении аккаунта:</p>
                <ul class="warning-list">
                    <li>Ваши брифы и сделки будут сохранены в системе</li>
                    <li>Вы не сможете больше войти в систему с текущими данными</li>
                    <li>Для возобновления работы потребуется создать новый аккаунт</li>
                </ul>
            </div>
            
            <form id="delete-account-form" action="{{ route('delete_account') }}" method="POST">
                @csrf
                <!-- Скрытое поле для передачи подтверждения без запроса пароля -->
                <input type="hidden" name="password" value="confirmed">
            </form>
        </div>
        
        <div class="custom-modal-footer">
            <div class="modal-buttons">
                <button type="button" class="btn-cancel" id="cancel-deletion">Отменить</button>
                <button type="button" class="btn-danger" id="confirm-delete-btn">Удалить аккаунт</button>
            </div>
        </div>
    </div>
</div>

<!-- Добавляем стили для модального окна -->
<style>
/* Стили для модального окна */
.custom-modal-overlay {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-color: rgba(0, 0, 0, 0.5);
    z-index: 1050;
    align-items: center;
    justify-content: center;
    overflow-y: auto;
    padding: 20px;
    opacity: 0;
    transition: opacity 0.3s ease;
}

.custom-modal-overlay.active {
    display: flex;
    opacity: 1;
}

.custom-modal {
    background-color: #fff;
    border-radius: 8px;
    box-shadow: 0 4px 25px rgba(0, 0, 0, 0.25);
    width: 100%;
    max-width: 500px;
    position: relative;
    transform: translateY(-20px);
    transition: transform 0.3s ease;
    max-height: 90vh;
    display: flex;
    flex-direction: column;
}

.custom-modal-overlay.active .custom-modal {
    transform: translateY(0);
}

.custom-modal-header {
    padding: 20px;
    border-bottom: 1px solid #f5c6cb;
    background-color: #f8d7da;
    color: #721c24;
    border-top-left-radius: 8px;
    border-top-right-radius: 8px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.custom-modal-header h3 {
    margin: 0;
    font-size: 18px;
    font-weight: 600;
    display: flex;
    align-items: center;
}

.custom-modal-header h3 i {
    margin-right: 10px;
    font-size: 20px;
}

.custom-modal-close {
    background: none;
    border: none;
    font-size: 22px;
    color: #721c24;
    cursor: pointer;
    padding: 0;
    width: 30px;
    height: 30px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: background-color 0.2s;
}

.custom-modal-close:hover {
    background-color: rgba(114, 28, 36, 0.1);
}

.custom-modal-body {
    padding: 20px;
    overflow-y: auto;
}

.warning-icon {
    text-align: center;
    margin-bottom: 20px;
}

.warning-icon i {
    font-size: 64px;
    color: #dc3545;
    opacity: 0.8;
}

.delete-account-warning {
    margin-bottom: 20px;
}

.warning-title {
    font-size: 18px;
    font-weight: 600;
    color: #dc3545;
    margin-bottom: 10px;
}

.warning-message {
    font-size: 15px;
    margin-bottom: 10px;
    color: #333;
}

.warning-list {
    padding-left: 20px;
    color: #555;
    font-size: 14px;
    margin-bottom: 20px;
}

.warning-list li {
    margin-bottom: 8px;
}

.form-group {
    margin-bottom: 20px;
}

.control-label {
    display: block;
    margin-bottom: 8px;
    font-weight: 500;
    color: #333;
}

.control-label i {
    margin-right: 5px;
    color: #6c757d;
}

.password-input-wrapper {
    position: relative;
}

.toggle-password {
    position: absolute;
    right: 10px;
    top: 50%;
    transform: translateY(-50%);
    background: none;
    border: none;
    color: #6c757d;
    cursor: pointer;
}

.password-error {
    color: #dc3545;
    font-size: 13px;
    margin-top: 5px;
    height: 20px;
}

.form-confirmation {
    margin-bottom: 10px;
}

.confirmation-checkbox {
    display: flex;
    align-items: center;
}

.confirmation-checkbox input {
    margin-right: 8px;
}

.custom-modal-footer {
    padding: 15px 20px;
    border-top: 1px solid #f5c6cb;
    background-color: #f8f9fa;
    border-bottom-left-radius: 8px;
    border-bottom-right-radius: 8px;
}

.modal-buttons {
    display: flex;
    justify-content: flex-end;
    gap: 10px;
}

.btn-cancel {
    padding: 8px 16px;
    background-color: #f8f9fa;
    border: 1px solid #ccc;
    border-radius: 4px;
    color: #333;
    font-size: 14px;
    cursor: pointer;
    transition: all 0.2s;
}

.btn-cancel:hover {
    background-color: #e2e6ea;
    border-color: #dae0e5;
}

.btn-danger {
    padding: 8px 16px;
    background-color: #dc3545;
    border: 1px solid #dc3545;
    border-radius: 4px;
    color: #fff;
    font-size: 14px;
    cursor: pointer;
    transition: all 0.2s;
}

.btn-danger:hover {
    background-color: #c82333;
    border-color: #bd2130;
}

.btn-danger:disabled {
    background-color: #f1aeb5;
    border-color: #f1aeb5;
    cursor: not-allowed;
    opacity: 0.65;
}

/* Дополнительная анимация для модального окна */
@keyframes shake {
    0%, 100% { transform: translateX(0); }
    10%, 30%, 50%, 70%, 90% { transform: translateX(-5px); }
    20%, 40%, 60%, 80% { transform: translateX(5px); }
}

.shake {
    animation: shake 0.8s cubic-bezier(.36,.07,.19,.97) both;
}
</style>

<!-- Добавляем JavaScript для работы с модальным окном -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Элементы модального окна
    const overlay = document.getElementById('delete-account-overlay');
    const modal = overlay.querySelector('.custom-modal');
    const openModalBtn = document.getElementById('open-delete-modal');
    const closeModalBtn = document.getElementById('close-delete-modal');
    const cancelBtn = document.getElementById('cancel-deletion');
    const confirmBtn = document.getElementById('confirm-delete-btn');
    const deleteAccountForm = document.getElementById('delete-account-form');
    
    // Функции для открытия и закрытия модального окна
    function openModal() {
        overlay.classList.add('active');
        document.body.style.overflow = 'hidden'; // Блокируем прокрутку страницы
    }
    
    function closeModal() {
        overlay.classList.remove('active');
        document.body.style.overflow = ''; // Разблокируем прокрутку
    }
    
    // Обработчики событий для открытия/закрытия модального окна
    openModalBtn.addEventListener('click', openModal);
    closeModalBtn.addEventListener('click', closeModal);
    cancelBtn.addEventListener('click', closeModal);
    
    // Закрытие при клике вне модального окна
    overlay.addEventListener('click', function(e) {
        if (e.target === overlay) {
            closeModal();
        }
    });
    
    // Закрытие по клавише Escape
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && overlay.classList.contains('active')) {
            closeModal();
        }
    });
    
    // Обработка нажатия на кнопку удаления
    confirmBtn.addEventListener('click', function() {
        // Отправляем форму напрямую без проверок
        deleteAccountForm.submit();
    });
});
</script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM загружен, инициализация скриптов');
    
    // Переключение между разделами профиля
    const menuLinks = document.querySelectorAll('.profile-menu-link');
    console.log('Найдено элементов меню:', menuLinks.length);
    
    const sections = document.querySelectorAll('.profile-section');
    console.log('Найдено секций:', sections.length);
    
    // Назначаем обработчик событий непосредственно на родительский UL элемент для использования делегирования событий
    const menuContainer = document.querySelector('.profile-menu');
    if (menuContainer) {
        console.log('Найден контейнер меню');
        menuContainer.addEventListener('click', function(e) {
            // Находим ближайший элемент .profile-menu-link от места клика
            const link = e.target.closest('.profile-menu-link');
            if (!link) return; // Клик был не по ссылке
            
            e.preventDefault(); // Предотвращаем стандартное поведение
            console.log('Клик по элементу меню:', link.getAttribute('href'));
            
            // Удаляем активный класс у всех ссылок
            menuLinks.forEach(item => {
                item.classList.remove('active');
            });
            
            // Добавляем активный класс текущей ссылке
            link.classList.add('active');
            
            // Скрываем все секции
            sections.forEach(section => {
                section.style.display = 'none';
            });
            
            // Получаем ID секции из атрибута data-section
            const targetSectionId = link.getAttribute('data-section');
            console.log('Целевая секция:', targetSectionId);
            
            // Показываем нужную секцию
            const targetSection = document.getElementById(targetSectionId);
            if (targetSection) {
                console.log('Секция найдена, отображаем');
                targetSection.style.display = 'block';
            } else {
                console.error(`Секция с ID "${targetSectionId}" не найдена на странице`);
            }
        });
    } else {
        console.error('Контейнер меню не найден!');
    }
    
    // Для совместимости также оставляем обработчики на каждой ссылке, но в упрощенном виде
    menuLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            console.log('Прямой клик по ссылке:', link.getAttribute('href'));
            // Обработка выполняется через делегирование выше
        });
    });
    
    // Валидация форм с визуальной обратной связью
    function validateForm(form) {
        let isValid = true;
        const inputs = form.querySelectorAll('input, textarea');
        
        inputs.forEach(input => {
            if (input.hasAttribute('required') && !input.value) {
                input.classList.add('is-invalid');
                isValid = false;
            } else if (input.pattern && input.value && !new RegExp(input.pattern).test(input.value)) {
                input.classList.add('is-invalid');
                isValid = false;
            } else {
                input.classList.remove('is-invalid');
            }
        });
        
        // Проверка совпадения паролей
        if (form.id === 'password-change-form') {
            const password = form.querySelector('#new_password');
            const confirmation = form.querySelector('#new_password_confirmation');
            
            if (password.value !== confirmation.value) {
                confirmation.classList.add('is-invalid');
                isValid = false;
            }
        }
        
        return isValid;
    }
    
    // Функция для обновления CSRF токена с обработкой ошибок
    function refreshCsrfToken() {
        return fetch('/refresh-csrf', {
            method: 'GET',
            credentials: 'same-origin',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(response => {
            if (response.ok) {
                return response.json().then(data => {
                    if (data && data.token) {
                        document.querySelector('meta[name="csrf-token"]').content = data.token;
                        return data.token;
                    } else {
                        throw new Error('Получен неверный формат токена');
                    }
                });
            } else {
                // Возвращаем текущий токен при ошибке
                return document.querySelector('meta[name="csrf-token"]')?.content || '';
            }
        })
        .catch(error => {
            console.warn('Не удалось обновить CSRF токен:', error);
            return document.querySelector('meta[name="csrf-token"]')?.content || '';
        });
    }
    
    // Обработка формы обновления профиля с валидацией
    const updateProfileForm = document.getElementById('update-profile-form');
    if (updateProfileForm) {
        updateProfileForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            if (!validateForm(this)) {
                const messageElement = document.getElementById('profile-update-message');
                messageElement.style.display = 'block';
                messageElement.className = 'alert alert-danger';
                messageElement.textContent = 'Пожалуйста, проверьте правильность заполнения всех полей';
                return;
            }
            
            const formData = new FormData(this);
            const urlEncodedData = new URLSearchParams();
            
            // Преобразуем FormData в URLSearchParams
            for (const [name, value] of formData) {
                urlEncodedData.append(name, value);
            }
            
            // Сначала обновляем CSRF токен
            refreshCsrfToken().then(token => {
                // Отправляем запрос с обновленным токеном
                fetch('{{ route("profile.update_all") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                        'X-CSRF-TOKEN': token,
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    },
                    body: urlEncodedData
                })
                .then(response => {
                    // Пробуем получить JSON ответ независимо от кода ответа
                    return response.text().then(text => {
                        try {
                            // Пытаемся распарсить ответ как JSON
                            const data = JSON.parse(text);
                            if (!response.ok) {
                                if (response.status === 422) {
                                    throw new Error('Ошибка валидации: ' + (data.message || JSON.stringify(data)));
                                }
                                throw new Error('Ошибка сервера: ' + response.status);
                            }
                            return data;
                        } catch (e) {
                            // Если ответ не в формате JSON
                            console.error('Неверный формат ответа:', text);
                            throw new Error('Получен неверный формат ответа');
                        }
                    });
                })
                .then(data => {
                    const messageElement = document.getElementById('profile-update-message');
                    if (messageElement) {
                        messageElement.style.display = 'block';
                        
                        if (data.success) {
                            messageElement.className = 'alert alert-success';
                            messageElement.textContent = data.message || 'Данные успешно обновлены';
                        } else {
                            messageElement.className = 'alert alert-danger';
                            messageElement.textContent = data.message || 'Произошла ошибка при обновлении профиля';
                        }
                    }
                })
                .catch(error => {
                    console.error('Ошибка:', error);
                    const messageElement = document.getElementById('profile-update-message');
                    if (messageElement) {
                        messageElement.style.display = 'block';
                        messageElement.className = 'alert alert-danger';
                        messageElement.textContent = 'Произошла ошибка при обновлении профиля: ' + error.message;
                    }
                });
            });
        });
    }
    
    // Обработка формы смены пароля с валидацией
    const passwordChangeForm = document.getElementById('password-change-form');
    if (passwordChangeForm) {
        passwordChangeForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            if (!validateForm(this)) {
                const messageElement = document.getElementById('password-change-message');
                messageElement.style.display = 'block';
                messageElement.className = 'alert alert-danger';
                messageElement.textContent = 'Пожалуйста, проверьте правильность заполнения полей пароля';
                return;
            }
            
            const formData = new FormData(this);
            const urlEncodedData = new URLSearchParams();
            
            // Преобразуем FormData в URLSearchParams
            for (const [name, value] of formData) {
                urlEncodedData.append(name, value);
            }
            
            // Сначала обновляем CSRF токен
            refreshCsrfToken().then(token => {
                // Отправляем запрос с обновленным токеном
                fetch('{{ route("profile.change-password") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                        'X-CSRF-TOKEN': token,
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    },
                    body: urlEncodedData
                })
                .then(response => {
                    // Пробуем получить JSON ответ независимо от кода ответа
                    return response.text().then(text => {
                        try {
                            // Пытаемся распарсить ответ как JSON
                            const data = JSON.parse(text);
                            if (!response.ok) {
                                if (response.status === 422) {
                                    throw new Error('Ошибка валидации: ' + (data.message || JSON.stringify(data)));
                                }
                                throw new Error('Ошибка сервера: ' + response.status);
                            }
                            return data;
                        } catch (e) {
                            // Если ответ не в формате JSON
                            console.error('Неверный формат ответа:', text);
                            throw new Error('Получен неверный формат ответа');
                        }
                    });
                })
                .then(data => {
                    const messageElement = document.getElementById('password-change-message');
                    if (messageElement) {
                        messageElement.style.display = 'block';
                        
                        if (data.success) {
                            messageElement.className = 'alert alert-success';
                            messageElement.textContent = data.message || 'Пароль успешно изменен';
                            passwordChangeForm.reset();
                        } else {
                            messageElement.className = 'alert alert-danger';
                            messageElement.textContent = data.message || 'Произошла ошибка при смене пароля';
                        }
                    }
                })
                .catch(error => {
                    console.error('Ошибка:', error);
                    const messageElement = document.getElementById('password-change-message');
                    if (messageElement) {
                        messageElement.style.display = 'block';
                        messageElement.className = 'alert alert-danger';
                        messageElement.textContent = 'Произошла ошибка при смене пароля: ' + error.message;
                    }
                });
            });
        });
    }
    
    // Обработка отправки кода подтверждения на телефон
    const sendCodeBtn = document.getElementById('send-code-btn');
    if (sendCodeBtn) {
        sendCodeBtn.addEventListener('click', function() {
            const phone = document.getElementById('new-phone').value;
            if (!phone) {
                alert('Пожалуйста, введите номер телефона');
                return;
            }
            const formattedPhone = phone.replace(/\D/g, '');
            if (formattedPhone.length < 10) {
                alert('Пожалуйста, введите корректный номер телефона');
                return;
            }
            sendCodeBtn.disabled = true;
            sendCodeBtn.innerHTML = 'Отправка кода...';
            
            const messageElement = document.getElementById('phone-change-message');
            messageElement.style.display = 'none';
            refreshCsrfToken().then(token => {
                fetch('{{ route("profile.send-code") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': token,
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ phone: phone })
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Ошибка сервера: ' + response.status);
                    }
                    return response.json();
                })
                .then(data => {
                    // Разблокируем кнопку
                    sendCodeBtn.disabled = false;
                    sendCodeBtn.innerHTML = 'Отправить код подтверждения';
                    
                    if (data.success) {
                        document.getElementById('verification-section').style.display = 'flex';
                        messageElement.style.display = 'flex';
                        messageElement.className = 'alert alert-success';
                        messageElement.textContent = data.message || 'Код подтверждения отправлен на указанный номер';
                        
                        // Если в ответе есть отладочный код (временно), заполняем поля автоматически
                        if (data.debug_code && data.debug_code.length === 4) {
                            const codeChars = data.debug_code.toString().split('');
                            document.querySelectorAll('.verification-code-input').forEach((input, index) => {
                                if (index < codeChars.length) {
                                    input.value = codeChars[index];
                                    input.classList.add('filled');
                                }
                            });
                            document.getElementById('verification-code').value = data.debug_code;
                        }
                    } else {
                        messageElement.style.display = 'block';
                        messageElement.className = 'alert alert-danger';
                        messageElement.textContent = data.message || 'Произошла ошибка при отправке кода';
                    }
                })
                .catch(error => {
                    // Разблокируем кнопку
                    sendCodeBtn.disabled = false;
                    sendCodeBtn.innerHTML = 'Отправить код подтверждения';
                    
                    console.error('Ошибка:', error);
                    messageElement.style.display = 'block';
                    messageElement.className = 'alert alert-danger';
                    messageElement.textContent = 'Произошла ошибка при отправке кода: ' + error.message;
                });
            });
        });
    }
    const setupVerificationCodeInputs = function() {
        const inputs = document.querySelectorAll('.verification-code-input');
        const hiddenInput = document.getElementById('verification-code');
        
        if (!inputs.length || !hiddenInput) {
            console.warn('Элементы для ввода кода не найдены');
            return;
        }
        
        console.log('Инициализация полей для ввода проверочного кода');
        
        const updateHiddenInput = function() {
            const code = Array.from(inputs).map(input => input.value).join('');
            hiddenInput.value = code;
            console.log('Код обновлен:', code);
        };
        inputs.forEach((input, index) => {
            // Обработка ввода в поле
            input.addEventListener('input', function(e) {
                // Разрешаем только цифры
                this.value = this.value.replace(/[^0-9]/g, '');
                
                // Добавляем класс, если поле заполнено
                if (this.value) {
                    this.classList.add('filled');
                } else {
                    this.classList.remove('filled');
                }
                updateHiddenInput();
                if (this.value && index < inputs.length - 1) {
                    inputs[index + 1].focus();
                }
            });
            input.addEventListener('keydown', function(e) {
                if (e.key === 'Backspace') {
                    if (!this.value && index > 0) {
                        // Если поле пустое и это не первое поле, переходим к предыдущему
                        inputs[index - 1].focus();
                        // Предотвращаем стандартное поведение Backspace
                        e.preventDefault();
                    } else if (this.value) {
                        // Если поле не пустое, очищаем его
                        this.value = '';
                        this.classList.remove('filled');
                        updateHiddenInput();
                    }
                }
            });
            
            input.addEventListener('paste', function(e) {
                e.preventDefault();
                const pasteData = (e.clipboardData || window.clipboardData).getData('text');
                const digits = pasteData.replace(/\D/g, '').slice(0, 4);
                
                if (digits) {
                    for (let i = 0; i < Math.min(digits.length, inputs.length); i++) {
                        inputs[i].value = digits[i];
                        inputs[i].classList.add('filled');
                    }
                    
                    const nextEmpty = Math.min(digits.length, inputs.length - 1);
                    inputs[nextEmpty].focus();
                    
                    updateHiddenInput();
                }
            });
        });
    };
    
    setupVerificationCodeInputs();
    
    const verifyCodeBtn = document.getElementById('verify-code-btn');
    if (verifyCodeBtn) {
        verifyCodeBtn.addEventListener('click', function() {
            const phone = document.getElementById('new-phone').value;
            const code = document.getElementById('verification-code').value;
            
            if (!code || code.length !== 4) {
                alert('Пожалуйста, введите полный код подтверждения (4 цифры)');
                return;
            }
            
            // Сначала обновляем CSRF токен
            refreshCsrfToken().then(token => {
                fetch('{{ route("profile.verify-code") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': token,
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ 
                        phone: phone,
                        verification_code: code 
                    })
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Ошибка сервера: ' + response.status);
                    }
                    return response.json();
                })
                .then(data => {
                    const messageElement = document.getElementById('phone-change-message');
                    messageElement.style.display = 'block';
                    
                    if (data.success) {
                        messageElement.className = 'alert alert-success';
                        messageElement.textContent = data.message || 'Номер телефона успешно обновлен';
                        document.getElementById('verification-section').style.display = 'none';
                        document.getElementById('phone-change-form').reset();
                        
                        // Обновляем отображаемый номер телефона без перезагрузки страницы
                        setTimeout(function() {
                            location.reload();
                        }, 2000);
                    } else {
                        messageElement.className = 'alert alert-danger';
                        messageElement.textContent = data.message || 'Неверный или просроченный код';
                    }
                })
                .catch(error => {
                    console.error('Ошибка:', error);
                    const messageElement = document.getElementById('phone-change-message');
                    messageElement.style.display = 'block';
                    messageElement.className = 'alert alert-danger';
                    messageElement.textContent = 'Произошла ошибка при проверке кода: ' + error.message;
                });
            });
        });
    }
    
    // Повторная отправка кода
    const resendCodeBtn = document.getElementById('resend-code');
    if (resendCodeBtn) {
        resendCodeBtn.addEventListener('click', function() {
            sendCodeBtn.click();
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
    
    // Маска для ввода телефона
    const phoneInputs = document.querySelectorAll('.maskphone');
    phoneInputs.forEach(input => {
        input.addEventListener('input', maskPhone);
        input.addEventListener('focus', maskPhone);
        input.addEventListener('blur', maskPhone);
    });
    
    // Маска для ввода кода подтверждения (для каждого поля отдельно)
    const codeInputs = document.querySelectorAll('.verification-code-input');
    if (codeInputs.length) {
        codeInputs.forEach(input => {
            input.addEventListener('input', function() {
                this.value = this.value.replace(/[^0-9]/g, '').slice(0, 1);
            });
        });
    }
    
    // Инициализация при загрузке страницы
    // Показываем первую секцию по умолчанию, если не видна
    const defaultSection = document.getElementById('personal-section');
    if (defaultSection && window.getComputedStyle(defaultSection).display === 'none') {
        console.log('Показываем секцию по умолчанию');
        sections.forEach(section => section.style.display = 'none');
        defaultSection.style.display = 'block';
        
        // Активируем соответствующую ссылку
        const defaultLink = document.querySelector('[data-section="personal-section"]');
        if (defaultLink) {
            menuLinks.forEach(link => link.classList.remove('active'));
            defaultLink.classList.add('active');
        }
    }

    // Добавляем обработчик изменения файла для автоматической отправки формы
    const avatarInput = document.getElementById('avatar-upload');
    if (avatarInput) {
        avatarInput.addEventListener('change', function() {
            if (this.files && this.files[0]) {
                // Проверка размера файла (не более 2MB)
                const maxSize = 2 * 1024 * 1024; // 2MB в байтах
                if (this.files[0].size > maxSize) {
                    alert('Размер файла не должен превышать 2MB');
                    this.value = ''; // Очищаем input
                    return;
                }
                
                // Проверка типа файла
                const acceptedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/svg+xml'];
                if (!acceptedTypes.includes(this.files[0].type)) {
                    alert('Пожалуйста, выберите изображение (JPG, PNG, GIF или SVG)');
                    this.value = ''; // Очищаем input
                    return;
                }
                
                // Если проверки прошли успешно, отправляем форму
                document.getElementById('update-avatar-form').submit();
            }
        });
    }

    // Дополнительная валидация поля опыта работы
    const experienceInputs = document.querySelectorAll('input[name="experience"]');
    if (experienceInputs.length) {
        experienceInputs.forEach(input => {
            input.addEventListener('input', function(e) {
                // Удаляем все нецифровые символы
                this.value = this.value.replace(/[^\d]/g, '');
                
                // Проверяем на превышение диапазона
                const value = parseInt(this.value, 10);
                if (isNaN(value)) {
                    this.value = '';
                } else if (value > 100) {
                    this.value = '100';
                }
            });
            
            // Добавляем проверку при потере фокуса
            input.addEventListener('blur', function() {
                if (this.value.trim() === '') {
                    this.value = '';
                }
            });
        });
    }

    // Находим форму удаления аккаунта
    const deleteForm = document.getElementById('delete-account-form');
    
    if (deleteForm) {
        // Добавляем обработчик отправки формы
        deleteForm.addEventListener('submit', function(e) {
            // Запрашиваем дополнительное подтверждение
            if (!confirm('ВНИМАНИЕ! Вы действительно хотите удалить свой аккаунт? Это действие нельзя отменить.')) {
                e.preventDefault();
                return false;
            }
            
            return true;
        });
    }
});
</script>

<!-- Улучшенные стили для модального окна удаления аккаунта -->
<style>
#deleteAccountModal .modal-header {
    background-color: #f8d7da;
    color: #721c24;
    border-bottom: 1px solid #f5c6cb;
}

#deleteAccountModal .modal-footer {
    border-top: 1px solid #f5c6cb;
}

#deleteAccountModal .btn-danger {
    background-color: #dc3545;
    border-color: #dc3545;
}

#deleteAccountModal .btn-danger:hover {
    background-color: #c82333;
    border-color: #bd2130;
}
</style>