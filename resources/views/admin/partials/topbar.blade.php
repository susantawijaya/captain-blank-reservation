@php
    $user = auth()->user();
    $pageTitle = trim($__env->yieldContent('title', 'Dashboard Admin'));
    $pageContext = match (true) {
        request()->routeIs('admin.dashboard') => 'Pantau ringkasan reservasi, pembayaran, dan aktivitas terbaru hari ini.',
        request()->routeIs('admin.reservations.*') => 'Kelola status trip pelanggan dan lanjutkan proses operasional.',
        request()->routeIs('admin.payments.*') => 'Verifikasi transfer pelanggan dengan alur yang lebih cepat dan jelas.',
        request()->routeIs('admin.customers.*') => 'Lihat data pelanggan dan jejak aktivitas reservasi mereka.',
        request()->routeIs('admin.packages.*') => 'Rapikan paket snorkeling yang tampil di website publik.',
        request()->routeIs('admin.destinations.*') => 'Atur destinasi agar katalog trip tetap akurat dan menarik.',
        request()->routeIs('admin.schedules.*') => 'Jaga ketersediaan jadwal snorkeling tetap sinkron.',
        request()->routeIs('admin.reviews.*') => 'Moderasi review pelanggan untuk menjaga kualitas tampilan publik.',
        request()->routeIs('admin.complaints.*') => 'Balas pesan pelanggan dan pantau tindak lanjutnya.',
        request()->routeIs('admin.faqs.*') => 'Perbarui FAQ website agar pertanyaan umum cepat terjawab.',
        request()->routeIs('admin.gallery.*') => 'Kelola galeri untuk menjaga tampilan website tetap segar.',
        request()->routeIs('admin.users.*') => 'Atur akses admin tambahan dengan kontrol yang aman.',
        default => 'Area pengelolaan internal Captain Blank.',
    };
@endphp

<header class="topbar">
    <div class="topbar-leading">
        <button class="admin-sidebar-toggle" type="button" aria-controls="adminSidebar" aria-expanded="false" aria-label="Buka menu admin" data-admin-sidebar-toggle>
            <span class="nav-toggle-icon" aria-hidden="true">
                <span></span>
                <span></span>
                <span></span>
            </span>
            <span class="nav-toggle-text">Menu</span>
        </button>
        <div class="topbar-copy">
            <span class="topbar-kicker">Panel Admin</span>
            <strong>{{ $pageTitle }}</strong>
            <p class="topbar-meta">{{ $pageContext }}</p>
            <div class="topbar-user">{{ $user?->isMasterAdmin() ? 'Master admin' : 'Admin operator' }} - {{ $user?->name }} - {{ $user?->email }}</div>
        </div>
    </div>
    <div class="topbar-actions">
        @hasSection('admin_topbar_actions')
            <div class="topbar-actions-page">
                @yield('admin_topbar_actions')
            </div>
        @endif
        <div class="topbar-actions-default">
            <a class="button secondary" href="{{ route('home') }}">Lihat Website</a>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button class="button primary" type="submit">Logout</button>
            </form>
        </div>
    </div>
</header>
