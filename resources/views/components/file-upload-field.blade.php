{{-- 
    Компонент для файловых полей с drag-and-drop функциональностью
    
    Параметры:
    - $fieldId: ID поля
    - $fieldName: Name поля
    - $label: Подпись поля
    - $icon: Иконка (Font Awesome класс)
    - $accept: Разрешенные типы файлов
    - $currentFile: Текущий файл (URL)
    - $originalName: Оригинальное имя файла
    - $isYandex: Загрузка на Яндекс.Диск (по умолчанию true)
    - $required: Обязательное поле (по умолчанию false)
--}}

@php
    $fieldId = $fieldId ?? $fieldName;
    $isYandex = $isYandex ?? true;
    $required = $required ?? false;
    $icon = $icon ?? 'fas fa-file';
    $accept = $accept ?? '';
    $currentFile = $currentFile ?? '';
    $originalName = $originalName ?? '';
    
    // Определяем класс для Яндекс.Диска
    $inputClass = 'form-control' . ($isYandex ? ' yandex-upload' : '');
@endphp

<div class="col-md-6 mb-3">
    <label for="{{ $fieldId }}" class="form-label">
        <i class="{{ $icon }} me-1"></i>{{ $label }}
        @if($required)<span class="text-danger">*</span>@endif
    </label>
    
    <div class="file-upload-container" data-field="{{ $fieldName }}">
        <input type="file" 
               class="{{ $inputClass }}" 
               id="{{ $fieldId }}" 
               name="{{ $fieldName }}"
               @if($accept) accept="{{ $accept }}" @endif
               @if($required) required @endif>
    </div>
    
    {{-- Единый контейнер для всех ссылок Яндекс.Диска --}}
    @if($isYandex)
        <div class="yandex-file-links-container" data-field="{{ $fieldName }}">
            {{-- Если есть текущий файл, отображаем его --}}
            @if($currentFile)
                <div class="mt-2">
                    <a href="{{ $currentFile }}" 
                       target="_blank" 
                       class="btn btn-sm btn-outline-success yandex-file-link">
                        <i class="fas fa-cloud-download-alt me-1"></i>
                        {{ $originalName ?: 'Просмотр файла' }}
                    </a>
                </div>
            @endif
        </div>
    @endif
</div>

{{-- Инициализация drag-and-drop для этого поля --}}
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Если система drag-and-drop уже загружена, переинициализируем
    if (window.dragDropFileUpload) {
        setTimeout(() => {
            window.dragDropFileUpload.reinitialize();
        }, 100);
    }
});
</script>
