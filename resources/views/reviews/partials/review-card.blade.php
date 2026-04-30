<article class="card">
    <div class="card-body">
        <div class="flex items-center justify-between gap-3">
            <strong class="rounded-full bg-amber-50 px-3 py-1 text-sm font-black text-amber-700">Rating {{ $review->rating }}/5</strong>
            <x-status-badge :status="$review->status" />
        </div>
        <p class="mt-5 text-lg font-semibold leading-8 text-slate-700">"{{ $review->comment }}"</p>
        <div class="mt-6 border-t border-slate-100 pt-4">
            <h3 class="font-black text-slate-950">{{ $review->user->name }}</h3>
            <p class="mt-1 text-sm text-slate-500">{{ $review->package->name }}</p>
        </div>
    </div>
</article>
