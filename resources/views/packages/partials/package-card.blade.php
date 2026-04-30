@php($availability = $package->availabilitySummary())
@php($availabilityQuery = request()->only(['date', 'adult_count', 'child_count']))
@php($selectedDestinationId = request('destination'))
@php($packageDestinations = $package->destinations->sortBy('name')->values())
@php($resolvedDestinationId = $selectedDestinationId ?: ($packageDestinations->count() === 1 ? $packageDestinations->first()->id : null))
<article class="card stack-card h-full">
    <div class="image-placeholder relative overflow-hidden">
        <div class="absolute inset-0 bg-[radial-gradient(circle_at_20%_20%,rgba(255,255,255,.34),transparent_30%),linear-gradient(135deg,rgba(8,47,73,.2),rgba(20,184,166,.15))]"></div>
        <div class="relative">
            <span class="mb-3 inline-flex rounded-full bg-white/15 px-3 py-1 text-xs font-black uppercase tracking-wide text-cyan-50">{{ $package->duration }}</span>
            <div>{{ $package->name }}</div>
        </div>
    </div>
    <div class="card-body">
        <div class="card-copy">
            <h3 class="package-card-title">{{ $package->name }}</h3>
            <p class="package-card-description">{{ $package->short_description }}</p>
            <div class="meta package-card-meta">
                <span class="rounded-full bg-sky-50 px-3 py-1 font-bold text-sky-800">Private charter maks. {{ $package->capacity }} orang</span>
                <span class="badge {{ $availability['variant'] }}">{{ $availability['label'] }}</span>
            </div>
            <div class="package-card-price">
                <div class="price">Rp {{ number_format($package->price, 0, ',', '.') }}</div>
            </div>
            <div class="package-card-availability">
                <p class="text-sm leading-6 text-slate-600">{{ $availability['note'] }}</p>
                @if ($availability['next_start_at'])
                    <p class="mt-1 text-sm text-slate-500">Trip terdekat: {{ $availability['next_start_at']->format('d M Y H:i') }}</p>
                @endif
            </div>
            <div class="meta package-card-destinations">
                @foreach($packageDestinations->take(3) as $destination)
                    <span class="badge">{{ $destination->name }}</span>
                @endforeach
            </div>
        </div>
        <div class="card-actions">
            @if ($availability['remaining_slots'] > 0)
                @if ($resolvedDestinationId)
                    <a class="button primary w-full" href="{{ route('reservations.create', array_merge(['package' => $package->id, 'destination' => $resolvedDestinationId], $availabilityQuery)) }}">Reservasi Paket Ini</a>
                @else
                    <a class="button primary w-full" href="{{ route('packages.show', array_merge(['package' => $package], $availabilityQuery)) }}">Lihat Paket & Pilih Destinasi</a>
                @endif
            @else
                <span class="button secondary w-full opacity-70">Kapal Habis</span>
            @endif
        </div>
    </div>
</article>
