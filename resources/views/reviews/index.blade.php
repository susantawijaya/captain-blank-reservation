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
    <div class="container grid three">
        @foreach($reviews as $review)
            @include('reviews.partials.review-card', ['review' => $review])
        @endforeach
    </div>
</section>
@endsection
