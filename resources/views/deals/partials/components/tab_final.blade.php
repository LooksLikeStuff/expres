<!-- Модуль: Финал проекта -->
<fieldset class="module__deal" id="module-final">
    <legend>Финал проекта</legend>
    @foreach($dealFields['final'] as $field)
        <div class="form-group-deal">
            <label title="{{ $field['description'] ?? 'Поле: ' . $field['label'] }}">
                @if(isset($field['icon']))
                <i class="{{ $field['icon'] }}"></i>
                @endif
                {{ $field['label'] ?? ucfirst(str_replace('_', ' ', $field['name'])) }}:
                @if($field['type'] == 'file')
                    @if(in_array(Auth::user()->status, ['admin', 'coordinator', 'partner']) && (!isset($field['role']) || in_array(Auth::user()->status, $field['role'])))
                        <input type="file" name="{{ $field['name'] }}" id="{{ $field['id'] ?? $field['name'] }}" 
                                {{ isset($field['required']) && $field['required'] ? 'required' : '' }} 
                                {{ isset($field['accept']) ? 'accept='.$field['accept'] : '' }} 
                                class="file-input yandex-upload">
                        
                        @php
                            $yandexUrlField = 'yandex_url_' . $field['name'];
                            $originalNameField = 'original_name_' . $field['name'];
                        @endphp
                        
                        @if(!empty($deal->{$yandexUrlField}))
                            <div class="file-link yandex-file-link">
                                <a href="{{ $deal->{$yandexUrlField} }}" target="_blank" title="Открыть файл, загруженный на Яндекс.Диск">
                                    <i class="fas fa-cloud-download-alt"></i> {{ $deal->{$originalNameField} ?? 'Просмотр файла' }}
                                </a>
                            </div>
                        @endif
                    @else
                        @php
                            $yandexUrlField = 'yandex_url_' . $field['name'];
                            $originalNameField = 'original_name_' . $field['name'];
                        @endphp
                        
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
                @elseif($field['type'] == 'text')
                    @if(isset($field['role']) && in_array($userRole, $field['role']))
                        <input type="text" name="{{ $field['name'] }}" id="{{ $field['id'] ?? $field['name'] }}" value="{{ $deal->{$field['name']} }}" {{ isset($field['required']) && $field['required'] ? 'required' : '' }} {{ isset($field['maxlength']) ? 'maxlength='.$field['maxlength'] : '' }}>
                    @else
                        <input type="text" name="{{ $field['name'] }}_display" id="{{ $field['id'] ?? $field['name'] }}_display" value="{{ $deal->{$field['name']} }}" disabled {{ isset($field['maxlength']) ? 'maxlength='.$field['maxlength'] : '' }} class="read-only-field">
                        <!-- Добавляем скрытое поле для отправки значения -->
                        <input type="hidden" name="{{ $field['name'] }}" value="{{ $deal->{$field['name']} }}">
                        <span class="read-only-hint">Только для чтения</span>
                    @endif
                @elseif($field['type'] == 'select')
                    @if(isset($field['role']) && in_array($userRole, $field['role']))
                        <select name="{{ $field['name'] }}" id="{{ $field['id'] ?? $field['name'] }}">
                            <option value="">-- Выберите {{ strtolower($field['label'] ?? '') }} --</option>
                            @foreach($field['options'] as $value => $text)
                                <option value="{{ $value }}" {{ $deal->{$field['name']} == $value ? 'selected' : '' }}>{{ $text }}</option>
                            @endforeach
                        </select>
                    @else
                        <select name="{{ $field['name'] }}_display" id="{{ $field['id'] ?? $field['name'] }}_display" disabled class="read-only-field">
                            <option value="">-- Выберите {{ strtolower($field['label'] ?? '') }} --</option>
                            @foreach($field['options'] as $value => $text)
                                <option value="{{ $value }}" {{ $deal->{$field['name']} == $value ? 'selected' : '' }}>{{ $text }}</option>
                            @endforeach
                        </select>
                        <!-- Добавляем скрытое поле для отправки значения -->
                        <input type="hidden" name="{{ $field['name'] }}" value="{{ $deal->{$field['name']} }}">
                        <span class="read-only-hint">Только для чтения</span>
                    @endif
                @elseif($field['type'] == 'textarea')
                    @if(isset($field['role']) && in_array($userRole, $field['role']))
                        <textarea name="{{ $field['name'] }}" id="{{ $field['id'] ?? $field['name'] }}" {{ isset($field['required']) && $field['required'] ? 'required' : '' }} {{ isset($field['maxlength']) ? 'maxlength='.$field['maxlength'] : '' }}>{{ $deal->{$field['name']} }}</textarea>
                    @else
                        <textarea name="{{ $field['name'] }}_display" id="{{ $field['id'] ?? $field['name'] }}_display" disabled class="read-only-field" {{ isset($field['maxlength']) ? 'maxlength='.$field['maxlength'] : '' }}>{{ $deal->{$field['name']} }}</textarea>
                        <!-- Добавляем скрытое поле для отправки значения -->
                        <input type="hidden" name="{{ $field['name'] }}" value="{{ $deal->{$field['name']} }}">
                        <span class="read-only-hint">Только для чтения</span>
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
