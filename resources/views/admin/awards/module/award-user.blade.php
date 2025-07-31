
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Выдать награду пользователю</h1>
        <a href="{{ route('profile.view', $user->id) }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Вернуться к профилю
        </a>
    </div>

    <div class="row">
        <div class="col-md-4">
            <div class="card mb-4">
                <div class="card-body">
                    <h5 class="card-title">Информация о пользователе</h5>
                    <div class="user-info">
                        <div class="user-avatar">
                            <img src="{{ $user->avatar_url ? asset($user->avatar_url) : asset('storage/icon/profile.svg') }}" 
                                 alt="{{ $user->name }}" class="rounded-circle" width="80" height="80">
                        </div>
                        <div class="user-details">
                            <h4>{{ $user->name }}</h4>
                            <p class="text-muted">{{ ucfirst($user->status) }}</p>
                            <p><i class="fas fa-envelope"></i> {{ $user->email }}</p>
                            @if($user->phone)
                            <p><i class="fas fa-phone"></i> {{ $user->phone }}</p>
                            @endif
                        </div>
                    </div>
                    
                    @if($user->awards->count() > 0)
                    <h6 class="mt-3">Текущие награды:</h6>
                    <div class="current-awards">
                        @foreach($user->awards as $existingAward)
                        <div class="award-badge" title="{{ $existingAward->name }}">
                            {!! $existingAward->icon !!}
                        </div>
                        @endforeach
                    </div>
                    @endif
                </div>
            </div>
        </div>
        
        <div class="col-md-8">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Выберите награду для выдачи</h5>
                    
                    @if (session('success'))
                        <div class="alert alert-success">
                            {{ session('success') }}
                        </div>
                    @endif

                    @if (session('error'))
                        <div class="alert alert-danger">
                            {{ session('error') }}
                        </div>
                    @endif
                    
                    <form action="{{ route('admin.awards.user.give', $user->id) }}" method="POST">
                        @csrf
                        
                        <div class="form-group mb-4">
                            <label for="award_id">Награда</label>
                            <select class="form-control @error('award_id') is-invalid @enderror" id="award_id" name="award_id" required>
                                <option value="">Выберите награду...</option>
                                @foreach($awards->groupBy('category') as $category => $categoryAwards)
                                    <optgroup label="{{ $category ?: 'Без категории' }}">
                                        @foreach($categoryAwards as $award)
                                            <option value="{{ $award->id }}" data-icon='{!! htmlspecialchars($award->icon) !!}' 
                                                    data-description="{{ $award->description }}">
                                                {{ $award->name }}
                                            </option>
                                        @endforeach
                                    </optgroup>
                                @endforeach
                            </select>
                            @error('award_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="form-group mb-4">
                            <label for="comment">Комментарий (необязательно)</label>
                            <textarea class="form-control @error('comment') is-invalid @enderror" 
                                      id="comment" name="comment" rows="3">{{ old('comment') }}</textarea>
                            @error('comment')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">Укажите причину или комментарий к награде</small>
                        </div>
                        
                        <div class="award-preview-container mb-4">
                            <label>Предпросмотр:</label>
                            <div class="award-preview">
                                <div id="award-icon-preview" class="award-preview-icon">
                                    <span class="text-muted">Выберите награду</span>
                                </div>
                                <div class="award-preview-details">
                                    <h4 id="award-name-preview" class="award-preview-name">Название награды</h4>
                                    <p id="award-description-preview" class="award-preview-description">Описание награды</p>
                                </div>
                            </div>
                        </div>
                        
                        <button type="submit" class="btn btn-primary">Выдать награду</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const awardSelect = document.getElementById('award_id');
    const awardIconPreview = document.getElementById('award-icon-preview');
    const awardNamePreview = document.getElementById('award-name-preview');
    const awardDescriptionPreview = document.getElementById('award-description-preview');
    
    // Функция для обновления предпросмотра
    function updatePreview() {
        const selectedOption = awardSelect.options[awardSelect.selectedIndex];
        
        if (selectedOption && selectedOption.value) {
            const icon = selectedOption.dataset.icon;
            const description = selectedOption.dataset.description;
            const name = selectedOption.text;
            
            awardIconPreview.innerHTML = icon;
            awardNamePreview.textContent = name;
            awardDescriptionPreview.textContent = description;
            
            document.querySelector('.award-preview').classList.add('active');
        } else {
            awardIconPreview.innerHTML = '<span class="text-muted">Выберите награду</span>';
            awardNamePreview.textContent = 'Название награды';
            awardDescriptionPreview.textContent = 'Описание награды';
            
            document.querySelector('.award-preview').classList.remove('active');
        }
    }
    
    // Обновляем предпросмотр при изменении выбора награды
    awardSelect.addEventListener('change', updatePreview);
    
    // Инициализируем предпросмотр
    updatePreview();
});
</script>

<style>
.user-info {
    display: flex;
    align-items: center;
    gap: 15px;
}

.user-avatar {
    flex-shrink: 0;
}

.user-details {
    flex-grow: 1;
}

.user-details h4 {
    margin-bottom: 0;
    font-size: 18px;
}

.user-details p {
    margin-bottom: 5px;
}

.current-awards {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    margin-top: 10px;
}

.award-badge {
    width: 35px;
    height: 35px;
}

.award-badge svg {
    width: 100%;
    height: 100%;
}

.award-preview {
    display: flex;
    align-items: flex-start;
    gap: 20px;
    padding: 15px;
    border: 1px solid #eee;
    border-radius: 8px;
    background-color: #f9f9f9;
    opacity: 0.7;
    transition: opacity 0.3s;
}

.award-preview.active {
    opacity: 1;
}

.award-preview-icon {
    flex: 0 0 80px;
    height: 80px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.award-preview-icon svg {
    width: 70px;
    height: 70px;
}

.award-preview-details {
    flex-grow: 1;
}

.award-preview-name {
    margin: 0 0 8px;
    font-size: 20px;
    color: #333;
}

.award-preview-description {
    margin: 0;
    color: #666;
}
</style>

