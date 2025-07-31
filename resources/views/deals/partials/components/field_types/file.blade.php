@if($field['name'] == 'avatar_path')
    <!-- Обычная загрузка только для аватара -->
    @if(!isset($field['role']) || (isset($field['role']) && in_array($userRole, $field['role'])))
        <input type="file" name="{{ $field['name'] }}" id="{{ $field['id'] ?? $field['name'] }}" accept="{{ isset($field['accept']) ? $field['accept'] : '' }}" class="file-input">
        @if(!empty($deal->{$field['name']}))
            <div class="file-link">
                <a href="{{ asset('storage/' . $deal->{$field['name']}) }}" target="_blank" title="Просмотреть загруженный аватар">
                    <i class="fas fa-image"></i> Просмотреть аватар
                </a>
            </div>
        @endif
    @else
        @if(!empty($deal->{$field['name']}))
            <div class="file-link">
                <a href="{{ asset('storage/' . $deal->{$field['name']}) }}" target="_blank">Просмотреть аватар</a>
            </div>
        @else
            <span class="no-file-message">Файл не загружен</span>
        @endif
    @endif
@else
    <!-- Загрузка на Яндекс.Диск для всех остальных полей -->
    @php
        // Явно проверяем статус пользователя для coordinator и admin
        $canUpload = $userRole == 'coordinator' || $userRole == 'admin' || 
                    (!isset($field['role']) || (isset($field['role']) && in_array($userRole, $field['role'])));
        
        // Получаем поля для Яндекс.Диска
        $yandexUrlField = 'yandex_url_' . $field['name'];
        $originalNameField = 'original_name_' . $field['name'];
    @endphp
    
    @if($canUpload)
        <input type="file" name="{{ $field['name'] }}" id="{{ $field['id'] ?? $field['name'] }}" accept="{{ isset($field['accept']) ? $field['accept'] : '' }}" class="file-input yandex-upload">
        
        @if(!empty($deal->{$yandexUrlField}))
            <div class="file-link yandex-file-link">
                <a href="{{ $deal->{$yandexUrlField} }}" target="_blank" title="Открыть файл, загруженный на Яндекс.Диск">
                    <i class="fas fa-cloud-download-alt"></i> {{ $deal->{$originalNameField} ?? 'Просмотр файла' }}
                </a>
            </div>
        @endif
    @else
        @if(!empty($deal->{$yandexUrlField}))
            <div class="file-link yandex-file-link">
                <a href="{{ $deal->{$yandexUrlField} }}" target="_blank" title="Открыть файл, загруженный на Яндекс.Диск">
                    <i class="fas fa-cloud-download-alt"></i> {{ $deal->{$originalNameField} ?? 'Просмотр файла' }}
                </a>
            </div>
        @else
            <span class="no-file-message">Файл не загружен</span>
        @endif
    @endif
@endif
