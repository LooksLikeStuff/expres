
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Создать новую награду</h1>
        <a href="{{ route('admin.awards.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Назад к списку
        </a>
    </div>

    <div class="card">
        <div class="card-body">
            <form action="{{ route('admin.awards.store') }}" method="POST">
                @csrf

                <div class="form-group mb-3">
                    <label for="name">Название награды</label>
                    <input type="text" class="form-control @error('name') is-invalid @enderror" 
                           id="name" name="name" value="{{ old('name') }}" required>
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group mb-3">
                    <label for="category">Категория</label>
                    <input type="text" class="form-control @error('category') is-invalid @enderror" 
                           id="category" name="category" value="{{ old('category') }}">
                    @error('category')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <small class="form-text text-muted">Например: Достижения, Опыт, Специальные</small>
                </div>

                <div class="form-group mb-3">
                    <label for="description">Описание</label>
                    <textarea class="form-control @error('description') is-invalid @enderror" 
                              id="description" name="description" rows="3" required>{{ old('description') }}</textarea>
                    @error('description')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group mb-3">
                    <label for="icon">SVG иконка</label>
                    <div class="input-group">
                        <textarea class="form-control @error('icon') is-invalid @enderror" 
                                 id="icon" name="icon" rows="5" required>{{ old('icon') }}</textarea>
                        @error('icon')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <small class="form-text text-muted">Вставьте SVG-код иконки</small>
                </div>

                <div class="form-group mb-4">
                    <label>Предпросмотр иконки:</label>
                    <div id="icon-preview" class="border p-3 text-center">
                        <span class="text-muted">Нет предпросмотра</span>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary">Создать награду</button>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const iconInput = document.getElementById('icon');
    const iconPreview = document.getElementById('icon-preview');
    
    // Функция для обновления предпросмотра
    function updatePreview() {
        const svgCode = iconInput.value.trim();
        if (svgCode) {
            iconPreview.innerHTML = svgCode;
        } else {
            iconPreview.innerHTML = '<span class="text-muted">Нет предпросмотра</span>';
        }
    }
    
    // Обновляем предпросмотр при изменении содержимого поля
    iconInput.addEventListener('input', updatePreview);
    
    // Инициализируем предпросмотр
    updatePreview();
});
</script>

<style>
#icon-preview {
    min-height: 100px;
    display: flex;
    align-items: center;
    justify-content: center;
}

#icon-preview svg {
    max-width: 100px;
    max-height: 100px;
}
</style>

