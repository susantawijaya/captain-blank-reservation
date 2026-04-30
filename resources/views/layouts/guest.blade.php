@php
    $pageHeroImage = 'https://commons.wikimedia.org/wiki/Special:FilePath/Nusa%20Lembongan%2C%20Bali.jpg';
    $authEyebrow = trim($__env->yieldContent('auth_eyebrow', 'Portal Akses'));
    $authHeading = trim($__env->yieldContent('auth_heading', 'Masuk ke sistem Captain Blank'));
    $authDescription = trim($__env->yieldContent('auth_description', 'Gunakan portal ini untuk login pelanggan, akses panel admin, atau mengatur ulang password dengan tampilan yang konsisten.'));
    $authShowcaseTitle = trim($__env->yieldContent('auth_showcase_title', 'Satu bahasa visual untuk seluruh portal.'));
    $authShowcaseText = trim($__env->yieldContent('auth_showcase_text', 'Area login, registrasi, dan reset password sekarang mengikuti warna serta gaya panel yang sama dengan dashboard admin dan pelanggan.'));
    $authCompactMode = trim($__env->yieldContent('auth_compact', '')) !== '';
@endphp
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Masuk - Captain Blank')</title>
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @else
        <script src="https://cdn.tailwindcss.com"></script>
    @endif
    <link rel="stylesheet" href="{{ asset('css/captain-blank.css') }}">
    <script defer src="{{ asset('js/password-ui.js') }}"></script>
    <script defer src="{{ asset('js/flash-toast.js') }}"></script>
</head>
<body class="site-background text-slate-900" style="--page-hero-image: url('{{ $pageHeroImage }}');">
    <main class="section auth-shell-section">
        <div class="container auth-shell{{ $authCompactMode ? ' compact' : '' }}">
            @unless ($authCompactMode)
                <section class="auth-showcase">
                    <div class="auth-showcase-head">
                        <a class="brand brand-inverse" href="{{ route('home') }}">
                            <x-brand-logo />
                            <span>
                                <span class="block">Captain Blank</span>
                                <span class="sidebar-brand-subtitle">Reservation Portal</span>
                            </span>
                        </a>
                        <a class="button secondary auth-ghost-button" href="{{ route('home') }}">Kembali ke Website</a>
                    </div>

                    <div class="auth-showcase-copy">
                        <span class="eyebrow">{{ $authEyebrow }}</span>
                        <h1>{{ $authHeading }}</h1>
                        <p>{{ $authDescription }}</p>
                    </div>

                    <div class="auth-showcase-panel">
                        <h2>{{ $authShowcaseTitle }}</h2>
                        <p>{{ $authShowcaseText }}</p>
                        <div class="auth-showcase-notes">
                            <article class="auth-note">
                                <span>Warna Konsisten</span>
                                <strong>Palet laut biru dan teal yang sama dengan admin serta portal pelanggan.</strong>
                            </article>
                            <article class="auth-note">
                                <span>Alur Lebih Jelas</span>
                                <strong>Masuk, daftar, dan reset password memakai struktur panel yang seragam.</strong>
                            </article>
                            <article class="auth-note">
                                <span>Siap Multi Peran</span>
                                <strong>Sistem akan mengarahkan akun ke dashboard yang sesuai setelah login.</strong>
                            </article>
                        </div>
                    </div>
                </section>
            @endunless

            <section class="auth-stage{{ $authCompactMode ? ' compact' : '' }}">
                @include('partials.flash')
                <div class="auth-card">
                    <div class="auth-card-body">
                        @yield('content')
                    </div>
                </div>
            </section>
        </div>
    </main>
</body>
</html>
