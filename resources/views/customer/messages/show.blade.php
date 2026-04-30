@extends('layouts.customer')

@section('title', 'Detail Pesan')
@section('customer_title', $message->subject)
@section('customer_badge', 'Detail Pesan')
@section('customer_intro', 'Baca kembali isi pesan Anda dan cek balasan admin pada panel yang sama.')
@section('customer_actions')
    <a class="button secondary" href="{{ route('customer.messages.index') }}">Kembali ke Inbox</a>
    <a class="button primary" href="{{ route('contact.index') }}">Kirim Pesan Baru</a>
@endsection

@section('customer_content')
<div class="grid gap-6 xl:grid-cols-2">
        <div class="card">
            <div class="card-body">
                <h2>Pesan Anda</h2>
                <p class="mt-4"><strong>Status:</strong> <x-status-badge :status="$message->status" /></p>
                <p><strong>Dikirim:</strong> {{ $message->created_at->format('d M Y H:i') }}</p>
                @if($message->reservation)
                    <p><strong>Reservasi:</strong> {{ $message->reservation->code }}</p>
                @endif
                <p class="mt-4 leading-7 text-slate-700">{{ $message->message }}</p>
            </div>
        </div>
        <div class="card">
            <div class="card-body">
                <h2>Balasan Admin</h2>
                @if($message->admin_reply)
                    <div class="rounded-2xl border border-emerald-200 bg-emerald-50 p-4">
                        <p class="leading-7 text-slate-700">{{ $message->admin_reply }}</p>
                        @if($message->replied_at)
                            <p class="mt-3 text-sm text-slate-500">Dibalas pada {{ $message->replied_at->format('d M Y H:i') }}</p>
                        @endif
                    </div>
                @else
                    <p>Admin belum memberikan balasan untuk pesan ini.</p>
                @endif
                <a class="button secondary mt-5" href="{{ route('customer.messages.index') }}">Kembali ke Pesan Saya</a>
            </div>
        </div>
    </div>
@endsection
