@extends('layouts.app')

@section('title', 'Reservasi Berhasil')

@section('content')
<section class="section">
    <div class="container">
        <div class="card"><div class="card-body">
            <h1>Reservasi berhasil dibuat</h1>
            <p>Status awal reservasi menjadi menunggu pembayaran. Silakan lanjutkan proses pembayaran sesuai instruksi.</p>
            @if (session('reservation_code'))
                <p class="mt-3"><strong>Kode Reservasi:</strong> {{ session('reservation_code') }}</p>
            @endif
            <div class="mt-5 flex flex-wrap gap-3">
                @if (session('reservation_code'))
                    <a class="button primary" href="{{ route('customer.reservations.show', ['reservation' => session('reservation_code')]) }}#pembayaran">Lanjut ke Detail Reservasi</a>
                @endif
                <a class="button secondary" href="{{ route('customer.reservations.index') }}">Lihat Riwayat</a>
            </div>
        </div></div>
    </div>
</section>
@endsection
