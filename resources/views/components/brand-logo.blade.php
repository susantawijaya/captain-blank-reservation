@props(['alt' => 'Captain Blank logo'])

@php
    $logoCandidates = [
        'images/brand/logo-captain-blank.jpeg',
        
    ];

    $logoPath = collect($logoCandidates)->first(fn ($path) => file_exists(public_path($path)));
@endphp

<img {{ $attributes->merge(['class' => 'brand-logo']) }} src="{{ asset($logoPath) }}" alt="{{ $alt }}">
