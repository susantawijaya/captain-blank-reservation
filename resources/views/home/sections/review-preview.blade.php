<section class="section">
    <div class="container">
        <div class="section-head">
            <div>
                <span class="text-sm font-black uppercase tracking-[0.16em] text-sky-700">Review</span>
                <h2>Review Pelanggan Captain Blank.</h2>
                <p>Baca pengalaman pelanggan Captain Blank setelah trip snorkeling, mulai dari pelayanan, destinasi, hingga kenyamanan perjalanan mereka.</p>
            </div>
            <a class="button secondary" href="{{ route('reviews.index') }}">Semua Review</a>
        </div>
        <div class="grid three">
            @foreach($reviews as $review)
                @include('reviews.partials.review-card', ['review' => $review])
            @endforeach
        </div>
    </div>
</section>
