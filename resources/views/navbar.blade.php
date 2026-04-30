@php
    $user = auth()->user();
    $navItems = [
        [
            'label' => 'Beranda',
            'route' => route('home'),
            'patterns' => ['home'],
        ],
        [
            'label' => 'Paket',
            'route' => route('packages.index'),
            'patterns' => ['packages.*'],
        ],
        [
            'label' => 'Destinasi',
            'route' => route('destinations.index'),
            'patterns' => ['destinations.*'],
        ],
        [
            'label' => 'Review',
            'route' => route('reviews.index'),
            'patterns' => ['reviews.*'],
        ],
        [
            'label' => 'Galeri',
            'route' => route('gallery.index'),
            'patterns' => ['gallery.*'],
        ],
        [
            'label' => 'Kontak',
            'route' => route('contact.index'),
            'patterns' => ['contact.*'],
        ],
    ];
@endphp

<header class="navbar">
    <div class="container navbar-inner">
        <a class="brand" href="{{ route('home') }}">
            <x-brand-logo />
            <span>
                <span class="block">Captain Blank</span>
                <span class="block text-xs font-bold uppercase tracking-[0.16em] text-sky-600">Snorkeling</span>
            </span>
        </a>
        <button class="nav-toggle" type="button" aria-expanded="false" aria-controls="siteNavigationPanel" aria-label="Buka menu navigasi" data-nav-toggle>
            <span class="nav-toggle-icon" aria-hidden="true">
                <span></span>
                <span></span>
                <span></span>
            </span>
            <span class="nav-toggle-text">Menu</span>
        </button>
        <div class="navbar-panel" id="siteNavigationPanel" data-nav-panel>
            <nav class="nav-links">
                @foreach ($navItems as $item)
                    @php($isActive = collect($item['patterns'])->contains(fn ($pattern) => request()->routeIs($pattern)))
                    <a class="nav-link {{ $isActive ? 'is-active' : '' }}" href="{{ $item['route'] }}" @if($isActive) aria-current="page" @endif>{{ $item['label'] }}</a>
                @endforeach
            </nav>
            <div class="nav-actions">
                @auth
                    @if ($user?->isAdmin())
                        <a class="button secondary{{ request()->routeIs('admin.*') ? ' is-active' : '' }}" href="{{ route('admin.dashboard') }}" @if(request()->routeIs('admin.*')) aria-current="page" @endif>Dashboard Admin</a>
                    @else
                        <a class="button secondary{{ request()->routeIs('customer.*') ? ' is-active' : '' }}" href="{{ route('customer.dashboard') }}" @if(request()->routeIs('customer.*')) aria-current="page" @endif>Dashboard</a>
                        <a class="button primary{{ request()->routeIs('reservations.create') ? ' is-active' : '' }}" href="{{ route('reservations.create') }}" @if(request()->routeIs('reservations.create')) aria-current="page" @endif>Reservasi</a>
                    @endif

                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button class="button secondary" type="submit">Logout</button>
                    </form>
                @else
                    <a class="button secondary{{ request()->routeIs('login') ? ' is-active' : '' }}" href="{{ route('login') }}" @if(request()->routeIs('login')) aria-current="page" @endif>Login</a>
                    <a class="button secondary{{ request()->routeIs('register') ? ' is-active' : '' }}" href="{{ route('register') }}" @if(request()->routeIs('register')) aria-current="page" @endif>Buat Akun</a>
                    <a class="button primary{{ request()->routeIs('reservations.create') ? ' is-active' : '' }}" href="{{ route('reservations.create') }}" @if(request()->routeIs('reservations.create')) aria-current="page" @endif>Reservasi</a>
                @endauth
            </div>
        </div>
    </div>
</header>
