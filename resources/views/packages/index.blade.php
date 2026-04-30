@extends('layouts.app')

@section('title', 'Paket Snorkeling')

@section('content')
<section class="page-header">
    <div class="container">
        <span class="eyebrow">Paket</span>
        <h1>{{ $selectedDestination ? 'Paket untuk '.$selectedDestination->name : 'Paket Snorkeling' }}</h1>
        <p>
            @if($selectedDestination)
                Hanya paket yang tersedia untuk destinasi {{ $selectedDestination->name }} yang ditampilkan di halaman ini.
            @else
                Pilih paket snorkeling yang sesuai dengan kebutuhan perjalanan Anda. Ketersediaan kapal langsung terlihat di setiap paket.
            @endif
        </p>
    </div>
</section>
<section class="section">
    <div class="container">
        @include('packages.partials.package-filter')
        <div class="grid three">
            @forelse($packages as $package)
                @include('packages.partials.package-card', ['package' => $package])
            @empty
                <div class="card" style="grid-column: 1 / -1;">
                    <div class="card-body">
                        <h3 class="text-xl font-black text-slate-950">Paket tidak ditemukan</h3>
                        @if(($availabilityFilters['active'] ?? false) && $availabilityFilters['date'])
                            <p class="mt-2 leading-7 text-slate-600">Belum ada paket yang tersedia pada {{ \Illuminate\Support\Carbon::parse($availabilityFilters['date'])->translatedFormat('d M Y') }} untuk {{ $availabilityFilters['adult_count'] }} dewasa dan {{ $availabilityFilters['child_count'] }} anak.</p>
                        @else
                            <p class="mt-2 leading-7 text-slate-600">Coba ubah kata kunci, tanggal reservasi, atau pilihan harga yang Anda gunakan.</p>
                        @endif
                    </div>
                </div>
            @endforelse
        </div>
    </div>
</section>
@endsection
