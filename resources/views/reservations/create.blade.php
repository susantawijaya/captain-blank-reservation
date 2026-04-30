@extends('layouts.app')

@section('title', 'Buat Reservasi')

@section('content')
<section class="page-header">
    <div class="container">
        <span class="eyebrow">Reservasi</span>
        <h1>Buat Reservasi</h1>
        <p>Lengkapi detail reservasi berdasarkan paket, destinasi, tanggal, dan jam trip yang memang masih tersedia. Anda harus login sebagai pelanggan untuk melanjutkan pemesanan.</p>
    </div>
</section>
<section class="section">
    <div class="container grid gap-6 xl:grid-cols-[1fr_0.7fr]">
        <div class="card"><div class="card-body">
            @include('reservations.partials.reservation-form', [
                'action' => route('reservations.store'),
                'method' => 'POST',
                'submitLabel' => 'Simpan Reservasi',
            ])
        </div></div>
        <div class="card"><div class="card-body">@include('reservations.partials.price-summary')</div></div>
    </div>
</section>
@endsection
