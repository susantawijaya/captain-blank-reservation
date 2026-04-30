@extends('layouts.customer')

@section('title', 'Edit '.$reservation->code)
@section('customer_badge', 'Edit Reservasi')
@section('customer_intro', $reservation->code.' masih bisa diubah karena bukti pembayaran belum dikirim.')
@section('customer_actions')
    <a class="button secondary" href="{{ route('customer.reservations.show', $reservation) }}">Kembali ke Detail</a>
    <a class="button primary" href="{{ route('customer.reservations.index') }}">Lihat Semua Reservasi</a>
@endsection

@section('customer_content')
<div class="grid gap-6 xl:grid-cols-[1fr_0.7fr]">
        <div class="card">
            <div class="card-body">
                @include('reservations.partials.reservation-form', [
                    'reservation' => $reservation,
                    'action' => route('customer.reservations.update', $reservation),
                    'method' => 'PUT',
                    'submitLabel' => 'Perbarui Reservasi',
                ])
            </div>
        </div>
        <div class="card">
            <div class="card-body">
                <h2>Aturan Perubahan</h2>
                <p class="mt-3 text-slate-600">Reservasi hanya bisa diubah atau dihapus sebelum Anda mengirim bukti pembayaran.</p>
                <p class="mt-3 text-slate-600">Setelah bukti pembayaran dikirim, data reservasi akan terkunci agar proses verifikasi admin tetap konsisten.</p>
            </div>
        </div>
    </div>
@endsection
