@extends('layouts.admin')

@section('title', 'Dashboard Admin')

@section('content')
<div class="mb-6 rounded-lg border border-sky-100 bg-gradient-to-br from-sky-900 to-teal-700 p-6 text-white shadow-lg shadow-sky-950/10">
    <span class="eyebrow mb-3 border-white/20 bg-white/10 text-cyan-100">Panel pengelola</span>
    <h1 class="text-3xl font-black tracking-tight">Dashboard Reservasi Captain Blank</h1>
    <p class="mt-2 max-w-3xl leading-7 text-sky-100">Pantau reservasi, pembayaran manual, jadwal snorkeling, dan review pelanggan dari satu halaman.</p>
</div>

<div class="stats-grid">
    <x-stat-card label="Total Reservasi" :value="$reservationCount" />
    <x-stat-card label="Menunggu Konfirmasi" :value="$waitingConfirmationReservations" />
    <x-stat-card label="Terkonfirmasi" :value="$confirmedReservations" />
    <x-stat-card label="Menunggu Bayar" :value="$waitingPaymentReservations" />
</div>
<div class="card"><div class="card-body">
    <div class="section-head mb-5">
        <div>
            <h2>Reservasi Terbaru</h2>
            <p>Data reservasi terbaru untuk operasional admin.</p>
        </div>
        <a class="button secondary" href="{{ route('admin.reservations.index') }}">Lihat Semua</a>
    </div>
    @include('admin.reservations.partials.table', ['reservations' => $recentReservations])
</div></div>
@endsection
