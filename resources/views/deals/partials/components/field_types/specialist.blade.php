@php
    $roleName = str_replace('_id', '', $field['name']); // architect, designer, visualizer
    switch($roleName) {
        case 'architect':
            $userRole = 'architect';
            $placeholderText = 'Выберите архитектора...';
            break;
        case 'designer':
            $userRole = 'designer';
            $placeholderText = 'Выберите дизайнера...';
            break;
        case 'visualizer':
            $userRole = 'visualizer';
            $placeholderText = 'Выберите визуализатора...';
            break;
        default:
            $userRole = $roleName;
            $placeholderText = 'Выберите специалиста...';
    }
@endphp

@if(in_array(Auth::user()->status, ['admin', 'coordinator']))
    <select name="{{ $field['name'] }}" id="{{ $field['name'] }}" class="form-control select2-specialist" 
            data-role="{{ $userRole }}" data-placeholder="{{ $placeholderText }}">
        @if($deal->{$field['name']})
            @php
                $selectedUser = \App\Models\User::find($deal->{$field['name']});
            @endphp
            @if($selectedUser)
                <option value="{{ $selectedUser->id }}" selected data-rating="{{ $selectedUser->rating ?? 0 }}">{{ $selectedUser->name }} @if($selectedUser->rating) ⭐ {{ number_format($selectedUser->rating, 1) }} @endif</option>
            @endif
        @endif
    </select>
@else
    @php
        $userId = $deal->{$field['name']};
        $userName = null;
        $userRating = 0;
        
        if ($userId) {
            $user = \App\Models\User::find($userId);
            if ($user) {
                $userName = $user->name;
                $userRating = $user->rating ?? 0;
            } else {
                $userName = 'Не найден';
            }
        } else {
            $userName = 'Не назначен';
        }
    @endphp
    <input type="text" value="{{ $userName }} @if($userRating) ⭐ {{ number_format($userRating, 1) }} @endif" disabled class="form-control">
    <input type="hidden" name="{{ $field['name'] }}" value="{{ $userId }}">
@endif
