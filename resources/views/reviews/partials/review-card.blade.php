<article class="card">
    <div class="card-body">
        <div class="review-card-rating">
            <x-rating-stars :rating="$review->rating" />
        </div>
        <p class="mt-5 text-lg font-semibold leading-8 text-slate-700">"{{ $review->comment }}"</p>
        <div class="mt-6 border-t border-slate-100 pt-4">
            <h3 class="font-bold text-slate-950">{{ $review->user->name }}</h3>
            <p class="mt-1 text-sm text-slate-500">{{ $review->package->name }}</p>
        </div>
    </div>
</article>
