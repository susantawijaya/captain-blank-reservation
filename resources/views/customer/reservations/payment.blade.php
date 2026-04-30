@extends('layouts.customer')

@section('title', 'Upload Pembayaran')
@section('customer_badge', 'Pembayaran Reservasi')
@section('customer_intro', $reservation->code.' - '.$reservation->package->name)
@section('customer_actions')
    <a class="button secondary" href="{{ route('customer.reservations.show', $reservation) }}">Detail Reservasi</a>
    <a class="button primary" href="{{ route('customer.reservations.index') }}">Riwayat Reservasi</a>
@endsection

@section('customer_content')
<div class="grid gap-6 xl:grid-cols-[1.05fr_0.95fr]">
    <div>
        @include('customer.reservations.partials.payment-upload-panel', [
            'reservation' => $reservation,
            'company' => $company,
            'canUploadPayment' => $canUploadPayment,
        ])
    </div>
    <div class="soft-panel">
        <h2>Langkah Pembayaran</h2>
        <p class="mt-3 text-slate-600">Pastikan nominal transfer sesuai total reservasi, lalu unggah bukti pembayaran yang jelas agar proses verifikasi admin berjalan lebih cepat.</p>
        <div class="info-list mt-5">
            <div class="info-item">
                <span>Kode Reservasi</span>
                <strong>{{ $reservation->code }}</strong>
            </div>
            <div class="info-item">
                <span>Total Transfer</span>
                <strong>Rp {{ number_format($reservation->total_price, 0, ',', '.') }}</strong>
            </div>
            <div class="info-item">
                <span>Status Saat Ini</span>
                <strong>{{ $reservation->displayStatusLabel() }}</strong>
            </div>
        </div>
    </div>
</div>
@endsection
