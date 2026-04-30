@extends('layouts.admin')

@section('title', 'Detail Pembayaran')

@section('content')
<div class="grid gap-6 xl:grid-cols-2">
    <div class="card">
        <div class="card-body">
            <h1>{{ $payment->reservation->code }}</h1>
            <div class="mt-4 space-y-2 text-sm text-slate-600">
                <p><strong>Pelanggan:</strong> {{ $payment->reservation->user->name }}</p>
                <p><strong>Email:</strong> {{ $payment->reservation->user->email }}</p>
                <p><strong>Paket:</strong> {{ $payment->reservation->package->name }}</p>
                <p><strong>Nominal:</strong> Rp {{ number_format($payment->amount, 0, ',', '.') }}</p>
                <p><strong>Metode:</strong> {{ str_replace('_', ' ', $payment->method) }}</p>
                <p><strong>Status:</strong> <x-status-badge :status="$payment->status" /></p>
                @if($payment->verified_at)
                    <p><strong>Diverifikasi:</strong> {{ $payment->verified_at->format('d M Y H:i') }}</p>
                @endif
                @if($payment->notes)
                    <p><strong>Catatan:</strong> {{ $payment->notes }}</p>
                @endif
            </div>

            @if($payment->proof_image)
                <div class="mt-6">
                    <p class="text-sm font-bold text-slate-700">Bukti Transfer</p>
                    <img class="mt-3 w-full rounded-2xl border border-slate-200 object-cover" src="{{ asset($payment->proof_image) }}" alt="Bukti pembayaran {{ $payment->reservation->code }}">
                </div>
            @endif
        </div>
    </div>
    <div class="card">
        <div class="card-body">
            @include('admin.payments.partials.verification-form')
        </div>
    </div>
</div>
@endsection
