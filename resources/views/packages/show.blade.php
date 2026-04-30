@extends('layouts.app')

@section('title', $package->name)

@section('content')
@php($availability = $package->availabilitySummary())
@php($availabilityQuery = request()->only(['date', 'adult_count', 'child_count']))
@php($packageDestinations = $package->destinations->sortBy('name')->values())
@php($resolvedDestination = $selectedDestination ?? ($packageDestinations->count() === 1 ? $packageDestinations->first() : null))
@php($reservationQuery = array_merge($availabilityQuery, $resolvedDestination ? ['destination' => $resolvedDestination->id] : []))
<section class="page-header">
    <div class="container">
        <span class="eyebrow">Detail paket</span>
        <h1>{{ $package->name }}</h1>
        <p>{{ $package->short_description }}</p>
        @if(($availabilityFilters['active'] ?? false) && $availabilityFilters['date'])
            <p class="mt-4 text-sm text-sky-100/80">Hasil disesuaikan untuk {{ \Illuminate\Support\Carbon::parse($availabilityFilters['date'])->translatedFormat('d M Y') }}, {{ $availabilityFilters['adult_count'] }} dewasa, dan {{ $availabilityFilters['child_count'] }} anak.</p>
        @endif
        @if($resolvedDestination)
            <p class="mt-2 text-sm text-sky-100/80">Destinasi yang sedang dipilih: <strong class="text-white">{{ $resolvedDestination->name }}</strong>.</p>
        @endif
    </div>
</section>
<section class="section">
    <div class="container grid gap-6 xl:grid-cols-[1.4fr_0.8fr]">
        <article class="card">
            <div class="image-placeholder">{{ $package->name }}</div>
            <div class="card-body">
                <p>{{ $package->description }}</p>
                <h3>Destinasi</h3>
                <div class="mt-4 flex flex-wrap gap-3">
                    @foreach($packageDestinations as $destination)
                        <a
                            class="{{ $resolvedDestination && $resolvedDestination->is($destination) ? 'button primary' : 'button secondary' }}"
                            href="{{ route('packages.show', array_merge(['package' => $package], $availabilityQuery, ['destination' => $destination->id])) }}"
                        >
                            {{ $destination->name }}
                        </a>
                    @endforeach
                </div>
                <h3>Fasilitas</h3>
                <p>{{ $package->facilities }}</p>
            </div>
        </article>
        <aside class="card">
            <div class="card-body">
                <div class="price">Rp {{ number_format($package->price, 0, ',', '.') }}</div>
                <p>{{ $package->duration }} | private charter maks. {{ $package->capacity }} orang</p>
                <div class="mt-4 flex flex-wrap items-center gap-3">
                    <span class="badge {{ $availability['variant'] }}">{{ $availability['label'] }}</span>
                    @if ($availability['remaining_slots'] > 0)
                        <span class="text-sm text-slate-600">{{ $availability['note'] }}</span>
                    @endif
                </div>
                @if ($availability['remaining_slots'] > 0)
                    @if ($resolvedDestination)
                        <a class="button primary mt-4" href="{{ route('reservations.create', array_merge(['package' => $package->id], $reservationQuery)) }}" style="width: 100%;">Reservasi Paket Ini</a>
                    @else
                        <div class="mt-4 rounded-lg border border-amber-200 bg-amber-50 p-4 text-sm font-medium text-amber-900">
                            Pilih dulu salah satu destinasi di atas supaya reservasi mengarah ke spot yang benar.
                        </div>
                    @endif
                @else
                    <div class="mt-4 rounded-lg border border-red-200 bg-red-50 p-4 text-sm font-medium text-red-700">
                        Paket ini sedang habis karena belum ada jadwal terbuka atau semua kapal pada jadwal aktif sudah terpakai.
                    </div>
                @endif
                <h3 class="mt-6">Ketersediaan Trip</h3>
                @forelse($package->schedules as $schedule)
                    <div class="schedule-item">
                        <div class="schedule-item-head">
                            <p class="font-semibold text-slate-900">{{ $schedule->start_at->translatedFormat('d M Y') }} | {{ $schedule->start_at->format('H:i') }} - {{ $schedule->end_at->format('H:i') }}</p>
                            <x-status-badge :status="$schedule->status" />
                        </div>
                        <p class="mt-2 text-sm text-slate-600">Tersedia {{ $schedule->availableBoats() }} dari {{ $schedule->boat_count }} kapal untuk trip ini. Maksimal {{ $schedule->capacity }} orang per kapal.</p>
                        @if ($schedule->hasRemainingSlots() && $resolvedDestination)
                            <a class="button secondary mt-3 w-full" href="{{ route('reservations.create', array_merge(['package' => $package->id, 'schedule' => $schedule->id], $reservationQuery)) }}">Pilih Jadwal Ini</a>
                        @elseif($schedule->hasRemainingSlots())
                            <p class="mt-3 text-sm font-medium text-amber-700">Pilih destinasi dulu untuk melanjutkan ke reservasi jadwal ini.</p>
                        @else
                            <p class="mt-3 text-sm font-medium text-red-700">Semua kapal pada jadwal ini sudah terpakai.</p>
                        @endif
                    </div>
                @empty
                    <p class="mt-3 text-sm text-slate-600">Belum ada jadwal yang bisa dipesan untuk paket ini.</p>
                @endforelse
            </div>
        </aside>
    </div>
</section>
@endsection
