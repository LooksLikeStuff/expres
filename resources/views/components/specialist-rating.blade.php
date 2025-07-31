@props(['rating' => 0, 'count' => 0, 'size' => 'md', 'showCount' => true])

@php
    $sizeClasses = [
        'sm' => 'text-sm',
        'md' => 'text-base',
        'lg' => 'text-lg',
        'xl' => 'text-xl'
    ];
    
    $sizeClass = $sizeClasses[$size] ?? $sizeClasses['md'];
    
    // Округляем рейтинг до ближайшей половины
    $roundedRating = round($rating * 2) / 2;
    $fullStars = floor($roundedRating);
    $halfStar = ($roundedRating - $fullStars) >= 0.5;
    $emptyStars = 5 - $fullStars - ($halfStar ? 1 : 0);
@endphp

<div class="specialist-rating {{ $sizeClass }}" title="Рейтинг: {{ number_format($rating, 1) }} из 5 ({{ $count }} {{ trans_choice('оценка|оценки|оценок', $count) }})">
    <div class="rating-stars">
        {{-- Полные звезды --}}
        @for($i = 0; $i < $fullStars; $i++)
            <i class="fas fa-star text-warning"></i>
        @endfor
        
        {{-- Половина звезды --}}
        @if($halfStar)
            <i class="fas fa-star-half-alt text-warning"></i>
        @endif
        
        {{-- Пустые звезды --}}
        @for($i = 0; $i < $emptyStars; $i++)
            <i class="far fa-star text-muted"></i>
        @endfor
    </div>
    
    @if($showCount && $rating > 0)
        <span class="rating-info">
            <span class="rating-value">{{ number_format($rating, 1) }}</span>
            @if($count > 0)
                <span class="rating-count">({{ $count }})</span>
            @endif
        </span>
    @endif
</div>

<style>
.specialist-rating {
    display: inline-flex;
    align-items: center;
    gap: 6px;
}

.specialist-rating .rating-stars {
    display: flex;
    gap: 2px;
}

.specialist-rating .rating-stars i {
    transition: all 0.2s ease;
}

.specialist-rating .rating-info {
    display: flex;
    align-items: center;
    gap: 4px;
    font-size: 0.9em;
    color: #6c757d;
}

.specialist-rating .rating-value {
    font-weight: 600;
    color: #495057;
}

.specialist-rating .rating-count {
    font-size: 0.85em;
    opacity: 0.8;
}

/* Анимация при наведении */
.specialist-rating:hover .rating-stars i.fas {
    transform: scale(1.1);
    filter: brightness(1.1);
}

/* Размеры */
.specialist-rating.text-sm .rating-stars i {
    font-size: 0.8em;
}

.specialist-rating.text-lg .rating-stars i {
    font-size: 1.2em;
}

.specialist-rating.text-xl .rating-stars i {
    font-size: 1.4em;
}
</style>
