@extends('layouts.customer')

@section('title', 'Riwayat Reservasi')
@section('customer_badge', 'Reservasi Saya')
@section('customer_intro', 'Semua reservasi pelanggan tersimpan di sini. Buka detail, cek status, atau edit pemesanan sebelum bukti pembayaran dikirim.')
@section('customer_actions')
    <a class="button secondary" href="{{ route('customer.dashboard') }}">Dashboard</a>
    <a class="button primary" href="{{ route('reservations.create') }}">Reservasi Baru</a>
@endsection

@section('customer_content')
<div class="customer-page-stack">
    <div class="section-head">
        <div>
            <h2>Daftar Reservasi</h2>
            <p>Pilih salah satu reservasi untuk melihat detail trip, bukti pembayaran, dan status terbaru dari admin.</p>
        </div>
    </div>

    @include('customer.reservations.partials.table', ['reservations' => $reservations])
</div>
@endsection
