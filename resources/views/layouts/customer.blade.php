@extends('layouts.app')

@php
    $customerUser = auth()->user();
    $customerPageTitle = trim($__env->yieldContent('customer_title', $__env->yieldContent('title', 'Portal Pelanggan')));
    $customerPageIntro = trim($__env->yieldContent('customer_intro', 'Kelola reservasi, pembayaran, pesan, review, dan profil Anda dari satu area pelanggan yang lebih rapi.'));
    $customerPageBadge = trim($__env->yieldContent('customer_badge', 'Portal Pelanggan'));
    $customerNavItems = [
        [
            'label' => 'Dashboard',
            'description' => 'Ringkasan aktivitas akun dan trip terbaru.',
            'route' => route('customer.dashboard'),
            'patterns' => ['customer.dashboard'],
        ],
        [
            'label' => 'Reservasi',
            'description' => 'Pantau status, pembayaran, dan detail trip.',
            'route' => route('customer.reservations.index'),
            'patterns' => ['customer.reservations.*'],
        ],
        [
            'label' => 'Pesan',
            'description' => 'Lihat pertanyaan dan balasan dari admin.',
            'route' => route('customer.messages.index'),
            'patterns' => ['customer.messages.*'],
        ],
        [
            'label' => 'Review',
            'description' => 'Tulis dan cek review pengalaman snorkeling.',
            'route' => route('customer.reviews.index'),
            'patterns' => ['customer.reviews.*'],
        ],
        [
            'label' => 'Profil',
            'description' => 'Perbarui data kontak dan identitas akun.',
            'route' => route('customer.profile.index'),
            'patterns' => ['customer.profile.*'],
        ],
    ];
@endphp

@section('content')
    <section class="customer-hero">
        <div class="container">
            <div class="customer-hero-card">
                <div class="customer-hero-inner">
                    <div class="customer-hero-copy">
                        <span class="eyebrow">{{ $customerPageBadge }}</span>
                        <h1>{{ $customerPageTitle }}</h1>
                        <p>{{ $customerPageIntro }}</p>
                    </div>
                    <div class="customer-hero-aside">
                        <div class="customer-profile-card">
                            <span class="customer-profile-kicker">Akun aktif</span>
                            <strong>{{ $customerUser?->name }}</strong>
                            <span>{{ $customerUser?->email }}</span>
                            <span>{{ $customerUser?->phone ?: 'WhatsApp belum diisi' }}</span>
                        </div>
                        <div class="customer-hero-actions">
                            @hasSection('customer_actions')
                                @yield('customer_actions')
                            @else
                                <a class="button secondary{{ request()->routeIs('customer.dashboard') ? ' is-active' : '' }}" href="{{ route('customer.dashboard') }}">Ringkasan Akun</a>
                                <a class="button primary{{ request()->routeIs('reservations.create') ? ' is-active' : '' }}" href="{{ route('reservations.create') }}">Buat Reservasi</a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="section customer-shell-section">
        <div class="container customer-shell">
            <aside class="customer-sidebar">
                <div class="customer-sidebar-card">
                    <div class="customer-sidebar-head">
                        <span class="customer-sidebar-kicker">Navigasi pelanggan</span>
                        <p>Pilih menu aktif dengan cepat. Halaman yang sedang dibuka akan tampil berbeda agar mudah dikenali.</p>
                    </div>
                    <nav class="customer-nav">
                        @foreach ($customerNavItems as $item)
                            @php($isActive = collect($item['patterns'])->contains(fn ($pattern) => request()->routeIs($pattern)))
                            <a class="{{ $isActive ? 'is-active' : '' }}" href="{{ $item['route'] }}" @if($isActive) aria-current="page" @endif>
                                <span>{{ $item['label'] }}</span>
                                <small>{{ $item['description'] }}</small>
                            </a>
                        @endforeach
                    </nav>
                </div>
            </aside>
            <div class="customer-main">
                @yield('customer_content')
            </div>
        </div>
    </section>
@endsection
