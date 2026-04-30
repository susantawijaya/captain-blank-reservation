@php
    $pageHeroImage = asset('images/site/nusa-lembongan-hero.jpg');
    $pageHeroMap = [
        'packages.show' => asset('images/site/lembongan-package-detail.jpg'),
        'packages.*' => asset('images/site/lembongan-packages.jpg'),
        'destinations.*' => 'https://commons.wikimedia.org/wiki/Special:FilePath/Nusa%20Lembongan%20Aerial%20cropped.jpg',
        'reviews.*' => 'https://commons.wikimedia.org/wiki/Special:FilePath/Nusa%20Lembongan%20Dream%20Beach.jpg',
        'gallery.*' => 'https://commons.wikimedia.org/wiki/Special:FilePath/Nusa%20Lembongan%20Mushroom%20Beach.JPG',
        'contact.*' => 'https://commons.wikimedia.org/wiki/Special:FilePath/Nusa%20Lembongan%20Nusa%20Ceningan%20bridge.jpg',
        'reservations.*' => 'https://commons.wikimedia.org/wiki/Special:FilePath/Nusa%20Lembongan%20-%20aqua%20waters%20of%20Mushroom%20Bay.jpg',
        'customer.*' => 'https://commons.wikimedia.org/wiki/Special:FilePath/Jungut%20Batu%20Village.JPG',
    ];

    foreach ($pageHeroMap as $pattern => $image) {
        if (request()->routeIs($pattern)) {
            $pageHeroImage = $image;
            break;
        }
    }
@endphp
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Captain Blank Reservation')</title>
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @else
        <script src="https://cdn.tailwindcss.com"></script>
    @endif
    <link rel="stylesheet" href="{{ asset('css/captain-blank.css') }}">
    <script defer src="{{ asset('js/password-ui.js') }}"></script>
    <script defer src="{{ asset('js/filter-auto-submit.js') }}"></script>
</head>
<body class="site-background text-slate-900" style="--page-hero-image: url('{{ $pageHeroImage }}');">
    @include('navbar')
    @include('partials.flash')

    <main>
        @yield('content')
    </main>

    @include('footer')
</body>
</html>
