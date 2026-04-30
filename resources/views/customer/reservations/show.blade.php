@extends('layouts.customer')

@section('title', 'Detail '.$reservation->code)
@section('customer_title', 'Reservasi '.$reservation->code)
@section('customer_badge', 'Detail Reservasi')
@section('customer_intro', $reservation->package->name)
@section('customer_actions')
    <a class="button secondary" href="{{ route('customer.reservations.index') }}">Riwayat Reservasi</a>
    @if ($canUploadPayment)
        <a class="button primary" href="{{ route('customer.reservations.payment', $reservation) }}">Upload Pembayaran</a>
    @elseif ($canManageReservation)
        <a class="button primary" href="{{ route('customer.reservations.edit', $reservation) }}">Edit Reservasi</a>
    @endif
@endsection

@section('customer_content')
<div class="grid gap-6 xl:grid-cols-[1.3fr_0.7fr]">
        <div class="card">
            <div class="card-body">
                <h2>Ringkasan Reservasi</h2>
                <div class="mt-5 grid gap-4 md:grid-cols-2">
                    <div>
                        <p class="text-sm font-bold uppercase tracking-wide text-slate-500">Status Reservasi</p>
                        <div class="mt-2"><x-status-badge :status="$reservation->status" :label="$reservation->displayStatusLabel()" /></div>
                        @if($reservation->statusContextNote())
                            <p class="mt-2 text-sm text-slate-500">{{ $reservation->statusContextNote() }}</p>
                        @endif
                    </div>
                    <div>
                        <p class="text-sm font-bold uppercase tracking-wide text-slate-500">Status Pembayaran</p>
                        <div class="mt-2">
                            @if($reservation->payment)
                                <x-status-badge :status="$reservation->payment->status" />
                            @else
                                <span class="badge">Belum ada pembayaran</span>
                            @endif
                        </div>
                    </div>
                    <div>
                        <p class="text-sm font-bold uppercase tracking-wide text-slate-500">Paket</p>
                        <p class="mt-2 font-semibold text-slate-900">{{ $reservation->package->name }}</p>
                    </div>
                    <div>
                        <p class="text-sm font-bold uppercase tracking-wide text-slate-500">Destinasi</p>
                        <p class="mt-2 font-semibold text-slate-900">{{ $reservation->destination?->name ?? 'Belum ditentukan' }}</p>
                    </div>
                    <div>
                        <p class="text-sm font-bold uppercase tracking-wide text-slate-500">Tanggal Trip</p>
                        <p class="mt-2 font-semibold text-slate-900">{{ $reservation->booking_date->format('d M Y') }}</p>
                    </div>
                    <div>
                        <p class="text-sm font-bold uppercase tracking-wide text-slate-500">Jam Trip</p>
                        <p class="mt-2 font-semibold text-slate-900">
                            @if($reservation->schedule)
                                {{ $reservation->schedule->start_at->format('H:i') }} - {{ $reservation->schedule->end_at->format('H:i') }}
                            @else
                                Menunggu jadwal
                            @endif
                        </p>
                    </div>
                    <div>
                        <p class="text-sm font-bold uppercase tracking-wide text-slate-500">Peserta</p>
                        <p class="mt-2 font-semibold text-slate-900">{{ $reservation->adult_count }} dewasa, {{ $reservation->child_count }} anak</p>
                        <p class="text-sm text-slate-500">Total {{ $reservation->participants }} orang</p>
                    </div>
                    <div>
                        <p class="text-sm font-bold uppercase tracking-wide text-slate-500">Nominal Transfer</p>
                        <p class="mt-2 text-2xl font-black text-sky-900">Rp {{ number_format($reservation->total_price, 0, ',', '.') }}</p>
                    </div>
                    <div>
                        <p class="text-sm font-bold uppercase tracking-wide text-slate-500">Lokasi Jemput</p>
                        <p class="mt-2 font-semibold text-slate-900">{{ $reservation->pickup_location ?: 'Belum diisi' }}</p>
                    </div>
                    <div>
                        <p class="text-sm font-bold uppercase tracking-wide text-slate-500">Pembayaran Ke</p>
                        @if($company?->bank_name && $company?->bank_account_number)
                            <p class="mt-2 font-semibold text-slate-900">{{ $company->bank_name }}</p>
                            <p class="text-sm text-slate-500">{{ $company->bank_account_number }} a.n. {{ $company->bank_account_name }}</p>
                        @else
                            <p class="mt-2 text-sm text-slate-500">Informasi rekening belum diisi admin.</p>
                        @endif
                    </div>
                </div>

                @if($reservation->payment?->notes)
                    <div class="mt-6 rounded-2xl border border-slate-200 bg-slate-50 p-4">
                        <p class="text-sm font-bold uppercase tracking-wide text-slate-500">Catatan Pembayaran</p>
                        <p class="mt-2 text-slate-700">{{ $reservation->payment->notes }}</p>
                    </div>
                @endif

                @if($reservation->payment?->proof_image)
                    <div class="mt-6">
                        <p class="text-sm font-bold uppercase tracking-wide text-slate-500">Bukti Pembayaran</p>
                        <img class="mt-3 w-full rounded-2xl border border-slate-200 object-cover" src="{{ asset($reservation->payment->proof_image) }}" alt="Bukti pembayaran {{ $reservation->code }}">
                    </div>
                @endif

                @if($canManageReservation)
                    <div class="mt-6 rounded-2xl border border-sky-200 bg-sky-50 p-4 text-sm text-sky-900">
                        Reservasi ini masih bisa diubah atau dihapus karena bukti pembayaran belum dikirim.
                    </div>
                    <div class="mt-4 flex flex-wrap gap-3">
                        <a class="button secondary" href="{{ route('customer.reservations.edit', $reservation) }}">Edit Reservasi</a>
                        <form method="POST" action="{{ route('customer.reservations.destroy', $reservation) }}" onsubmit="return confirm('Hapus reservasi {{ $reservation->code }}?');">
                            @csrf
                            @method('DELETE')
                            <button class="button secondary !border-red-200 !text-red-700 hover:!bg-red-50" type="submit">Hapus Reservasi</button>
                        </form>
                    </div>
                @else
                    <div class="mt-6 rounded-2xl border border-slate-200 bg-slate-50 p-4 text-sm text-slate-700">
                        Reservasi sudah terkunci karena bukti pembayaran sudah pernah dikirim.
                    </div>
                @endif
            </div>
        </div>

        <div class="grid gap-6">
            @if ($canUploadPayment)
                <div id="pembayaran">
                    @include('customer.reservations.partials.payment-upload-panel', [
                        'reservation' => $reservation,
                        'company' => $company,
                        'canUploadPayment' => $canUploadPayment,
                    ])
                </div>
            @else
                <div class="card">
                    <div class="card-body">
                        <h2>Informasi Pembayaran</h2>
                        @if($reservation->payment?->proof_image)
                            <p class="mt-3 text-slate-600">Bukti pembayaran sudah dikirim dan saat ini tinggal menunggu proses admin sesuai status yang tampil di ringkasan reservasi.</p>
                        @else
                            <p class="mt-3 text-slate-600">Belum ada tindakan pembayaran yang bisa dilakukan dari halaman ini.</p>
                        @endif
                    </div>
                </div>
            @endif

            @if($reservation->status === 'selesai' && !$reservation->review)
                <div class="card">
                    <div class="card-body">
                        <h2>Review Trip</h2>
                        <p class="mt-3 text-slate-600">Trip sudah selesai. Anda bisa mengirim review untuk pengalaman snorkeling ini.</p>
                        <a class="button secondary mt-4" href="{{ route('customer.reviews.create') }}">Beri Review</a>
                    </div>
                </div>
            @endif
        </div>
    </div>
@endsection
