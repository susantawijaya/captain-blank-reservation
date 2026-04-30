<article class="card">
    <div class="card-body">
        <h3>{{ $schedule->package->name }}</h3>
        <p>{{ $schedule->start_at->translatedFormat('d M Y') }} | {{ $schedule->start_at->format('H:i') }} - {{ $schedule->end_at->format('H:i') }}</p>
        <div class="meta">
            <span>Private charter maks. {{ $schedule->capacity }} orang</span>
            <span>{{ $schedule->availableBoats() }} / {{ $schedule->boat_count }} kapal</span>
            <x-status-badge :status="$schedule->status" />
        </div>
        @if($schedule->weather_note)
            <p>{{ $schedule->weather_note }}</p>
        @endif
    </div>
</article>
