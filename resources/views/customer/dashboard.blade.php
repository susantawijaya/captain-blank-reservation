@extends('layouts.customer')

@section('title', 'Dashboard Pelanggan')
@section('customer_badge', '')
@section('customer_intro', 'Pantau reservasi terbaru, progres pesan ke admin, dan review snorkeling Anda dari satu dashboard yang lebih ringkas.')
@section('customer_actions')
    <a class="button primary" href="{{ route('reservations.create') }}">Buat Reservasi</a>
@endsection

@section('customer_content')
<div class="customer-page-stack">
    <div class="customer-summary-grid">
        <article class="portal-stat-card">
            <span>Reservasi Terbaru</span>
            <strong>{{ $reservations->count() }}</strong>
            <p>Item reservasi terakhir yang sedang Anda pantau dari dashboard pelanggan.</p>
        </article>
        <article class="portal-stat-card">
            <span>Pesan Aktif</span>
            <strong>{{ $messages->count() }}</strong>
            <p>Lihat pertanyaan terbaru dan cek apakah admin sudah membalas.</p>
        </article>
        <article class="portal-stat-card">
            <span>Review Tersimpan</span>
            <strong>{{ $reviews->count() }}</strong>
            <p>Semua pengalaman trip yang sudah Anda bagikan terkumpul di sini.</p>
        </article>
    </div>

    <div class="grid gap-6 xl:grid-cols-[1.2fr_0.8fr]">
        <div class="card">
            <div class="card-body">
                <h2>Reservasi Terbaru</h2>
                @include('customer.reservations.partials.table', ['reservations' => $reservations])
            </div>
        </div>
        <div class="card">
            <div class="card-body">
                <h2>Review Saya</h2>
                @forelse($reviews as $review)
                    <p><strong>{{ $review->package->name }}</strong><br>{{ $review->comment }}</p>
                @empty
                    <p>Belum ada review.</p>
                @endforelse
            </div>
        </div>
    </div>

    <div class="section-head">
        <div>
            <h2>Pesan Saya</h2>
            <p>Pantau pertanyaan yang Anda kirim dan lihat balasan admin tanpa perlu pindah menu terlalu jauh.</p>
        </div>
        <a class="button primary" href="{{ route('customer.messages.index') }}">Lihat Semua Pesan</a>
    </div>

    <div class="grid three">
        @forelse($messages as $message)
            <div class="card">
                <div class="card-body">
                    <h3>{{ $message->subject }}</h3>
                    <p class="mt-2 line-clamp-3 leading-7 text-slate-600">{{ $message->message }}</p>
                    <div class="mt-4 flex flex-wrap items-center gap-3">
                        <x-status-badge :status="$message->status" />
                        @if($message->admin_reply)
                            <span class="badge">Sudah Dibalas</span>
                        @else
                            <span class="text-sm text-slate-500">Menunggu balasan admin</span>
                        @endif
                    </div>
                    <a class="button secondary mt-4 w-full" href="{{ route('customer.messages.show', $message) }}">Lihat Detail</a>
                </div>
            </div>
        @empty
            <div class="card customer-full-span">
                <div class="card-body">
                    <p>Belum ada pesan yang dikirim dari akun Anda.</p>
                </div>
            </div>
        @endforelse
    </div>
</div>
@endsection
