@extends('layouts.app')

@section('title', 'Review Pelanggan')

@section('content')
<section class="page-header">
    <div class="container">
        <span class="eyebrow">Review</span>
        <h1>Ulasan Pelanggan</h1>
        <p>Baca pengalaman pelanggan Captain Blank setelah trip snorkeling, mulai dari pelayanan, destinasi, hingga kenyamanan perjalanan mereka.</p>
    </div>
</section>
<section class="section">
    <div class="container review-filter-stack">
        <div class="card review-filter-card">
            <div class="card-body">
                <form class="form-grid" method="GET" action="{{ route('reviews.index') }}" data-auto-filter-form data-auto-submit-delay="200">
                    <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                        <div class="field">
                            <label for="package">Filter paket</label>
                            <select id="package" name="package" data-auto-filter-change>
                                <option value="">Semua paket</option>
                                @foreach($packages as $package)
                                    <option value="{{ $package->id }}" @selected((string) ($filters['package'] ?? '') === (string) $package->id)>{{ $package->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="field">
                            <label for="destination">Filter destinasi</label>
                            <select id="destination" name="destination" data-auto-filter-change>
                                <option value="">Semua destinasi</option>
                                @foreach($destinations as $destination)
                                    <option value="{{ $destination->id }}" @selected((string) ($filters['destination'] ?? '') === (string) $destination->id)>{{ $destination->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="field">
                            <label for="rating">Filter bintang</label>
                            <select id="rating" name="rating" data-auto-filter-change>
                                <option value="all" @selected(($filters['rating'] ?? 'all') === 'all')>Semua bintang</option>
                                <option value="5" @selected(($filters['rating'] ?? 'all') === '5')>5 bintang</option>
                                <option value="4" @selected(($filters['rating'] ?? 'all') === '4')>4 bintang</option>
                                <option value="3" @selected(($filters['rating'] ?? 'all') === '3')>3 bintang</option>
                                <option value="2" @selected(($filters['rating'] ?? 'all') === '2')>2 bintang</option>
                                <option value="1" @selected(($filters['rating'] ?? 'all') === '1')>1 bintang</option>
                            </select>
                        </div>
                        <div class="field">
                            <label for="order">Urutkan</label>
                            <select id="order" name="order" data-auto-filter-change>
                                <option value="latest" @selected(($filters['order'] ?? 'latest') === 'latest')>Terbaru</option>
                                <option value="oldest" @selected(($filters['order'] ?? 'latest') === 'oldest')>Terlama</option>
                            </select>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>
<section class="section review-results-section">
    <div class="container grid three">
        @forelse($reviews as $review)
            @include('reviews.partials.review-card', ['review' => $review])
        @empty
            <div class="card grid-full-span">
                <div class="card-body">
                    <p>Belum ada review yang cocok dengan filter yang dipilih.</p>
                </div>
            </div>
        @endforelse
    </div>
</section>
@endsection
