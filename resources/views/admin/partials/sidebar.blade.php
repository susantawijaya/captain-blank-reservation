@php
    $navigationGroups = [
        [
            'label' => 'Operasional',
            'items' => [
                ['label' => 'Dashboard', 'route' => route('admin.dashboard'), 'patterns' => ['admin.dashboard']],
                ['label' => 'Reservasi', 'route' => route('admin.reservations.index'), 'patterns' => ['admin.reservations.*']],
                ['label' => 'Pembayaran', 'route' => route('admin.payments.index'), 'patterns' => ['admin.payments.*']],
                ['label' => 'Jadwal', 'route' => route('admin.schedules.index'), 'patterns' => ['admin.schedules.*']],
                ['label' => 'Pelanggan', 'route' => route('admin.customers.index'), 'patterns' => ['admin.customers.*']],
            ],
        ],
        [
            'label' => 'Master Data',
            'items' => array_filter([
                ['label' => 'Paket', 'route' => route('admin.packages.index'), 'patterns' => ['admin.packages.*']],
                ['label' => 'Destinasi', 'route' => route('admin.destinations.index'), 'patterns' => ['admin.destinations.*']],
                auth()->user()?->isMasterAdmin()
                    ? ['label' => 'Admin', 'route' => route('admin.users.index'), 'patterns' => ['admin.users.*']]
                    : null,
            ]),
        ],
        [
            'label' => 'Konten Website',
            'items' => [
                ['label' => 'Review', 'route' => route('admin.reviews.index'), 'patterns' => ['admin.reviews.*']],
                ['label' => 'Pesan Masuk', 'route' => route('admin.complaints.index'), 'patterns' => ['admin.complaints.*']],
                ['label' => 'FAQ Website', 'route' => route('admin.faqs.index'), 'patterns' => ['admin.faqs.*']],
                ['label' => 'Galeri', 'route' => route('admin.gallery.index'), 'patterns' => ['admin.gallery.*']],
            ],
        ],
    ];
@endphp

<aside class="sidebar" id="adminSidebar" data-admin-sidebar aria-label="Navigasi admin">
    <div class="sidebar-head">
        <a class="brand brand-inverse" href="{{ route('admin.dashboard') }}">
            <x-brand-logo />
            <span>
                <span class="block">Captain Blank</span>
                <span class="sidebar-brand-subtitle">Admin Workspace</span>
            </span>
        </a>
        <button class="sidebar-close" type="button" aria-label="Tutup menu admin" data-admin-sidebar-close>
            <span aria-hidden="true">&times;</span>
        </button>
    </div>

    @foreach ($navigationGroups as $group)
        <div class="sidebar-group">
            <p class="sidebar-group-label">{{ $group['label'] }}</p>
            <nav class="sidebar-nav">
                @foreach ($group['items'] as $item)
                    @php($isActive = collect($item['patterns'])->contains(fn ($pattern) => request()->routeIs($pattern)))
                    <a class="{{ $isActive ? 'is-active' : '' }}" href="{{ $item['route'] }}" @if($isActive) aria-current="page" @endif>{{ $item['label'] }}</a>
                @endforeach
            </nav>
        </div>
    @endforeach
</aside>
