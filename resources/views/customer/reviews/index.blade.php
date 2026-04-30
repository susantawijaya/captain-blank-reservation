@extends('layouts.customer')

@section('title', 'Review Saya')
@section('customer_badge', 'Review Pelanggan')
@section('customer_intro', 'Semua review snorkeling yang pernah Anda kirim terkumpul di sini dan siap ditinjau kembali.')
@section('customer_actions')
    <a class="button secondary" href="{{ route('customer.dashboard') }}">Dashboard</a>
    <a class="button primary" href="{{ route('customer.reviews.create') }}">Buat Review</a>
@endsection

@section('customer_content')
<div class="customer-page-stack">
    <div class="section-head">
        <div>
            <h2>Daftar Review</h2>
            <p>Semua review yang pernah Anda kirimkan akan tampil di bawah ini sebagai arsip pengalaman trip.</p>
        </div>
    </div>

    <div class="grid three">
        @forelse($reviews as $review)
            @include('reviews.partials.review-card', ['review' => $review])
        @empty
            <div class="card customer-full-span">
                <div class="card-body">
                    <p>Belum ada review yang dikirim.</p>
                </div>
            </div>
        @endforelse
    </div>
</div>
@endsection
