@props(['rating' => 0, 'size' => 'md', 'showValue' => false])

@php
    $value = max(0, min(5, (int) $rating));
    $sizeClass = match ($size) {
        'sm' => 'rating-stars--sm',
        'lg' => 'rating-stars--lg',
        default => '',
    };
@endphp

<span {{ $attributes->class(['rating-stars', $sizeClass]) }} role="img" aria-label="Rating {{ $value }} dari 5 bintang">
    @for ($star = 1; $star <= 5; $star++)
        <span class="rating-star{{ $star <= $value ? ' is-filled' : '' }}">★</span>
    @endfor
    @if ($showValue)
        <span class="rating-stars-value">{{ $value }}/5</span>
    @endif
</span>
