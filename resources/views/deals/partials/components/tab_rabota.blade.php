<!-- Модуль: Работа над проектом -->
<fieldset class="module__deal" id="module-rabota">
    <legend>Работа над проектом</legend>
    @foreach($dealFields['rabota'] as $field)
        <div class="form-group-deal">
            <label title="{{ $field['description'] ?? 'Поле: ' . $field['label'] }}">
                @if(isset($field['icon']))
                <i class="{{ $field['icon'] }}"></i>
                @endif
                {{ $field['label'] }}:
                @if($field['name'] == 'architect_id' || $field['name'] == 'designer_id' || $field['name'] == 'visualizer_id')
                    @php
                        $roleName = str_replace('_id', '', $field['name']); // architect, designer, visualizer
                        switch($roleName) {
                            case 'architect':
                                $roleUser = 'architect';
                                $placeholderText = 'Выберите архитектора...';
                                break;
                            case 'designer':
                                $roleUser = 'designer';
                                $placeholderText = 'Выберите дизайнера...';
                                break;
                            case 'visualizer':
                                $roleUser = 'visualizer';
                                $placeholderText = 'Выберите визуализатора...';
                                break;
                            default:
                                $roleUser = $roleName;
                                $placeholderText = 'Выберите специалиста...';
                        }
                    @endphp
                    @if(in_array(Auth::user()->status, ['admin', 'coordinator']))
                        <select name="{{ $field['name'] }}" id="{{ $field['name'] }}" class="form-control select2-specialist" 
                                data-role="{{ $roleUser }}" data-placeholder="{{ $placeholderText }}">
                            @if($deal->{$field['name']})
                                @php
                                    $selectedUser = \App\Models\User::find($deal->{$field['name']});
                                @endphp
                                @if($selectedUser)
                                    <option value="{{ $selectedUser->id }}" selected>{{ $selectedUser->name }}</option>
                                @endif
                            @endif
                        </select>
                    @else
                        @php
                            $userId = $deal->{$field['name']};
                            $userName = null;
                            
                            if ($userId) {
                                $user = \App\Models\User::find($userId);
                                $userName = $user ? $user->name : 'Не найден';
                            } else {
                                $userName = 'Не назначен';
                            }
                        @endphp
                        <input type="text" value="{{ $userName }}" disabled class="form-control">
                        <input type="hidden" name="{{ $field['name'] }}" value="{{ $userId }}">
                    @endif
                @elseif($field['type'] == 'text')
                    @if(isset($field['role']) && in_array($userRole, $field['role']))
                        <input type="text" name="{{ $field['name'] }}" id="{{ $field['id'] ?? $field['name'] }}" value="{{ $deal->{$field['name']} }}" {{ isset($field['required']) && $field['required'] ? 'required' : '' }} {{ isset($field['maxlength']) ? 'maxlength='.$field['maxlength'] : '' }}>
                    @else
                        <input type="text" name="{{ $field['name'] }}_display" id="{{ $field['id'] ?? $field['name'] }}_display" value="{{ $deal->{$field['name']} }}" disabled {{ isset($field['maxlength']) ? 'maxlength='.$field['maxlength'] : '' }}>
                        <!-- Добавляем скрытое поле для отправки значения -->
                        <input type="hidden" name="{{ $field['name'] }}" value="{{ $deal->{$field['name']} }}">
                    @endif
                @elseif($field['type'] == 'url')
                    @if(isset($field['role']) && in_array($userRole, $field['role']))
                        <input type="url" name="{{ $field['name'] }}" id="{{ $field['id'] ?? $field['name'] }}" value="{{ $deal->{$field['name']} }}" {{ isset($field['required']) && $field['required'] ? 'required' : '' }} placeholder="https://">
                    @else
                        <input type="url" name="{{ $field['name'] }}_display" id="{{ $field['id'] ?? $field['name'] }}_display" value="{{ $deal->{$field['name']} }}" disabled placeholder="https://">
                        <!-- Добавляем скрытое поле для отправки значения -->
                        <input type="hidden" name="{{ $field['name'] }}" value="{{ $deal->{$field['name']} }}">
                    @endif
                @elseif($field['type'] == 'select')
                    @if(isset($field['role']) && in_array($userRole, $field['role']))
                        <select name="{{ $field['name'] }}" id="{{ $field['id'] ?? $field['name'] }}">
                            <option value="">-- Выберите значение --</option>
                            @foreach($field['options'] as $value => $text)
                                <option value="{{ $value }}" {{ $deal->{$field['name']} == $value ? 'selected' : '' }}>{{ $text }}</option>
                            @endforeach
                        </select>
                    @else
                        <input type="text" name="{{ $field['name'] }}" id="{{ $field['id'] ?? $field['name'] }}" value="{{ $deal->{$field['name']} }}" disabled>
                    @endif
                @elseif($field['type'] == 'textarea')
                    @if(isset($field['role']) && in_array($userRole, $field['role']))
                        <textarea name="{{ $field['name'] }}" id="{{ $field['id'] ?? $field['name'] }}" {{ isset($field['maxlength']) ? 'maxlength='.$field['maxlength'] : '' }}>{{ $deal->{$field['name']} }}</textarea>
                    @else
                        <textarea name="{{ $field['name'] }}_display" id="{{ $field['id'] ?? $field['name'] }}_display" disabled {{ isset($field['maxlength']) ? 'maxlength='.$field['maxlength'] : '' }}>{{ $deal->{$field['name']} }}</textarea>
                        <!-- Добавляем скрытое поле для отправки значения -->
                        <input type="hidden" name="{{ $field['name'] }}" value="{{ $deal->{$field['name']} }}">
                    @endif
                @elseif($field['type'] == 'file')
                    @php
                        $canUpload = in_array(Auth::user()->status, ['coordinator', 'admin']) || 
                                   (!isset($field['role']) || (isset($field['role']) && in_array($userRole, $field['role'])));
                        $yandexUrlField = 'yandex_url_' . $field['name'];
                        $originalNameField = 'original_name_' . $field['name'];
                    @endphp
                    
                    @if($canUpload)
                        <input type="file" name="{{ $field['name'] }}" id="{{ $field['id'] ?? $field['name'] }}" 
                                accept="{{ isset($field['accept']) ? $field['accept'] : '' }}" class="file-input yandex-upload">
                        
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
                @elseif($field['type'] == 'date')
                    @if(isset($field['role']) && in_array($userRole, $field['role']))
                        <input type="date" name="{{ $field['name'] }}" id="{{ $field['id'] ?? $field['name'] }}" value="{{ $deal->{$field['name']} }}">
                    @else
                        <input type="date" name="{{ $field['name'] }}_display" id="{{ $field['id'] ?? $field['name'] }}_display" value="{{ $deal->{$field['name']} }}" disabled>
                        <!-- Добавляем скрытое поле для отправки значения -->
                        <input type="hidden" name="{{ $field['name'] }}" value="{{ $deal->{$field['name']} }}">
                    @endif
                @elseif($field['type'] == 'number')
                    @if(isset($field['role']) && in_array($userRole, $field['role']))
                        <input type="number" name="{{ $field['name'] }}" id="{{ $field['id'] ?? $field['name'] }}" value="{{ $deal->{$field['name']} }}" step="{{ isset($field['step']) ? $field['step'] : '0.01' }}">
                    @else
                        <input type="number" name="{{ $field['name'] }}_display" id="{{ $field['id'] ?? $field['name'] }}_display" value="{{ $deal->{$field['name']} }}" disabled>
                        <!-- Добавляем скрытое поле для отправки значения -->
                        <input type="hidden" name="{{ $field['name'] }}" value="{{ $deal->{$field['name']} }}">
                    @endif
                @endif
            </label>
        </div>
    @endforeach
</fieldset>
