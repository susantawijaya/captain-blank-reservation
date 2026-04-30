<article class="card">
    <div class="image-placeholder min-h-40">{{ $destination->name }}</div>
    <div class="card-body">
        <div class="flex items-start justify-between gap-3">
            <h3 class="text-xl font-black text-slate-950">{{ $destination->name }}</h3>
            <span class="badge">{{ $destination->difficulty }}</span>
        </div>
        <p class="mt-3 line-clamp-3 leading-7 text-slate-600">{{ $destination->description }}</p>
        <div class="meta">
            <span>{{ $destination->packages_count ?? $destination->packages()->where('status', 'aktif')->count() }} paket tersedia</span>
        </div>
        <a class="button secondary w-full" href="{{ route('packages.index', ['destination' => $destination->id]) }}">Lihat Paket Destinasi Ini</a>
    </div>
</article>
