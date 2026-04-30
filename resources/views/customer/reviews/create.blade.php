@extends('layouts.customer')

@section('title', 'Buat Review')
@section('customer_badge', 'Tulis Review')
@section('customer_intro', 'Pilih reservasi yang sudah selesai lalu ceritakan pengalaman snorkeling Anda dengan tampilan form yang lebih nyaman.')
@section('customer_actions')
    <a class="button secondary" href="{{ route('customer.reviews.index') }}">Lihat Review Saya</a>
    <a class="button primary" href="{{ route('customer.reservations.index') }}">Cek Reservasi</a>
@endsection

@section('customer_content')
<div class="grid gap-6 xl:grid-cols-[1fr_0.7fr]">
    <div class="card">
        <div class="card-body">
            @if ($reservations->isEmpty())
                <p>Belum ada reservasi selesai yang dapat direview.</p>
            @else
                @include('customer.reviews.partials.form')
            @endif
        </div>
    </div>
    <div class="soft-panel">
        <h2>Tips Review</h2>
        <p class="mt-3 text-slate-600">Ceritakan pengalaman yang paling terasa, seperti pelayanan kru, kondisi destinasi, atau kenyamanan trip. Review yang jelas membantu admin dan calon pelanggan lain.</p>
        <div class="info-list mt-5">
            <div class="info-item">
                <span>Isi yang disarankan</span>
                <strong>Kualitas trip, pelayanan, dan kesan destinasi.</strong>
            </div>
            <div class="info-item">
                <span>Waktu terbaik menulis</span>
                <strong>Setelah trip selesai saat detail pengalaman masih segar.</strong>
            </div>
        </div>
    </div>
</div>
@endsection
