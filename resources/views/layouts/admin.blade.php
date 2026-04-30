@php
    $pageHeroImage = 'https://commons.wikimedia.org/wiki/Special:FilePath/Nusa%20Lembongan%20Mangroves%20and%20Jukung%20Boat.jpg';
@endphp
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Admin Captain Blank')</title>
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @else
        <script src="https://cdn.tailwindcss.com"></script>
    @endif
    <link rel="stylesheet" href="{{ asset('css/captain-blank.css') }}">
    <script defer src="{{ asset('js/password-ui.js') }}"></script>
    <script defer src="{{ asset('js/filter-auto-submit.js') }}"></script>
    <script defer src="{{ asset('js/flash-toast.js') }}"></script>
    <script defer src="{{ asset('js/responsive-ui.js') }}"></script>
</head>
<body class="site-background admin-body text-slate-900" style="--page-hero-image: url('{{ $pageHeroImage }}');">
    <div class="admin-shell" data-admin-shell>
        <button class="admin-sidebar-backdrop" type="button" aria-label="Tutup menu admin" data-admin-sidebar-backdrop></button>
        @include('admin.partials.sidebar')
        <div class="admin-main">
            @include('admin.partials.topbar')
            <main class="admin-content">
                @include('partials.flash')
                @yield('content')
            </main>
        </div>
    </div>
</body>
</html>
