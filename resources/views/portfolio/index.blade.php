<div class="container">
        <h1 class="portfolio-title">Управление портфолио</h1>
    <section class="portfolio-add-section">
        <h2 class="section-title">Добавить новый проект в портфолио</h2>
        <div class="portfolio-form-container">
            <form action="{{ route('portfolio.store') }}" method="POST" enctype="multipart/form-data" class="portfolio-form">
                @csrf
                <div class="form-row">
                    <label for="title" class="form-label">Название проекта</label>
                    <input type="text" name="title" id="title" class="form-input" required>
                </div>
                <div class="form-row">
                    <label for="description" class="form-label">Описание проекта</label>
                    <textarea name="description" id="description" class="form-textarea" rows="4" required></textarea>
                </div>
                <div class="form-row">
                    <label for="image" class="form-label">Изображение проекта</label>
                    <div class="image-upload-wrapper">
                        <input type="file" name="image" id="image" class="form-file-input" required accept="image/*">
                        <label for="image" class="file-input-label">
                            <span class="file-input-text">Выберите файл</span>
                            <span class="file-input-button">Обзор</span>
                        </label>
                        <div class="image-preview-container">
                            <img id="image-preview" src="#" alt="Предпросмотр" class="image-preview">
                        </div>
                    </div>
                    <small class="form-helper-text">Рекомендуемое разрешение: 1200x800px, максимальный размер: 5MB</small>
                </div>
                <div class="form-row form-action-row">
                    <button type="submit" class="">Добавить проект</button>
                </div>
            </form>
        </div>
    </section>
    
    <section class="portfolio-list-section">
        <h2 class="section-title">Мои проекты</h2>
        <p class="section-description">Перетащите элементы для изменения порядка отображения в портфолио</p>
        
        <div class="portfolio-grid" id="portfolio-items">
            @forelse(auth()->user()->portfolioItems()->orderBy('order')->get() as $item)
                <div class="portfolio-grid-item" data-id="{{ $item->id }}">
                    <div class="portfolio-card">
                        <div class="portfolio-image" style="background-image: url('{{ asset('storage/'.$item->image_path) }}')"></div>
                        <div class="portfolio-overlay">
                            <h5 class="portfolio-item-title">{{ $item->title }}</h5>
                            <p class="portfolio-item-description">{{ \Illuminate\Support\Str::limit($item->description, 100) }}</p>
                            <div class="portfolio-actions">
                                <button class="portfolio-btn edit-btn edit-item" data-id="{{ $item->id }}" 
                                        data-title="{{ $item->title }}" data-description="{{ $item->description }}"
                                        data-image="{{ asset('storage/'.$item->image_path) }}">
                                    <span class="btn-icon"><i class="fas fa-edit"></i></span> Редактировать
                                </button>
                                <form action="{{ route('portfolio.destroy', $item->id) }}" method="POST" class="portfolio-delete-form">
                                    @csrf
                                    @method('DELETE')
                                    <button type="button" class="portfolio-btn delete-btn" onclick="confirmDelete(this)">
                                        <span class="btn-icon"><i class="fas fa-trash"></i></span> Удалить
                                    </button>
                                </form>
                            </div>
                        </div>
                        <div class="portfolio-drag-handle" title="Перетащите для изменения порядка">
                            <i class="fas fa-grip-lines"></i>
                        </div>
                    </div>
                </div>
            @empty
                <div class="portfolio-empty-state">
                    <div class="empty-state-icon">
                        <i class="fas fa-images"></i>
                    </div>
                    <p class="empty-state-message">У вас пока нет проектов в портфолио. Добавьте свой первый проект!</p>
                </div>
            @endforelse
        </div>
    </section>
    
    <!-- Модальное окно для редактирования -->
    <div class="modal" id="editModal">
        <div class="modal-container">
            <div class="modal-content">
                <div class="modal-header">
                    <h3 class="modal-title">Редактировать проект</h3>
                    <button type="button" class="modal-close" data-dismiss="modal">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="edit-form" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        <div class="form-row">
                            <label for="edit-title" class="form-label">Название проекта</label>
                            <input type="text" name="title" id="edit-title" class="form-input" required>
                        </div>
                        <div class="form-row">
                            <label for="edit-description" class="form-label">Описание проекта</label>
                            <textarea name="description" id="edit-description" class="form-textarea" rows="4" required></textarea>
                        </div>
                        <div class="form-row">
                            <label for="edit-image" class="form-label">Изображение проекта</label>
                            <div class="image-upload-wrapper">
                                <input type="file" name="image" id="edit-image" class="form-file-input" accept="image/*">
                                <label for="edit-image" class="file-input-label">
                                    <span class="file-input-text">Выберите новый файл</span>
                                    <span class="file-input-button">Обзор</span>
                                </label>
                                <small class="form-helper-text">Оставьте пустым, если не хотите менять изображение</small>
                            </div>
                            <div class="current-image-preview">
                                <h4 class="preview-title">Текущее изображение:</h4>
                                <img id="current-image-preview" src="" alt="Текущее изображение" class="current-image">
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Отмена</button>
                    <button type="button" class="" id="save-edit">Сохранить изменения</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Модальное окно подтверждения удаления -->
    <div class="modal" id="deleteConfirmModal">
        <div class="modal-container modal-sm">
            <div class="modal-content">
                <div class="modal-header">
                    <h3 class="modal-title">Подтверждение удаления</h3>
                    <button type="button" class="modal-close" onclick="closeDeleteModal()">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div class="modal-body">
                    <p class="confirm-text">Вы действительно хотите удалить этот проект? Это действие нельзя отменить.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="" onclick="closeDeleteModal()">Отмена</button>
                    <button type="button" class="" id="confirmDeleteButton">Удалить</button>
                </div>
            </div>
        </div>
    </div>
</div>


<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/js/all.min.js"></script>
<script>
    $(document).ready(function() {
        // Предпросмотр изображения при загрузке нового файла
        $('#image').change(function() {
            const file = this.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    $('#image-preview').attr('src', e.target.result);
                    $('#image-preview').show();
                }
                reader.readAsDataURL(file);
                
                // Меняем текст на имя файла
                $('.file-input-text').text(file.name);
            }
        });
        
        $('#edit-image').change(function() {
            const file = this.files[0];
            if (file) {
                $('.file-input-text').text(file.name);
            }
        });
        
        // Сортировка элементов портфолио
        $("#portfolio-items").sortable({
            items: "> div",
            handle: ".portfolio-drag-handle",
            placeholder: "ui-sortable-placeholder",
            update: function(event, ui) {
                let items = [];
                $('#portfolio-items > div').each(function() {
                    items.push($(this).data('id'));
                });
                
                $.ajax({
                    url: '{{ route("portfolio.reorder") }}',
                    method: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        items: items
                    },
                    success: function(response) {
                        if(response.success) {
                            showNotification('Порядок элементов обновлен', 'success');
                        }
                    },
                    error: function() {
                        showNotification('Ошибка при обновлении порядка элементов', 'error');
                    }
                });
            }
        }).disableSelection();
        
        // Открытие модального окна редактирования
        $('.edit-item').click(function() {
            const id = $(this).data('id');
            const title = $(this).data('title');
            const description = $(this).data('description');
            const image = $(this).data('image');
            
            $('#edit-title').val(title);
            $('#edit-description').val(description);
            $('#current-image-preview').attr('src', image);
            
            $('#edit-form').attr('action', '{{ url("portfolio") }}/' + id);
            $('#editModal').addClass('active');
            $('body').css('overflow', 'hidden');
        });
        
        // Закрытие модального окна
        $('.modal-close, button[data-dismiss="modal"]').click(function() {
            $('.modal').removeClass('active');
            $('body').css('overflow', '');
        });
        
        // Сохранение формы редактирования
        $('#save-edit').click(function() {
            $('#edit-form').submit();
        });
        
        // Закрытие по клику вне модального окна
        $('.modal').on('click', function(e) {
            if ($(e.target).hasClass('modal')) {
                $('.modal').removeClass('active');
                $('body').css('overflow', '');
            }
        });
    });
    
    // Функция для подтверждения удаления
    function confirmDelete(button) {
        // Находим форму удаления
        const form = $(button).closest('form');
        
        // Показываем модальное окно подтверждения
        $('#deleteConfirmModal').addClass('active');
        $('body').css('overflow', 'hidden');
        
        // Настраиваем обработчик для кнопки подтверждения
        $('#confirmDeleteButton').off('click').on('click', function() {
            form.submit();
        });
    }
    
    // Закрытие модального окна подтверждения
    function closeDeleteModal() {
        $('#deleteConfirmModal').removeClass('active');
        $('body').css('overflow', '');
    }
    
    // Уведомления
    function showNotification(message, type = 'info') {
        const notification = $('<div class="notification ' + type + '">' + message + '</div>');
        $('body').append(notification);
        
        setTimeout(function() {
            notification.addClass('show');
        }, 10);
        
        setTimeout(function() {
            notification.removeClass('show');
            setTimeout(function() {
                notification.remove();
            }, 300);
        }, 3000);
    }
</script>

<style>


</style>

