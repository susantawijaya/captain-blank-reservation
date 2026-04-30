@php
    $packageName = $review->package?->name ?? 'Paket tidak tersedia';
    $destinationName = $review->reservation?->destination?->name ?? 'Destinasi tidak tercatat';
@endphp

<article class="card stack-card">
    <div class="card-body">
        <div class="review-card-rating">
            <x-rating-stars :rating="$review->rating" />
        </div>
        <p class="review-card-copy">"{{ $review->comment }}"</p>
        <div class="review-card-footer">
            <h3>{{ $review->user->name }}</h3>
            <div class="review-card-details">
                <p><span class="review-card-label">Paket</span>{{ $packageName }}</p>
                <p><span class="review-card-label">Destinasi</span>{{ $destinationName }}</p>
            </div>
        </div>
    </div>
</article>
