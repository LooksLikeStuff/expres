<!-- Модуль: Заказ -->
<fieldset class="module__deal" id="module-zakaz"> 
    <legend>Заказ</legend>
    @foreach($dealFields['zakaz'] as $field)
        <div class="form-group-deal">
            <label title="{{ $field['description'] ?? 'Поле: ' . $field['label'] }}">
                @if(isset($field['icon']))
                <i class="{{ $field['icon'] }}"></i>
                @endif
                {{ $field['label'] }}:                @if($field['name'] == 'client_city')
                    @if(isset($field['role']) && in_array($userRole, $field['role']))
                        <select name="{{ $field['name'] }}" id="client_city" class="select2-field city-select" data-current-value="{{ $deal->{$field['name']} }}">
                        </select>
                    @else
                        <input type="text" name="{{ $field['name'] }}" id="{{ $field['id'] ?? $field['name'] }}" value="{{ $deal->{$field['name']} }}" disabled>
                    @endif
                @elseif($field['type'] == 'text')
                    @if(isset($field['role']) && in_array($userRole, $field['role']))
                        <input type="text" name="{{ $field['name'] }}" id="{{ $field['id'] ?? $field['name'] }}" 
                               value="{{ $deal->{$field['name']} }}" 
                               {{ isset($field['required']) && $field['required'] ? 'required' : '' }} 
                               {{ isset($field['maxlength']) ? 'maxlength='.$field['maxlength'] : '' }}
                               class="{{ $field['name'] == 'client_phone' ? 'maskphone' : '' }}">
                    @else
                        <input type="text" name="{{ $field['name'] }}_display" id="{{ $field['id'] ?? $field['name'] }}_display" 
                               value="{{ $deal->{$field['name']} }}" disabled 
                               {{ isset($field['maxlength']) ? 'maxlength='.$field['maxlength'] : '' }} 
                               class="{{ $field['name'] == 'client_phone' ? 'maskphone read-only-field' : 'read-only-field' }}">
                        <!-- Добавляем скрытое поле для отправки значения -->
                        <input type="hidden" name="{{ $field['name'] }}" value="{{ $deal->{$field['name']} }}">
                        <span class="read-only-hint">Только для чтения</span>
                    @endif
                @elseif($field['type'] == 'select')
                    @if($field['name'] == 'coordinator_id')
                        @if(Auth::user()->status == 'partner')
                            <select name="{{ $field['name'] }}" id="{{ $field['id'] ?? $field['name'] }}" disabled>
                                <option value="">-- Выберите координатора --</option>
                                @foreach($field['options'] as $value => $text)
                                    <option value="{{ $value }}" {{ $deal->{$field['name']} == $value ? 'selected' : '' }}>{{ $text }}</option>
                                @endforeach
                            </select>
                        @elseif(Auth::user()->status == 'coordinator')
                            <select name="{{ $field['name'] }}" id="{{ $field['id'] ?? $field['name'] }}" disabled>
                                <option value="">-- Выберите координатора --</option>
                                @foreach($field['options'] as $value => $text)
                                    <option value="{{ $value }}" {{ $deal->{$field['name']} == $value ? 'selected' : '' }}>{{ $text }}</option>
                                @endforeach
                            </select>
                            <input type="hidden" name="{{ $field['name'] }}" value="{{ $deal->{$field['name']} }}">
                        @else
                            <select name="{{ $field['name'] }}" id="{{ $field['id'] ?? $field['name'] }}" class="select2-coordinator-search" data-placeholder="-- Выберите координатора --">
                                <option value="">-- Выберите координатора --</option>
                                @foreach($field['options'] as $value => $text)
                                    <option value="{{ $value }}" {{ $deal->{$field['name']} == $value ? 'selected' : '' }}>{{ $text }}</option>
                                @endforeach
                            </select>
                        @endif
                    @elseif($field['name'] == 'office_partner_id')
                        @if(isset($field['role']) && in_array($userRole, $field['role']))
                            <select name="{{ $field['name'] }}" id="{{ $field['id'] ?? $field['name'] }}" class="select2-partner-search" data-placeholder="-- Выберите партнера --">
                                <option value="">-- Выберите партнера --</option>
                                @foreach($field['options'] as $value => $text)
                                    <option value="{{ $value }}" {{ $deal->{$field['name']} == $value ? 'selected' : '' }}>{{ $text }}</option>
                                @endforeach
                            </select>
                        @else
                            <input type="text" name="{{ $field['name'] }}" id="{{ $field['id'] ?? $field['name'] }}" value="{{ $deal->{$field['name']} }}" disabled>
                        @endif
                    @else
                        @if(isset($field['role']) && in_array($userRole, $field['role']))
                            @if($field['name'] == 'client_timezone')
                                <select name="{{ $field['name'] }}" id="{{ $field['id'] ?? $field['name'] }}" class="select2-cities-search form-control" data-current-value="{{ $deal->{$field['name']} }}" data-placeholder="-- Выберите город/часовой пояс --">
                                    <option value="">-- Выберите город/часовой пояс --</option>
                                    @if($deal->{$field['name']})
                                        <option value="{{ $deal->{$field['name']} }}" selected>{{ $deal->{$field['name']} }}</option>
                                    @endif
                                    @foreach($field['options'] as $value => $text)
                                        @if($value != $deal->{$field['name']})
                                            <option value="{{ $value }}" {{ $deal->{$field['name']} == $value ? 'selected' : '' }}>{{ $text }}</option>
                                        @endif
                                    @endforeach
                                </select>
                            @else
                                <select name="{{ $field['name'] }}" id="{{ $field['id'] ?? $field['name'] }}" class="form-control">
                                    <option value="">-- Выберите значение --</option>
                                    @foreach($field['options'] as $value => $text)
                                        <option value="{{ $value }}" {{ $deal->{$field['name']} == $value ? 'selected' : '' }}>{{ $text }}</option>
                                    @endforeach
                                </select>
                            @endif
                        @else
                            <input type="text" name="{{ $field['name'] }}" id="{{ $field['id'] ?? $field['name'] }}" value="{{ $deal->{$field['name']} }}" disabled>
                        @endif
                    @endif
                @elseif($field['type'] == 'textarea')
                    @if(isset($field['role']) && in_array($userRole, $field['role']))
                        <textarea name="{{ $field['name'] }}" id="{{ $field['id'] ?? $field['name'] }}" 
                            class="{{ $field['name'] == 'comment' ? 'deal-comment-textarea' : '' }}"
                            {{ isset($field['maxlength']) ? 'maxlength='.$field['maxlength'] : '' }}>{{ $deal->{$field['name']} }}</textarea>
                    @else
                        <textarea name="{{ $field['name'] }}_display" id="{{ $field['id'] ?? $field['name'] }}_display" 
                            class="{{ $field['name'] == 'comment' ? 'deal-comment-textarea' : '' }}"
                            disabled {{ isset($field['maxlength']) ? 'maxlength='.$field['maxlength'] : '' }}>{{ $deal->{$field['name']} }}</textarea>
                        <!-- Добавляем скрытое поле для отправки значения -->
                        <input type="hidden" name="{{ $field['name'] }}" value="{{ $deal->{$field['name']} }}">
                    @endif
                @elseif($field['type'] == 'file')
                    @if($field['name'] == 'avatar_path')
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
                        @php
                            $canUpload = $userRole == 'coordinator' || $userRole == 'admin' || 
                                        (!isset($field['role']) || (isset($field['role']) && in_array($userRole, $field['role'])));
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
                @elseif($field['type'] == 'date')
                    @if($field['name'] == 'created_date')
                        @if(in_array($userRole, ['coordinator', 'admin']))
                            <input type="date" name="{{ $field['name'] }}" id="{{ $field['id'] ?? $field['name'] }}" value="{{ $deal->{$field['name']} }}">
                        @else
                            <p class="deal-date-display">{{ \Carbon\Carbon::parse($deal->{$field['name']})->format('d.m.Y') }}</p>
                            <!-- Добавляем скрытое поле для отправки значения -->
                            <input type="hidden" name="{{ $field['name'] }}" value="{{ $deal->{$field['name']} }}">
                        @endif
                    @else
                        @if(isset($field['role']) && in_array($userRole, $field['role']))
                            <input type="date" name="{{ $field['name'] }}" id="{{ $field['id'] ?? $field['name'] }}" value="{{ $deal->{$field['name']} }}">
                        @else
                            <input type="date" name="{{ $field['name'] }}_display" id="{{ $field['id'] ?? $field['name'] }}_display" value="{{ $deal->{$field['name']} }}" disabled>
                            <!-- Добавляем скрытое поле для отправки значения -->
                            <input type="hidden" name="{{ $field['name'] }}" value="{{ $deal->{$field['name']} }}">
                        @endif
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